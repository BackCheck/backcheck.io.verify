<?php

/**
 * SAP SuccessFactors Integration Examples
 * 
 * This file contains practical examples of how to use the SAP SuccessFactors
 * API connector in the BackCheck application.
 * 
 * @package BackCheck
 * @subpackage SAP
 * @version 1.0.0
 */

// Include the connector
require_once dirname(__FILE__) . '/include/sap/SAPSuccessFactorsConnector.php';

/**
 * Example 1: Basic Authentication and Setup
 */
function exampleBasicSetup() {
    try {
        // Initialize connector for production environment
        $connector = new SAPSuccessFactorsConnector('prod');
        
        // Check current status
        $status = $connector->getStatus();
        echo "Connector Status:\n";
        print_r($status);
        
        // Authenticate if not already authenticated
        if (!$connector->isAuthenticated()) {
            echo "Authenticating with SAP SuccessFactors...\n";
            $success = $connector->authenticate();
            
            if ($success) {
                echo "Authentication successful!\n";
            } else {
                echo "Authentication failed!\n";
                return false;
            }
        } else {
            echo "Already authenticated.\n";
        }
        
        return $connector;
        
    } catch (SAPException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Example 2: Employee Data Operations
 */
function exampleEmployeeOperations($connector) {
    try {
        $dataService = $connector->getDataService();
        
        // Create new employee
        $employeeData = array(
            'employeeId' => 'EMP001',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'phoneNumber' => '+1-555-123-4567',
            'dateOfBirth' => '1985-06-15',
            'hireDate' => '2023-01-15',
            'jobTitle' => 'Software Engineer',
            'department' => 'Engineering',
            'location' => 'New York'
        );
        
        echo "Creating employee...\n";
        $createResult = $connector->sendEmployeeData($employeeData);
        echo "Employee created: " . json_encode($createResult, JSON_PRETTY_PRINT) . "\n";
        
        // Retrieve employee data
        echo "Retrieving employee data...\n";
        $employee = $connector->getEmployeeData('EMP001');
        echo "Employee data: " . json_encode($employee, JSON_PRETTY_PRINT) . "\n";
        
        // Update employee data
        $updateData = array(
            'jobTitle' => 'Senior Software Engineer',
            'department' => 'Engineering - Backend'
        );
        
        echo "Updating employee data...\n";
        $updateResult = $connector->updateEmployeeData('EMP001', $updateData);
        echo "Employee updated: " . json_encode($updateResult, JSON_PRETTY_PRINT) . "\n";
        
        // Search employees
        echo "Searching employees in Engineering department...\n";
        $searchResults = $dataService->searchEmployees(
            array('department' => 'Engineering'), 
            array('select' => 'employeeId,firstName,lastName,jobTitle', 'top' => 10)
        );
        echo "Search results: " . json_encode($searchResults, JSON_PRETTY_PRINT) . "\n";
        
    } catch (SAPException $e) {
        echo "Employee operations error: " . $e->getMessage() . "\n";
    }
}

/**
 * Example 3: Background Check Results
 */
function exampleBackgroundCheckOperations($connector) {
    try {
        // Send background check results to SAP
        $checkResults = array(
            'checkType' => 'criminal',
            'status' => 'completed',
            'result' => 'clear',
            'completedDate' => date('Y-m-d H:i:s'),
            'vendor' => 'BackCheck',
            'comments' => 'No criminal records found'
        );
        
        echo "Sending background check results...\n";
        $result = $connector->sendBackgroundCheckResults('EMP001', $checkResults);
        echo "Background check result sent: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
        
        // Get background check results for employee
        $dataService = $connector->getDataService();
        $allChecks = $dataService->getBackgroundCheckResults('EMP001');
        echo "All background checks for employee: " . json_encode($allChecks, JSON_PRETTY_PRINT) . "\n";
        
    } catch (SAPException $e) {
        echo "Background check operations error: " . $e->getMessage() . "\n";
    }
}

/**
 * Example 4: Document Management
 */
function exampleDocumentOperations($connector) {
    try {
        $documentService = $connector->getDocumentService();
        
        // Create a sample document for demonstration
        $sampleFile = '/tmp/sample_report.pdf';
        $sampleContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n>>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000074 00000 n \n0000000120 00000 n \ntrailer\n<<\n/Size 4\n/Root 1 0 R\n>>\nstartxref\n181\n%%EOF";
        file_put_contents($sampleFile, $sampleContent);
        
        // Upload document
        $metadata = array(
            'employeeId' => 'EMP001',
            'documentType' => 'background_check_report',
            'description' => 'Criminal background check report',
            'category' => 'Background Check'
        );
        
        echo "Uploading document...\n";
        $uploadResult = $connector->uploadDocument($sampleFile, $metadata);
        echo "Document uploaded: " . json_encode($uploadResult, JSON_PRETTY_PRINT) . "\n";
        
        $documentId = $uploadResult['documentId'];
        
        // Get document information
        echo "Retrieving document information...\n";
        $docInfo = $documentService->getDocumentInfo($documentId);
        echo "Document info: " . json_encode($docInfo, JSON_PRETTY_PRINT) . "\n";
        
        // Download document
        $downloadPath = '/tmp/downloaded_report.pdf';
        echo "Downloading document...\n";
        $downloadSuccess = $connector->downloadDocument($documentId, $downloadPath);
        echo "Document downloaded: " . ($downloadSuccess ? 'Success' : 'Failed') . "\n";
        
        // List documents
        echo "Listing documents...\n";
        $documents = $documentService->listDocuments(
            array('category' => 'Background Check'),
            array('top' => 5, 'select' => 'documentId,fileName,category,createdAt')
        );
        echo "Documents list: " . json_encode($documents, JSON_PRETTY_PRINT) . "\n";
        
        // Clean up
        unlink($sampleFile);
        if (file_exists($downloadPath)) {
            unlink($downloadPath);
        }
        
    } catch (SAPException $e) {
        echo "Document operations error: " . $e->getMessage() . "\n";
    }
}

/**
 * Example 5: Batch Operations
 */
function exampleBatchOperations($connector) {
    try {
        // Prepare batch operations
        $operations = array(
            array(
                'type' => 'create_employee',
                'data' => array(
                    'employeeId' => 'EMP002',
                    'firstName' => 'Jane',
                    'lastName' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'jobTitle' => 'Data Analyst',
                    'department' => 'Analytics'
                )
            ),
            array(
                'type' => 'create_employee', 
                'data' => array(
                    'employeeId' => 'EMP003',
                    'firstName' => 'Bob',
                    'lastName' => 'Johnson',
                    'email' => 'bob.johnson@example.com',
                    'jobTitle' => 'Marketing Manager',
                    'department' => 'Marketing'
                )
            ),
            array(
                'type' => 'update_employee',
                'employee_id' => 'EMP001',
                'data' => array(
                    'location' => 'San Francisco'
                )
            )
        );
        
        echo "Executing batch operations...\n";
        $batchResult = $connector->batchOperation($operations);
        echo "Batch operations result: " . json_encode($batchResult, JSON_PRETTY_PRINT) . "\n";
        
    } catch (SAPException $e) {
        echo "Batch operations error: " . $e->getMessage() . "\n";
    }
}

/**
 * Example 6: Error Handling
 */
function exampleErrorHandling() {
    try {
        // Intentionally trigger an error with invalid configuration
        $connector = new SAPSuccessFactorsConnector('invalid_env');
        $connector->authenticate();
        
    } catch (SAPConfigException $e) {
        echo "Configuration Error:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Config Errors: " . json_encode($e->getConfigErrors(), JSON_PRETTY_PRINT) . "\n";
        
    } catch (SAPAuthException $e) {
        echo "Authentication Error:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "HTTP Code: " . $e->getHttpStatusCode() . "\n";
        
    } catch (SAPRateLimitException $e) {
        echo "Rate Limit Error:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Retry After: " . $e->getRetryAfter() . " seconds\n";
        echo "Limit Remaining: " . $e->getLimitRemaining() . "\n";
        
    } catch (SAPException $e) {
        echo "General SAP Error:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Code: " . $e->getCode() . "\n";
        echo "Details: " . json_encode($e->getErrorDetails(), JSON_PRETTY_PRINT) . "\n";
        
    } catch (Exception $e) {
        echo "Unexpected Error: " . $e->getMessage() . "\n";
    }
}

/**
 * Example 7: Custom Configuration
 */
function exampleCustomConfiguration() {
    try {
        // Custom configuration for sandbox environment
        $customConfig = array(
            'oauth' => array(
                'client_id' => 'your_sandbox_client_id',
                'client_secret' => 'your_sandbox_client_secret'
            ),
            'api' => array(
                'base_url' => 'https://sandbox.successfactors.com/odata'
            ),
            'connection' => array(
                'timeout' => 60,
                'ssl_verify' => false
            ),
            'rate_limit' => array(
                'requests_per_minute' => 30
            )
        );
        
        $connector = new SAPSuccessFactorsConnector('dev', $customConfig);
        
        echo "Custom connector initialized for development environment\n";
        $status = $connector->getStatus();
        echo "Status: " . json_encode($status, JSON_PRETTY_PRINT) . "\n";
        
        return $connector;
        
    } catch (SAPException $e) {
        echo "Custom configuration error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Example 8: Integration with BackCheck Workflow
 */
function exampleBackCheckIntegration() {
    try {
        $connector = new SAPSuccessFactorsConnector('prod');
        
        // Simulate BackCheck workflow
        echo "=== BackCheck SAP Integration Workflow ===\n";
        
        // Step 1: Receive new hire request from SAP
        echo "1. Processing new hire from SAP SuccessFactors...\n";
        $newHire = array(
            'employeeId' => 'NH001',
            'firstName' => 'Alice',
            'lastName' => 'Williams',
            'email' => 'alice.williams@company.com',
            'hireDate' => date('Y-m-d'),
            'jobTitle' => 'Software Developer',
            'department' => 'Engineering'
        );
        
        // Step 2: Create background check case in BackCheck
        echo "2. Creating background check case...\n";
        // This would integrate with existing BackCheck case creation logic
        $caseId = 'BC' . date('YmdHis');
        echo "Background check case created: {$caseId}\n";
        
        // Step 3: Process background checks
        echo "3. Processing background checks...\n";
        $checkTypes = array('criminal', 'employment', 'education');
        $checkResults = array();
        
        foreach ($checkTypes as $checkType) {
            // Simulate check processing
            sleep(1); // Simulate processing time
            $result = array(
                'checkType' => $checkType,
                'status' => 'completed',
                'result' => 'clear',
                'completedDate' => date('Y-m-d H:i:s'),
                'vendor' => 'BackCheck',
                'caseId' => $caseId
            );
            $checkResults[] = $result;
            echo "  - {$checkType} check: CLEAR\n";
        }
        
        // Step 4: Send results back to SAP
        echo "4. Sending results to SAP SuccessFactors...\n";
        foreach ($checkResults as $result) {
            $connector->sendBackgroundCheckResults($newHire['employeeId'], $result);
        }
        echo "All results sent to SAP SuccessFactors\n";
        
        // Step 5: Generate and upload final report
        echo "5. Generating final report...\n";
        $reportContent = generateSampleReport($newHire, $checkResults);
        $reportFile = '/tmp/final_report_' . $caseId . '.pdf';
        file_put_contents($reportFile, $reportContent);
        
        $metadata = array(
            'employeeId' => $newHire['employeeId'],
            'documentType' => 'final_background_report',
            'description' => 'Final background check report',
            'caseId' => $caseId
        );
        
        $uploadResult = $connector->uploadDocument($reportFile, $metadata);
        echo "Final report uploaded: {$uploadResult['documentId']}\n";
        
        // Clean up
        unlink($reportFile);
        
        echo "=== Integration workflow completed successfully ===\n";
        
    } catch (SAPException $e) {
        echo "Integration workflow error: " . $e->getMessage() . "\n";
    }
}

/**
 * Generate sample report content
 */
function generateSampleReport($employee, $checkResults) {
    $content = "BACKGROUND CHECK REPORT\n";
    $content .= "========================\n\n";
    $content .= "Employee: {$employee['firstName']} {$employee['lastName']}\n";
    $content .= "Employee ID: {$employee['employeeId']}\n";
    $content .= "Position: {$employee['jobTitle']}\n";
    $content .= "Department: {$employee['department']}\n";
    $content .= "Report Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    $content .= "CHECK RESULTS:\n";
    $content .= "==============\n";
    foreach ($checkResults as $result) {
        $content .= "- " . ucfirst($result['checkType']) . " Check: " . strtoupper($result['result']) . "\n";
    }
    
    $content .= "\nOVERALL STATUS: CLEARED FOR EMPLOYMENT\n";
    
    return $content;
}

// Run examples if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    echo "SAP SuccessFactors Integration Examples\n";
    echo "=====================================\n\n";
    
    // Example 1: Basic Setup
    echo "--- Example 1: Basic Setup ---\n";
    $connector = exampleBasicSetup();
    
    if ($connector) {
        // Example 2: Employee Operations
        echo "\n--- Example 2: Employee Operations ---\n";
        exampleEmployeeOperations($connector);
        
        // Example 3: Background Check Operations
        echo "\n--- Example 3: Background Check Operations ---\n";
        exampleBackgroundCheckOperations($connector);
        
        // Example 4: Document Operations
        echo "\n--- Example 4: Document Operations ---\n";
        exampleDocumentOperations($connector);
        
        // Example 5: Batch Operations
        echo "\n--- Example 5: Batch Operations ---\n";
        exampleBatchOperations($connector);
    }
    
    // Example 6: Error Handling
    echo "\n--- Example 6: Error Handling ---\n";
    exampleErrorHandling();
    
    // Example 7: Custom Configuration
    echo "\n--- Example 7: Custom Configuration ---\n";
    exampleCustomConfiguration();
    
    // Example 8: BackCheck Integration Workflow
    echo "\n--- Example 8: BackCheck Integration Workflow ---\n";
    exampleBackCheckIntegration();
    
    echo "\nAll examples completed!\n";
}