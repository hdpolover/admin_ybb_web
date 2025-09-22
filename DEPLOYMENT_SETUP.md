# GitHub Actions Deployment Setup for YBB Admin System

This document provides step-by-step instructions to set up automated deployment from GitHub to your VPS whenever you push to the main branch.

## 🚀 Quick Overview

The deployment system includes:
- **GitHub Actions Workflow**: Automatically triggers on push to main branch
- **Deployment Script**: Handles backup, file transfer, and application setup
- **Rollback Script**: Allows quick rollback to previous versions
- **Health Checks**: Verifies deployment success

## 📋 Prerequisites

### VPS Requirements
- Linux-based VPS (Ubuntu 20.04+ recommended)
- PHP 8.1+ with required extensions
- Web server (Apache/Nginx) configured
- SSH access with sudo privileges
- Composer installed globally

### Required PHP Extensions
```bash
# Install required PHP extensions
sudo apt update
sudo apt install php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-intl php8.1-gd php8.1-dom
```

## 🔧 VPS Setup

### 1. Create Application Directory
```bash
# Create directory for your application
sudo mkdir -p /var/www/ybb-admin
sudo chown $USER:$USER /var/www/ybb-admin
cd /var/www/ybb-admin
```

### 2. Configure Web Server

#### For Apache:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/ybb-admin/public
    
    <Directory /var/www/ybb-admin/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/ybb-admin_error.log
    CustomLog ${APACHE_LOG_DIR}/ybb-admin_access.log combined
</VirtualHost>
```

#### For Nginx:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/ybb-admin/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. Setup Environment File
```bash
# Create .env file in /var/www/ybb-admin/
cat > .env << 'EOF'
# CI Environment
CI_ENVIRONMENT = production

# Database
database.default.hostname = your-db-host
database.default.database = your-db-name
database.default.username = your-db-user
database.default.password = your-db-password
database.default.DBDriver = MySQLi
database.default.charset = utf8mb4

# Security
encryption.key = your-encryption-key-here

# YBB Export API
YBB_EXPORT_API_URL = your-export-api-url

# Midtrans Configuration
MIDTRANS_SERVER_KEY = your-midtrans-server-key
MIDTRANS_CLIENT_KEY = your-midtrans-client-key
MIDTRANS_IS_PRODUCTION = false
EOF
```

## 🔑 SSH Key Setup

### 1. Generate SSH Key Pair (on your local machine)
```bash
# Generate a new SSH key for deployment
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/ybb-deploy

# Copy the public key
cat ~/.ssh/ybb-deploy.pub
```

### 2. Add Public Key to VPS
```bash
# On your VPS, add the public key to authorized_keys
mkdir -p ~/.ssh
echo "your-public-key-here" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

### 3. Test SSH Connection
```bash
# Test the connection from your local machine
ssh -i ~/.ssh/ybb-deploy user@your-vps-ip
```

## ⚙️ GitHub Secrets Configuration

Add the following secrets to your GitHub repository:

1. Go to your repository on GitHub
2. Navigate to Settings → Secrets and variables → Actions
3. Click "New repository secret" and add each of these:

### Required Secrets

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `VPS_SSH_KEY` | Private SSH key content | Copy content of `~/.ssh/ybb-deploy` |
| `VPS_HOST` | VPS IP address or domain | `123.456.789.0` or `your-domain.com` |
| `VPS_USER` | SSH username | `ubuntu` or `root` |
| `VPS_PATH` | Application path on VPS | `/var/www/ybb-admin` |

### Optional Secrets

| Secret Name | Description | Default |
|-------------|-------------|---------|
| `PHP_VERSION` | PHP version to use | `8.1` |
| `RUN_MIGRATIONS` | Run DB migrations | `false` |

### Adding SSH Key Secret
```bash
# Get the private key content
cat ~/.ssh/ybb-deploy

# Copy the entire output (including -----BEGIN and -----END lines)
# Paste this as the value for VPS_SSH_KEY secret
```

## 🎯 Deployment Workflow

### Automatic Deployment
1. Push code to the `main` branch
2. GitHub Actions automatically triggers
3. Code is built and dependencies installed
4. Application is deployed to your VPS
5. Backup is created and old deployment is replaced
6. Permissions are set and cache is cleared

### Manual Deployment
You can also trigger deployment manually:
1. Go to GitHub Actions tab in your repository
2. Select "Deploy to VPS" workflow
3. Click "Run workflow" button

## 📁 File Structure After Deployment

```
/var/www/ybb-admin/
├── app/                    # CodeIgniter application
├── public/                 # Web root
├── system/                 # CodeIgniter system files
├── vendor/                 # Composer dependencies
├── writable/               # Writable directories
│   ├── cache/              # Application cache
│   ├── logs/               # Log files
│   ├── session/            # Session files
│   └── uploads/            # Uploaded files
├── backups/                # Automatic backups
├── scripts/                # Deployment scripts
├── .env                    # Environment configuration
├── spark                   # CodeIgniter CLI tool
└── deployment.log          # Deployment logs
```

