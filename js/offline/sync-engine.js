/**
 * Sync engine: token auth, bootstrap, push queue, pull deltas.
 */
(function (window) {
	'use strict';

	var syncing = false;
	var lastSummary = null;
	var MAX_PUSH_RETRIES = 8;
	var SYNC_LOCK_NAME = 'darzi-erp-sync';
	var SYNC_LOCK_LS_KEY = 'darzi_erp_sync_lock';
	var ENTITY_STORE = {
		customer: 'customers',
		measurement: 'measurements',
		order: 'orders'
	};

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function db() {
		return window.DarziOffline.db;
	}

	function apiUrl(path) {
		var base = (cfg().apiBase || '').replace(/\/$/, '');
		return base + path;
	}

	function t(key, fallback) {
		var lang = window.APP_LANG || {};
		return lang[key] || fallback || key;
	}

	function authHeaders(token, deviceId) {
		var h = {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-Device-Id': deviceId || ''
		};
		if (token) {
			h.Authorization = 'Bearer ' + token;
		}
		return h;
	}

	function fetchJson(url, options) {
		return fetch(url, options).then(function (res) {
			return res.json().catch(function () {
				return {};
			}).then(function (json) {
				if (!res.ok) {
					var err = new Error((json && json.error) || 'http_' + res.status);
					err.status = res.status;
					err.payload = json;
					throw err;
				}
				return json;
			});
		});
	}

	function getAuth() {
		return Promise.all([
			db().getMeta('sync_token', null),
			db().getMeta('sync_token_expires', null),
			db().ensureDeviceId()
		]).then(function (parts) {
			return {
				token: parts[0],
				expires: parts[1],
				deviceId: parts[2]
			};
		});
	}

	function tokenValid(auth) {
		if (!auth.token) {
			return false;
		}
		if (!auth.expires) {
			return true;
		}
		return new Date(auth.expires).getTime() > Date.now() + 60000;
	}

	function issueToken() {
		return db().ensureDeviceId().then(function (deviceId) {
			return fetchJson(apiUrl('/issue_token'), {
				method: 'POST',
				credentials: 'same-origin',
				headers: authHeaders(null, deviceId),
				body: JSON.stringify({
					device_id: deviceId,
					label: (cfg().deviceLabel || navigator.userAgent || '').slice(0, 160)
				})
			}).then(function (json) {
				if (!json.ok || !json.token) {
					throw new Error(json.error || 'issue_token_failed');
				}
				var userName = json.user_name || '';
				var activate =
					db().activateUser && userName
						? db().activateUser(userName)
						: Promise.resolve();
				return activate
					.then(function () {
						return Promise.all([
							db().setMeta('sync_token', json.token),
							db().setMeta('sync_token_expires', json.expires_at),
							db().setMeta('sync_user', userName)
						]);
					})
					.then(function () {
						return json;
					});
			});
		});
	}

	function ensureAuth(forceIssue) {
		return getAuth().then(function (auth) {
			if (!forceIssue && tokenValid(auth)) {
				return auth;
			}
			return issueToken().then(function () {
				return getAuth();
			});
		});
	}

	function upsertSnapshot(store, rows, idField) {
		idField = idField || 'server_id';
		return db()
			.getAll(store)
			.then(function (existing) {
				var pendingByServer = {};
				var pendingByLocal = {};
				(existing || []).forEach(function (row) {
					if (!row || (row.sync_status !== 'pending' && row.sync_status !== 'conflict')) {
						return;
					}
					if (row.server_id) {
						pendingByServer[String(row.server_id)] = true;
					}
					if (row.local_id) {
						pendingByLocal[String(row.local_id)] = true;
					}
					if (row.client_uuid) {
						pendingByLocal[String(row.client_uuid)] = true;
					}
				});

				var records = [];
				(rows || []).forEach(function (r) {
					var clientUuid = r.client_uuid || null;
					var serverId = r[idField] != null ? String(r[idField]) : '';
					var localId = db().scopedLocalId
						? db().scopedLocalId(serverId, clientUuid)
						: clientUuid || (serverId ? 'srv:' + serverId : '');
					if (!localId) {
						return;
					}
					var legacyLocal = serverId ? 'srv:' + serverId : '';
					// Never overwrite unsynced local drafts with pull/bootstrap snapshots.
					if (
						(serverId && pendingByServer[serverId]) ||
						pendingByLocal[localId] ||
						(legacyLocal && pendingByLocal[legacyLocal])
					) {
						return;
					}
					records.push(
						Object.assign({}, r, {
							local_id: localId,
							client_uuid: clientUuid,
							server_id: serverId,
							sync_status: r.sync_status || 'synced'
						})
					);
				});
				return db().putAll(store, records);
			});
	}

	function withSyncLock(work) {
		if (typeof navigator !== 'undefined' && navigator.locks && typeof navigator.locks.request === 'function') {
			return navigator.locks.request(SYNC_LOCK_NAME, { mode: 'exclusive' }, function () {
				return work();
			});
		}
		try {
			var now = Date.now();
			var raw = window.localStorage ? window.localStorage.getItem(SYNC_LOCK_LS_KEY) : null;
			if (raw) {
				var parsed = JSON.parse(raw);
				if (parsed && parsed.until && parsed.until > now) {
					return Promise.reject(new Error('sync_in_progress'));
				}
			}
			if (window.localStorage) {
				window.localStorage.setItem(
					SYNC_LOCK_LS_KEY,
					JSON.stringify({ until: now + 120000, at: now })
				);
			}
		} catch (e) {
			/* ignore lock storage errors */
		}
		return Promise.resolve()
			.then(work)
			.then(
				function (result) {
					try {
						if (window.localStorage) {
							window.localStorage.removeItem(SYNC_LOCK_LS_KEY);
						}
					} catch (e2) {
						/* ignore */
					}
					return result;
				},
				function (err) {
					try {
						if (window.localStorage) {
							window.localStorage.removeItem(SYNC_LOCK_LS_KEY);
						}
					} catch (e3) {
						/* ignore */
					}
					throw err;
				}
			);
	}

	function loadIdMap() {
		return db()
			.getAll('id_map')
			.then(function (rows) {
				var byType = { customer: {}, measurement: {}, order: {}, delivery: {}, cash: {} };
				(rows || []).forEach(function (row) {
					if (!row || !row.client_uuid || !row.server_id) {
						return;
					}
					var type = row.entity_type || '';
					if (!byType[type]) {
						byType[type] = {};
					}
					byType[type][row.client_uuid] = String(row.server_id);
				});
				return byType;
			});
	}

	function enrichOpPayload(op, idMap) {
		var payload = Object.assign({}, op.payload || {});
		var type = op.entity_type;

		if (type === 'measurement') {
			if ((!payload.customer_code || parseInt(payload.customer_code, 10) < 1) && payload.customer_client_id) {
				var custId = idMap.customer[payload.customer_client_id];
				if (custId) {
					payload.customer_code = custId;
					payload.customerno1 = custId;
				}
			}
		}

		if (type === 'order') {
			var naap = parseInt(payload.naap_no || payload.Naapno || '0', 10) || 0;
			if (naap < 1 && payload.measurement_client_id && idMap.measurement[payload.measurement_client_id]) {
				naap = parseInt(idMap.measurement[payload.measurement_client_id], 10) || 0;
				payload.naap_no = String(naap);
				payload.Naapno = String(naap);
			}
			if ((!payload.customer_code || parseInt(payload.customer_code, 10) < 1) && payload.customer_client_id) {
				var oc = idMap.customer[payload.customer_client_id];
				if (oc) {
					payload.customer_code = oc;
					payload.customer_id = oc;
				}
			}
		}

		if (type === 'cash' || type === 'delivery') {
			if ((!payload.customer_code || parseInt(payload.customer_code, 10) < 1) && payload.customer_client_id) {
				var cc = idMap.customer[payload.customer_client_id];
				if (cc) {
					payload.customer_code = cc;
					payload.customer_id = cc;
				}
			}
			if ((!payload.order_id || parseInt(payload.order_id, 10) < 1) && payload.order_client_id) {
				var oid = idMap.order[payload.order_client_id];
				if (oid) {
					payload.order_id = oid;
					payload.order_no = oid;
				}
			}
		}

		return payload;
	}

	function markLocalEntitySynced(matched, serverEntityId) {
		var store = ENTITY_STORE[matched.entity_type];
		if (!store || !matched.entity_client_id) {
			return Promise.resolve();
		}
		var serverId = serverEntityId != null ? String(serverEntityId) : '';
		return db()
			.get(store, matched.entity_client_id)
			.then(function (row) {
				if (row) {
					return row;
				}
				if (!serverId) {
					return null;
				}
				var scoped = db().scopedLocalId ? db().scopedLocalId(serverId, null) : 'srv:' + serverId;
				return db().get(store, scoped).then(function (scopedRow) {
					if (scopedRow) {
						return scopedRow;
					}
					return db().get(store, 'srv:' + serverId);
				});
			})
			.then(function (row) {
				if (!row) {
					return null;
				}
				row.sync_status = 'synced';
				if (serverId) {
					row.server_id = serverId;
				}
				row.updated_at = new Date().toISOString();
				return db().put(store, row);
			});
	}

	function emitDataProgress(detail) {
		window.dispatchEvent(
			new CustomEvent('darzi-offline-data', { detail: detail || {} })
		);
	}

	var DELTA_MAX_AGE_MS = 60 * 60 * 1000; // 1 hour

	function applyBootstrap(data, options) {
		options = options || {};
		var quiet = !!options.quiet;
		var customers = data.customers || [];
		var measurements = data.measurements || [];
		var orders = data.orders || [];
		if (!quiet) {
			emitDataProgress({
				phase: 'download',
				percent: 55,
				delta: true,
				customers: customers.length,
				measurements: measurements.length,
				orders: orders.length
			});
		}
		return Promise.all([
			upsertSnapshot('customers', customers),
			upsertSnapshot('measurements', measurements),
			upsertSnapshot('orders', orders),
			db().setMeta('masters', data.masters || {}),
			db().setMeta('capabilities', data.capabilities || {}),
			db().setMeta('last_pull_at', data.server_time || new Date().toISOString()),
			db().setMeta('last_sync_at', data.server_time || new Date().toISOString())
		]).then(function () {
			// Totals from IndexedDB — never overwrite with delta batch size.
			return countCached().then(function (totals) {
				return db()
					.setMeta('cache_counts', {
						customers: totals.customers,
						measurements: totals.measurements,
						orders: totals.orders,
						delta_customers: customers.length,
						delta_measurements: measurements.length,
						at: new Date().toISOString()
					})
					.then(function () {
						if (!quiet) {
							emitDataProgress({
								phase: 'saved',
								percent: 70,
								customers: totals.customers,
								measurements: totals.measurements,
								orders: totals.orders
							});
						}
						return totals;
					});
			});
		});
	}

	function countCached() {
		return Promise.all([
			db().getAll('customers'),
			db().getAll('measurements'),
			db().getAll('orders')
		]).then(function (parts) {
			return {
				customers: (parts[0] || []).length,
				measurements: (parts[1] || []).length,
				orders: (parts[2] || []).length
			};
		});
	}

	function bootstrapFull() {
		emitDataProgress({ phase: 'start', percent: 5, label: 'auth' });
		return ensureAuth(true).then(function (auth) {
			emitDataProgress({ phase: 'download', percent: 20, label: 'customers' });
			return fetchJson(apiUrl('/bootstrap'), {
				method: 'GET',
				credentials: 'same-origin',
				headers: authHeaders(auth.token, auth.deviceId)
			}).then(function (json) {
				return applyBootstrap(json).then(function (counts) {
					return { ok: true, counts: counts, data: json };
				});
			});
		});
	}

	function pushPending(auth) {
		return Promise.all([db().pendingOps(), db().getMeta('sync_user', ''), loadIdMap()]).then(function (parts) {
			var ops = parts[0] || [];
			var currentUser = parts[1] || '';
			var idMap = parts[2] || {};
			if (!ops.length) {
				return { ok: true, summary: { customers: 0, orders: 0, measurements: 0, conflicts: 0, failed: 0 }, results: [] };
			}

			// Skip queue rows owned by a different logged-in user (shared browser profile).
			ops = ops.filter(function (op) {
				if (!op.owner_user || !currentUser) {
					return true;
				}
				return String(op.owner_user) === String(currentUser);
			});
			if (!ops.length) {
				return { ok: true, summary: { customers: 0, orders: 0, measurements: 0, conflicts: 0, failed: 0 }, results: [], skipped_other_user: true };
			}

			// Dependency order: customer → measurement → order → cash/delivery/transfer
			var rank = { customer: 1, measurement: 2, order: 3, cash: 4, delivery: 5, transfer: 6 };
			ops.sort(function (a, b) {
				var ra = rank[a.entity_type] || 9;
				var rb = rank[b.entity_type] || 9;
				if (ra !== rb) {
					return ra - rb;
				}
				return String(a.created_at).localeCompare(String(b.created_at));
			});

			var batch = ops.slice(0, 50).map(function (op) {
				return {
					op_id: op.op_id,
					entity_type: op.entity_type,
					entity_client_id: op.entity_client_id,
					op_type: op.op_type,
					payload: enrichOpPayload(op, idMap),
					idempotency_key: op.idempotency_key
				};
			});

			return fetchJson(apiUrl('/push'), {
				method: 'POST',
				credentials: 'same-origin',
				headers: authHeaders(auth.token, auth.deviceId),
				body: JSON.stringify({ device_id: auth.deviceId, ops: batch })
			}).then(function (json) {
				var chain = Promise.resolve();
				(json.results || []).forEach(function (res) {
					chain = chain.then(function () {
						var opId = res.op_id;
						if (!opId) {
							return null;
						}
						var matched = null;
						for (var bi = 0; bi < batch.length; bi++) {
							if (batch[bi].op_id === opId) {
								matched = batch[bi];
								break;
							}
						}
						// Only mark synced when the server actually applied (or prior applied duplicate).
						// Never treat failed duplicate / conflict duplicate as success.
						if (res.ok) {
							return db()
								.updateOp(opId, {
									status: 'synced',
									last_attempt_at: new Date().toISOString(),
									last_error: ''
								})
								.then(function () {
									var mapPut = Promise.resolve();
									if (res.server_entity_id && matched && matched.entity_client_id) {
										mapPut = db().put('id_map', {
											client_uuid: matched.entity_client_id,
											entity_type: matched.entity_type || '',
											server_id: String(res.server_entity_id),
											synced_at: new Date().toISOString()
										});
									}
									return mapPut.then(function () {
										return markLocalEntitySynced(matched || {}, res.server_entity_id);
									});
								});
						}
						if (res.conflict) {
							return db()
								.updateOp(opId, {
									status: 'conflict',
									last_attempt_at: new Date().toISOString(),
									last_error: 'conflict'
								})
								.then(function () {
									// Preserve original pending payload until staff resolves.
									return db().put('conflicts', {
										id: db().uuid(),
										op_id: opId,
										entity_type: (matched && matched.entity_type) || '',
										entity_client_id: (matched && matched.entity_client_id) || '',
										op_type: (matched && matched.op_type) || 'update',
										server_entity_id: res.server_entity_id || '',
										conflict_fields: res.conflict_fields || [],
										payload: (matched && matched.payload) || null,
										local_payload: (matched && matched.payload) || null,
										server_snapshot: res.server_snapshot || (res.result && res.result.server_snapshot) || null,
										idempotency_key: (matched && matched.idempotency_key) || '',
										created_at: new Date().toISOString(),
										status: 'open',
										owner_user: db().getActiveUser ? db().getActiveUser() : ''
									});
								});
						}
						var retryBase = 0;
						for (var oi = 0; oi < ops.length; oi++) {
							if (ops[oi].op_id === opId) {
								retryBase = ops[oi].retry_count || 0;
								break;
							}
						}
						var nextRetry = retryBase + 1;
						var permanent = nextRetry >= MAX_PUSH_RETRIES;
						return db().updateOp(opId, {
							status: permanent ? 'failed_permanent' : 'pending',
							retry_count: nextRetry,
							last_attempt_at: new Date().toISOString(),
							last_error: res.error || 'push_failed'
						});
					});
				});
				return chain.then(function () {
					return json;
				});
			});
		});
	}

	function pull(auth, options) {
		options = options || {};
		return db().getMeta('last_pull_at', null).then(function (since) {
			var url = apiUrl('/pull') + (since ? '?since=' + encodeURIComponent(since) : '');
			return fetchJson(url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: authHeaders(auth.token, auth.deviceId)
			}).then(function (json) {
				return applyBootstrap(json, { quiet: !!options.quiet }).then(function (totals) {
					json._cache_totals = totals;
					return json;
				});
			});
		});
	}

	function syncNow(options) {
		options = options || {};
		var quiet = !!options.quiet;
		if (syncing) {
			return Promise.reject(new Error('sync_in_progress'));
		}
		var conn = window.DarziOffline.connectivity;
		syncing = true;
		if (!quiet) {
			window.dispatchEvent(new CustomEvent('darzi-offline-sync', { detail: { phase: 'start' } }));
		}

		return withSyncLock(function () {
			return conn
				.check()
				.then(function (st) {
					if (st.status !== 'online') {
						throw new Error('offline');
					}
					return ensureAuth(!!options.forceToken);
				})
				.then(function (auth) {
					if (!quiet) {
						window.dispatchEvent(new CustomEvent('darzi-offline-sync', { detail: { phase: 'push' } }));
					}
					// Drain pending queue (up to 5 batches) so Sync Now clears "Pending Sync".
					function pushRounds(left, lastPush) {
						if (left < 1) {
							return Promise.resolve(lastPush || { ok: true, summary: {} });
						}
						return pushPending(auth).then(function (pushResult) {
							return db().pendingOps().then(function (ops) {
								if (!ops.length) {
									return pushResult;
								}
								return pushRounds(left - 1, pushResult);
							});
						});
					}
					return pushRounds(5, null).then(function (pushResult) {
						if (!quiet) {
							window.dispatchEvent(new CustomEvent('darzi-offline-sync', { detail: { phase: 'pull' } }));
						}
						return pull(auth, { quiet: quiet }).then(function (pullResult) {
							lastSummary = {
								at: new Date().toISOString(),
								push: (pushResult && pushResult.summary) || {},
								pull: {
									customers: (pullResult && pullResult.customers && pullResult.customers.length) || 0,
									measurements:
										(pullResult && pullResult.measurements && pullResult.measurements.length) || 0
								},
								ok: true,
								quiet: quiet
							};
							return db().setMeta('last_sync_summary', lastSummary).then(function () {
								return lastSummary;
							});
						});
					});
				});
		})
			.then(function (summary) {
				syncing = false;
				window.dispatchEvent(
					new CustomEvent('darzi-offline-sync', {
						detail: { phase: 'done', summary: summary, quiet: quiet }
					})
				);
				return summary;
			})
			.catch(function (err) {
				syncing = false;
				lastSummary = {
					at: new Date().toISOString(),
					ok: false,
					error: (err && err.message) || 'sync_failed',
					quiet: quiet
				};
				window.dispatchEvent(
					new CustomEvent('darzi-offline-sync', {
						detail: { phase: 'error', error: lastSummary.error, quiet: quiet }
					})
				);
				return db().setMeta('last_sync_summary', lastSummary).then(function () {
					throw err;
				});
			});
	}

	/** Hourly: only new/changed rows since last_pull_at (no full reload). */
	function deltaSyncIfDue(options) {
		options = options || {};
		var maxAge = typeof options.maxAgeMs === 'number' ? options.maxAgeMs : DELTA_MAX_AGE_MS;
		return db().getMeta('last_pull_at', null).then(function (last) {
			var age = last ? Date.now() - Date.parse(last) : Infinity;
			if (!options.force && last && age < maxAge) {
				return { ok: true, skipped: true, reason: 'fresh', age_ms: age };
			}
			return countCached().then(function (counts) {
				if (counts.customers < 1) {
					return ensureOfflineData({ force: true });
				}
				return syncNow({ quiet: true, forceToken: false }).then(function (summary) {
					return { ok: true, delta: true, summary: summary };
				});
			});
		});
	}

	function bootstrapIfNeeded() {
		return ensureOfflineData({ force: false });
	}

	/**
	 * Make sure IndexedDB has customers/measurements for offline search.
	 * Pages alone are not enough — without this, WiFi-off search looks broken.
	 */
	function ensureOfflineData(options) {
		options = options || {};
		var force = !!options.force;
		return countCached().then(function (counts) {
			var hasData = counts.customers > 0;
			return db().getMeta('last_pull_at', null).then(function (last) {
				var age = last ? Date.now() - Date.parse(last) : Infinity;
				// Have data and still fresh (< 1h): skip.
				if (!force && hasData && last && age < DELTA_MAX_AGE_MS) {
					emitDataProgress({
						phase: 'ready',
						percent: 100,
						skipped: true,
						customers: counts.customers,
						measurements: counts.measurements,
						orders: counts.orders
					});
					return { ok: true, skipped: true, counts: counts };
				}
				// Have data but older than 1h: delta pull only (new/changed rows).
				if (!force && hasData && last) {
					return syncNow({ quiet: true }).then(function (summary) {
						return countCached().then(function (totals) {
							emitDataProgress({
								phase: 'ready',
								percent: 100,
								skipped: true,
								delta: true,
								customers: totals.customers,
								measurements: totals.measurements,
								orders: totals.orders
							});
							return { ok: true, delta: true, summary: summary, counts: totals };
						});
					});
				}
				emitDataProgress({
					phase: 'start',
					percent: 8,
					customers: counts.customers,
					measurements: counts.measurements
				});
				// Always push pending first so bootstrap/pull cannot race ahead of local drafts.
				var prep =
					force || !hasData
						? db().pendingOps().then(function (ops) {
								if (ops && ops.length) {
									return syncNow({ quiet: true }).catch(function () {
										return null;
									});
								}
								return null;
						  })
						: Promise.resolve(null);
				return prep
					.then(function () {
						return bootstrapFull();
					})
					.then(function (res) {
						emitDataProgress({
							phase: 'ready',
							percent: 100,
							customers: (res.counts && res.counts.customers) || 0,
							measurements: (res.counts && res.counts.measurements) || 0,
							orders: (res.counts && res.counts.orders) || 0
						});
						db().setMeta('offline_data_ready', true);
						return res;
					})
					.catch(function (err) {
						emitDataProgress({
							phase: 'error',
							percent: 0,
							error: (err && err.message) || 'data_sync_failed'
						});
						db().setMeta('offline_data_ready', false);
						throw err;
					});
			});
		});
	}

	function revokeOnLogout() {
		return getAuth().then(function (auth) {
			if (!auth.token && !auth.deviceId) {
				return null;
			}
			return fetchJson(apiUrl('/revoke_token'), {
				method: 'POST',
				credentials: 'same-origin',
				headers: authHeaders(auth.token, auth.deviceId),
				body: JSON.stringify({ device_id: auth.deviceId })
			})
				.catch(function () {
					return null;
				})
				.then(function () {
					// Keep queue + snapshots; only clear auth meta for this user scope.
					return Promise.all([
						db().setMeta('sync_token', null),
						db().setMeta('sync_token_expires', null)
					]);
				});
		});
	}

	function fetchSchemaStatus() {
		return ensureAuth(false).then(function (auth) {
			return fetchJson(apiUrl('/schema_status'), {
				method: 'GET',
				credentials: 'same-origin',
				headers: authHeaders(auth.token, auth.deviceId)
			}).then(function (json) {
				return db().setMeta('schema_status', json).then(function () {
					return json;
				});
			});
		});
	}

	/**
	 * Resolve a local conflict: keep_local re-queues with force flag; keep_server discards local op.
	 * Original conflict row is retained with status resolved_* (never silently deleted).
	 */
	function resolveConflict(conflictId, resolution) {
		if (resolution !== 'keep_local' && resolution !== 'keep_server') {
			return Promise.reject(new Error('invalid_resolution'));
		}
		return db()
			.get('conflicts', conflictId)
			.then(function (conflict) {
				if (!conflict || conflict.status !== 'open') {
					throw new Error('conflict_not_found');
				}
				if (
					resolution === 'keep_local' &&
					(conflict.entity_type === 'delivery' || conflict.entity_type === 'cash')
				) {
					throw new Error('keep_local_not_allowed');
				}

				var localPayload = conflict.local_payload || conflict.payload || {};
				var notifyServer = function () {
					return ensureAuth(false)
						.then(function (auth) {
							return fetchJson(apiUrl('/resolve_conflict'), {
								method: 'POST',
								credentials: 'same-origin',
								headers: authHeaders(auth.token, auth.deviceId),
								body: JSON.stringify({
									device_id: auth.deviceId,
									entity_type: conflict.entity_type,
									server_entity_id: conflict.server_entity_id || '',
									resolution: resolution,
									op_id: conflict.op_id || ''
								})
							}).catch(function () {
								return { ok: false, offline: true };
							});
						})
						.catch(function () {
							return { ok: false, offline: true };
						});
				};

				if (resolution === 'keep_server') {
					return db()
						.updateOp(conflict.op_id, {
							status: 'discarded_keep_server',
							last_error: 'resolved_keep_server'
						})
						.then(function () {
							conflict.status = 'resolved_keep_server';
							conflict.resolved_at = new Date().toISOString();
							conflict.resolution = 'keep_server';
							return db().put('conflicts', conflict);
						})
						.then(function () {
							var store = ENTITY_STORE[conflict.entity_type];
							if (!store || !conflict.server_entity_id) {
								return null;
							}
							var sid = conflict.server_entity_id;
							var scoped = db().scopedLocalId ? db().scopedLocalId(sid, null) : 'srv:' + sid;
							return db()
								.get(store, scoped)
								.then(function (row) {
									if (!row) {
										return db().get(store, 'srv:' + sid);
									}
									return row;
								})
								.then(function (row) {
									if (!row) {
										return null;
									}
									row.sync_status = 'synced';
									row.updated_at = new Date().toISOString();
									return db().put(store, row);
								});
						})
						.then(notifyServer)
						.then(function () {
							window.dispatchEvent(
								new CustomEvent('darzi-offline-conflict-resolved', {
									detail: { id: conflictId, resolution: resolution }
								})
							);
							return { ok: true, resolution: resolution };
						});
				}

				// keep_local: re-queue forced apply; preserve conflict history.
				var newPayload = Object.assign({}, localPayload, {
					force_keep_local: true,
					server_id: conflict.server_entity_id || localPayload.server_id || '',
					base_row_version: 0
				});
				return db()
					.updateOp(conflict.op_id, {
						status: 'superseded_keep_local',
						last_error: 'resolved_keep_local'
					})
					.then(function () {
						return db().enqueue({
							entity_type: conflict.entity_type,
							entity_client_id: conflict.entity_client_id || db().uuid(),
							op_type: conflict.op_type || 'update',
							payload: newPayload,
							idempotency_key:
								db().uuid() + ':resolve.keep_local:' + (conflict.server_entity_id || '')
						});
					})
					.then(function () {
						conflict.status = 'resolved_keep_local';
						conflict.resolved_at = new Date().toISOString();
						conflict.resolution = 'keep_local';
						return db().put('conflicts', conflict);
					})
					.then(notifyServer)
					.then(function () {
						window.dispatchEvent(
							new CustomEvent('darzi-offline-conflict-resolved', {
								detail: { id: conflictId, resolution: resolution }
							})
						);
						var conn = window.DarziOffline.connectivity;
						if (conn && conn.isOnline()) {
							return syncNow({ quiet: true }).catch(function () {
								return { ok: true, resolution: resolution, pending_push: true };
							});
						}
						return { ok: true, resolution: resolution, pending_push: true };
					});
			});
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.sync = {
		syncNow: syncNow,
		ensureAuth: ensureAuth,
		issueToken: issueToken,
		bootstrapIfNeeded: bootstrapIfNeeded,
		ensureOfflineData: ensureOfflineData,
		deltaSyncIfDue: deltaSyncIfDue,
		bootstrapFull: bootstrapFull,
		countCached: countCached,
		DELTA_MAX_AGE_MS: DELTA_MAX_AGE_MS,
		revokeOnLogout: revokeOnLogout,
		resolveConflict: resolveConflict,
		fetchSchemaStatus: fetchSchemaStatus,
		isSyncing: function () {
			return syncing;
		},
		getLastSummary: function () {
			return lastSummary;
		},
		t: t
	};
})(window);
