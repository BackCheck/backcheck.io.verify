<?php

/**
 * SAP SuccessFactors API Endpoint
 * 
 * Main API endpoint for SAP SuccessFactors integration operations.
 * Handles authentication, employee data sync, background check results,
 * and document management.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

// Include configuration and dependencies
include_once dirname(__FILE__) . "/include/config.php";
require_once dirname(__FILE__) . "/include/sap/SAPSuccessFactorsConnector.php";
require_once dirname(__FILE__) . "/include/sap/SAPUtils.php";

// Set content type
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize response
$response = array('success' => false, 'error' => 'Unknown error occurred');

try {
    // Validate request method
    if (!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST', 'PUT', 'PATCH', 'DELETE'))) {
        throw new SAPException('Method not allowed', 405);
    }
    
    // Get request data
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    
    // Merge GET and POST data with request body
    $data = array_merge($_GET, $_POST, $requestData ?: array());
    
    // Get action and method
    $action = isset($data['action']) ? $data['action'] : '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Validate API access (use existing token system)
    if (!validateApiAccess($data)) {
        throw new SAPAuthException('Invalid API access token', 401);
    }
    
    // Initialize SAP connector
    $environment = isset($data['environment']) ? $data['environment'] : 'prod';
    $connector = new SAPSuccessFactorsConnector($environment);
    
    // Handle different actions
    switch ($action) {
        case 'authenticate':
            $response = handleAuthentication($connector, $data);
            break;
            
        case 'employee':
            $response = handleEmployeeOperations($connector, $method, $data);
            break;
            
        case 'background_check':
            $response = handleBackgroundCheckOperations($connector, $method, $data);
            break;
            
        case 'document':
            $response = handleDocumentOperations($connector, $method, $data);
            break;
            
        case 'batch':
            $response = handleBatchOperations($connector, $data);
            break;
            
        case 'status':
            $response = handleStatusCheck($connector);
            break;
            
        case 'webhook':
            $response = handleWebhook($connector, $data);
            break;
            
        default:
            throw new SAPException('Unknown action: ' . $action, 400);
    }
    
} catch (SAPException $e) {
    $response = SAPResponseFormatter::error($e->getMessage(), $e->getCode(), $e->getErrorDetails());
    http_response_code($e->getCode());
    
    // Log error
    SAPLogger::error('SAP API Error', array(
        'action' => $action ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'],
        'error' => $e->getFormattedError(),
        'request_data' => $data ?? array()
    ));
    
} catch (Exception $e) {
    $response = SAPResponseFormatter::error('Internal server error: ' . $e->getMessage(), 500);
    http_response_code(500);
    
    // Log error
    SAPLogger::error('General API Error', array(
        'action' => $action ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'],
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'request_data' => $data ?? array()
    ));
}

// Output response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;

/**
 * Validate API access using existing token system
 * 
 * @param array $data Request data
 * @return bool Valid access
 */
function validateApiAccess($data) {
    global $db;
    
    // Check for API token
    $token = isset($data['token']) ? $data['token'] : '';
    if (empty($token)) {
        return false;
    }
    
    // Use existing token validation logic
    if (function_exists('token_access')) {
        $tokenInfo = token_access($token);
        return $tokenInfo !== false;
    }
    
    return false;
}

/**
 * Handle authentication operations
 * 
 * @param SAPSuccessFactorsConnector $connector SAP connector
 * @param array $data Request data
 * @return array Response
 */
function handleAuthentication($connector, $data) {
    try {
        $result = $connector->authenticate();
        
        if ($result) {
            return SAPResponseFormatter::success(array(
                'authenticated' => true,
                'status' => $connector->getStatus()
            ), 'Authentication successful');
        } else {
            throw new SAPAuthException('Authentication failed');
        }
        
    } catch (Exception $e) {
        throw new SAPAuthException('Authentication error: ' . $e->getMessage());
    }
}

/**
 * Handle employee operations
 * 
 * @param SAPSuccessFactorsConnector $connector SAP connector
 * @param string $method HTTP method
 * @param array $data Request data
 * @return array Response
 */
