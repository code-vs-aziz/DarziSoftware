/**
 * DarziSoftware — free local WhatsApp bridge (no Cloud API).
 * Uses your shop WhatsApp Web session via whatsapp-web.js.
 *
 * Setup:
 *   cd bridge/whatsapp-bridge
 *   npm install
 *   npm start
 *
 * QR + restart are also available in Customer Messages UI (not only terminal).
 */
const express = require('express');
const qrcodeTerminal = require('qrcode-terminal');
const QRCode = require('qrcode');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const fs = require('fs');
const path = require('path');

const PORT = parseInt(process.env.WHATSAPP_BRIDGE_PORT || '3099', 10);
const SECRET = String(process.env.WHATSAPP_BRIDGE_SECRET || '').trim();
const AUTH_PATH = path.resolve(__dirname, '.wwebjs_auth');

const app = express();
app.use(express.json({ limit: '8mb' }));

let client = null;
let clientReady = false;
let lastError = '';
let initializing = false;
let lastQr = null;
let lastQrDataUrl = null;
let lastQrAt = null;
let state = 'starting'; // starting | qr | ready | disconnected | restarting | authenticated
let bootStartedAt = Date.now();
let resetInFlight = null;
let clientGeneration = 0;

function withTimeout(promise, ms, label) {
	let timer = null;
	return Promise.race([
		Promise.resolve(promise).finally(() => {
			if (timer) {
				clearTimeout(timer);
			}
		}),
		new Promise((_, reject) => {
			timer = setTimeout(() => {
				reject(new Error((label || 'operation') + '_timeout'));
			}, ms);
		}),
	]);
}

function markBootProgress() {
	bootStartedAt = Date.now();
}

function checkSecret(req, res) {
	if (!SECRET) {
		return true;
	}
	const header = String(req.get('x-bridge-secret') || (req.body && req.body.secret) || '');
	if (header !== SECRET) {
		res.status(403).json({ success: false, message: 'forbidden' });
		return false;
	}
	return true;
}

function normalizePhone(phone) {
	let digits = String(phone || '').replace(/\D+/g, '');
	if (!digits) {
		return '';
	}
	if (digits.startsWith('92') && digits.length >= 12) {
		return digits;
	}
	if (digits[0] === '0' && digits.length >= 11) {
		return '92' + digits.slice(1);
	}
	if (digits.length === 10 && digits[0] === '3') {
		return '92' + digits;
	}
	return digits;
}

function markSessionBroken(err) {
	const msg = err && err.message ? err.message : String(err || '');
	lastError = msg;
	if (/detached Frame|Session closed|Target closed|Protocol error|Execution context was destroyed/i.test(msg)) {
		clientReady = false;
		state = 'disconnected';
	}
}

async function resolveChatId(phone) {
	const digits = normalizePhone(phone);
	if (!digits || digits.length < 11) {
		return '';
	}
	try {
		const numberId = await client.getNumberId(digits);
		if (typeof numberId === 'string' && numberId.includes('@')) {
			return numberId;
		}
		if (numberId && typeof numberId === 'object') {
			if (numberId._serialized) {
				return String(numberId._serialized);
			}
			if (numberId.user) {
				return String(numberId.user) + '@' + String(numberId.server || 'c.us');
			}
		}
	} catch (e) {
		// fall through to c.us
	}
	return digits + '@c.us';
}

function mimeForFile(filePath) {
	const lower = String(path.basename(filePath) || '').toLowerCase();
	if (lower.endsWith('.pdf')) {
		return 'application/pdf';
	}
	if (lower.endsWith('.png')) {
		return 'image/png';
	}
	if (lower.endsWith('.jpg') || lower.endsWith('.jpeg')) {
		return 'image/jpeg';
	}
	return 'application/octet-stream';
}

function buildDocumentMedia(filePath) {
	const filename = path.basename(filePath);
	const mime = mimeForFile(filePath);
	const buf = fs.readFileSync(filePath);
	const data = buf.toString('base64');
	// Explicit filesize helps newer WhatsApp Web builds that memoize by id/size.
	const media = new MessageMedia(mime, data, filename, buf.length);
	if (!media.filesize) {
		media.filesize = buf.length;
	}
	return media;
}

function chatIdFromNumberId(numberId) {
	if (!numberId) {
		return '';
	}
	if (typeof numberId === 'string' && numberId.includes('@')) {
		return numberId;
	}
	if (typeof numberId === 'object') {
		if (numberId._serialized) {
			return String(numberId._serialized);
		}
		if (numberId.user) {
			return String(numberId.user) + '@' + String(numberId.server || 'c.us');
		}
	}
	return '';
}

