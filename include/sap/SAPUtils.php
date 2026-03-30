<?php

/**
 * SAP SuccessFactors Utility Classes
 * 
 * Provides utility functions for data transformation, validation,
 * and common operations in SAP SuccessFactors integration.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

/**
 * Data Transformation Utilities
 */
class SAPDataTransformer {
    
    /**
     * Transform BackCheck employee data to SAP format
     * 
     * @param array $backcheckData BackCheck employee data
     * @return array SAP formatted data
     */
    public static function transformEmployeeToSAP($backcheckData) {
        $mapping = array(
            'employee_id' => 'userId',
            'first_name' => 'firstName',
            'last_name' => 'lastName',
            'email' => 'email',
            'phone' => 'phoneNumber',
            'birth_date' => 'dateOfBirth',
            'hire_date' => 'startDate',
            'job_title' => 'title',
            'department' => 'department',
            'manager_id' => 'manager',
            'location' => 'location',
            'employment_type' => 'employmentType',
            'status' => 'status',
            'social_security_number' => 'ssn',
            'address_line1' => 'addressLine1',
            'address_line2' => 'addressLine2',
            'city' => 'city',
            'state' => 'state',
            'postal_code' => 'zipCode',
            'country' => 'country'
        );
        
        return self::mapFields($backcheckData, $mapping);
    }
    
    /**
     * Transform SAP employee data to BackCheck format
     * 
     * @param array $sapData SAP employee data
     * @return array BackCheck formatted data
     */
    public static function transformEmployeeFromSAP($sapData) {
        $mapping = array(
            'userId' => 'employee_id',
            'firstName' => 'first_name',
            'lastName' => 'last_name',
            'email' => 'email',
            'phoneNumber' => 'phone',
            'dateOfBirth' => 'birth_date',
            'startDate' => 'hire_date',
            'title' => 'job_title',
            'department' => 'department',
            'manager' => 'manager_id',
            'location' => 'location',
            'employmentType' => 'employment_type',
            'status' => 'status',
            'ssn' => 'social_security_number',
            'addressLine1' => 'address_line1',
            'addressLine2' => 'address_line2',
            'city' => 'city',
            'state' => 'state',
            'zipCode' => 'postal_code',
            'country' => 'country'
        );
        
        return self::mapFields($sapData, $mapping);
    }
    
    /**
     * Transform background check data to SAP format
     * 
     * @param array $backcheckData BackCheck data
     * @return array SAP formatted data
     */
    public static function transformBackgroundCheckToSAP($backcheckData) {
        $mapping = array(
            'check_id' => 'id',
            'employee_id' => 'employeeId',
            'check_type' => 'checkType',
            'status' => 'status',
            'result' => 'result',
            'completed_date' => 'completedDate',
            'vendor' => 'vendor',
            'comments' => 'comments',
            'document_ids' => 'documentIds',
            'created_date' => 'createdDate',
            'updated_date' => 'lastModified'
        );
        
        $sapData = self::mapFields($backcheckData, $mapping);
        
        // Transform status to SAP values
        if (isset($sapData['status'])) {
            $sapData['status'] = self::transformBackgroundCheckStatus($sapData['status']);
        }
        
        // Transform result to SAP values
        if (isset($sapData['result'])) {
            $sapData['result'] = self::transformBackgroundCheckResult($sapData['result']);
        }
        
        return $sapData;
    }
    
    /**
     * Transform background check data from SAP format
     * 
     * @param array $sapData SAP data
     * @return array BackCheck formatted data
     */
    public static function transformBackgroundCheckFromSAP($sapData) {
        $mapping = array(
            'id' => 'check_id',
            'employeeId' => 'employee_id',
            'checkType' => 'check_type',
            'status' => 'status',
            'result' => 'result',
            'completedDate' => 'completed_date',
            'vendor' => 'vendor',
            'comments' => 'comments',
            'documentIds' => 'document_ids',
            'createdDate' => 'created_date',
            'lastModified' => 'updated_date'
        );
        
        return self::mapFields($sapData, $mapping);
    }
    
