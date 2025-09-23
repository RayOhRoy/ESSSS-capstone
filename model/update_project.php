<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

session_start();
include '../server/server.php'; 
include 'phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'POST required']));
}

date_default_timezone_set("Asia/Manila");

$employeeId = $_SESSION['employeeid'] ?? null;
$projectId = $_POST['projectId'] ?? null;

if (!$projectId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing projectId']);
    exit;
}

// Check if project exists
$sqlCheckProject = "SELECT * FROM project WHERE ProjectID = '".mysqli_real_escape_string($conn, $projectId)."'";
$resCheck = $conn->query($sqlCheckProject);

if (!$resCheck || $resCheck->num_rows === 0) {
    die(json_encode(['status' => 'error', 'message' => 'Project not found: ' . htmlspecialchars($projectId)]));
}

// Address update
$province = mysqli_real_escape_string($conn, $_POST['province'] ?? '');
$municipality = mysqli_real_escape_string($conn, $_POST['municipality'] ?? '');
$barangay = mysqli_real_escape_string($conn, $_POST['barangay'] ?? '');
$street = mysqli_real_escape_string($conn, $_POST['street'] ?? '');

$currentProject = $resCheck->fetch_assoc();
$currentAddressId = $currentProject['AddressID'] ?? null;

if (!$currentAddressId) {
    die(json_encode(['status' => 'error', 'message' => 'Associated address not found']));
}

$sqlUpdateAddress = "UPDATE address SET Province='$province', Municipality='$municipality', Barangay='$barangay', Address='$street' WHERE AddressID='$currentAddressId'";
if ($conn->query($sqlUpdateAddress) !== TRUE) {
    die(json_encode(['status' => 'error', 'message' => 'Error updating address', 'error' => $conn->error]));
}

// Project update
$lotNo = mysqli_real_escape_string($conn, $_POST['lot_no'] ?? '');
$fname = mysqli_real_escape_string($conn, $_POST['client_name'] ?? '');
$lname = mysqli_real_escape_string($conn, $_POST['last_name'] ?? '');
$surveyType = mysqli_real_escape_string($conn, $_POST['survey_type'] ?? '');
$startDate = mysqli_real_escape_string($conn, $_POST['survey_start'] ?? '');
$endDate = mysqli_real_escape_string($conn, $_POST['survey_end'] ?? '');
$agent = mysqli_real_escape_string($conn, $_POST['agent'] ?? '');
$requestType = mysqli_real_escape_string($conn, $_POST['requestType'] ?? '');
$projectStatus = mysqli_real_escape_string($conn, $_POST['projectStatus'] ?? '');
$approval = isset($_POST['approval']) ? mysqli_real_escape_string($conn, $_POST['approval']) : null;

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
    WHERE ProjectID = '$projectId'";

if ($conn->query($sqlUpdateProject) !== TRUE) {
    die(json_encode(['status' => 'error', 'message' => 'Error updating project', 'error' => $conn->error]));
}

