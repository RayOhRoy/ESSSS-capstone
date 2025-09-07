<?php 
$database	= 'u977775529_esdb';
$username	= 'u977775529_escdms';
$host		= 'localhost';
$password	= 'cT?8B7hL';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => '❌ Database connection failed: ' . $conn->connect_error
    ]));
}

if (php_sapi_name() !== 'cli' && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '✅ Successfully connected to the database'
    ]);
}
