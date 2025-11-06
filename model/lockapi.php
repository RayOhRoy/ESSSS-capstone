<?php
// model/lockapi.php
header('Content-Type: application/json');

// Allow your frontend to access it
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Replace with your ESP’s *local* or *public* IP if it has one
$espIP = "http://192.168.34.189"; // LAN IP

// Security token (optional but recommended)
$API_KEY = "mysecretkey";

// Verify API key
if (!isset($_GET['key']) || $_GET['key'] !== $API_KEY) {
    echo json_encode(["success" => false, "message" => "Invalid API key"]);
    exit;
}

// Validate params
$lock = $_GET['lock'] ?? null;
$action = $_GET['action'] ?? null;

if (!$lock || !$action) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

// Relay the command to ESP32
$url = "{$espIP}/relay?lock={$lock}&action={$action}";
$response = @file_get_contents($url);

if ($response === FALSE) {
    echo json_encode(["success" => false, "message" => "ESP unreachable"]);
} else {
    echo json_encode(["success" => true, "response" => $response]);
}
?>
