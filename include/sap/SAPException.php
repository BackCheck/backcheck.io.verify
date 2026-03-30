<?php

/**
 * SAP SuccessFactors Custom Exception Classes
 * 
 * Provides comprehensive error handling with specific exception types
 * for different SAP SuccessFactors integration scenarios.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

/**
 * Base SAP Exception Class
 */
class SAPException extends Exception {
    
    protected $errorCode;
    protected $errorDetails;
    protected $httpStatusCode;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param int $code Error code (defaults to HTTP status code)
     * @param Exception $previous Previous exception
     * @param array $details Additional error details
     */
    public function __construct($message = '', $code = 0, Exception $previous = null, $details = array()) {
        parent::__construct($message, $code, $previous);
        
        $this->errorCode = $code;
        $this->errorDetails = $details;
        $this->httpStatusCode = $code;
    }
    
    /**
     * Get error details
     * 
     * @return array Error details
     */
    public function getErrorDetails() {
        return $this->errorDetails;
    }
    
    /**
     * Get HTTP status code
     * 
     * @return int HTTP status code
     */
    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }
    
    /**
     * Set error details
     * 
     * @param array $details Error details
     */
    public function setErrorDetails($details) {
        $this->errorDetails = $details;
    }
    
    /**
     * Add error detail
     * 
     * @param string $key Detail key
     * @param mixed $value Detail value
     */
    public function addErrorDetail($key, $value) {
        $this->errorDetails[$key] = $value;
    }
    
    /**
     * Get formatted error information
     * 
     * @return array Formatted error information
     */
    public function getFormattedError() {
        return array(
            'error' => true,
            'type' => get_class($this),
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'http_status' => $this->getHttpStatusCode(),
            'details' => $this->getErrorDetails(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'timestamp' => date('Y-m-d H:i:s')
        );
    }
    
    /**
     * Convert to JSON string
     * 
     * @return string JSON representation
     */
    public function toJson() {
        return json_encode($this->getFormattedError());
    }
}

/**
 * Authentication Exception
 * 
 * Thrown when authentication or authorization fails
 */
class SAPAuthException extends SAPException {
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param int $code Error code
     * @param Exception $previous Previous exception
     * @param array $details Additional details
     */
    public function __construct($message = 'Authentication failed', $code = 401, Exception $previous = null, $details = array()) {
        parent::__construct($message, $code, $previous, $details);
    }
}

/**
 * Configuration Exception
 * 
 * Thrown when configuration is invalid or missing
 */
class SAPConfigException extends SAPException {
    
    private $configErrors;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param array $configErrors Configuration validation errors
     * @param int $code Error code
     */
    public function __construct($message = 'Configuration error', $configErrors = array(), $code = 500) {
        $this->configErrors = $configErrors;
        
        $details = array('config_errors' => $configErrors);
        parent::__construct($message, $code, null, $details);
    }
    
    /**
     * Get configuration errors
     * 
     * @return array Configuration errors
     */
    public function getConfigErrors() {
        return $this->configErrors;
    }
}

/**
 * API Exception
 * 
 * Thrown when API requests fail
 */
class SAPApiException extends SAPException {
    
    private $requestUrl;
    private $requestMethod;
    private $requestData;
    private $responseData;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param int $httpCode HTTP status code
     * @param string $url Request URL
     * @param string $method Request method
     * @param mixed $requestData Request data
     * @param mixed $responseData Response data
     */
    public function __construct($message, $httpCode = 0, $url = '', $method = '', $requestData = null, $responseData = null) {
        $this->requestUrl = $url;
        $this->requestMethod = $method;
        $this->requestData = $requestData;
        $this->responseData = $responseData;
        
        $details = array(
            'url' => $url,
            'method' => $method,
            'request_data' => $requestData,
            'response_data' => $responseData
        );
        
        parent::__construct($message, $httpCode, null, $details);
    }
    
    /**
     * Get request URL
     * 
     * @return string Request URL
     */
    public function getRequestUrl() {
        return $this->requestUrl;
    }
    
