<?php

/**
 * SAP SuccessFactors Configuration Template
 * 
 * Copy this file to sap_config_local.php and customize for your environment.
 * This file contains sensitive configuration that should not be committed to version control.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

return array(
    
    // Production Environment Configuration
    'prod' => array(
        'oauth' => array(
            'client_id' => 'YOUR_PRODUCTION_CLIENT_ID',
            'client_secret' => 'YOUR_PRODUCTION_CLIENT_SECRET',
            'redirect_uri' => 'https://your-domain.com/sap-oauth-callback',
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
            'level' => 'warning',
            'file' => '/var/log/sap_connector.log',
            'max_file_size' => 10485760 // 10MB
        )
    ),
    
    // Staging Environment Configuration
    'staging' => array(
        'oauth' => array(
            'client_id' => 'YOUR_STAGING_CLIENT_ID',
            'client_secret' => 'YOUR_STAGING_CLIENT_SECRET',
            'redirect_uri' => 'https://staging.your-domain.com/sap-oauth-callback',
            'scope' => 'read write',
            'auth_url' => 'https://api.sap.com/oauth2/authorize',
            'token_url' => 'https://api.sap.com/oauth2/token'
        ),
        'api' => array(
            'base_url' => 'https://api-staging.successfactors.com/odata',
            'version' => 'v1'
        ),
        'connection' => array(
            'timeout' => 45,
            'ssl_verify' => true
        ),
        'rate_limit' => array(
            'requests_per_minute' => 45
        ),
        'logging' => array(
            'level' => 'info'
        )
    ),
    
    // Development Environment Configuration
    'dev' => array(
        'oauth' => array(
            'client_id' => 'YOUR_SANDBOX_CLIENT_ID',
            'client_secret' => 'YOUR_SANDBOX_CLIENT_SECRET',
            'redirect_uri' => 'https://dev.your-domain.com/sap-oauth-callback',
            'scope' => 'read write',
            'auth_url' => 'https://api.sap.com/oauth2/authorize',
            'token_url' => 'https://api.sap.com/oauth2/token'
        ),
        'api' => array(
            'base_url' => 'https://api-sandbox.successfactors.com/odata',
            'version' => 'v1'
        ),
        'connection' => array(
            'timeout' => 60,
            'ssl_verify' => false // Disabled for development
        ),
        'rate_limit' => array(
            'requests_per_minute' => 30
        ),
        'logging' => array(
            'level' => 'debug',
            'file' => '/tmp/sap_connector_dev.log'
        )
    ),
    
    // Shared Configuration (applies to all environments)
    'shared' => array(
        'storage' => array(
            'token_table' => 'sap_tokens',
            'config_table' => 'sap_config'
        ),
        'field_mappings' => array(
            // Employee field mappings
            'employee' => array(
                'backcheck_to_sap' => array(
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
                    'status' => 'status'
                ),
                'sap_to_backcheck' => array(
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
                    'status' => 'status'
                )
            ),
            
            // Background check field mappings
            'background_check' => array(
                'backcheck_to_sap' => array(
                    'check_id' => 'id',
                    'employee_id' => 'employeeId',
                    'check_type' => 'checkType',
                    'status' => 'status',
                    'result' => 'result',
                    'completed_date' => 'completedDate',
                    'vendor' => 'vendor',
                    'comments' => 'comments',
                    'document_ids' => 'documentIds'
                ),
                'sap_to_backcheck' => array(
                    'id' => 'check_id',
                    'employeeId' => 'employee_id',
                    'checkType' => 'check_type',
                    'status' => 'status',
                    'result' => 'result',
                    'completedDate' => 'completed_date',
                    'vendor' => 'vendor',
                    'comments' => 'comments',
                    'documentIds' => 'document_ids'
                )
            )
        ),
        
        // Status value mappings
        'status_mappings' => array(
            'background_check_status' => array(
                'backcheck_to_sap' => array(
                    'pending' => 'IN_PROGRESS',
                    'in_progress' => 'IN_PROGRESS',
                    'completed' => 'COMPLETED',
                    'cancelled' => 'CANCELLED',
                    'on_hold' => 'ON_HOLD',
                    'failed' => 'FAILED'
                ),
                'sap_to_backcheck' => array(
                    'IN_PROGRESS' => 'in_progress',
                    'COMPLETED' => 'completed',
                    'CANCELLED' => 'cancelled',
                    'ON_HOLD' => 'on_hold',
                    'FAILED' => 'failed'
                )
            ),
            'background_check_result' => array(
                'backcheck_to_sap' => array(
                    'clear' => 'CLEAR',
                    'consider' => 'CONSIDER',
                    'not_clear' => 'NOT_CLEAR',
                    'pending' => 'PENDING',
                    'dispute' => 'DISPUTE'
                ),
                'sap_to_backcheck' => array(
                    'CLEAR' => 'clear',
                    'CONSIDER' => 'consider',
                    'NOT_CLEAR' => 'not_clear',
                    'PENDING' => 'pending',
                    'DISPUTE' => 'dispute'
                )
            )
        ),
        
        // Document management settings
        'document_settings' => array(
            'allowed_file_types' => array(
                'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'csv', 'xlsx'
            ),
            'max_file_size' => 10485760, // 10MB
            'allowed_mime_types' => array(
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/gif',
                'text/plain',
                'text/csv',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ),
            'upload_path' => '/tmp/sap_uploads',
            'download_path' => '/tmp/sap_downloads'
        ),
        
        // Webhook configuration
        'webhook' => array(
            'enabled' => true,
            'secret_key' => 'YOUR_WEBHOOK_SECRET_KEY',
            'supported_events' => array(
                'employee_created',
                'employee_updated',
                'employee_deleted',
                'background_check_completed',
                'document_uploaded'
            ),
            'retry_attempts' => 3,
            'retry_delay' => 5
        ),
        
        // Batch operation settings
        'batch' => array(
            'max_operations' => 100,
            'timeout_per_operation' => 30,
            'parallel_processing' => false
        ),
        
        // Data validation rules
        'validation' => array(
            'employee' => array(
                'required_fields' => array('userId', 'firstName', 'lastName'),
                'email_validation' => true,
                'phone_validation' => true,
                'date_format' => 'Y-m-d'
            ),
            'background_check' => array(
                'required_fields' => array('employeeId', 'checkType'),
                'valid_check_types' => array(
                    'criminal', 'employment', 'education', 'reference',
                    'credit', 'driving', 'professional_license', 'identity',
                    'drug_screen', 'medical', 'social_media'
                ),
                'valid_statuses' => array(
                    'pending', 'in_progress', 'completed', 'cancelled', 'on_hold', 'failed'
                ),
                'valid_results' => array(
                    'clear', 'consider', 'not_clear', 'pending', 'dispute'
                )
            )
        )
    )
);

/*
 * Environment Variable Mappings
 * 
 * The following environment variables can override configuration values:
 * 
 * SAP_CLIENT_ID - OAuth client ID
 * SAP_CLIENT_SECRET - OAuth client secret  
 * SAP_REDIRECT_URI - OAuth redirect URI
 * SAP_API_BASE_URL - API base URL
 * SAP_TIMEOUT - Connection timeout in seconds
 * SAP_RATE_LIMIT - Rate limit requests per minute
 * SAP_LOG_LEVEL - Logging level (debug, info, warning, error)
 * SAP_LOG_FILE - Log file path
 * SAP_SSL_VERIFY - SSL verification (true/false)
 * 
 * Example usage:
 * export SAP_CLIENT_ID="your_client_id"
 * export SAP_CLIENT_SECRET="your_client_secret"
 */

/*
 * Database Configuration
 * 
 * The following database tables will be created automatically:
 * 
 * - sap_tokens: OAuth token storage
 * - sap_config: Configuration storage
 * - sap_sync_log: Synchronization logging
 * - sap_webhook_log: Webhook logging
 * - sap_employee_mapping: Employee ID mapping
 * - sap_document_mapping: Document ID mapping
 * 
 * Run sap_migration.php to create these tables.
 */

/*
 * Security Considerations
 * 
 * 1. Keep OAuth credentials secure and never commit them to version control
 * 2. Use environment variables for sensitive configuration in production
 * 3. Enable SSL verification in production environments
 * 4. Regularly rotate OAuth credentials
 * 5. Monitor webhook endpoints for suspicious activity
 * 6. Use strong webhook secret keys
 * 7. Implement proper access controls for API endpoints
 */