function isSessionFrameError(err) {
	return /detached Frame|Session closed|Target closed|Protocol error|Execution context was destroyed/i.test(
		errText(err)
	);
}

/**
 * Resolve WhatsApp chat id for media. getNumberId can flake (detached frame) and
 * must NOT be reported as "number_not_on_whatsapp" when the number is fine.
 * After the __x_id media patch, @c.us fallback is safe enough for documents.
 */
async function resolveChatIdStrict(phone) {
	const digits = normalizePhone(phone);
	if (!digits || digits.length < 11) {
		return { chatId: '', reason: 'invalid_phone' };
	}

	const candidates = [digits];
	if (digits.startsWith('92') && digits.length >= 12) {
		candidates.push(digits.slice(2)); // national without leading 0
		candidates.push('0' + digits.slice(2)); // local 03xx…
	}

	let sawExplicitMissing = false;
	let frameBroken = false;

	for (let round = 0; round < 2; round++) {
		if (round === 1) {
			await new Promise((r) => setTimeout(r, 600));
		}
		for (const cand of candidates) {
			try {
				const numberId = await client.getNumberId(cand);
				const chatId = chatIdFromNumberId(numberId);
				if (chatId) {
					return { chatId: chatId, reason: '' };
				}
				sawExplicitMissing = true;
			} catch (e) {
				console.error('getNumberId failed (' + cand + '):', errText(e));
				if (isSessionFrameError(e)) {
					frameBroken = true;
					markSessionBroken(e);
				}
			}
		}
		if (frameBroken) {
			break;
		}
	}

	// Technical failure / flaky WA Web — still try classic wid (media patch fixes memoize).
	if (frameBroken || !sawExplicitMissing) {
		console.warn('resolveChatIdStrict fallback to @c.us for', digits);
		return { chatId: digits + '@c.us', reason: frameBroken ? 'frame_fallback' : 'lookup_fallback' };
	}

	// getNumberId returned null for all formats → likely not on WhatsApp.
	return { chatId: '', reason: 'number_not_on_whatsapp' };
}

async function waitUntilClientReady(ms) {
	const deadline = Date.now() + (ms || 45000);
	while (Date.now() < deadline) {
		if (clientReady && client) {
			return true;
		}
		await new Promise((r) => setTimeout(r, 500));
	}
	return !!(clientReady && client);
}

/**
 * Soft reconnect when Puppeteer frame dies (common after restarts / long idle).
 * Keeps LocalAuth session — no new QR in most cases.
 */
async function healDetachedSession(reason) {
	console.warn('Healing WhatsApp session:', reason || 'detached_frame');
	lastError = String(reason || 'detached_frame');
	clientReady = false;
	try {
		await restartBridge(false);
	} catch (e) {
		console.error('heal restart failed:', errText(e));
		return false;
	}
	return waitUntilClientReady(50000);
}

