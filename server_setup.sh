#!/bin/bash
# ==========================================
# MCV Network — Server Setup Script
# Run on: 14.225.7.175 (as root)
# ==========================================

set -e
echo "🚀 Setting up MCV Network server..."

# 1. Update system
echo ""
echo "📦 [1/8] Updating system..."
apt update && apt upgrade -y

# 2. Install required packages
echo ""
echo "📦 [2/8] Installing Nginx, Certbot, rsync..."
apt install -y nginx certbot python3-certbot-nginx rsync ufw

# 3. Create deploy user
echo ""
echo "👤 [3/8] Creating deploy user..."
if id "deploy" &>/dev/null; then
    echo "   User 'deploy' already exists, skipping..."
else
    adduser --disabled-password --gecos "" deploy
fi
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh

# 4. Create web directory
echo ""
echo "📁 [4/8] Creating web root..."
mkdir -p /var/www/mcv.network/html
chown -R deploy:www-data /var/www/mcv.network
chmod -R 775 /var/www/mcv.network

# 5. Generate SSH key for GitHub Actions
echo ""
echo "🔑 [5/8] Generating SSH key for CI/CD..."
if [ ! -f /home/deploy/.ssh/github_actions ]; then
    ssh-keygen -t ed25519 -C "github-actions-deploy" -f /home/deploy/.ssh/github_actions -N ""
    cat /home/deploy/.ssh/github_actions.pub >> /home/deploy/.ssh/authorized_keys
    chmod 600 /home/deploy/.ssh/authorized_keys
    chown -R deploy:deploy /home/deploy/.ssh
    echo ""
    echo "   ⚠️  IMPORTANT: Copy this PRIVATE KEY to GitHub Secrets (DEPLOY_KEY):"
    echo "   ────────────────────────────────────────────────────────"
    cat /home/deploy/.ssh/github_actions
    echo ""
    echo "   ────────────────────────────────────────────────────────"
else
    echo "   SSH key already exists, skipping..."
fi

# 6. Configure Nginx
echo ""
echo "🌐 [6/8] Configuring Nginx..."
cat > /etc/nginx/sites-available/mcv.network << 'NGINX'
server {
    listen 80;
    listen [::]:80;
    
    server_name mcv.network www.mcv.network;
    root /var/www/mcv.network/html;
    index index.html;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location ~* \.html$ {
        expires 1h;
        add_header Cache-Control "public, must-revalidate";
    }

    location / {
        try_files $uri $uri/ $uri/index.html =404;
    }

    location = /health {
        access_log off;
        return 200 "OK\n";
        add_header Content-Type text/plain;
    }

    location ~ /\. {
        deny all;
    }

    location ~* (build\.mjs|serve\.mjs|setup_github\.bat|\.md$) {
        deny all;
    }

    error_page 404 /404.html;
}
NGINX

# Enable site
ln -sf /etc/nginx/sites-available/mcv.network /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test & reload
nginx -t
systemctl reload nginx
echo "   ✅ Nginx configured"

# 7. Setup firewall
echo ""
echo "🔒 [7/8] Configuring firewall (UFW)..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
echo "   ✅ Firewall enabled (SSH + HTTP + HTTPS)"

# 8. Sudoers for deploy user
echo ""
echo "⚙️  [8/8] Setting sudo permissions for deploy..."
echo "deploy ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx" > /etc/sudoers.d/deploy
chmod 440 /etc/sudoers.d/deploy
echo "   ✅ deploy user can reload nginx without password"

# 9. Create placeholder index
echo ""
echo "📄 Creating placeholder page..."
cat > /var/www/mcv.network/html/index.html << 'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>MCV Network — Coming Soon</title>
<style>
body{margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#0D1B2E;font-family:Inter,sans-serif;color:#fff}
.container{text-align:center}
h1{font-size:48px;font-weight:900;margin-bottom:8px}
h1 span{color:#38C0B8}
p{color:rgba(255,255,255,0.6);font-size:16px}
</style>
</head>
<body><div class="container"><h1>MCV<span>.</span>Network</h1><p>Performance Advertising at Scale — Coming Soon</p></div></body>
</html>
HTML

echo ""
echo "=========================================="
echo "✅ SERVER SETUP COMPLETE!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "  1. Copy the PRIVATE KEY above → GitHub Secrets (DEPLOY_KEY)"
echo "  2. Add GitHub Secrets: DEPLOY_HOST=14.225.7.175, DEPLOY_USER=deploy, DEPLOY_PATH=/var/www/mcv.network/html"
echo "  3. Push code to main branch → auto deploy"
echo "  4. Run: certbot --nginx -d mcv.network -d www.mcv.network (for SSL)"
echo ""
echo "Test: curl http://14.225.7.175/health"
echo ""
