<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

session_start();
include '../server/server.php'; 
include 'phpqrcode/qrlib.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'POST required']));
}

date_default_timezone_set("Asia/Manila");

$employeeID = $_SESSION['employeeid'] ?? null;

$projectId = $_POST['projectId'] ?? null;
if (!$projectId) {
    // Throw error: missing projectId
    echo json_encode(['status' => 'error', 'message' => 'Missing projectId']);
    exit;
}
// Proceed with update using $projectId


// Get existing project info to check if project exists
$sqlCheckProject = "SELECT * FROM project WHERE ProjectID = '$projectID'";
$resCheck = $conn->query($sqlCheckProject);
if (!$resCheck || $resCheck->num_rows === 0) {
    die(json_encode([
        'status' => 'error', 
        'message' => 'Project not found: ' . htmlspecialchars($projectID)
    ]));
}


// — Address Handling —
// We assume AddressID won't change, but address fields may be updated
$province = mysqli_real_escape_string($conn, $_POST['province']);
$municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
$barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
$street = mysqli_real_escape_string($conn, $_POST['street']);

// Get current AddressID from project
$currentProject = $resCheck->fetch_assoc();
$currentAddressID = $currentProject['AddressID'] ?? null;

if (!$currentAddressID) {
    die(json_encode(['status' => 'error', 'message' => 'Associated address not found']));
}

// Update address fields
$sqlUpdateAddress = "UPDATE address SET Province='$province', Municipality='$municipality', Barangay='$barangay', Address='$street' WHERE AddressID='$currentAddressID'";
if ($conn->query($sqlUpdateAddress) !== TRUE) {
    die(json_encode(['status' => 'error', 'message' => 'Error updating address', 'error' => $conn->error]));
}

// — Project Handling —
$lotNo = mysqli_real_escape_string($conn, $_POST['lot_no']);
$fname = mysqli_real_escape_string($conn, $_POST['client_name']);
$lname = mysqli_real_escape_string($conn, $_POST['last_name']);
$surveyType = mysqli_real_escape_string($conn, $_POST['survey_type']);
$startDate = mysqli_real_escape_string($conn, $_POST['survey_start']);
$endDate = mysqli_real_escape_string($conn, $_POST['survey_end']);
$agent = mysqli_real_escape_string($conn, $_POST['agent']);
$requestType = mysqli_real_escape_string($conn, $_POST['requestType']);
$projectStatus = mysqli_real_escape_string($conn, $_POST['projectStatus']);
$approval = isset($_POST['approval']) ? mysqli_real_escape_string($conn, $_POST['approval']) : null;

// Update project fields
$sqlUpdateProject = "UPDATE project SET 
    LotNo = '$lotNo',
    ClientFName = '$fname',
    ClientLName = '$lname',
    SurveyType = '$surveyType',
    SurveyStartDate = '$startDate',
    SurveyEndDate = '$endDate',
    Agent = '$agent',
    RequestType = '$requestType',
    Approval = " . ($approval !== null ? "'$approval'" : "NULL") . ",
    ProjectStatus = '$projectStatus'
    WHERE ProjectID = '$projectID'";

if ($conn->query($sqlUpdateProject) !== TRUE) {
    die(json_encode(['status' => 'error', 'message' => 'Error updating project', 'error' => $conn->error]));
}

