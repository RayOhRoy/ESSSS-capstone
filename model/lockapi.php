<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ===== CONFIGURE YOUR ESP32 PUBLIC URL OR NGROK =====
$espIP = "https://unostentatious-unconfected-marya.ngrok-free.dev"; 
// or "http://yourpublicip:8080" if using port forwarding

$endpoint = $_GET['endpoint'] ?? '';
$lock = $_GET['lock'] ?? '';
$action = $_GET['action'] ?? '';

if (!$endpoint) {
    echo json_encode(["error" => "Missing endpoint"]);
    exit;
}

$url = rtrim($espIP, "/") . $endpoint;
if ($lock && $action) {
    $url .= "?lock=$lock&action=$action";
}

$response = @file_get_contents($url);
if ($response === FALSE) {
    echo json_encode(["error" => "ESP not reachable"]);
} else {
    echo $response;
}
?>
