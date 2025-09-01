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

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'Project ID is required']);
    exit;
}

$projectId = $conn->real_escape_string($projectId);

// Fetch project info with AddressID
$sql = "SELECT 
            p.ProjectID,
            p.LotNo,
            p.ClientLName,
            p.ClientFName,
            p.SurveyType,
            p.PhysicalLocation,
            p.DigitalLocation,
            p.SurveyStartDate,
            p.SurveyEndDate,
            p.Agent,
            p.ProjectQR,
            p.AddressID,
            a.Address,
            a.Barangay,
            a.Municipality,
            a.Province
        FROM project p
        LEFT JOIN address a ON p.AddressID = a.AddressID
        WHERE p.ProjectID = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    exit;
}

$project = $result->fetch_assoc();

// Format client full name
$project['ClientName'] = trim($project['ClientFName'] . ' ' . $project['ClientLName']);

// Format Survey Period
$project['SurveyStartDate'] = $project['SurveyStartDate'] ? date('F j, Y', strtotime($project['SurveyStartDate'])) : '';
$project['SurveyEndDate'] = $project['SurveyEndDate'] ? date('F j, Y', strtotime($project['SurveyEndDate'])) : '';

// Build full address
$addressParts = array_filter([
    $project['Address'] ?? '',
    $project['Barangay'] ?? '',
    $project['Municipality'] ?? '',
    $project['Province'] ?? ''
]);
$project['FullAddress'] = implode(', ', $addressParts);

// Predefined document types (display names)
$predefinedDocs = [
    "Original Plan",
    "Lot Title",
    "Deed of Sale",
    "Tax Declaration",
    "Building Permit",
    "Authorization Letter",
    "Others"
];

// Normalize mapping: ["original-plan" => "Original Plan", ...]
$normalizedMap = [];
foreach ($predefinedDocs as $docName) {
    $normalizedKey = strtolower(str_replace(' ', '-', $docName));
    $normalizedMap[$normalizedKey] = $docName;
}

// Initialize all predefined documents with empty statuses
$documents = [];
foreach ($predefinedDocs as $docName) {
    $documents[strtolower(str_replace(' ', '-', $docName))] = [
        'name' => $docName,
        'physical_status' => '',
        'digital_status' => ''
    ];
}

// Fetch documents from database
$doc_sql = "SELECT DocumentName, DocumentStatus, DigitalLocation FROM document WHERE ProjectID = ?";
$doc_stmt = $conn->prepare($doc_sql);
$doc_stmt->bind_param("s", $projectId);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();

while ($doc = $doc_result->fetch_assoc()) {
    $docNameRaw = $doc['DocumentName'];
    $matchedKey = null;

    foreach ($normalizedMap as $key => $label) {
        // Match based on whether DocumentName ends with or contains the normalized keyword
        if (stripos(str_replace(' ', '-', $docNameRaw), $key) !== false) {
            $matchedKey = $key;
            break;
        }
    }

    if ($matchedKey !== null) {
        $documents[$matchedKey]['physical_status'] = strtoupper($doc['DocumentStatus'] ?? '');
        $documents[$matchedKey]['digital_status'] = !empty(trim($doc['DigitalLocation'])) ? 'available' : '';
    }
}

// Re-index as array
$project['documents'] = array_values($documents);

// Output lowercase key for frontend
$project['Physicallocation'] = $project['PhysicalLocation'] ?? '';

echo json_encode(['success' => true, 'project' => $project]);
