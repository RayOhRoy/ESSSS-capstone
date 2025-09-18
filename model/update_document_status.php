<?php
session_start();
include '../server/server.php'; // Your DB connection
header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'POST request required']));
    $conn->close();
}

// Check required POST fields
if (!isset($_POST['projectId'], $_POST['documentName'], $_POST['newStatus'], $_POST['scannedQR'])) {
    die(json_encode(['status' => 'error', 'message' => 'Missing required input.']));
    $conn->close();
}

$projectId     = $_POST['projectId'];
$documentName  = $_POST['documentName'];
$newStatus     = $_POST['newStatus'];
$scannedQR     = trim($_POST['scannedQR']);

// Step 1: Fetch stored DocumentQR from DB
$query = "SELECT DocumentQR FROM document WHERE ProjectID = ? AND DocumentName = ?";
$stmt  = $conn->prepare($query);
$stmt->bind_param("ss", $projectId, $documentName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(['status' => 'error', 'message' => 'Document not found.']));
    $conn->close();
}

$row = $result->fetch_assoc();
$expectedQR = trim($row['DocumentQR']);

// Step 2: Normalize paths for comparison
$normalizedScannedQR    = rtrim(strtolower($scannedQR), '/');
$expectedQRPath         = strtolower(pathinfo($expectedQR, PATHINFO_DIRNAME));
$normalizedExpectedQR   = rtrim($expectedQRPath, '/');

if ($normalizedScannedQR !== $normalizedExpectedQR) {
    $conn->close();
    die(json_encode([
        'status'  => 'error',
        'message' => 'Incorrect QR Code.',
        'details' => [
            'scanned'  => $normalizedScannedQR,
            'expected' => $normalizedExpectedQR
        ]
    ]));
}

// Step 3: Update the document status
$updateQuery = "UPDATE document SET DocumentStatus = ? WHERE ProjectID = ? AND DocumentName = ?";
$updateStmt  = $conn->prepare($updateQuery);
$updateStmt->bind_param("sss", $newStatus, $projectId, $documentName);

if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Document status updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update document status.']);
    $conn->close();
}
?>
