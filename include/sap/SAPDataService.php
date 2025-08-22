<?php

/**
 * SAP SuccessFactors Data Service
 * 
 * Handles CRUD operations for employee data, organizational data,
 * background check results, and other business objects.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

require_once dirname(__FILE__) . '/SAPException.php';

class SAPDataService {
    
    private $config;
    private $authHandler;
    private $apiBaseUrl;
    
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
    }
    
    /**
     * Create employee record
     * 
     * @param array $employeeData Employee data
     * @return array API response
     */
    public function createEmployee($employeeData) {
        $endpoint = $this->config->getConfigValue('api.endpoints.employees', '/Employee');
        
        // Transform data to SAP format
        $sapData = $this->transformEmployeeDataForSAP($employeeData);
        
        // Validate required fields
        $this->validateEmployeeData($sapData);
        
        return $this->makeApiRequest('POST', $endpoint, $sapData);
    }
    
    /**
     * Get employee by ID
     * 
     * @param string $employeeId Employee ID
     * @return array Employee data
     */
    public function getEmployee($employeeId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.employees', '/Employee');
        $url = $endpoint . "('" . urlencode($employeeId) . "')";
        
        $response = $this->makeApiRequest('GET', $url);
        
        // Transform SAP data to BackCheck format
        return $this->transformEmployeeDataFromSAP($response);
    }
    
    /**
     * Update employee record
     * 
     * @param string $employeeId Employee ID
     * @param array $updateData Data to update
     * @return array API response
     */
    public function updateEmployee($employeeId, $updateData) {
        $endpoint = $this->config->getConfigValue('api.endpoints.employees', '/Employee');
        $url = $endpoint . "('" . urlencode($employeeId) . "')";
        
        // Transform data to SAP format
        $sapData = $this->transformEmployeeDataForSAP($updateData, true);
        
        return $this->makeApiRequest('PATCH', $url, $sapData);
    }
    
    /**
     * Delete employee record
     * 
     * @param string $employeeId Employee ID
     * @return bool Deletion success
     */
    public function deleteEmployee($employeeId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.employees', '/Employee');
        $url = $endpoint . "('" . urlencode($employeeId) . "')";
        
        $response = $this->makeApiRequest('DELETE', $url);
        
        return isset($response['success']) ? $response['success'] : true;
    }
    
    /**
     * Search employees
     * 
     * @param array $criteria Search criteria
     * @param array $options Query options (select, expand, top, skip)
     * @return array Search results
     */
    public function searchEmployees($criteria = array(), $options = array()) {
        $endpoint = $this->config->getConfigValue('api.endpoints.employees', '/Employee');
        
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
        
        if (isset($options['expand'])) {
            $queryParams['$expand'] = is_array($options['expand']) ? 
                                     implode(',', $options['expand']) : 
                                     $options['expand'];
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
        
        // Transform results
        $results = array();
        if (isset($response['value']) && is_array($response['value'])) {
            foreach ($response['value'] as $employee) {
                $results[] = $this->transformEmployeeDataFromSAP($employee);
            }
        }
        
        return array(
            'results' => $results,
            'count' => isset($response['@odata.count']) ? $response['@odata.count'] : count($results),
            'nextLink' => isset($response['@odata.nextLink']) ? $response['@odata.nextLink'] : null
        );
    }
    
    /**
     * Create background check result
     * 
     * @param string $employeeId Employee ID
     * @param array $checkData Background check data
     * @return array API response
     */
    public function createBackgroundCheckResult($employeeId, $checkData) {
        $endpoint = $this->config->getConfigValue('api.endpoints.background_checks', '/BackgroundCheck');
        
        // Transform data to SAP format
        $sapData = $this->transformBackgroundCheckDataForSAP($employeeId, $checkData);
        
        return $this->makeApiRequest('POST', $endpoint, $sapData);
    }
    
    /**
     * Get background check results for employee
     * 
     * @param string $employeeId Employee ID
     * @return array Background check results
     */
    public function getBackgroundCheckResults($employeeId) {
        $endpoint = $this->config->getConfigValue('api.endpoints.background_checks', '/BackgroundCheck');
        $url = $endpoint . "?\$filter=employeeId eq '" . urlencode($employeeId) . "'";
        
        $response = $this->makeApiRequest('GET', $url);
        
        $results = array();
        if (isset($response['value']) && is_array($response['value'])) {
            foreach ($response['value'] as $check) {
                $results[] = $this->transformBackgroundCheckDataFromSAP($check);
            }
        }
        
        return $results;
    }
    
    /**
     * Update background check result
     * 
     * @param string $checkId Check ID
     * @param array $updateData Update data
     * @return array API response
     */
    public function updateBackgroundCheckResult($checkId, $updateData) {
        $endpoint = $this->config->getConfigValue('api.endpoints.background_checks', '/BackgroundCheck');
        $url = $endpoint . "('" . urlencode($checkId) . "')";
        
        $sapData = $this->transformBackgroundCheckDataForSAP(null, $updateData, true);
        
        return $this->makeApiRequest('PATCH', $url, $sapData);
    }
    
    /**
     * Get job requisitions
     * 
     * @param array $criteria Search criteria
     * @param array $options Query options
     * @return array Job requisitions
     */
    public function getJobRequisitions($criteria = array(), $options = array()) {
        $endpoint = $this->config->getConfigValue('api.endpoints.job_requisitions', '/JobRequisition');
        
        $queryParams = array();
        
        // Build filter query
        if (!empty($criteria)) {
            $filters = array();
            foreach ($criteria as $field => $value) {
                $filters[] = $field . " eq '" . $value . "'";
            }
            $queryParams['$filter'] = implode(' and ', $filters);
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
        
        $url = $endpoint;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        $response = $this->makeApiRequest('GET', $url);
        
        return isset($response['value']) ? $response['value'] : array();
    }
    
    /**
     * Get organizational data
     * 
     * @param string $type Organization data type (department, position, etc.)
     * @param array $options Query options
     * @return array Organizational data
     */
    public function getOrganizationalData($type, $options = array()) {
        $endpointMap = array(
            'department' => '/Department',
            'position' => '/Position',
            'company' => '/Company',
            'location' => '/Location',
            'division' => '/Division'
        );
        
        $endpoint = isset($endpointMap[$type]) ? $endpointMap[$type] : '/Organization';
        
        $queryParams = array();
        
        if (isset($options['select'])) {
            $queryParams['$select'] = is_array($options['select']) ? 
                                    implode(',', $options['select']) : 
                                    $options['select'];
        }
        
        if (isset($options['filter'])) {
            $queryParams['$filter'] = $options['filter'];
        }
        
        if (isset($options['top'])) {
            $queryParams['$top'] = intval($options['top']);
        }
        
        $url = $endpoint;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        $response = $this->makeApiRequest('GET', $url);
        
        return isset($response['value']) ? $response['value'] : array();
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
    
    /**
     * Transform employee data for SAP format
     * 
     * @param array $data BackCheck employee data
     * @param bool $isUpdate Whether this is an update operation
     * @return array SAP formatted data
     */
    private function transformEmployeeDataForSAP($data, $isUpdate = false) {
        $sapData = array();
        
        // Field mappings from BackCheck to SAP
        $fieldMappings = array(
            'employeeId' => 'userId',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email',
            'phoneNumber' => 'phoneNumber',
            'dateOfBirth' => 'dateOfBirth',
            'hireDate' => 'startDate',
            'jobTitle' => 'title',
            'department' => 'department',
            'manager' => 'manager',
            'location' => 'location',
            'employmentType' => 'employmentType',
            'status' => 'status'
        );
        
        foreach ($fieldMappings as $backcheckField => $sapField) {
            if (isset($data[$backcheckField])) {
                $sapData[$sapField] = $data[$backcheckField];
            }
        }
        
        // Handle date transformations
        $dateFields = array('dateOfBirth', 'startDate');
        foreach ($dateFields as $field) {
            if (isset($sapData[$field])) {
                $sapData[$field] = $this->transformDate($sapData[$field]);
            }
        }
        
        // Add required fields for new employee creation
        if (!$isUpdate) {
            if (!isset($sapData['userId'])) {
                throw new SAPException('Employee ID is required for new employee creation');
            }
        }
        
        return $sapData;
    }
    
    /**
     * Transform employee data from SAP format
     * 
     * @param array $sapData SAP employee data
     * @return array BackCheck formatted data
     */
    private function transformEmployeeDataFromSAP($sapData) {
        $data = array();
        
        // Field mappings from SAP to BackCheck
        $fieldMappings = array(
            'userId' => 'employeeId',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email',
            'phoneNumber' => 'phoneNumber',
            'dateOfBirth' => 'dateOfBirth',
            'startDate' => 'hireDate',
            'title' => 'jobTitle',
            'department' => 'department',
            'manager' => 'manager',
            'location' => 'location',
            'employmentType' => 'employmentType',
            'status' => 'status'
        );
        
        foreach ($fieldMappings as $sapField => $backcheckField) {
            if (isset($sapData[$sapField])) {
                $data[$backcheckField] = $sapData[$sapField];
            }
        }
        
        return $data;
    }
    
    /**
     * Transform background check data for SAP format
     * 
     * @param string $employeeId Employee ID
     * @param array $checkData Check data
     * @param bool $isUpdate Whether this is an update operation
     * @return array SAP formatted data
     */
    private function transformBackgroundCheckDataForSAP($employeeId, $checkData, $isUpdate = false) {
        $sapData = array();
        
        if (!$isUpdate && $employeeId) {
            $sapData['employeeId'] = $employeeId;
        }
        
        // Map BackCheck fields to SAP fields
        $fieldMappings = array(
            'checkType' => 'checkType',
            'status' => 'status',
            'result' => 'result',
            'completedDate' => 'completedDate',
            'vendor' => 'vendor',
            'comments' => 'comments',
            'documentIds' => 'documentIds'
        );
        
        foreach ($fieldMappings as $backcheckField => $sapField) {
            if (isset($checkData[$backcheckField])) {
                $sapData[$sapField] = $checkData[$backcheckField];
            }
        }
        
        // Handle date transformation
        if (isset($sapData['completedDate'])) {
            $sapData['completedDate'] = $this->transformDate($sapData['completedDate']);
        }
        
        return $sapData;
    }
    
    /**
     * Transform background check data from SAP format
     * 
     * @param array $sapData SAP background check data
     * @return array BackCheck formatted data
     */
    private function transformBackgroundCheckDataFromSAP($sapData) {
        $data = array();
        
        $fieldMappings = array(
            'id' => 'checkId',
            'employeeId' => 'employeeId',
            'checkType' => 'checkType',
            'status' => 'status',
            'result' => 'result',
            'completedDate' => 'completedDate',
            'vendor' => 'vendor',
            'comments' => 'comments',
            'documentIds' => 'documentIds'
        );
        
        foreach ($fieldMappings as $sapField => $backcheckField) {
            if (isset($sapData[$sapField])) {
                $data[$backcheckField] = $sapData[$sapField];
            }
        }
        
        return $data;
    }
    
    /**
     * Validate employee data
     * 
     * @param array $data Employee data
     * @throws SAPException If validation fails
     */
    private function validateEmployeeData($data) {
        $requiredFields = array('userId', 'firstName', 'lastName');
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new SAPException("Required field '{$field}' is missing or empty");
            }
        }
        
        // Validate email format
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new SAPException('Invalid email format');
        }
        
        // Validate date formats
        $dateFields = array('dateOfBirth', 'startDate');
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && !$this->isValidDate($data[$field])) {
                throw new SAPException("Invalid date format for field '{$field}'");
            }
        }
    }
    
    /**
     * Transform date to SAP format
     * 
     * @param string $date Date string
     * @return string SAP formatted date
     */
    private function transformDate($date) {
        if (empty($date)) return null;
        
        $timestamp = strtotime($date);
        if ($timestamp === false) return $date; // Return original if can't parse
        
        return date('Y-m-d\TH:i:s\Z', $timestamp);
    }
    
    /**
     * Check if date is valid
     * 
     * @param string $date Date string
     * @return bool Validity
     */
    private function isValidDate($date) {
        if (empty($date)) return false;
        
        $timestamp = strtotime($date);
        return $timestamp !== false;
    }
}