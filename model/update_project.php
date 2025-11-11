<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
session_start();

include '../server/server.php';
include 'phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'POST required']));
}

date_default_timezone_set("Asia/Manila");

$employeeID = $_SESSION['employeeid'] ?? null;
$timeNow = date('Y-m-d H:i:s');

// Project fields
$projectID = mysqli_real_escape_string($conn, $_POST['projectId']);
$lotNo = mysqli_real_escape_string($conn, $_POST['lotNumber']);
$fname = mysqli_real_escape_string($conn, $_POST['clientFirstName']);
$lname = mysqli_real_escape_string($conn, $_POST['clientLastName']);
$surveyType = mysqli_real_escape_string($conn, $_POST['surveyType']);
$startDate = mysqli_real_escape_string($conn, $_POST['surveyStartDate']);
$endDate = mysqli_real_escape_string($conn, $_POST['surveyEndDate']);
$agent = mysqli_real_escape_string($conn, $_POST['agent']);
$requestType = mysqli_real_escape_string($conn, $_POST['requestType']);
$projectStatus = mysqli_real_escape_string($conn, $_POST['projectStatus']);
$approval = isset($_POST['approval']) ? mysqli_real_escape_string($conn, $_POST['approval']) : null;

$province = mysqli_real_escape_string($conn, $_POST['province']);
$municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
$barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
$street = mysqli_real_escape_string($conn, $_POST['address']);

// Fetch AddressID
$resAddr = $conn->query("SELECT AddressID FROM project WHERE ProjectID='$projectID'");
if (!$resAddr || $resAddr->num_rows == 0) {
    die(json_encode(['status' => 'error', 'message' => 'Project not found']));
}
$addressID = $resAddr->fetch_assoc()['AddressID'];

