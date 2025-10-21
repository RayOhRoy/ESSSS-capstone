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
    if (strpos($typeClean, 'ASBUILTSURVEY') !== false) return 'ASB';
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

// — File & Folder Rename Logic —
$basePath = '../uploads';
$oldFolderPath = "$basePath/$baseProjectID"; // use folder without suffix
$newFolderPath = "$basePath/$baseProjectID"; // stays same base folder (no suffix change)

if (is_dir($oldFolderPath)) {
    // ✅ REGENERATE NEW PROJECT QR (folder stays same)
    $qrContent = "uploads/$baseProjectID";
    $newQRFile = "$newFolderPath/{$baseProjectID}-QR.png";

    // Remove any old QR file(s)
    foreach (glob("$newFolderPath/*-QR.png") as $oldQR) {
        unlink($oldQR);
    }

    QRcode::png($qrContent, $newQRFile, QR_ECLEVEL_L, 4);

    // Rename document QR codes inside subfolders (keep base ID only)
    $subfolders = scandir($newFolderPath);
    foreach ($subfolders as $folder) {
        if ($folder === '.' || $folder === '..') continue;
        $subfolderPath = "$newFolderPath/$folder";
        if (is_dir($subfolderPath)) {
            $formattedFolder = str_replace(' ', '-', $folder);
            $oldSubQR = glob("$subfolderPath/*-QR.png");
            foreach ($oldSubQR as $oldQRFile) {
                unlink($oldQRFile);
            }
            $newSubQR = "$subfolderPath/{$baseProjectID}-$formattedFolder-QR.png";
            $subQRContent = "uploads/$baseProjectID/$formattedFolder";
            QRcode::png($subQRContent, $newSubQR, QR_ECLEVEL_L, 4);
        }
    }
}

// — Update project record —
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

// — Update document table —
$sqlGetDocuments = "SELECT DocumentID, DocumentName, DocumentType FROM document WHERE ProjectID = '$projectID'";
$resDocuments = $conn->query($sqlGetDocuments);

if ($resDocuments && $resDocuments->num_rows > 0) {
    while ($doc = $resDocuments->fetch_assoc()) {
        $docID = $doc['DocumentID'];
        $docType = str_replace(' ', '-', $doc['DocumentType']);
        $newDocName = "$newProjectID-$docType";
        $newDocQRPath = "uploads/$baseProjectID/$docType/{$baseProjectID}-$docType-QR.png";

        $sqlUpdateDoc = "UPDATE document SET 
            ProjectID = '$newProjectID',
            DocumentName = '$newDocName',
            DocumentQR = '$newDocQRPath'
            WHERE DocumentID = '$docID'";

        if (!$conn->query($sqlUpdateDoc)) {
            die(json_encode(['status' => 'error', 'message' => 'Error updating document', 'error' => $conn->error]));
        }
    }
}

// Log modification
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

// ✅ Return response
echo json_encode([
    'status' => 'success',
    'message' => 'Project updated successfully.',
    'projectID' => $newProjectID
]);

$conn->close();
?>
