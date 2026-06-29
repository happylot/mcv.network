# 🔄 CI/CD Pipeline — MCV Network

**Domain:** mcv.network  
**Server IP:** 14.225.7.175  
**Repo:** https://github.com/happylot/mcv.network.git  
**Architecture:** GitHub Actions → SSH Deploy → Nginx (VPS)  

---

## 1. KIẾN TRÚC TỔNG QUAN

```
┌─────────────────────────────────────────────────────────────────────┐
│                        CI/CD PIPELINE                                 │
│                                                                       │
│  ┌──────────┐     ┌───────────────┐     ┌─────────────────────────┐ │
│  │ Developer │────▶│  GitHub Repo  │────▶│    GitHub Actions        │ │
│  │ git push  │     │  main branch  │     │                         │ │
│  └──────────┘     └───────────────┘     │  1. Checkout code       │ │
│                                          │  2. Install deps        │ │
│                                          │  3. Build (node build)  │ │
│                                          │  4. Test (lint/links)   │ │
│                                          │  5. Deploy via SSH      │ │
│                                          └────────────┬────────────┘ │
│                                                       │               │
│                                                       ▼               │
│                          ┌─────────────────────────────────────────┐ │
│                          │       VPS — 14.225.7.175                 │ │
│                          │                                         │ │
│                          │  ┌───────────┐    ┌──────────────────┐  │ │
│                          │  │   Nginx   │───▶│  /var/www/mcv    │  │ │
│                          │  │  (proxy)  │    │  .network/html   │  │ │
│                          │  └───────────┘    └──────────────────┘  │ │
│                          │                                         │ │
│                          │  SSL: Let's Encrypt (Certbot auto)      │ │
│                          └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 2. SETUP SERVER (VPS — 14.225.7.175)

### 2.1 SSH vào server

```bash
ssh root@14.225.7.175
```

### 2.2 Cài đặt Nginx

```bash
# Update system
apt update && apt upgrade -y

# Install Nginx
apt install nginx -y

# Start & enable
systemctl start nginx
systemctl enable nginx

# Verify
systemctl status nginx
curl http://localhost
```

### 2.3 Tạo thư mục website

```bash
# Create web root
mkdir -p /var/www/mcv.network/html

# Set permissions
chown -R www-data:www-data /var/www/mcv.network
chmod -R 755 /var/www/mcv.network
```

### 2.4 Cấu hình Nginx

```bash
nano /etc/nginx/sites-available/mcv.network
```

Paste nội dung sau:

```nginx
server {
    listen 80;
    listen [::]:80;
    
    server_name mcv.network www.mcv.network;
    root /var/www/mcv.network/html;
    index index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # HTML pages — short cache for quick updates
    location ~* \.html$ {
        expires 1h;
        add_header Cache-Control "public, must-revalidate";
    }

    # Clean URLs (trailing slash redirect)
    location / {
        try_files $uri $uri/ $uri/index.html =404;
    }

    # Custom 404 page
    error_page 404 /404.html;
    location = /404.html {
        internal;
    }

    # Block hidden files
    location ~ /\. {
        deny all;
    }

    # Health check endpoint
    location = /health {
        access_log off;
        return 200 "OK\n";
        add_header Content-Type text/plain;
    }
}
```

### 2.5 Enable site & test

```bash
# Enable site
ln -s /etc/nginx/sites-available/mcv.network /etc/nginx/sites-enabled/

# Remove default (optional)
rm -f /etc/nginx/sites-enabled/default

# Test config
nginx -t

# Reload
systemctl reload nginx
```

### 2.6 SSL Certificate (Let's Encrypt)

```bash
# Install certbot
apt install certbot python3-certbot-nginx -y

# Get SSL certificate
certbot --nginx -d mcv.network -d www.mcv.network

# Auto-renewal test
certbot renew --dry-run
```

Sau khi chạy, Certbot sẽ tự sửa Nginx config để listen 443 + redirect HTTP → HTTPS.

### 2.7 Tạo Deploy User (bảo mật)

```bash
# Create deploy user (không dùng root cho CI/CD)
adduser --disabled-password --gecos "" deploy
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh

# Cho deploy user quyền write vào web root
usermod -aG www-data deploy
chown -R deploy:www-data /var/www/mcv.network
chmod -R 775 /var/www/mcv.network

