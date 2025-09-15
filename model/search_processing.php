<?php

include '../server/server.php'; 

// Get inputs from GET with keys matching your form input names
$project    = $_GET['projectName']    ?? '';
$lot        = $_GET['lotNumber']      ?? '';
$fname      = $_GET['clientFName']    ?? '';
$lname      = $_GET['clientLName']    ?? '';
$province   = $_GET['province']       ?? '';
$municipality = $_GET['municipality'] ?? '';
$barangay   = $_GET['barangay']       ?? '';
$surveyType = $_GET['surveyType']     ?? '';
$agent      = $_GET['agent']          ?? '';
$approval   = $_GET['processingType'] ?? '';
$status     = $_GET['projectStatus']  ?? '';
$startDate  = $_GET['startDate']      ?? '';
$endDate    = $_GET['endDate']        ?? '';

// Build query
$sql = "SELECT * FROM project WHERE 1=1";
$params = [];
$types = "";

// Add LIKE conditions for text fields
if ($project) {
    $sql .= " AND ProjectID LIKE ?";
    $params[] = "%$project%";
    $types .= "s";
}
if ($lot) {
    $sql .= " AND LotNo LIKE ?";
    $params[] = "%$lot%";
    $types .= "s";
}
if ($fname) {
    $sql .= " AND ClientFName LIKE ?";
    $params[] = "%$fname%";
    $types .= "s";
}
if ($lname) {
    $sql .= " AND ClientLName LIKE ?";
    $params[] = "%$lname%";
    $types .= "s";
}
if ($agent) {
    $sql .= " AND Agent LIKE ?";
    $params[] = "%$agent%";
    $types .= "s";
}

// Exact match conditions for enum or fixed sets
if ($surveyType) {
    $sql .= " AND SurveyType = ?";
    $params[] = $surveyType;
    $types .= "s";
}
if ($approval) {
    $sql .= " AND Approval = ?";
    $params[] = $approval;
    $types .= "s";
}
if ($status) {
    $sql .= " AND ProjectStatus = ?";
    $params[] = $status;
    $types .= "s";
}

// Location fields assumed stored inside DigitalLocation or separate fields (adjust as needed)
if ($province) {
    $sql .= " AND DigitalLocation LIKE ?";
    $params[] = "%$province%";
    $types .= "s";
}
if ($municipality) {
    $sql .= " AND DigitalLocation LIKE ?";
    $params[] = "%$municipality%";
    $types .= "s";
}
if ($barangay) {
    $sql .= " AND DigitalLocation LIKE ?";
    $params[] = "%$barangay%";
    $types .= "s";
}

// Date filters (assuming date format is correct, and SurveyEndDate can be null)
if ($startDate) {
    $sql .= " AND SurveyStartDate >= ?";
    $params[] = $startDate;
    $types .= "s";
}
if ($endDate) {
    $sql .= " AND (SurveyEndDate <= ? OR SurveyEndDate IS NULL)";
    $params[] = $endDate;
    $types .= "s";
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>No results found.</p>";
} else {
    echo "<ul class='result-list'>";
    while ($row = $result->fetch_assoc()) {
        $projectId = htmlspecialchars($row['ProjectID']);
        $lot = htmlspecialchars($row['LotNo']);
        $client = htmlspecialchars($row['ClientFName'] . ' ' . $row['ClientLName']);
        $agent = htmlspecialchars($row['Agent']);
        $status = htmlspecialchars($row['ProjectStatus']);

        echo "<li class='result-item' data-projectid='{$projectId}'>
                <strong>{$projectId}</strong> | Lot: {$lot} | Client: {$client} | Agent: {$agent} | Status: {$status}
            </li>";
    }
    echo "</ul>";
}

$conn->close();
?>
