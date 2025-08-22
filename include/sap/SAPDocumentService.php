<?php

/**
 * SAP SuccessFactors Document Management Service
 * 
 * Handles document upload, download, and management operations
 * for SAP SuccessFactors Document Management system.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

require_once dirname(__FILE__) . '/SAPException.php';

class SAPDocumentService {
    
    private $config;
    private $authHandler;
    private $apiBaseUrl;
    private $allowedFileTypes;
    private $maxFileSize;
    
    /**
     * Constructor
     * 
     * @param SAPConfig $config Configuration instance
     * @param SAPAuthHandler $authHandler Authentication handler
     */
    public function __construct($config, $authHandler) {
        $this->config = $config;
        $this->authHandler = $authHandler;
        $this->apiBaseUrl = $config->getApiBaseUrl() . '/' . $config->getApiVersion();
        
        // Set up file type restrictions
        $this->allowedFileTypes = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'csv', 'xlsx');
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB default
    }
    
    /**
     * Upload document to SAP SuccessFactors
     * 
     * @param string $filePath Local file path
     * @param array $metadata Document metadata
     * @return array Upload response with document ID and details
     */
    public function uploadDocument($filePath, $metadata = array()) {
        // Validate file
        $this->validateFile($filePath);
        
        // Prepare metadata
        $documentMetadata = $this->prepareDocumentMetadata($filePath, $metadata);
        
        try {
            // Step 1: Create document record
            $documentRecord = $this->createDocumentRecord($documentMetadata);
            
            // Step 2: Upload file content
            $uploadResult = $this->uploadFileContent($documentRecord['documentId'], $filePath);
            
            // Step 3: Finalize document
            $finalResult = $this->finalizeDocument($documentRecord['documentId']);
            
            return array(
                'success' => true,
                'documentId' => $documentRecord['documentId'],
                'fileName' => $documentMetadata['fileName'],
                'fileSize' => $documentMetadata['fileSize'],
                'mimeType' => $documentMetadata['mimeType'],
                'uploadedAt' => date('Y-m-d H:i:s'),
                'url' => $finalResult['url'] ?? null
            );
            
        } catch (Exception $e) {
            // Clean up on failure
            if (isset($documentRecord['documentId'])) {
                $this->deleteDocument($documentRecord['documentId']);
            }
            
            throw new SAPException('Document upload failed: ' . $e->getMessage());
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
        try {
            // Get document metadata
            $documentInfo = $this->getDocumentInfo($documentId);
            
            if (!$documentInfo) {
                throw new SAPException('Document not found: ' . $documentId);
            }
            
            // Download file content
            $content = $this->downloadFileContent($documentId);
            
            // Ensure directory exists
            $directory = dirname($savePath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new SAPException('Failed to create directory: ' . $directory);
                }
            }
            
            // Save file
            $bytesWritten = file_put_contents($savePath, $content);
            
            if ($bytesWritten === false) {
                throw new SAPException('Failed to save file: ' . $savePath);
            }
            
            return true;
            
        } catch (Exception $e) {
            throw new SAPException('Document download failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get document information
     * 
     * @param string $documentId Document ID
     * @return array|null Document information
     */
    public function getDocumentInfo($documentId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        $url = $endpoint . "('" . urlencode($documentId) . "')";
        
        try {
            $response = $this->makeApiRequest('GET', $url);
            return $this->transformDocumentFromSAP($response);
        } catch (SAPException $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }
    
    /**
     * List documents with optional filtering
     * 
     * @param array $criteria Search criteria
     * @param array $options Query options
     * @return array Document list
     */
    public function listDocuments($criteria = array(), $options = array()) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        
        $queryParams = array();
        
        // Build filter query
        if (!empty($criteria)) {
            $filters = array();
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $filters[] = $field . " in ('" . implode("','", $value) . "')";
                } else {
                    $filters[] = $field . " eq '" . $value . "'";
                }
            }
            if (!empty($filters)) {
                $queryParams['$filter'] = implode(' and ', $filters);
            }
        }
        
        // Add query options
        if (isset($options['select'])) {
            $queryParams['$select'] = is_array($options['select']) ? 
                                    implode(',', $options['select']) : 
                                    $options['select'];
        }
        
        if (isset($options['top'])) {
            $queryParams['$top'] = intval($options['top']);
        }
        
        if (isset($options['skip'])) {
            $queryParams['$skip'] = intval($options['skip']);
        }
        
        if (isset($options['orderby'])) {
            $queryParams['$orderby'] = $options['orderby'];
        }
        
        $url = $endpoint;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        $response = $this->makeApiRequest('GET', $url);
        
        $documents = array();
        if (isset($response['value']) && is_array($response['value'])) {
            foreach ($response['value'] as $doc) {
                $documents[] = $this->transformDocumentFromSAP($doc);
            }
        }
        
        return array(
            'documents' => $documents,
            'count' => isset($response['@odata.count']) ? $response['@odata.count'] : count($documents),
            'nextLink' => isset($response['@odata.nextLink']) ? $response['@odata.nextLink'] : null
        );
    }
    
    /**
     * Update document metadata
     * 
     * @param string $documentId Document ID
     * @param array $metadata New metadata
     * @return array Update response
     */
    public function updateDocumentMetadata($documentId, $metadata) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        $url = $endpoint . "('" . urlencode($documentId) . "')";
        
        $sapMetadata = $this->transformDocumentMetadataForSAP($metadata);
        
        return $this->makeApiRequest('PATCH', $url, $sapMetadata);
    }
    
    /**
     * Delete document
     * 
     * @param string $documentId Document ID
     * @return bool Deletion success
     */
    public function deleteDocument($documentId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        $url = $endpoint . "('" . urlencode($documentId) . "')";
        
        try {
            $this->makeApiRequest('DELETE', $url);
            return true;
        } catch (SAPException $e) {
            if ($e->getCode() === 404) {
                return true; // Already deleted
            }
            throw $e;
        }
    }
    
    /**
     * Get document download URL
     * 
     * @param string $documentId Document ID
     * @return string Download URL
     */
    public function getDocumentDownloadUrl($documentId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        $url = $endpoint . "('" . urlencode($documentId) . "')/\$value";
        
        return $this->apiBaseUrl . $url;
    }
    
    /**
     * Validate file before upload
     * 
     * @param string $filePath File path
     * @throws SAPException If validation fails
     */
    private function validateFile($filePath) {
        // Check if file exists
        if (!file_exists($filePath)) {
            throw new SAPException('File not found: ' . $filePath);
        }
        
        // Check if file is readable
        if (!is_readable($filePath)) {
            throw new SAPException('File is not readable: ' . $filePath);
        }
        
        // Check file size
        $fileSize = filesize($filePath);
        if ($fileSize > $this->maxFileSize) {
            throw new SAPException('File size exceeds maximum allowed size (' . 
                                 $this->formatFileSize($this->maxFileSize) . '): ' . 
                                 $this->formatFileSize($fileSize));
        }
        
        // Check file type
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $this->allowedFileTypes)) {
            throw new SAPException('File type not allowed: ' . $fileExtension . 
                                 '. Allowed types: ' . implode(', ', $this->allowedFileTypes));
        }
        
        // Check MIME type
        $mimeType = $this->getMimeType($filePath);
        if (!$this->isAllowedMimeType($mimeType)) {
            throw new SAPException('MIME type not allowed: ' . $mimeType);
        }
    }
    
    /**
     * Prepare document metadata
     * 
     * @param string $filePath File path
     * @param array $metadata Additional metadata
     * @return array Complete metadata
     */
    private function prepareDocumentMetadata($filePath, $metadata) {
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = $this->getMimeType($filePath);
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $documentMetadata = array_merge(array(
            'fileName' => $fileName,
            'fileSize' => $fileSize,
            'mimeType' => $mimeType,
            'fileExtension' => $fileExtension,
            'uploadedBy' => $this->getCurrentUserId(),
            'category' => 'BackCheck Document',
            'description' => 'Document uploaded via BackCheck SAP integration'
        ), $metadata);
        
        return $documentMetadata;
    }
    
    /**
     * Create document record in SAP
     * 
     * @param array $metadata Document metadata
     * @return array Document record
     */
    private function createDocumentRecord($metadata) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        
        $sapMetadata = $this->transformDocumentMetadataForSAP($metadata);
        
        $response = $this->makeApiRequest('POST', $endpoint, $sapMetadata);
        
        return array(
            'documentId' => $response['documentId'] ?? $response['id'] ?? uniqid(),
            'metadata' => $response
        );
    }
    
    /**
     * Upload file content to document
     * 
     * @param string $documentId Document ID
     * @param string $filePath File path
     * @return array Upload result
     */
    private function uploadFileContent($documentId, $filePath) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        $url = $endpoint . "('" . urlencode($documentId) . "')/content";
        
        // Read file content
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new SAPException('Failed to read file: ' . $filePath);
        }
        
        // Prepare headers for binary upload
        $headers = array(
            'Content-Type: ' . $this->getMimeType($filePath),
            'Content-Length: ' . strlen($fileContent)
        );
        
        return $this->makeBinaryApiRequest('PUT', $url, $fileContent, $headers);
    }
    
    /**
     * Finalize document after upload
     * 
     * @param string $documentId Document ID
     * @return array Finalize result
     */
    private function finalizeDocument($documentId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        $url = $endpoint . "('" . urlencode($documentId) . "')/finalize";
        
        try {
            return $this->makeApiRequest('POST', $url, array('status' => 'finalized'));
        } catch (SAPException $e) {
            // Some SAP versions may not have finalize endpoint
            if ($e->getCode() === 404) {
                return array('success' => true);
            }
            throw $e;
        }
    }
    
    /**
     * Download file content from document
     * 
     * @param string $documentId Document ID
     * @return string File content
     */
    private function downloadFileContent($documentId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.documents', '/Document');
        $url = $endpoint . "('" . urlencode($documentId) . "')/\$value";
        
        return $this->makeBinaryApiRequest('GET', $url);
    }
    
    /**
     * Make API request to SAP SuccessFactors
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param array $headers Additional headers
     * @return array Response data
     */
    private function makeApiRequest($method, $endpoint, $data = null, $headers = array()) {
        $url = $this->apiBaseUrl . $endpoint;
        
        // Prepare headers
        $defaultHeaders = array(
            'Authorization: ' . $this->authHandler->getAuthorizationHeader(),
            'Accept: application/json',
            'Content-Type: application/json'
        );
        $headers = array_merge($defaultHeaders, $headers);
        
        // Initialize cURL
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->config->isSSLVerificationEnabled(),
            CURLOPT_TIMEOUT => $this->config->getTimeout(),
            CURLOPT_USERAGENT => $this->config->getUserAgent(),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method
        ));
        
        // Add request body for POST/PUT/PATCH
        if (in_array($method, array('POST', 'PUT', 'PATCH')) && $data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        // Execute request with retry logic
        $response = $this->executeWithRetry($ch);
        
        curl_close($ch);
        
        return $response;
    }
    
    /**
     * Make binary API request for file upload/download
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param string $data Binary data
     * @param array $headers Additional headers
     * @return string|array Response data
     */
    private function makeBinaryApiRequest($method, $endpoint, $data = null, $headers = array()) {
        $url = $this->apiBaseUrl . $endpoint;
        
        // Prepare headers
        $defaultHeaders = array(
            'Authorization: ' . $this->authHandler->getAuthorizationHeader()
        );
        $headers = array_merge($defaultHeaders, $headers);
        
        // Initialize cURL
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->config->isSSLVerificationEnabled(),
            CURLOPT_TIMEOUT => $this->config->getTimeout() * 2, // Double timeout for file operations
            CURLOPT_USERAGENT => $this->config->getUserAgent(),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method
        ));
        
        // Add binary data for PUT
        if ($method === 'PUT' && $data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new SAPException('cURL error: ' . $error);
        }
        
        if ($httpCode >= 400) {
            throw new SAPException('HTTP ' . $httpCode . ' error: ' . $response, $httpCode);
        }
        
        // For GET requests, return raw binary data
        if ($method === 'GET') {
            return $response;
        }
        
        // For other methods, try to parse JSON response
        $responseData = json_decode($response, true);
        return $responseData ?: array('success' => true);
    }
    
    /**
     * Execute cURL request with retry logic
     * 
     * @param resource $ch cURL handle
     * @return array Response data
     */
    private function executeWithRetry($ch) {
        $maxAttempts = $this->config->getRetryAttempts();
        $delay = $this->config->getRetryDelay();
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($error) {
                if ($attempt === $maxAttempts) {
                    throw new SAPException('cURL error after ' . $maxAttempts . ' attempts: ' . $error);
                }
                sleep($delay);
                $delay *= 2; // Exponential backoff
                continue;
            }
            
            // Parse response
            $responseData = json_decode($response, true);
            
            // Handle HTTP errors
            if ($httpCode >= 400) {
                $errorMessage = $this->extractErrorMessage($responseData, $httpCode);
                
                // Retry on server errors (5xx) or rate limiting (429)
                if (($httpCode >= 500 || $httpCode === 429) && $attempt < $maxAttempts) {
                    sleep($delay);
                    $delay *= 2;
                    continue;
                }
                
                throw new SAPException($errorMessage, $httpCode);
            }
            
            return $responseData ?: array();
        }
        
        throw new SAPException('Request failed after ' . $maxAttempts . ' attempts');
    }
    
    /**
     * Transform document metadata for SAP format
     * 
     * @param array $metadata BackCheck metadata
     * @return array SAP formatted metadata
     */
    private function transformDocumentMetadataForSAP($metadata) {
        return array(
            'name' => $metadata['fileName'] ?? '',
            'description' => $metadata['description'] ?? '',
            'category' => $metadata['category'] ?? 'Document',
            'mimeType' => $metadata['mimeType'] ?? 'application/octet-stream',
            'size' => $metadata['fileSize'] ?? 0,
            'author' => $metadata['uploadedBy'] ?? 'System',
            'tags' => $metadata['tags'] ?? array(),
            'properties' => array(
                'source' => 'BackCheck',
                'integration_version' => '1.0.0'
            )
        );
    }
    
    /**
     * Transform document from SAP format
     * 
     * @param array $sapDocument SAP document data
     * @return array BackCheck formatted document
     */
    private function transformDocumentFromSAP($sapDocument) {
        return array(
            'documentId' => $sapDocument['id'] ?? $sapDocument['documentId'] ?? '',
            'fileName' => $sapDocument['name'] ?? '',
            'description' => $sapDocument['description'] ?? '',
            'category' => $sapDocument['category'] ?? '',
            'mimeType' => $sapDocument['mimeType'] ?? '',
            'fileSize' => $sapDocument['size'] ?? 0,
            'author' => $sapDocument['author'] ?? '',
            'createdAt' => $sapDocument['createdAt'] ?? '',
            'updatedAt' => $sapDocument['updatedAt'] ?? '',
            'downloadUrl' => $this->getDocumentDownloadUrl($sapDocument['id'] ?? $sapDocument['documentId'] ?? ''),
            'tags' => $sapDocument['tags'] ?? array()
        );
    }
    
    /**
     * Get MIME type of file
     * 
     * @param string $filePath File path
     * @return string MIME type
     */
    private function getMimeType($filePath) {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }
        
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($finfo, $filePath);
        }
        
        // Fallback based on file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = array(
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        
        return isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';
    }
    
    /**
     * Check if MIME type is allowed
     * 
     * @param string $mimeType MIME type
     * @return bool Allowed status
     */
    private function isAllowedMimeType($mimeType) {
        $allowedMimeTypes = array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
            'text/csv',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        
        return in_array($mimeType, $allowedMimeTypes);
    }
    
    /**
     * Format file size in human readable format
     * 
     * @param int $bytes File size in bytes
     * @return string Formatted size
     */
    private function formatFileSize($bytes) {
        $units = array('B', 'KB', 'MB', 'GB');
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
    
    /**
     * Get current user ID
     * 
     * @return string User ID
     */
    private function getCurrentUserId() {
        global $USER;
        return isset($USER['user_id']) ? $USER['user_id'] : 'system';
    }
    
    /**
     * Extract error message from API response
     * 
     * @param array $responseData Response data
     * @param int $httpCode HTTP status code
     * @return string Error message
     */
    private function extractErrorMessage($responseData, $httpCode) {
        if (isset($responseData['error']['message']['value'])) {
            return $responseData['error']['message']['value'];
        }
        
        if (isset($responseData['error']['message'])) {
            return $responseData['error']['message'];
        }
        
        if (isset($responseData['message'])) {
            return $responseData['message'];
        }
        
        return 'HTTP ' . $httpCode . ' error occurred';
    }
}