async function sendDocumentToPhoneOnce(phone, filePath, caption) {
	const resolved = await resolveChatIdStrict(phone);
	const chatId = resolved.chatId;
	if (!chatId) {
		const err = new Error(resolved.reason || 'number_not_on_whatsapp');
		err.code = resolved.reason || 'number_not_on_whatsapp';
		throw err;
	}
	console.log('send-document to', chatId, resolved.reason ? '(' + resolved.reason + ')' : '');

	const media = buildDocumentMedia(filePath);
	const captionText = String(caption || '').trim();
	const safeCaption = captionText.length > 900 ? captionText.slice(0, 897) + '…' : captionText;
	let captionSent = false;

	// Proven path first: text, then PDF (most reliable on WA Web).
	// Then try combined caption+document as a bonus single-bubble send when possible.
	if (safeCaption) {
		try {
			await client.sendMessage(chatId, safeCaption);
			captionSent = true;
		} catch (capErr) {
			console.error('document caption (text) failed:', errText(capErr));
			if (isSessionFrameError(capErr)) {
				throw capErr;
			}
		}
	}

	const attempts = [
		async () => client.sendMessage(chatId, media, { sendMediaAsDocument: true }),
		async () => {
			const chat = await client.getChatById(chatId);
			return chat.sendMessage(media, { sendMediaAsDocument: true });
		},
		async () => client.sendMessage(chatId, media),
		async () => {
			if (typeof MessageMedia.fromFilePath !== 'function') {
				throw new Error('fromFilePath_unavailable');
			}
			const alt = MessageMedia.fromFilePath(filePath);
			if (alt && !alt.filesize) {
				try {
					alt.filesize = fs.statSync(filePath).size;
				} catch (e) {
					/* ignore */
				}
			}
			return client.sendMessage(chatId, alt, {
				sendMediaAsDocument: true,
				caption: captionSent ? undefined : safeCaption || undefined,
			});
		},
	];

	let lastErr = null;
	for (let i = 0; i < attempts.length; i++) {
		try {
			const result = await attempts[i]();
			return result;
		} catch (attemptErr) {
			lastErr = attemptErr;
			console.error('send-document attempt ' + (i + 1) + ' failed:', errText(attemptErr));
			if (isSessionFrameError(attemptErr)) {
				throw attemptErr;
			}
		}
	}

	// Optional single-bubble retry only if text was NOT already delivered.
	if (safeCaption && !captionSent) {
		try {
			const result = await client.sendMessage(chatId, media, {
				sendMediaAsDocument: true,
				caption: safeCaption,
			});
			console.log('send-document combined caption+pdf ok');
			return result;
		} catch (combinedErr) {
			console.error('send-document combined failed:', errText(combinedErr));
			lastErr = combinedErr;
			if (isSessionFrameError(combinedErr)) {
				throw combinedErr;
			}
		}
	}

	if (captionSent) {
		const partial = new Error(
			'caption_sent_document_failed:' + errText(lastErr)
		);
		partial.code = 'caption_sent_document_failed';
		partial.captionSent = true;
		throw partial;
	}

	throw lastErr || new Error('send_document_failed');
}

let healInFlight = null;

function kickBackgroundHeal(reason) {
	if (healInFlight || resetInFlight) {
		return healInFlight || resetInFlight;
	}
	healInFlight = healDetachedSession(reason || 'background_heal')
		.finally(() => {
			healInFlight = null;
		});
	return healInFlight;
}

async function sendDocumentToPhone(phone, filePath, caption) {
	// Never block the HTTP request for a long heal — PHP would freeze the whole ERP.
	if (!clientReady || isSessionFrameError({ message: lastError })) {
		kickBackgroundHeal(lastError || 'not_ready');
		const err = new Error('bridge_session_recovering');
		err.code = 'bridge_session_recovering';
		throw err;
	}

	try {
		return await sendDocumentToPhoneOnce(phone, filePath, caption);
	} catch (err) {
		if (isSessionFrameError(err)) {
			markSessionBroken(err);
			kickBackgroundHeal(errText(err));
		}
		throw err;
	}
}

function errText(err) {
	if (!err) {
		return 'unknown_error';
	}
	if (typeof err === 'string') {
		return err;
	}
	if (err.message) {
		return String(err.message);
	}
	try {
		return JSON.stringify(err);
	} catch (e) {
		return String(err);
	}
}

function attachClientEvents(c) {
	c.on('qr', async (qr) => {
		clientReady = false;
		initializing = false;
		state = 'qr';
		lastQr = qr;
		lastQrAt = new Date().toISOString();
		lastError = '';
		markBootProgress();
		console.log('\nScan this QR with WhatsApp (Linked Devices) — also shown in Customer Messages UI:\n');
		qrcodeTerminal.generate(qr, { small: true });
		try {
			lastQrDataUrl = await QRCode.toDataURL(qr, {
				errorCorrectionLevel: 'M',
				type: 'image/png',
				width: 320,
				margin: 2,
			});
		} catch (err) {
			lastQrDataUrl = null;
			console.error('QR image error:', err.message || err);
		}
	});

	c.on('ready', () => {
		clientReady = true;
		initializing = false;
		lastError = '';
		lastQr = null;
		lastQrDataUrl = null;
		state = 'ready';
		markBootProgress();
		console.log('WhatsApp bridge ready on port', PORT);
	});

	c.on('authenticated', () => {
		lastError = '';
		state = 'authenticated';
		markBootProgress();
		console.log('WhatsApp authenticated');
		// If Chromium hangs after auth and never reaches ready, force a fresh QR session.
		setTimeout(() => {
			if (!clientReady && (state === 'authenticated' || state === 'starting')) {
				// Soft reconnect first — hard-clearing mid-boot often crashes the Node process.
				console.warn('Stuck after authenticate — soft restart (keep session)');
				lastError = 'session_stuck_soft_restart';
				restartBridge(false).catch((e) => {
					console.error('Soft restart failed:', e && e.message ? e.message : e);
				});
			}
		}, 90000);
	});

	c.on('loading_screen', (percent, message) => {
		console.log('Loading WhatsApp', percent, message || '');
		if (!clientReady && state !== 'qr') {
			state = 'starting';
		}
	});

	c.on('auth_failure', (msg) => {
		clientReady = false;
		initializing = false;
		lastError = String(msg || 'auth_failure');
		state = 'disconnected';
		console.error('Auth failure:', lastError);
	});

	c.on('disconnected', (reason) => {
		clientReady = false;
		lastError = String(reason || 'disconnected');
		state = 'disconnected';
		console.warn('Disconnected:', lastError);
	});
}

