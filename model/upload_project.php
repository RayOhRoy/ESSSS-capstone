<?php
include '../server/server.php'; 
include 'phpqrcode/qrlib.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("❌ POST required");

// ================= ADDRESS ==================
$province     = mysqli_real_escape_string($conn, $_POST['province']);
$municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
$barangay     = mysqli_real_escape_string($conn, $_POST['barangay']);
$street       = mysqli_real_escape_string($conn, $_POST['street']);

$prefix = ($municipality === 'Hagonoy') ? 'HAG' : (($municipality === 'Calumpit') ? 'CAL' : 'OTH');

// Generate Address ID
$sqlLast = "SELECT AddressID FROM address WHERE AddressID LIKE '$prefix-%' ORDER BY AddressID DESC LIMIT 1";
$resLast = $conn->query($sqlLast);
$newNum = ($resLast && $resLast->num_rows > 0) 
          ? str_pad(intval(substr($resLast->fetch_assoc()['AddressID'], 4)) + 1, 3, "0", STR_PAD_LEFT) 
          : "001";
$addressID = "$prefix-$newNum";

// Insert address
$sqlAddress = "INSERT INTO address (AddressID, Province, Municipality, Barangay, Address) 
               VALUES ('$addressID','$province','$municipality','$barangay','$street')";
if ($conn->query($sqlAddress) !== TRUE) die("❌ Error inserting address: " . $conn->error);

// ================= PROJECT ==================
$lotNo      = mysqli_real_escape_string($conn, $_POST['lot_no']);
$fname      = mysqli_real_escape_string($conn, $_POST['client_name']);
$lname      = mysqli_real_escape_string($conn, $_POST['last_name']);
$surveyType = mysqli_real_escape_string($conn, $_POST['survey_type']);
$startDate  = mysqli_real_escape_string($conn, $_POST['survey_start']);
$endDate    = mysqli_real_escape_string($conn, $_POST['survey_end']);
$agent      = mysqli_real_escape_string($conn, $_POST['agent']);
$requestType= mysqli_real_escape_string($conn, $_POST['requestType']);
$approval   = isset($_POST['approval']) ? mysqli_real_escape_string($conn, $_POST['approval']) : null;

// Generate Project ID
$sqlLastProj = "SELECT ProjectID FROM project WHERE ProjectID LIKE '$prefix-%' ORDER BY ProjectID DESC LIMIT 1";
$resLastProj = $conn->query($sqlLastProj);
$newNumP = ($resLastProj && $resLastProj->num_rows > 0) 
           ? str_pad(intval(substr($resLastProj->fetch_assoc()['ProjectID'], 4)) + 1, 3, "0", STR_PAD_LEFT)
           : "001";
$projectID = "$prefix-$newNumP";

// Physical location
$numericPart = intval(substr($projectID, 4)); 
$block = intval(($numericPart - 1) / 50) + 1; 
$slot = $numericPart; 
$physicalLocation = sprintf("%s-%02d-%03d", $prefix, $block, $slot);

// Upload folder
$uploadBase = __DIR__ . "/../uploads/";
$projectFolder = $uploadBase . $projectID;
if (!is_dir($projectFolder)) mkdir($projectFolder, 0777, true);

