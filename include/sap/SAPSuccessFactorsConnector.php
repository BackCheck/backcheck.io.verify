<?php

/**
 * SAP SuccessFactors API Connector
 * 
 * Main connector class for SAP SuccessFactors integration
 * Provides comprehensive API access with OAuth 2.0 authentication,
 * data operations, and document management capabilities.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

require_once dirname(__FILE__) . '/SAPConfig.php';
require_once dirname(__FILE__) . '/SAPAuthHandler.php';
require_once dirname(__FILE__) . '/SAPDataService.php';
require_once dirname(__FILE__) . '/SAPDocumentService.php';
require_once dirname(__FILE__) . '/SAPException.php';

class SAPSuccessFactorsConnector {
    
    private $config;
    private $authHandler;
    private $dataService;
    private $documentService;
    private $logger;
    private $rateLimiter;
    
    /**
     * Constructor
     * 
     * @param string $environment Environment (dev, staging, prod)
     * @param array $customConfig Optional custom configuration
     */
    public function __construct($environment = 'prod', $customConfig = array()) {
        try {
            // Initialize configuration
            $this->config = new SAPConfig($environment, $customConfig);
            
            // Initialize authentication handler
            $this->authHandler = new SAPAuthHandler($this->config);
            
            // Initialize services
            $this->dataService = new SAPDataService($this->config, $this->authHandler);
            $this->documentService = new SAPDocumentService($this->config, $this->authHandler);
            
            // Initialize logging
            $this->initializeLogger();
            
            // Initialize rate limiter
            $this->initializeRateLimiter();
            
            $this->log('info', 'SAP SuccessFactors Connector initialized', array(
                'environment' => $environment
            ));
            
        } catch (Exception $e) {
            throw new SAPException('Failed to initialize SAP SuccessFactors Connector: ' . $e->getMessage());
        }
    }
    
    /**
     * Authenticate with SAP SuccessFactors using OAuth 2.0
     * 
     * @return bool Authentication success
     */
    public function authenticate() {
        try {
            $this->log('info', 'Starting authentication process');
            
            $result = $this->authHandler->authenticate();
            
            if ($result) {
                $this->log('info', 'Authentication successful');
            } else {
                $this->log('error', 'Authentication failed');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log('error', 'Authentication error: ' . $e->getMessage());
            throw new SAPException('Authentication failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if currently authenticated
     * 
     * @return bool Authentication status
     */
    public function isAuthenticated() {
        return $this->authHandler->hasValidToken();
    }
    
    /**
     * Get authentication handler
     * 
     * @return SAPAuthHandler
     */
    public function getAuthHandler() {
        return $this->authHandler;
    }
    
    /**
     * Get data service for employee and organizational operations
     * 
     * @return SAPDataService
     */
    public function getDataService() {
        return $this->dataService;
    }
    
    /**
     * Get document service for document management
     * 
     * @return SAPDocumentService
     */
    public function getDocumentService() {
        return $this->documentService;
    }
    
    /**
     * Send employee data to SAP SuccessFactors
     * 
     * @param array $employeeData Employee information
     * @return array API response
     */
    public function sendEmployeeData($employeeData) {
        $this->checkAuthentication();
        $this->checkRateLimit();
        
        try {
            $this->log('info', 'Sending employee data', array('employee_id' => $employeeData['employeeId'] ?? 'unknown'));
            
            $result = $this->dataService->createEmployee($employeeData);
            
            $this->log('info', 'Employee data sent successfully', array(
                'employee_id' => $employeeData['employeeId'] ?? 'unknown',
                'response' => $result
            ));
            
            return $result;
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to send employee data: ' . $e->getMessage(), array(
                'employee_data' => $employeeData
            ));
            throw $e;
        }
    }
    
    /**
     * Retrieve employee information from SAP SuccessFactors
     * 
     * @param string $employeeId Employee ID
     * @return array Employee data
     */
    public function getEmployeeData($employeeId) {
        $this->checkAuthentication();
        $this->checkRateLimit();
        
        try {
            $this->log('info', 'Retrieving employee data', array('employee_id' => $employeeId));
            
            $result = $this->dataService->getEmployee($employeeId);
            
            $this->log('info', 'Employee data retrieved successfully', array('employee_id' => $employeeId));
            
            return $result;
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to retrieve employee data: ' . $e->getMessage(), array(
                'employee_id' => $employeeId
            ));
            throw $e;
        }
    }
    
    /**
     * Update employee information in SAP SuccessFactors
     * 
     * @param string $employeeId Employee ID
     * @param array $updateData Data to update
     * @return array API response
     */
    public function updateEmployeeData($employeeId, $updateData) {
        $this->checkAuthentication();
        $this->checkRateLimit();
        
        try {
            $this->log('info', 'Updating employee data', array(
                'employee_id' => $employeeId,
                'update_fields' => array_keys($updateData)
            ));
            
            $result = $this->dataService->updateEmployee($employeeId, $updateData);
            
            $this->log('info', 'Employee data updated successfully', array('employee_id' => $employeeId));
            
            return $result;
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to update employee data: ' . $e->getMessage(), array(
                'employee_id' => $employeeId,
                'update_data' => $updateData
            ));
            throw $e;
        }
    }
    
    /**
     * Send background check results to SAP SuccessFactors
     * 
     * @param string $employeeId Employee ID
     * @param array $checkResults Background check results
     * @return array API response
     */
    public function sendBackgroundCheckResults($employeeId, $checkResults) {
        $this->checkAuthentication();
        $this->checkRateLimit();
        
        try {
            $this->log('info', 'Sending background check results', array(
                'employee_id' => $employeeId,
                'check_type' => $checkResults['checkType'] ?? 'unknown'
            ));
            
            $result = $this->dataService->createBackgroundCheckResult($employeeId, $checkResults);
            
            $this->log('info', 'Background check results sent successfully', array('employee_id' => $employeeId));
            
            return $result;
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to send background check results: ' . $e->getMessage(), array(
                'employee_id' => $employeeId,
                'check_results' => $checkResults
            ));
            throw $e;
        }
    }
    
    /**
     * Upload document to SAP SuccessFactors
     * 
     * @param string $filePath Local file path
     * @param array $metadata Document metadata
     * @return array Upload response
     */
    public function uploadDocument($filePath, $metadata = array()) {
        $this->checkAuthentication();
        $this->checkRateLimit();
        
        try {
            $this->log('info', 'Uploading document', array(
                'file_path' => $filePath,
                'metadata' => $metadata
            ));
            
            $result = $this->documentService->uploadDocument($filePath, $metadata);
            
            $this->log('info', 'Document uploaded successfully', array(
                'file_path' => $filePath,
                'document_id' => $result['documentId'] ?? 'unknown'
            ));
            
            return $result;
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to upload document: ' . $e->getMessage(), array(
                'file_path' => $filePath,
                'metadata' => $metadata
            ));
            throw $e;
        }
    }
    
    /**
     * Download document from SAP SuccessFactors
     * 
     * @param string $documentId Document ID
     * @param string $savePath Local save path
     * @return bool Download success
     */
    public function downloadDocument($documentId, $savePath) {
        $this->checkAuthentication();
        $this->checkRateLimit();
        
        try {
            $this->log('info', 'Downloading document', array(
                'document_id' => $documentId,
                'save_path' => $savePath
            ));
            
            $result = $this->documentService->downloadDocument($documentId, $savePath);
            
            $this->log('info', 'Document downloaded successfully', array(
                'document_id' => $documentId,
                'save_path' => $savePath
            ));
            
            return $result;
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to download document: ' . $e->getMessage(), array(
                'document_id' => $documentId,
                'save_path' => $savePath
            ));
            throw $e;
        }
    }
    
    /**
     * Perform batch operations
     * 
     * @param array $operations Array of operations to perform
     * @return array Batch results
     */
    public function batchOperation($operations) {
        $this->checkAuthentication();
        
        try {
            $this->log('info', 'Starting batch operation', array('operation_count' => count($operations)));
            
            $results = array();
            $errors = array();
            
            foreach ($operations as $index => $operation) {
                try {
                    $this->checkRateLimit();
                    
                    switch ($operation['type']) {
                        case 'create_employee':
                            $results[$index] = $this->dataService->createEmployee($operation['data']);
                            break;
                        case 'update_employee':
                            $results[$index] = $this->dataService->updateEmployee($operation['employee_id'], $operation['data']);
                            break;
                        case 'upload_document':
                            $results[$index] = $this->documentService->uploadDocument($operation['file_path'], $operation['metadata'] ?? array());
                            break;
                        default:
                            throw new SAPException('Unknown operation type: ' . $operation['type']);
                    }
                } catch (Exception $e) {
                    $errors[$index] = $e->getMessage();
                    $this->log('error', 'Batch operation failed for index ' . $index . ': ' . $e->getMessage());
                }
            }
            
            $batchResult = array(
                'results' => $results,
                'errors' => $errors,
                'success_count' => count($results),
                'error_count' => count($errors),
                'total_count' => count($operations)
            );
            
            $this->log('info', 'Batch operation completed', $batchResult);
            
            return $batchResult;
            
        } catch (Exception $e) {
            $this->log('error', 'Batch operation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get connector status and health information
     * 
     * @return array Status information
     */
    public function getStatus() {
        return array(
            'authenticated' => $this->isAuthenticated(),
            'environment' => $this->config->getEnvironment(),
            'api_base_url' => $this->config->getApiBaseUrl(),
            'token_expires_at' => $this->authHandler->getTokenExpirationTime(),
            'rate_limit_remaining' => $this->getRateLimitRemaining(),
            'last_request_time' => $this->getLastRequestTime()
        );
    }
    
    /**
     * Initialize logger
     */
    private function initializeLogger() {
        // Use existing logging mechanism or create simple file logger
        $this->logger = true; // Simple flag for now
    }
    
    /**
     * Initialize rate limiter
     */
    private function initializeRateLimiter() {
        $this->rateLimiter = array(
            'requests_per_minute' => $this->config->getRateLimitRequestsPerMinute(),
            'requests' => array(),
            'last_reset' => time()
        );
    }
    
    /**
     * Check authentication status and refresh token if needed
     */
    private function checkAuthentication() {
        if (!$this->isAuthenticated()) {
            if (!$this->authHandler->refreshToken()) {
                throw new SAPException('Authentication required. Please call authenticate() first.');
            }
        }
    }
    
    /**
     * Check and enforce rate limits
     */
    private function checkRateLimit() {
        $now = time();
        $windowStart = $now - 60; // 1 minute window
        
        // Clean old requests
        $this->rateLimiter['requests'] = array_filter(
            $this->rateLimiter['requests'],
            function($timestamp) use ($windowStart) {
                return $timestamp > $windowStart;
            }
        );
        
        // Check rate limit
        if (count($this->rateLimiter['requests']) >= $this->rateLimiter['requests_per_minute']) {
            $waitTime = 61 - ($now - min($this->rateLimiter['requests']));
            throw new SAPException('Rate limit exceeded. Please wait ' . $waitTime . ' seconds before making another request.');
        }
        
        // Add current request
        $this->rateLimiter['requests'][] = $now;
    }
    
    /**
     * Get remaining rate limit
     */
    private function getRateLimitRemaining() {
        $now = time();
        $windowStart = $now - 60;
        
        $recentRequests = array_filter(
            $this->rateLimiter['requests'],
            function($timestamp) use ($windowStart) {
                return $timestamp > $windowStart;
            }
        );
        
        return max(0, $this->rateLimiter['requests_per_minute'] - count($recentRequests));
    }
    
    /**
     * Get last request time
     */
    private function getLastRequestTime() {
        return empty($this->rateLimiter['requests']) ? null : max($this->rateLimiter['requests']);
    }
    
    /**
     * Log message
     * 
     * @param string $level Log level (info, warning, error)
     * @param string $message Log message
     * @param array $context Additional context
     */
    private function log($level, $message, $context = array()) {
        if (!$this->logger) return;
        
        $logEntry = array(
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        );
        
        // Simple file logging for now
        $logFile = '/tmp/sap_connector.log';
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}