/**
 * Sync engine: token auth, bootstrap, push queue, pull deltas.
 */
(function (window) {
	'use strict';

	var syncing = false;
	var lastSummary = null;

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
				return Promise.all([
					db().setMeta('sync_token', json.token),
					db().setMeta('sync_token_expires', json.expires_at),
					db().setMeta('sync_user', json.user_name || '')
				]).then(function () {
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
		var records = (rows || []).map(function (r) {
			var clientUuid = r.client_uuid || null;
			var serverId = r[idField] != null ? String(r[idField]) : '';
			return Object.assign({}, r, {
				local_id: clientUuid || 'srv:' + serverId,
				client_uuid: clientUuid,
				server_id: serverId,
				sync_status: r.sync_status || 'synced'
			});
		});
		return db().putAll(store, records);
	}

	function applyBootstrap(data) {
		return Promise.all([
			upsertSnapshot('customers', data.customers),
			upsertSnapshot('measurements', data.measurements),
			upsertSnapshot('orders', data.orders),
			db().setMeta('masters', data.masters || {}),
			db().setMeta('capabilities', data.capabilities || {}),
			db().setMeta('last_pull_at', data.server_time || new Date().toISOString()),
			db().setMeta('last_sync_at', data.server_time || new Date().toISOString())
		]);
	}

	function pushPending(auth) {
		return db().pendingOps().then(function (ops) {
			if (!ops.length) {
				return { ok: true, summary: { customers: 0, orders: 0, measurements: 0, conflicts: 0, failed: 0 }, results: [] };
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
					payload: op.payload,
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
						if (res.ok || res.duplicate) {
							return db()
								.updateOp(opId, {
									status: 'synced',
									last_attempt_at: new Date().toISOString(),
									last_error: ''
								})
								.then(function () {
									if (res.server_entity_id && matched && matched.entity_client_id) {
										return db().put('id_map', {
											client_uuid: matched.entity_client_id,
											entity_type: matched.entity_type || '',
											server_id: String(res.server_entity_id),
											synced_at: new Date().toISOString()
										});
									}
									return null;
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
									return db().put('conflicts', {
										id: db().uuid(),
										op_id: opId,
										entity_type: (matched && matched.entity_type) || '',
										server_entity_id: res.server_entity_id || '',
										conflict_fields: res.conflict_fields || [],
										created_at: new Date().toISOString(),
										status: 'open'
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
						return db().updateOp(opId, {
							status: 'pending',
							retry_count: retryBase + 1,
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

	function pull(auth) {
		return db().getMeta('last_pull_at', null).then(function (since) {
			var url = apiUrl('/pull') + (since ? '?since=' + encodeURIComponent(since) : '');
			return fetchJson(url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: authHeaders(auth.token, auth.deviceId)
			}).then(function (json) {
				return applyBootstrap(json).then(function () {
					return json;
				});
			});
		});
	}

	function syncNow(options) {
		options = options || {};
		if (syncing) {
			return Promise.reject(new Error('sync_in_progress'));
		}
		var conn = window.DarziOffline.connectivity;
		syncing = true;
		window.dispatchEvent(new CustomEvent('darzi-offline-sync', { detail: { phase: 'start' } }));

		return conn
			.check()
			.then(function (st) {
				if (st.status !== 'online') {
					throw new Error('offline');
				}
				return ensureAuth(!!options.forceToken);
			})
			.then(function (auth) {
				window.dispatchEvent(new CustomEvent('darzi-offline-sync', { detail: { phase: 'push' } }));
				return pushPending(auth).then(function (pushResult) {
					window.dispatchEvent(new CustomEvent('darzi-offline-sync', { detail: { phase: 'pull' } }));
					return pull(auth).then(function (pullResult) {
						lastSummary = {
							at: new Date().toISOString(),
							push: (pushResult && pushResult.summary) || {},
							ok: true
						};
						return db().setMeta('last_sync_summary', lastSummary).then(function () {
							return lastSummary;
						});
					});
				});
			})
			.then(function (summary) {
				syncing = false;
				window.dispatchEvent(
					new CustomEvent('darzi-offline-sync', { detail: { phase: 'done', summary: summary } })
				);
				return summary;
			})
			.catch(function (err) {
				syncing = false;
				lastSummary = {
					at: new Date().toISOString(),
					ok: false,
					error: (err && err.message) || 'sync_failed'
				};
				window.dispatchEvent(
					new CustomEvent('darzi-offline-sync', { detail: { phase: 'error', error: lastSummary.error } })
				);
				return db().setMeta('last_sync_summary', lastSummary).then(function () {
					throw err;
				});
			});
	}

	function bootstrapIfNeeded() {
		return db().getMeta('last_pull_at', null).then(function (last) {
			if (last) {
				return null;
			}
			return syncNow({ forceToken: true }).catch(function () {
				return null;
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
					return Promise.all([
						db().setMeta('sync_token', null),
						db().setMeta('sync_token_expires', null)
					]);
				});
		});
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.sync = {
		syncNow: syncNow,
		ensureAuth: ensureAuth,
		issueToken: issueToken,
		bootstrapIfNeeded: bootstrapIfNeeded,
		revokeOnLogout: revokeOnLogout,
		isSyncing: function () {
			return syncing;
		},
		getLastSummary: function () {
			return lastSummary;
		},
		t: t
	};
})(window);
