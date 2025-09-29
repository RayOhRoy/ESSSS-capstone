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
    empty($surveyType) &&
    empty($doctype)
) {
    echo "<p>Provide input or select from the list to initiate a search for matching project data...</p>";
    exit;
}

// CSS and header
echo "
<style>
.result-item {
    display: flex;
    flex-wrap: nowrap; /* keep fields in a row */
    align-items: center;
    height: 3rem;
    margin-bottom: 1%;
    background-color: white;
    /* Removed border */
    border-radius: 6px;
    cursor: pointer;
    color: black;
    transition: all 0.2s ease;
    justify-content: space-between;
    /* Added subtle shadow below */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.result-item .field {
    flex-basis: 33%; /* equally divide space */
    flex-grow: 0;
    flex-shrink: 0;
    min-width: 0;
    color: black;
    display: flex;
    align-items: center;
}

.result-item:hover {
    filter: brightness(0.85);
    /* Optionally intensify shadow on hover */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

/* First field aligned left */
.result-item .field:nth-child(1) {
    justify-content: flex-start;
}

/* Second field aligned center */
.result-item .field:nth-child(2) {
    justify-content: center;
}

/* Third field aligned right */
.result-item .field:nth-child(3) {
    justify-content: flex-end;
}

/* Responsive */
@media screen and (max-width: 768px) {
    .result-item {
        flex-direction: column;
        align-items: flex-start;
        height: auto;
    }
    .result-item .field {
        width: 100%;
        justify-content: flex-start !important;
        margin-bottom: 4px;
    }
}
</style>

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
$hasResults = false;  // flag to detect if we output anything

// Show project list only if doctype is 'Project'
if ($doctype === "Project") {
    while ($row = $result1->fetch_assoc()) {
        $hasResults = true;
        $projectId = htmlspecialchars($row['ProjectID']);
        $matchedProjectIds[] = $row['ProjectID']; // store raw value

        echo "<li class='result-item' data-projectid='{$projectId}'>
                <div class='field'>Project</div>
                <div class='field'>{$projectId}</div>
                <div class='field'><i class='fa fa-eye'></i></div>
            </li>";
    }
}
// Show projects only if no doctype filter
elseif (empty($doctype)) {
    while ($row = $result1->fetch_assoc()) {
        $hasResults = true;
        $projectId = htmlspecialchars($row['ProjectID']);
        $matchedProjectIds[] = $row['ProjectID']; // store raw value

        echo "<li class='result-item' data-projectid='{$projectId}'>
                <div class='field'>Project</div>
                <div class='field'>{$projectId}</div>
                <div class='field'><i class='fa fa-eye'></i></div>
            </li>";
    }
}
// For any other doctype, just collect project IDs, no output here
else {
    while ($row = $result1->fetch_assoc()) {
        $matchedProjectIds[] = $row['ProjectID'];
    }
}

// ---------------------
// 2. Fetch matching documents by filtered ProjectIDs
// ---------------------

if (!empty($matchedProjectIds) && $doctype !== "Project") {
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

    if ($result2->num_rows > 0) {
        $hasResults = true;
    }

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

// If nothing was output, show a no results message
if (!$hasResults) {
    echo "<p>No matching results found.</p>";
}

echo "</ul>";
$conn->close();
?>
