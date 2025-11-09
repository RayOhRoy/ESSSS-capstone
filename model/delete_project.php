<?php
include '../server/server.php'; // database connection
session_start();
date_default_timezone_set("Asia/Manila");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (empty($_POST['projectId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing projectId']);
    exit;
}

$projectId = trim($_POST['projectId']);
$employeeID = $_SESSION['employeeid'] ?? null;

try {
    // 🔹 Step 1: Prepare folder name (remove last -ABC part)
    $folderBase = preg_replace('/-\w+$/', '', $projectId);
    $uploadDir = __DIR__ . "/../uploads/$folderBase";

    $parts = explode('-', $projectId);
    if (count($parts) >= 3) {
        $addressId = $parts[0] . '-' . $parts[2];
    } else {
        $addressId = $folderBase; // fallback if unexpected format
    }

    // 🔹 Step 2: Delete folder recursively if exists
    if (is_dir($uploadDir)) {
        deleteDirectory($uploadDir);
    }

    // 🔹 Step 3: Delete from `document` and `project` tables
    $conn->begin_transaction();

    $stmtDocs = $conn->prepare("DELETE FROM document WHERE ProjectID = ?");
    $stmtDocs->bind_param("s", $projectId);
    $stmtDocs->execute();

    $stmtProj = $conn->prepare("DELETE FROM project WHERE ProjectID = ?");
    $stmtProj->bind_param("s", $projectId);
    $stmtProj->execute();

    $stmtAddr = $conn->prepare("DELETE FROM address WHERE AddressID = ?");
    $stmtAddr->bind_param("s", $addressId);
    $stmtAddr->execute();

    // 🔹 Step 4: Insert into `activity_log`
    $logId = generateActivityLogId($conn);
    $status = 'DELETED';
    $documentId = ''; // empty per your instructions
    $timeNow = date("Y-m-d H:i:s");

    $stmtLog = $conn->prepare("
        INSERT INTO activity_log (ActivityLogID, ProjectID, DocumentID, Status, EmployeeID, Time)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtLog->bind_param("ssssss", $logId, $projectId, $documentId, $status, $employeeID, $timeNow);
    $stmtLog->execute();

    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Project deleted successfully']);

} catch (Exception $e) {
    if ($conn->errno)
        $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

/**
 * Recursively delete a directory and its contents
 */
function deleteDirectory($dir)
{
    if (!file_exists($dir))
        return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..')
            continue;
        $path = "$dir/$item";
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function generateActivityLogId($conn)
{
    $query = "SELECT ActivityLogID FROM activity_log ORDER BY ActivityLogID DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['ActivityLogID'];

        // Extract last numeric part safely
        if (preg_match('/(\d+)$/', $lastId, $matches)) {
            $num = (int) $matches[1];
            $newNum = str_pad($num + 1, 5, '0', STR_PAD_LEFT); 
        } else {
            $newNum = '00001';
        }
    } else {
        $newNum = '00001'; 
    }

    return "ACT-" . $newNum;
}
