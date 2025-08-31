<?php
include '../server/server.php'; // Database connection
include 'phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => '❌ POST required']));
}

// ----------------------
// Determine next Project ID
// ----------------------
$municipality = $_POST['municipality'] ?? 'OTH';
$prefix = ($municipality === 'Hagonoy') ? 'HAG' : (($municipality === 'Calumpit') ? 'CAL' : 'OTH');

$sqlLastProj = "SELECT ProjectID FROM project WHERE ProjectID LIKE '$prefix-%' ORDER BY ProjectID DESC LIMIT 1";
$resLastProj = $conn->query($sqlLastProj);
$newNumP = ($resLastProj && $resLastProj->num_rows > 0) 
           ? str_pad(intval(substr($resLastProj->fetch_assoc()['ProjectID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
           : "001";

$projectID = "$prefix-$newNumP";

// ----------------------
// Prepare folders
// ----------------------
$rootUploadsPath = realpath(__DIR__ . '/../') . '/uploads';

if (!is_dir($rootUploadsPath)) {
    if (!mkdir($rootUploadsPath, 0777, true)) {
        die(json_encode(['success' => false, 'message' => '❌ Failed to create uploads directory']));
    }
}

$projectFolder = $rootUploadsPath . '/' . $projectID;

if (!is_dir($projectFolder)) {
    if (!mkdir($projectFolder, 0777, true)) {
        die(json_encode(['success' => false, 'message' => "❌ Failed to create project folder: $projectID"]));
    }
}

// ----------------------
// Generate Project QR
// ----------------------
$projQRFile = $projectFolder . '/' . "{$projectID}-QR.png"; // ✅ new filename
$projQRContent = $projectID; // e.g. "HAG-001"
QRcode::png($projQRContent, $projQRFile, QR_ECLEVEL_L, 4);

if (!file_exists($projQRFile)) {
    die(json_encode(['success' => false, 'message' => '❌ Failed to generate project QR']));
}

$projQRBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($projQRFile));

// ----------------------
// Get submitted documents info from POST
// Expect arrays of document keys where physical or digital files exist
// e.g., physical_docs[]=Original Plan&digital_docs[]=Deed of Sale
// ----------------------
$physicalDocs = $_POST['physical_docs'] ?? [];
$digitalDocs = $_POST['digital_docs'] ?? [];

// Merge and unique docs that require QR generation
$docsToProcess = array_unique(array_merge($physicalDocs, $digitalDocs));

// ----------------------
// Generate Document QRs only for selected docs
// ----------------------
$documentQRs = [];

foreach ($docsToProcess as $docName) {
    // Sanitize docName and create folder name as before
    $folderName = str_replace(' ', '-', $docName); 

    $docFolder = $projectFolder . '/' . $folderName;

    if (!is_dir($docFolder)) {
        if (!mkdir($docFolder, 0777, true)) {
            die(json_encode(['success' => false, 'message' => "❌ Failed to create folder for document: $docName"]));
        }
    }

    $fileSafeDocName = $folderName;
    $docQRFile = $docFolder . '/' . "{$projectID}-{$fileSafeDocName}-QR.png";

    $docQRContent = "$projectID/$folderName"; 
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
    'projectID' => $projectID
]);