function handleEmployeeOperations($connector, $method, $data) {
    $dataService = $connector->getDataService();
    
    switch ($method) {
        case 'GET':
            if (isset($data['employee_id'])) {
                // Get single employee
                $employee = $dataService->getEmployee($data['employee_id']);
                return SAPResponseFormatter::success($employee, 'Employee retrieved successfully');
            } else {
                // Search employees
                $criteria = isset($data['criteria']) ? $data['criteria'] : array();
                $options = isset($data['options']) ? $data['options'] : array();
                $results = $dataService->searchEmployees($criteria, $options);
                return SAPResponseFormatter::paginated(
                    $results['results'],
                    $results['count'],
                    isset($options['page']) ? $options['page'] : 1,
                    isset($options['per_page']) ? $options['per_page'] : 10,
                    $results['nextLink']
                );
            }
            
        case 'POST':
            // Create employee
            if (!isset($data['employee'])) {
                throw new SAPValidationException('Employee data is required');
            }
            
            $validationErrors = SAPDataValidator::validateEmployee($data['employee']);
            if (!empty($validationErrors)) {
                throw new SAPValidationException('Employee validation failed', $validationErrors);
            }
            
            $result = $dataService->createEmployee($data['employee']);
            return SAPResponseFormatter::success($result, 'Employee created successfully');
            
        case 'PUT':
        case 'PATCH':
            // Update employee
            if (!isset($data['employee_id'])) {
                throw new SAPValidationException('Employee ID is required for update');
            }
            
            if (!isset($data['employee'])) {
                throw new SAPValidationException('Employee data is required for update');
            }
            
            $result = $dataService->updateEmployee($data['employee_id'], $data['employee']);
            return SAPResponseFormatter::success($result, 'Employee updated successfully');
            
        case 'DELETE':
            // Delete employee
            if (!isset($data['employee_id'])) {
                throw new SAPValidationException('Employee ID is required for deletion');
            }
            
            $result = $dataService->deleteEmployee($data['employee_id']);
            return SAPResponseFormatter::success($result, 'Employee deleted successfully');
            
        default:
            throw new SAPException('Method not supported for employee operations: ' . $method, 405);
    }
}

/**
 * Handle background check operations
 * 
 * @param SAPSuccessFactorsConnector $connector SAP connector
 * @param string $method HTTP method
 * @param array $data Request data
 * @return array Response
 */
function handleBackgroundCheckOperations($connector, $method, $data) {
    $dataService = $connector->getDataService();
    
    switch ($method) {
        case 'GET':
            if (isset($data['employee_id'])) {
                // Get background checks for employee
                $checks = $dataService->getBackgroundCheckResults($data['employee_id']);
                return SAPResponseFormatter::success($checks, 'Background checks retrieved successfully');
            } else {
                throw new SAPValidationException('Employee ID is required to retrieve background checks');
            }
            
        case 'POST':
            // Create background check result
            if (!isset($data['employee_id']) || !isset($data['check_data'])) {
                throw new SAPValidationException('Employee ID and check data are required');
            }
            
            $validationErrors = SAPDataValidator::validateBackgroundCheck($data['check_data']);
            if (!empty($validationErrors)) {
                throw new SAPValidationException('Background check validation failed', $validationErrors);
            }
            
            $result = $dataService->createBackgroundCheckResult($data['employee_id'], $data['check_data']);
            return SAPResponseFormatter::success($result, 'Background check result created successfully');
            
        case 'PUT':
        case 'PATCH':
            // Update background check result
            if (!isset($data['check_id']) || !isset($data['check_data'])) {
                throw new SAPValidationException('Check ID and check data are required for update');
            }
            
            $result = $dataService->updateBackgroundCheckResult($data['check_id'], $data['check_data']);
            return SAPResponseFormatter::success($result, 'Background check result updated successfully');
            
        default:
            throw new SAPException('Method not supported for background check operations: ' . $method, 405);
    }
}

/**
 * Handle document operations
 * 
 * @param SAPSuccessFactorsConnector $connector SAP connector
 * @param string $method HTTP method
 * @param array $data Request data
 * @return array Response
 */
