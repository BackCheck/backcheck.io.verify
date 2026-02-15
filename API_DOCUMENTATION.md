# API Documentation - BackCheck.io Verify

## Table of Contents
- [Overview](#overview)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Request/Response Format](#requestresponse-format)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Code Examples](#code-examples)

## Overview

BackCheck.io Verify provides a REST API for programmatic access to the verification system. The API allows external systems to create verification requests, check status, upload documents, and retrieve reports.

### Base URL
```
https://backcheck.io/verify/api_verify.php
```

### API Version
Current Version: 1.0

### Supported Formats
- **Request**: JSON, Form Data (multipart for file uploads)
- **Response**: JSON

## Authentication

### Token-Based Authentication

All API requests require a valid authentication token passed in the Authorization header.

#### Request Header
```http
Authorization: Bearer {your_api_token}
```

#### Obtaining an API Token

Contact the system administrator to obtain an API token. Tokens are stored in the `auth_token` table with the following attributes:
- Token string (unique)
- User/Client association
- Expiration date
- Permissions/Scope

#### Example Authentication
```bash
curl -X POST https://backcheck.io/verify/api_verify.php?action=get_status \
  -H "Authorization: Bearer abc123def456ghi789" \
  -H "Content-Type: application/json"
```

## API Endpoints

### 1. Create Verification Request

Create a new verification request in the system.

**Endpoint**: `POST /api_verify.php?action=create_check`

**Parameters**:
```json
{
  "client_ref": "string (required) - Client reference number",
  "applicant_name": "string (required) - Full name of applicant",
  "email": "string (optional) - Applicant email",
  "phone": "string (optional) - Applicant phone number",
  "check_type": "string (required) - employment|education|criminal|identity|address",
  "company_id": "integer (required) - Client company ID",
  "priority": "string (optional) - normal|urgent|critical",
  "tat_days": "integer (optional) - Turnaround time in days (default: 10)",
  "documents": "array (optional) - Array of base64 encoded documents",
  "custom_fields": "object (optional) - Additional custom fields"
}
```

**Request Example**:
```json
{
  "client_ref": "EMP-2026-001",
  "applicant_name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "+92-300-1234567",
  "check_type": "employment",
  "company_id": 87,
  "priority": "normal",
  "tat_days": 10,
  "custom_fields": {
    "position": "Software Engineer",
    "department": "IT"
  }
}
```

**Response Example**:
```json
{
  "status": "success",
  "message": "Verification request created successfully",
  "data": {
    "verification_id": 12345,
    "client_ref": "EMP-2026-001",
    "status": "submitted",
    "created_date": "2026-02-15 10:30:00",
    "tat_date": "2026-02-25 10:30:00"
  }
}
```

---

### 2. Get Verification Status

Retrieve the current status of a verification request.

**Endpoint**: `GET /api_verify.php?action=get_status`

**Parameters**:
- `verification_id` (required) - Integer ID of the verification
- `client_ref` (optional) - Alternative: search by client reference number

**Request Example**:
```bash
GET /api_verify.php?action=get_status&verification_id=12345
```

**Response Example**:
```json
{
  "status": "success",
  "data": {
    "verification_id": 12345,
    "client_ref": "EMP-2026-001",
    "applicant_name": "John Doe",
    "check_type": "employment",
    "status": "in_progress",
    "assigned_to": "Analyst Name",
    "created_date": "2026-02-15 10:30:00",
    "modified_date": "2026-02-16 14:20:00",
    "tat_date": "2026-02-25 10:30:00",
    "progress_percentage": 60,
    "current_stage": "verification",
    "timeline": [
      {
        "stage": "submitted",
        "date": "2026-02-15 10:30:00",
        "user": "System"
      },
      {
        "stage": "assigned",
        "date": "2026-02-15 11:00:00",
        "user": "Team Lead"
      },
      {
        "stage": "in_progress",
        "date": "2026-02-16 09:00:00",
        "user": "Analyst"
      }
    ]
  }
}
```

---

### 3. Upload Documents

Upload supporting documents for a verification request.

**Endpoint**: `POST /api_verify.php?action=upload_document`

**Parameters**:
- `verification_id` (required) - Integer ID of the verification
- `file` (required) - File upload (multipart/form-data)
- `document_type` (optional) - Type of document (resume, certificate, id_card, etc.)
- `description` (optional) - Document description

**Request Example** (multipart/form-data):
```bash
curl -X POST https://backcheck.io/verify/api_verify.php?action=upload_document \
  -H "Authorization: Bearer abc123def456ghi789" \
  -F "verification_id=12345" \
  -F "file=@/path/to/document.pdf" \
  -F "document_type=certificate" \
  -F "description=Education Certificate"
```

**Response Example**:
```json
{
  "status": "success",
  "message": "Document uploaded successfully",
  "data": {
    "upload_id": 789,
    "filename": "document.pdf",
    "file_size": "245678",
    "upload_date": "2026-02-15 10:35:00"
  }
}
```

---

### 4. Get Verification Report

Retrieve the completed verification report.

**Endpoint**: `GET /api_verify.php?action=get_report`

**Parameters**:
- `verification_id` (required) - Integer ID of the verification
- `format` (optional) - pdf|json (default: json)

**Request Example**:
```bash
GET /api_verify.php?action=get_report&verification_id=12345&format=json
```

**Response Example**:
```json
{
  "status": "success",
  "data": {
    "verification_id": 12345,
    "client_ref": "EMP-2026-001",
    "applicant_name": "John Doe",
    "check_type": "employment",
    "final_status": "verified",
    "completed_date": "2026-02-20 16:00:00",
    "report": {
      "summary": "Employment verification completed successfully",
      "findings": [
        {
          "employer": "ABC Company",
          "position": "Software Engineer",
          "duration": "Jan 2020 - Dec 2023",
          "verification_status": "confirmed",
          "remarks": "All details verified with HR department"
        }
      ],
      "recommendation": "Clear - No discrepancies found",
      "verified_by": "Senior Analyst Name",
      "qc_approved_by": "QC Manager Name"
    },
    "report_url": "https://backcheck.io/verify/reports/12345.pdf"
  }
}
```

---

### 5. List Verifications

List verification requests with filtering and pagination.

**Endpoint**: `GET /api_verify.php?action=list_verifications`

**Parameters**:
- `company_id` (optional) - Filter by company
- `status` (optional) - Filter by status
- `from_date` (optional) - Start date (YYYY-MM-DD)
- `to_date` (optional) - End date (YYYY-MM-DD)
- `page` (optional) - Page number (default: 1)
- `per_page` (optional) - Results per page (default: 20, max: 100)

**Request Example**:
```bash
GET /api_verify.php?action=list_verifications&company_id=87&status=completed&page=1&per_page=20
```

**Response Example**:
```json
{
  "status": "success",
  "data": {
    "total_count": 145,
    "page": 1,
    "per_page": 20,
    "total_pages": 8,
    "verifications": [
      {
        "verification_id": 12345,
        "client_ref": "EMP-2026-001",
        "applicant_name": "John Doe",
        "check_type": "employment",
        "status": "completed",
        "created_date": "2026-02-15 10:30:00",
        "completed_date": "2026-02-20 16:00:00"
      },
      // ... more records
    ]
  }
}
```

---

### 6. Update Verification

Update details of an existing verification request.

**Endpoint**: `PUT /api_verify.php?action=update_check`

**Parameters**:
```json
{
  "verification_id": "integer (required) - Verification ID",
  "priority": "string (optional) - Update priority",
  "notes": "string (optional) - Add notes",
  "custom_fields": "object (optional) - Update custom fields"
}
```

**Request Example**:
```json
{
  "verification_id": 12345,
  "priority": "urgent",
  "notes": "Client requested expedited processing"
}
```

**Response Example**:
```json
{
  "status": "success",
  "message": "Verification updated successfully",
  "data": {
    "verification_id": 12345,
    "updated_fields": ["priority", "notes"],
    "modified_date": "2026-02-16 11:00:00"
  }
}
```

---

### 7. Cancel Verification

Cancel a pending verification request.

**Endpoint**: `DELETE /api_verify.php?action=cancel_check`

**Parameters**:
- `verification_id` (required) - Integer ID of the verification
- `reason` (optional) - Cancellation reason

**Request Example**:
```json
{
  "verification_id": 12345,
  "reason": "Applicant withdrew application"
}
```

**Response Example**:
```json
{
  "status": "success",
  "message": "Verification cancelled successfully",
  "data": {
    "verification_id": 12345,
    "previous_status": "in_progress",
    "current_status": "cancelled",
    "cancelled_date": "2026-02-16 12:00:00"
  }
}
```

---

### 8. Get Webhook Status (Future Feature)

Register a webhook URL for status updates.

**Endpoint**: `POST /api_verify.php?action=register_webhook`

**Parameters**:
```json
{
  "url": "string (required) - Webhook URL",
  "events": "array (required) - Events to subscribe to",
  "secret": "string (optional) - Webhook secret for verification"
}
```

## Request/Response Format

### Standard Response Structure

All API responses follow a consistent structure:

**Success Response**:
```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": {
    // Response data object
  }
}
```

**Error Response**:
```json
{
  "status": "error",
  "message": "Error description",
  "error_code": "ERROR_CODE",
  "details": {
    // Additional error details
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success - Request completed successfully |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid parameters or request format |
| 401 | Unauthorized - Invalid or missing authentication token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error occurred |
| 503 | Service Unavailable - Service temporarily unavailable |

## Error Handling

### Error Response Structure

```json
{
  "status": "error",
  "message": "Human-readable error message",
  "error_code": "UNIQUE_ERROR_CODE",
  "details": {
    "field": "Field that caused the error",
    "reason": "Detailed reason for the error"
  }
}
```

### Common Error Codes

| Error Code | Description |
|------------|-------------|
| `AUTH_TOKEN_INVALID` | Invalid or expired authentication token |
| `AUTH_TOKEN_MISSING` | Authorization header not provided |
| `PARAM_MISSING` | Required parameter is missing |
| `PARAM_INVALID` | Parameter value is invalid |
| `VERIFICATION_NOT_FOUND` | Verification ID not found |
| `PERMISSION_DENIED` | User does not have permission for this action |
| `FILE_TOO_LARGE` | Uploaded file exceeds size limit |
| `FILE_TYPE_INVALID` | File type not allowed |
| `RATE_LIMIT_EXCEEDED` | Too many requests - rate limit exceeded |
| `SERVER_ERROR` | Internal server error occurred |

### Error Examples

**Missing Parameter**:
```json
{
  "status": "error",
  "message": "Required parameter missing",
  "error_code": "PARAM_MISSING",
  "details": {
    "field": "client_ref",
    "reason": "Client reference number is required"
  }
}
```

**Invalid Token**:
```json
{
  "status": "error",
  "message": "Authentication failed",
  "error_code": "AUTH_TOKEN_INVALID",
  "details": {
    "reason": "Token has expired or is invalid"
  }
}
```

## Rate Limiting

### Limits
- **Rate Limit**: 100 requests per minute per API token
- **Burst Limit**: 10 concurrent requests

### Rate Limit Headers

Response headers include rate limit information:

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1639584000
```

### Rate Limit Exceeded Response

```json
{
  "status": "error",
  "message": "Rate limit exceeded",
  "error_code": "RATE_LIMIT_EXCEEDED",
  "details": {
    "limit": 100,
    "reset_time": "2026-02-15T11:00:00Z",
    "retry_after": 45
  }
}
```

## Code Examples

### PHP Example

```php
<?php
// API Configuration
$api_url = 'https://backcheck.io/verify/api_verify.php';
$api_token = 'your_api_token_here';

// Create verification request
function createVerification($api_url, $api_token, $data) {
    $ch = curl_init($api_url . '?action=create_check');
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_token,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Usage
$verification_data = [
    'client_ref' => 'EMP-2026-001',
    'applicant_name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'check_type' => 'employment',
    'company_id' => 87
];

$result = createVerification($api_url, $api_token, $verification_data);
print_r($result);
?>
```

### JavaScript (Node.js) Example

```javascript
const axios = require('axios');

const API_URL = 'https://backcheck.io/verify/api_verify.php';
const API_TOKEN = 'your_api_token_here';

// Create verification request
async function createVerification(data) {
  try {
    const response = await axios.post(
      `${API_URL}?action=create_check`,
      data,
      {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json'
        }
      }
    );
    return response.data;
  } catch (error) {
    console.error('API Error:', error.response.data);
    throw error;
  }
}

// Usage
const verificationData = {
  client_ref: 'EMP-2026-001',
  applicant_name: 'John Doe',
  email: 'john.doe@example.com',
  check_type: 'employment',
  company_id: 87
};

createVerification(verificationData)
  .then(result => console.log(result))
  .catch(error => console.error(error));
```

### Python Example

```python
import requests
import json

API_URL = 'https://backcheck.io/verify/api_verify.php'
API_TOKEN = 'your_api_token_here'

def create_verification(data):
    """Create a new verification request"""
    headers = {
        'Authorization': f'Bearer {API_TOKEN}',
        'Content-Type': 'application/json'
    }
    
    response = requests.post(
        f'{API_URL}?action=create_check',
        headers=headers,
        json=data
    )
    
    return response.json()

# Usage
verification_data = {
    'client_ref': 'EMP-2026-001',
    'applicant_name': 'John Doe',
    'email': 'john.doe@example.com',
    'check_type': 'employment',
    'company_id': 87
}

result = create_verification(verification_data)
print(json.dumps(result, indent=2))
```

### cURL Example

```bash
# Create verification
curl -X POST 'https://backcheck.io/verify/api_verify.php?action=create_check' \
  -H 'Authorization: Bearer your_api_token_here' \
  -H 'Content-Type: application/json' \
  -d '{
    "client_ref": "EMP-2026-001",
    "applicant_name": "John Doe",
    "email": "john.doe@example.com",
    "check_type": "employment",
    "company_id": 87
  }'

# Get status
curl -X GET 'https://backcheck.io/verify/api_verify.php?action=get_status&verification_id=12345' \
  -H 'Authorization: Bearer your_api_token_here'

# Upload document
curl -X POST 'https://backcheck.io/verify/api_verify.php?action=upload_document' \
  -H 'Authorization: Bearer your_api_token_here' \
  -F 'verification_id=12345' \
  -F 'file=@document.pdf' \
  -F 'document_type=certificate'
```

## Best Practices

1. **Secure Token Storage**: Store API tokens securely, never in source code
2. **Error Handling**: Always implement proper error handling
3. **Retry Logic**: Implement exponential backoff for transient errors
4. **Logging**: Log all API requests and responses for debugging
5. **Timeout Handling**: Set appropriate timeout values for requests
6. **Webhook Validation**: Verify webhook signatures when available
7. **Rate Limit Awareness**: Monitor rate limit headers and adjust accordingly
8. **Data Validation**: Validate data before sending to API
9. **HTTPS Only**: Always use HTTPS for API communication
10. **Token Rotation**: Regularly rotate API tokens for security

## Support

For API support or to request additional features:
- **Email**: support@backcheckgroup.com
- **Documentation**: https://backcheck.io/verify/api-docs
- **Status Page**: https://status.backcheckgroup.com

---

**API Version**: 1.0  
**Last Updated**: 2026  
**Maintained by**: Background Check Development Team
