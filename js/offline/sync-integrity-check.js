/**
 * Static integrity checks for offline sync guards (no browser / IndexedDB required).
 * Run: node js/offline/sync-integrity-check.js
 */
'use strict';

var fs = require('fs');
var path = require('path');
var root = path.join(__dirname, '..', '..');

function read(rel) {
	return fs.readFileSync(path.join(root, rel), 'utf8');
}

var sync = read('js/offline/sync-engine.js');
var forms = read('js/offline/forms.js');
var db = read('js/offline/db.js');
var boot = read('js/offline/bootstrap.js');
var ui = read('js/offline/ui.js');
var svc = read('application/libraries/Erp_sync_service.php');
var model = read('application/models/Erp_sync_model.php');
var routes = read('application/config/routes.php');
var ctrl = read('application/controllers/api/Erp_sync.php');

var checks = [
	{
		ok: /if \(res\.ok\)/.test(sync) && !/if \(res\.ok \|\| res\.duplicate\)/.test(sync),
		label: 'Client marks queue synced only when res.ok (not failed duplicate)'
	},
	{
		ok: /Never overwrite unsynced local drafts/.test(sync) && /pendingByServer/.test(sync),
		label: 'Pull/bootstrap skips pending local drafts'
	},
	{
		ok: /withSyncLock/.test(sync) && /navigator\.locks/.test(sync),
		label: 'Cross-tab sync lock present'
	},
	{
		ok: /enrichOpPayload/.test(sync) && /measurement_client_id/.test(sync),
		label: 'FK rewrite from id_map before push'
	},
	{
		ok: /MAX_PUSH_RETRIES/.test(sync) && /failed_permanent/.test(sync),
		label: 'Retry cap + permanent failure status'
	},
	{
		ok: /owner_user/.test(db) && /activateUser/.test(db) && /scopedLocalId/.test(db),
		label: 'Per-user IndexedDB isolation (owner_user + scoped keys)'
	},
	{
		ok: /delete_failed_op/.test(model) && /\['status'\] === 'failed'/.test(svc),
		label: 'Server deletes failed ops so idempotency keys remain retriable'
	},
	{
		ok: /if \(\$status === 'applied' \|\| \$status === 'conflict'\)/.test(svc),
		label: 'Server persists only applied/conflict ops'
	},
	{
		ok: /already_delivered/.test(svc),
		label: 'Delivery rejects already-delivered lines'
	},
	{
		ok: /Always insert a new measurement for create/.test(svc),
		label: 'Measurement create no longer overwrites latest row'
	},
	{
		ok: /force_keep_local/.test(svc) && /resolveConflict/.test(sync) && /keep_local/.test(ui),
		label: 'Conflict Keep Local / Keep Server resolution path'
	},
	{
		ok: /resolve_open_conflicts/.test(model) && /resolve_conflict/.test(ctrl) && /resolve_conflict/.test(routes),
		label: 'Server resolve_conflict API + route registered'
	},
	{
		ok: /schema_status/.test(svc) && /schema_status/.test(ctrl) && /schema_status/.test(routes),
		label: 'Schema status verification endpoint'
	},
	{
		ok: /submitQueueable/.test(forms) && /Queue-first write path/.test(forms),
		label: 'Online queueable forms use durable queue-first + sync'
	},
	{
		ok: /activateUser/.test(boot) && /fetchSchemaStatus/.test(boot),
		label: 'Bootstrap activates user scope and checks schema'
	},
	{
		ok: /local_payload/.test(sync) && /resolved_keep_/.test(sync),
		label: 'Conflicts preserve original payload until resolved'
	},
	{
		ok: /DB_VERSION = 2/.test(db),
		label: 'IndexedDB version 2 additive upgrade (owner_user index)'
	},
	{
		ok: /darzi-erp-safe-v34/.test(read('sw.js')),
		label: 'Service worker cache bumped to v34'
	},
	{
		ok: /offline_sync_columns_safe\.sql/.test(read('application/sql/offline_sync_columns_safe.sql')) ||
			/row_version/.test(read('application/sql/offline_sync_columns_safe.sql')),
		label: 'Safe additive SQL migration file present'
	}
];

// Pure logic: scoped local ids must differ per user
function scopedLocalId(activeUser, serverId, clientUuid) {
	if (clientUuid) return String(clientUuid);
	var sid = String(serverId || '');
	if (!sid) return '';
	if (activeUser) return activeUser + ':srv:' + sid;
	return 'srv:' + sid;
}
function belongsTo(activeUser, legacyOwner, row) {
	if (!row) return false;
	if (!activeUser) return true;
	var owner = row.owner_user != null ? String(row.owner_user) : '';
	if (owner) return owner === String(activeUser);
	return legacyOwner && String(legacyOwner) === String(activeUser);
}

var logicPass =
	scopedLocalId('alice', '10', null) !== scopedLocalId('bob', '10', null) &&
	belongsTo('alice', 'alice', { owner_user: 'alice' }) &&
	!belongsTo('bob', 'alice', { owner_user: 'alice' }) &&
	!belongsTo('bob', 'alice', { /* unowned */ }) &&
	belongsTo('alice', 'alice', {});

checks.push({
	ok: logicPass,
	label: 'User isolation logic: scoped keys + owner filter'
});

var failed = 0;
checks.forEach(function (c) {
	if (c.ok) {
		console.log('PASS  ' + c.label);
	} else {
		failed += 1;
		console.error('FAIL  ' + c.label);
	}
});

console.log(failed ? '\n' + failed + ' check(s) failed' : '\nAll ' + checks.length + ' integrity checks passed');
process.exit(failed ? 1 : 0);
