<?php
ob_start();          // start output buffering
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
session_start();
include '../server/server.php';
include 'phpqrcode/qrlib.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    die(json_encode(['status' => 'error', 'message' => 'POST required']));

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
$sqlAddr = "SELECT AddressID FROM project WHERE ProjectID = '$projectID'";
$resAddr = $conn->query($sqlAddr);
if (!$resAddr || $resAddr->num_rows == 0)
    die(json_encode(['status' => 'error', 'message' => 'Project not found']));
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

// Create main activity log
$resLastAct = $conn->query("SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1");
$newActNum = ($resLastAct && $resLastAct->num_rows > 0)
    ? str_pad(intval(substr($resLastAct->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
    : "001";
$activityLogID = "ACT-" . $newActNum;
$conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time)
              VALUES ('$activityLogID', '$projectID', 'MODIFIED', '$employeeID', '$timeNow')");

// Base upload folder
$uploadBase = __DIR__ . '/../uploads/';
$baseProjectID = preg_replace('/-[A-Z]{3}$/', '', $projectID);
$projectFolder = $uploadBase . $baseProjectID;
if (!is_dir($projectFolder))
    mkdir($projectFolder, 0777, true);

$updatedDocs = [];

// --------------------------
// Document handling
// --------------------------
foreach ($_POST as $key => $value) {
    if (!preg_match('/^(physical|digital)_(.+)$/', $key, $matches)) continue;

    $type = $matches[1]; // "physical" or "digital"
    $docKey = $matches[2]; // e.g., "cad_file"
    $docType = ucwords(str_replace("_", " ", $docKey));
    if (strcasecmp($docType, 'Cad File') === 0) $docType = 'CAD File';
    $safeDocName = str_replace(" ", "-", $docType);

    $physicalChecked = isset($_POST["physical_$docKey"]);
    $digitalFile = $_FILES["digital_$docKey"] ?? null;
    $hasDigital = $digitalFile && $digitalFile['error'] === UPLOAD_ERR_OK;

    // Fetch existing document
    $resDoc = $conn->query("SELECT DocumentID, DigitalLocation, DocumentStatus FROM document WHERE ProjectID='$projectID' AND DocumentType='$docType'");
    $existingDoc = $resDoc && $resDoc->num_rows > 0 ? $resDoc->fetch_assoc() : null;

    // Delete row if both physical and digital empty
    if (!$physicalChecked && !$hasDigital && $existingDoc) {
        $conn->query("DELETE FROM document WHERE DocumentID='{$existingDoc['DocumentID']}'");
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time)
                      VALUES ('$activityLogID', '$projectID', 'DELETED', '$employeeID', '$timeNow')");
        continue;
    }

    // Update DocumentStatus to NULL if physical unchecked
    if ($existingDoc && !$physicalChecked) {
        $conn->query("UPDATE document 
                      SET DocumentStatus=NULL 
                      WHERE DocumentID='{$existingDoc['DocumentID']}'");
        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time)
                      VALUES ('$activityLogID', '$projectID', 'STATUS CLEARED', '$employeeID', '$timeNow')");
    }

    // Create new document if doesn't exist
    if (!$existingDoc && ($physicalChecked || $hasDigital)) {
        $resLastDoc = $conn->query("SELECT DocumentID FROM document ORDER BY DocumentID DESC LIMIT 1");
        $newDocNum = ($resLastDoc && $resLastDoc->num_rows > 0)
            ? str_pad(intval(substr($resLastDoc->fetch_assoc()['DocumentID'], 4)) + 1, 5, "0", STR_PAD_LEFT)
            : "00001";
        $documentID = "DOC-" . $newDocNum;
        $documentName = "$projectID-$safeDocName";
        $statusToInsert = $physicalChecked ? 'Stored' : null;

        $docFolder = "$projectFolder/$safeDocName";
        if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

        // QR file
        $qrFile = "{$baseProjectID}-{$safeDocName}-QR.png";
        $qrPath = "$docFolder/$qrFile";
        $qrData = "uploads/$baseProjectID/$safeDocName";
        QRcode::png($qrData, $qrPath, QR_ECLEVEL_L, 4);

        $qrLocation = "uploads/$baseProjectID/$safeDocName/$qrFile";

        $conn->query("INSERT INTO document (DocumentID, ProjectID, DocumentName, DocumentType, DocumentStatus, DocumentQR)
                      VALUES ('$documentID', '$projectID', '$documentName', '$docType', " . ($statusToInsert !== null ? "'$statusToInsert'" : "NULL") . ", '$qrLocation')");

        $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time)
                      VALUES ('$activityLogID', '$projectID', 'UPLOADED', '$employeeID', '$timeNow')");
        $existingDoc = ['DocumentID' => $documentID, 'DocumentQR' => $qrLocation]; // for later
    }

    // Handle digital upload
    if ($hasDigital) {
        $docFolder = "$projectFolder/$safeDocName";
        if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

        $ext = pathinfo($digitalFile['name'], PATHINFO_EXTENSION);
        $newName = "$safeDocName.$ext";
        $destPath = "$docFolder/$newName";

        if (move_uploaded_file($digitalFile['tmp_name'], $destPath)) {
            $digitalLocation = "../uploads/$baseProjectID/$safeDocName/$newName";
            $conn->query("UPDATE document SET DigitalLocation='$digitalLocation' WHERE ProjectID='$projectID' AND DocumentType='$docType'");

            if ($existingDoc) {
                $conn->query("INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time)
                              VALUES ('$activityLogID', '$projectID', 'UPLOADED', '$employeeID', '$timeNow')");
            }
        }
    }

    $updatedDocs[] = [
        'DocumentType' => $docType,
        'DigitalLocation' => $digitalLocation ?? $existingDoc['DigitalLocation'] ?? null,
        'DocumentStatus' => $physicalChecked ? 'Stored' : null,
        'DocumentQR' => $existingDoc['DocumentQR'] ?? null
    ];
}

ob_clean();
echo json_encode(['status'=>'success', 'message'=>'Test']);
exit;