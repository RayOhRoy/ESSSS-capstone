<?php
session_start();
include '../server/server.php'; // adjust path if needed

if (!isset($_SESSION['employeeid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$projectId = $input['projectId'] ?? '';
$documentPath = $input['documentPath'] ?? ''; // or documentId if you prefer

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'Project ID is required']);
    exit;
}

$projectId = $conn->real_escape_string($projectId);
$documentPath = $conn->real_escape_string($documentPath);

// Fetch document and project info using projectId and path (adjust if using DocumentID)
$sql = "SELECT 
            d.DocumentName,
            d.DocumentType,
            d.DocumentStatus,
            d.DigitalLocation,
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
        WHERE d.ProjectID = ?
          AND d.DigitalLocation LIKE CONCAT('%', ?, '%')
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
$doc['ClientName'] = trim($doc['ClientFName'] . ' ' . $doc['ClientLName']);

$doc['SurveyStartDate'] = $doc['SurveyStartDate'] && $doc['SurveyStartDate'] !== '0000-00-00' 
    ? date('F j, Y', strtotime($doc['SurveyStartDate'])) 
    : '';

if ($doc['SurveyEndDate'] && $doc['SurveyEndDate'] !== '0000-00-00') {
    $doc['SurveyEndDate'] = date('F j, Y', strtotime($doc['SurveyEndDate']));
} else {
    $doc['SurveyEndDate'] = 'Ongoing';
}

// Build full address
$addressParts = array_filter([
    $doc['Address'] ?? '',
    $doc['Barangay'] ?? '',
    $doc['Municipality'] ?? '',
    $doc['Province'] ?? ''
]);
$doc['FullAddress'] = implode(', ', $addressParts);

echo json_encode(['success' => true, 'document' => $doc]);
