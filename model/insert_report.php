<?php
ob_start();
session_start();
include '../server/server.php';
header('Content-Type: application/json');
date_default_timezone_set("Asia/Manila");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$employeeID = $_SESSION['employeeid'] ?? null;
if (!$employeeID) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// ✅ Match actual frontend POST keys
$reportType = trim($_POST['report_type'] ?? $_POST['reportType'] ?? '');
$projectID  = trim($_POST['project_id'] ?? $_POST['projectId'] ?? '');
$reportDesc = trim($_POST['report_description'] ?? $_POST['reportDesc'] ?? '');

if (empty($reportType) || empty($projectID) || empty($reportDesc)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// ✅ Generate Report ID
$res = $conn->query("SELECT ReportID FROM report ORDER BY ReportID DESC LIMIT 1");
if ($res && $res->num_rows > 0) {
    $last = $res->fetch_assoc();
    $lastNum = intval(substr($last['ReportID'], 4)) + 1;
} else {
    $lastNum = 1;
}
$reportID = "REP-" . str_pad($lastNum, 5, "0", STR_PAD_LEFT);

// 🕒 Current Manila Time
$reportTime = date("Y-m-d H:i:s");

// 🧩 Use report type as DocumentID directly
$documentID = $reportType;

// 🧾 Insert new report (default status = PENDING)
$stmt = $conn->prepare("INSERT INTO report 
    (ReportID, ProjectID, DocumentID, EmployeeID, ReportDesc, ReportStatus, Time)
    VALUES (?, ?, ?, ?, ?, 'PENDING', ?)"
);
$stmt->bind_param("ssssss", $reportID, $projectID, $documentID, $employeeID, $reportDesc, $reportTime);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Report inserted successfully',
        'reportID' => $reportID,
        'reportStatus' => 'PENDING',
        'documentID' => $documentID,
        'time' => $reportTime
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
