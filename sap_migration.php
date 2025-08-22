<?php

/**
 * SAP SuccessFactors Database Migration
 * 
 * Creates the necessary database tables for SAP SuccessFactors integration
 * including token storage, configuration, and logging tables.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

// Include configuration
require_once dirname(__FILE__) . '/include/config.php';

/**
 * Create SAP integration database tables
 */
function createSAPTables() {
    global $db;
    
    if (!$db) {
        throw new Exception('Database connection not available');
    }
    
    echo "Creating SAP SuccessFactors integration tables...\n";
    
    // Create sap_tokens table
    createTokenTable();
    
    // Create sap_config table
    createConfigTable();
    
    // Create sap_sync_log table
    createSyncLogTable();
    
    // Create sap_webhook_log table
    createWebhookLogTable();
    
    // Create sap_employee_mapping table
    createEmployeeMappingTable();
    
    // Create sap_document_mapping table
    createDocumentMappingTable();
    
    echo "All SAP integration tables created successfully!\n";
}

/**
 * Create sap_tokens table for OAuth token storage
 */
function createTokenTable() {
    global $db;
    
    $sql = "CREATE TABLE IF NOT EXISTS sap_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        environment VARCHAR(20) NOT NULL,
        access_token TEXT NOT NULL,
        refresh_token TEXT,
        expires_at TIMESTAMP NOT NULL,
        scope VARCHAR(255),
        token_type VARCHAR(50) DEFAULT 'Bearer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_environment (environment),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SAP SuccessFactors OAuth tokens'";
    
    if ($db->query($sql)) {
        echo "✓ Created sap_tokens table\n";
    } else {
        throw new Exception('Failed to create sap_tokens table: ' . mysql_error());
    }
}

/**
 * Create sap_config table for configuration storage
 */
function createConfigTable() {
    global $db;
    
    $sql = "CREATE TABLE IF NOT EXISTS sap_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        environment VARCHAR(20) NOT NULL,
        config_key VARCHAR(100) NOT NULL,
        config_value TEXT NOT NULL,
        description TEXT,
        is_encrypted TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_env_key (environment, config_key),
        INDEX idx_environment (environment)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SAP SuccessFactors configuration settings'";
    
    if ($db->query($sql)) {
        echo "✓ Created sap_config table\n";
    } else {
        throw new Exception('Failed to create sap_config table: ' . mysql_error());
    }
}

/**
 * Create sap_sync_log table for synchronization logging
 */
