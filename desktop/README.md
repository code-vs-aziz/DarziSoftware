# Darzi Baba Desktop (same real ERP UI)

Yeh **Offline Desk / SPA nahi** hai. Yeh Electron app **wahi classic PHP ERP** kholti hai jo tum roz use karte ho.

## Kya solve hota hai

| Problem | Solution |
|---------|----------|
| Wi‑Fi / internet band | App local PHP + local MariaDB use karti hai — internet ki zaroorat nahi |
| PHP server band bhool jana | App start hote hi `php -S 127.0.0.1:8080` khud chalati hai |
| Alag / ajeeb offline UI | Nahi — **same ERP screens** |

## Zaruri shartein

1. **MariaDB local** honi chahiye (`DB_HOST=127.0.0.1` — tumhare `.env` jaisa)
2. Computer pe **PHP** installed (`php` CLI)
3. Pehli dafa DB / ERP pehle se setup ho

Agar database cloud pe hai aur internet band hai, to koi bhi app full ERP nahi chala sakti — data local hona lazmi hai.

## Chalana (dev)

```bash
cd desktop
npm install
npm start
```

Window mein **Login / Welcome** — bilkul real ERP.

### Windows one-click

1. Pehle PC pe **PHP + Node.js + local MariaDB** install karo, `.env` set karo.
2. Double-click:
   - `start-darzi-baba.bat` (project root pe bhi shortcut hai)
   - ya `desktop\start-darzi-baba.bat`
3. EXE banane ke liye: `desktop\build-windows-exe.bat`  
   Output: `desktop\dist\`

## Desktop icon / install

```bash
cd desktop
npm run dist
```

`desktop/dist/` mein Mac `.dmg` / Windows installer banega (OS ke hisaab se).

## Offline Desk vs Desktop

| | Offline Desk (`offline-app.html`) | Desktop Electron |
|--|----------------------------------|------------------|
| UI | Alag simple forms | **Same real ERP** |
| PHP | Band ho to bhi chal sakti | App PHP ko auto start karti hai |
| Use case | Emergency lightweight | Roz ka shop software |

Shop ke liye recommended: **Desktop Electron**.
