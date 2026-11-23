/**
 * IndexedDB local database for Darzi Baba ERP offline-first.
 * Same database name; business rows scoped by owner_user (no second DB).
 * Attaches window.DarziOffline.db
 */
(function (window) {
	'use strict';

	var DB_NAME = 'darzi_erp_offline';
	var DB_VERSION = 2;
	var STORE_NAMES = [
		'meta',
		'customers',
		'measurements',
		'orders',
		'masters',
		'sync_queue',
		'conflicts',
		'id_map'
	];
	var GLOBAL_META_KEYS = {
		device_id: true,
		active_user: true,
		legacy_data_owner: true,
		cfg_snapshot: true
	};

	var dbPromise = null;
	var activeUser = '';
	var legacyOwner = '';

	function openDb() {
		if (dbPromise) {
			return dbPromise;
		}
		dbPromise = new Promise(function (resolve, reject) {
			if (!window.indexedDB) {
				reject(new Error('indexeddb_unavailable'));
				return;
			}
			var req = window.indexedDB.open(DB_NAME, DB_VERSION);
			req.onupgradeneeded = function (event) {
				var db = event.target.result;
				var tx = event.target.transaction;
				STORE_NAMES.forEach(function (name) {
					var store;
					if (!db.objectStoreNames.contains(name)) {
						var keyPath = 'id';
						if (name === 'meta') {
							keyPath = 'key';
						} else if (name === 'sync_queue') {
							keyPath = 'op_id';
						} else if (name === 'id_map') {
							keyPath = 'client_uuid';
						} else if (name === 'conflicts') {
							keyPath = 'id';
						} else {
							keyPath = 'local_id';
						}
						store = db.createObjectStore(name, { keyPath: keyPath });
						if (name === 'sync_queue') {
							store.createIndex('status', 'status', { unique: false });
							store.createIndex('created_at', 'created_at', { unique: false });
							store.createIndex('idempotency_key', 'idempotency_key', { unique: true });
						}
						if (name === 'customers' || name === 'orders' || name === 'measurements') {
							store.createIndex('server_id', 'server_id', { unique: false });
							store.createIndex('client_uuid', 'client_uuid', { unique: false });
							store.createIndex('sync_status', 'sync_status', { unique: false });
						}
					} else {
						store = tx.objectStore(name);
					}
					if (
						store &&
						(name === 'customers' ||
							name === 'orders' ||
							name === 'measurements' ||
							name === 'conflicts' ||
							name === 'sync_queue') &&
						!store.indexNames.contains('owner_user')
					) {
						try {
							store.createIndex('owner_user', 'owner_user', { unique: false });
						} catch (e) {
							/* index may already exist mid-upgrade */
						}
					}
				});
			};
			req.onsuccess = function () {
				resolve(req.result);
			};
			req.onerror = function () {
				reject(req.error || new Error('indexeddb_open_failed'));
			};
		});
		return dbPromise;
	}

	function txDone(tx) {
		return new Promise(function (resolve, reject) {
			tx.oncomplete = function () {
				resolve();
			};
			tx.onerror = function () {
				reject(tx.error || new Error('tx_error'));
			};
			tx.onabort = function () {
				reject(tx.error || new Error('tx_abort'));
			};
		});
	}

	function reqDone(req) {
		return new Promise(function (resolve, reject) {
			req.onsuccess = function () {
				resolve(req.result);
			};
			req.onerror = function () {
				reject(req.error || new Error('req_error'));
			};
		});
	}

	function uuid() {
		if (window.crypto && typeof window.crypto.randomUUID === 'function') {
			return window.crypto.randomUUID();
		}
		var buf = new Uint8Array(16);
		if (window.crypto && window.crypto.getRandomValues) {
			window.crypto.getRandomValues(buf);
		} else {
			for (var i = 0; i < 16; i++) {
				buf[i] = Math.floor(Math.random() * 256);
			}
		}
		buf[6] = (buf[6] & 0x0f) | 0x40;
		buf[8] = (buf[8] & 0x3f) | 0x80;
		var hex = [];
		for (var j = 0; j < 16; j++) {
			hex.push((buf[j] + 0x100).toString(16).substr(1));
		}
		return (
			hex.slice(0, 4).join('') +
			'-' +
			hex.slice(4, 6).join('') +
			'-' +
			hex.slice(6, 8).join('') +
			'-' +
			hex.slice(8, 10).join('') +
			'-' +
			hex.slice(10, 16).join('')
		);
	}

	function metaKey(key) {
		if (GLOBAL_META_KEYS[key] || !activeUser) {
			return key;
		}
		return 'u:' + activeUser + ':' + key;
	}

	function getMetaRaw(key, fallback) {
		return openDb().then(function (db) {
			var tx = db.transaction('meta', 'readonly');
			return reqDone(tx.objectStore('meta').get(key)).then(function (row) {
				return row && typeof row.value !== 'undefined' ? row.value : fallback;
			});
		});
	}

	function setMetaRaw(key, value) {
		return openDb().then(function (db) {
			var tx = db.transaction('meta', 'readwrite');
			tx.objectStore('meta').put({ key: key, value: value, updated_at: new Date().toISOString() });
			return txDone(tx);
		});
	}

	function getMeta(key, fallback) {
		var scoped = metaKey(key);
		return getMetaRaw(scoped, undefined).then(function (val) {
			if (typeof val !== 'undefined') {
				return val;
			}
			if (scoped !== key) {
				return getMetaRaw(key, fallback);
			}
			return fallback;
		});
	}

	function setMeta(key, value) {
		return setMetaRaw(metaKey(key), value);
	}

	function belongsToActiveUser(row) {
		if (!row) {
			return false;
		}
		if (!activeUser) {
			return true;
		}
		var owner = row.owner_user != null ? String(row.owner_user) : '';
		if (owner) {
			return owner === String(activeUser);
		}
		// Legacy unowned rows: visible only to the user who claimed them first.
		return legacyOwner && String(legacyOwner) === String(activeUser);
	}

	function stampOwner(record) {
		if (!record || typeof record !== 'object') {
			return record;
		}
		if (activeUser && !record.owner_user) {
			record.owner_user = activeUser;
		}
		return record;
	}

	function put(store, record) {
		stampOwner(record);
		return openDb().then(function (db) {
			var tx = db.transaction(store, 'readwrite');
			tx.objectStore(store).put(record);
			return txDone(tx).then(function () {
				return record;
			});
		});
	}

	function putAll(store, records) {
		if (!records || !records.length) {
			return Promise.resolve();
		}
		records.forEach(stampOwner);
		return openDb().then(function (db) {
			var tx = db.transaction(store, 'readwrite');
			var os = tx.objectStore(store);
			records.forEach(function (r) {
				os.put(r);
			});
			return txDone(tx);
		});
	}

	function getAll(store) {
		return openDb().then(function (db) {
			var tx = db.transaction(store, 'readonly');
			return reqDone(tx.objectStore(store).getAll()).then(function (rows) {
				if (store === 'meta' || store === 'masters' || store === 'id_map') {
					return rows || [];
				}
				return (rows || []).filter(belongsToActiveUser);
			});
		});
	}

	function getAllRaw(store) {
		return openDb().then(function (db) {
			var tx = db.transaction(store, 'readonly');
			return reqDone(tx.objectStore(store).getAll());
		});
	}

	function getByIndex(store, indexName, value) {
		return openDb().then(function (db) {
			var tx = db.transaction(store, 'readonly');
			return reqDone(tx.objectStore(store).index(indexName).getAll(value)).then(function (rows) {
				if (store === 'meta' || store === 'masters' || store === 'id_map') {
					return rows || [];
				}
				return (rows || []).filter(belongsToActiveUser);
			});
		});
	}

	function get(store, key) {
		return openDb().then(function (db) {
			var tx = db.transaction(store, 'readonly');
			return reqDone(tx.objectStore(store).get(key)).then(function (row) {
				if (!row) {
					return null;
				}
				if (store === 'meta' || store === 'masters' || store === 'id_map') {
					return row;
				}
				return belongsToActiveUser(row) ? row : null;
			});
		});
	}

	function enqueue(op) {
		return Promise.resolve(activeUser || '').then(function (owner) {
			var row = {
				op_id: op.op_id || uuid(),
				entity_type: op.entity_type,
				entity_client_id: op.entity_client_id || '',
				op_type: op.op_type,
				payload: op.payload || {},
				created_at: op.created_at || new Date().toISOString(),
				status: 'pending',
				retry_count: 0,
				last_attempt_at: null,
				last_error: '',
				idempotency_key: op.idempotency_key || uuid(),
				owner_user: op.owner_user || owner || ''
			};
			return put('sync_queue', row).then(function () {
				return row;
			});
		});
	}

	function pendingOps() {
		return getByIndex('sync_queue', 'status', 'pending').then(function (rows) {
			return (rows || []).sort(function (a, b) {
				return String(a.created_at).localeCompare(String(b.created_at));
			});
		});
	}

	function countPending() {
		return pendingOps().then(function (rows) {
			return rows.length;
		});
	}

	function updateOp(opId, patch) {
		return openDb().then(function (db) {
			var tx = db.transaction('sync_queue', 'readwrite');
			var os = tx.objectStore('sync_queue');
			return reqDone(os.get(opId)).then(function (row) {
				if (!row) {
					return null;
				}
				if (!belongsToActiveUser(row) && row.owner_user) {
					return null;
				}
				Object.keys(patch || {}).forEach(function (k) {
					row[k] = patch[k];
				});
				os.put(row);
				return txDone(tx).then(function () {
					return row;
				});
			});
		});
	}

	function ensureDeviceId() {
		return getMetaRaw('device_id', null).then(function (id) {
			if (id) {
				return id;
			}
			id = uuid();
			return setMetaRaw('device_id', id).then(function () {
				return id;
			});
		});
	}

	function claimUnowned(store, userName) {
		return getAllRaw(store).then(function (rows) {
			var updates = (rows || []).filter(function (r) {
				return r && !r.owner_user;
			});
			if (!updates.length) {
				return null;
			}
			updates.forEach(function (r) {
				r.owner_user = userName;
			});
			return openDb().then(function (db) {
				var tx = db.transaction(store, 'readwrite');
				var os = tx.objectStore(store);
				updates.forEach(function (r) {
					os.put(r);
				});
				return txDone(tx);
			});
		});
	}

	/**
	 * Switch active user without wiping other users' pending data.
	 * First user after upgrade claims legacy unowned rows.
	 */
	function activateUser(userName) {
		userName = String(userName || '').trim();
		return Promise.all([getMetaRaw('legacy_data_owner', null), openDb()]).then(function (parts) {
			legacyOwner = parts[0] || '';
			activeUser = userName;
			var chain = setMetaRaw('active_user', userName);
			if (userName && !legacyOwner) {
				legacyOwner = userName;
				chain = chain
					.then(function () {
						return setMetaRaw('legacy_data_owner', userName);
					})
					.then(function () {
						return Promise.all([
							claimUnowned('customers', userName),
							claimUnowned('measurements', userName),
							claimUnowned('orders', userName),
							claimUnowned('sync_queue', userName),
							claimUnowned('conflicts', userName)
						]);
					});
			}
			return chain.then(function () {
				return { active_user: activeUser, legacy_data_owner: legacyOwner };
			});
		});
	}

	function getActiveUser() {
		return activeUser;
	}

	/** Stable per-user local primary key for server-backed rows (avoids cross-user collisions). */
	function scopedLocalId(serverId, clientUuid) {
		if (clientUuid) {
			return String(clientUuid);
		}
		var sid = String(serverId || '');
		if (!sid) {
			return '';
		}
		if (activeUser) {
			return activeUser + ':srv:' + sid;
		}
		return 'srv:' + sid;
	}

	function restoreActiveUser() {
		return getMetaRaw('active_user', '').then(function (user) {
			return getMetaRaw('legacy_data_owner', null).then(function (legacy) {
				activeUser = user || '';
				legacyOwner = legacy || '';
				return activeUser;
			});
		});
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.db = {
		open: openDb,
		uuid: uuid,
		getMeta: getMeta,
		setMeta: setMeta,
		getMetaRaw: getMetaRaw,
		setMetaRaw: setMetaRaw,
		get: get,
		put: put,
		putAll: putAll,
		getAll: getAll,
		getAllRaw: getAllRaw,
		getByIndex: getByIndex,
		enqueue: enqueue,
		pendingOps: pendingOps,
		countPending: countPending,
		updateOp: updateOp,
		ensureDeviceId: ensureDeviceId,
		activateUser: activateUser,
		restoreActiveUser: restoreActiveUser,
		getActiveUser: getActiveUser,
		scopedLocalId: scopedLocalId,
		belongsToActiveUser: belongsToActiveUser,
		STORE_NAMES: STORE_NAMES,
		DB_VERSION: DB_VERSION
	};
})(window);