function createClient() {
	return new Client({
		authStrategy: new LocalAuth({ dataPath: AUTH_PATH }),
		puppeteer: {
			headless: true,
			args: [
				'--no-sandbox',
				'--disable-setuid-sandbox',
				'--disable-dev-shm-usage',
				'--disable-gpu',
			],
		},
	});
}

function killOrphanBrowsers() {
	try {
		const { execSync } = require('child_process');
		// Match only Chromium's LocalAuth session profile (not node / shells).
		const sessionDir = path.join(AUTH_PATH, 'session');
		// Keep this short — a long sync pkill blocks /status and makes ERP show "Bridge down".
		execSync(
			'pkill -f ' + JSON.stringify('user-data-dir=' + sessionDir) + ' || true',
			{ stdio: 'ignore', timeout: 2000 }
		);
	} catch (e) {
		// ignore
	}
	clearBrowserLocks();
}

function startClient() {
	if (initializing) {
		return;
	}
	const gen = ++clientGeneration;
	initializing = true;
	// Never leave UI stuck on "restarting" after the new client has begun booting.
	state = 'starting';
	markBootProgress();
	client = createClient();
	attachClientEvents(client);
	client.initialize().catch(async (err) => {
		if (gen !== clientGeneration) {
			return; // superseded by a newer start/reset
		}
		const msg = err && err.message ? err.message : String(err || '');
		console.error('Init error:', msg);
		if (/already running|userDataDir/i.test(msg)) {
			console.warn('Retrying after clearing orphan Chromium…');
			try {
				await destroyClient();
			} catch (e) {
				// ignore
			}
			killOrphanBrowsers();
			await new Promise((r) => setTimeout(r, 1500));
			if (gen !== clientGeneration) {
				return;
			}
			initializing = false;
			return startClient();
		}
		initializing = false;
		clientReady = false;
		lastError = msg;
		state = 'disconnected';
	});
}

async function destroyClient() {
	clientGeneration += 1; // invalidate in-flight initialize() handlers
	initializing = false;
	if (!client) {
		return;
	}
	const c = client;
	client = null;
	clientReady = false;
	try {
		await withTimeout(c.destroy(), 8000, 'destroy');
	} catch (e) {
		// ignore — force-null already done; orphans killed separately
	}
}

function clearAuthFolder() {
	try {
		if (fs.existsSync(AUTH_PATH)) {
			fs.rmSync(AUTH_PATH, { recursive: true, force: true });
		}
	} catch (err) {
		console.error('Could not clear auth folder:', err.message || err);
	}
}

function clearBrowserLocks() {
	try {
		if (!fs.existsSync(AUTH_PATH)) {
			return;
		}
		const walk = (dir) => {
			for (const name of fs.readdirSync(dir)) {
				const full = path.join(dir, name);
				let st;
				try {
					st = fs.statSync(full);
				} catch (e) {
					continue;
				}
				if (st.isDirectory()) {
					walk(full);
				} else if (/^Singleton/i.test(name) || name === 'lockfile') {
					try {
						fs.unlinkSync(full);
					} catch (e) {
						// ignore
					}
				}
			}
		};
		walk(AUTH_PATH);
	} catch (err) {
		console.error('Could not clear browser locks:', err.message || err);
	}
}

/**
 * Soft reconnect (keep session) or hard reset (clear session → new QR).
 * Logout/destroy are hard-capped so PHP UI never blocks for minutes.
 */