    /**
     * Map fields from source to target format
     * 
     * @param array $sourceData Source data
     * @param array $mapping Field mapping
     * @return array Mapped data
     */
    private static function mapFields($sourceData, $mapping) {
        $result = array();
        
        foreach ($mapping as $sourceField => $targetField) {
            if (isset($sourceData[$sourceField])) {
                $value = $sourceData[$sourceField];
                
                // Transform dates
                if (self::isDateField($targetField) && !empty($value)) {
                    $value = self::transformDate($value);
                }
                
                $result[$targetField] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Transform background check status to SAP format
     * 
     * @param string $status BackCheck status
     * @return string SAP status
     */
    private static function transformBackgroundCheckStatus($status) {
        $statusMapping = array(
            'pending' => 'IN_PROGRESS',
            'in_progress' => 'IN_PROGRESS',
            'completed' => 'COMPLETED',
            'cancelled' => 'CANCELLED',
            'on_hold' => 'ON_HOLD',
            'failed' => 'FAILED'
        );
        
        return isset($statusMapping[$status]) ? $statusMapping[$status] : $status;
    }
    
    /**
     * Transform background check result to SAP format
     * 
     * @param string $result BackCheck result
     * @return string SAP result
     */
    private static function transformBackgroundCheckResult($result) {
        $resultMapping = array(
            'clear' => 'CLEAR',
            'consider' => 'CONSIDER',
            'not_clear' => 'NOT_CLEAR',
            'pending' => 'PENDING',
            'dispute' => 'DISPUTE'
        );
        
        return isset($resultMapping[$result]) ? $resultMapping[$result] : $result;
    }
    
    /**
     * Transform date to ISO format
     * 
     * @param string $date Date string
     * @return string ISO formatted date
     */
    private static function transformDate($date) {
        if (empty($date)) return null;
        
        $timestamp = strtotime($date);
        if ($timestamp === false) return $date;
        
        return date('c', $timestamp); // ISO 8601 format
    }
    
    /**
     * Check if field is a date field
     * 
     * @param string $fieldName Field name
     * @return bool Is date field
     */
    private static function isDateField($fieldName) {
        $dateFields = array(
            'dateOfBirth', 'startDate', 'endDate', 'completedDate',
            'createdDate', 'lastModified', 'birth_date', 'hire_date',
            'completed_date', 'created_date', 'updated_date'
        );
        
        return in_array($fieldName, $dateFields);
    }
}

/**
 * Data Validation Utilities
 */
class SAPDataValidator {
    
    /**
     * Validate employee data
     * 
     * @param array $data Employee data
     * @return array Validation errors (empty if valid)
     */
    public static function validateEmployee($data) {
        $errors = array();
        
        // Required fields
        $requiredFields = array('userId', 'firstName', 'lastName');
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Field '{$field}' is required";
            }
        }
        
        // Email validation
        if (isset($data['email']) && !empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
        }
        
        // Phone validation
        if (isset($data['phoneNumber']) && !empty($data['phoneNumber'])) {
            if (!self::isValidPhone($data['phoneNumber'])) {
                $errors['phoneNumber'] = 'Invalid phone number format';
            }
        }
        
        // Date validation
        $dateFields = array('dateOfBirth', 'startDate', 'endDate');
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                if (!self::isValidDate($data[$field])) {
                    $errors[$field] = "Invalid date format for field '{$field}'";
                }
            }
        }
        
        // User ID format validation
        if (isset($data['userId']) && !self::isValidUserId($data['userId'])) {
            $errors['userId'] = 'User ID must be alphanumeric and 3-20 characters long';
        }
        
