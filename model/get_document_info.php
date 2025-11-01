<?php
session_start();
include '../server/server.php';

if (!isset($_SESSION['employeeid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$projectId = strtolower(trim($input['projectId'] ?? ''));
$documentPath = strtolower(trim($input['documentPath'] ?? ''));

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'Project ID is required']);
    exit;
}

$projectId = $conn->real_escape_string($projectId);
$documentPath = strtolower($conn->real_escape_string($documentPath));

// 🧹 Remove "-ABC" or any trailing 3-letter suffix (case-insensitive)
$projectId = preg_replace('/-[a-z]{3}$/i', '', $projectId);

// 🧹 Remove "uploads/" prefix if present
if (strpos($documentPath, 'uploads/') === 0) {
    $documentPath = substr($documentPath, strlen('uploads/'));
}

// 🧩 Use only folder portion for flexible matching
$documentPath = trim($documentPath, '/');

// ✅ Query using DocumentQR (case-insensitive, partial folder match)
$sql = "SELECT 
            d.DocumentName,
            d.DocumentType,
            d.DocumentStatus,
            d.DigitalLocation,
            d.DocumentQR,
            p.ProjectID,
            p.LotNo,
            p.ClientLName,
            p.ClientFName,
            p.SurveyType,
            p.SurveyStartDate,
            p.SurveyEndDate,
            p.Agent,
            p.ProjectQR,
            a.Address,
            a.Barangay,
            a.Municipality,
            a.Province
        FROM document d
        INNER JOIN project p ON d.ProjectID = p.ProjectID
        LEFT JOIN address a ON p.AddressID = a.AddressID
        WHERE p.ProjectID LIKE CONCAT(?, '%')
          AND LOWER(REPLACE(d.DocumentQR, 'uploads/', '')) LIKE CONCAT('%', ?, '%')
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $projectId, $documentPath);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Document not found']);
    exit;
}

$doc = $result->fetch_assoc();

// Format client full name
$doc['ClientName'] = trim(($doc['ClientFName'] ?? '') . ' ' . ($doc['ClientLName'] ?? ''));

// Format survey dates
$doc['SurveyStartDate'] = ($doc['SurveyStartDate'] && $doc['SurveyStartDate'] !== '0000-00-00')
    ? date('F j, Y', strtotime($doc['SurveyStartDate']))
    : '';

$doc['SurveyEndDate'] = ($doc['SurveyEndDate'] && $doc['SurveyEndDate'] !== '0000-00-00')
    ? date('F j, Y', strtotime($doc['SurveyEndDate']))
    : 'Ongoing';

// Build full address
$addressParts = array_filter([
    $doc['Address'] ?? '',
    $doc['Barangay'] ?? '',
    $doc['Municipality'] ?? '',
    $doc['Province'] ?? ''
]);
$doc['FullAddress'] = implode(', ', $addressParts);

echo json_encode(['success' => true, 'document' => $doc]);
