# YBB Admin Production Deployment Structure

## Recommended VPS Folder Structure

```
/
├─ srv/
│  ├─ apps/
│  │  └─ ybb-admin/
│  │     ├─ prod/
│  │     │  ├─ releases/
│  │     │  │  ├─ 2025-09-22_2015/
│  │     │  │  ├─ 2025-09-23_1030/
│  │     │  │  └─ 2025-09-24_1445/
│  │     │  │     ├─ app/
│  │     │  │     ├─ public/
│  │     │  │     ├─ vendor/
│  │     │  │     ├─ system/
│  │     │  │     ├─ composer.json
│  │     │  │     ├─ composer.lock
│  │     │  │     ├─ spark
│  │     │  │     ├─ writable -> /srv/storage/ybb-storage/writable
│  │     │  │     ├─ public/uploads -> /srv/storage/ybb-storage/uploads
│  │     │  │     └─ .env -> /etc/ybb-admin/prod/.env
│  │     │  └─ current -> releases/2025-09-24_1445
│  │     │
│  │     └─ staging/               # future staging environment
│  │        ├─ releases/
│  │        └─ current -> releases/latest
│  │
│  └─ storage/
│     └─ ybb-storage/              # custom storage structure (accessible via storage.ybbfoundation.com)
│
├─ etc/
│  ├─ ybb-admin/
│  │  ├─ prod/
│  │  │  ├─ .env                   # production environment config
│  │  │  └─ crontab                # scheduled tasks
│  │  └─ staging/
│  │     └─ .env                   # staging environment config
│  │
│  ├─ nginx/
│  │  ├─ sites-available/
│  │  │  ├─ ybb-admin-prod.conf    # main app nginx config
│  │  │  ├─ ybb-admin-staging.conf # staging nginx config
│  │  │  └─ ybb-storage.conf       # storage server nginx config
│  │  └─ sites-enabled/ -> symlinks
│  │
│  ├─ php/
│  │  ├─ fpm/
│  │  │  ├─ pool.d/
│  │  │  │  ├─ ybb-admin-prod.conf
│  │  │  │  └─ ybb-admin-staging.conf
│  │  │  └─ php.ini
│  │  └─ cli/
│  │     └─ php.ini
│  │
│  └─ systemd/
│     └─ system/
│        ├─ ybb-admin-worker.service      # for background jobs
│        └─ ybb-admin-scheduler.service   # for cron-like tasks
│
├─ var/
│  ├─ log/
│  │  ├─ ybb-admin/
│  │  │  ├─ prod/
│  │  │  │  ├─ nginx.access.log
│  │  │  │  ├─ nginx.error.log
│  │  │  │  ├─ app.log
│  │  │  │  ├─ worker.log
│  │  │  │  └─ php-fpm.log
│  │  │  └─ staging/
│  │  │     └─ [similar log structure]
│  │  └─ nginx/
│  │     ├─ ybb-storage.access.log
│  │     └─ ybb-storage.error.log
│  │
│  └─ backup/
│     └─ ybb-admin/
│        ├─ database/              # automated DB backups
│        ├─ storage/               # storage backups if needed
│        └─ releases/              # old release archives
│
└─ opt/
   └─ scripts/
      └─ ybb-admin/
         ├─ deploy.sh               # deployment script
         ├─ rollback.sh             # rollback script
         ├─ backup.sh               # backup script
         └─ health-check.sh         # monitoring script
```

## Key Improvements Made

### 1. **Deployment Structure**

- Multiple releases with timestamp-based naming
- Clean rollback mechanism with `current` symlink
- External storage via symlinks (no shared directory needed)

### 2. **Storage Organization**

- Organized uploads by type (participants, abstracts, papers, etc.)
- Separated temporary exports from permanent uploads
- Added brand assets directory

### 3. **Configuration Management**

- Dedicated config directories for each environment
- PHP-FPM pool configurations
- Systemd service definitions for background processes

### 4. **Logging Structure**

- Environment-specific log separation
- Application and infrastructure logs separated
- Centralized log location following FHS

### 5. **Backup Strategy**

- Dedicated backup location
- Database and file backup separation
- Old release archival

## Deployment Workflow

### Initial Setup

```bash
# Create directory structure
sudo mkdir -p /srv/apps/ybb-admin/prod/releases
sudo mkdir -p /srv/storage/ybb-storage
sudo mkdir -p /etc/ybb-admin/prod
sudo mkdir -p /var/{log,backup}/ybb-admin/prod
sudo mkdir -p /opt/scripts/ybb-admin

# Set ownership and permissions
sudo chown -R root:root /srv/apps/ybb-admin
sudo chown -R www-data:www-data /srv/storage/ybb-storage
sudo chown -R root:root /opt/scripts/ybb-admin
sudo chmod -R 755 /srv/storage/ybb-storage
sudo chmod -R 755 /srv/apps/ybb-admin
sudo chmod -R 755 /opt/scripts/ybb-admin
```

