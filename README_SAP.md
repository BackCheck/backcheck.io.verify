# SAP SuccessFactors API Connector

A comprehensive PHP API connector for SAP SuccessFactors integration with the BackCheck application. This connector enables seamless data exchange between BackCheck's background screening platform and SAP SuccessFactors HR system.

## Features

- **OAuth 2.0 Authentication**: Secure authentication with token management
- **Employee Data Management**: Create, read, update, and delete employee records
- **Background Check Integration**: Send background check results to SAP SuccessFactors
- **Document Management**: Upload, download, and manage documents
- **Batch Operations**: Process multiple operations efficiently
- **Error Handling**: Comprehensive error handling with custom exceptions
- **Rate Limiting**: Built-in rate limiting compliance
- **Logging**: Detailed logging for monitoring and debugging
- **Multi-Environment Support**: Support for development, staging, and production environments

## Installation

1. Copy the SAP connector files to your BackCheck installation:
   ```
   include/sap/
   ├── SAPSuccessFactorsConnector.php
   ├── SAPConfig.php
   ├── SAPAuthHandler.php
   ├── SAPDataService.php
   ├── SAPDocumentService.php
   ├── SAPException.php
   └── SAPUtils.php
   ```

2. Include the main connector class in your PHP files:
   ```php
   require_once 'include/sap/SAPSuccessFactorsConnector.php';
   ```

## Configuration

### Environment Variables

Set the following environment variables for production use:

```bash
export SAP_CLIENT_ID="your_client_id"
export SAP_CLIENT_SECRET="your_client_secret"
export SAP_REDIRECT_URI="https://your-domain.com/sap-callback"
export SAP_API_BASE_URL="https://api.successfactors.com/odata"
```

### Database Tables

The connector will automatically create the following database tables:

- `sap_tokens` - Stores OAuth tokens
- `sap_config` - Stores configuration settings

### Custom Configuration

You can also provide configuration programmatically:

```php
$customConfig = array(
    'oauth' => array(
        'client_id' => 'your_client_id',
        'client_secret' => 'your_client_secret',
        'redirect_uri' => 'https://your-domain.com/sap-callback'
    ),
    'api' => array(
        'base_url' => 'https://api.successfactors.com/odata'
    )
);

$connector = new SAPSuccessFactorsConnector('prod', $customConfig);
```

## Quick Start

### Basic Usage

```php
// Initialize the connector
$connector = new SAPSuccessFactorsConnector('prod');

// Authenticate
if (!$connector->isAuthenticated()) {
    $connector->authenticate();
}

// Create an employee
$employeeData = array(
    'employeeId' => 'EMP001',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john.doe@company.com',
    'jobTitle' => 'Software Engineer'
);

$result = $connector->sendEmployeeData($employeeData);
```

### Background Check Integration

```php
// Send background check results
$checkResults = array(
    'checkType' => 'criminal',
    'status' => 'completed',
    'result' => 'clear',
    'completedDate' => date('Y-m-d H:i:s'),
    'vendor' => 'BackCheck'
);

$result = $connector->sendBackgroundCheckResults('EMP001', $checkResults);
```

### Document Upload

```php
// Upload a document
$metadata = array(
    'employeeId' => 'EMP001',
    'documentType' => 'background_check_report',
    'description' => 'Criminal background check report'
);

$result = $connector->uploadDocument('/path/to/document.pdf', $metadata);
```

## API Reference

### SAPSuccessFactorsConnector

Main connector class that orchestrates all SAP SuccessFactors operations.

#### Methods

- `__construct($environment, $customConfig)` - Initialize connector
- `authenticate()` - Authenticate with SAP SuccessFactors
- `isAuthenticated()` - Check authentication status
- `sendEmployeeData($employeeData)` - Send employee data to SAP
- `getEmployeeData($employeeId)` - Retrieve employee data from SAP
- `updateEmployeeData($employeeId, $updateData)` - Update employee data
- `sendBackgroundCheckResults($employeeId, $checkResults)` - Send background check results
- `uploadDocument($filePath, $metadata)` - Upload document
- `downloadDocument($documentId, $savePath)` - Download document
- `batchOperation($operations)` - Execute batch operations
- `getStatus()` - Get connector status

### SAPDataService

Handles employee and organizational data operations.

#### Methods

- `createEmployee($employeeData)` - Create employee record
- `getEmployee($employeeId)` - Get employee by ID
- `updateEmployee($employeeId, $updateData)` - Update employee record
- `deleteEmployee($employeeId)` - Delete employee record
- `searchEmployees($criteria, $options)` - Search employees
- `createBackgroundCheckResult($employeeId, $checkData)` - Create background check result
- `getBackgroundCheckResults($employeeId)` - Get background check results
- `getJobRequisitions($criteria, $options)` - Get job requisitions
- `getOrganizationalData($type, $options)` - Get organizational data

### SAPDocumentService

Handles document management operations.

#### Methods

- `uploadDocument($filePath, $metadata)` - Upload document
- `downloadDocument($documentId, $savePath)` - Download document
- `getDocumentInfo($documentId)` - Get document information
- `listDocuments($criteria, $options)` - List documents
- `updateDocumentMetadata($documentId, $metadata)` - Update document metadata
- `deleteDocument($documentId)` - Delete document

## REST API Endpoints

The connector provides REST API endpoints accessible via `api_sap.php`:

### Authentication
- `POST /api_sap.php?action=authenticate` - Authenticate connector

