# GitHub Actions Secrets Setup

This document explains how to configure the required GitHub Secrets for automated deployment of YBB Admin to your production server.

## Required Secrets

You need to add the following secrets to your GitHub repository:

### 1. `DEPLOY_SSH_KEY`
**Description**: The private SSH key for connecting to your production server
**Value**: Your private SSH key content (the `deploy_key` file, not the `.pub` file)

**How to get it:**
```bash
# On your local machine, copy the private key content
cat ~/.ssh/deploy_key
```

Copy the entire output including the header and footer:
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAFwAAAAdzc2gtcn
...
-----END OPENSSH PRIVATE KEY-----
```

### 2. `SERVER_HOST`
**Description**: Your production server's IP address or domain name
**Value**: `31.97.111.96` (your VPS IP)

### 3. `SERVER_USER`
**Description**: The user account for SSH connection
**Value**: `root` (since we're using root user for deployment)

## Environment Configuration Setup

Your production `.env` file needs to be configured on the server **before** running deployments, since it contains sensitive data and is not stored in Git.

### Create Production .env File

1. **SSH to your server:**
   ```bash
   ssh root@31.97.111.96
   ```

2. **Create the production .env file:**
   ```bash
   # Create the config directory if it doesn't exist
   mkdir -p /etc/ybb-admin/prod
   
   # Create the production .env file
   nano /etc/ybb-admin/prod/.env
   ```

3. **Add your production configuration:**
   ```env
   # Basic Config
   CI_ENVIRONMENT = production
   app.baseURL = 'https://admin.ybbfoundation.com'
   app.forceGlobalSecureRequests = true
   
   # Database Configuration
   database.default.hostname = localhost
   database.default.database = ybb_prod
   database.default.username = ybb_user
   database.default.password = your_secure_database_password
   database.default.DBDriver = MySQLi
   database.default.charset = utf8mb4
   database.default.DBCollat = utf8mb4_unicode_ci
   
   # Storage Configuration
   app.storageURL = 'https://storage.ybbfoundation.com'
   app.uploadPath = '/srv/storage/ybb-storage/uploads'
   
   # Caching Configuration
   cache.redis.host = 127.0.0.1
   cache.redis.port = 6379
   cache.redis.database = 0
   
   # YBB Export API
   YBB_EXPORT_API_URL = 'https://your-export-api.railway.app'
   
   # Security Keys (generate new ones for production)
   encryption.key = your_32_character_encryption_key_here
   
   # Session Configuration
   app.sessionDriver = redis
   app.sessionCookieName = ybb_session
   app.sessionExpiration = 7200
   
   # Email Configuration (if needed)
   email.fromEmail = noreply@ybbfoundation.com
   email.fromName = 'YBB Foundation'
   
   # Logging
   log.threshold = 4
   ```

4. **Set proper permissions:**
   ```bash
   # Secure the .env file
   chmod 600 /etc/ybb-admin/prod/.env
   chown root:root /etc/ybb-admin/prod/.env
   ```

### Create Storage Directories

You also need to set up the storage structure:

```bash
# Create storage directories
mkdir -p /srv/storage/ybb-storage/{uploads,assets,writable}
mkdir -p /srv/storage/ybb-storage/writable/{cache,logs,session,debugbar}

# Set proper permissions
chown -R www-data:www-data /srv/storage/ybb-storage
chmod -R 755 /srv/storage/ybb-storage
chmod -R 775 /srv/storage/ybb-storage/writable
```

### Install Composer

Composer is required for dependency management:

```bash
# Download and install Composer
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Verify installation
composer --version
```

### Install Required PHP Extensions

Make sure all required PHP extensions are installed:

```bash
# Update package lists
apt update

# Add Ondřej Surý PPA for PHP 8.2
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.2 and required extensions (corrected package names)
apt install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
php8.2-intl php8.2-gd php8.2-zip php8.2-curl php8.2-redis php8.2-common

# Restart PHP-FPM
systemctl restart php8.2-fpm
systemctl enable php8.2-fpm

