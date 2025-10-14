<?php
session_start();
include '../server/server.php';
include 'phpqrcode/qrlib.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    die(json_encode(['status' => 'error', 'message' => 'POST required']));

date_default_timezone_set("Asia/Manila");

$employeeID = $_SESSION['employeeid'] ?? null;

// — Address Handling —
$province = mysqli_real_escape_string($conn, $_POST['province']);
$municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
$barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
$street = mysqli_real_escape_string($conn, $_POST['street']);

$prefix = ($municipality === 'Hagonoy') ? 'HAG' : (($municipality === 'Calumpit') ? 'CAL' : 'OTH');

// Generate new AddressID
$sqlLast = "SELECT AddressID FROM address WHERE AddressID LIKE '$prefix-%' ORDER BY AddressID DESC LIMIT 1";
$resLast = $conn->query($sqlLast);
$newNum = ($resLast && $resLast->num_rows > 0)
    ? str_pad(intval(substr($resLast->fetch_assoc()['AddressID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
    : "001";
$addressID = "$prefix-$newNum";

// Insert into address
$sqlAddress = "INSERT INTO address (AddressID, Province, Municipality, Barangay, Address) 
               VALUES ('$addressID','$province','$municipality','$barangay','$street')";
if ($conn->query($sqlAddress) !== TRUE) {
    die(json_encode(['status' => 'error', 'message' => 'Error inserting address', 'error' => $conn->error]));
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

// Generate Survey Code
$surveyCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $surveyType), 0, 3)); // e.g., 'REL'

// Generate Project ID
$sqlLastProject = "SELECT ProjectID FROM project WHERE ProjectID LIKE '$prefix-%' ORDER BY ProjectID DESC LIMIT 1";
$resLastProject = $conn->query($sqlLastProject);
$lastNumber = ($resLastProject && $resLastProject->num_rows > 0)
    ? intval(substr($resLastProject->fetch_assoc()['ProjectID'], -7, 3)) + 1
    : 1;

$block = intval(($lastNumber - 1) / 100) + 1;
$slot = $lastNumber;

$projectID = sprintf("%s-%02d-%03d-%s", $prefix, $block, $slot, $surveyCode);

$uploadBase = __DIR__ . '/../uploads/';
$projectFolder = $uploadBase . $projectID;
if (!is_dir($projectFolder))
    mkdir($projectFolder, 0777, true);

$sqlProject = "INSERT INTO project 
    (ProjectID, LotNo, ClientFName, ClientLName, SurveyType, SurveyStartDate, SurveyEndDate, Agent, RequestType, Approval, AddressID, DigitalLocation, ProjectStatus, StorageStatus) 
    VALUES ('$projectID','$lotNo','$fname','$lname','$surveyType','$startDate','$endDate','$agent','$requestType',"
    . ($approval !== null ? "'$approval'" : "NULL") .
    ",'$addressID','uploads/$projectID','$projectStatus', 'Stored')";
if ($conn->query($sqlProject) !== TRUE) {
    die(json_encode(['status' => 'error', 'message' => 'Error inserting project', 'error' => $conn->error]));
}

// Log project creation
$sqlLastActivity = "SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1";
$resLastActivity = $conn->query($sqlLastActivity);
$newActivityNum = ($resLastActivity && $resLastActivity->num_rows > 0)
    ? str_pad(intval(substr($resLastActivity->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
    : "001";
$activityLogID = "ACT-" . $newActivityNum;

$timeNow = date('Y-m-d H:i:s');

$sqlActivityLog = "INSERT INTO activity_log (ActivityLogID, ProjectID, Status, EmployeeID, Time) 
                   VALUES ('$activityLogID','$projectID','CREATED','$employeeID', '$timeNow')";
$conn->query($sqlActivityLog);

// Generate project-level QR
$projQRFileName = "$projectID-QR.png";
$projQRFile = "$projectFolder/$projQRFileName";
$projQRContent = "uploads/$projectID";
QRcode::png($projQRContent, $projQRFile, QR_ECLEVEL_L, 4);
$projQRPath = "uploads/$projectID/$projQRFileName";
$conn->query("UPDATE project SET projectqr='$projQRPath' WHERE ProjectID='$projectID'");

// — Document Handling —
$documentKeys = [];
foreach ($_FILES as $inputName => $fileGroup) {
    if (preg_match('/^digital_(.+)$/', $inputName, $matches)) {
        $documentKeys[] = $matches[1];
    }
}

foreach ($documentKeys as $docKey) {
    $docName = ucwords(str_replace("_", " ", $docKey));

    // Force correct capitalization for CAD folder only
    if (strcasecmp(trim($docName), 'Cad File') === 0) {
        $docName = 'CAD File';
    }
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
            if ($files['error'][$i] !== UPLOAD_ERR_OK)
                continue;
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
    if (!is_dir($docFolder))
        mkdir($docFolder, 0777, true);

    $uploadedPaths = [];
    foreach ($uploadedFiles as $f) {
        $dest = "$docFolder/" . $f['newFileName'];
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            $uploadedPaths[] = "../uploads/$projectID/$safeDocName/" . $f['newFileName'];
        }
    }

    $filesString = count($uploadedPaths) ? implode(";", $uploadedPaths) : NULL;

    // Generate document QR
    $docQRFileName = "$projectID-$safeDocName-QR.png";
    $docQRFile = "$docFolder/$docQRFileName";
    $docQRContent = "uploads/$projectID/$safeDocName";
    QRcode::png($docQRContent, $docQRFile, QR_ECLEVEL_L, 4);
    $docQRPath = "uploads/$projectID/$safeDocName/$docQRFileName";

    // Generate DocumentID
    $sqlLastDoc = "SELECT DocumentID FROM document ORDER BY DocumentID DESC LIMIT 1";
    $resLastDoc = $conn->query($sqlLastDoc);
    $newDocNum = ($resLastDoc && $resLastDoc->num_rows > 0)
        ? str_pad(intval(substr($resLastDoc->fetch_assoc()['DocumentID'], 4)) + 1, 5, "0", STR_PAD_LEFT)
        : "00001";
    $documentID = "DOC-" . $newDocNum;
    $documentName = "$projectID-$safeDocName";

    $sqlDoc = "INSERT INTO document 
        (DocumentID, DocumentName, ProjectID, DocumentType, DigitalLocation, DocumentStatus, DocumentQR)
        VALUES ('$documentID','$documentName','$projectID','$docName'," .
        ($filesString !== NULL ? "'" . mysqli_real_escape_string($conn, $filesString) . "'" : "NULL") . "," .
        ($status !== null ? "'$status'" : "NULL") . ",'$docQRPath')";
    if ($conn->query($sqlDoc) === TRUE) {
        $sqlLastAct = "SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1";
        $resLastAct = $conn->query($sqlLastAct);
        $newActNum = ($resLastAct && $resLastAct->num_rows > 0)
            ? str_pad(intval(substr($resLastAct->fetch_assoc()['ActivityLogID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
            : "001";
        $activityLogIDDoc = "ACT-" . $newActNum;
        $statusDoc = 'UPLOADED';
        $timeNow = date('Y-m-d H:i:s');
        $sqlActDoc = "INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time) 
                      VALUES ('$activityLogIDDoc','$projectID','$documentID','$statusDoc','$employeeID', '$timeNow')";
        $conn->query($sqlActDoc);
    }
}

echo json_encode(['status' => 'success', 'message' => 'Project uploaded successfully.', 'projectID' => $projectID]);
$conn->close();
?>