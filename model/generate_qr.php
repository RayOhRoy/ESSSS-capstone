<?php
include 'phpqrcode/qrlib.php';
include '../server/server.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("❌ POST required");

// ----------------------
// Determine next Project ID (like your project insertion logic)
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
$uploadBase = __DIR__ . "/../uploads/";
$projectFolder = $uploadBase . $projectID;

if (!is_dir($projectFolder)) mkdir($projectFolder, 0777, true);

// ----------------------
// Generate Project QR
// ----------------------
$projQRFile = $projectFolder . '/project_qr.png';
$projQRContent = "uploads/$projectID"; // QR encodes the path relative to web root
QRcode::png($projQRContent, $projQRFile, QR_ECLEVEL_L, 4);
$projQRBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($projQRFile));

// ----------------------
// Generate Document QRs
// ----------------------
$allDocuments = [
    "Original Plan",
    "Lot Title",
    "Deed of Sale",
    "Tax Declaration",
    "Building Permit",
    "Authorization Letter",
    "Others"
];

$documentQRs = [];

foreach ($allDocuments as $docName) {
    $docKey = strtolower(preg_replace('/[^a-z0-9]/i', '_', $docName));
    $docFolder = $projectFolder . '/' . str_replace("_", "-", $docKey);

    // Create folder even if no digital file uploaded
    if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

    $docQRFile = $docFolder . '/doc_qr.png';
    $docQRContent = "uploads/$projectID/" . str_replace("_", "-", $docKey); // QR encodes relative path
    QRcode::png($docQRContent, $docQRFile, QR_ECLEVEL_L, 4);

    $documentQRs[$docName] = 'data:image/png;base64,' . base64_encode(file_get_contents($docQRFile));
}

// ----------------------
// Return JSON
// ----------------------
header('Content-Type: application/json');
echo json_encode([
    'projectQR' => $projQRBase64,
    'documentQRs' => $documentQRs,
    'projectID' => $projectID // useful for the front-end
]);
?>