## 🔄 Rollback Instructions

If something goes wrong, you can rollback to a previous version:

### Using the Rollback Script
```bash
# SSH to your VPS
ssh user@your-vps-ip

# Navigate to application directory
cd /var/www/ybb-admin

# List available backups
bash scripts/rollback.sh list

# Rollback to specific backup
bash scripts/rollback.sh rollback /var/www/ybb-admin/backups/backup_20250920_143022.tar.gz

# Or rollback to latest backup
bash scripts/rollback.sh latest
```

### Manual Rollback
```bash
# SSH to your VPS
cd /var/www/ybb-admin/backups

# List available backups
ls -la backup_*.tar.gz

# Extract a specific backup
tar -xzf backup_20250920_143022.tar.gz -C ../

# Set proper permissions
cd ..
chmod -R 755 .
chmod -R 777 writable/
```

## 🔍 Troubleshooting

### Common Issues

#### 1. SSH Connection Failed
```bash
# Check SSH key permissions
chmod 600 ~/.ssh/ybb-deploy

# Test SSH connection manually
ssh -i ~/.ssh/ybb-deploy -v user@your-vps-ip
```

#### 2. Permission Denied on VPS
```bash
# Ensure user owns the application directory
sudo chown -R $USER:$USER /var/www/ybb-admin

# Set proper permissions
chmod -R 755 /var/www/ybb-admin
chmod -R 777 /var/www/ybb-admin/writable
```

#### 3. Composer Dependencies Failed
```bash
# SSH to VPS and install manually
cd /var/www/ybb-admin
composer install --no-dev --optimize-autoloader
```

#### 4. Database Connection Issues
```bash
# Check .env file
cat /var/www/ybb-admin/.env

# Test database connection
php /var/www/ybb-admin/spark db:table
```

### Viewing Logs

#### GitHub Actions Logs
1. Go to your repository → Actions tab
2. Click on the failed workflow run
3. Expand the failed step to see detailed logs

#### VPS Deployment Logs
```bash
# View deployment logs
tail -f /var/www/ybb-admin/deployment.log

# View web server logs
sudo tail -f /var/log/apache2/error.log
# or for Nginx:
sudo tail -f /var/log/nginx/error.log

# View PHP logs
sudo tail -f /var/log/php8.1-fpm.log
```

## 🛡️ Security Considerations

### SSH Security
- Use strong SSH keys (ed25519 recommended)
- Disable password authentication
- Use non-standard SSH port
- Configure firewall rules

### File Permissions
- Application files: 644
- Directories: 755
- Writable directory: 777 (only for writable/)
- Executable files (spark): 755

### Environment Security
- Keep `.env` file secure and never commit to repository
- Use strong database passwords
- Enable HTTPS with SSL certificates
- Regular security updates

## 📊 Monitoring

### Health Checks
The deployment includes basic health checks:
- Critical files existence
- Directory structure validation
- PHP syntax validation
- Composer dependencies verification

### Custom Monitoring
Add your own monitoring by extending the verification step in the deployment script:

```bash
# Add to scripts/deploy.sh in verify_deployment() function
# Check database connection
php spark db:table users > /dev/null || error "Database connection failed"

# Check critical functionality
curl -f http://localhost/health-check || error "Application health check failed"
```

## 🔧 Advanced Configuration

### Environment-Specific Deployments
You can modify the workflow to support different environments:

```yaml
# Add to .github/workflows/deploy.yml
- name: Deploy to Staging
  if: github.ref == 'refs/heads/develop'
  # staging deployment steps

- name: Deploy to Production
  if: github.ref == 'refs/heads/main'
  # production deployment steps
```

### Database Migrations
Enable automatic database migrations by setting the secret:
- `RUN_MIGRATIONS` = `true`

### Custom Scripts
Add custom deployment scripts in the `scripts/` directory and call them in the workflow.

## 📞 Support

If you encounter issues:

1. Check the GitHub Actions logs
2. Review VPS deployment logs
3. Verify all secrets are correctly set
4. Test SSH connection manually
5. Ensure VPS meets all requirements

For CodeIgniter 4 specific issues, refer to the [official documentation](https://codeigniter.com/user_guide/).

## 🎉 Deployment Complete!

Once everything is set up:
1. Make a change to your code
2. Push to the main branch
3. Watch the GitHub Actions tab for deployment progress
4. Your changes will automatically appear on your VPS!

The deployment system creates automatic backups, so you can always rollback if needed. Happy deploying! 🚀