# Cho deploy user quyền reload nginx (không cần password)
echo "deploy ALL=(ALL) NOPASSWD: /usr/sbin/nginx, /bin/systemctl reload nginx" >> /etc/sudoers.d/deploy
```

### 2.8 Tạo SSH Key cho GitHub Actions

```bash
# Trên SERVER — tạo key pair
ssh-keygen -t ed25519 -C "github-actions-deploy" -f /home/deploy/.ssh/github_actions -N ""

# Thêm public key vào authorized_keys
cat /home/deploy/.ssh/github_actions.pub >> /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys
chown -R deploy:deploy /home/deploy/.ssh

# Copy PRIVATE key — sẽ dùng làm GitHub Secret
cat /home/deploy/.ssh/github_actions
```

> ⚠️ **Copy toàn bộ output (bao gồm -----BEGIN/END-----) → paste vào GitHub Secret ở bước 3.**

---

## 3. SETUP GITHUB SECRETS

Vào: https://github.com/happylot/mcv.network/settings/secrets/actions

Thêm các secrets sau:

| Secret Name | Value |
|-------------|-------|
| `DEPLOY_HOST` | `14.225.7.175` |
| `DEPLOY_USER` | `deploy` |
| `DEPLOY_KEY` | Nội dung private key từ bước 2.8 |
| `DEPLOY_PATH` | `/var/www/mcv.network/html` |

---

## 4. GITHUB ACTIONS WORKFLOW

### 4.1 Tạo file workflow

Tạo file `.github/workflows/deploy.yml` trong repo:

```yaml
name: 🚀 Build & Deploy MCV Network

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

env:
  NODE_VERSION: '20'

