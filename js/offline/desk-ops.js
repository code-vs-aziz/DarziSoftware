/**
 * Shared offline write helpers for ERP desk + forms.
 */
(function (window) {
	'use strict';

	function db() {
		return window.DarziOffline.db;
	}

	function provisionalNumber(prefix) {
		return db().getMeta('seq_' + prefix, 0).then(function (n) {
			n = (parseInt(n, 10) || 0) + 1;
			return db().setMeta('seq_' + prefix, n).then(function () {
				var pad = String(n);
				while (pad.length < 4) {
					pad = '0' + pad;
				}
				return 'OFF-' + prefix + '-' + pad;
			});
		});
	}

	function saveCustomer(payload) {
		var clientUuid = db().uuid();
		return provisionalNumber('C').then(function (displayNo) {
			var man = (payload.man_napno || '').toString().trim() || displayNo;
			var record = {
				local_id: clientUuid,
				client_uuid: clientUuid,
				server_id: '',
				man_napno: man,
				name: payload.name || '',
				city: payload.city || '',
				country: payload.country || '',
				Phone: payload.Phone || payload.phone || '',
				Address: payload.Address || payload.address || '',
				cnic: payload.cnic || '',
				cus_type: payload.cus_type || '',
				remarks: payload.remarks || '',
				row_version: 0,
				sync_status: 'pending',
				provisional_no: displayNo,
				updated_at: new Date().toISOString()
			};
			return db()
				.put('customers', record)
				.then(function () {
					return db().enqueue({
						entity_type: 'customer',
						entity_client_id: clientUuid,
						op_type: 'create',
						payload: {
							name: record.name,
							city: record.city,
							country: record.country,
							Phone: record.Phone,
							Address: record.Address,
							cnic: record.cnic,
							cus_type: record.cus_type,
							man_napno: record.man_napno,
							remarks: record.remarks
						},
						idempotency_key: clientUuid + ':customer.create'
					});
				})
				.then(function () {
					return record;
				});
		});
	}

	function saveMeasurement(payload) {
		var clientUuid = db().uuid();
		var serverId = String(payload.code || payload.server_id || '');
		var customerCode = String(payload.customerno1 || payload.customer_code || '');
		var opType = serverId ? 'update' : 'create';
		return provisionalNumber('M').then(function (displayNo) {
			var record = {
				local_id: db().scopedLocalId
					? db().scopedLocalId(serverId, serverId ? null : clientUuid)
					: serverId
						? 'srv:' + serverId
						: clientUuid,
				client_uuid: clientUuid,
				server_id: serverId,
				customer_code: customerCode,
				provisional_no: displayNo,
				payload: payload,
				raw: payload,
				sync_status: 'pending',
				updated_at: new Date().toISOString()
			};
			return db()
				.put('measurements', record)
				.then(function () {
					return db().enqueue({
						entity_type: 'measurement',
						entity_client_id: clientUuid,
						op_type: opType,
						payload: Object.assign({}, payload, {
							server_id: serverId,
							code: serverId,
							customer_code: customerCode,
							client_uuid: clientUuid
						}),
						idempotency_key: clientUuid + ':measurement.' + opType
					});
				})
				.then(function () {
					return record;
				});
		});
	}

	function saveOrder(payload) {
		var clientUuid = db().uuid();
		var enriched = Object.assign({}, payload || {});
		var naapRaw = String(enriched.Naapno || enriched.naap_no || '').trim();
		if (/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(naapRaw)) {
			enriched.measurement_client_id = naapRaw;
			enriched.Naapno = '';
			enriched.naap_no = '';
		}
		return provisionalNumber('O').then(function (displayNo) {
			var record = {
				local_id: clientUuid,
				client_uuid: clientUuid,
				server_id: '',
				provisional_no: displayNo,
				naap_no: enriched.Naapno || enriched.naap_no || '',
				customer_name: enriched.customer_name || '',
				payload: enriched,
				sync_status: 'pending',
				updated_at: new Date().toISOString()
			};
			return db()
				.put('orders', record)
				.then(function () {
					return db().enqueue({
						entity_type: 'order',
						entity_client_id: clientUuid,
						op_type: 'create',
						payload: enriched,
						idempotency_key: clientUuid + ':order.create'
					});
				})
				.then(function () {
					return record;
				});
		});
	}

	function saveCash(payload) {
		var clientUuid = db().uuid();
		return provisionalNumber('CASH').then(function (displayNo) {
			return db()
				.enqueue({
					entity_type: 'cash',
					entity_client_id: clientUuid,
					op_type: 'create',
					payload: payload,
					idempotency_key: clientUuid + ':cash.create'
				})
				.then(function () {
					return { client_uuid: clientUuid, provisional_no: displayNo };
				});
		});
	}

	function saveDelivery(payload) {
		var clientUuid = db().uuid();
		return provisionalNumber('DEL').then(function (displayNo) {
			return db()
				.enqueue({
					entity_type: 'delivery',
					entity_client_id: clientUuid,
					op_type: 'create',
					payload: payload,
					idempotency_key: clientUuid + ':delivery.create'
				})
				.then(function () {
					return { client_uuid: clientUuid, provisional_no: displayNo };
				});
		});
	}

	function saveTransfer(payload) {
		var clientUuid = db().uuid();
		return provisionalNumber('TR').then(function (displayNo) {
			return db()
				.enqueue({
					entity_type: 'transfer',
					entity_client_id: clientUuid,
					op_type: 'process',
					payload: payload,
					idempotency_key: clientUuid + ':transfer.process'
				})
				.then(function () {
					return { client_uuid: clientUuid, provisional_no: displayNo };
				});
		});
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.deskOps = {
		saveCustomer: saveCustomer,
		saveMeasurement: saveMeasurement,
		saveOrder: saveOrder,
		saveCash: saveCash,
		saveDelivery: saveDelivery,
		saveTransfer: saveTransfer,
		provisionalNumber: provisionalNumber
	};
})(window);
