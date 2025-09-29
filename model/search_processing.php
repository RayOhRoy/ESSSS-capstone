<?php

include '../server/server.php';

// Get inputs from GET
$project = $_GET['projectName'] ?? '';
$lot = $_GET['lotNumber'] ?? '';
$fname = $_GET['clientFName'] ?? '';
$lname = $_GET['clientLName'] ?? '';
$province = $_GET['province'] ?? '';
$municipality = $_GET['municipality'] ?? '';
$barangay = $_GET['barangay'] ?? '';
$surveyType = $_GET['surveyType'] ?? '';
$doctype = $_GET['doctype'] ?? '';

// Prevent execution if all are empty
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

// CSS and header
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
</style>

<div class='result-header'>
    <div class='field'>Document Type</div>
    <div class='field'>Project ID</div>
    <div class='field'>Preview</div>
</div>
<ul class='result-list'>
";

// ---------------------
// 1. Fetch matching projects
// ---------------------

$sqlProject = "SELECT project.ProjectID
               FROM project
               JOIN address ON project.AddressID = address.AddressID
               WHERE 1=1";

$params = [];
$types = "";

if ($project) {
    $sqlProject .= " AND project.ProjectID LIKE ?";
    $params[] = "%$project%";
    $types .= "s";
}
if ($lot) {
    $sqlProject .= " AND project.LotNo LIKE ?";
    $params[] = "%$lot%";
    $types .= "s";
}
if ($fname) {
    $sqlProject .= " AND project.ClientFName LIKE ?";
    $params[] = "%$fname%";
    $types .= "s";
}
if ($lname) {
    $sqlProject .= " AND project.ClientLName LIKE ?";
    $params[] = "%$lname%";
    $types .= "s";
}
if ($surveyType) {
    $sqlProject .= " AND project.SurveyType = ?";
    $params[] = $surveyType;
    $types .= "s";
}
if ($province) {
    $sqlProject .= " AND address.Province LIKE ?";
    $params[] = "%$province%";
    $types .= "s";
}
if ($municipality) {
    $sqlProject .= " AND address.Municipality LIKE ?";
    $params[] = "%$municipality%";
    $types .= "s";
}
if ($barangay) {
    $sqlProject .= " AND address.Barangay LIKE ?";
    $params[] = "%$barangay%";
    $types .= "s";
}

$stmt1 = $conn->prepare($sqlProject);
if (!empty($params)) {
    $stmt1->bind_param($types, ...$params);
}
$stmt1->execute();
$result1 = $stmt1->get_result();

// Collect matched project IDs
$matchedProjectIds = [];

if (empty($doctype)) {  // Show projects only if no document type filter
    while ($row = $result1->fetch_assoc()) {
        $projectId = htmlspecialchars($row['ProjectID']);
        $matchedProjectIds[] = $row['ProjectID']; // store raw value for SQL use

        echo "<li class='result-item' data-projectid='{$projectId}'>
                <div class='field'>Project</div>
                <div class='field'>{$projectId}</div>
                <div class='field'><i class='fa fa-eye'></i></div>
            </li>";
    }
} else {
    while ($row = $result1->fetch_assoc()) {
        $matchedProjectIds[] = $row['ProjectID']; // just collect project IDs, no output
    }
}

// ---------------------
// 2. Fetch matching documents by filtered ProjectIDs
// ---------------------
if (!empty($matchedProjectIds)) {
    $placeholders = implode(',', array_fill(0, count($matchedProjectIds), '?'));
    
    if ($doctype) {
        $sqlDocs = "SELECT ProjectID, DocumentType FROM document WHERE ProjectID IN ($placeholders) AND DocumentType = ?";
        $stmt2 = $conn->prepare($sqlDocs);

        $typesDoc = str_repeat('s', count($matchedProjectIds)) . 's';
        $params = array_merge($matchedProjectIds, [$doctype]);
        $stmt2->bind_param($typesDoc, ...$params);
    } else {
        $sqlDocs = "SELECT ProjectID, DocumentType FROM document WHERE ProjectID IN ($placeholders)";
        $stmt2 = $conn->prepare($sqlDocs);

        $typesDoc = str_repeat('s', count($matchedProjectIds));
        $stmt2->bind_param($typesDoc, ...$matchedProjectIds);
    }

    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($doc = $result2->fetch_assoc()) {
        $projectId = htmlspecialchars($doc['ProjectID']);
        $docType = htmlspecialchars($doc['DocumentType']);

        echo "<li class='result-item' data-projectid='{$projectId}'>
            <div class='field'>{$docType}</div>
            <div class='field'>{$projectId}</div>
            <div class='field'><i class='fa fa-eye'></i></div>
        </li>";
    }
}

echo "</ul>";
$conn->close();
?>