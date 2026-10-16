# Darzi WhatsApp Bridge (FREE)

Shop ka apna WhatsApp use karo — **koi Cloud API / payment nahi**.

## Preferred: software UI (no terminal)

1. App → **Customer Messages**
2. Panel **WhatsApp bridge (QR & restart)**
3. **Start bridge** (agar band ho)
4. Screen par **QR** aayega → phone se scan
5. Session toot jaye → **Restart + new QR** → naya QR scan

Terminal optional hai — pehli dafa sirf `npm install` ke liye.

## Setup (ek dafa)

```bash
cd bridge/whatsapp-bridge
npm install
```

PHP se Start button Node process launch karta hai. Manual bhi chal sakta hai:

```bash
npm start
```

## App config

Root `.env` mein (already in `.env.example`):

```
WHATSAPP_BRIDGE_ENABLED=true
WHATSAPP_BRIDGE_URL=http://127.0.0.1:3099
WHATSAPP_BRIDGE_SECRET=
```

**Customer Messages** → Dispatch mode: **Auto send (local WhatsApp bridge)**

## HTTP API (bridge)

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/status` | ready / state / has_qr |
| GET | `/qr` | QR PNG as data URL |
| POST | `/restart` | `{ "clear_session": true }` → fresh QR |
| POST | `/send` | text message |
| POST | `/send-document` | PDF etc. |

## Order book flow

1. Order save
2. Print slip open
3. Background mein customer ko WhatsApp **auto bhej** (bridge se)

## Security

Optional: set `WHATSAPP_BRIDGE_SECRET` in `.env` (and same for Node env). Bridge listens on `127.0.0.1` only.

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Bridge OFF | Customer Messages → Start bridge |
| Waiting / QR | Scan QR in the same page |
| Session broken | Restart + new QR |
| node_not_found | Install Node.js, then Start again |
| bridge_unreachable | Start bridge; check port 3099 |
| missing_phone | Customer profile mein phone add karo |
