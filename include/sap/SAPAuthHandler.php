<?php

/**
 * SAP SuccessFactors OAuth 2.0 Authentication Handler
 * 
 * Handles OAuth 2.0 authentication flow, token management,
 * and secure token storage for SAP SuccessFactors integration.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

require_once dirname(__FILE__) . '/SAPException.php';

class SAPAuthHandler {
    
    private $config;
    private $accessToken;
    private $refreshToken;
    private $tokenExpiresAt;
    private $tokenScope;
    
    /**
     * Constructor
     * 
     * @param SAPConfig $config Configuration instance
     */
    public function __construct($config) {
        $this->config = $config;
        $this->loadStoredTokens();
    }
    
    /**
     * Initiate OAuth 2.0 authorization flow
     * 
     * @param array $additionalParams Additional parameters for authorization URL
     * @return string Authorization URL
     */
    public function getAuthorizationUrl($additionalParams = array()) {
        $params = array_merge(array(
            'response_type' => 'code',
            'client_id' => $this->config->getClientId(),
            'redirect_uri' => $this->config->getRedirectUri(),
            'scope' => $this->config->getScope(),
            'state' => $this->generateState()
        ), $additionalParams);
        
        $authUrl = $this->config->getAuthUrl() . '?' . http_build_query($params);
        
        // Store state for verification
        $this->storeState($params['state']);
        
        return $authUrl;
    }
    
    /**
     * Exchange authorization code for access token
     * 
     * @param string $code Authorization code
     * @param string $state State parameter for verification
     * @return bool Authentication success
     */
    public function authenticateWithCode($code, $state = null) {
        try {
            // Verify state if provided
            if ($state !== null && !$this->verifyState($state)) {
                throw new SAPException('Invalid state parameter. Possible CSRF attack.');
            }
            
            $tokenData = $this->exchangeCodeForToken($code);
            
            if ($tokenData) {
                $this->setTokenData($tokenData);
                $this->storeTokens();
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            throw new SAPException('Authentication failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Authenticate using client credentials flow
     * 
     * @return bool Authentication success
     */
    public function authenticateWithClientCredentials() {
        try {
            $tokenData = $this->getClientCredentialsToken();
            
            if ($tokenData) {
                $this->setTokenData($tokenData);
                $this->storeTokens();
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            throw new SAPException('Client credentials authentication failed: ' . $e->getMessage());
        }
    }
    
    /**
     * General authenticate method (tries stored tokens first)
     * 
     * @return bool Authentication success
     */
    public function authenticate() {
        // Check if we have valid stored tokens
        if ($this->hasValidToken()) {
            return true;
        }
        
        // Try to refresh token
        if ($this->refreshToken && $this->refreshToken()) {
            return true;
        }
        
        // Fall back to client credentials if no refresh token
        return $this->authenticateWithClientCredentials();
    }
    
    /**
     * Check if we have a valid access token
     * 
     * @return bool Token validity
     */
    public function hasValidToken() {
        return $this->accessToken && 
               $this->tokenExpiresAt && 
               time() < $this->tokenExpiresAt - 60; // 60 second buffer
    }
    
    /**
     * Get current access token
     * 
     * @return string|null Access token
     */
    public function getAccessToken() {
        return $this->accessToken;
    }
    
    /**
     * Get token expiration time
     * 
     * @return int|null Expiration timestamp
     */
    public function getTokenExpirationTime() {
        return $this->tokenExpiresAt;
    }
    
    /**
     * Get token scope
     * 
     * @return string|null Token scope
     */
    public function getTokenScope() {
        return $this->tokenScope;
    }
    
    /**
     * Refresh access token using refresh token
     * 
     * @return bool Refresh success
     */
    public function refreshToken() {
        if (!$this->refreshToken) {
            return false;
        }
        
        try {
            $tokenData = $this->performTokenRefresh();
            
            if ($tokenData) {
                $this->setTokenData($tokenData);
                $this->storeTokens();
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            // Clear invalid refresh token
            $this->clearTokens();
            throw new SAPException('Token refresh failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Revoke current tokens
     * 
     * @return bool Revocation success
     */
    public function revokeTokens() {
        try {
            if ($this->accessToken) {
                $this->performTokenRevocation($this->accessToken);
            }
            
            if ($this->refreshToken) {
                $this->performTokenRevocation($this->refreshToken);
            }
            
            $this->clearTokens();
            $this->clearStoredTokens();
            
            return true;
            
        } catch (Exception $e) {
            // Even if revocation fails, clear local tokens
            $this->clearTokens();
            $this->clearStoredTokens();
            throw new SAPException('Token revocation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get authorization header for API requests
     * 
     * @return string Authorization header value
     */
    public function getAuthorizationHeader() {
        if (!$this->hasValidToken()) {
            throw new SAPException('No valid access token available');
        }
        
        return 'Bearer ' . $this->accessToken;
    }
    
    /**
     * Exchange authorization code for access token
     * 
     * @param string $code Authorization code
     * @return array|false Token data or false on failure
     */
    private function exchangeCodeForToken($code) {
        $params = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
            'code' => $code,
            'redirect_uri' => $this->config->getRedirectUri()
        );
        
        return $this->makeTokenRequest($params);
    }
    
    /**
     * Get access token using client credentials
     * 
     * @return array|false Token data or false on failure
     */
    private function getClientCredentialsToken() {
        $params = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
            'scope' => $this->config->getScope()
        );
        
        return $this->makeTokenRequest($params);
    }
    
    /**
     * Refresh access token
     * 
     * @return array|false Token data or false on failure
     */
    private function performTokenRefresh() {
        $params = array(
            'grant_type' => 'refresh_token',
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
            'refresh_token' => $this->refreshToken
        );
        
        return $this->makeTokenRequest($params);
    }
    
    /**
     * Make token request to OAuth server
     * 
     * @param array $params Request parameters
     * @return array|false Token data or false on failure
     */
    private function makeTokenRequest($params) {
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->config->getTokenUrl(),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->config->isSSLVerificationEnabled(),
            CURLOPT_TIMEOUT => $this->config->getTimeout(),
            CURLOPT_USERAGENT => $this->config->getUserAgent(),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            )
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new SAPException('cURL error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = isset($errorData['error_description']) ? 
                          $errorData['error_description'] : 
                          'HTTP ' . $httpCode . ': ' . $response;
            throw new SAPException('Token request failed: ' . $errorMessage);
        }
        
        $tokenData = json_decode($response, true);
        
        if (!$tokenData || !isset($tokenData['access_token'])) {
            throw new SAPException('Invalid token response format');
        }
        
        return $tokenData;
    }
    
    /**
     * Revoke a token
     * 
     * @param string $token Token to revoke
     * @return bool Revocation success
     */
    private function performTokenRevocation($token) {
        $params = array(
            'token' => $token,
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret()
        );
        
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->config->getTokenUrl() . '/revoke',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->config->isSSLVerificationEnabled(),
            CURLOPT_TIMEOUT => $this->config->getTimeout(),
            CURLOPT_USERAGENT => $this->config->getUserAgent()
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    /**
     * Set token data from OAuth response
     * 
     * @param array $tokenData Token response data
     */
    private function setTokenData($tokenData) {
        $this->accessToken = $tokenData['access_token'];
        $this->refreshToken = isset($tokenData['refresh_token']) ? $tokenData['refresh_token'] : null;
        $this->tokenScope = isset($tokenData['scope']) ? $tokenData['scope'] : null;
        
        // Calculate expiration time
        $expiresIn = isset($tokenData['expires_in']) ? intval($tokenData['expires_in']) : 3600;
        $this->tokenExpiresAt = time() + $expiresIn;
    }
    
    /**
     * Clear all token data
     */
    private function clearTokens() {
        $this->accessToken = null;
        $this->refreshToken = null;
        $this->tokenExpiresAt = null;
        $this->tokenScope = null;
    }
    
    /**
     * Load stored tokens from database
     */
    private function loadStoredTokens() {
        try {
            global $db;
            if (!$db) return;
            
            $tableName = $this->config->getTokenStorageTable();
            
            // Create table if it doesn't exist
            $this->createTokenTable();
            
            $query = "SELECT * FROM {$tableName} WHERE environment = '{$this->config->getEnvironment()}' ORDER BY created_at DESC LIMIT 1";
            $result = $db->selectq($query);
            
            if ($result && mysql_num_rows($result) > 0) {
                $row = mysql_fetch_assoc($result);
                
                $this->accessToken = $row['access_token'];
                $this->refreshToken = $row['refresh_token'];
                $this->tokenExpiresAt = strtotime($row['expires_at']);
                $this->tokenScope = $row['scope'];
            }
            
        } catch (Exception $e) {
            // Silently fail if database is not available
            error_log('Failed to load stored SAP tokens: ' . $e->getMessage());
        }
    }
    
    /**
     * Store tokens in database
     */
    private function storeTokens() {
        try {
            global $db;
            if (!$db || !$this->accessToken) return;
            
            $tableName = $this->config->getTokenStorageTable();
            
            // Create table if it doesn't exist
            $this->createTokenTable();
            
            // Clear old tokens for this environment
            $deleteQuery = "DELETE FROM {$tableName} WHERE environment = '{$this->config->getEnvironment()}'";
            $db->query($deleteQuery);
            
            // Insert new tokens
            $expiresAt = date('Y-m-d H:i:s', $this->tokenExpiresAt);
            $insertQuery = "INSERT INTO {$tableName} (environment, access_token, refresh_token, expires_at, scope, created_at) VALUES (
                '{$this->config->getEnvironment()}',
                '" . mysql_real_escape_string($this->accessToken) . "',
                '" . mysql_real_escape_string($this->refreshToken) . "',
                '{$expiresAt}',
                '" . mysql_real_escape_string($this->tokenScope) . "',
                NOW()
            )";
            
            $db->query($insertQuery);
            
        } catch (Exception $e) {
            error_log('Failed to store SAP tokens: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear stored tokens from database
     */
    private function clearStoredTokens() {
        try {
            global $db;
            if (!$db) return;
            
            $tableName = $this->config->getTokenStorageTable();
            $deleteQuery = "DELETE FROM {$tableName} WHERE environment = '{$this->config->getEnvironment()}'";
            $db->query($deleteQuery);
            
        } catch (Exception $e) {
            error_log('Failed to clear stored SAP tokens: ' . $e->getMessage());
        }
    }
    
    /**
     * Create token storage table if it doesn't exist
     */
    private function createTokenTable() {
        global $db;
        if (!$db) return;
        
        $tableName = $this->config->getTokenStorageTable();
        
        $createQuery = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            environment VARCHAR(20) NOT NULL,
            access_token TEXT NOT NULL,
            refresh_token TEXT,
            expires_at TIMESTAMP NOT NULL,
            scope VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_environment (environment),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $db->query($createQuery);
    }
    
    /**
     * Generate secure state parameter for OAuth flow
     * 
     * @return string State parameter
     */
    private function generateState() {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Store state parameter for verification
     * 
     * @param string $state State parameter
     */
    private function storeState($state) {
        // Simple session-based storage for now
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['sap_oauth_state'] = $state;
    }
    
    /**
     * Verify state parameter
     * 
     * @param string $state State parameter to verify
     * @return bool Verification result
     */
    private function verifyState($state) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $stored = isset($_SESSION['sap_oauth_state']) ? $_SESSION['sap_oauth_state'] : null;
        unset($_SESSION['sap_oauth_state']);
        
        return $stored && hash_equals($stored, $state);
    }
}