async function restartBridge(clearSession) {
	state = 'restarting';
	initializing = false;
	clientReady = false;
	lastQr = null;
	lastQrDataUrl = null;
	lastQrAt = null;
	lastError = '';
	markBootProgress();

	if (client) {
		if (clearSession) {
			try {
				await withTimeout(client.logout(), 4000, 'logout');
			} catch (e) {
				console.warn('Logout skipped:', e && e.message ? e.message : e);
			}
		}
		try {
			await destroyClient();
		} catch (e) {
			client = null;
			clientReady = false;
		}
	}

	killOrphanBrowsers();

	// Ensure Chromium is gone before wiping the profile (avoids crash/race).
	await new Promise((r) => setTimeout(r, 600));
	if (clearSession) {
		clearAuthFolder();
	} else {
		clearBrowserLocks();
	}

	await new Promise((r) => setTimeout(r, 900));
	initializing = false;
	startClient();
}

/**
 * Hard wipe auth + orphans, boot client, wait briefly for a fresh QR image.
 * Used by the dedicated "Reset & Regenerate QR" control.
 */
async function resetAndWaitForQr(waitMs) {
	const waitFor = typeof waitMs === 'number' && waitMs > 0 ? waitMs : 35000;
	if (resetInFlight) {
		return resetInFlight;
	}
	resetInFlight = (async () => {
		await restartBridge(true);
		const deadline = Date.now() + waitFor;
		while (Date.now() < deadline) {
			if (lastQrDataUrl) {
				return {
					success: true,
					message: 'qr_ready',
					ready: false,
					state: state,
					has_qr: true,
					qr: lastQrDataUrl,
					qr_at: lastQrAt,
					error: lastError || null,
				};
			}
			if (clientReady) {
				return {
					success: true,
					message: 'already_ready',
					ready: true,
					state: 'ready',
					has_qr: false,
					qr: null,
					qr_at: null,
					error: null,
				};
			}
			await new Promise((r) => setTimeout(r, 400));
		}
		return {
			success: true,
			message: 'waiting_for_qr',
			ready: clientReady,
			state: state,
			has_qr: !!lastQrDataUrl,
			qr: lastQrDataUrl,
			qr_at: lastQrAt,
			error: lastError || null,
		};
	})().finally(() => {
		resetInFlight = null;
	});
	return resetInFlight;
}

startClient();

// If boot stalls without QR/ready, auto hard-reset once (orphans / bad session).
setInterval(() => {
	if (clientReady || lastQrDataUrl || resetInFlight) {
		return;
	}
	if (!['starting', 'restarting', 'authenticated'].includes(state)) {
		return;
	}
	if (Date.now() - bootStartedAt < 75000) {
		return;
	}
	console.warn('QR watchdog: stuck in', state, '— forcing hard reset');
	lastError = 'qr_watchdog_reset';
	resetAndWaitForQr(20000).catch((e) => {
		console.error('QR watchdog reset failed:', e && e.message ? e.message : e);
	});
}, 15000);

app.get('/status', (req, res) => {
	if (!checkSecret(req, res)) {
		return;
	}
	res.json({
		success: true,
		ready: clientReady,
		state: state,
		has_qr: !!lastQrDataUrl,
		qr_at: lastQrAt,
		error: lastError || null,
	});
});

app.get('/qr', (req, res) => {
	if (!checkSecret(req, res)) {
		return;
	}
	res.json({
		success: true,
		ready: clientReady,
		state: state,
		has_qr: !!lastQrDataUrl,
		qr: lastQrDataUrl,
		qr_at: lastQrAt,
		error: lastError || null,
	});
});

app.post('/restart', (req, res) => {
	if (!checkSecret(req, res)) {
		return;
	}
	const clearSession = !!(req.body && (req.body.clear_session || req.body.clearSession));
	// Respond immediately so PHP (esp. built-in server) can keep polling /status + /qr.
	state = 'restarting';
	clientReady = false;
	lastQr = null;
	lastQrDataUrl = null;
	lastQrAt = null;
	lastError = '';
	markBootProgress();
	res.json({
		success: true,
		message: clearSession ? 'restarting_fresh_qr' : 'restarting',
		clear_session: clearSession,
		ready: false,
		state: 'restarting',
		has_qr: false,
		qr: null,
		qr_at: null,
	});
	const job = clearSession ? resetAndWaitForQr(45000) : restartBridge(false);
	Promise.resolve(job).catch((err) => {
		console.error('restart job failed:', err && err.message ? err.message : err);
		lastError = err && err.message ? err.message : 'restart_failed';
		state = 'disconnected';
	});
});

