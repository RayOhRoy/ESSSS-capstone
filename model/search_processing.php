<?php

include '../server/server.php';

// Get inputs from GET with keys matching your form input names
$project      = $_GET['projectName']    ?? '';
$lot          = $_GET['lotNumber']      ?? '';
$fname        = $_GET['clientFName']    ?? '';
$lname        = $_GET['clientLName']    ?? '';
$province     = $_GET['province']       ?? '';
$municipality = $_GET['municipality']   ?? '';
$barangay     = $_GET['barangay']       ?? '';
$surveyType   = $_GET['surveyType']     ?? '';
$agent        = $_GET['agent']          ?? '';
$approval     = $_GET['processingType'] ?? '';
$status       = $_GET['projectStatus']  ?? '';
$startDate    = $_GET['startDate']      ?? '';
$endDate      = $_GET['endDate']        ?? '';

// Start query with JOIN to address table
$sql = "SELECT project.*, address.Province, address.Municipality, address.Barangay 
        FROM project 
        JOIN address ON project.AddressID = address.AddressID 
        WHERE 1=1";

$params = [];
$types = "";

// Add LIKE conditions for text fields
if ($project) {
    $sql .= " AND project.ProjectID LIKE ?";
    $params[] = "%$project%";
    $types .= "s";
}
if ($lot) {
    $sql .= " AND project.LotNo LIKE ?";
    $params[] = "%$lot%";
    $types .= "s";
}
if ($fname) {
    $sql .= " AND project.ClientFName LIKE ?";
    $params[] = "%$fname%";
    $types .= "s";
}
if ($lname) {
    $sql .= " AND project.ClientLName LIKE ?";
    $params[] = "%$lname%";
    $types .= "s";
}
if ($agent) {
    $sql .= " AND project.Agent LIKE ?";
    $params[] = "%$agent%";
    $types .= "s";
}

// Exact match conditions for enums or fixed values
if ($surveyType) {
    $sql .= " AND project.SurveyType = ?";
    $params[] = $surveyType;
    $types .= "s";
}
if ($approval) {
    $sql .= " AND IFNULL(project.Approval, 'Sketch Plan') = ?";
    $params[] = $approval;
    $types .= "s";
}
if ($status) {
    $sql .= " AND project.ProjectStatus = ?";
    $params[] = $status;
    $types .= "s";
}

// Location filters from address table
if ($province) {
    $sql .= " AND address.Province LIKE ?";
    $params[] = "%$province%";
    $types .= "s";
}
if ($municipality) {
    $sql .= " AND address.Municipality LIKE ?";
    $params[] = "%$municipality%";
    $types .= "s";
}
if ($barangay) {
    $sql .= " AND address.Barangay LIKE ?";
    $params[] = "%$barangay%";
    $types .= "s";
}

// Date filters
if ($startDate) {
    $sql .= " AND project.SurveyStartDate >= ?";
    $params[] = $startDate;
    $types .= "s";
}
if ($endDate) {
    $sql .= " AND (project.SurveyEndDate <= ? OR project.SurveyEndDate IS NULL)";
    $params[] = $endDate;
    $types .= "s";
}

// Prepare and execute
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>No results found.</p>";
} else {
    // Style block
    echo "
    <style>
        .result-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
            font-family: Arial, sans-serif;
        }

        .result-item {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 20px;
            padding: 15px 20px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            cursor: pointer;
        }

        .result-item:hover {
            background-color: #ecdedeff !important;
        }

        .field {
            flex: 1 1 7%;
            min-width: 190px;
        }

        .field strong {
            display: block;
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 13px;
            color: #555;
        }

        @media screen and (max-width: 768px) {
            .result-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .field {
                width: 100%;
            }
        }
    </style>";

    // Result list
    echo "<ul class='result-list'>";

    while ($row = $result->fetch_assoc()) {
        $projectId     = htmlspecialchars($row['ProjectID']);
        $lot           = htmlspecialchars($row['LotNo']);
        $client        = htmlspecialchars($row['ClientFName'] . ' ' . $row['ClientLName']);
        $province      = htmlspecialchars($row['Province']);
        $municipality  = htmlspecialchars($row['Municipality']);
        $barangay      = htmlspecialchars($row['Barangay']);
        $address       = htmlspecialchars("{$barangay}, {$municipality}, {$province}");
        $surveyType    = htmlspecialchars($row['SurveyType']);
        $approval      = htmlspecialchars($row['Approval'] ?? 'Sketch Plan');
        $status        = htmlspecialchars($row['ProjectStatus']);

        echo "<li class='result-item' data-projectid='{$projectId}'>
                <div class='field'><strong>Project ID</strong>{$projectId}</div>
                <div class='field'><strong>Lot No</strong>{$lot}</div>
                <div class='field'><strong>Client</strong>{$client}</div>
                <div class='field'><strong>Address</strong>{$address}</div>
                <div class='field'><strong>Survey Type</strong>{$surveyType}</div>
                <div class='field'><strong>Processing Type</strong>{$approval}</div>
                <div class='field'><strong>Status</strong>{$status}</div>
            </li>";
    }

    echo "</ul>";
}

$conn->close();
?>