// Log activity for project modification
$sqlLastActivity = "SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1";
$resLastActivity = $conn->query($sqlLastActivity);
$newActivityNum = ($resLastActivity && $resLastActivity->num_rows > 0) 
                  ? str_pad(intval(substr($resLastActivity->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
                  : "001";
$activityLogId = "ACT-" . $newActivityNum;
$timeNow = date('Y-m-d H:i:s');

$sqlActivityLog = "INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time) 
                   VALUES ('$activityLogId','$projectId','MODIFIED','$employeeId', '$timeNow')";
$conn->query($sqlActivityLog);

// Handle document removal
foreach ($_POST as $key => $value) {
    if (strpos($key, 'remove_file_') === 0 && $value == '1') {
        $docId = substr($key, strlen('remove_file_'));
        if ($docId) {
            $sqlDoc = "SELECT * FROM document WHERE DocumentID = '".mysqli_real_escape_string($conn, $docId)."' AND ProjectID = '".mysqli_real_escape_string($conn, $projectId)."'";
            $resDoc = $conn->query($sqlDoc);
            if ($resDoc && $resDoc->num_rows > 0) {
                $doc = $resDoc->fetch_assoc();
                $digitalLoc = $doc['DigitalLocation'];
                if ($digitalLoc) {
                    $files = explode(";", $digitalLoc);
                    foreach ($files as $filePath) {
                        $fullPath = __DIR__ . '/../' . $filePath;
                        if (file_exists($fullPath)) unlink($fullPath);
                    }
                }

                $conn->query("DELETE FROM document WHERE DocumentID = '".mysqli_real_escape_string($conn, $docId)."'");

                // Log document deletion
                $resLastActDoc = $conn->query("SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1");
                $newActNumDoc = ($resLastActDoc && $resLastActDoc->num_rows > 0)
                    ? str_pad(intval(substr($resLastActDoc->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
                    : "001";
                $activityLogIdDoc = "ACT-" . $newActNumDoc;
                $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time) 
                              VALUES ('$activityLogIdDoc','".mysqli_real_escape_string($conn, $projectId)."','".mysqli_real_escape_string($conn, $docId)."','DELETED','".mysqli_real_escape_string($conn, $employeeId)."', '$timeNow')");
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
$projectFolder = $uploadBase . $projectId;
if (!is_dir($projectFolder)) mkdir($projectFolder, 0777, true);

foreach ($documentKeys as $docKey) {
    $docName = ucwords(str_replace("_", " ", $docKey));
    $safeDocName = str_replace(" ", "-", $docName);

    $physicalChecked = isset($_POST["physical_$docKey"]);
    $status = isset($_POST["status_$docKey"]) ? mysqli_real_escape_string($conn, $_POST["status_$docKey"]) : null;

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

    if (!$physicalChecked && count($uploadedFiles) === 0) continue;

    $docFolder = "$projectFolder/$safeDocName";
    if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

    $uploadedPaths = [];
    foreach ($uploadedFiles as $f) {
        $dest = "$docFolder/" . $f['newFileName'];
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            $uploadedPaths[] = "uploads/$projectId/$safeDocName/" . $f['newFileName'];
        }
    }

    // Generate QR code for this document folder
    $qrFileName = "$projectId-$safeDocName-QR.png";
    $qrFilePath = "$docFolder/$qrFileName";
    $qrContent = "uploads/$projectId/$safeDocName";
    QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 4);
    $qrPath = "uploads/$projectId/$safeDocName/$qrFileName";

    // Check if document already exists by DocumentName = ProjectID-SafeDocName
    $documentName = "$projectId-$safeDocName";
    $sqlCheckDoc = "SELECT DocumentID, DigitalLocation FROM document WHERE DocumentName = '".mysqli_real_escape_string($conn, $documentName)."' LIMIT 1";
    $resDoc = $conn->query($sqlCheckDoc);

    if ($resDoc && $resDoc->num_rows > 0) {
        // Document exists — update
        $docRow = $resDoc->fetch_assoc();
        $existingDocId = $docRow['DocumentID'];
        $existingFiles = $docRow['DigitalLocation'];

        // Append new files if uploaded
        $newDigitalLocation = $existingFiles;
        if (count($uploadedPaths) > 0) {
            $parts = array_filter(explode(";", $existingFiles));
            $parts = array_merge($parts, $uploadedPaths);
            $newDigitalLocation = implode(";", $parts);
        }

        $sqlUpdateDoc = "UPDATE document SET 
            PhysicalLocation = " . ($physicalChecked ? "'YES'" : "NULL") . ", 
            DigitalLocation = " . ($newDigitalLocation ? "'".mysqli_real_escape_string($conn, $newDigitalLocation)."'" : "NULL") . ",
            DocumentQR = '".mysqli_real_escape_string($conn, $qrPath)."',
            DocumentStatus = " . ($status !== null ? "'$status'" : "NULL") . "
            WHERE DocumentID = '".mysqli_real_escape_string($conn, $existingDocId)."'";

        if (!$conn->query($sqlUpdateDoc)) {
            die(json_encode(['status' => 'error', 'message' => 'Error updating document: '.$conn->error]));
        }

        // Log document UPDATED activity
        $resLastActDoc = $conn->query("SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1");
        $newActNumDoc = ($resLastActDoc && $resLastActDoc->num_rows > 0)
            ? str_pad(intval(substr($resLastActDoc->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
            : "001";
        $activityLogIdDoc = "ACT-" . $newActNumDoc;
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time) 
                      VALUES ('".$activityLogIdDoc."','".mysqli_real_escape_string($conn, $projectId)."','".mysqli_real_escape_string($conn, $existingDocId)."','UPDATED','".mysqli_real_escape_string($conn, $employeeId)."', '$timeNow')");

    } else {
        // Document doesn't exist — insert new with unique DocumentID
        // Generate new DocumentID (e.g. DOC-00001)
        $sqlLastDocNum = "SELECT DocumentID FROM document ORDER BY DocumentID DESC LIMIT 1";
        $resLastDocNum = $conn->query($sqlLastDocNum);
        $newDocNum = "00001"; // default
        if ($resLastDocNum && $resLastDocNum->num_rows > 0) {
            $lastDocId = $resLastDocNum->fetch_assoc()['DocumentID'];
            $lastNum = intval(substr($lastDocId, 4));
            $newDocNum = str_pad($lastNum + 1, 5, "0", STR_PAD_LEFT);
        }
        $newDocumentID = "DOC-" . $newDocNum;

        $sqlInsertDoc = "INSERT INTO document 
            (DocumentID, DocumentName, ProjectID, DocumentType, PhysicalLocation, DigitalLocation, DocumentQR, DocumentStatus) VALUES (
            '".mysqli_real_escape_string($conn, $newDocumentID)."',
            '".mysqli_real_escape_string($conn, $documentName)."',
            '".mysqli_real_escape_string($conn, $projectId)."',
            '".mysqli_real_escape_string($conn, $docName)."',
            ".($physicalChecked ? "'YES'" : "NULL").",
            ".(count($uploadedPaths) > 0 ? "'".mysqli_real_escape_string($conn, implode(";", $uploadedPaths))."'" : "NULL").",
            '".mysqli_real_escape_string($conn, $qrPath)."',
            ".($status !== null ? "'$status'" : "NULL")."
        )";

        if (!$conn->query($sqlInsertDoc)) {
            die(json_encode(['status' => 'error', 'message' => 'Error inserting document: '.$conn->error]));
        }

        // Log document UPLOADED activity
        $resLastActDoc = $conn->query("SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1");
        $newActNumDoc = ($resLastActDoc && $resLastActDoc->num_rows > 0)
            ? str_pad(intval(substr($resLastActDoc->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
            : "001";
        $activityLogIdDoc = "ACT-" . $newActNumDoc;
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time) 
                      VALUES ('".$activityLogIdDoc."','".mysqli_real_escape_string($conn, $projectId)."','".mysqli_real_escape_string($conn, $newDocumentID)."','UPLOADED','".mysqli_real_escape_string($conn, $employeeId)."', '$timeNow')");
    }
}

file_put_contents('php://stderr', "POST data: " . print_r($_POST, true));
echo json_encode(['status' => 'success', 'message' => 'Project updated successfully']);
exit;
?>
