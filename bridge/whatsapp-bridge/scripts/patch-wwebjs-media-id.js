#!/usr/bin/env node
/**
 * Patch whatsapp-web.js Injected/Utils.js for WA Web media send crash:
 * "Data passed to getter must include an id property (it's how we memoize) but got undefined"
 *
 * Upstream: delete message.__x_id after the outgoing message object is built.
 * Safe to re-run; no-ops if already patched or file layout changes.
 */
const fs = require('fs');
const path = require('path');

const target = path.join(
	__dirname,
	'..',
	'node_modules',
	'whatsapp-web.js',
	'src',
	'util',
	'Injected',
	'Utils.js'
);

if (!fs.existsSync(target)) {
	console.warn('patch-wwebjs-media-id: Utils.js not found — skip');
	process.exit(0);
}

const src = fs.readFileSync(target, 'utf8');
if (src.includes('delete message.__x_id')) {
	console.log('patch-wwebjs-media-id: already applied');
	process.exit(0);
}

const needle = '...extraOptions,\n        };';
const insert =
	'...extraOptions,\n        };\n\n        // MediaData spreads an enumerable __x_id that clobbers Msg.id on newer WA Web\n        // ("Data passed to getter must include an id property … got undefined").\n        delete message.__x_id;';

if (!src.includes(needle)) {
	console.warn('patch-wwebjs-media-id: unexpected Utils.js layout — skip');
	process.exit(0);
}

fs.writeFileSync(target, src.replace(needle, insert));
console.log('patch-wwebjs-media-id: applied');