// Update address
$conn->query("UPDATE address 
    SET Province='$province', Municipality='$municipality', Barangay='$barangay', Address='$street'
    WHERE AddressID='$addressID'");

// Update project
$conn->query("UPDATE project 
    SET LotNo='$lotNo', ClientFName='$fname', ClientLName='$lname', SurveyType='$surveyType',
        SurveyStartDate='$startDate', SurveyEndDate='$endDate', Agent='$agent', 
        RequestType='$requestType', ProjectStatus='$projectStatus', 
        Approval=" . ($approval !== null ? "'$approval'" : "NULL") . "
    WHERE ProjectID='$projectID'");

// --------------------
// Activity Log Helper
// --------------------
function generateActivityLogId($conn) {
    $res = $conn->query("SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $last = $res->fetch_assoc();
        $lastNum = intval(substr($last['ActivityLogID'], 4)) + 1;
    } else {
        $lastNum = 1;
    }
    return "ACT-" . str_pad($lastNum, 5, "0", STR_PAD_LEFT);
}

// Insert main MODIFIED log for project
$activityLogID = generateActivityLogId($conn);
$conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time)
              VALUES ('$activityLogID', '$projectID', 'MODIFIED', '$employeeID', '$timeNow')");

// --------------------
// Document Handling
// --------------------
$uploadBase = __DIR__ . '/../uploads/';
$baseProjectID = preg_replace('/-[A-Z]{3}$/', '', $projectID);
$projectFolder = $uploadBase . $baseProjectID;
if (!is_dir($projectFolder)) mkdir($projectFolder, 0777, true);

$updatedDocs = [];

foreach ($_POST as $key => $value) {
    if (!preg_match('/^(physical|digital)_(.+)$/', $key, $matches)) continue;

    $type = $matches[1]; // physical/digital
    $docKey = $matches[2];
    $docType = ucwords(str_replace("_", " ", $docKey));
    if (strcasecmp($docType, 'Cad File') === 0) $docType = 'CAD File';
    $safeDocName = str_replace(" ", "-", $docType);

    $physicalState = strtolower($_POST["physical_$docKey"] ?? 'off');
    $digitalValue = $_POST["digital_$docKey"] ?? '';
    $digitalFile = $_FILES["digital_$docKey"] ?? null;
    $hasNewDigital = $digitalFile && $digitalFile['error'] === UPLOAD_ERR_OK;

    // Existing document
    $resDoc = $conn->query("SELECT * FROM document WHERE ProjectID='$projectID' AND DocumentType='$docType'");
    $existingDoc = $resDoc && $resDoc->num_rows > 0 ? $resDoc->fetch_assoc() : null;

    // CASE 1: Delete doc (physical off + no digital)
    if ($physicalState === 'off' && empty($digitalValue) && !$hasNewDigital && $existingDoc) {
        if (!empty($existingDoc['DigitalLocation'])) {
            $absPath = str_replace('../', __DIR__ . '/../', $existingDoc['DigitalLocation']);
            if (file_exists($absPath)) unlink($absPath);
        }
        $conn->query("DELETE FROM document WHERE DocumentID='{$existingDoc['DocumentID']}'");

        $newActID = generateActivityLogId($conn);
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time)
                      VALUES ('$newActID', '$projectID', '{$existingDoc['DocumentID']}', 'DELETED', '$employeeID', '$timeNow')");
        continue;
    }

    // CASE 2: Physical off only
    if ($existingDoc && $physicalState === 'off' && (!empty($digitalValue) || $existingDoc['DigitalLocation'])) {
        $conn->query("UPDATE document SET DocumentStatus=NULL WHERE DocumentID='{$existingDoc['DocumentID']}'");

        $newActID = generateActivityLogId($conn);
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time)
                      VALUES ('$newActID', '$projectID', '{$existingDoc['DocumentID']}', 'MODIFIED', '$employeeID', '$timeNow')");
    }

    // CASE 3: Digital removed only
    if ($existingDoc && $physicalState === 'on' && empty($digitalValue) && !$hasNewDigital) {
        if (!empty($existingDoc['DigitalLocation'])) {
            $absPath = str_replace('../', __DIR__ . '/../', $existingDoc['DigitalLocation']);
            if (file_exists($absPath)) unlink($absPath);
        }
        $conn->query("UPDATE document SET DigitalLocation=NULL WHERE DocumentID='{$existingDoc['DocumentID']}'");

        $newActID = generateActivityLogId($conn);
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time)
                      VALUES ('$newActID', '$projectID', '{$existingDoc['DocumentID']}', 'MODIFIED', '$employeeID', '$timeNow')");
    }

    // CASE 4: Create new document
    if (!$existingDoc && ($physicalState === 'on' || $hasNewDigital)) {
        $resLastDoc = $conn->query("SELECT DocumentID FROM document ORDER BY DocumentID DESC LIMIT 1");
        $newDocNum = ($resLastDoc && $resLastDoc->num_rows > 0)
            ? str_pad(intval(substr($resLastDoc->fetch_assoc()['DocumentID'], 4)) + 1, 5, "0", STR_PAD_LEFT)
            : "00001";
        $documentID = "DOC-" . $newDocNum;
        $documentName = "$projectID-$safeDocName";
        $statusToInsert = ($physicalState === 'on') ? 'Stored' : null;

        $docFolder = "$projectFolder/$safeDocName";
        if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

        $qrFile = "{$baseProjectID}-{$safeDocName}-QR.png";
        $qrPath = "$docFolder/$qrFile";
        $qrData = "uploads/$baseProjectID/$safeDocName";
        QRcode::png($qrData, $qrPath, QR_ECLEVEL_L, 4);
        $qrLocation = "uploads/$baseProjectID/$safeDocName/$qrFile";

        $conn->query("INSERT INTO document (DocumentID, ProjectID, DocumentName, DocumentType, DocumentStatus, DocumentQR)
                      VALUES ('$documentID', '$projectID', '$documentName', '$docType', " . ($statusToInsert ? "'$statusToInsert'" : "NULL") . ", '$qrLocation')");

        $newActID = generateActivityLogId($conn);
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time)
                      VALUES ('$newActID', '$projectID', '$documentID', 'UPLOADED', '$employeeID', '$timeNow')");

        $existingDoc = ['DocumentID' => $documentID, 'DocumentQR' => $qrLocation];
    }

    // CASE 5: New digital upload
    if ($hasNewDigital) {
        $docFolder = "$projectFolder/$safeDocName";
        if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

        $ext = pathinfo($digitalFile['name'], PATHINFO_EXTENSION);
        $newName = "$safeDocName.$ext";
        $destPath = "$docFolder/$newName";

        if (move_uploaded_file($digitalFile['tmp_name'], $destPath)) {
            $digitalLocation = "../uploads/$baseProjectID/$safeDocName/$newName";
            $conn->query("UPDATE document SET DigitalLocation='$digitalLocation' 
                          WHERE ProjectID='$projectID' AND DocumentType='$docType'");

            $newActID = generateActivityLogId($conn);
            $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time)
                          VALUES ('$newActID', '$projectID', '{$existingDoc['DocumentID']}', 'MODIFIED', '$employeeID', '$timeNow')");
        }
    }

    // JSON response array
    $resUpdated = $conn->query("SELECT DocumentType, DigitalLocation, DocumentStatus, DocumentQR, DocumentID 
                                FROM document WHERE ProjectID='$projectID' AND DocumentType='$docType'");
    if ($resUpdated && $resUpdated->num_rows > 0) {
        $updatedDocs[] = $resUpdated->fetch_assoc();
    }
}

ob_clean();
echo json_encode(['status' => 'success', 'updatedDocs' => $updatedDocs]);
exit;
?>
