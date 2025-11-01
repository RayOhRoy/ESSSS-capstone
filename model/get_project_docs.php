<?php
// get_project_docs.php
header('Content-Type: application/json');
include '../server/server.php'; // adjust path to your DB connection

// Get projectId from query string
$projectId = $_GET['projectId'] ?? '';

if (!$projectId) {
    echo json_encode([]);
    exit;
}

try {
    // 1️⃣ Fetch all document QRs for the project
    $stmt = $conn->prepare("SELECT DocumentType, DocumentQR FROM document WHERE ProjectID = ?");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    $documents = [];
    while ($row = $result->fetch_assoc()) {
        $documents[] = [
            'DocumentType' => $row['DocumentType'],
            'DocumentQR' => $row['DocumentQR']
        ];
    }

    // 2️⃣ Fetch the Project QR from the project table
    $stmt2 = $conn->prepare("SELECT ProjectQR FROM project WHERE ProjectID = ?");
    $stmt2->bind_param("s", $projectId);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $projectQRRow = $result2->fetch_assoc();

    if (!empty($projectQRRow['ProjectQR'])) {
        // Add Project QR as the first item with label = ProjectID
        array_unshift($documents, [
            'DocumentType' => 'Project QR',
            'DocumentQR' => $projectQRRow['ProjectQR']
        ]);
    }

    echo json_encode($documents);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
