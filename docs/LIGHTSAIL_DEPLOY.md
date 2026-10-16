# Darzibaba — Amazon Lightsail Deploy Guide

90-day free trial par Darzibaba + WhatsApp bridge online chalane ka step-by-step guide.

**Recommended plan:** Linux Ubuntu 22.04, **$12/mo (2 GB RAM)** — Mumbai (`ap-south-1`) ya Singapore.

---

## Part 1 — Lightsail instance

1. Open https://lightsail.aws.amazon.com
2. **Create instance**
   - Platform: **Linux/Unix**
   - Blueprint: **OS Only → Ubuntu 22.04 LTS**
   - Plan: **$12 USD — 2 GB RAM, 2 vCPU, 60 GB SSD** (free trial eligible)
   - Name: `darzibaba`
   - Region: **Mumbai** or **Singapore**
3. Instance create hone ke baad:
   - **Networking → IPv4 firewall:** allow **HTTP (80)**, **HTTPS (443)**, **SSH (22)**
   - **Port 3099 mat kholo** (WhatsApp bridge sirf localhost par)
4. **Account → SSH keys** se default key download karo (Mac/Linux par use hoga)

Static IP Lightsail bundle mein included hai — domain isi IP par point karni hai.

---

## Part 2 — SSH connect

Lightsail console se **Connect using SSH** ya apne Mac se:

```bash
ssh -i ~/Downloads/LightsailDefaultKey-ap-south-1.pem ubuntu@YOUR_LIGHTSAIL_IP
```

`YOUR_LIGHTSAIL_IP` = Lightsail dashboard par instance ka public IP.

---

## Part 3 — Server setup (copy-paste)

### 3.1 System update + swap (WhatsApp bridge ke liye helpful)

```bash
sudo apt update && sudo apt upgrade -y

sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

### 3.2 Nginx + MariaDB + PHP 7.4

Darzibaba abhi **PHP 7.4** par hai (`composer.json`).

```bash
sudo apt install -y nginx mariadb-server git unzip curl software-properties-common

sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y \
  php7.4-fpm php7.4-cli php7.4-mysqli php7.4-mbstring \
  php7.4-xml php7.4-gd php7.4-curl php7.4-zip php7.4-json
```

MariaDB secure setup:

```bash
sudo mysql -e "CREATE DATABASE alibaba CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'darzi'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';"
sudo mysql -e "GRANT ALL PRIVILEGES ON alibaba.* TO 'darzi'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

`STRONG_PASSWORD_HERE` ko apna strong password se replace karo.

### 3.3 Node.js 18 + PM2 (WhatsApp bridge)

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
sudo npm install -g pm2
```

### 3.4 Chromium dependencies (whatsapp-web.js / Puppeteer)

```bash
sudo apt install -y \
  ca-certificates fonts-liberation libasound2 libatk-bridge2.0-0 libatk1.0-0 \
  libc6 libcairo2 libcups2 libdbus-1-3 libdrm2 libexpat1 libfontconfig1 \
  libgbm1 libgcc1 libglib2.0-0 libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 \
  libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 \
  libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 \
  libxss1 libxtst6 wget xdg-utils
```

---

## Part 4 — App upload

### Option A — Git (recommended)

Server par:

```bash
sudo mkdir -p /var/www/darzibaba
sudo chown ubuntu:ubuntu /var/www/darzibaba
cd /var/www/darzibaba
git clone https://github.com/Aziz-io/DarziSoftware.git .
```

Agar repo private hai to deploy key ya token use karo.

### Option B — ZIP from Mac

Apne Mac par project folder se (`.env` exclude):

```bash
cd /Users/mac/Documents/aziz/Darzibaba
zip -r darzibaba.zip . -x ".git/*" -x ".env" -x "node_modules/*" -x "bridge/whatsapp-bridge/node_modules/*"
scp -i ~/Downloads/LightsailDefaultKey-ap-south-1.pem darzibaba.zip ubuntu@YOUR_LIGHTSAIL_IP:/tmp/
```

Server par:

```bash
sudo mkdir -p /var/www/darzibaba
sudo chown ubuntu:ubuntu /var/www/darzibaba
cd /var/www/darzibaba
unzip /tmp/darzibaba.zip
```

### Writable folders

```bash
cd /var/www/darzibaba
sudo chown -R www-data:www-data application/cache application/logs application/sessions 2>/dev/null || true
sudo chmod -R 775 application/cache application/logs application/sessions 2>/dev/null || true
sudo mkdir -p application/cache application/logs application/sessions
sudo chown -R www-data:www-data application/cache application/logs application/sessions
sudo chmod -R 775 application/cache application/logs application/sessions
```

---

## Part 5 — Database import

Apne **local Mac** se full dump banao:

```bash
mysqldump -h 127.0.0.1 -P 3310 -u root alibaba > alibaba_backup.sql
scp -i ~/Downloads/LightsailDefaultKey-ap-south-1.pem alibaba_backup.sql ubuntu@YOUR_LIGHTSAIL_IP:/tmp/
```

Server par import:

```bash
mysql -u darzi -p alibaba < /tmp/alibaba_backup.sql
```

Extra SQL patches (agar pehle se local par run nahi kiye):

```bash
cd /var/www/darzibaba
mysql -u darzi -p alibaba < application/sql/notification_automation.sql
mysql -u darzi -p alibaba < application/sql/notification_templates_professional.sql
mysql -u darzi -p alibaba < application/sql/rbac_schema.sql
```

Jo files pehle se DB mein hain unhe dubara run karne ki zaroorat nahi.

---

## Part 6 — `.env` file

```bash
cd /var/www/darzibaba
cp .env.example .env
nano .env
```

Production values:

```env
CI_ENV=production

DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=darzi
DB_PASSWORD=STRONG_PASSWORD_HERE
DB_NAME=alibaba

COOKIE_SECURE=true

WHATSAPP_BRIDGE_ENABLED=true
WHATSAPP_BRIDGE_URL=http://127.0.0.1:3099
WHATSAPP_BRIDGE_SECRET=your_random_secret_here

OLLAMA_ENABLED=false
```

Save: `Ctrl+O`, Enter, `Ctrl+X`

```bash
chmod 600 .env
```

---

## Part 7 — Nginx site config

```bash
sudo nano /etc/nginx/sites-available/darzibaba
```

Paste (domain replace karo):

```nginx
server {
    listen 80;
    server_name erp.yourdomain.com;
    root /var/www/darzibaba;
    index index.php;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(env|git|ht) {
        deny all;
    }

    location ~ ^/(system|application)/ {
        deny all;
    }
}
```

Enable:

```bash
sudo ln -sf /etc/nginx/sites-available/darzibaba /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Test (domain se pehle IP se):

```
http://YOUR_LIGHTSAIL_IP
```

---

## Part 8 — Domain + HTTPS

### DNS (apne domain registrar par)

| Type | Name | Value |
|------|------|-------|
| A | `erp` (ya `@`) | Lightsail public IP |

Propagation: 5 min – 24 hours.

### Free SSL (Let's Encrypt)

Domain DNS live hone ke baad:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d erp.yourdomain.com
```

Auto-renew check:

```bash
sudo certbot renew --dry-run
```

Ab open karo: `https://erp.yourdomain.com`

---

## Part 9 — WhatsApp bridge

```bash
cd /var/www/darzibaba/bridge/whatsapp-bridge
npm install
```

Secret set karo (same as `.env`):

```bash
export WHATSAPP_BRIDGE_SECRET=your_random_secret_here
pm2 start server.js --name whatsapp-bridge
pm2 save
pm2 startup
```

Jo command `pm2 startup` print kare use copy karke run karo (sudo wali line).

### QR scan

```bash
pm2 logs whatsapp-bridge
```

Terminal mein QR dikhega → shop WhatsApp → **Linked Devices** → scan.

Ready message:

```
WhatsApp bridge ready on port 3099
```

### App mein check

1. Login → **Customer Messages**
2. Dispatch mode: **Auto send (local WhatsApp bridge)**
3. Bridge status: connected / ready

---

## Part 10 — Final checklist

- [ ] Login page khulta hai
- [ ] Order save hota hai
- [ ] Reports chalte hain
- [ ] Customer Messages → bridge **ready**
- [ ] Test order par WhatsApp jata hai
- [ ] HTTPS lock icon browser mein
- [ ] `.env` public access se protected

---

## Part 11 — Backup (important)

Daily DB backup cron:

```bash
crontab -e
```

Add:

```cron
0 2 * * * mysqldump -u darzi -p'STRONG_PASSWORD_HERE' alibaba | gzip > /home/ubuntu/backups/alibaba-$(date +\%F).sql.gz
```

```bash
mkdir -p /home/ubuntu/backups
```

Trial se pehle backup download karo:

```bash
scp -i ~/Downloads/LightsailDefaultKey-ap-south-1.pem ubuntu@YOUR_LIGHTSAIL_IP:/home/ubuntu/backups/alibaba-*.sql.gz .
```

---

## Part 12 — 90 din ke baad

| Option | Action |
|--------|--------|
| Continue Lightsail | Kuch mat karo — ~$12/month charge |
| Band karna | Instance **Delete** karo (snapshot optional) |
| Apne PC par shift | DB dump + files copy → local server setup |

AWS Billing → **Budget alert** set karo ($5 threshold).

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 502 Bad Gateway | `sudo systemctl status php7.4-fpm nginx` |
| DB connection error | `.env` DB_PORT=3306, password check |
| Blank page | `application/logs/` check; temporarily `CI_ENV=development` |
| WhatsApp not_ready | `pm2 logs whatsapp-bridge` → QR scan |
| Bridge crash (RAM) | Swap enabled? `$12` plan use karo, `$5` mat |
| CSRF error on reports | Already fixed in codebase — latest code deploy karo |
| Permission denied cache | Step 4 writable folders dubara run karo |

---

## Quick command reference

```bash
# App logs
tail -f /var/www/darzibaba/application/logs/log-*.php

# Restart services
sudo systemctl restart php7.4-fpm nginx
pm2 restart whatsapp-bridge

# Bridge status (server par)
curl -s http://127.0.0.1:3099/status
```

---

*Darzibaba — Lightsail deploy guide*