// Insert project
$sqlProject = "INSERT INTO project 
    (ProjectID, LotNo, ClientFName, ClientLName, SurveyType, SurveyStartDate, SurveyEndDate, Agent, RequestType, Approval, AddressID, PhysicalLocation, DigitalLocation) 
    VALUES 
    ('$projectID','$lotNo','$fname','$lname','$surveyType','$startDate','$endDate','$agent','$requestType',"
    . ($approval !== null ? "'$approval'" : "NULL") . 
    ",'$addressID','$physicalLocation','" . mysqli_real_escape_string($conn, $projectFolder) . "')";
if ($conn->query($sqlProject) !== TRUE) die("❌ Error inserting project: " . $conn->error);

// Generate Project QR with new naming convention
$projQRFileName = $projectID . "-QR.png";  // e.g., HAG-001-QR.png
$projQRFile = $projectFolder . "/" . $projQRFileName; // full path ../uploads/HAG-001/HAG-001-QR.png
$projQRContent = "uploads/$projectID"; // QR encodes relative web path
QRcode::png($projQRContent, $projQRFile, QR_ECLEVEL_L, 4);

// Save relative QR path in DB
$projQRPath = "uploads/$projectID/$projQRFileName";
$conn->query("UPDATE project SET projectqr='$projQRPath' WHERE ProjectID='$projectID'");

$docs = [
    "original_plan"         => "Original Plan",
    "lot_title"             => "Lot Title",
    "deed_of_sale"          => "Deed of Sale",
    "tax_declaration"       => "Tax Declaration",
    "building_permit"       => "Building Permit",
    "authorization_letter" => "Authorization Letter",
    "others"                => "Others",
];

foreach ($docs as $docKey => $docName) {
    $safeDocName = str_replace("_", " ", $docKey);   // "Original Plan"
    $safeDocName = ucwords(strtolower($safeDocName)); // "Original Plan"
    $safeDocName = str_replace(" ", "-", $safeDocName); // "Original-Plan"

    $status = isset($_POST["status_$docKey"]) ? mysqli_real_escape_string($conn, $_POST["status_$docKey"]) : null;
    $physicalChecked = isset($_POST["physical_$docKey"]) ? 1 : 0;

    $uploadedFiles = [];
    if (isset($_FILES["digital_{$docKey}"]) && !empty($_FILES["digital_{$docKey}"]['name'][0])) {
        $files = $_FILES["digital_{$docKey}"];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            // We only prepare the destination path here, folder not yet created.
            $uploadedFiles[] = [
                'tmp_name' => $files['tmp_name'][$i],
                'newFileName' => $safeDocName . "-" . ($i + 1) . "." . $ext
            ];
        }
    }

    // Only proceed if physical doc is checked OR there are digital uploads
    if ($physicalChecked || count($uploadedFiles) > 0) {
        // Now create the folder since it's needed
        $docFolder = $projectFolder . "/" . $safeDocName;
        if (!is_dir($docFolder)) mkdir($docFolder, 0777, true);

        // Move uploaded files now that folder exists
        $uploadedPaths = [];
        foreach ($uploadedFiles as $file) {
            $destPath = $docFolder . "/" . $file['newFileName'];
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $uploadedPaths[] = "../uploads/$projectID/$safeDocName/" . $file['newFileName'];
            }
        }

        $filesString = count($uploadedPaths) > 0 ? implode(";", $uploadedPaths) : NULL;

        // Generate QR code
        $docQRFileName = $projectID . "-" . $safeDocName . "-QR.png";
        $docQRFile = $docFolder . "/" . $docQRFileName;
        $docQRContent = "uploads/$projectID/$safeDocName";
        QRcode::png($docQRContent, $docQRFile, QR_ECLEVEL_L, 4);

        $docQRPath = "uploads/$projectID/$safeDocName/$docQRFileName";

        // Insert document record into DB
        $sqlLastDoc = "SELECT DocumentID FROM document ORDER BY DocumentID DESC LIMIT 1";
        $resLastDoc = $conn->query($sqlLastDoc);
        $newDocNum = ($resLastDoc && $resLastDoc->num_rows > 0) 
            ? str_pad(intval(substr($resLastDoc->fetch_assoc()['DocumentID'], 4)) + 1, 5, "0", STR_PAD_LEFT)
            : "00001";
        $documentID = "DOC-" . $newDocNum;

        $documentName = "$projectID-$safeDocName";

        $sqlDoc = "INSERT INTO document 
            (DocumentID, DocumentName, ProjectID, DocumentType, DigitalLocation, DocumentStatus, DocumentQR)
            VALUES
            ('$documentID','$documentName','$projectID',
            '$docName',
            " . ($filesString !== NULL ? "'" . mysqli_real_escape_string($conn, $filesString) . "'" : "NULL") . ",
            " . ($status !== null ? "'$status'" : "NULL") . ",
            '$docQRPath')";
        $conn->query($sqlDoc);
    }
}

echo json_encode([
  'status' => 'success',
  'message' => 'Project uploaded successfully.',
  'projectID' => $projectID
]);
$conn->close();
?>
