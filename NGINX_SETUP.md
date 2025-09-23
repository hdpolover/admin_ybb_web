# Nginx Configuration for YBB Admin

This document explains how to configure Nginx to serve your YBB Admin application.

## 1. Create Nginx Server Block

Create the main application configuration:

```bash
# SSH to your server
ssh root@31.97.111.96

# Create Nginx configuration for the main app
nano /etc/nginx/sites-available/ybb-admin-prod.conf
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name admin.ybbfoundation.com ybbfoundation.com www.ybbfoundation.com;
    
    root /srv/apps/ybb-admin/prod/current/public;
    index index.php index.html index.htm;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript application/json;
    
    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        
        # Use the correct PHP-FPM socket (adjust version as needed)
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        
        # Or if using TCP:
        # fastcgi_pass 127.0.0.1:9000;
        
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\.ht {
        deny all;
    }
    
    location ~ /\.env {
        deny all;
    }
    
    location ~ /app/ {
        deny all;
    }
    
    location ~ /system/ {
        deny all;
    }
    
    location ~ /writable/ {
        deny all;
    }
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Logs
    access_log /var/log/ybb-admin/prod/nginx.access.log;
    error_log /var/log/ybb-admin/prod/nginx.error.log;
}
```

## 2. Create Storage Server Configuration

Create configuration for storage.ybbfoundation.com:

```bash
nano /etc/nginx/sites-available/ybb-storage.conf
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name storage.ybbfoundation.com;
    
    root /srv/storage/ybb-storage;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    
    # Serve uploaded files
    location /uploads {
        alias /srv/storage/ybb-storage/uploads;
        expires 1y;
        add_header Cache-Control "public, immutable";
        
        # Allow CORS for uploads
        add_header Access-Control-Allow-Origin "*";
        add_header Access-Control-Allow-Methods "GET, POST, OPTIONS";
        add_header Access-Control-Allow-Headers "DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range";
    }
    
    # Serve static assets
    location /assets {
        alias /srv/storage/ybb-storage/assets;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to writable directory
    location /writable {
        deny all;
        return 404;
    }
    
    # Default location
    location / {
        return 404;
    }
    
    # Logs
    access_log /var/log/nginx/ybb-storage.access.log;
    error_log /var/log/nginx/ybb-storage.error.log;
}
```

## 3. Enable Sites and Create Directories

```bash
# Create log directories
mkdir -p /var/log/ybb-admin/prod

# Enable the sites
ln -s /etc/nginx/sites-available/ybb-admin-prod.conf /etc/nginx/sites-enabled/
ln -s /etc/nginx/sites-available/ybb-storage.conf /etc/nginx/sites-enabled/

# Test Nginx configuration
nginx -t

# If test passes, reload Nginx
systemctl reload nginx
```

## 4. Install and Configure PHP-FPM

```bash
# Check which PHP version is installed
php --version

# Install and configure PHP-FPM for the detected version
# For PHP 8.4:
apt install -y php8.4-fpm

# Or for PHP 8.3:
# apt install -y php8.3-fpm

# Start and enable PHP-FPM
systemctl start php8.4-fpm
systemctl enable php8.4-fpm

# Check if it's running
systemctl status php8.4-fpm
```

## 5. Set Up SSL (Optional but Recommended)

Install Certbot for free SSL certificates:

```bash
# Install Certbot
apt install -y certbot python3-certbot-nginx

# Get SSL certificates for your domains
certbot --nginx -d ybbfoundation.com -d www.ybbfoundation.com -d admin.ybbfoundation.com -d storage.ybbfoundation.com

# Test auto-renewal
certbot renew --dry-run
```

## 6. Verify Setup

```bash
# Check if Nginx is running
systemctl status nginx

# Check if PHP-FPM is running
systemctl status php8.4-fpm

# Check Nginx error logs if there are issues
tail -f /var/log/nginx/error.log
tail -f /var/log/ybb-admin/prod/nginx.error.log
```

## 7. Test Your Site

After completing the setup:

1. **Main site**: https://app.ybbfoundation.com (with SSL auto-redirect)
2. **Storage**: http://storage.ybbfoundation.com (can be configured with SSL later)
3. **HTTP access**: Automatically redirects to HTTPS

## Troubleshooting

### If you get "502 Bad Gateway":
```bash
# Check PHP-FPM status
systemctl status php8.4-fpm

# Check PHP-FPM socket exists
ls -la /var/run/php/php8.4-fpm.sock

# Check Nginx error logs
tail -f /var/log/nginx/error.log
```

### If you get "File not found":
```bash
# Make sure the current symlink exists and points to a valid release
ls -la /srv/apps/ybb-admin/prod/current

# Check if index.php exists
ls -la /srv/apps/ybb-admin/prod/current/public/index.php
```

### If CSS/JS files don't load:
```bash
# Check file permissions
ls -la /srv/apps/ybb-admin/prod/current/public/assets/

# Make sure www-data can read the files
chown -R www-data:www-data /srv/apps/ybb-admin/prod/current/
```

This should get your domain working! The key steps are:
1. Create Nginx configuration
2. Enable the site
3. Make sure PHP-FPM is running
4. Test the configuration