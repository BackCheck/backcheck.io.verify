# Development Guide - BackCheck.io Verify

## Table of Contents
- [Development Environment Setup](#development-environment-setup)
- [Code Structure](#code-structure)
- [Coding Standards](#coding-standards)
- [Development Workflow](#development-workflow)
- [Testing](#testing)
- [Debugging](#debugging)
- [Security Best Practices](#security-best-practices)
- [Modernization Recommendations](#modernization-recommendations)
- [Common Tasks](#common-tasks)

## Development Environment Setup

### Prerequisites

- PHP 5.6+ (PHP 7.4+ recommended for development)
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- Git for version control
- Code editor (VS Code, PHPStorm, etc.)

### Local Development Setup

#### 1. Clone Repository

```bash
git clone https://github.com/BackCheck/backcheck.io.verify.git
cd backcheck.io.verify
```

#### 2. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE backglob_dev;
USE backglob_dev;
SOURCE database_schema.sql;
EXIT;
```

#### 3. Configuration

Copy and edit configuration:

```bash
cp include/global_config.php.example include/global_config.php
```

Edit `include/global_config.php`:

```php
<?php
// Development settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

define("HOST", 'localhost');
define("DB", 'backglob_dev');
define("USER", 'dev_user');
define("PASS", 'dev_password');

define("SITE_URL", 'http://localhost/verify/');
define("SURL", 'http://localhost/verify/');

// Disable external integrations in development
define("ENABLE_BITRIX", false);
define("ENABLE_SAVVION", false);
?>
```

#### 4. Web Server Configuration

**Apache + XAMPP/WAMP**:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/backcheck.io.verify"
    ServerName backcheck.local
    
    <Directory "C:/xampp/htdocs/backcheck.io.verify">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**:
```nginx
server {
    listen 80;
    server_name backcheck.local;
    root /var/www/backcheck.io.verify;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Add to `/etc/hosts`:
```
127.0.0.1 backcheck.local
```

#### 5. File Permissions

```bash
chmod 775 images/uploads
chmod 775 images/case_uploads
chmod 775 images/profile_pics
chmod 640 include/global_config.php
```

## Code Structure

### Directory Organization

```
/verify/
├── index.php                    # Main entry point
├── actions.php                  # AJAX request handler
├── api_verify.php              # REST API endpoint
│
├── include/                     # Core configuration
│   ├── global_config.php       # Main config (sensitive)
│   ├── db_class.php            # Database wrapper
│   └── config_*.php            # Role-based configs
│
├── functions/                   # Business logic
│   ├── functions.php           # Core functions (374KB - needs refactoring)
│   ├── bitrix/                 # Bitrix integration
│   ├── savvion/                # Savvion workflow
│   └── class.phpmailer.php     # Email handling
│
├── include_pages/              # Page templates (400+ files)
│   ├── index_new_inc.php      # Dashboard
│   ├── applicant_inc.php      # Applicant pages
│   ├── checks_inc.php         # Check management
│   └── reports_inc.php        # Report generation
│
├── js/                         # JavaScript
│   ├── ajax_script-2.js       # AJAX functions
│   ├── js_functions-2.js      # Utility functions
│   └── encoder.js             # Encoding utilities
│
├── css/                        # Stylesheets
├── images/                     # Static assets
└── scripts/                    # External libraries
```

### Request Flow

```
User Request
    ↓
index.php (routing based on ?action= parameter)
    ↓
Load configuration (config_*.php based on user level)
    ↓
Include appropriate template (include_pages/*.php)
    ↓
Execute business logic (functions/functions.php)
    ↓
Query database (include/db_class.php)
    ↓
Render HTML response
```

### AJAX Flow

```
JavaScript (jQuery)
    ↓
$.ajax() → actions.php
    ↓
Load config_actions.php
    ↓
Route based on ?action= parameter
    ↓
Include appropriate handler (include_pages/*_inc.php)
    ↓
Process and return JSON response
```

## Coding Standards

### PHP Coding Style

**File Structure**:
```php
<?php
/**
 * Filename: example.php
 * Purpose: Brief description
 * Author: Your Name
 * Date: 2026-02-15
 */

// Include dependencies
include 'include/config.php';

// Constants
define("CONSTANT_NAME", "value");

// Functions
function functionName($param1, $param2) {
    // Function body
}

// Main execution
if(isset($_REQUEST['action'])) {
    // Handle action
}
?>
```

**Naming Conventions**:
- Variables: `$snake_case` (existing convention)
- Functions: `camelCase()` or `snake_case()` (be consistent)
- Constants: `UPPER_CASE`
- Classes: `PascalCase`
- Database tables: `lowercase_underscore`

**Indentation**:
- Use 4 spaces or 1 tab (be consistent)
- Opening brace on same line for functions
- Closing brace on new line

**Example**:
```php
function getUserById($user_id) {
    if($user_id > 0) {
        $query = "SELECT * FROM users WHERE id = " . intval($user_id);
        $result = mysql_query($query);
        
        if(mysql_num_rows($result) > 0) {
            return mysql_fetch_assoc($result);
        }
    }
    return false;
}
```

### SQL Best Practices

**Current (Legacy - Unsafe)**:
```php
// DON'T DO THIS - SQL Injection Risk
$query = "SELECT * FROM users WHERE username = '" . $_POST['username'] . "'";
$result = mysql_query($query);
```

**Recommended (Safe)**:
```php
// Use prepared statements with PDO or MySQLi
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_POST['username']]);
$user = $stmt->fetch();
```

**Input Sanitization** (interim solution):
```php
// Escape input (better than nothing, but not as good as prepared statements)
$username = mysql_real_escape_string($_POST['username']);
$query = "SELECT * FROM users WHERE username = '$username'";
```

### JavaScript/jQuery Style

```javascript
// Use descriptive variable names
var verificationId = 123;
var applicantName = "John Doe";

// Function declaration
function submitVerification(data) {
    $.ajax({
        url: 'actions.php',
        type: 'POST',
        data: {
            action: 'submit_check',
            data: data
        },
        success: function(response) {
            handleResponse(response);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}

// Event handling
$(document).ready(function() {
    $('#submit-btn').on('click', function(e) {
        e.preventDefault();
        submitVerification(getFormData());
    });
});
```

### HTML/Template Style

```php
<!-- Separate PHP logic from HTML where possible -->
<?php
$verifications = getVerifications();
?>

<div class="verification-list">
    <?php foreach($verifications as $ver): ?>
        <div class="verification-item">
            <h3><?= htmlspecialchars($ver['applicant_name']) ?></h3>
            <p>Status: <?= htmlspecialchars($ver['status']) ?></p>
            <p>Date: <?= date('d-m-Y', strtotime($ver['created_date'])) ?></p>
        </div>
    <?php endforeach; ?>
</div>
```

## Development Workflow

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/add-new-report

# Make changes and commit frequently
git add .
git commit -m "Add: New monthly report feature"

# Push to remote
git push origin feature/add-new-report

# Create pull request for review
# After approval, merge to main branch
```

### Commit Message Guidelines

**Format**:
```
Type: Brief description (50 chars or less)

More detailed explanation if needed (wrap at 72 characters).
Explain what changed and why, not how.
```

**Types**:
- `Add:` - New feature
- `Fix:` - Bug fix
- `Update:` - Update existing feature
- `Refactor:` - Code refactoring
- `Doc:` - Documentation changes
- `Style:` - Code style changes
- `Security:` - Security improvements

**Examples**:
```bash
git commit -m "Add: Bulk verification upload feature"
git commit -m "Fix: File upload validation error for PDF files"
git commit -m "Security: Implement prepared statements in user module"
git commit -m "Refactor: Extract email functions into separate class"
```

### Code Review Checklist

Before submitting for review:

- [ ] Code follows project coding standards
- [ ] All functions have descriptive names
- [ ] Input validation implemented
- [ ] SQL injection prevention (prepared statements or escaping)
- [ ] XSS prevention (output escaping)
- [ ] Error handling implemented
- [ ] Comments added for complex logic
- [ ] No hardcoded credentials
- [ ] Tested in local environment
- [ ] No debug code (var_dump, print_r, etc.)

## Testing

### Manual Testing

#### Test Checklist for New Features

1. **Functional Testing**:
   - Feature works as expected
   - All user flows complete successfully
   - Error messages display correctly

2. **UI Testing**:
   - Layout displays correctly
   - Responsive design works on mobile
   - Forms validate input properly

3. **Integration Testing**:
   - External API calls work
   - Database operations succeed
   - File uploads/downloads work

4. **Browser Testing**:
   - Test in Chrome, Firefox, Safari
   - Check console for JavaScript errors

### Database Testing

```sql
-- Test data creation
INSERT INTO ver_data (client_ref_num, applicant_name, check_type, status)
VALUES ('TEST-001', 'Test Applicant', 'employment', 'submitted');

-- Verify insertion
SELECT * FROM ver_data WHERE client_ref_num = 'TEST-001';

-- Cleanup after testing
DELETE FROM ver_data WHERE client_ref_num LIKE 'TEST-%';
```

### API Testing

Using cURL:

```bash
# Test API endpoint
curl -X POST 'http://localhost/verify/api_verify.php?action=create_check' \
  -H 'Authorization: Bearer test_token_123' \
  -H 'Content-Type: application/json' \
  -d '{
    "client_ref": "TEST-001",
    "applicant_name": "Test User",
    "check_type": "employment",
    "company_id": 87
  }'
```

Using Postman:
1. Create a new request
2. Set method to POST
3. Add Authorization header
4. Add request body (JSON)
5. Send and verify response

## Debugging

### Enable Error Reporting

```php
// In development config
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/errors.log');
```

### Debugging Techniques

**1. var_dump() and print_r()**:
```php
// Output variable contents
var_dump($user_data);
print_r($verification_array);

// Pretty print
echo '<pre>' . print_r($data, true) . '</pre>';
```

**2. Error Logging**:
```php
// Write to error log
error_log("Debug: User ID = " . $user_id);
error_log("Database query: " . $query);

// Log arrays
error_log("Post Data: " . print_r($_POST, true));
```

**3. MySQL Query Debugging**:
```php
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysql_query($query);

if(!$result) {
    error_log("MySQL Error: " . mysql_error());
    error_log("Query: " . $query);
}
```

**4. AJAX Debugging**:
```javascript
// In JavaScript
$.ajax({
    url: 'actions.php',
    data: {action: 'test'},
    success: function(response) {
        console.log('Response:', response);
    },
    error: function(xhr, status, error) {
        console.error('Error:', error);
        console.log('Response:', xhr.responseText);
    }
});
```

**5. Network Debugging**:
- Use browser DevTools (F12)
- Network tab to see AJAX requests
- Console tab for JavaScript errors
- Application tab for session/cookie inspection

### Common Issues and Solutions

**Issue**: White screen (no error message)
**Solution**: Enable `display_errors` in php.ini or check error logs

**Issue**: Database connection failed
**Solution**: Verify credentials in global_config.php, check MySQL service

**Issue**: File upload not working
**Solution**: Check directory permissions, PHP upload settings

**Issue**: Session not persisting
**Solution**: Check session.save_path permissions, ensure session_start() called

## Security Best Practices

### Input Validation

```php
// Validate and sanitize user input
function validateInput($data, $type) {
    switch($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
        case 'string':
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        default:
            return false;
    }
}

// Usage
$email = validateInput($_POST['email'], 'email');
$user_id = validateInput($_POST['user_id'], 'int');
$name = validateInput($_POST['name'], 'string');
```

### Output Escaping

```php
// Always escape output to prevent XSS
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// In templates
<p><?= htmlspecialchars($applicant_name) ?></p>
```

### File Upload Security

```php
function secureFileUpload($file) {
    // Validate file type
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if(!in_array($file_ext, $allowed_types)) {
        return ['error' => 'Invalid file type'];
    }
    
    // Validate file size (5MB)
    if($file['size'] > 5242880) {
        return ['error' => 'File too large'];
    }
    
    // Generate unique filename
    $new_filename = uniqid('file_', true) . '.' . $file_ext;
    
    // Move to secure directory
    $upload_path = '/secure/uploads/' . $new_filename;
    move_uploaded_file($file['tmp_name'], $upload_path);
    
    return ['success' => true, 'filename' => $new_filename];
}
```

### Password Security

```php
// NEVER store plain text passwords
// Use password_hash() (PHP 5.5+)

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Store $password_hash in database

// Verify password
if(password_verify($input_password, $stored_hash)) {
    // Password correct
} else {
    // Password incorrect
}
```

## Modernization Recommendations

### Priority 1: Security Updates

**1. Migrate from mysql_* to PDO**:
```php
// Old (deprecated)
$result = mysql_query("SELECT * FROM users WHERE id = $id");

// New (PDO)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll();
```

**2. Implement Password Hashing**:
```php
// Replace MD5 with bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);
```

**3. Add CSRF Protection**:
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate token
if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed');
}
```

### Priority 2: Code Organization

**1. Extract Functions into Classes**:
```php
// Instead of 374KB functions.php, create:
class UserManager {
    public function getUser($id) { }
    public function createUser($data) { }
}

class VerificationManager {
    public function createVerification($data) { }
    public function updateStatus($id, $status) { }
}
```

**2. Implement Autoloading**:
```php
// composer.json
{
    "autoload": {
        "psr-4": {
            "BackCheck\\": "src/"
        }
    }
}
```

**3. Use Environment Variables**:
```php
// .env file
DB_HOST=localhost
DB_NAME=backglob_db
DB_USER=user
DB_PASS=password

// Load with vlucas/phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db_host = $_ENV['DB_HOST'];
```

### Priority 3: Framework Migration

Consider migrating to a modern PHP framework:
- **Laravel**: Full-featured MVC framework
- **Symfony**: Enterprise-grade framework
- **CodeIgniter 4**: Lightweight framework

## Common Tasks

### Adding a New Page

1. Create template in `include_pages/`:
```php
// include_pages/new_feature_inc.php
<?php
if(!isset($_SESSION['userid'])) {
    die('Unauthorized');
}

// Page logic here
?>
<div class="container">
    <h1>New Feature</h1>
    <!-- Page content -->
</div>
```

2. Add routing in `index.php`:
```php
if($_REQUEST['action'] == 'new_feature') {
    include 'include_pages/new_feature_inc.php';
}
```

### Adding an AJAX Endpoint

1. Create handler in `include_pages/`:
```php
// include_pages/ajax_new_action_inc.php
<?php
$data = json_decode(file_get_contents('php://input'), true);

// Process data
$result = processData($data);

// Return JSON
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'data' => $result
]);
?>
```

2. Add routing in `actions.php`:
```php
if($_REQUEST['action'] == 'new_action') {
    include 'include_pages/ajax_new_action_inc.php';
}
```

3. Call from JavaScript:
```javascript
$.ajax({
    url: 'actions.php',
    type: 'POST',
    data: {action: 'new_action', param: 'value'},
    success: function(response) {
        console.log(response);
    }
});
```

### Adding a Database Table

```sql
CREATE TABLE IF NOT EXISTS new_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status TINYINT DEFAULT 1,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Resources

### Documentation
- [PHP Manual](https://www.php.net/manual/en/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [jQuery Documentation](https://api.jquery.com/)

### Tools
- **PHPStorm**: IDE for PHP development
- **VS Code**: Lightweight code editor
- **Postman**: API testing
- **MySQL Workbench**: Database management
- **Git**: Version control

### Learning Resources
- [PHP The Right Way](https://phptherightway.com/)
- [OWASP Security Guidelines](https://owasp.org/)
- [PSR Standards](https://www.php-fig.org/psr/)

---

**Last Updated**: 2026  
**Version**: 3.4  
**Maintained by**: Background Check Development Team
