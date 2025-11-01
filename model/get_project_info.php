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

$projectId = $conn->real_escape_string(trim($projectId));

// 🧹 Remove "-ABC" or any trailing 3-letter suffix (case-insensitive)
$projectId = preg_replace('/-[a-z]{3}$/i', '', $projectId);

// Fetch project info with AddressID and requestType, approvalType
$sql = "SELECT 
            p.ProjectID,
            p.LotNo,
            p.ClientLName,
            p.ClientFName,
            p.SurveyType,
            p.DigitalLocation,
            p.SurveyStartDate,
            p.SurveyEndDate,
            p.Agent,
            p.ProjectQR,
            p.AddressID,
            a.Address,
            a.Barangay,
            a.Municipality,
            a.Province,
            p.RequestType,
            p.Approval
        FROM project p
        LEFT JOIN address a ON p.AddressID = a.AddressID
        WHERE p.ProjectID LIKE CONCAT(?, '%')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    exit;
}

$project = $result->fetch_assoc();

$requestType = $project['RequestType'] ?? '';
$approvalType = $project['Approval'] ?? '';

// Format client full name
$project['ClientName'] = trim(($project['ClientFName'] ?? '') . ' ' . ($project['ClientLName'] ?? ''));

// Format survey dates
$project['SurveyStartDate'] = $project['SurveyStartDate'] && $project['SurveyStartDate'] !== '0000-00-00' 
    ? date('F j, Y', strtotime($project['SurveyStartDate'])) 
    : '';

if ($project['SurveyEndDate'] && $project['SurveyEndDate'] !== '0000-00-00') {
    $project['SurveyEndDate'] = date('F j, Y', strtotime($project['SurveyEndDate']));
} else {
    $project['SurveyEndDate'] = 'Ongoing';
}

// Build full address
$addressParts = array_filter([
    $project['Address'] ?? '',
    $project['Barangay'] ?? '',
    $project['Municipality'] ?? '',
    $project['Province'] ?? ''
]);
$project['FullAddress'] = implode(', ', $addressParts);

// Determine docsToRender based on RequestType and ApprovalType from project table
if ($requestType === "For Approval" && $approvalType === "PSD") {
    $docsToRender = [
        "Original Plan",
        "Certified Title",
        "Reference Plan",
        "Lot Data",
        "Technical Description",
        "Transmital",
        "Fieldnotes",
        "Tax Declaration",
        "Blueprint",
        "CAD File",
    ];
} else if ($requestType === "For Approval" && $approvalType === "CSD") {
    $docsToRender = [
        "Original Plan",
        "Reference Plan",
        "Lot Data",
        "Cadastral Map",
        "Technical Description",
        "Transmital",
        "Fieldnotes",
        "Tax Declaration",
        "Survey Authority",
        "Blueprint",
        "CAD File",
    ];
} else if ($requestType === "For Approval" && $approvalType === "LRA") {
    $docsToRender = [
        "Original Plan",
        "Certified Title",
        "Reference Plan",
        "Lot Data",
        "Technical Description",
        "Fieldnotes",
        "Blueprint",
        "CAD File",
    ];
} else if ($requestType === "Sketch Plan") {
    $docsToRender = [
        "Original Plan",
        "Title",
        "Reference Plan",
        "Lot Data",
        "Tax Declaration",
        "Blueprint",
        "CAD File",
    ];
} else {
    $docsToRender = [
        "Original Plan",
        "Lot Title",
        "Deed of Sale",
        "Tax Declaration",
        "Building Permit",
        "Authorization Letter",
    ];
}

// Normalize mapping for lookup
$normalizedMap = [];
foreach ($docsToRender as $docName) {
    $normalizedKey = strtolower(str_replace(' ', '-', $docName));
    $normalizedMap[$normalizedKey] = $docName;
}

// Initialize documents array with empty statuses
$documents = [];
foreach ($docsToRender as $docName) {
    $key = strtolower(str_replace(' ', '-', $docName));
    $documents[$key] = [
        'name' => $docName,
        'physical_status' => '',
        'digital_status' => ''
    ];
}

// Fetch documents from DB for this project
$doc_sql = "SELECT DocumentName, DocumentStatus, DigitalLocation FROM document WHERE ProjectID LIKE CONCAT(?, '%')";
$doc_stmt = $conn->prepare($doc_sql);
$doc_stmt->bind_param("s", $projectId);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();

while ($doc = $doc_result->fetch_assoc()) {
    $docNameRaw = $doc['DocumentName'];
    $matchedKey = null;

    foreach ($normalizedMap as $key => $label) {
        if (stripos(str_replace(' ', '-', $docNameRaw), $key) !== false) {
            $matchedKey = $key;
            break;
        }
    }

    if ($matchedKey !== null && isset($documents[$matchedKey])) {
        $documentStatus = strtoupper(trim($doc['DocumentStatus'] ?? ''));
        $digitalLocation = trim($doc['DigitalLocation'] ?? '');

        // Set digital status if digital location exists
        if (!empty($digitalLocation)) {
            $documents[$matchedKey]['digital_status'] = 'available';
        }

        // Set physical status ONLY if DocumentStatus is NOT empty/null
        if (!empty($documentStatus)) {
            $documents[$matchedKey]['physical_status'] = $documentStatus;
        } else {
            unset($documents[$matchedKey]['physical_status']);
        }
    }
}

// Re-index array for frontend
$project['documents'] = array_values($documents);

// Output lowercase key for frontend (if needed)
$project['Physicallocation'] = $project['PhysicalLocation'] ?? '';

echo json_encode(['success' => true, 'project' => $project]);