function createSyncLogTable() {
    global $db;
    
    $sql = "CREATE TABLE IF NOT EXISTS sap_sync_log (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        sync_id VARCHAR(50) NOT NULL,
        operation VARCHAR(50) NOT NULL,
        entity_type VARCHAR(50) NOT NULL,
        entity_id VARCHAR(100),
        direction ENUM('to_sap', 'from_sap') NOT NULL,
        status ENUM('pending', 'in_progress', 'completed', 'failed', 'retry') NOT NULL DEFAULT 'pending',
        request_data JSON,
        response_data JSON,
        error_message TEXT,
        retry_count INT DEFAULT 0,
        started_at TIMESTAMP NULL,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_sync_id (sync_id),
        INDEX idx_operation (operation),
        INDEX idx_entity_type (entity_type),
        INDEX idx_entity_id (entity_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SAP SuccessFactors synchronization logs'";
    
    if ($db->query($sql)) {
        echo "✓ Created sap_sync_log table\n";
    } else {
        throw new Exception('Failed to create sap_sync_log table: ' . mysql_error());
    }
}

/**
 * Create sap_webhook_log table for webhook logging
 */
function createWebhookLogTable() {
    global $db;
    
    $sql = "CREATE TABLE IF NOT EXISTS sap_webhook_log (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        webhook_id VARCHAR(100),
        event_type VARCHAR(100) NOT NULL,
        source_system VARCHAR(50) NOT NULL DEFAULT 'sap_successfactors',
        payload JSON,
        headers JSON,
        status ENUM('received', 'processing', 'processed', 'failed') NOT NULL DEFAULT 'received',
        response_data JSON,
        error_message TEXT,
        processed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_webhook_id (webhook_id),
        INDEX idx_event_type (event_type),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SAP SuccessFactors webhook logs'";
    
    if ($db->query($sql)) {
        echo "✓ Created sap_webhook_log table\n";
    } else {
        throw new Exception('Failed to create sap_webhook_log table: ' . mysql_error());
    }
}

/**
 * Create sap_employee_mapping table for employee ID mapping
 */
function createEmployeeMappingTable() {
    global $db;
    
    $sql = "CREATE TABLE IF NOT EXISTS sap_employee_mapping (
        id INT AUTO_INCREMENT PRIMARY KEY,
        backcheck_employee_id VARCHAR(100) NOT NULL,
        sap_employee_id VARCHAR(100) NOT NULL,
        sap_user_id VARCHAR(100),
        mapping_status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
        sync_direction ENUM('bidirectional', 'to_sap_only', 'from_sap_only') NOT NULL DEFAULT 'bidirectional',
        last_sync_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_backcheck_id (backcheck_employee_id),
        UNIQUE KEY unique_sap_id (sap_employee_id),
        INDEX idx_sap_user_id (sap_user_id),
        INDEX idx_mapping_status (mapping_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Employee ID mapping between BackCheck and SAP SuccessFactors'";
    
    if ($db->query($sql)) {
        echo "✓ Created sap_employee_mapping table\n";
    } else {
        throw new Exception('Failed to create sap_employee_mapping table: ' . mysql_error());
    }
}

/**
 * Create sap_document_mapping table for document ID mapping
 */
function createDocumentMappingTable() {
    global $db;
    
    $sql = "CREATE TABLE IF NOT EXISTS sap_document_mapping (
        id INT AUTO_INCREMENT PRIMARY KEY,
        backcheck_document_id VARCHAR(100) NOT NULL,
        sap_document_id VARCHAR(100) NOT NULL,
        employee_id VARCHAR(100),
        document_type VARCHAR(100),
        file_name VARCHAR(255),
        mime_type VARCHAR(100),
        file_size BIGINT,
        upload_status ENUM('pending', 'uploading', 'completed', 'failed') NOT NULL DEFAULT 'pending',
        sync_direction ENUM('bidirectional', 'to_sap_only', 'from_sap_only') NOT NULL DEFAULT 'to_sap_only',
        last_sync_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_backcheck_doc_id (backcheck_document_id),
        INDEX idx_sap_document_id (sap_document_id),
        INDEX idx_employee_id (employee_id),
        INDEX idx_document_type (document_type),
        INDEX idx_upload_status (upload_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Document ID mapping between BackCheck and SAP SuccessFactors'";
    
    if ($db->query($sql)) {
        echo "✓ Created sap_document_mapping table\n";
    } else {
        throw new Exception('Failed to create sap_document_mapping table: ' . mysql_error());
    }
}

/**
 * Insert default configuration values
 */
function insertDefaultConfiguration() {
    global $db;
    
    echo "Inserting default configuration values...\n";
    
    $defaultConfigs = array(
        // Production environment
        array(
            'environment' => 'prod',
            'config_key' => 'api.base_url',
            'config_value' => json_encode('https://api.successfactors.com/odata'),
            'description' => 'SAP SuccessFactors API base URL for production'
        ),
        array(
            'environment' => 'prod',
            'config_key' => 'api.version',
            'config_value' => json_encode('v1'),
            'description' => 'SAP SuccessFactors API version'
        ),
        array(
            'environment' => 'prod',
            'config_key' => 'oauth.auth_url',
            'config_value' => json_encode('https://api.sap.com/oauth2/authorize'),
            'description' => 'OAuth authorization URL'
        ),
        array(
            'environment' => 'prod',
            'config_key' => 'oauth.token_url',
            'config_value' => json_encode('https://api.sap.com/oauth2/token'),
            'description' => 'OAuth token URL'
        ),
        array(
            'environment' => 'prod',
            'config_key' => 'rate_limit.requests_per_minute',
            'config_value' => json_encode(60),
            'description' => 'Rate limit: requests per minute'
        ),
        
        // Development environment
        array(
            'environment' => 'dev',
            'config_key' => 'api.base_url',
            'config_value' => json_encode('https://api-sandbox.successfactors.com/odata'),
            'description' => 'SAP SuccessFactors API base URL for development'
        ),
        array(
            'environment' => 'dev',
            'config_key' => 'rate_limit.requests_per_minute',
            'config_value' => json_encode(30),
            'description' => 'Rate limit: requests per minute (development)'
        ),
        array(
            'environment' => 'dev',
            'config_key' => 'connection.ssl_verify',
            'config_value' => json_encode(false),
            'description' => 'SSL verification (disabled for development)'
        ),
        
        // Staging environment
        array(
            'environment' => 'staging',
            'config_key' => 'api.base_url',
            'config_value' => json_encode('https://api-staging.successfactors.com/odata'),
            'description' => 'SAP SuccessFactors API base URL for staging'
        ),
        array(
            'environment' => 'staging',
            'config_key' => 'rate_limit.requests_per_minute',
            'config_value' => json_encode(45),
            'description' => 'Rate limit: requests per minute (staging)'
        )
    );
    
    foreach ($defaultConfigs as $config) {
        $sql = "INSERT IGNORE INTO sap_config (environment, config_key, config_value, description) 
                VALUES ('{$config['environment']}', '{$config['config_key']}', '{$config['config_value']}', '{$config['description']}')";
        
        if ($db->query($sql)) {
            echo "✓ Inserted config: {$config['environment']}.{$config['config_key']}\n";
        }
    }
}

/**
 * Create database indexes for performance
 */
function createPerformanceIndexes() {
    global $db;
    
    echo "Creating performance indexes...\n";
    
    $indexes = array(
        "CREATE INDEX idx_sap_sync_log_composite ON sap_sync_log (entity_type, entity_id, status, created_at)",
        "CREATE INDEX idx_sap_webhook_log_composite ON sap_webhook_log (event_type, status, created_at)",
        "CREATE INDEX idx_sap_employee_mapping_sync ON sap_employee_mapping (mapping_status, last_sync_at)",
        "CREATE INDEX idx_sap_document_mapping_sync ON sap_document_mapping (upload_status, last_sync_at)"
    );
    
    foreach ($indexes as $sql) {
        if ($db->query($sql)) {
            echo "✓ Created performance index\n";
        }
    }
}

/**
 * Main migration function
 */
function runMigration() {
    try {
        echo "Starting SAP SuccessFactors database migration...\n\n";
        
        // Create tables
        createSAPTables();
        
        echo "\n";
        
        // Insert default configuration
        insertDefaultConfiguration();
        
        echo "\n";
        
        // Create performance indexes
        createPerformanceIndexes();
        
        echo "\n";
        echo "SAP SuccessFactors database migration completed successfully!\n";
        echo "\nNext steps:\n";
        echo "1. Configure OAuth credentials in sap_config table or environment variables\n";
        echo "2. Test the connection using sap_examples.php\n";
        echo "3. Review the configuration in README_SAP.md\n";
        
    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run migration if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    runMigration();
}