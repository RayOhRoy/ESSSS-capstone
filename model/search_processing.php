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
    echo "<p>To proceed, please enter search details or select criteria from the list.</p>";
    exit;
}

// CSS and header
echo "
<style>
.result-list {
    max-height: 20rem;
    overflow-y: auto; 
}

.result-item {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    height: 3rem;
    margin-bottom: 1%;
    background-color: white;
    border-radius: 6px;
    cursor: pointer;
    color: black;
    transition: all 0.2s ease;
    justify-content: space-between;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.result-item .field {
    flex-basis: 25%;
    flex-grow: 0;
    flex-shrink: 0;
    min-width: 0;
    color: black;
    display: flex;
    align-items: center;
}

.result-item:hover {
    filter: brightness(0.85);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.result-item .field:nth-child(1) {
    justify-content: flex-start;
}
.result-item .field:nth-child(2) {
    justify-content: center;
}
.result-item .field:nth-child(3) {
    justify-content: center;
}
.result-item .field:nth-child(4) {
    justify-content: flex-end;
}

.result-item .fa-eye {
    cursor: pointer;
    color: #007bff;
    transition: color 0.2s ease;
}

.result-item .fa-eye:hover {
    color: #0056b3;
}

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

$matchedProjectIds = [];
$hasResults = false;

while ($row = $result1->fetch_assoc()) {
    $matchedProjectIds[] = $row['ProjectID'];
}

// ---------------------
// 2. Show Project Document Type - Always 2 rows (Digital + Physical)
// ---------------------

if (!empty($matchedProjectIds) && ($doctype === "Project" || empty($doctype))) {
    foreach ($matchedProjectIds as $projectId) {
        $hasResults = true;

        // Fetch Digital version
        $sqlDigital = "SELECT 1 FROM document WHERE ProjectID = ? AND DocumentType = 'Project' AND DigitalLocation IS NOT NULL LIMIT 1";
        $stmtDigital = $conn->prepare($sqlDigital);
        $stmtDigital->bind_param("s", $projectId);
        $stmtDigital->execute();
        $stmtDigital->store_result();
        $hasDigital = $stmtDigital->num_rows > 0;

        // Fetch Physical version
        $sqlPhysical = "SELECT 1 FROM document WHERE ProjectID = ? AND DocumentType = 'Project' AND DocumentStatus IS NOT NULL LIMIT 1";
        $stmtPhysical = $conn->prepare($sqlPhysical);
        $stmtPhysical->bind_param("s", $projectId);
        $stmtPhysical->execute();
        $stmtPhysical->store_result();
        $hasPhysical = $stmtPhysical->num_rows > 0;

        // Output Digital row
        echo "<li class='result-item' data-projectid='{$projectId}'>
                <div class='field'>Project</div>
                <div class='field'>{$projectId}</div>
                <div class='field'>Digital</div>
                <div class='field'><i class='fa fa-eye' data-value='ProjectQR'></i></div>
            </li>";

        // Output Physical row
        echo "<li class='result-item' data-projectid='{$projectId}'>
                <div class='field'>Project</div>
                <div class='field'>{$projectId}</div>
                <div class='field'>Physical</div>
                <div class='field'><i class='fa fa-eye' data-value='ProjectQR'></i></div>
            </li>";
    }
}

// ---------------------
// 3. Other Document Types (e.g. TaxDec, SurveyPlan...)
// ---------------------

if (!empty($matchedProjectIds) && $doctype !== "Project") {
    $placeholders = implode(',', array_fill(0, count($matchedProjectIds), '?'));
    $sqlDocs = "SELECT ProjectID, DocumentType, DigitalLocation, DocumentStatus 
                FROM document 
                WHERE ProjectID IN ($placeholders)";
    if ($doctype) {
        $sqlDocs .= " AND DocumentType = ?";
        $stmtDocs = $conn->prepare($sqlDocs);
        $typesDoc = str_repeat('s', count($matchedProjectIds)) . 's';
        $params = array_merge($matchedProjectIds, [$doctype]);
        $stmtDocs->bind_param($typesDoc, ...$params);
    } else {
        $stmtDocs = $conn->prepare($sqlDocs);
        $typesDoc = str_repeat('s', count($matchedProjectIds));
        $stmtDocs->bind_param($typesDoc, ...$matchedProjectIds);
    }

    $stmtDocs->execute();
    $result2 = $stmtDocs->get_result();

    while ($doc = $result2->fetch_assoc()) {
        $projectId = htmlspecialchars($doc['ProjectID']);
        $docType = htmlspecialchars($doc['DocumentType']);
        $isDigital = !empty($doc['DigitalLocation']);
        $isPhysical = !empty($doc['DocumentStatus']);

        if ($isDigital) {
            echo "<li class='result-item' data-projectid='{$projectId}'>
                    <div class='field'>{$docType}</div>
                    <div class='field'>{$projectId}</div>
                    <div class='field'>Digital</div>
                    <div class='field'><i class='fa fa-eye' data-value='DocumentQR'></i></div>
                </li>";
        }

        if ($isPhysical) {
            echo "<li class='result-item' data-projectid='{$projectId}'>
                    <div class='field'>{$docType}</div>
                    <div class='field'>{$projectId}</div>
                    <div class='field'>Physical</div>
                    <div class='field'><i class='fa fa-eye' data-value='DocumentQR'></i></div>
                </li>";
        }

        $hasResults = true;
    }
}

// If no results at all
if (!$hasResults) {
    echo "<p>No matching results found.</p>";
}

echo "</ul>";
$conn->close();
?>
