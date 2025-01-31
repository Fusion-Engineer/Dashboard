<?php
header('Content-Type: application/json');

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'dashboard'
];

try {
    // Test database connection
    $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Test if we can query the table
    $result = $conn->query("SELECT 1 FROM airqualitydata LIMIT 1");
    if ($result === false) {
        throw new Exception("Unable to query the airqualitydata table");
    }

    // Close connection
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Connection successful! Database and table are accessible.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
