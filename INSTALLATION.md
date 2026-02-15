# Installation Guide - BackCheck.io Verify

This guide provides detailed instructions for installing and configuring the BackCheck.io Verify application.

## Table of Contents
- [System Requirements](#system-requirements)
- [Pre-Installation Checklist](#pre-installation-checklist)
- [Installation Steps](#installation-steps)
- [Configuration](#configuration)
- [Post-Installation](#post-installation)
- [Troubleshooting](#troubleshooting)

## System Requirements

### Server Requirements
- **Operating System**: Linux (Ubuntu 18.04+ or CentOS 7+)
- **Web Server**: Apache 2.4+ or Nginx 1.14+
- **PHP Version**: 5.6 or higher (PHP 7.2+ recommended)
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Disk Space**: Minimum 2GB (recommended 10GB+ for document storage)
- **Memory**: Minimum 512MB RAM (recommended 2GB+)

### PHP Extensions Required
```bash
php-mysql
php-gd
php-mbstring
php-curl
php-xml
php-json
php-zip
php-fileinfo
```

### PHP Configuration
Update your `php.ini` with the following minimum values:

```ini
upload_max_filesize = 5M
post_max_size = 8M
max_execution_time = 300
memory_limit = 256M
session.gc_maxlifetime = 3600
date.timezone = Asia/Karachi
```

### MySQL Configuration
- Character Set: UTF-8 (utf8mb4 recommended)
- Default Collation: utf8mb4_general_ci
- InnoDB storage engine enabled

## Pre-Installation Checklist

- [ ] Server meets minimum requirements
- [ ] Required PHP extensions installed
- [ ] MySQL database created
- [ ] Database user with appropriate privileges created
- [ ] Web server configured (Apache/Nginx)
- [ ] SSL certificate installed (recommended)
- [ ] Domain name configured
- [ ] Backup plan in place

## Installation Steps

### Step 1: Download and Extract

```bash
# Clone from repository
cd /var/www/html
git clone https://github.com/BackCheck/backcheck.io.verify.git verify
cd verify

# Or extract from archive
tar -xzf backcheck-verify.tar.gz
mv backcheck-verify verify
```

### Step 2: Set File Permissions

```bash
# Set ownership (replace www-data with your web server user)
chown -R www-data:www-data /var/www/html/verify

# Set directory permissions
find /var/www/html/verify -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/html/verify -type f -exec chmod 644 {} \;

# Set writable directories
chmod -R 775 /var/www/html/verify/images/uploads
chmod -R 775 /var/www/html/verify/images/case_uploads
chmod -R 775 /var/www/html/verify/images/profile_pics
```

### Step 3: Database Setup

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE backglob_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

# Create database user
CREATE USER 'backglob_user'@'localhost' IDENTIFIED BY 'your_secure_password';

# Grant privileges
GRANT ALL PRIVILEGES ON backglob_db.* TO 'backglob_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import database schema (contact administrator for SQL file)
mysql -u backglob_user -p backglob_db < database_schema.sql
```

### Step 4: Configure Application

Edit `/include/global_config.php`:

```php
<?php 
    // Database Configuration
    define("HOST", 'localhost');
    define("DB", 'backglob_db');
    define("USER", 'backglob_user');
    define("PASS", 'your_secure_password');
    
    // Site URLs
    define("SITE_URL", 'https://your-domain.com/verify/');
    define("SURL", 'https://your-domain.com/verify/');
    
    // Site Information
    define("SITENM", 'BackgroundCheckGroup');
    define("DEMAIL", 'noreply@your-domain.com');
    define("SUPPORT_EMAIL", 'support@your-domain.com');
    define("INFO_EMAIL", 'info@your-domain.com');
    
    // File Upload Settings
    define("FILE_TYPES_ALLOWED", "gif, jpeg, jpg, png, docx, doc, pdf");
    define("FILE_SIZE_ALLOWED", "5 MB");
    
    // Business Settings
    define("TAT", 10);  // Turnaround time in days
    
    // API URLs (configure if using integrations)
    define("BITRIX_URL", "https://your-bitrix-url.com/rest_api.php");
    define("WHMCS_APIURL", "https://your-whmcs-url.com/includes/api.php");
    
    $BCPV = 3.4;  // Application version
?>
```

### Step 5: Web Server Configuration

#### Apache Configuration

Create `/etc/apache2/sites-available/backcheck-verify.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/html/verify
    
    <Directory /var/www/html/verify>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/verify-error.log
    CustomLog ${APACHE_LOG_DIR}/verify-access.log combined
</VirtualHost>

# SSL Configuration (recommended)
<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/html/verify
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt
    
    <Directory /var/www/html/verify>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/verify-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/verify-ssl-access.log combined
</VirtualHost>
```

Enable the site and restart Apache:

```bash
a2ensite backcheck-verify
a2enmod rewrite ssl
systemctl restart apache2
```

#### Nginx Configuration

Create `/etc/nginx/sites-available/backcheck-verify`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/html/verify;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # File upload size
    client_max_body_size 5M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /include/ {
        deny all;
    }
    
    access_log /var/log/nginx/verify-access.log;
    error_log /var/log/nginx/verify-error.log;
}

# SSL Configuration
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/html/verify;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    
    # Same configuration as HTTP above
    # ... (copy from above)
}
```

Enable the site and restart Nginx:

```bash
ln -s /etc/nginx/sites-available/backcheck-verify /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

### Step 6: Create Initial Admin User

Login to MySQL and create an admin account:

```sql
USE backglob_db;

INSERT INTO users (username, password, email, level, status) 
VALUES ('admin', MD5('temporary_password'), 'admin@your-domain.com', 1, 1);

-- Note: Change password immediately after first login
```

## Configuration

### Email Configuration

Configure email settings in PHPMailer (in functions/functions.php or separate config):

```php
$mail->Host = 'smtp.your-domain.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'noreply@your-domain.com';
$mail->Password = 'your_email_password';
$mail->SMTPSecure = 'tls';
$mail->From = 'noreply@your-domain.com';
$mail->FromName = 'BackCheck Verify';
```

### Bitrix CRM Integration

To enable Bitrix integration:

1. Obtain API credentials from your Bitrix instance
2. Update `BITRIX_URL` in `global_config.php`
3. Configure admin mappings in `/functions/bitrix/bitrix_functions.php`

### Google Sheets Integration

1. Create a Google Cloud Project
2. Enable Google Sheets API
3. Create service account and download JSON credentials
4. Place credentials in secure location
5. Update API configuration in `api_google.php`

### Savvion BPM Integration

Configure Savvion connection settings in `/functions/savvion/savvion_config.php` (if file exists) or contact administrator for configuration details.

## Post-Installation

### 1. Verify Installation

Visit your installation URL and verify:
- [ ] Application loads without errors
- [ ] Login page is accessible
- [ ] Can login with admin credentials
- [ ] Dashboard loads correctly
- [ ] File upload works
- [ ] Email notifications work

### 2. Security Hardening

```bash
# Remove installer files (if any)
rm -f install.php setup.php

# Secure configuration files
chmod 640 /var/www/html/verify/include/global_config.php

# Set up firewall rules
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable

# Configure fail2ban for brute force protection
apt-get install fail2ban
systemctl enable fail2ban
```

### 3. Set Up Backups

```bash
# Database backup script
#!/bin/bash
BACKUP_DIR="/backup/mysql"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u backglob_user -p backglob_db > $BACKUP_DIR/backglob_db_$DATE.sql
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete

# Files backup
tar -czf /backup/files/verify_files_$DATE.tar.gz /var/www/html/verify/images/uploads

# Add to crontab
crontab -e
# Add: 0 2 * * * /path/to/backup_script.sh
```

### 4. Configure Cron Jobs

Set up automated tasks:

```bash
crontab -e

# Daily digest notifications
0 8 * * * /usr/bin/php /var/www/html/verify/daily_digest_insuff.php

# Monthly invoice generation
0 0 1 * * /usr/bin/php /var/www/html/verify/monthly_invoice_cron.php

# Pre-employment verification cron
0 */2 * * * /usr/bin/php /var/www/html/verify/pre_emp_send_and_repsone_cron.php

# Bitrix sync (if enabled)
*/30 * * * * /usr/bin/php /var/www/html/verify/auto_addtasks_to_bitrix2.php
```

### 5. Monitor Logs

```bash
# Application logs
tail -f /var/log/apache2/verify-error.log
# or
tail -f /var/log/nginx/verify-error.log

# PHP logs
tail -f /var/log/php7.4-fpm.log

# MySQL logs
tail -f /var/log/mysql/error.log
```

## Troubleshooting

### Common Issues

#### 1. White Screen / 500 Error
**Solution:**
- Check PHP error logs
- Verify file permissions
- Ensure all PHP extensions are installed
- Check database connection in global_config.php

#### 2. Database Connection Failed
**Solution:**
```bash
# Verify MySQL is running
systemctl status mysql

# Test connection
mysql -u backglob_user -p -h localhost backglob_db

# Check credentials in global_config.php
```

#### 3. File Upload Not Working
**Solution:**
- Check directory permissions (775)
- Verify PHP upload settings in php.ini
- Check available disk space
- Review web server error logs

#### 4. Session Timeout Issues
**Solution:**
```ini
# In php.ini
session.gc_maxlifetime = 3600
session.cookie_lifetime = 0

# Restart PHP-FPM
systemctl restart php7.4-fpm
```

#### 5. Email Not Sending
**Solution:**
- Verify SMTP credentials
- Check firewall rules for port 587/465
- Test with mail() function
- Review email logs

#### 6. Permission Denied Errors
**Solution:**
```bash
# Fix ownership
chown -R www-data:www-data /var/www/html/verify

# Fix permissions
find /var/www/html/verify -type d -exec chmod 755 {} \;
find /var/www/html/verify -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/html/verify/images/uploads
```

### Getting Help

If you encounter issues not covered here:

1. Check application error logs
2. Review web server error logs
3. Contact support: support@backcheckgroup.com
4. Provide: error messages, server details, PHP version, steps to reproduce

## Updating the Application

```bash
# Backup first
mysqldump -u backglob_user -p backglob_db > backup.sql
tar -czf backup_files.tar.gz /var/www/html/verify

# Pull latest changes
cd /var/www/html/verify
git pull origin main

# Update database if needed
mysql -u backglob_user -p backglob_db < updates/update_schema.sql

# Clear cache if applicable
rm -rf /var/www/html/verify/cache/*

# Restart services
systemctl restart apache2  # or nginx
```

## Security Recommendations

1. **Use HTTPS**: Always use SSL/TLS certificates
2. **Strong Passwords**: Enforce strong password policy
3. **Regular Updates**: Keep PHP, MySQL, and web server updated
4. **Firewall**: Configure UFW or iptables
5. **Backup**: Automate daily backups
6. **Monitoring**: Set up monitoring and alerting
7. **Code Updates**: Migrate from deprecated mysql_* functions to PDO
8. **Input Validation**: Review and enhance input validation
9. **SQL Injection**: Implement prepared statements throughout
10. **File Upload Security**: Validate file types and scan for malware

---

**Support**: For installation support, contact support@backcheckgroup.com
