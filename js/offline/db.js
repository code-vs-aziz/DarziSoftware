/**
 * IndexedDB local database for Darzi Baba ERP offline-first.
 * Attaches window.DarziOffline.db
 */
(function (window) {
	'use strict';

	var DB_NAME = 'darzi_erp_offline';
	var DB_VERSION = 1;
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

	var dbPromise = null;

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
				STORE_NAMES.forEach(function (name) {
					if (db.objectStoreNames.contains(name)) {
						return;
					}
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
					var store = db.createObjectStore(name, { keyPath: keyPath });
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

	function getMeta(key, fallback) {
		return openDb().then(function (db) {
			var tx = db.transaction('meta', 'readonly');
			return reqDone(tx.objectStore('meta').get(key)).then(function (row) {
				return row && typeof row.value !== 'undefined' ? row.value : fallback;
			});
		});
	}

	function setMeta(key, value) {
		return openDb().then(function (db) {
			var tx = db.transaction('meta', 'readwrite');
			tx.objectStore('meta').put({ key: key, value: value, updated_at: new Date().toISOString() });
			return txDone(tx);
		});
	}

	function put(store, record) {
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
			return reqDone(tx.objectStore(store).getAll());
		});
	}

	function getByIndex(store, indexName, value) {
		return openDb().then(function (db) {
			var tx = db.transaction(store, 'readonly');
			return reqDone(tx.objectStore(store).index(indexName).getAll(value));
		});
	}

	function enqueue(op) {
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
			idempotency_key: op.idempotency_key || uuid()
		};
		return put('sync_queue', row).then(function () {
			return row;
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
		return getMeta('device_id', null).then(function (id) {
			if (id) {
				return id;
			}
			id = uuid();
			return setMeta('device_id', id).then(function () {
				return id;
			});
		});
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.db = {
		open: openDb,
		uuid: uuid,
		getMeta: getMeta,
		setMeta: setMeta,
		put: put,
		putAll: putAll,
		getAll: getAll,
		getByIndex: getByIndex,
		enqueue: enqueue,
		pendingOps: pendingOps,
		countPending: countPending,
		updateOp: updateOp,
		ensureDeviceId: ensureDeviceId,
		STORE_NAMES: STORE_NAMES
	};
})(window);
