<?php

/**
 * SAP SuccessFactors Configuration Manager
 * 
 * Manages configuration settings for SAP SuccessFactors integration
 * including OAuth credentials, API endpoints, and environment settings.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

class SAPConfig {
    
    private $environment;
    private $config;
    private $defaultConfig;
    
    /**
     * Constructor
     * 
     * @param string $environment Environment (dev, staging, prod)
     * @param array $customConfig Optional custom configuration
     */
    public function __construct($environment = 'prod', $customConfig = array()) {
        $this->environment = $environment;
        $this->initializeDefaultConfig();
        $this->loadConfiguration($customConfig);
    }
    
    /**
     * Get environment
     * 
     * @return string Current environment
     */
    public function getEnvironment() {
        return $this->environment;
    }
    
    /**
     * Get OAuth client ID
     * 
     * @return string Client ID
     */
    public function getClientId() {
        return $this->getConfigValue('oauth.client_id');
    }
    
    /**
     * Get OAuth client secret
     * 
     * @return string Client secret
     */
    public function getClientSecret() {
        return $this->getConfigValue('oauth.client_secret');
    }
    
    /**
     * Get OAuth redirect URI
     * 
     * @return string Redirect URI
     */
    public function getRedirectUri() {
        return $this->getConfigValue('oauth.redirect_uri');
    }
    
    /**
     * Get OAuth scope
     * 
     * @return string OAuth scope
     */
    public function getScope() {
        return $this->getConfigValue('oauth.scope');
    }
    
    /**
     * Get API base URL
     * 
     * @return string API base URL
     */
    public function getApiBaseUrl() {
        return $this->getConfigValue('api.base_url');
    }
    
    /**
     * Get API version
     * 
     * @return string API version
     */
    public function getApiVersion() {
        return $this->getConfigValue('api.version');
    }
    
    /**
     * Get OAuth token URL
     * 
     * @return string Token URL
     */
    public function getTokenUrl() {
        return $this->getConfigValue('oauth.token_url');
    }
    
    /**
     * Get OAuth authorization URL
     * 
     * @return string Authorization URL
     */
    public function getAuthUrl() {
        return $this->getConfigValue('oauth.auth_url');
    }
    
    /**
     * Get connection timeout in seconds
     * 
     * @return int Timeout in seconds
     */
    public function getTimeout() {
        return $this->getConfigValue('connection.timeout');
    }
    
    /**
     * Get SSL verification setting
     * 
     * @return bool SSL verification enabled
     */
    public function isSSLVerificationEnabled() {
        return $this->getConfigValue('connection.ssl_verify');
    }
    
    /**
     * Get rate limit requests per minute
     * 
     * @return int Requests per minute
     */
    public function getRateLimitRequestsPerMinute() {
        return $this->getConfigValue('rate_limit.requests_per_minute');
    }
    
    /**
     * Get retry attempts for failed requests
     * 
     * @return int Number of retry attempts
     */
    public function getRetryAttempts() {
        return $this->getConfigValue('retry.attempts');
    }
    
    /**
     * Get retry delay in seconds
     * 
     * @return int Retry delay in seconds
     */
    public function getRetryDelay() {
        return $this->getConfigValue('retry.delay');
    }
    
    /**
     * Get user agent string
     * 
     * @return string User agent
     */
    public function getUserAgent() {
        return $this->getConfigValue('connection.user_agent');
    }
    
    /**
     * Get logging configuration
     * 
     * @return array Logging configuration
     */
    public function getLoggingConfig() {
        return $this->getConfigValue('logging');
    }
    
    /**
     * Get database table name for token storage
     * 
     * @return string Table name
     */
    public function getTokenStorageTable() {
        return $this->getConfigValue('storage.token_table');
    }
    
    /**
     * Get database table name for configuration storage
     * 
     * @return string Table name
     */
    public function getConfigStorageTable() {
        return $this->getConfigValue('storage.config_table');
    }
    
    /**
     * Get all configuration
     * 
     * @return array Complete configuration
     */
    public function getAllConfig() {
        return $this->config;
    }
    
    /**
     * Update configuration value
     * 
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $value Configuration value
     */
    public function setConfigValue($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = array();
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }
    
    /**
     * Get configuration value
     * 
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public function getConfigValue($key, $default = null) {
        $keys = explode('.', $key);
        $config = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                return $default;
            }
            $config = $config[$k];
        }
        
        return $config;
    }
    
    /**
     * Initialize default configuration
     */
    private function initializeDefaultConfig() {
        $this->defaultConfig = array(
            'oauth' => array(
                'client_id' => '',
                'client_secret' => '',
                'redirect_uri' => '',
                'scope' => 'read write',
                'auth_url' => 'https://api.sap.com/oauth2/authorize',
                'token_url' => 'https://api.sap.com/oauth2/token'
            ),
            'api' => array(
                'base_url' => 'https://api.successfactors.com/odata',
                'version' => 'v1',
                'endpoints' => array(
                    'employees' => '/Employee',
                    'job_requisitions' => '/JobRequisition',
                    'background_checks' => '/BackgroundCheck',
                    'documents' => '/Document'
                )
            ),
            'connection' => array(
                'timeout' => 30,
                'ssl_verify' => true,
                'user_agent' => 'BackCheck SAP SuccessFactors Connector/1.0.0'
            ),
            'rate_limit' => array(
                'requests_per_minute' => 60,
                'requests_per_hour' => 3600
            ),
            'retry' => array(
                'attempts' => 3,
                'delay' => 1,
                'backoff_multiplier' => 2
            ),
            'logging' => array(
                'enabled' => true,
                'level' => 'info',
                'file' => '/tmp/sap_connector.log',
                'max_file_size' => 10485760 // 10MB
            ),
            'storage' => array(
                'token_table' => 'sap_tokens',
                'config_table' => 'sap_config'
            )
        );
    }
    
    /**
     * Load configuration based on environment
     * 
     * @param array $customConfig Custom configuration to merge
     */
    private function loadConfiguration($customConfig = array()) {
        // Start with default configuration
        $this->config = $this->defaultConfig;
        
        // Load environment-specific configuration
        $envConfig = $this->getEnvironmentConfig();
        $this->config = $this->mergeConfigArrays($this->config, $envConfig);
        
        // Merge custom configuration
        if (!empty($customConfig)) {
            $this->config = $this->mergeConfigArrays($this->config, $customConfig);
        }
        
        // Load configuration from database if available
        $this->loadDatabaseConfig();
        
        // Load configuration from environment variables
        $this->loadEnvironmentVariables();
    }
    
    /**
     * Get environment-specific configuration
     * 
     * @return array Environment configuration
     */
    private function getEnvironmentConfig() {
        $envConfigs = array(
            'dev' => array(
                'api' => array(
                    'base_url' => 'https://api-sandbox.successfactors.com/odata'
                ),
                'connection' => array(
                    'ssl_verify' => false
                ),
                'logging' => array(
                    'level' => 'debug'
                )
            ),
            'staging' => array(
                'api' => array(
                    'base_url' => 'https://api-staging.successfactors.com/odata'
                ),
                'rate_limit' => array(
                    'requests_per_minute' => 30
                ),
                'logging' => array(
                    'level' => 'info'
                )
            ),
            'prod' => array(
                'api' => array(
                    'base_url' => 'https://api.successfactors.com/odata'
                ),
                'connection' => array(
                    'ssl_verify' => true
                ),
                'logging' => array(
                    'level' => 'warning'
                )
            )
        );
        
        return isset($envConfigs[$this->environment]) ? $envConfigs[$this->environment] : array();
    }
    
    /**
     * Load configuration from database
     */
    private function loadDatabaseConfig() {
        try {
            global $db;
            if (!$db) return;
            
            $tableName = $this->getConfigValue('storage.config_table');
            $query = "SELECT config_key, config_value FROM {$tableName} WHERE environment = '{$this->environment}'";
            $result = $db->selectq($query);
            
            if ($result && mysql_num_rows($result) > 0) {
                while ($row = mysql_fetch_assoc($result)) {
                    $this->setConfigValue($row['config_key'], json_decode($row['config_value'], true));
                }
            }
        } catch (Exception $e) {
            // Silently fail if database config is not available
            error_log('Failed to load SAP configuration from database: ' . $e->getMessage());
        }
    }
    
    /**
     * Load configuration from environment variables
     */
    private function loadEnvironmentVariables() {
        $envVars = array(
            'SAP_CLIENT_ID' => 'oauth.client_id',
            'SAP_CLIENT_SECRET' => 'oauth.client_secret',
            'SAP_REDIRECT_URI' => 'oauth.redirect_uri',
            'SAP_API_BASE_URL' => 'api.base_url',
            'SAP_TIMEOUT' => 'connection.timeout',
            'SAP_RATE_LIMIT' => 'rate_limit.requests_per_minute'
        );
        
        foreach ($envVars as $envVar => $configKey) {
            $value = getenv($envVar);
            if ($value !== false) {
                // Convert numeric values
                if (is_numeric($value)) {
                    $value = is_float($value + 0) ? floatval($value) : intval($value);
                }
                // Convert boolean values
                elseif (in_array(strtolower($value), array('true', 'false'))) {
                    $value = strtolower($value) === 'true';
                }
                
                $this->setConfigValue($configKey, $value);
            }
        }
    }
    
    /**
     * Validate configuration
     * 
     * @return array Validation errors (empty if valid)
     */
    public function validate() {
        $errors = array();
        
        // Check required OAuth settings
        if (empty($this->getClientId())) {
            $errors[] = 'OAuth client ID is required';
        }
        
        if (empty($this->getClientSecret())) {
            $errors[] = 'OAuth client secret is required';
        }
        
        if (empty($this->getApiBaseUrl())) {
            $errors[] = 'API base URL is required';
        }
        
        // Check URL formats
        if ($this->getApiBaseUrl() && !filter_var($this->getApiBaseUrl(), FILTER_VALIDATE_URL)) {
            $errors[] = 'API base URL must be a valid URL';
        }
        
        if ($this->getRedirectUri() && !filter_var($this->getRedirectUri(), FILTER_VALIDATE_URL)) {
            $errors[] = 'OAuth redirect URI must be a valid URL';
        }
        
        // Check numeric values
        if ($this->getTimeout() < 1) {
            $errors[] = 'Connection timeout must be at least 1 second';
        }
        
        if ($this->getRateLimitRequestsPerMinute() < 1) {
            $errors[] = 'Rate limit must be at least 1 request per minute';
        }
        
        return $errors;
    }
    
    /**
     * Save configuration to database
     * 
     * @param array $config Configuration to save
     * @return bool Save success
     */
    public function saveToDatabase($config = null) {
        try {
            global $db;
            if (!$db) {
                throw new Exception('Database connection not available');
            }
            
            if ($config === null) {
                $config = $this->config;
            }
            
            $tableName = $this->getConfigValue('storage.config_table');
            
            // Create table if it doesn't exist
            $this->createConfigTable();
            
            // Save configuration
            foreach ($config as $key => $value) {
                $configValue = json_encode($value);
                $insertQuery = "INSERT INTO {$tableName} (environment, config_key, config_value, updated_at) 
                               VALUES ('{$this->environment}', '{$key}', '{$configValue}', NOW()) 
                               ON DUPLICATE KEY UPDATE config_value = '{$configValue}', updated_at = NOW()";
                
                $db->query($insertQuery);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to save SAP configuration to database: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create configuration table if it doesn't exist
     */
    private function createConfigTable() {
        global $db;
        if (!$db) return;
        
        $tableName = $this->getConfigValue('storage.config_table');
        
        $createQuery = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            environment VARCHAR(20) NOT NULL,
            config_key VARCHAR(100) NOT NULL,
            config_value TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_env_key (environment, config_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $db->query($createQuery);
    }
    
    /**
     * Merge configuration arrays properly (override instead of append)
     * 
     * @param array $array1 Base array
     * @param array $array2 Override array
     * @return array Merged array
     */
    private function mergeConfigArrays($array1, $array2) {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                // For associative arrays, merge recursively
                if ($this->isAssociativeArray($value) && $this->isAssociativeArray($array1[$key])) {
                    $array1[$key] = $this->mergeConfigArrays($array1[$key], $value);
                } else {
                    // For indexed arrays, replace entirely
                    $array1[$key] = $value;
                }
            } else {
                // Replace scalar values
                $array1[$key] = $value;
            }
        }
        return $array1;
    }
    
    /**
     * Check if array is associative
     * 
     * @param array $array Array to check
     * @return bool Is associative
     */
    private function isAssociativeArray($array) {
        if (!is_array($array) || empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
}