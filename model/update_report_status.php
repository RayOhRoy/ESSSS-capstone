<?php
ob_start();
session_start();
include '../server/server.php';
header('Content-Type: application/json');
date_default_timezone_set("Asia/Manila");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get raw JSON input
$input = json_decode(file_get_contents('php://input'), true);
$reportID = trim($input['reportID'] ?? '');
$status   = trim($input['status'] ?? '');

if (!$reportID || !$status) {
    echo json_encode(['status' => 'error', 'message' => 'Missing report ID or status']);
    exit;
}

// Optional: Validate session / employee permissions
$employeeID = $_SESSION['employeeid'] ?? null;
if (!$employeeID) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Update report status
$stmt = $conn->prepare("UPDATE report SET ReportStatus = ? WHERE ReportID = ?");
$stmt->bind_param("ss", $status, $reportID);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => "Report $reportID updated to $status successfully"
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
ob_end_flush();
?>
