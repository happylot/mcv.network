# Control Plane Deployment Notes

Target domain: `ads.mcv.network`

The control plane is a Laravel application under `apps/control-plane`. It should be deployed separately from the static marketing site on `mcv.network`.

## Runtime

- Nginx
- PHP-FPM 8.4.1+
- PostgreSQL
- Redis
- Supervisor or systemd workers for queues
- Node.js 22.12.0+ for asset builds

## Suggested Directory Layout

```text
/var/www/mcv.network/current
  apps/control-plane
```

Laravel public root:

```text
/var/www/mcv.network/current/apps/control-plane/public
```

## Nginx Server Block

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name ads.mcv.network;
    root /var/www/mcv.network/current/apps/control-plane/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Use Certbot to add HTTPS:

```bash
certbot --nginx -d ads.mcv.network
```

## Deploy Steps

```bash
cd /var/www/mcv.network/current/apps/control-plane
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Make sure these paths are writable by the PHP user:

```text
storage
bootstrap/cache
```

## Queue Worker

Example Supervisor program:

```ini
[program:mcv-control-plane-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mcv.network/current/apps/control-plane/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/mcv.network/current/apps/control-plane/storage/logs/worker.log
stopwaitsecs=3600
```

Reload workers after each deploy:

```bash
php artisan queue:restart
```

## Production Environment Checklist

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://ads.mcv.network`
- `DB_CONNECTION=pgsql`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`
- `CACHE_STORE=redis`
- `MAIL_MAILER` set to a real provider
- Google OAuth client ID/secret configured as server secrets
- scheduler installed if scheduled jobs are added:

```cron
* * * * * cd /var/www/mcv.network/current/apps/control-plane && php artisan schedule:run >> /dev/null 2>&1
```
