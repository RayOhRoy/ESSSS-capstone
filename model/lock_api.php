<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");   
// File-based storage (or use DB)
$statusFile = 'lock_status.json';

// Read POST data
$input = json_decode(file_get_contents('php://input'), true);

// Initialize file if not exists
if (!file_exists($statusFile)) {
    file_put_contents($statusFile, json_encode([
        'lock1' => 1, // HIGH = locked
        'lock2' => 1,
        'cmd1' => '',
        'cmd2' => ''
    ]));
}

$status = json_decode(file_get_contents($statusFile), true);

// ESP32 polling for commands
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    echo json_encode([
        'lock1' => $status['lock1'],
        'lock2' => $status['lock2'],
        'cmd1' => $status['cmd1'],
        'cmd2' => $status['cmd2']
    ]);
    // Reset commands after sending
    $status['cmd1'] = '';
    $status['cmd2'] = '';
    file_put_contents($statusFile, json_encode($status));
    exit;
}

// Web app sends a command
if (isset($input['lock']) && isset($input['action'])) {
    $lock = $input['lock'];
    $action = $input['action']; // 'unlock' or 'lock'
    $status["cmd$lock"] = $action;
    file_put_contents($statusFile, json_encode($status));
    echo json_encode(['success' => true]);
    exit;
}

// ESP32 updates current state
if (isset($input['update']) && $input['update'] === 'status') {
    $status['lock1'] = $input['lock1'];
    $status['lock2'] = $input['lock2'];
    file_put_contents($statusFile, json_encode($status));
    echo json_encode(['success' => true]);
    exit;
}

?>
