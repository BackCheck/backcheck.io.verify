# Architecture Documentation - BackCheck.io Verify

## Table of Contents
- [System Overview](#system-overview)
- [Technology Stack](#technology-stack)
- [Application Architecture](#application-architecture)
- [Database Design](#database-design)
- [Security Architecture](#security-architecture)
- [Integration Architecture](#integration-architecture)
- [File Structure](#file-structure)
- [Request Flow](#request-flow)
- [Authentication & Authorization](#authentication--authorization)
- [Performance Considerations](#performance-considerations)

## System Overview

BackCheck.io Verify is a **monolithic PHP web application** built on a traditional LAMP stack architecture. The system follows a **procedural programming paradigm** with heavy use of include files and global functions.

### Architecture Pattern
- **Pattern**: Monolithic, procedural PHP
- **Design**: Include-based modular architecture
- **Data Access**: Direct MySQL queries (legacy mysql_* extension)
- **Session Management**: PHP native sessions
- **State Management**: Server-side session storage

### Key Characteristics
- **Legacy Codebase**: Uses deprecated PHP mysql_* functions
- **High Coupling**: Tight coupling between layers
- **Action-Based Routing**: URL parameter-driven page routing
- **Mixed Concerns**: HTML, PHP, and SQL often intermixed
- **Global State**: Heavy reliance on global variables and sessions

## Technology Stack

### Server-Side
```
┌─────────────────────────────────────┐
│         Web Server Layer            │
│    Apache 2.4+ / Nginx 1.14+        │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│      Application Layer (PHP)        │
│         PHP 5.6+ / 7.x              │
│  Extensions: mysql, gd, curl, xml   │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│        Database Layer               │
│         MySQL 5.7+ / MariaDB        │
│       Character Set: UTF-8          │
└─────────────────────────────────────┘
```

### Frontend
- **JavaScript Library**: jQuery 1.x / 2.x
- **UI Framework**: jQuery UI, Bootstrap 3.x
- **File Upload**: Blueimp jQuery File Upload (v9.9.3)
- **Rich Text**: TinyMCE editor
- **AJAX**: jQuery Ajax for async operations

### Third-Party Services
- **CRM**: Bitrix24 (REST API integration)
- **BPM**: Savvion Business Process Management
- **Cloud Storage**: Google Sheets API
- **Billing**: WHMCS API
- **Email**: PHPMailer SMTP

## Application Architecture

### Layered Architecture

```
┌────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
│  (index.php, include_pages/*.php, dashboard/*.php)         │
│  - HTML Templates                                           │
│  - jQuery UI Components                                     │
│  - Form Handling                                            │
└────────────────────────────────────────────────────────────┘
                           ↓
┌────────────────────────────────────────────────────────────┐
│                   Business Logic Layer                      │
│        (functions/functions.php, actions.php)              │
│  - Verification Processing                                  │
│  - Workflow Management                                      │
│  - Report Generation                                        │
│  - Email Notifications                                      │
└────────────────────────────────────────────────────────────┘
                           ↓
┌────────────────────────────────────────────────────────────┐
│                 Data Access Layer                           │
│              (include/db_class.php)                         │
│  - Database Connections                                     │
│  - Query Execution                                          │
│  - Result Processing                                        │
└────────────────────────────────────────────────────────────┘
                           ↓
┌────────────────────────────────────────────────────────────┐
│                   Integration Layer                         │
│  (functions/bitrix/, functions/savvion/, api_*.php)        │
│  - Bitrix CRM Integration                                   │
│  - Savvion BPM Integration                                  │
│  - Google Sheets Integration                                │
│  - External API Calls                                       │
└────────────────────────────────────────────────────────────┘
```

### Component Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                        Client Browser                        │
│  (HTML/CSS/JavaScript - jQuery, Bootstrap)                  │
└─────────────────────────────────────────────────────────────┘
         ↑                           ↓
         │                           │
    HTTP Response               HTTP Request
         │                           │
         ↓                           ↑
┌─────────────────────────────────────────────────────────────┐
│                    Application Server                        │
│                                                              │
│  ┌────────────┐  ┌────────────┐  ┌─────────────┐          │
│  │  index.php │  │actions.php │  │ api_verify  │          │
│  │  (Router)  │  │(AJAX Hub)  │  │    .php     │          │
│  └────────────┘  └────────────┘  └─────────────┘          │
│         │               │                │                  │
│         └───────────────┴────────────────┘                  │
│                         ↓                                    │
│  ┌──────────────────────────────────────────────┐          │
│  │         Configuration Layer                   │          │
│  │  (include/config_*.php, global_config.php)   │          │
│  └──────────────────────────────────────────────┘          │
│                         ↓                                    │
│  ┌──────────────────────────────────────────────┐          │
│  │           Core Functions                      │          │
│  │  (functions/functions.php - 374KB)           │          │
│  └──────────────────────────────────────────────┘          │
│                         ↓                                    │
│  ┌──────────────────────────────────────────────┐          │
│  │         Database Access Layer                 │          │
│  │      (include/db_class.php)                  │          │
│  └──────────────────────────────────────────────┘          │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                    MySQL Database                            │
│                   (backglob_db)                              │
└─────────────────────────────────────────────────────────────┘
         ↑
         │
┌─────────────────────────────────────────────────────────────┐
│                External Integrations                         │
│  ┌──────────┐  ┌──────────┐  ┌───────────┐  ┌─────────┐  │
│  │ Bitrix24 │  │ Savvion  │  │  Google   │  │  WHMCS  │  │
│  │   CRM    │  │   BPM    │  │  Sheets   │  │   API   │  │
│  └──────────┘  └──────────┘  └───────────┘  └─────────┘  │
└─────────────────────────────────────────────────────────────┘
```

## Database Design

### Database Schema Overview

```sql
-- Core Tables
users                   -- User accounts and authentication
ver_data                -- Main verification records
checks                  -- Verification check assignments
companies               -- Client companies
cases                   -- Case management

-- Workflow Tables
savvion_checks          -- Savvion workflow instances
bitrix_tasks            -- Bitrix task tracking
auth_token              -- API authentication tokens

-- Reference Tables
categories              -- Check categories/types
statuses                -- Status definitions
countries               -- Country data
ratings                 -- Rating data

-- Supporting Tables
email_logs              -- Email notification logs
cron_logs               -- Automated task logs
user_sessions           -- Session tracking
uploads                 -- File upload tracking
```

### Key Table Structures

#### users Table
```sql
users
├── id (PK)
├── username
├── password (MD5 hash - legacy)
├── email
├── level (1-14, role identifier)
├── company_id (FK)
├── status (1=active, 0=inactive)
├── created_date
└── last_login
```

#### ver_data Table (Main verification records)
```sql
ver_data
├── id (PK)
├── client_ref_num
├── applicant_name
├── check_type
├── status
├── assigned_to (FK -> users.id)
├── company_id (FK -> companies.id)
├── created_date
├── modified_date
├── tat_date (turnaround time deadline)
└── ... (50+ additional fields)
```

#### checks Table
```sql
checks
├── id (PK)
├── ver_data_id (FK -> ver_data.id)
├── check_category
├── analyst_id (FK -> users.id)
├── status
├── assigned_date
└── completed_date
```

### Data Flow

```
Application Submission
        ↓
  Insert ver_data
        ↓
  Create checks
        ↓
  Assign to analyst (users)
        ↓
  Update status progressively
        ↓
  Generate final report
```

## Security Architecture

### Authentication Flow

```
User Login Request
    ↓
Validate Credentials (MD5 hash - legacy)
    ↓
Check User Status & Level
    ↓
Create PHP Session
    ↓
Store Session Variables
    - $_SESSION['userid']
    - $_SESSION['username']
    - $_SESSION['level']
    - $_SESSION['company_id']
    ↓
Redirect to Dashboard
```

### Authorization Model

**Role-Based Access Control (RBAC)**

```php
// Level-based permissions
$LEVEL = $_SESSION['level'];

switch($LEVEL) {
    case 1:  // Super Admin - Full access
    case 2:  // Admin - User/client management
    case 3:  // Team Lead - Team management
    case 4:  // Senior Analyst - Complex cases
    case 5:  // Analyst - Standard cases
    case 6:  // Quality Control - Review access
    case 7:  // Client Admin - Client portal
    // ... etc
}
```

### Security Concerns (Legacy)

⚠️ **Known Security Issues**:
1. **MD5 Password Hashing**: Weak, should use bcrypt/password_hash()
2. **Direct MySQL Queries**: Using deprecated mysql_* functions
3. **SQL Injection Risk**: Lack of prepared statements
4. **XSS Vulnerabilities**: Limited output escaping
5. **CSRF Protection**: No CSRF tokens implemented
6. **Session Fixation**: No session regeneration on login

**Recommended Improvements**:
- Migrate to PDO with prepared statements
- Implement password_hash() / password_verify()
- Add CSRF token validation
- Implement output escaping (htmlspecialchars)
- Add input validation and sanitization
- Enable HTTPS only with HSTS headers

## Integration Architecture

### Bitrix CRM Integration

```
BackCheck Application
        ↓
Bitrix Integration Layer
(functions/bitrix/bitrix_functions.php)
        ↓
REST API Call
https://my.backcheck.io/rest_api.php
        ↓
Bitrix24 CRM
    ├── Lead Creation (insertleads2)
    ├── Task Management (add_task, task_del)
    ├── Work Group Assignment
    └── Status Updates
```

**Key Functions**:
- `insertleads2()`: Create leads with auto-assignment by country
- `add_task()`: Create tasks with TAT and reminders
- `getworkgroup()`: Retrieve work group information

### Savvion BPM Integration

```
Verification Request
        ↓
Savvion Workflow Layer
(functions/savvion/savvion_functions.php)
        ↓
Savvion BPM System
    ├── Workflow Initiation
    ├── Task Assignment
    ├── Process Tracking
    ├── Approval Routing
    └── Completion Notification
```

**Workflow Types**:
- Employment Verification Workflow
- Education Verification Workflow
- Complex Multi-Step Verifications

### Google Sheets Integration

```
Report Generation Request
        ↓
Google Sheets API Layer
(api_google.php)
        ↓
Google Sheets API v4
        ↓
Spreadsheet Creation/Update
    ├── Export verification data
    ├── Generate analytics reports
    └── Timeline analysis
```

## File Structure

### Directory Organization

```
/verify/
├── index.php                    # Main entry point
├── actions.php                  # AJAX handler
├── api_verify.php              # REST API endpoint
│
├── include/                     # Core configuration
│   ├── global_config.php       # Database & constants
│   ├── config_index.php        # Index configuration
│   ├── config_actions.php      # Actions configuration
│   ├── config_client.php       # Client portal config
│   ├── db_class.php            # Database wrapper
│   ├── paginator.class.php     # Pagination utility
│   └── search_cls.php          # Search functionality
│
├── functions/                   # Business logic
│   ├── functions.php           # Core functions (374KB)
│   ├── class.phpmailer.php     # Email handler
│   ├── bitrix/                 # Bitrix integration
│   ├── savvion/                # Savvion integration
│   ├── dashboard/              # Dashboard functions
│   ├── advance_search/         # Search functions
│   ├── bulkupload/            # Bulk upload handlers
│   └── credits/                # Credits management
│
├── include_pages/              # Page templates (400+ files)
│   ├── index_new_inc.php      # Main dashboard
│   ├── applicant_inc.php      # Applicant pages
│   ├── checks_inc.php         # Check management
│   ├── reports_inc.php        # Report generation
│   ├── rating_inc.php         # Rating pages
│   └── ... (many more)
│
├── dashboard/                  # Dashboard modules
│   ├── document_head.php      # Dashboard header
│   └── ... (dashboard components)
│
├── js/                         # JavaScript files
│   ├── ajax_script-2.js       # AJAX functions
│   ├── js_functions-2.js      # Utility functions
│   └── encoder.js             # Encoding utilities
│
├── css/                        # Stylesheets
├── images/                     # Static images
│   ├── uploads/               # User uploads
│   ├── case_uploads/          # Case documents
│   └── profile_pics/          # Profile images
│
├── scripts/                    # External libraries
│   └── vendor/                # Third-party libraries
│       └── tinymce/           # Rich text editor
│
└── formbuilder/               # Form builder module
```

## Request Flow

### Page Request Flow

```
1. User Request
   URL: https://backcheck.io/verify/?action=dashboard
        ↓
2. index.php
   - Include config_index.php
   - Authenticate user
   - Check session
        ↓
3. Route Based on Action
   - Load config_*.php based on user level
   - Include document_head.php
   - Load appropriate include_pages/*.php
        ↓
4. Page Processing
   - Execute business logic
   - Query database
   - Generate HTML output
        ↓
5. Response
   - Render page with sidebar
   - Include JavaScript
   - Send to browser
```

### AJAX Request Flow

```
1. Client JavaScript
   $.ajax({ url: 'actions.php', data: {action: 'submit_check'} })
        ↓
2. actions.php
   - Include config_actions.php
   - Authenticate user
   - Validate action parameter
        ↓
3. Action Routing
   if($_REQUEST['action'] == 'submit_check') {
       include 'include_pages/submit_check_inc.php';
   }
        ↓
4. Process Request
   - Execute business logic
   - Update database
   - Call external APIs (Bitrix, Savvion)
        ↓
5. JSON Response
   echo json_encode(['status' => 'success', 'data' => $result]);
        ↓
6. Client Callback
   success: function(response) { /* handle response */ }
```

### API Request Flow

```
1. External API Call
   POST /api_verify.php
   Authorization: Bearer {token}
        ↓
2. Token Validation
   - Query auth_token table
   - Verify token validity
   - Check expiration
        ↓
3. Action Processing
   switch($_REQUEST['action']) {
       case 'create_check':
           // Create verification
       case 'get_status':
           // Get status
   }
        ↓
4. Business Logic
   - Call functions from functions.php
   - Database operations
        ↓
5. JSON Response
   {
       "status": "success",
       "data": { ... },
       "message": "Check created successfully"
   }
```

## Authentication & Authorization

### Session Management

```php
// Start session
session_start();

// Set session variables on login
$_SESSION['userid'] = $user_id;
$_SESSION['username'] = $username;
$_SESSION['level'] = $user_level;
$_SESSION['company_id'] = $company_id;
$_SESSION['name'] = $full_name;

// Check authentication on each request
if(!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

// Check authorization
$LEVEL = $_SESSION['level'];
if($LEVEL != 1 && $LEVEL != 2) {
    die("Access denied");
}
```

### Permission Matrix

| Level | Role | Permissions |
|-------|------|-------------|
| 1 | Super Admin | All permissions |
| 2 | Admin | User/client management, reports |
| 3 | Team Lead | Team management, assignment, review |
| 4 | Senior Analyst | Complex verifications, mentoring |
| 5 | Analyst | Standard verifications |
| 6 | Quality Control | Review, approve/reject |
| 7 | Client Admin | Client portal admin |
| 8 | Client User | Submit requests, view status |
| 9 | Finance | Billing, invoicing |
| 10-14 | Custom | Specialized roles |

## Performance Considerations

### Current Limitations
- **N+1 Query Problem**: Multiple database queries in loops
- **Large File Sizes**: functions.php is 374KB (monolithic)
- **No Caching**: No query caching or page caching
- **Session Storage**: File-based sessions (slow at scale)
- **No CDN**: Static assets served from application server

### Optimization Opportunities
1. **Database**:
   - Add indexes on frequently queried columns
   - Implement query caching
   - Use connection pooling
   
2. **Application**:
   - Implement opcode caching (OPcache)
   - Break down large functions file
   - Add result caching (Redis/Memcached)
   
3. **Frontend**:
   - Minify CSS/JavaScript
   - Implement CDN for static assets
   - Enable browser caching
   
4. **Infrastructure**:
   - Load balancing for horizontal scaling
   - Database replication (master-slave)
   - File storage on S3/object storage

## Scalability Considerations

### Current Architecture Limitations
- **Monolithic Design**: Tight coupling makes scaling difficult
- **Shared Session State**: File-based sessions don't scale horizontally
- **Direct Database Access**: No abstraction layer for sharding
- **Synchronous Processing**: No background job processing

### Recommended Improvements
1. **Microservices**: Extract integrations into separate services
2. **Message Queue**: Implement RabbitMQ/Redis for async tasks
3. **API Gateway**: Centralized API management
4. **Service-Oriented Architecture**: Break monolith into services
5. **Containerization**: Docker for consistent deployment
6. **Orchestration**: Kubernetes for container management

---

**Version**: 3.4  
**Last Updated**: 2026  
**Maintained by**: Background Check Development Team
