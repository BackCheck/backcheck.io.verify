# Integration Guide - BackCheck.io Verify

## Table of Contents
- [Overview](#overview)
- [Bitrix CRM Integration](#bitrix-crm-integration)
- [Savvion BPM Integration](#savvion-bpm-integration)
- [Google Sheets Integration](#google-sheets-integration)
- [WHMCS Integration](#whmcs-integration)
- [Third-Party APIs](#third-party-apis)
- [Custom Integrations](#custom-integrations)
- [Troubleshooting](#troubleshooting)

## Overview

BackCheck.io Verify integrates with multiple external systems to automate workflows, sync data, and enhance functionality. This guide covers the setup and usage of each integration.

### Integration Architecture

```
┌─────────────────────────────────────┐
│   BackCheck.io Verify Application   │
│                                      │
│  ┌──────────────────────────────┐  │
│  │   Integration Layer          │  │
│  │  /functions/bitrix/          │  │
│  │  /functions/savvion/         │  │
│  │  /api_google.php             │  │
│  └──────────────────────────────┘  │
└─────────────────────────────────────┘
         │         │          │
         ▼         ▼          ▼
┌─────────┐  ┌──────────┐  ┌─────────┐
│ Bitrix  │  │ Savvion  │  │ Google  │
│   CRM   │  │   BPM    │  │ Sheets  │
└─────────┘  └──────────┘  └─────────┘
```

## Bitrix CRM Integration

### Overview

Bitrix24 is a CRM platform used for managing leads, tasks, and customer relationships. The integration automatically creates leads and tasks in Bitrix when verification requests are submitted.

### Configuration

#### 1. Bitrix API Setup

**In Bitrix24**:
1. Go to **Settings** → **Applications**
2. Create a new **REST API** application
3. Note the **Webhook URL** or **API credentials**
4. Set permissions for Leads and Tasks

**In BackCheck.io Verify**:

Edit `/include/global_config.php`:
```php
define("BITRIX_URL", "https://my.backcheck.io/rest_api.php");
```

#### 2. Admin Mapping Configuration

Edit `/functions/bitrix/bitrix_functions.php`:

```php
// Map countries to Bitrix admin IDs
function getAdminByCountry($country) {
    $admin_mapping = array(
        'Pakistan' => 529,    // Sharjeel
        'India' => 529,       // Sharjeel
        'UAE' => 591,         // Other admin
        'Saudi Arabia' => 591,
        'default' => 480      // Sadia (default)
    );
    
    return isset($admin_mapping[$country]) 
        ? $admin_mapping[$country] 
        : $admin_mapping['default'];
}
```

### Key Functions

#### insertleads2() - Create Lead

Creates a new lead in Bitrix CRM with auto-assignment based on country.

```php
function insertleads2($data) {
    $bitrix_url = BITRIX_URL;
    
    $lead_data = array(
        'TITLE' => $data['title'],
        'NAME' => $data['name'],
        'EMAIL' => $data['email'],
        'PHONE' => $data['phone'],
        'ASSIGNED_BY_ID' => $data['admin_id'],
        'SOURCE_ID' => 'WEB',
        'COMMENTS' => $data['comments']
    );
    
    $response = sendBitrixRequest($bitrix_url, 'crm.lead.add', $lead_data);
    return $response;
}

// Usage
$result = insertleads2([
    'title' => 'Employment Verification - John Doe',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+92-300-1234567',
    'admin_id' => 529,
    'comments' => 'Verification request from client XYZ'
]);
```

#### add_task() - Create Task

Creates a task in Bitrix with TAT and reminders.

```php
function add_task($task_data) {
    $bitrix_url = BITRIX_URL;
    
    $tat_date = date('Y-m-d', strtotime('+10 days'));
    
    $task = array(
        'TITLE' => $task_data['title'],
        'DESCRIPTION' => $task_data['description'],
        'RESPONSIBLE_ID' => $task_data['assigned_to'],
        'DEADLINE' => $tat_date,
        'GROUP_ID' => $task_data['group_id'],
        'PRIORITY' => 1  // 1=High, 2=Medium
    );
    
    // Add reminder 1 day before deadline
    $reminder = array(
        'REMIND_DATE' => date('Y-m-d', strtotime($tat_date . ' -1 day'))
    );
    
    $response = sendBitrixRequest($bitrix_url, 'task.item.add', $task);
    return $response;
}
```

#### task_del() - Delete Task

```php
function task_del($task_id) {
    $bitrix_url = BITRIX_URL;
    $response = sendBitrixRequest($bitrix_url, 'task.item.delete', ['ID' => $task_id]);
    return $response;
}
```

#### getworkgroup() - Get Work Groups

```php
function getworkgroup() {
    $bitrix_url = BITRIX_URL;
    $response = sendBitrixRequest($bitrix_url, 'sonet_group.get', []);
    return $response;
}
```

### Workflow Integration

```
Verification Request Submitted
        ↓
Create Lead in Bitrix (insertleads2)
        ↓
Auto-assign based on country
        ↓
Create Task with TAT (add_task)
        ↓
Set reminder (1 day before deadline)
        ↓
Update task status as verification progresses
```

### Webhook Configuration

To receive updates from Bitrix:

```php
// webhook_bitrix.php
if($_POST['event'] == 'ONTASKUPDATE') {
    $task_id = $_POST['data']['FIELDS_AFTER']['ID'];
    $status = $_POST['data']['FIELDS_AFTER']['STATUS'];
    
    // Update verification status in BackCheck
    updateVerificationFromBitrix($task_id, $status);
}
```

### Testing the Integration

```php
// Test Bitrix connection
function testBitrixConnection() {
    $bitrix_url = BITRIX_URL;
    
    // Test with a simple API call
    $response = sendBitrixRequest($bitrix_url, 'user.current', []);
    
    if($response['error']) {
        echo "Connection Failed: " . $response['error_description'];
    } else {
        echo "Connection Successful! User: " . $response['result']['NAME'];
    }
}
```

---

## Savvion BPM Integration

### Overview

Savvion Business Process Management system handles structured workflows for complex verifications with multiple approval stages.

### Configuration

Edit `/functions/savvion/savvion_config.php` (or functions.php):

```php
// Savvion API Configuration
define("SAVVION_URL", "https://savvion.example.com/api");
define("SAVVION_USERNAME", "api_user");
define("SAVVION_PASSWORD", "api_password");

// Workflow Definitions
$savvion_workflows = array(
    'employment' => 'EMP_VERIFICATION_WF',
    'education' => 'EDU_VERIFICATION_WF',
    'criminal' => 'CRIMINAL_CHECK_WF'
);
```

### Key Functions

#### addsavvioncheck() - Create Savvion Check

```php
function addsavvioncheck($check_data) {
    $savvion_url = SAVVION_URL;
    
    $workflow_data = array(
        'workflow_id' => $check_data['workflow_type'],
        'applicant_name' => $check_data['name'],
        'check_type' => $check_data['type'],
        'assigned_to' => $check_data['analyst_id'],
        'priority' => $check_data['priority'],
        'documents' => $check_data['documents']
    );
    
    $response = sendSavvionRequest($savvion_url, 'workflow/create', $workflow_data);
    return $response['workflow_instance_id'];
}
```

#### approvesavvioncheck() - Approve/Reject

```php
function approvesavvioncheck($workflow_id, $action, $comments) {
    $savvion_url = SAVVION_URL;
    
    $approval_data = array(
        'workflow_id' => $workflow_id,
        'action' => $action,  // 'approve' or 'reject'
        'comments' => $comments,
        'approved_by' => $_SESSION['userid']
    );
    
    $response = sendSavvionRequest($savvion_url, 'workflow/approve', $approval_data);
    return $response;
}
```

#### assignSavvionChecks() - Assign to Analyst

```php
function assignSavvionChecks($workflow_ids, $analyst_id) {
    foreach($workflow_ids as $workflow_id) {
        $data = array(
            'workflow_id' => $workflow_id,
            'assigned_to' => $analyst_id,
            'assigned_by' => $_SESSION['userid'],
            'assigned_date' => date('Y-m-d H:i:s')
        );
        
        sendSavvionRequest(SAVVION_URL, 'workflow/assign', $data);
    }
}
```

#### delegateSavvionChecks() - Delegate/Reassign

```php
function delegateSavvionChecks($workflow_id, $from_analyst, $to_analyst) {
    $data = array(
        'workflow_id' => $workflow_id,
        'from_user' => $from_analyst,
        'to_user' => $to_analyst,
        'delegated_by' => $_SESSION['userid'],
        'reason' => 'Workload rebalancing'
    );
    
    return sendSavvionRequest(SAVVION_URL, 'workflow/delegate', $data);
}
```

### Savvion File Upload

Supported file types:
```php
define("FILE_TYPES_ALLOWED_SAVVION", 
    "jpg,png,gif,bmp,jpeg,pdf,doc,docx,xls,csv,txt,pcx,svg,xlsx,xlm,msg,xps");
```

Upload files to Savvion workflow:

```php
function uploadToSavvion($workflow_id, $file) {
    $savvion_url = SAVVION_URL;
    
    $file_data = array(
        'workflow_id' => $workflow_id,
        'file_name' => $file['name'],
        'file_content' => base64_encode(file_get_contents($file['tmp_name'])),
        'file_type' => $file['type']
    );
    
    return sendSavvionRequest($savvion_url, 'workflow/upload', $file_data);
}
```

### Workflow Status Tracking

```php
function getSavvionStatus($workflow_id) {
    $savvion_url = SAVVION_URL;
    
    $response = sendSavvionRequest($savvion_url, 'workflow/status', [
        'workflow_id' => $workflow_id
    ]);
    
    return [
        'status' => $response['status'],
        'current_stage' => $response['current_stage'],
        'assigned_to' => $response['assigned_to'],
        'completion' => $response['completion_percentage']
    ];
}
```

---

## Google Sheets Integration

### Overview

Google Sheets API is used for automated data export, reporting, and analytics.

### Setup

#### 1. Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project: "BackCheck Verify"
3. Enable **Google Sheets API**
4. Create credentials (OAuth 2.0 Client ID)
5. Download credentials JSON

#### 2. Configure in Application

Edit `/api_google.php`:

```php
// Google API Configuration
$google_config = array(
    'client_id' => 'YOUR_CLIENT_ID.apps.googleusercontent.com',
    'client_secret' => 'YOUR_CLIENT_SECRET',
    'redirect_uri' => 'https://backcheck.io/verify/api_google.php',
    'api_key' => 'YOUR_API_KEY'
);
```

#### 3. OAuth Authentication Flow

```php
// Redirect user to Google for authorization
function getGoogleAuthUrl() {
    $params = array(
        'client_id' => $GLOBALS['google_config']['client_id'],
        'redirect_uri' => $GLOBALS['google_config']['redirect_uri'],
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/spreadsheets',
        'access_type' => 'offline'
    );
    
    return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
}

// Exchange code for access token
function getGoogleAccessToken($code) {
    $token_url = 'https://oauth2.googleapis.com/token';
    
    $post_data = array(
        'code' => $code,
        'client_id' => $GLOBALS['google_config']['client_id'],
        'client_secret' => $GLOBALS['google_config']['client_secret'],
        'redirect_uri' => $GLOBALS['google_config']['redirect_uri'],
        'grant_type' => 'authorization_code'
    );
    
    $response = sendPostRequest($token_url, $post_data);
    return json_decode($response, true);
}
```

### Export Data to Google Sheets

```php
function exportToGoogleSheets($data, $spreadsheet_id, $access_token) {
    $sheets_api = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values:append";
    
    $headers = array(
        "Authorization: Bearer {$access_token}",
        "Content-Type: application/json"
    );
    
    $body = array(
        'range' => 'Sheet1!A1',
        'majorDimension' => 'ROWS',
        'values' => $data
    );
    
    $ch = curl_init($sheets_api . '?valueInputOption=RAW');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Usage
$verification_data = [
    ['ID', 'Name', 'Type', 'Status', 'Date'],
    [12345, 'John Doe', 'Employment', 'Completed', '2026-02-15'],
    [12346, 'Jane Smith', 'Education', 'In Progress', '2026-02-16']
];

exportToGoogleSheets($verification_data, 'SPREADSHEET_ID', $access_token);
```

### Create New Spreadsheet

```php
function createGoogleSheet($title, $access_token) {
    $sheets_api = "https://sheets.googleapis.com/v4/spreadsheets";
    
    $headers = array(
        "Authorization: Bearer {$access_token}",
        "Content-Type: application/json"
    );
    
    $body = array(
        'properties' => array(
            'title' => $title
        )
    );
    
    $ch = curl_init($sheets_api);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

### Automated Reporting

Schedule reports via cron:

```bash
# Daily export to Google Sheets
0 2 * * * /usr/bin/php /var/www/html/verify/google-sheets.php
```

---

## WHMCS Integration

### Overview

WHMCS is used for billing, customer management, and invoicing.

### Configuration

```php
define("WHMCS_APIURL", "https://backcheckgroup.com/support/includes/api.php");
define("WHMCS_IDENTIFIER", "YOUR_API_IDENTIFIER");
define("WHMCS_SECRET", "YOUR_API_SECRET");
```

### API Functions

#### Create Client

```php
function createWHMCSClient($client_data) {
    $postfields = array(
        'identifier' => WHMCS_IDENTIFIER,
        'secret' => WHMCS_SECRET,
        'action' => 'AddClient',
        'firstname' => $client_data['firstname'],
        'lastname' => $client_data['lastname'],
        'email' => $client_data['email'],
        'address1' => $client_data['address'],
        'city' => $client_data['city'],
        'state' => $client_data['state'],
        'postcode' => $client_data['postcode'],
        'country' => $client_data['country'],
        'phonenumber' => $client_data['phone'],
        'password2' => $client_data['password']
    );
    
    $response = sendWHMCSRequest(WHMCS_APIURL, $postfields);
    return $response['clientid'];
}
```

#### Create Invoice

```php
function createWHMCSInvoice($client_id, $items) {
    $postfields = array(
        'identifier' => WHMCS_IDENTIFIER,
        'secret' => WHMCS_SECRET,
        'action' => 'CreateInvoice',
        'userid' => $client_id,
        'date' => date('Y-m-d'),
        'duedate' => date('Y-m-d', strtotime('+30 days')),
        'itemdescription' => $items
    );
    
    $response = sendWHMCSRequest(WHMCS_APIURL, $postfields);
    return $response['invoiceid'];
}
```

---

## Third-Party APIs

### Piple API (Data Enrichment)

Located in `piple_api.php` and `piple_api2.php`:

```php
function getPipleData($email_or_phone) {
    $api_url = 'https://api.pipl.com/search/';
    $api_key = 'YOUR_PIPL_API_KEY';
    
    $params = array(
        'email' => $email_or_phone,
        'key' => $api_key
    );
    
    $response = file_get_contents($api_url . '?' . http_build_query($params));
    return json_decode($response, true);
}
```

---

## Custom Integrations

### Webhook Support

Create a webhook endpoint for receiving external data:

```php
// webhook_endpoint.php
$webhook_secret = 'your_secret_key';

// Verify signature
$received_signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$payload = file_get_contents('php://input');
$calculated_signature = hash_hmac('sha256', $payload, $webhook_secret);

if($received_signature !== $calculated_signature) {
    http_response_code(401);
    die('Invalid signature');
}

// Process webhook data
$data = json_decode($payload, true);

switch($data['event']) {
    case 'verification.completed':
        updateVerificationStatus($data['verification_id'], 'completed');
        break;
    case 'document.uploaded':
        processDocument($data['document_id']);
        break;
}

http_response_code(200);
echo json_encode(['status' => 'success']);
```

---

## Troubleshooting

### Bitrix Integration Issues

**Connection Failed**:
- Verify BITRIX_URL is correct
- Check API credentials
- Ensure firewall allows outbound connections
- Test with curl: `curl -X POST BITRIX_URL`

**Lead Not Created**:
- Check Bitrix permissions for API user
- Verify all required fields are provided
- Check Bitrix error logs

### Google Sheets Issues

**Authentication Failed**:
- Verify OAuth credentials
- Check redirect URI matches configuration
- Ensure Google Sheets API is enabled
- Refresh access token if expired

**Export Failed**:
- Check spreadsheet permissions
- Verify access token is valid
- Ensure data format is correct

### General API Issues

**Timeout Errors**:
```php
// Increase timeout
ini_set('max_execution_time', 300);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
```

**SSL Certificate Errors**:
```php
// For development only (NOT recommended for production)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
```

**Debug API Calls**:
```php
function debugAPICall($url, $data, $response) {
    error_log("API Call to: " . $url);
    error_log("Request Data: " . print_r($data, true));
    error_log("Response: " . print_r($response, true));
}
```

---

**Last Updated**: 2026  
**Version**: 3.4  
**Maintained by**: Background Check Development Team
