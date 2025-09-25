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
$projectID = $_POST['projectID'] ?? '';
if (!$projectID) {
    die(json_encode(['status' => 'error', 'message' => 'Project ID missing']));
}

// Sanitize helper
function safe_escape(mysqli $conn, $key) {
    $val = $_POST[$key] ?? '';
    if (!is_string($val)) $val = '';
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
$surveyType = safe_escape($conn, 'surveyType'); // ✅ This must not be empty!
$agent = safe_escape($conn, 'agent');
$projectStatus = safe_escape($conn, 'projectStatus');
$startDate = safe_escape($conn, 'surveyStartDate');
$endDate = safe_escape($conn, 'surveyEndDate');
$requestType = safe_escape($conn, 'requestType');
$approvalType = safe_escape($conn, 'approvalType');
$approval = isset($_POST['approval']) ? mysqli_real_escape_string($conn, $_POST['approval']) : null;

// ✅ Compute new project ID based on survey type suffix
function getSurveyCode($type) {
    $map = [
        'AS-BUILT' => 'ASB',
        'SKETCH PLAN / VICINITY MAP' => 'SKE',
        'TOPOGRAPHIC SURVEY' => 'TOP',
        'SUBDIVISION PLAN' => 'SUB',
        'LOCATION PLAN' => 'LOC',
        // Add more mappings as needed
    ];
    $typeUpper = strtoupper(trim($type));
    return $map[$typeUpper] ?? strtoupper(substr($typeUpper, 0, 3));
}

$surveyCode = getSurveyCode($surveyType);

// Replace the suffix in projectID
// Example: HAG-01-001-SKE -> HAG-01-001-TOP
$parts = explode('-', $projectID);
if (count($parts) === 4) {
    $parts[3] = $surveyCode;
    $newProjectID = implode('-', $parts);
} else {
    $newProjectID = $projectID; // Fallback
}

// 🚨 Update project ID and SurveyType
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
    ProjectStatus = '$projectStatus'
    WHERE ProjectID = '$projectID'"; // Old ID in WHERE clause

if (!$conn->query($sqlUpdateProject)) {
    die(json_encode(['status' => 'error', 'message' => 'Error updating project', 'error' => $conn->error]));
}

// Log activity - MODIFIED
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

// ✅ Return success response with new ProjectID
echo json_encode([
    'status' => 'success',
    'message' => 'Project updated successfully.',
    'projectID' => $newProjectID
]);

$conn->close();