    /**
     * Get request method
     * 
     * @return string Request method
     */
    public function getRequestMethod() {
        return $this->requestMethod;
    }
    
    /**
     * Get request data
     * 
     * @return mixed Request data
     */
    public function getRequestData() {
        return $this->requestData;
    }
    
    /**
     * Get response data
     * 
     * @return mixed Response data
     */
    public function getResponseData() {
        return $this->responseData;
    }
}

/**
 * Rate Limit Exception
 * 
 * Thrown when rate limits are exceeded
 */
class SAPRateLimitException extends SAPException {
    
    private $retryAfter;
    private $limitRemaining;
    private $limitTotal;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param int $retryAfter Seconds to wait before retry
     * @param int $limitRemaining Remaining requests
     * @param int $limitTotal Total request limit
     */
    public function __construct($message = 'Rate limit exceeded', $retryAfter = 60, $limitRemaining = 0, $limitTotal = 0) {
        $this->retryAfter = $retryAfter;
        $this->limitRemaining = $limitRemaining;
        $this->limitTotal = $limitTotal;
        
        $details = array(
            'retry_after' => $retryAfter,
            'limit_remaining' => $limitRemaining,
            'limit_total' => $limitTotal
        );
        
        parent::__construct($message, 429, null, $details);
    }
    
    /**
     * Get retry after seconds
     * 
     * @return int Seconds to wait
     */
    public function getRetryAfter() {
        return $this->retryAfter;
    }
    
    /**
     * Get remaining requests
     * 
     * @return int Remaining requests
     */
    public function getLimitRemaining() {
        return $this->limitRemaining;
    }
    
    /**
     * Get total request limit
     * 
     * @return int Total limit
     */
    public function getLimitTotal() {
        return $this->limitTotal;
    }
}

/**
 * Validation Exception
 * 
 * Thrown when data validation fails
 */
class SAPValidationException extends SAPException {
    
    private $validationErrors;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param array $validationErrors Validation errors
     * @param int $code Error code
     */
    public function __construct($message = 'Validation failed', $validationErrors = array(), $code = 400) {
        $this->validationErrors = $validationErrors;
        
        $details = array('validation_errors' => $validationErrors);
        parent::__construct($message, $code, null, $details);
    }
    
    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getValidationErrors() {
        return $this->validationErrors;
    }
    
    /**
     * Add validation error
     * 
     * @param string $field Field name
     * @param string $error Error message
     */
    public function addValidationError($field, $error) {
        $this->validationErrors[$field] = $error;
        $this->addErrorDetail('validation_errors', $this->validationErrors);
    }
}

/**
 * Document Exception
 * 
 * Thrown when document operations fail
 */
class SAPDocumentException extends SAPException {
    
    private $filePath;
    private $documentId;
    private $operation;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param string $operation Operation that failed
     * @param string $filePath File path (if applicable)
     * @param string $documentId Document ID (if applicable)
     * @param int $code Error code
     */
    public function __construct($message, $operation = '', $filePath = '', $documentId = '', $code = 500) {
        $this->filePath = $filePath;
        $this->documentId = $documentId;
        $this->operation = $operation;
        
        $details = array(
            'operation' => $operation,
            'file_path' => $filePath,
            'document_id' => $documentId
        );
        
        parent::__construct($message, $code, null, $details);
    }
    
    /**
     * Get file path
     * 
     * @return string File path
     */
    public function getFilePath() {
        return $this->filePath;
    }
    
    /**
     * Get document ID
     * 
     * @return string Document ID
     */
    public function getDocumentId() {
        return $this->documentId;
    }
    
    /**
     * Get operation
     * 
     * @return string Operation
     */
    public function getOperation() {
        return $this->operation;
    }
}

/**
 * Connection Exception
 * 
 * Thrown when network/connection issues occur
 */
class SAPConnectionException extends SAPException {
    
