# Art Gallery - Deployment Guide

This guide outlines the steps to deploy the Art Gallery application to production.

## Prerequisites

- PHP 8.4+
- Composer
- Node.js & NPM
- MySQL 8.0+ or compatible database
- Web server (Apache/Nginx)
- SSL certificate (recommended)

## Environment Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd gallery
```

### 2. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 3. Configure Environment

Copy the example environment file and update it:

```bash
cp .env.example .env
```

Update the following variables in `.env`:

```env
APP_NAME="Art Gallery"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database

# For production, consider using 's3' for image storage
FILESYSTEM_DISK=local
# If using S3:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your-key
# AWS_SECRET_ACCESS_KEY=your-secret
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your-bucket-name

MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate --force
```

### 6. Create Admin User

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Important:** Change the default admin password immediately after first login.

### 7. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

### 8. Set Permissions

Ensure the web server has write permissions to:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Generate Sitemap

```bash
php artisan sitemap:generate
```

## Web Server Configuration

### Nginx Example

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com;
    root /var/www/gallery/public;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Maximum upload size for artwork images (10MB)
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache Example

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/gallery/public

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /var/www/gallery/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Maximum upload size for artwork images (10MB)
    php_value upload_max_filesize 10M
    php_value post_max_size 10M

    ErrorLog ${APACHE_LOG_DIR}/gallery-error.log
    CustomLog ${APACHE_LOG_DIR}/gallery-access.log combined
</VirtualHost>
```

## Image Storage Configuration

### Local Storage (Default)

Images are stored in `storage/app/public`. Create a symbolic link:

```bash
php artisan storage:link
```

### S3 Storage (Recommended for Production)

1. Update `.env`:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

2. Ensure the S3 bucket has appropriate CORS configuration:
```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "HEAD"],
        "AllowedOrigins": ["https://yourdomain.com"],
        "ExposeHeaders": ["ETag"]
    }
]
```

3. Set bucket policy for public read access to media files.

## Queue Worker Setup

For background job processing, set up a queue worker:

### Supervisor Configuration

```ini
[program:gallery-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gallery/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/gallery/storage/logs/worker.log
stopwaitsecs=3600
```

## Scheduled Tasks

Add to crontab for the web server user:

```bash
* * * * * cd /var/www/gallery && php artisan schedule:run >> /dev/null 2>&1
```

Add to your scheduler (in `routes/console.php` or `app/Console/Kernel.php`):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('sitemap:generate')->daily();
```

## Security Checklist

- [ ] APP_DEBUG is set to `false`
- [ ] APP_ENV is set to `production`
- [ ] Strong database password is set
- [ ] SSL certificate is installed and configured
- [ ] Admin password has been changed from default
- [ ] File permissions are correctly set (755 for directories, 644 for files)
- [ ] `.env` file is not publicly accessible
- [ ] All caches are cleared and optimized
- [ ] Firewall rules are configured
- [ ] Backup strategy is implemented

## Maintenance

### Updating the Application

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# Regenerate sitemap
php artisan sitemap:generate

# Restart queue workers if running
php artisan queue:restart
```

### Backup Strategy

Regularly backup:
- Database: `mysqldump -u username -p database_name > backup.sql`
- Uploaded images: `storage/app/public/` (or S3 bucket)
- Environment configuration: `.env` file

## Monitoring

- Monitor application logs: `storage/logs/laravel.log`
- Monitor queue worker logs if applicable
- Set up application monitoring (e.g., Laravel Telescope, Sentry)
- Monitor disk space for image storage
- Set up uptime monitoring

## Support

For issues or questions, refer to:
- Laravel Documentation: https://laravel.com/docs
- Filament Documentation: https://filamentphp.com/docs
- Spatie Media Library: https://spatie.be/docs/laravel-medialibrary
