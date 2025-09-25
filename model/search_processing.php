<?php

include '../server/server.php';

// Get inputs from GET with keys matching your form input names
$project = $_GET['projectName'] ?? '';
$lot = $_GET['lotNumber'] ?? '';
$fname = $_GET['clientFName'] ?? '';
$lname = $_GET['clientLName'] ?? '';
$province = $_GET['province'] ?? '';
$municipality = $_GET['municipality'] ?? '';
$barangay = $_GET['barangay'] ?? '';
$surveyType = $_GET['surveyType'] ?? '';

// 🛑 Prevent query execution if all fields are empty
if (
    empty($project) &&
    empty($lot) &&
    empty($fname) &&
    empty($lname) &&
    empty($province) &&
    empty($municipality) &&
    empty($barangay) &&
    empty($surveyType)
) {
    echo "<p>Provide input or select from the list to initiate a search for matching project data...</p>";
    exit;
}

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

// Exact match condition for survey type
if ($surveyType) {
    $sql .= " AND project.SurveyType = ?";
    $params[] = $surveyType;
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
        .result-header {
            display: flex;
            padding: 12px 20px;
            font-weight: bold;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 10px;
            font-family: Arial, sans-serif;
        }

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
            flex: 1;
            min-width: 0;
        }

        .field:nth-child(1) { color: #7B0302; font-weight: 700; text-align: left; }
        .field:nth-child(2) { text-align: center; }
        .field:nth-child(3) { color: #7B0302; text-align: right; }

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
    echo "
    <div class='result-header'>
        <div class='field'>Document Type</div>
        <div class='field'>Project Name</div>
        <div class='field'>Survey Type</div>
    </div>
    ";
    echo "<ul class='result-list'>";

    while ($row = $result->fetch_assoc()) {
        $projectId = htmlspecialchars($row['ProjectID']);
        $lot = htmlspecialchars($row['LotNo']);
        $client = htmlspecialchars($row['ClientFName'] . ' ' . $row['ClientLName']);
        $province = htmlspecialchars($row['Province']);
        $municipality = htmlspecialchars($row['Municipality']);
        $barangay = htmlspecialchars($row['Barangay']);
        $address = htmlspecialchars("{$barangay}, {$municipality}, {$province}");
        $surveyType = htmlspecialchars($row['SurveyType']);

        echo "<li class='result-item' data-projectid='{$projectId}'>
                <div class='field'>Project</div>
                <div class='field'>{$projectId}</div>
                <div class='field'>{$surveyType}</div>
            </li>";
    }

    echo "</ul>";
}

$conn->close();
?>