    private $endpoint;
    private $timeout;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param string $endpoint Endpoint that failed
     * @param int $timeout Timeout value
     * @param int $code Error code
     */
    public function __construct($message = 'Connection failed', $endpoint = '', $timeout = 0, $code = 500) {
        $this->endpoint = $endpoint;
        $this->timeout = $timeout;
        
        $details = array(
            'endpoint' => $endpoint,
            'timeout' => $timeout
        );
        
        parent::__construct($message, $code, null, $details);
    }
    
    /**
     * Get endpoint
     * 
     * @return string Endpoint
     */
    public function getEndpoint() {
        return $this->endpoint;
    }
    
    /**
     * Get timeout
     * 
     * @return int Timeout
     */
    public function getTimeout() {
        return $this->timeout;
    }
}

/**
 * Data Transformation Exception
 * 
 * Thrown when data transformation fails
 */
class SAPDataException extends SAPException {
    
    private $sourceData;
    private $transformationType;
    
    /**
     * Constructor
     * 
     * @param string $message Error message
     * @param string $transformationType Type of transformation
     * @param mixed $sourceData Source data that failed
     * @param int $code Error code
     */
    public function __construct($message, $transformationType = '', $sourceData = null, $code = 500) {
        $this->transformationType = $transformationType;
        $this->sourceData = $sourceData;
        
        $details = array(
            'transformation_type' => $transformationType,
            'source_data' => $sourceData
        );
        
        parent::__construct($message, $code, null, $details);
    }
    
    /**
     * Get source data
     * 
     * @return mixed Source data
     */
    public function getSourceData() {
        return $this->sourceData;
    }
    
    /**
     * Get transformation type
     * 
     * @return string Transformation type
     */
    public function getTransformationType() {
        return $this->transformationType;
    }
}

/**
 * Exception Factory
 * 
 * Factory class for creating appropriate exception types
 */
class SAPExceptionFactory {
    
    /**
     * Create exception from HTTP response
     * 
     * @param int $httpCode HTTP status code
     * @param string $message Error message
     * @param string $url Request URL
     * @param string $method Request method
     * @param mixed $requestData Request data
     * @param mixed $responseData Response data
     * @return SAPException Appropriate exception
     */
    public static function createFromHttpResponse($httpCode, $message, $url = '', $method = '', $requestData = null, $responseData = null) {
        switch ($httpCode) {
            case 401:
            case 403:
                return new SAPAuthException($message, $httpCode, null, array(
                    'url' => $url,
                    'method' => $method
                ));
                
            case 400:
                return new SAPValidationException($message, array(), $httpCode);
                
            case 429:
                $retryAfter = 60; // Default retry after
                if (is_array($responseData) && isset($responseData['retry_after'])) {
                    $retryAfter = $responseData['retry_after'];
                }
                return new SAPRateLimitException($message, $retryAfter);
                
            case 404:
            case 405:
            case 500:
            case 502:
            case 503:
            case 504:
                return new SAPApiException($message, $httpCode, $url, $method, $requestData, $responseData);
                
            default:
                return new SAPException($message, $httpCode, null, array(
                    'url' => $url,
                    'method' => $method,
                    'request_data' => $requestData,
                    'response_data' => $responseData
                ));
        }
    }
    
    /**
     * Create configuration exception
     * 
     * @param array $configErrors Configuration errors
     * @param string $message Error message
     * @return SAPConfigException
     */
    public static function createConfigException($configErrors, $message = 'Configuration validation failed') {
        return new SAPConfigException($message, $configErrors);
    }
    
    /**
     * Create validation exception
     * 
     * @param array $validationErrors Validation errors
     * @param string $message Error message
     * @return SAPValidationException
     */
    public static function createValidationException($validationErrors, $message = 'Data validation failed') {
        return new SAPValidationException($message, $validationErrors);
    }
    
    /**
     * Create document exception
     * 
     * @param string $operation Operation type
     * @param string $message Error message
     * @param string $filePath File path
     * @param string $documentId Document ID
     * @return SAPDocumentException
     */
    public static function createDocumentException($operation, $message, $filePath = '', $documentId = '') {
        return new SAPDocumentException($message, $operation, $filePath, $documentId);
    }
}