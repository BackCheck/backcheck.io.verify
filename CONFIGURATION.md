# Configuration Guide - BackCheck.io Verify

## Table of Contents
- [Configuration Files](#configuration-files)
- [Database Configuration](#database-configuration)
- [Application Settings](#application-settings)
- [Email Configuration](#email-configuration)
- [File Upload Settings](#file-upload-settings)
- [Integration Settings](#integration-settings)
- [Security Settings](#security-settings)
- [Performance Tuning](#performance-tuning)
- [Environment-Specific Configuration](#environment-specific-configuration)

## Configuration Files

The system uses multiple configuration files located in the `/include/` directory:

### Primary Configuration Files

| File | Purpose |
|------|---------|
| `global_config.php` | Main configuration - database, URLs, constants |
| `config_index.php` | Index page configuration and session handling |
| `config_actions.php` | AJAX actions configuration |
| `config_client.php` | Client portal configuration |
| `config_admin.php` | Admin panel configuration |

### Configuration File Locations

```
/verify/include/
├── global_config.php      # Main configuration (EDIT THIS)
├── config_index.php        # Index configuration
├── config_actions.php      # Actions configuration
├── config_client.php       # Client configuration
└── config_admin.php        # Admin configuration
```

## Database Configuration

### global_config.php - Database Settings

```php
<?php 
// Database Connection Settings
define("HOST", 'localhost');           // Database host
define("DB", 'backglob_db');          // Database name
define("USER", 'backglob_user');      // Database username
define("PASS", 'your_password_here'); // Database password

// Timezone Setting
date_default_timezone_set('Asia/Karachi');
?>
```

### Database Connection Options

**Local Development**:
```php
define("HOST", 'localhost');
define("DB", 'backglob_dev');
define("USER", 'dev_user');
define("PASS", 'dev_password');
```

**Production**:
```php
define("HOST", 'production-db.example.com');
define("DB", 'backglob_production');
define("USER", 'prod_user');
define("PASS", 'strong_production_password');
```

**Remote Database**:
```php
define("HOST", '192.168.1.100');  // Remote DB server IP
define("DB", 'backglob_db');
define("USER", 'remote_user');
define("PASS", 'remote_password');
```

### Database Character Set

The database should use UTF-8 encoding:

```sql
ALTER DATABASE backglob_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

## Application Settings

### Site URLs

```php
// Main Site URLs
define("SITE_URL", 'https://backcheck.io/verify/');
define("SURL", 'https://backcheck.io/verify/');

// For subdomain installations
define("SITE_URL", 'https://verify.backcheck.io/');

// For development/localhost
define("SITE_URL", 'http://localhost/verify/');
```

### Site Information

```php
// Organization Details
define("SITENM", 'BackgroundCheckGroup');
define("PORTAL", 'BackgroundCheckGroup Verification System');

// Email Addresses
define("DEMAIL", 'noreply@backcheckgroup.com');      // Default sender
define("SUPPORT_EMAIL", 'support@backcheckgroup.com');
define("INFO_EMAIL", 'info@backcheckgroup.com');

// Copyright Information
define("COPYRIGHT_URL", 'https://backcheckgroup.com/');
```

### Business Settings

```php
// Turnaround Time (in days)
define("TAT", 10);  // Default turnaround time for verifications

// Applicant Label
define("APPLICANT", "Applicant");  // Can be customized to "Candidate", etc.

// Company IDs for checks
define("CHECK_COMIDS", serialize(array(87, 96)));
```

### Application Version

```php
// Version number (used for cache busting)
$BCPV = 3.4;  // Increment when deploying updates
```

### Office Address

```php
define("OFFICE_ADDRESS", 
    "Background Check (Private) Limited<br />
     3rd Floor, GSA House, 19 Timber Pond,<br />
     Near KPT Overpass Bridge East Wharf,<br />
     Keamari, Karachi - Pakistan<br />
     Tel. : 92-21-32863920 - 31<br />
     Fax : 92-21-32863931<br />
     Email : info@backcheckgroup.com<br />
     SNTN: S2913136-7, NTN: 2913136-7"
);
```

### Payment Information

```php
define("DECLARATION",
    "Payment Instructions:<br />
     Name of Beneficiary: BackgroundCheckGroup<br />
     Account No: 08517900292703 (PKR)<br />
     IBAN: PKHABB0008577900292703<br />
     Bank: Habib Bank Limited, PNSC Karachi, Pakistan<br />
     Swift Code: HABBPKKA"
);
```

## Email Configuration

### PHPMailer Settings

Edit in `/functions/functions.php` or create a separate email config file:

```php
// SMTP Configuration
$mail->IsSMTP();
$mail->Host = 'smtp.gmail.com';           // SMTP server
$mail->Port = 587;                        // SMTP port (587 for TLS, 465 for SSL)
$mail->SMTPAuth = true;                   // Enable authentication
$mail->Username = 'noreply@backcheckgroup.com';  // SMTP username
$mail->Password = 'your_smtp_password';   // SMTP password
$mail->SMTPSecure = 'tls';                // Encryption (tls or ssl)

// Email From Settings
$mail->From = 'noreply@backcheckgroup.com';
$mail->FromName = 'BackCheck Verify';

// Additional Settings
$mail->CharSet = 'UTF-8';
$mail->IsHTML(true);
```

### Email Provider Configurations

**Gmail/G Suite**:
```php
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
// Note: Enable "Less secure app access" or use App Password
```

**Office 365**:
```php
$mail->Host = 'smtp.office365.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
```

**AWS SES**:
```php
$mail->Host = 'email-smtp.us-east-1.amazonaws.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->Username = 'YOUR_SES_SMTP_USERNAME';
$mail->Password = 'YOUR_SES_SMTP_PASSWORD';
```

**SendGrid**:
```php
$mail->Host = 'smtp.sendgrid.net';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->Username = 'apikey';
$mail->Password = 'YOUR_SENDGRID_API_KEY';
```

### Email Templates

Email templates are located in `/include_pages/email_templates/` or embedded in functions. Customize as needed:

```php
// Example email template
$email_body = "
<html>
<body>
    <h2>Verification Request Submitted</h2>
    <p>Dear {applicant_name},</p>
    <p>Your verification request has been submitted successfully.</p>
    <p>Reference Number: {ref_number}</p>
    <p>Expected Completion: {tat_date}</p>
</body>
</html>
";
```

## File Upload Settings

### Upload Configuration

```php
// Allowed File Types
define("FILE_TYPES_ALLOWED", "gif, jpeg, jpg, png, docx, doc, pdf");

// Allowed File Types for Savvion Checks
define("FILE_TYPES_ALLOWED_SAVVION", 
    "jpg,png,gif,bmp,jpeg,pdf,doc,docx,xls,csv,txt,pcx,svg,xlsx,xlm,msg,xps");

// Maximum File Size
define("FILE_SIZE_ALLOWED", "5 MB");
```

### PHP File Upload Settings

Edit `php.ini` or `.htaccess`:

**php.ini**:
```ini
upload_max_filesize = 5M
post_max_size = 8M
max_file_uploads = 20
```

**.htaccess** (for Apache):
```apache
php_value upload_max_filesize 5M
php_value post_max_size 8M
php_value max_file_uploads 20
```

### Upload Directory Configuration

Ensure writable permissions:

```bash
chmod 775 /var/www/html/verify/images/uploads
chmod 775 /var/www/html/verify/images/case_uploads
chmod 775 /var/www/html/verify/images/profile_pics
```

### File Upload Security

```php
// Validate file types
$allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif');
$file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if(!in_array($file_ext, $allowed_types)) {
    die('Invalid file type');
}

// Validate file size (5MB = 5242880 bytes)
if($_FILES['file']['size'] > 5242880) {
    die('File too large');
}

// Generate unique filename
$filename = uniqid() . '.' . $file_ext;
```

## Integration Settings

### Bitrix CRM Integration

```php
// Bitrix API URL
define("BITRIX_URL", "https://my.backcheck.io/rest_api.php");

// Bitrix Admin Mappings (in functions/bitrix/bitrix_functions.php)
$admin_mapping = array(
    'PK' => 529,  // Pakistan - Sharjeel
    'UAE' => 591, // UAE - Other admin
    'default' => 480  // Default - Sadia
);

// Bitrix Task Settings
$task_tat = 10;  // Days
$task_reminder = 1;  // Day before deadline
```

### Google Sheets API

```php
// Google API Configuration (in api_google.php)
$google_client_id = 'YOUR_CLIENT_ID';
$google_client_secret = 'YOUR_CLIENT_SECRET';
$google_redirect_uri = 'https://backcheck.io/verify/api_google.php';
$google_api_key = 'YOUR_API_KEY';
```

### WHMCS Integration

```php
// WHMCS API URL
define("WHMCS_APIURL", "https://backcheckgroup.com/support/includes/api.php");

// WHMCS API Credentials (store securely)
$whmcs_identifier = 'YOUR_API_IDENTIFIER';
$whmcs_secret = 'YOUR_API_SECRET';
```

### Savvion BPM Configuration

Configuration typically in `/functions/savvion/savvion_config.php`:

```php
// Savvion API Endpoint
$savvion_url = 'https://savvion.example.com/api';

// Savvion Credentials
$savvion_username = 'api_user';
$savvion_password = 'api_password';

// Workflow IDs
$workflows = array(
    'employment' => 'WF_EMP_001',
    'education' => 'WF_EDU_001'
);
```

## Security Settings

### Session Configuration

```php
// Session Settings (in config files)
ini_set('session.gc_maxlifetime', 3600);  // 1 hour
session_set_cookie_params(0);  // Expire on browser close
session_start();

// Regenerate session ID on login (recommended)
session_regenerate_id(true);
```

### Password Hashing

**Current (Legacy - MD5)**:
```php
$password_hash = md5($password);  // NOT RECOMMENDED
```

**Recommended (Modern)**:
```php
// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Verify password
if(password_verify($input_password, $stored_hash)) {
    // Password correct
}
```

### HTTPS Configuration

Force HTTPS in `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### CORS Configuration

If enabling API access from external domains:

```php
// Allow specific origins
header('Access-Control-Allow-Origin: https://trusted-domain.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

## Performance Tuning

### PHP Configuration

```ini
; Memory limit
memory_limit = 256M

; Execution time
max_execution_time = 300
max_input_time = 300

; Opcache (PHP 5.5+)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

### MySQL Optimization

```ini
# my.cnf / my.ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_type = 1
query_cache_size = 64M
```

### Apache Configuration

```apache
# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Enable caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

## Environment-Specific Configuration

### Development Environment

```php
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Development database
define("HOST", 'localhost');
define("DB", 'backglob_dev');
define("USER", 'dev_user');
define("PASS", 'dev_password');

// Development URLs
define("SITE_URL", 'http://localhost/verify/');
define("SURL", 'http://localhost/verify/');

// Disable external integrations in dev
define("ENABLE_BITRIX", false);
define("ENABLE_SAVVION", false);
?>
```

### Staging Environment

```php
<?php
// Limited error reporting
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/staging_errors.log');

// Staging database
define("HOST", 'staging-db.internal');
define("DB", 'backglob_staging');
define("USER", 'staging_user');
define("PASS", 'staging_password');

// Staging URLs
define("SITE_URL", 'https://staging.backcheck.io/verify/');

// Test mode for integrations
define("ENABLE_BITRIX", true);
define("BITRIX_TEST_MODE", true);
?>
```

### Production Environment

```php
<?php
// No error display
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/production_errors.log');

// Production database
define("HOST", 'production-db.internal');
define("DB", 'backglob_production');
define("USER", 'prod_user');
define("PASS", 'STRONG_PRODUCTION_PASSWORD');

// Production URLs
define("SITE_URL", 'https://backcheck.io/verify/');

// Enable all integrations
define("ENABLE_BITRIX", true);
define("ENABLE_SAVVION", true);
define("ENABLE_GOOGLE_SHEETS", true);
?>
```

## Custom Configuration

### Company-Specific Constants

```php
// Custom field labels
define("CLIENT_REF_NUM", "Client Reference Number");
define("ID_CARD_NUM", "ID Card Number");

// Add more as needed
define("PASSPORT_NUM", "Passport Number");
define("LICENSE_NUM", "License Number");
```

### Feature Toggles

```php
// Feature flags
define("ENABLE_BULK_UPLOAD", true);
define("ENABLE_SAVVION_CHECKS", true);
define("ENABLE_INSTANT_REPORTS", true);
define("ENABLE_LIVE_CHAT", false);
```

### Regional Settings

```php
// Timezone
date_default_timezone_set('Asia/Karachi');

// Currency
define("CURRENCY", "PKR");
define("CURRENCY_SYMBOL", "Rs.");

// Date format
define("DATE_FORMAT", "d-m-Y");
define("DATETIME_FORMAT", "d-m-Y H:i:s");
```

## Configuration Best Practices

1. **Never Commit Passwords**: Keep credentials out of version control
2. **Use Environment Variables**: Store sensitive data in environment variables
3. **Separate Configs**: Use different config files per environment
4. **Document Changes**: Comment configuration changes
5. **Backup Before Changes**: Always backup before modifying configs
6. **Test After Changes**: Verify system works after configuration changes
7. **Restrict Access**: Set proper file permissions (640 for config files)
8. **Regular Review**: Periodically review and update configurations

## Configuration Checklist

Before deploying:

- [ ] Database credentials configured correctly
- [ ] Site URLs updated for environment
- [ ] Email settings configured and tested
- [ ] File upload directories writable
- [ ] Integration credentials configured
- [ ] HTTPS enabled in production
- [ ] Error reporting appropriate for environment
- [ ] Session timeout configured
- [ ] Timezone set correctly
- [ ] Backup strategy in place
- [ ] Security settings reviewed
- [ ] Performance settings optimized

---

**Last Updated**: 2026  
**Version**: 3.4  
**Maintained by**: Background Check Development Team
