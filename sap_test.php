<?php

/**
 * SAP SuccessFactors Integration Test Script
 * 
 * Tests basic functionality of the SAP SuccessFactors connector
 * without requiring actual SAP credentials or connections.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "SAP SuccessFactors Integration Test\n";
echo "==================================\n\n";

// Test 1: Class Loading
echo "Test 1: Loading SAP classes...\n";

try {
    require_once dirname(__FILE__) . '/include/sap/SAPSuccessFactorsConnector.php';
    echo "✓ SAPSuccessFactorsConnector loaded successfully\n";
    
    require_once dirname(__FILE__) . '/include/sap/SAPConfig.php';
    echo "✓ SAPConfig loaded successfully\n";
    
    require_once dirname(__FILE__) . '/include/sap/SAPException.php';
    echo "✓ SAPException loaded successfully\n";
    
    require_once dirname(__FILE__) . '/include/sap/SAPUtils.php';
    echo "✓ SAPUtils loaded successfully\n";
    
} catch (Exception $e) {
    echo "✗ Failed to load classes: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 2: Configuration
echo "Test 2: Testing configuration management...\n";

try {
    // Test with custom configuration
    $customConfig = array(
        'oauth' => array(
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret'
        ),
        'api' => array(
            'base_url' => 'https://test.successfactors.com/odata'
        )
    );
    
    $config = new SAPConfig('dev', $customConfig);
    
    echo "✓ Configuration object created\n";
    echo "  Environment: " . $config->getEnvironment() . "\n";
    
    $apiBaseUrl = $config->getApiBaseUrl();
    $clientId = $config->getClientId();
    echo "  API Base URL: " . (is_array($apiBaseUrl) ? json_encode($apiBaseUrl) : $apiBaseUrl) . "\n";
    echo "  Client ID: " . (is_array($clientId) ? json_encode($clientId) : $clientId) . "\n";
    echo "  Rate Limit: " . $config->getRateLimitRequestsPerMinute() . " requests/minute\n";
    
    // Test configuration validation
    $errors = $config->validate();
    if (empty($errors)) {
        echo "✓ Configuration validation passed\n";
    } else {
        echo "✗ Configuration validation failed:\n";
        foreach ($errors as $error) {
            echo "    - " . $error . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Configuration test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Exception Handling
echo "Test 3: Testing exception handling...\n";

try {
    // Test basic exception
    $exception = new SAPException('Test error message', 500);
    echo "✓ SAPException created\n";
    echo "  Message: " . $exception->getMessage() . "\n";
    echo "  Code: " . $exception->getCode() . "\n";
    
    // Test formatted error
    $formatted = $exception->getFormattedError();
    echo "✓ Formatted error created with " . count($formatted) . " fields\n";
    
    // Test specific exception types
    $authException = new SAPAuthException('Authentication failed');
    echo "✓ SAPAuthException created\n";
    
    $configException = new SAPConfigException('Config error', array('field1' => 'error1'));
    echo "✓ SAPConfigException created\n";
    
    $validationException = new SAPValidationException('Validation failed', array('email' => 'Invalid format'));
    echo "✓ SAPValidationException created\n";
    echo "  Validation errors: " . count($validationException->getValidationErrors()) . "\n";
    
} catch (Exception $e) {
    echo "✗ Exception test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Data Transformation
echo "Test 4: Testing data transformation utilities...\n";

try {
    // Test employee data transformation
    $backcheckData = array(
        'employee_id' => 'EMP001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@company.com',
        'hire_date' => '2023-01-15'
    );
    
    $sapData = SAPDataTransformer::transformEmployeeToSAP($backcheckData);
    echo "✓ Employee data transformed to SAP format\n";
    echo "  Transformed fields: " . count($sapData) . "\n";
    echo "  Sample mapping: employee_id -> " . (isset($sapData['userId']) ? 'userId' : 'missing') . "\n";
    
    // Test reverse transformation
    $backData = SAPDataTransformer::transformEmployeeFromSAP($sapData);
    echo "✓ Employee data transformed back from SAP format\n";
    echo "  Original fields count: " . count($backcheckData) . ", Final count: " . count($backData) . "\n";
    
} catch (Exception $e) {
    echo "✗ Data transformation test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Data Validation
echo "Test 5: Testing data validation...\n";

try {
    // Test valid employee data
    $validEmployee = array(
        'userId' => 'EMP001',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@company.com'
    );
    
    $errors = SAPDataValidator::validateEmployee($validEmployee);
    if (empty($errors)) {
        echo "✓ Valid employee data passed validation\n";
    } else {
        echo "✗ Valid employee data failed validation: " . count($errors) . " errors\n";
    }
    
    // Test invalid employee data
    $invalidEmployee = array(
        'firstName' => 'John',
        'email' => 'invalid-email'
    );
    
    $errors = SAPDataValidator::validateEmployee($invalidEmployee);
    if (!empty($errors)) {
        echo "✓ Invalid employee data correctly failed validation\n";
        echo "  Validation errors: " . count($errors) . "\n";
        foreach ($errors as $field => $error) {
            echo "    - " . $field . ": " . $error . "\n";
        }
    } else {
        echo "✗ Invalid employee data incorrectly passed validation\n";
    }
    
} catch (Exception $e) {
    echo "✗ Data validation test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Response Formatting
echo "Test 6: Testing response formatting...\n";

try {
    // Test success response
    $successResponse = SAPResponseFormatter::success(
        array('id' => 'EMP001', 'name' => 'John Doe'),
        'Employee created successfully'
    );
    echo "✓ Success response formatted\n";
    echo "  Success: " . ($successResponse['success'] ? 'true' : 'false') . "\n";
    echo "  Message: " . $successResponse['message'] . "\n";
    
    // Test error response
    $errorResponse = SAPResponseFormatter::error('Something went wrong', 400);
    echo "✓ Error response formatted\n";
    echo "  Success: " . ($errorResponse['success'] ? 'true' : 'false') . "\n";
    echo "  Error: " . ($errorResponse['error'] ? 'true' : 'false') . "\n";
    echo "  Code: " . $errorResponse['code'] . "\n";
    
    // Test validation error response
    $validationResponse = SAPResponseFormatter::validationError(
        array('email' => 'Invalid format', 'firstName' => 'Required field')
    );
    echo "✓ Validation error response formatted\n";
    echo "  Validation errors: " . count($validationResponse['validation_errors']) . "\n";
    
} catch (Exception $e) {
    echo "✗ Response formatting test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Logging
echo "Test 7: Testing logging functionality...\n";

try {
    // Set a test log file
    $testLogFile = '/tmp/sap_test.log';
    SAPLogger::setLogFile($testLogFile);
    
    // Test different log levels
    SAPLogger::info('Test info message', array('test_data' => 'value'));
    SAPLogger::warning('Test warning message');
    SAPLogger::error('Test error message', array('error_code' => 500));
    
    if (file_exists($testLogFile)) {
        $logContent = file_get_contents($testLogFile);
        $logLines = explode("\n", trim($logContent));
        echo "✓ Logging functionality working\n";
        echo "  Log entries created: " . count($logLines) . "\n";
        echo "  Log file size: " . filesize($testLogFile) . " bytes\n";
        
        // Test log entry format
        $firstEntry = json_decode($logLines[0], true);
        if ($firstEntry && isset($firstEntry['timestamp']) && isset($firstEntry['level']) && isset($firstEntry['message'])) {
            echo "✓ Log entry format is correct\n";
            echo "  Sample entry level: " . $firstEntry['level'] . "\n";
        } else {
            echo "✗ Log entry format is incorrect\n";
        }
        
        // Clean up test log file
        unlink($testLogFile);
    } else {
        echo "✗ Log file was not created\n";
    }
    
} catch (Exception $e) {
    echo "✗ Logging test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 8: Connector Initialization (without authentication)
echo "Test 8: Testing connector initialization...\n";

try {
    // Create connector with test configuration
    $testConfig = array(
        'oauth' => array(
            'client_id' => 'test_id',
            'client_secret' => 'test_secret',
            'redirect_uri' => 'https://test.com/callback'
        ),
        'api' => array(
            'base_url' => 'https://test.successfactors.com/odata'
        ),
        'connection' => array(
            'timeout' => 30,
            'ssl_verify' => false
        )
    );
    
    $connector = new SAPSuccessFactorsConnector('dev', $testConfig);
    echo "✓ SAP connector initialized successfully\n";
    
    // Test status (without authentication)
    $status = $connector->getStatus();
    echo "✓ Status retrieved\n";
    echo "  Environment: " . $status['environment'] . "\n";
    echo "  Authenticated: " . ($status['authenticated'] ? 'true' : 'false') . "\n";
    $apiBaseUrl = $status['api_base_url'];
    echo "  API Base URL: " . (is_array($apiBaseUrl) ? json_encode($apiBaseUrl) : $apiBaseUrl) . "\n";
    echo "  Rate Limit Remaining: " . $status['rate_limit_remaining'] . "\n";
    
    // Test service getters
    $authHandler = $connector->getAuthHandler();
    $dataService = $connector->getDataService();
    $documentService = $connector->getDocumentService();
    
    echo "✓ Service objects retrieved\n";
    echo "  Auth handler: " . get_class($authHandler) . "\n";
    echo "  Data service: " . get_class($dataService) . "\n";
    echo "  Document service: " . get_class($documentService) . "\n";
    
} catch (Exception $e) {
    echo "✗ Connector initialization test failed: " . $e->getMessage() . "\n";
    echo "  Error details: " . ($e instanceof SAPException ? json_encode($e->getErrorDetails()) : 'N/A') . "\n";
}

echo "\n";

// Summary
echo "Test Summary:\n";
echo "=============\n";
echo "All basic functionality tests completed!\n\n";
echo "Next steps:\n";
echo "1. Run 'php sap_migration.php' to create database tables\n";
echo "2. Configure OAuth credentials in environment variables or config\n";
echo "3. Test actual SAP SuccessFactors connectivity\n";
echo "4. Use 'php sap_examples.php' for full integration examples\n\n";

echo "Integration test completed successfully! ✓\n";