### Employee Operations
- `GET /api_sap.php?action=employee&employee_id=EMP001` - Get employee
- `GET /api_sap.php?action=employee&criteria[department]=Engineering` - Search employees
- `POST /api_sap.php?action=employee` - Create employee
- `PUT /api_sap.php?action=employee&employee_id=EMP001` - Update employee
- `DELETE /api_sap.php?action=employee&employee_id=EMP001` - Delete employee

### Background Check Operations
- `GET /api_sap.php?action=background_check&employee_id=EMP001` - Get background checks
- `POST /api_sap.php?action=background_check` - Create background check result

### Document Operations
- `GET /api_sap.php?action=document&document_id=DOC001` - Get document info
- `GET /api_sap.php?action=document` - List documents
- `POST /api_sap.php?action=document` - Upload document (with file upload)
- `PUT /api_sap.php?action=document&document_id=DOC001` - Update document metadata
- `DELETE /api_sap.php?action=document&document_id=DOC001` - Delete document

### Batch Operations
- `POST /api_sap.php?action=batch` - Execute batch operations

### Status Check
- `GET /api_sap.php?action=status` - Get connector status

### Webhook Support
- `POST /api_sap.php?action=webhook` - Handle webhook notifications

## Error Handling

The connector provides comprehensive error handling with specific exception types:

- `SAPException` - Base exception class
- `SAPAuthException` - Authentication errors
- `SAPConfigException` - Configuration errors
- `SAPApiException` - API request errors
- `SAPRateLimitException` - Rate limiting errors
- `SAPValidationException` - Data validation errors
- `SAPDocumentException` - Document operation errors
- `SAPConnectionException` - Network connection errors
- `SAPDataException` - Data transformation errors

### Example Error Handling

```php
try {
    $result = $connector->sendEmployeeData($employeeData);
} catch (SAPValidationException $e) {
    echo "Validation errors: " . json_encode($e->getValidationErrors());
} catch (SAPRateLimitException $e) {
    echo "Rate limit exceeded. Retry after: " . $e->getRetryAfter() . " seconds";
} catch (SAPAuthException $e) {
    echo "Authentication failed: " . $e->getMessage();
} catch (SAPException $e) {
    echo "SAP error: " . $e->getMessage();
    echo "Details: " . json_encode($e->getErrorDetails());
}
```

## Data Transformation

The connector automatically transforms data between BackCheck and SAP SuccessFactors formats:

### Employee Data Mapping

| BackCheck Field | SAP SuccessFactors Field |
|------------------|-------------------------|
| employee_id      | userId                  |
| first_name       | firstName               |
| last_name        | lastName                |
| email            | email                   |
| phone            | phoneNumber             |
| birth_date       | dateOfBirth             |
| hire_date        | startDate               |
| job_title        | title                   |
| department       | department              |

### Background Check Status Mapping

| BackCheck Status | SAP SuccessFactors Status |
|------------------|---------------------------|
| pending          | IN_PROGRESS              |
| in_progress      | IN_PROGRESS              |
| completed        | COMPLETED                |
| cancelled        | CANCELLED                |
| on_hold          | ON_HOLD                  |
| failed           | FAILED                   |

## Rate Limiting

The connector includes built-in rate limiting to comply with SAP SuccessFactors API limits:

- Default: 60 requests per minute
- Configurable via environment variables or configuration
- Automatic retry with exponential backoff
- Rate limit status available via `getStatus()` method

## Logging

Comprehensive logging is available for monitoring and debugging:

- Log file location: `/tmp/sap_connector.log` (configurable)
- Log levels: debug, info, warning, error
- Automatic log rotation when file size exceeds limit
- Structured JSON format for easy parsing

### Example Log Entry

```json
{
  "timestamp": "2023-12-07 10:30:45",
  "level": "INFO",
  "message": "Employee data sent successfully",
  "context": {
    "employee_id": "EMP001",
    "response": {"success": true}
  },
  "memory_usage": 1048576,
  "peak_memory": 2097152
}
```

## Security

### Token Management

- Secure OAuth 2.0 token storage in database
- Automatic token refresh before expiration
- Token encryption in transit and at rest

### Data Validation

- Input validation for all data operations
- CSRF protection for OAuth flows
- SQL injection prevention
- XSS protection for API responses

### SSL/TLS

- Enforced HTTPS connections
- Certificate validation
- Configurable SSL settings per environment

## Testing

Run the examples to test the integration:

```bash
php sap_examples.php
```

This will run through various integration scenarios and display results.

## Troubleshooting

### Common Issues

1. **Authentication Failures**
   - Verify OAuth credentials are correct
   - Check that redirect URI is registered with SAP
   - Ensure system time is synchronized

2. **API Errors**
   - Check API endpoint URLs are correct for your environment
   - Verify API permissions in SAP SuccessFactors
   - Review rate limiting settings

3. **Configuration Issues**
   - Validate all required configuration fields
   - Check environment variable names and values
   - Ensure database connectivity for token storage

### Debug Mode

Enable debug logging for detailed troubleshooting:

```php
$customConfig = array(
    'logging' => array(
        'level' => 'debug'
    )
);

$connector = new SAPSuccessFactorsConnector('dev', $customConfig);
```

## Support

For technical support or questions about the SAP SuccessFactors integration:

1. Check the examples in `sap_examples.php`
2. Review error logs for specific error messages
3. Consult SAP SuccessFactors documentation for API-specific issues

## License

This SAP SuccessFactors connector is part of the BackCheck application and follows the same licensing terms.

## Changelog

### Version 1.0.0
- Initial release
- OAuth 2.0 authentication
- Employee data operations
- Background check integration
- Document management
- Batch operations
- Comprehensive error handling
- Rate limiting
- Multi-environment support