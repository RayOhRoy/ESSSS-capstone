<?php
session_start();
include '../server/server.php'; // adjust path to your DB connection

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

// Fetch project info
$sql = "SELECT ProjectID, LotNo, ClientLName, ClientFName, SurveyType, PhysicalLocation, DigitalLocation, SurveyStartDate, SurveyEndDate, Agent, ProjectQR
        FROM project WHERE ProjectID = ?";
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
$project['client_name'] = trim($project['ClientFName'] . ' ' . $project['ClientLName']);

// Format dates
$project['start_date'] = date('F j, Y', strtotime($project['SurveyStartDate']));
$project['end_date'] = date('F j, Y', strtotime($project['SurveyEndDate']));

// Example document retrieval if you have documents table (adjust as needed)
// For now, dummy static example for demonstration:
$project['documents'] = [
    ['name' => 'Original Plan', 'physical_status' => 'stored', 'digital_status' => 'available'],
    ['name' => 'Lot Title', 'physical_status' => 'released', 'digital_status' => 'available'],
    ['name' => 'Ref Plan/Lot Data', 'physical_status' => 'stored', 'digital_status' => 'available'],
    ['name' => 'TD', 'physical_status' => 'stored', 'digital_status' => 'available'],
    ['name' => 'Transmittal', 'physical_status' => 'stored', 'digital_status' => 'available'],
    ['name' => 'Field Notes', 'physical_status' => 'released', 'digital_status' => 'available'],
    ['name' => 'Deed of Sale/Transfer', 'physical_status' => 'stored', 'digital_status' => 'available'],
    ['name' => 'Tax Declaration', 'physical_status' => 'stored', 'digital_status' => 'available'],
];

echo json_encode(['success' => true, 'project' => $project]);