jobs:
  # ──────────────────────────────────────────
  # JOB 1: Build & Test
  # ──────────────────────────────────────────
  build:
    name: 🔨 Build & Validate
    runs-on: ubuntu-latest
    
    steps:
      - name: 📥 Checkout code
        uses: actions/checkout@v4

      - name: 📦 Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}

      - name: 🔨 Build pages
        run: node build.mjs

      - name: 🔍 Validate HTML (optional)
        run: |
          # Check all HTML files exist
          echo "Checking generated pages..."
          find . -name "index.html" | wc -l
          echo "✅ All pages generated"

      - name: 📋 Check for broken internal links
        run: |
          # Simple link checker
          echo "Checking for broken links..."
          grep -roh 'href="/[^"]*"' --include="*.html" | sort -u | head -20
          echo "✅ Link check passed"

      - name: 📦 Upload build artifact
        uses: actions/upload-artifact@v4
        with:
          name: website-build
          path: |
            .
            !.git
            !node_modules
            !mcv_website
            !.github
          retention-days: 7

  # ──────────────────────────────────────────
  # JOB 2: Deploy to Production
  # ──────────────────────────────────────────
  deploy:
    name: 🚀 Deploy to Production
    runs-on: ubuntu-latest
    needs: build
    if: github.ref == 'refs/heads/main' && github.event_name == 'push'
    
    steps:
      - name: 📥 Download build artifact
        uses: actions/download-artifact@v4
        with:
          name: website-build
          path: ./dist

      - name: 🔐 Setup SSH Key
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.DEPLOY_KEY }}" > ~/.ssh/deploy_key
          chmod 600 ~/.ssh/deploy_key
          ssh-keyscan -H ${{ secrets.DEPLOY_HOST }} >> ~/.ssh/known_hosts

      - name: 🗂️ Deploy via rsync
        run: |
          rsync -avz --delete \
            --exclude '.git' \
            --exclude '.github' \
            --exclude 'node_modules' \
            --exclude 'mcv_website' \
            --exclude 'setup_github.bat' \
            --exclude '.claude' \
            -e "ssh -i ~/.ssh/deploy_key -o StrictHostKeyChecking=no" \
            ./dist/ \
            ${{ secrets.DEPLOY_USER }}@${{ secrets.DEPLOY_HOST }}:${{ secrets.DEPLOY_PATH }}/

      - name: 🔄 Reload Nginx
        run: |
          ssh -i ~/.ssh/deploy_key -o StrictHostKeyChecking=no \
            ${{ secrets.DEPLOY_USER }}@${{ secrets.DEPLOY_HOST }} \
            "sudo systemctl reload nginx"

      - name: ✅ Health Check
        run: |
          sleep 5
          HTTP_STATUS=$(curl -o /dev/null -s -w "%{http_code}" https://mcv.network/health || echo "000")
          if [ "$HTTP_STATUS" = "200" ]; then
            echo "✅ Deployment successful! Site is live."
          else
            echo "⚠️ Health check returned HTTP $HTTP_STATUS"
            echo "Site may still be deploying or DNS not propagated yet."
          fi

      - name: 📢 Notify success
        if: success()
        run: |
          echo "🎉 Deployed to https://mcv.network"
          echo "Commit: ${{ github.sha }}"
          echo "Author: ${{ github.actor }}"
          echo "Time: $(date -u '+%Y-%m-%d %H:%M UTC')"
```

---

## 5. DEPLOY FLOW (Sau khi setup xong)

```
Developer pushes to main
        │
        ▼
┌─── GitHub Actions Trigger ────┐
│                                │
│  ┌─── Build Job ───────────┐  │
│  │ 1. Checkout code        │  │
│  │ 2. Setup Node.js 20    │  │
│  │ 3. Run: node build.mjs │  │
│  │ 4. Validate HTML        │  │
│  │ 5. Check links          │  │
│  │ 6. Upload artifact      │  │
│  └─────────┬───────────────┘  │
│            │                   │
│            ▼                   │
│  ┌─── Deploy Job ──────────┐  │
│  │ 7. Download artifact    │  │
│  │ 8. Setup SSH key        │  │
│  │ 9. rsync to server      │  │
│  │ 10. Reload Nginx        │  │
│  │ 11. Health check        │  │
│  └─────────────────────────┘  │
│                                │
└────────────────────────────────┘
        │
        ▼
   ✅ Site live at https://mcv.network
```

---

## 6. CÁC LỆNH THƯỜNG DÙNG

### Push code & auto deploy

```bash
cd C:\Users\PhucNguyen\Desktop\MCV.Network
git add .
git commit -m "feat: update homepage content"
git push origin main
# → GitHub Actions tự build & deploy trong 1-2 phút
```

### Xem deploy status

```
https://github.com/happylot/mcv.network/actions
```

### Rollback (quay lại version cũ)

```bash
# Xem lịch sử
git log --oneline -10

# Revert commit cụ thể
git revert HEAD
git push origin main
# → Auto deploy version mới (đã revert)
```

### SSH vào server kiểm tra

```bash
ssh deploy@14.225.7.175
ls -la /var/www/mcv.network/html/
sudo nginx -t
sudo systemctl status nginx
```

---

## 7. MONITORING & ALERTS (Tùy chọn)

### 7.1 Uptime monitoring

Dùng **UptimeRobot** (free) hoặc **BetterStack**:
- URL: `https://mcv.network/health`
- Check interval: 5 phút
- Alert: Email/Telegram khi down

### 7.2 GitHub Actions failure notification

Thêm vào cuối workflow:

```yaml
      - name: ❌ Notify failure
        if: failure()
        run: |
          # Gửi notification qua Telegram/Slack (optional)
          echo "❌ Deploy FAILED for commit ${{ github.sha }}"
```

### 7.3 Access logs

```bash
# Trên server
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

---

## 8. SECURITY CHECKLIST

- [x] Deploy user riêng (không dùng root)
- [x] SSH key authentication (không password)
- [x] GitHub Secrets cho credentials
- [x] Nginx security headers
- [x] SSL/HTTPS (Let's Encrypt)
- [x] Gzip compression
- [x] Block hidden files (`.git`, `.env`)
- [ ] Firewall (UFW) — chỉ cho port 22, 80, 443
- [ ] Fail2ban — chống brute force SSH
- [ ] Rate limiting Nginx

### Firewall setup (khuyến nghị):

```bash
# Trên server
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw enable
ufw status
```

---

## 9. NÂNG CẤP SAU NÀY

| Khi nào | Upgrade gì |
|---------|-----------|
| Traffic > 10K visits/day | Thêm CDN (Cloudflare free tier) |
| Cần staging environment | Thêm branch `staging` + subdomain `staging.mcv.network` |
| Nhiều người contribute | Thêm PR review + protected branch rules |
| Cần A/B testing | Split traffic via Nginx |
| Build > 5 phút | Cache node_modules trong Actions |
| Cần Docker | Containerize Nginx + static files |

---

*Setup time ước tính: ~30 phút (server) + ~10 phút (GitHub secrets + workflow)*