## SSH Configuration for Root User

### 1. Generate SSH Key Pair (on your local machine)

```bash
# Generate a new SSH key specifically for deployment
ssh-keygen -t ed25519 -C "deploy-key" -f ~/.ssh/deploy_key

# Or use RSA if ed25519 is not supported
ssh-keygen -t rsa -b 4096 -C "deploy-key" -f ~/.ssh/deploy_key
```

### 2. Copy Public Key to Server

```bash
# Method 1: Using ssh-copy-id (if you have password access)
ssh-copy-id -i ~/.ssh/deploy_key.pub root@your-server-ip

# Method 2: Manual copy (if you already have root access)
# First, copy the public key content
cat ~/.ssh/deploy_key.pub

# Then on the server, add it to authorized_keys
echo "your-public-key-content-here" | tee -a /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys
chmod 700 /root/.ssh

# Method 3: If you're already on the server as root
cat ~/.ssh/deploy_key.pub >> /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys
```

### 3. Configure SSH Client (on your local machine)

Create or edit `~/.ssh/config`:

```ssh
# General Production Server Template
Host prod-server
    HostName your-server-ip-or-domain
    User root
    IdentityFile ~/.ssh/deploy_key
    IdentitiesOnly yes
    AddKeysToAgent yes
    
# YBB Admin Production Server Example
Host ybb-prod
    HostName 31.97.111.96
    User root
    Port 22
    IdentityFile ~/.ssh/deploy_key
    IdentitiesOnly yes
    AddKeysToAgent yes
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

### 4. Server SSH Security Configuration

Edit `/etc/ssh/sshd_config` on your server:

```ssh
# Enable public key authentication
PubkeyAuthentication yes
AuthorizedKeysFile .ssh/authorized_keys

# Optional: Disable password authentication for better security
# PasswordAuthentication no

# Allow root login with key (since we're using root)
PermitRootLogin prohibit-password

# Optional: Restrict root user to specific commands
# You can use ForceCommand or restrict in authorized_keys

# Change default SSH port (optional)
# Port 2222
```

Restart SSH service:
```bash
sudo systemctl restart sshd
```

### 5. Test SSH Connection

```bash
# Test the connection from your local machine
ssh prod-server whoami
# Should return: root

# Test with verbose output if there are issues
ssh -v prod-server

# Test YBB production server specifically
ssh ybb-prod whoami
# Should return: root

# Test with the actual server IP from your local machine
ssh -i ~/.ssh/deploy_key root@31.97.111.96
```

### 6. Advanced: Restricted SSH Keys (Optional)

For extra security, you can restrict what the deploy key can do by adding options to `authorized_keys`:

```bash
# Edit the authorized_keys file on the server
sudo nano /home/deploy/.ssh/authorized_keys

# Add restrictions before the key (all on one line):
command="/opt/scripts/project-name/deploy.sh",no-port-forwarding,no-X11-forwarding,no-agent-forwarding ssh-ed25519 AAAAC3... deploy-key
```

### 7. SSH Agent Configuration (Local Machine)

Add to your `~/.bashrc` or `~/.zshrc`:

```bash
# Start SSH agent and add key
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/deploy_key
```

Or use `ssh-add -A` on macOS to automatically load keys from keychain.

### 8. Deployment Script with SSH

Create a local deployment script `deploy-ybb.sh`:

```bash
#!/bin/bash

# Local deployment script
SERVER="prod-server"  # or ybb-prod for YBB specific
LOCAL_PATH="/path/to/your/project"
REMOTE_SCRIPT="/opt/scripts/project-name/deploy.sh"

echo "Deploying to production..."

# Upload code to server
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='writable' \
    $LOCAL_PATH/ $SERVER:/tmp/project-deploy/

# Run deployment script on server
ssh $SERVER "bash $REMOTE_SCRIPT /tmp/project-deploy"

echo "Deployment completed!"
```

### 9. Troubleshooting SSH Issues

```bash
# If SSH asks for password when using key, try these troubleshooting steps:

# 1. Check SSH connection with verbose output to see what's failing
ssh -v -i ~/.ssh/deploy_key deploy@your-server-ip

# 2. Verify your public key is correctly installed on server
ssh your-admin-user@your-server-ip "sudo cat /home/deploy/.ssh/authorized_keys"

# 3. Check file permissions on server (must be exact)
ssh your-admin-user@your-server-ip "sudo ls -la /home/deploy/.ssh/"
# Should show:
# drwx------ 2 deploy deploy 4096 /home/deploy/.ssh/
# -rw------- 1 deploy deploy  xxx  /home/deploy/.ssh/authorized_keys