        return $errors;
    }
    
    /**
     * Validate background check data
     * 
     * @param array $data Background check data
     * @return array Validation errors
     */
    public static function validateBackgroundCheck($data) {
        $errors = array();
        
        // Required fields
        $requiredFields = array('employeeId', 'checkType');
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Field '{$field}' is required";
            }
        }
        
        // Check type validation
        if (isset($data['checkType']) && !self::isValidCheckType($data['checkType'])) {
            $errors['checkType'] = 'Invalid check type';
        }
        
        // Status validation
        if (isset($data['status']) && !self::isValidCheckStatus($data['status'])) {
            $errors['status'] = 'Invalid check status';
        }
        
        // Result validation
        if (isset($data['result']) && !self::isValidCheckResult($data['result'])) {
            $errors['result'] = 'Invalid check result';
        }
        
        // Date validation
        if (isset($data['completedDate']) && !empty($data['completedDate'])) {
            if (!self::isValidDate($data['completedDate'])) {
                $errors['completedDate'] = 'Invalid completed date format';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate document metadata
     * 
     * @param array $metadata Document metadata
     * @return array Validation errors
     */
    public static function validateDocumentMetadata($metadata) {
        $errors = array();
        
        // Required fields
        if (empty($metadata['fileName'])) {
            $errors['fileName'] = 'File name is required';
        }
        
        if (empty($metadata['mimeType'])) {
            $errors['mimeType'] = 'MIME type is required';
        }
        
        // File size validation
        if (isset($metadata['fileSize']) && $metadata['fileSize'] > 10 * 1024 * 1024) {
            $errors['fileSize'] = 'File size exceeds 10MB limit';
        }
        
        // MIME type validation
        if (isset($metadata['mimeType']) && !self::isValidMimeType($metadata['mimeType'])) {
            $errors['mimeType'] = 'MIME type not allowed';
        }
        
        return $errors;
    }
    
    /**
     * Check if date is valid
     * 
     * @param string $date Date string
     * @return bool Is valid
     */
    private static function isValidDate($date) {
        if (empty($date)) return false;
        
        $timestamp = strtotime($date);
        return $timestamp !== false && $timestamp > 0;
    }
    
    /**
     * Check if phone number is valid
     * 
     * @param string $phone Phone number
     * @return bool Is valid
     */
    private static function isValidPhone($phone) {
        // Basic phone validation - allows various formats
        $pattern = '/^[\+]?[1-9][\d\s\-\(\)\.]{7,15}$/';
        return preg_match($pattern, $phone);
    }
    
    /**
     * Check if user ID is valid
     * 
     * @param string $userId User ID
     * @return bool Is valid
     */
    private static function isValidUserId($userId) {
        // Alphanumeric, 3-20 characters, may include underscores and hyphens
        $pattern = '/^[a-zA-Z0-9_-]{3,20}$/';
        return preg_match($pattern, $userId);
    }
    
    /**
     * Check if check type is valid
     * 
     * @param string $checkType Check type
     * @return bool Is valid
     */
    private static function isValidCheckType($checkType) {
        $validTypes = array(
            'criminal', 'employment', 'education', 'reference',
            'credit', 'driving', 'professional_license', 'identity',
            'drug_screen', 'medical', 'social_media'
        );
        
        return in_array(strtolower($checkType), $validTypes);
    }
    
    /**
     * Check if check status is valid
     * 
     * @param string $status Check status
     * @return bool Is valid
     */
    private static function isValidCheckStatus($status) {
        $validStatuses = array(
            'pending', 'in_progress', 'completed', 'cancelled',
            'on_hold', 'failed', 'IN_PROGRESS', 'COMPLETED',
            'CANCELLED', 'ON_HOLD', 'FAILED'
        );
        
        return in_array($status, $validStatuses);
    }
    
    /**
     * Check if check result is valid
     * 
     * @param string $result Check result
     * @return bool Is valid
     */
    private static function isValidCheckResult($result) {
        $validResults = array(
            'clear', 'consider', 'not_clear', 'pending', 'dispute',
            'CLEAR', 'CONSIDER', 'NOT_CLEAR', 'PENDING', 'DISPUTE'
        );
        
        return in_array($result, $validResults);
    }
    
    /**
     * Check if MIME type is valid
     * 
     * @param string $mimeType MIME type
     * @return bool Is valid
     */
    private static function isValidMimeType($mimeType) {
        $allowedTypes = array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        
        return in_array($mimeType, $allowedTypes);
    }
}

/**
 * API Response Utilities
 */
class SAPResponseFormatter {
    
    /**
     * Format success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @return array Formatted response
     */
    public static function success($data = null, $message = 'Operation completed successfully') {
        $response = array(
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        );
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Format error response
     * 
     * @param string $message Error message
     * @param int $code Error code
     * @param mixed $details Error details
     * @return array Formatted response
     */
    public static function error($message, $code = 500, $details = null) {
        $response = array(
            'success' => false,
            'error' => true,
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        );
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        return $response;
    }
    
    /**
     * Format validation error response
     * 
     * @param array $validationErrors Validation errors
     * @param string $message Error message
     * @return array Formatted response
     */
    public static function validationError($validationErrors, $message = 'Validation failed') {
        return array(
            'success' => false,
            'error' => true,
            'message' => $message,
            'code' => 400,
            'validation_errors' => $validationErrors,
            'timestamp' => date('Y-m-d H:i:s')
        );
    }
    
    /**
     * Format paginated response
     * 
     * @param array $data Response data
     * @param int $total Total records
     * @param int $page Current page
     * @param int $perPage Records per page
     * @param string $nextLink Next page link
     * @return array Formatted response
     */
    public static function paginated($data, $total, $page = 1, $perPage = 10, $nextLink = null) {
        $totalPages = ceil($total / $perPage);
        
        return array(
            'success' => true,
            'data' => $data,
            'pagination' => array(
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1,
                'next_link' => $nextLink
            ),
            'timestamp' => date('Y-m-d H:i:s')
        );
    }
}

/**
 * Logging Utilities
 */
class SAPLogger {
    
    private static $logFile = '/tmp/sap_connector.log';
    private static $maxFileSize = 10485760; // 10MB
    
    /**
     * Log message
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function log($level, $message, $context = array()) {
        $logEntry = array(
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        );
        
        $logLine = json_encode($logEntry) . "\n";
        
        // Check file size and rotate if necessary
        self::rotateLogFile();
        
        // Write to log file
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log info message
     * 
     * @param string $message Message
     * @param array $context Context
     */
    public static function info($message, $context = array()) {
        self::log('info', $message, $context);
    }
    
    /**
     * Log warning message
     * 
     * @param string $message Message
     * @param array $context Context
     */
    public static function warning($message, $context = array()) {
        self::log('warning', $message, $context);
    }
    
    /**
     * Log error message
     * 
     * @param string $message Message
     * @param array $context Context
     */
    public static function error($message, $context = array()) {
        self::log('error', $message, $context);
    }
    
    /**
     * Log debug message
     * 
     * @param string $message Message
     * @param array $context Context
     */
    public static function debug($message, $context = array()) {
        self::log('debug', $message, $context);
    }
    
    /**
     * Rotate log file if it exceeds maximum size
     */
    private static function rotateLogFile() {
        if (!file_exists(self::$logFile)) {
            return;
        }
        
        if (filesize(self::$logFile) > self::$maxFileSize) {
            $rotatedFile = self::$logFile . '.' . date('Y-m-d-H-i-s');
            rename(self::$logFile, $rotatedFile);
            
            // Keep only last 5 rotated files
            $pattern = self::$logFile . '.*';
            $files = glob($pattern);
            if (count($files) > 5) {
                arsort($files);
                $filesToDelete = array_slice($files, 5);
                foreach ($filesToDelete as $file) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * Set log file path
     * 
     * @param string $logFile Log file path
     */
    public static function setLogFile($logFile) {
        self::$logFile = $logFile;
    }
    
    /**
     * Set maximum file size
     * 
     * @param int $maxSize Maximum file size in bytes
     */
    public static function setMaxFileSize($maxSize) {
        self::$maxFileSize = $maxSize;
    }
}