# Verify PHP installation
php --version
php -m | grep -E "(mysql|mbstring|gd|zip|curl)"
```

### Database Setup

Make sure your production database is configured:

```bash
# Connect to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE ybb_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ybb_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON ybb_prod.* TO 'ybb_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Important Notes:

1. **Never commit .env to Git** - It should always be in your `.gitignore`
2. **Use strong passwords** - Generate secure database passwords
3. **Generate new encryption key** - Don't use development keys in production
4. **Configure Redis** - Make sure Redis is running for caching and sessions
5. **SSL Configuration** - Set `forceGlobalSecureRequests = true` for HTTPS

## How to Add Secrets to GitHub

1. **Go to your repository on GitHub**
2. **Click on "Settings" tab**
3. **In the left sidebar, click "Secrets and variables" → "Actions"**
4. **Click "New repository secret"**
5. **Add each secret one by one:**

   - **Name**: `DEPLOY_SSH_KEY`
     **Secret**: Paste your private key content from `~/.ssh/deploy_key`

   - **Name**: `SERVER_HOST`
     **Secret**: `31.97.111.96`

   - **Name**: `SERVER_USER`
     **Secret**: `root`

## Deployment Workflow

The GitHub Actions workflow will:

1. **Build Phase:**
   - Checkout code
   - Setup PHP 8.2
   - Install Composer dependencies
   - Run tests (if available)

2. **Deploy Phase:**
   - Connect to server via SSH
   - Create timestamped release directory
   - Upload application files via rsync
   - Install production dependencies
   - Create symlinks to storage and config
   - Update current release symlink
   - Reload web services

3. **Verify Phase:**
   - Check symlinks are valid
   - Verify web services are running
   - Cleanup old releases (keep last 5)

## Manual Deployment

You can also trigger deployment manually:

1. Go to your repository on GitHub
2. Click "Actions" tab
3. Select "Deploy YBB Admin to Production" workflow
4. Click "Run workflow" button
5. Select the branch (usually `main`)
6. Click "Run workflow"

## Deployment Structure

The workflow follows the deployment structure we defined:

```
/srv/apps/ybb-admin/prod/
├── releases/
│   ├── 2025-09-22_161530/    # Timestamped releases
│   ├── 2025-09-22_171245/
│   └── 2025-09-22_183020/
├── current -> releases/2025-09-22_183020  # Symlink to latest release
```

Each release includes:
- All application files
- Composer dependencies
- Symlinks to shared storage and config
- Proper file permissions for web server

## Troubleshooting

### SSH Connection Issues
If deployment fails with SSH errors:

1. **Check SSH key format**: Ensure the private key is properly formatted with headers/footers
2. **Verify server access**: Test SSH connection manually from your local machine
3. **Check known_hosts**: The workflow automatically adds server to known_hosts

### Permission Issues
If you get permission errors:

1. **Check server directory ownership**: Ensure directories are owned by root
2. **Verify SSH user**: Make sure `SERVER_USER` secret is set to `root`
3. **Check web server permissions**: Ensure www-data has access to deployed files

### Service Reload Issues
If web services fail to reload:

1. **Check service status**: SSH to server and run `systemctl status nginx php8.2-fpm`
2. **Verify configuration**: Check nginx and PHP-FPM configurations
3. **Check logs**: Look at `/var/log/nginx/error.log` and `/var/log/php8.2-fpm.log`

## Security Notes

1. **Private Key Security**: The private key is stored securely in GitHub Secrets and never exposed in logs
2. **Root Access**: We're using root access for simplicity - consider using a dedicated deploy user for additional security
3. **Server Hardening**: Ensure your server has proper firewall rules and security updates
4. **Key Rotation**: Regularly rotate SSH keys and update the GitHub Secret

## Next Steps

After setting up the secrets:

1. **Test the workflow**: Push a commit to `main` branch or run workflow manually
2. **Monitor deployment**: Check the Actions tab for deployment progress
3. **Verify application**: Visit your application URL to confirm deployment success
4. **Set up monitoring**: Consider adding health checks and monitoring for your production application