# 4. Fix permissions if they're wrong
ssh your-admin-user@your-server-ip "sudo chmod 700 /home/deploy/.ssh && sudo chmod 600 /home/deploy/.ssh/authorized_keys && sudo chown -R deploy:deploy /home/deploy/.ssh"

# 5. Test specific key with verbose output
ssh -v -i ~/.ssh/deploy_key deploy@your-server-ip

# 6. Check if SSH agent is interfering (disable it temporarily)
ssh -o IdentitiesOnly=yes -i ~/.ssh/deploy_key deploy@your-server-ip

# 7. Verify the public key matches what's on the server
# On local machine:
ssh-keygen -y -f ~/.ssh/deploy_key
# Compare output with what's in /home/deploy/.ssh/authorized_keys on server

# 8. Check SSH server logs for authentication failures
sudo tail -f /var/log/auth.log

# 9. Verify SSH server configuration allows public key auth
sudo grep -E "(PubkeyAuthentication|AuthorizedKeysFile)" /etc/ssh/sshd_config

# 10. If deploy user has no password set, you might need to set one first
sudo passwd deploy
# Then try ssh-copy-id again
ssh-copy-id -i ~/.ssh/deploy_key.pub deploy@your-server-ip
```

### 10. Security Best Practices

1. **Use SSH keys only** - Disable password authentication
2. **Separate keys** - Don't reuse your personal SSH key for deployments  
3. **Key rotation** - Regularly rotate deployment keys
4. **Restricted commands** - Use command restrictions in authorized_keys if needed
5. **Monitor access** - Regularly check SSH logs for unauthorized access attempts
6. **Firewall** - Restrict SSH access to specific IP addresses if possible

```bash
# Example: Restrict SSH to specific IPs using UFW
sudo ufw allow from your-office-ip to any port 22
sudo ufw deny 22
```

### Deployment Process

```bash
# Run as root user
# 1. Create new release directory
RELEASE_DIR="/srv/apps/ybb-admin/prod/releases/$(date +%Y-%m-%d_%H%M)"
mkdir -p $RELEASE_DIR

# 2. Extract/copy application files
rsync -av /path/to/source/ $RELEASE_DIR/

# 3. Create symlinks to external storage (adjust paths based on your ybb-storage structure)
ln -sfn /srv/storage/ybb-storage/writable $RELEASE_DIR/writable
ln -sfn /srv/storage/ybb-storage/uploads $RELEASE_DIR/public/uploads
ln -sfn /etc/ybb-admin/prod/.env $RELEASE_DIR/.env

# 4. Install dependencies
cd $RELEASE_DIR && composer install --no-dev --optimize-autoloader

# 5. Set proper ownership for web server
chown -R www-data:www-data $RELEASE_DIR

# 6. Update current symlink (atomic deployment)
ln -sfn $RELEASE_DIR /srv/apps/ybb-admin/prod/current

# 7. Reload services
systemctl reload nginx
systemctl reload php8.2-fpm
```

## Environment Configuration

### Sample .env for Production
```env
# Basic Config
CI_ENVIRONMENT = production
app.baseURL = 'https://admin.ybbfoundation.com'

# Database
database.default.hostname = localhost
database.default.database = ybb_prod
database.default.username = ybb_user
database.default.password = secure_password

# Storage
app.storageURL = 'https://storage.ybbfoundation.com'
app.uploadPath = '/srv/storage/ybb-storage/uploads'

# Caching
cache.redis.host = 127.0.0.1
cache.redis.port = 6379
cache.redis.database = 0

# YBB Export API
YBB_EXPORT_API_URL = 'https://your-export-api.railway.app'
```

### Nginx Configuration for Storage
```nginx
# /etc/nginx/sites-available/ybb-storage.conf
server {
    listen 80;
    server_name storage.ybbfoundation.com;
    root /srv/storage/ybb-storage;
    
    location /uploads {
        alias /srv/storage/ybb-storage/uploads;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    location /assets {
        alias /srv/storage/ybb-storage/assets;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Security: deny access to writable directory
    location /writable {
        deny all;
    }
}
```

## Security Considerations

1. **File Permissions**: Ensure proper ownership (www-data) and permissions
2. **Storage Access**: Only uploads and assets should be web-accessible
3. **Environment Files**: Keep .env files outside web root with restricted access
4. **Database**: Use dedicated database user with minimal privileges
5. **Backups**: Encrypt sensitive backup data

## Monitoring & Maintenance

1. **Log Rotation**: Configure logrotate for application logs
2. **Backup Automation**: Daily database backups, weekly storage snapshots
3. **Health Checks**: Monitor application, database, and storage connectivity
4. **Release Cleanup**: Keep only last 5 releases, archive older ones
5. **Cache Management**: Redis monitoring and optimization

This structure provides a solid foundation for scaling, maintaining security, and ensuring reliable deployments.