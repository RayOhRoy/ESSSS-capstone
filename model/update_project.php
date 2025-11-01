<?php
session_start();
include '../server/server.php';
include 'phpqrcode/qrlib.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'POST required']));
}

date_default_timezone_set("Asia/Manila");

$employeeID = $_SESSION['employeeid'] ?? null;
if (!$employeeID) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Required: project ID to update
$projectID = $_POST['projectId'] ?? '';
if (!$projectID) {
    die(json_encode(['status' => 'error', 'message' => 'Project ID missing']));
}

// Sanitize helper
function safe_escape(mysqli $conn, $key)
{
    $val = $_POST[$key] ?? '';
    if (!is_string($val))
        $val = '';
    return mysqli_real_escape_string($conn, $val);
}

// — Address Handling —
$province = safe_escape($conn, 'province');
$municipality = safe_escape($conn, 'municipality');
$barangay = safe_escape($conn, 'barangay');
$street = safe_escape($conn, 'street');

// Get current AddressID for this project
$sqlAddrID = "SELECT AddressID FROM project WHERE ProjectID = '$projectID'";
$resAddrID = $conn->query($sqlAddrID);
if (!$resAddrID || $resAddrID->num_rows === 0) {
    die(json_encode(['status' => 'error', 'message' => 'Project not found']));
}
$addressID = $resAddrID->fetch_assoc()['AddressID'];

// Update address record
$sqlUpdateAddress = "UPDATE address 
    SET Province = '$province', Municipality = '$municipality', Barangay = '$barangay', Address = '$street'
    WHERE AddressID = '$addressID'";
if (!$conn->query($sqlUpdateAddress)) {
    die(json_encode(['status' => 'error', 'message' => 'Error updating address', 'error' => $conn->error]));
}

// — Project Handling —
$lotNo = safe_escape($conn, 'lotNumber');
$fname = safe_escape($conn, 'clientFirstName');
$lname = safe_escape($conn, 'clientLastName');
$surveyType = safe_escape($conn, 'surveyType');
$agent = safe_escape($conn, 'agent');
$projectStatus = safe_escape($conn, 'projectStatus');
$startDate = safe_escape($conn, 'surveyStartDate');
$endDate = safe_escape($conn, 'surveyEndDate');
$requestType = safe_escape($conn, 'requestType');
$approval = isset($_POST['approval']) ? mysqli_real_escape_string($conn, $_POST['approval']) : null;

// Generate new Survey Code
function getSurveyCode($type)
{
    $typeClean = strtoupper(str_replace([' ', '/', '-'], '', trim($type)));
    if (strpos($typeClean, 'ASBUILTSURVEY') !== false)
        return 'ASB';
    return substr($typeClean, 0, 3);
}

$surveyCode = getSurveyCode($surveyType);

// Replace the suffix in projectID (e.g. HAG-01-001-SKE)
$parts = explode('-', $projectID);
if (count($parts) === 4) {
    $parts[3] = $surveyCode;
    $newProjectID = implode('-', $parts);
} else {
    $newProjectID = $projectID;
}

// Remove suffix for folders, QR, and digital location
$baseProjectID = preg_replace('/-[A-Z]{3}$/', '', $newProjectID);

// — File & Folder Logic —
$basePath = '../uploads';
$newFolderPath = "$basePath/$baseProjectID";
if (!is_dir($newFolderPath)) mkdir($newFolderPath, 0777, true);

// Generate Project QR
$qrContent = "uploads/$baseProjectID";
$newQRFile = "$newFolderPath/{$baseProjectID}-QR.png";
foreach (glob("$newFolderPath/*-QR.png") as $oldQR) unlink($oldQR);
QRcode::png($qrContent, $newQRFile, QR_ECLEVEL_L, 4);

// Update project record
$newQRPath = "uploads/$baseProjectID/{$baseProjectID}-QR.png";
$sqlUpdateProject = "UPDATE project SET 
    ProjectID = '$newProjectID',
    LotNo = '$lotNo',
    ClientFName = '$fname',
    ClientLName = '$lname',
    SurveyType = '$surveyType',
    SurveyStartDate = '$startDate',
    SurveyEndDate = '$endDate',
    Agent = '$agent',
    RequestType = '$requestType',
    Approval = " . ($approval !== null ? "'$approval'" : "NULL") . ",
    ProjectStatus = '$projectStatus',
    ProjectQR = '$newQRPath',
    DigitalLocation = 'uploads/$baseProjectID'
    WHERE ProjectID = '$projectID'";
if (!$conn->query($sqlUpdateProject)) {
    die(json_encode(['status' => 'error', 'message' => 'Error updating project', 'error' => $conn->error]));
}

// — Handle document table —

// 1️⃣ Handle deletions
$deleteDocIDs = $_POST['delete_rows'] ?? [];
if (!empty($deleteDocIDs) && is_array($deleteDocIDs)) {
    foreach ($deleteDocIDs as $docIDToDelete) {
        $docIDToDelete = mysqli_real_escape_string($conn, $docIDToDelete);

        $resDoc = $conn->query("SELECT DocumentQR, DocumentName FROM document WHERE DocumentID = '$docIDToDelete'");
        $docName = '';
        if ($resDoc && $resDoc->num_rows > 0) {
            $docData = $resDoc->fetch_assoc();
            $docName = $docData['DocumentName'];
            $qrPath = $docData['DocumentQR'];
            if (file_exists("../$qrPath")) unlink("../$qrPath");
        }

        $conn->query("DELETE FROM document WHERE DocumentID = '$docIDToDelete'");

        $sqlActivityLog = "INSERT INTO activity_log 
            (ActivityLogID, ProjectID, Status, EmployeeID, Time, DocumentName) 
            VALUES (
                'ACT-" . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . "',
                '$newProjectID',
                'DELETED',
                '$employeeID',
                '" . date('Y-m-d H:i:s') . "',
                '" . mysqli_real_escape_string($conn, $docName) . "'
            )";
        $conn->query($sqlActivityLog);
    }
}

