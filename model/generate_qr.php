<?php
include '../server/server.php'; // Database connection
include 'phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => '❌ POST required']));
}

// ----------------------
// Determine next Project ID (Physical Location style)
// ----------------------
$municipality = trim($_POST['municipality'] ?? 'OTH');

// Use first 3 alphabetic characters (uppercase) as prefix, default to "OTH"
if (!empty($municipality)) {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $municipality), 0, 3));
} else {
    $prefix = 'OTH';
}


// Get last project number by searching ProjectID with prefix
$sqlLastProj = "SELECT ProjectID FROM project WHERE ProjectID LIKE '$prefix-%' ORDER BY ProjectID DESC LIMIT 1";
$resLastProj = $conn->query($sqlLastProj);

if ($resLastProj && $resLastProj->num_rows > 0) {
    $lastProjectID = $resLastProj->fetch_assoc()['ProjectID'];
    // Extract last 3 digits as number (e.g. HAG-01-015-CON -> 15)
    preg_match('/(\d{2})-(\d{3})/', $lastProjectID, $matches);
    $block = isset($matches[1]) ? intval($matches[1]) : 1;
    $lastNum = isset($matches[2]) ? intval($matches[2]) : 0;
    $nextNum = $lastNum + 1;
} else {
    $block = 1;
    $nextNum = 1;
}

if ($nextNum > 100) {
    $block += 1;
    $nextNum = 1;
}

// Survey code (still used in ProjectID only)
$surveyType = $_POST['surveyType'] ?? 'GEN';
$surveyCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $surveyType), 0, 3)); // e.g. 'CON'

// ✅ Project ID includes survey code
$projectID = sprintf("%s-%02d-%03d-%s", $prefix, $block, $nextNum, $surveyCode); // e.g., CAL-01-001-CON

// ----------------------
// Folder path (without survey code)
// ----------------------
$folderBaseID = sprintf("%s-%02d-%03d", $prefix, $block, $nextNum); // e.g., CAL-01-001
$rootUploadsPath = realpath(__DIR__ . '/../') . '/uploads';

if (!is_dir($rootUploadsPath)) {
    if (!mkdir($rootUploadsPath, 0777, true)) {
        die(json_encode(['success' => false, 'message' => '❌ Failed to create uploads directory']));
    }
}

$projectFolder = $rootUploadsPath . '/' . $folderBaseID;

if (!is_dir($projectFolder)) {
    if (!mkdir($projectFolder, 0777, true)) {
        die(json_encode(['success' => false, 'message' => "❌ Failed to create project folder: $folderBaseID"]));
    }
}

// ----------------------
// Generate Project QR (based on folder path only)
// ----------------------
$projQRFile = $projectFolder . '/' . "{$projectID}-QR.png";
$projQRContent = "uploads/$folderBaseID"; // QR points to folder path only
QRcode::png($projQRContent, $projQRFile, QR_ECLEVEL_L, 4);

if (!file_exists($projQRFile)) {
    die(json_encode(['success' => false, 'message' => '❌ Failed to generate project QR']));
}

$projQRBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($projQRFile));

// ----------------------
// Generate Document QRs (folder & QR path without survey code)
// ----------------------
$physicalDocs = $_POST['physical_docs'] ?? [];
$digitalDocs = $_POST['digital_docs'] ?? [];

$docsToProcess = array_unique(array_merge($physicalDocs, $digitalDocs));

$documentQRs = [];

foreach ($docsToProcess as $docName) {
    $folderName = str_replace(' ', '-', $docName);
    $docFolder = $projectFolder . '/' . $folderName;

    if (!is_dir($docFolder)) {
        if (!mkdir($docFolder, 0777, true)) {
            die(json_encode(['success' => false, 'message' => "❌ Failed to create folder for document: $docName"]));
        }
    }

    $fileSafeDocName = $folderName;
    $docQRFile = $docFolder . '/' . "{$projectID}-{$fileSafeDocName}-QR.png";

    // ✅ QR content uses folderBaseID (no survey code)
    $docQRContent = "uploads/$folderBaseID/$folderName";

    QRcode::png($docQRContent, $docQRFile, QR_ECLEVEL_L, 4);

    if (!file_exists($docQRFile)) {
        die(json_encode(['success' => false, 'message' => "❌ Failed to generate QR for document: $docName"]));
    }

    $documentQRs[$docName] = 'data:image/png;base64,' . base64_encode(file_get_contents($docQRFile));
}

// ----------------------
// Return JSON to client
// ----------------------
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'projectQR' => $projQRBase64,
    'documentQRs' => $documentQRs,
    'projectID' => $projectID,
    'folderBaseID' => $folderBaseID // optional for debugging or reference
]);