// Log project update activity
$sqlLastActivity = "SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1";
$resLastActivity = $conn->query($sqlLastActivity);
$newActivityNum = ($resLastActivity && $resLastActivity->num_rows > 0) 
                  ? str_pad(intval(substr($resLastActivity->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
                  : "001";
$activityLogID = "ACT-" . $newActivityNum;

$timeNow = date('Y-m-d H:i:s');

$sqlActivityLog = "INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time) 
                   VALUES ('$activityLogID','$projectID','UPDATED','$employeeID', '$timeNow')";
$conn->query($sqlActivityLog);

// — Document Handling —
// Handle removal of documents flagged for removal
foreach ($_POST as $key => $value) {
    if (strpos($key, 'remove_file_') === 0 && $value == '1') {
        // Determine which document to remove
        // Assuming the key contains enough info to identify the document; if not, you might need to pass docID in hidden input on client
        // Here, assume the key format is "remove_file_{DocumentID}"
        $docID = substr($key, strlen('remove_file_'));
        if ($docID) {
            // Get document info
            $sqlDoc = "SELECT * FROM document WHERE DocumentID = '$docID' AND ProjectID = '$projectID'";
            $resDoc = $conn->query($sqlDoc);
            if ($resDoc && $resDoc->num_rows > 0) {
                $doc = $resDoc->fetch_assoc();
                $digitalLoc = $doc['DigitalLocation'];

                // Delete files on server if exist
                if ($digitalLoc) {
                    $files = explode(";", $digitalLoc);
                    foreach ($files as $filePath) {
                        $fullPath = __DIR__ . '/../' . $filePath;
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                }

                // Delete document record
                $conn->query("DELETE FROM document WHERE DocumentID = '$docID'");

                // Log document removal
                $sqlLastActDoc = "SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1";
                $resLastActDoc = $conn->query($sqlLastActDoc);
                $newActNumDoc = ($resLastActDoc && $resLastActDoc->num_rows > 0)
                              ? str_pad(intval(substr($resLastActDoc->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
                              : "001";
                $activityLogIDDoc = "ACT-" . $newActNumDoc;
                $sqlActDoc = "INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time) 
                              VALUES ('$activityLogIDDoc','$projectID','$docID','DELETED','$employeeID', '$timeNow')";
                $conn->query($sqlActDoc);
            }
        }
    }
}

// Handle uploaded documents
$documentKeys = [];
foreach ($_FILES as $inputName => $fileGroup) {
    if (preg_match('/^digital_(.+)$/', $inputName, $matches)) {
        $documentKeys[] = $matches[1];
    }
}

$uploadBase = __DIR__ . '/../uploads/';
$projectFolder = $uploadBase . $projectID;
if (!is_dir($projectFolder)) mkdir($projectFolder, 0777, true);

foreach ($documentKeys as $docKey) {
    $docName = ucwords(str_replace("_", " ", $docKey));
    $safeDocName = str_replace(" ", "-", $docName);

    $physicalChecked = isset($_POST["physical_$docKey"]);
    $status = null;

    if ($physicalChecked && isset($_POST["status_$docKey"])) {
        $status = mysqli_real_escape_string($conn, $_POST["status_$docKey"]);
    }

    $uploadedFiles = [];
    if (!empty($_FILES["digital_$docKey"]['name'][0])) {
        $files = $_FILES["digital_$docKey"];
        foreach ($files['name'] as $i => $origName) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $newName = "$safeDocName-" . ($i + 1) . "." . $ext;
            $uploadedFiles[] = [
                'tmp_name' => $files['tmp_name'][$i],
                'newFileName' => $newName
            ];
        }
    }

    if (!$physicalChecked && count($uploadedFiles) === 0) {
        continue;
    }

    $docFolder = "$projectFolder/$safeDocName";
    if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

    $uploadedPaths = [];
    foreach ($uploadedFiles as $f) {
        $dest = "$docFolder/" . $f['newFileName'];
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            $uploadedPaths[] = "uploads/$projectID/$safeDocName/" . $f['newFileName'];
        }
    }

    // Check if document already exists for this project and document type
    $sqlCheckDoc = "SELECT DocumentID, DigitalLocation FROM document WHERE ProjectID='$projectID' AND DocumentType='$docName'";
    $resDoc = $conn->query($sqlCheckDoc);

    if ($resDoc && $resDoc->num_rows > 0) {
        $docRow = $resDoc->fetch_assoc();
        $existingDocID = $docRow['DocumentID'];
        $existingFiles = $docRow['DigitalLocation'];

        // Append new files if any to existing files
        $newDigitalLocation = $existingFiles;
        if (count($uploadedPaths) > 0) {
            $newDigitalLocation = trim($existingFiles . ";" . implode(";", $uploadedPaths), ";");
        }

        // Generate/update QR code
        $qrFileName = "$projectID-$safeDocName-QR.png";
        $qrFilePath = "$docFolder/$qrFileName";
        $qrContent = "uploads/$projectID/$safeDocName";
        QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 4);
        $qrPath = "uploads/$projectID/$safeDocName/$qrFileName";

        // Update document record
        $sqlUpdateDoc = "UPDATE document SET 
            PhysicalLocation = " . ($physicalChecked ? "'YES'" : "NULL") . ", 
            DigitalLocation = " . ($newDigitalLocation ? "'$newDigitalLocation'" : "NULL") . ",
            DocumentQR = '$qrPath',
            DocumentStatus = " . ($status !== null ? "'$status'" : "NULL") . "
            WHERE DocumentID = '$existingDocID'";

        $conn->query($sqlUpdateDoc);
    } else {
        // Insert new document record if physical checked or files uploaded
        if ($physicalChecked || count($uploadedPaths) > 0) {
            // Generate QR code
            $qrFileName = "$projectID-$safeDocName-QR.png";
            $qrFilePath = "$docFolder/$qrFileName";
            $qrContent = "uploads/$projectID/$safeDocName";
            QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 4);
            $qrPath = "uploads/$projectID/$safeDocName/$qrFileName";

            $sqlInsertDoc = "INSERT INTO document 
                (ProjectID, DocumentType, PhysicalLocation, DigitalLocation, DocumentQR, DocumentStatus) VALUES (
                '$projectID',
                '$docName',
                " . ($physicalChecked ? "'YES'" : "NULL") . ",
                " . (count($uploadedPaths) > 0 ? "'" . implode(";", $uploadedPaths) . "'" : "NULL") . ",
                '$qrPath',
                " . ($status !== null ? "'$status'" : "NULL") . ")";

            $conn->query($sqlInsertDoc);
        }
    }
}

file_put_contents('php://stderr', "POST data: " . print_r($_POST, true));
echo json_encode(['status' => 'error', 'message' => 'Something went wrong']);
exit;