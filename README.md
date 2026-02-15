# BackCheck.io Verify

> A comprehensive background verification and document verification management system

[![License](https://img.shields.io/badge/license-Proprietary-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-5.6%2B-777BB4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1.svg)](https://www.mysql.com/)

## 🔍 Overview

**BackCheck.io Verify** is a full-featured background verification platform designed to streamline the process of employment verification, education verification, health checks, and other background screening services. The system provides a complete workflow from initial application submission through verification, quality control, and final report generation.

**Live Application**: [https://backcheck.io/verify](https://backcheck.io/verify)

## ✨ Key Features

### Core Functionality
- **Multi-Type Verification Support**
  - Employment Verification
  - Education Verification  
  - Health Verification
  - Identity Verification
  - Address Verification
  - Reference Checks
  - Criminal Record Checks

### Workflow Management
- **Role-Based Access Control**: 14+ user levels including Admin, Team Lead, Analysts, Quality Control, Clients
- **Automated Task Assignment**: Intelligent distribution based on workload and expertise
- **Multi-Stage Approval Process**: Initial Investigation → Analysis → Quality Control → Final Report
- **Real-Time Status Tracking**: Complete visibility of verification progress
- **SLA Management**: Configurable turnaround time (TAT) tracking with alerts

### Integration Capabilities
- **Bitrix CRM Integration**: Automatic lead and task creation
- **Savvion BPM**: Structured workflow management for complex verifications
- **Google Sheets API**: Automated reporting and data export
- **WHMCS Integration**: Customer and billing management
- **Email Notifications**: Automated stakeholder communication via PHPMailer

### Document Management
- **Multi-File Upload**: Support for PDF, DOC, DOCX, JPG, PNG (up to 5MB per file)
- **Bulk Upload**: Batch processing for high-volume operations
- **OCR Support**: Automated text extraction from documents
- **Secure Storage**: Document versioning and audit trails

### Reporting & Analytics
- **Dashboard Views**: Customized dashboards for each user role
- **Case Reports**: Detailed verification reports with evidence
- **Certificates**: Official verification certificates
- **Analytics**: Daily, weekly, and monthly performance reports
- **Export Options**: Excel, PDF, CSV formats

## 🏗️ Architecture

### Technology Stack
- **Backend**: PHP 5.6+ with MySQL database
- **Frontend**: jQuery, jQuery UI, Bootstrap
- **File Upload**: Blueimp jQuery File Upload (v9.9.3)
- **PDF Generation**: PHP PDF libraries
- **Email**: PHPMailer class

### System Components

```
/verify/
├── index.php              # Main application entry point
├── actions.php            # AJAX request handler
├── api_verify.php         # Token-based REST API
├── include/               # Core configuration and database classes
│   ├── global_config.php  # System constants and database config
│   ├── config_*.php       # Role-based configuration files
│   └── db_class.php       # MySQL database wrapper
├── functions/             # Business logic and integrations
│   ├── functions.php      # Core utility functions (374KB)
│   ├── bitrix/            # Bitrix CRM integration
│   └── savvion/           # Savvion workflow functions
├── include_pages/         # Page templates (400+ files)
├── dashboard/             # Dashboard modules
├── js/                    # JavaScript files
├── css/                   # Stylesheets
└── images/                # Static assets
```

## 🚀 Quick Start

### Prerequisites
- PHP 5.6 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- 256MB+ PHP memory limit
- File upload enabled (5MB+ max file size)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/BackCheck/backcheck.io.verify.git
   cd backcheck.io.verify
   ```

2. **Configure database**
   - Create a MySQL database
   - Import the database schema (contact admin for schema)
   - Update `/include/global_config.php` with your database credentials

3. **Configure web server**
   - Point document root to the project directory
   - Ensure `.htaccess` is enabled for Apache
   - Set proper file permissions (writable upload directories)

4. **Update configuration**
   - Edit `/include/global_config.php` with your site URL and settings
   - Configure email settings for notifications
   - Set up API keys for integrations (Bitrix, Google Sheets, etc.)

5. **Access the application**
   - Navigate to your configured URL
   - Default admin credentials (contact system administrator)

For detailed installation instructions, see [INSTALLATION.md](INSTALLATION.md)

## 📚 Documentation

> **[📖 Complete Documentation Index](DOCS_INDEX.md)** - Quick access to all documentation

### Core Documentation
- **[Installation Guide](INSTALLATION.md)** - Complete setup instructions (13 KB)
- **[Architecture Documentation](ARCHITECTURE.md)** - Technical architecture details (23 KB)
- **[API Documentation](API_DOCUMENTATION.md)** - API endpoints and usage (17 KB)
- **[User Guide](USER_GUIDE.md)** - User roles, features, and workflows (16 KB)
- **[Configuration Guide](CONFIGURATION.md)** - System configuration options (15 KB)
- **[Integration Guide](INTEGRATIONS.md)** - Third-party integration setup (18 KB)
- **[Development Guide](DEVELOPMENT.md)** - Development guidelines and best practices (18 KB)

**Total**: 8 comprehensive guides | ~128 KB of documentation | Last Updated: 2026-02-15

## 👥 User Roles

The system supports multiple user levels with different permissions:

| Level | Role | Description |
|-------|------|-------------|
| 1 | Super Admin | Full system access and configuration |
| 2 | Admin | User and client management |
| 3 | Team Lead | Team management and review |
| 4 | Senior Analyst | Complex verification cases |
| 5 | Analyst | Standard verification processing |
| 6 | Quality Control | Report review and approval |
| 7 | Client Admin | Client portal administration |
| 8 | Client User | Submit and track verifications |
| 9 | Finance | Billing and invoicing |
| 10-14 | Specialized Roles | Custom role assignments |

## 🔌 API Integration

The system provides REST API endpoints for external integrations:

```php
// Token-based authentication
POST /api_verify.php
Headers: Authorization: Bearer {token}

// Example: Create verification request
POST /api_verify.php?action=create_check
Body: {
  "client_ref": "EMP001",
  "applicant_name": "John Doe",
  "check_type": "employment",
  "documents": [...]
}
```

See [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for complete API reference.

## 🔧 Configuration

Key configuration constants in `/include/global_config.php`:

```php
define("SITE_URL", 'https://backcheck.io/verify/');
define("DB", 'backglob_db');
define("TAT", 10);  // Turnaround time in days
define("FILE_SIZE_ALLOWED", "5 MB");
define("BITRIX_URL", "https://my.backcheck.io/rest_api.php");
```

## 🔐 Security Considerations

- Session-based authentication
- Role-based access control (RBAC)
- Input validation and sanitization
- SQL injection prevention (requires PDO migration)
- File upload restrictions
- HTTPS enforcement recommended

**Note**: This is a legacy codebase using deprecated `mysql_*` functions. Migration to PDO/MySQLi is recommended for enhanced security.

## 📊 Database

- **Database**: MySQL (backglob_db)
- **Key Tables**: 
  - `users` - User accounts and roles
  - `ver_data` - Verification records
  - `checks` - Check assignments
  - `auth_token` - API authentication
  - Additional tables for Bitrix/Savvion integration

## 🤝 Contributing

This is a proprietary system. For contribution guidelines, contact the development team.

## 📝 License

Proprietary - All rights reserved by Background Check (Private) Limited

## 📞 Support

- **Email**: support@backcheckgroup.com
- **Phone**: +92-21-32863920-31
- **Address**: 3rd Floor, GSA House, 19 Timber Pond, Karachi, Pakistan
- **Website**: https://backcheckgroup.com

## 🏢 About Background Check Group

Background Check (Private) Limited is a leading background verification service provider based in Karachi, Pakistan. We provide comprehensive screening solutions for employment, education, identity, and other verification needs.

---

**Version**: 3.4  
**Last Updated**: 2026  
**Maintained by**: Background Check Development Team
