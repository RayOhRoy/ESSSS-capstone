<?php
include '../server/server.php';
header('Content-Type: application/json');

// Set timezone and current timestamp
date_default_timezone_set("Asia/Manila");
$timeNow = date('Y-m-d H:i:s');

session_start();
$userID = $_SESSION['employeeid'] ?? null; // get employee ID from session

$data = json_decode(file_get_contents('php://input'), true);
$scannedQR = $data['scannedQR'] ?? '';
$projectIdBase = $data['projectIdBase'] ?? '';
$action = strtolower($data['action'] ?? '');

if (!$scannedQR || !$projectIdBase || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

// Normalize for comparison
$scannedQR = strtolower(trim($scannedQR));

// Find matching project (case-insensitive)
$query = "
    SELECT projectid, projectQR
    FROM project
    WHERE LOWER(projectQR) LIKE CONCAT(?, '%')
    LIMIT 1
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $scannedQR);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Incorrect Project QR.']);
    exit;
}

$projectId = $row['projectid'];

// Determine statuses
$projectStatus = ($action === 'retrieve') ? 'Retrieve' : 'Stored';
$documentStatus = ($action === 'retrieve') ? 'Release' : 'Stored';
$activityStatus = ($action === 'retrieve') ? 'RETRIEVED' : 'STORED';

// Begin transaction
$conn->begin_transaction();

try {
    // --- Update project table ---
    $updateProject = $conn->prepare("UPDATE project SET storageStatus = ? WHERE projectid = ?");
    $updateProject->bind_param("ss", $projectStatus, $projectId);
    $updateProject->execute();

    // --- Update document table ---
    $updateDocs = $conn->prepare("UPDATE document SET documentStatus = ? WHERE projectid = ?");
    $updateDocs->bind_param("ss", $documentStatus, $projectId);
    $updateDocs->execute();

    // --- Generate new activity ID ---
    $getLastId = $conn->query("SELECT activitylogid FROM activity_log ORDER BY activitylogid DESC LIMIT 1");
    $lastRow = $getLastId->fetch_assoc();
    $nextNum = $lastRow ? (intval(substr($lastRow['activitylogid'], 4)) + 1) : 1;
    $newActivityId = "ACT-" . str_pad($nextNum, 5, "0", STR_PAD_LEFT); // ✅ 5 digits

    // --- Insert project-level activity (documentid = '') ---
    $emptyDocId = '';
    $insertActivity = $conn->prepare("
    INSERT INTO activity_log (activitylogid, projectid, documentid, status, employeeid, time)
    VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insertActivity->bind_param("ssssss", $newActivityId, $projectId, $emptyDocId, $activityStatus, $userID, $timeNow);
    $insertActivity->execute();

    // --- Insert one activity per document under same project ---
    $docQuery = $conn->prepare("SELECT documentid FROM document WHERE projectid = ?");
    $docQuery->bind_param("s", $projectId);
    $docQuery->execute();
    $docResult = $docQuery->get_result();

    while ($doc = $docResult->fetch_assoc()) {
        $nextNum++; // increment for next activity
        $docActivityId = "ACT-" . str_pad($nextNum, 5, "0", STR_PAD_LEFT); // ✅ 5 digits

        $insertDocActivity = $conn->prepare("
        INSERT INTO activity_log (activitylogid, projectid, documentid, status, employeeid, time)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
        $insertDocActivity->bind_param("ssssss", $docActivityId, $projectId, $doc['documentid'], $activityStatus, $userID, $timeNow);
        $insertDocActivity->execute();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'newStatus' => $projectStatus,
        'docStatus' => $documentStatus
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