// 2️⃣ Handle new rows (physical or digital)
$newRows = $_POST['new_rows'] ?? [];
if (!empty($newRows) && is_array($newRows)) {
    foreach ($newRows as $rowJSON) {
        $rowData = json_decode($rowJSON, true);
        if (!$rowData) continue;

        $physical = !empty($rowData['physical']) ? 1 : 0;
        $digitalName = $rowData['digital'] ?? '';
        $docLabel = $rowData['label'] ?? 'Unknown';
        $docTypeSafe = $docLabel; // Keep spaces

        // Folder for this document type
        $docFolder = "../uploads/$baseProjectID/$docTypeSafe";
        if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

        // Handle uploaded digital file
        $digitalLocation = null;
        if (!empty($digitalName)) {
            foreach ($_FILES as $fileInput => $fileGroup) {
                if (!empty($fileGroup['name'])) {
                    foreach ($fileGroup['name'] as $i => $fname) {
                        if ($fname === $digitalName) {
                            $tmpName = $fileGroup['tmp_name'][$i];
                            $extension = pathinfo($digitalName, PATHINFO_EXTENSION);
                            $newFileName = "$docTypeSafe.$extension";
                            $dest = "$docFolder/$newFileName";

                            if (move_uploaded_file($tmpName, $dest)) {
                                $digitalLocation = "../uploads/$baseProjectID/$docTypeSafe/$newFileName";
                            }
                        }
                    }
                }
            }
        }

        // Generate document QR
        $docQR = "uploads/$baseProjectID/$docTypeSafe/{$baseProjectID}-$docTypeSafe-QR.png";
        QRcode::png("uploads/$baseProjectID/$docTypeSafe", "../$docQR", QR_ECLEVEL_L, 4);

        // Generate new DocumentID
        $resLastDoc = $conn->query("SELECT DocumentID FROM document ORDER BY DocumentID DESC LIMIT 1");
        $newDocNum = ($resLastDoc && $resLastDoc->num_rows > 0)
            ? str_pad(intval(substr($resLastDoc->fetch_assoc()['DocumentID'], 4)) + 1, 5, "0", STR_PAD_LEFT)
            : "00001";
        $documentID = "DOC-" . $newDocNum;

        $documentName = "$newProjectID-$docTypeSafe";
        $documentStatus = $physical ? 'Stored' : null;

        // Insert document record
        $sqlInsertDoc = "INSERT INTO document 
            (DocumentID, ProjectID, DocumentName, DocumentType, DigitalLocation, DocumentStatus, DocumentQR) 
            VALUES (
                '$documentID',
                '$newProjectID',
                '$documentName',
                '$docTypeSafe',
                " . ($digitalLocation ? "'$digitalLocation'" : "NULL") . ",
                " . ($documentStatus ? "'$documentStatus'" : "NULL") . ",
                '$docQR'
            )";
        $conn->query($sqlInsertDoc);

        // Log upload
        $sqlActivityLog = "INSERT INTO activity_log 
            (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time, DocumentName)
            VALUES (
                'ACT-" . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . "',
                '$newProjectID',
                '$documentID',
                'UPLOADED',
                '$employeeID',
                '" . date('Y-m-d H:i:s') . "',
                '$documentName'
            )";
        $conn->query($sqlActivityLog);
    }
}

// 3️⃣ Update existing documents
$sqlGetDocuments = "SELECT DocumentID, DocumentName, DocumentType FROM document WHERE ProjectID = '$projectID'";
$resDocuments = $conn->query($sqlGetDocuments);
if ($resDocuments && $resDocuments->num_rows > 0) {
    while ($doc = $resDocuments->fetch_assoc()) {
        $docID = $doc['DocumentID'];
        $docType = $doc['DocumentType'];
        $newDocName = "$newProjectID-$docType";
        $newDocQRPath = "uploads/$baseProjectID/$docType/{$baseProjectID}-$docType-QR.png";

        $sqlUpdateDoc = "UPDATE document SET 
            ProjectID = '$newProjectID',
            DocumentName = '$newDocName',
            DocumentQR = '$newDocQRPath'
            WHERE DocumentID = '$docID'";
        $conn->query($sqlUpdateDoc);
    }
}

// Log project modification
$sqlLastActivity = "SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1";
$resLastActivity = $conn->query($sqlLastActivity);
$newActivityNum = ($resLastActivity && $resLastActivity->num_rows > 0)
    ? str_pad(intval(substr($resLastActivity->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
    : "001";
$activityLogID = "ACT-" . $newActivityNum;
$timeNow = date('Y-m-d H:i:s');

$sqlActivityLog = "INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time) 
    VALUES ('$activityLogID','$newProjectID','MODIFIED','$employeeID', '$timeNow')";
$conn->query($sqlActivityLog);

// ✅ Return success JSON
echo json_encode([
    'status' => 'success',
    'message' => 'Project updated successfully.',
    'projectID' => $newProjectID
]);

$conn->close();
?>