function handleDocumentOperations($connector, $method, $data) {
    $documentService = $connector->getDocumentService();
    
    switch ($method) {
        case 'GET':
            if (isset($data['document_id'])) {
                // Get single document info
                $document = $documentService->getDocumentInfo($data['document_id']);
                if (!$document) {
                    throw new SAPException('Document not found', 404);
                }
                return SAPResponseFormatter::success($document, 'Document retrieved successfully');
            } else {
                // List documents
                $criteria = isset($data['criteria']) ? $data['criteria'] : array();
                $options = isset($data['options']) ? $data['options'] : array();
                $results = $documentService->listDocuments($criteria, $options);
                return SAPResponseFormatter::paginated(
                    $results['documents'],
                    $results['count'],
                    isset($options['page']) ? $options['page'] : 1,
                    isset($options['per_page']) ? $options['per_page'] : 10,
                    $results['nextLink']
                );
            }
            
        case 'POST':
            // Upload document
            if (isset($_FILES['file'])) {
                // Handle file upload
                $uploadedFile = $_FILES['file'];
                if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                    throw new SAPDocumentException('File upload failed', 'upload');
                }
                
                $metadata = isset($data['metadata']) ? $data['metadata'] : array();
                $result = $documentService->uploadDocument($uploadedFile['tmp_name'], $metadata);
                return SAPResponseFormatter::success($result, 'Document uploaded successfully');
            } else {
                throw new SAPValidationException('No file uploaded');
            }
            
        case 'PUT':
        case 'PATCH':
            // Update document metadata
            if (!isset($data['document_id']) || !isset($data['metadata'])) {
                throw new SAPValidationException('Document ID and metadata are required for update');
            }
            
            $result = $documentService->updateDocumentMetadata($data['document_id'], $data['metadata']);
            return SAPResponseFormatter::success($result, 'Document metadata updated successfully');
            
        case 'DELETE':
            // Delete document
            if (!isset($data['document_id'])) {
                throw new SAPValidationException('Document ID is required for deletion');
            }
            
            $result = $documentService->deleteDocument($data['document_id']);
            return SAPResponseFormatter::success($result, 'Document deleted successfully');
            
        default:
            throw new SAPException('Method not supported for document operations: ' . $method, 405);
    }
}

/**
 * Handle batch operations
 * 
 * @param SAPSuccessFactorsConnector $connector SAP connector
 * @param array $data Request data
 * @return array Response
 */
function handleBatchOperations($connector, $data) {
    if (!isset($data['operations']) || !is_array($data['operations'])) {
        throw new SAPValidationException('Operations array is required for batch processing');
    }
    
    $result = $connector->batchOperation($data['operations']);
    return SAPResponseFormatter::success($result, 'Batch operations completed');
}

/**
 * Handle status check
 * 
 * @param SAPSuccessFactorsConnector $connector SAP connector
 * @return array Response
 */
function handleStatusCheck($connector) {
    $status = $connector->getStatus();
    return SAPResponseFormatter::success($status, 'Status retrieved successfully');
}

/**
 * Handle webhook notifications
 * 
 * @param SAPSuccessFactorsConnector $connector SAP connector
 * @param array $data Request data
 * @return array Response
 */
function handleWebhook($connector, $data) {
    // Log webhook received
    SAPLogger::info('Webhook received', array(
        'headers' => getallheaders(),
        'data' => $data
    ));
    
    // Process webhook based on event type
    $eventType = isset($data['event_type']) ? $data['event_type'] : 'unknown';
    
    switch ($eventType) {
        case 'employee_updated':
            return handleEmployeeUpdatedWebhook($data);
            
        case 'background_check_completed':
            return handleBackgroundCheckCompletedWebhook($data);
            
        case 'document_uploaded':
            return handleDocumentUploadedWebhook($data);
            
        default:
            SAPLogger::warning('Unknown webhook event type', array('event_type' => $eventType));
            return SAPResponseFormatter::success(null, 'Webhook processed (unknown event type)');
    }
}

/**
 * Handle employee updated webhook
 * 
 * @param array $data Webhook data
 * @return array Response
 */
function handleEmployeeUpdatedWebhook($data) {
    // Process employee update notification
    // This can trigger updates in BackCheck system
    
    SAPLogger::info('Employee updated webhook processed', array('data' => $data));
    return SAPResponseFormatter::success(null, 'Employee updated webhook processed');
}

/**
 * Handle background check completed webhook
 * 
 * @param array $data Webhook data
 * @return array Response
 */
function handleBackgroundCheckCompletedWebhook($data) {
    // Process background check completion notification
    // This can trigger notifications to clients
    
    SAPLogger::info('Background check completed webhook processed', array('data' => $data));
    return SAPResponseFormatter::success(null, 'Background check completed webhook processed');
}

/**
 * Handle document uploaded webhook
 * 
 * @param array $data Webhook data
 * @return array Response
 */
function handleDocumentUploadedWebhook($data) {
    // Process document upload notification
    
    SAPLogger::info('Document uploaded webhook processed', array('data' => $data));
    return SAPResponseFormatter::success(null, 'Document uploaded webhook processed');
}