app.post('/reset-qr', (req, res) => {
	if (!checkSecret(req, res)) {
		return;
	}
	state = 'restarting';
	clientReady = false;
	lastQr = null;
	lastQrDataUrl = null;
	lastQrAt = null;
	lastError = '';
	markBootProgress();
	res.json({
		success: true,
		message: 'resetting',
		ready: false,
		state: 'restarting',
		has_qr: false,
		qr: null,
		qr_at: null,
	});
	resetAndWaitForQr(45000).catch((err) => {
		console.error('reset-qr job failed:', err && err.message ? err.message : err);
		lastError = err && err.message ? err.message : 'reset_qr_failed';
		state = 'disconnected';
	});
});

app.post('/send', async (req, res) => {
	if (!checkSecret(req, res)) {
		return;
	}
	if (!clientReady) {
		return res.status(503).json({
			success: false,
			message: 'not_ready',
			error: lastError || 'WhatsApp not connected. Scan QR in Customer Messages.',
		});
	}

	const phone = normalizePhone(req.body.phone);
	const message = String(req.body.message || '').trim();
	if (!phone || phone.length < 11) {
		return res.status(400).json({ success: false, message: 'invalid_phone' });
	}
	if (!message) {
		return res.status(400).json({ success: false, message: 'empty_message' });
	}

	try {
		const chatId = await resolveChatId(phone);
		const result = await client.sendMessage(chatId, message);
		lastError = '';
		res.json({
			success: true,
			message_id: result && result.id ? result.id.id : null,
		});
	} catch (err) {
		markSessionBroken(err);
		res.status(500).json({ success: false, message: 'send_failed', error: errText(err) });
	}
});

/**
 * Send a local file (PDF etc.) as a WhatsApp document.
 * Body: { phone, file_path, caption? } — file_path must be under allowed roots.
 */
app.post('/send-document', async (req, res) => {
	if (!checkSecret(req, res)) {
		return;
	}
	if (!clientReady) {
		return res.status(503).json({
			success: false,
			message: 'not_ready',
			error: lastError || 'WhatsApp not connected. Scan QR in Customer Messages.',
		});
	}

	const phone = normalizePhone(req.body.phone);
	const filePath = String(req.body.file_path || '').trim();
	const caption = String(req.body.caption || '').trim();
	if (!phone || phone.length < 11) {
		return res.status(400).json({ success: false, message: 'invalid_phone' });
	}
	if (!filePath) {
		return res.status(400).json({ success: false, message: 'missing_file' });
	}

	const resolved = path.resolve(filePath);
	const allowedRoots = [
		path.resolve(__dirname, '../../uploads'),
		path.resolve(__dirname, '../../uploads/reports'),
		path.resolve(__dirname, '../../uploads/orders'),
	];
	const allowed = allowedRoots.some((root) => resolved === root || resolved.startsWith(root + path.sep));
	if (!allowed || !fs.existsSync(resolved)) {
		return res.status(400).json({ success: false, message: 'invalid_file' });
	}

	try {
		const result = await sendDocumentToPhone(phone, resolved, caption);
		lastError = '';
		res.json({
			success: true,
			message_id: result && result.id ? (result.id.id || result.id._serialized || null) : null,
		});
	} catch (err) {
		const msg = errText(err);
		console.error('send-document failed:', msg);
		if (err && err.code === 'number_not_on_whatsapp') {
			return res.status(400).json({
				success: false,
				message: 'number_not_on_whatsapp',
				error: 'number_not_on_whatsapp',
			});
		}
		if (err && err.code === 'caption_sent_document_failed') {
			// Message delivered; PDF failed — surface clearly so ERP can retry PDF.
			return res.status(500).json({
				success: false,
				message: 'caption_sent_document_failed',
				error: msg,
				caption_sent: true,
			});
		}
		// Wid/LID memoize crashes often leave the session usable — don't trip circuit.
		if (!/detached Frame|Session closed|Target closed|Protocol error|Execution context was destroyed/i.test(msg)) {
			lastError = msg;
		} else {
			markSessionBroken(err);
		}
		res.status(500).json({ success: false, message: 'send_failed', error: msg });
	}
});

process.on('unhandledRejection', (err) => {
	console.error('Unhandled rejection:', err && err.message ? err.message : err);
});
process.on('uncaughtException', (err) => {
	console.error('Uncaught exception:', err && err.message ? err.message : err);
});

const server = app.listen(PORT, '127.0.0.1', () => {
	console.log('Darzi WhatsApp bridge listening on http://127.0.0.1:' + PORT);
});
server.on('error', (err) => {
	console.error('Bridge listen error:', err && err.message ? err.message : err);
	process.exit(1);
});
