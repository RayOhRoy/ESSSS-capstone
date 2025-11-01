<?php
include '../server/server.php';
header('Content-Type: application/json');

// Query all full project IDs along with their storage status
$query = "SELECT ProjectID, StorageStatus FROM project";
$result = $conn->query($query);

$existing = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projectId = trim($row['ProjectID']);
        $status = trim($row['StorageStatus']); // e.g., "stored" or "retrieve"
        if (!empty($projectId)) {
            $existing[] = [
                'ProjectID' => $projectId,
                'StorageStatus' => $status
            ];
        }
    }
}

echo json_encode($existing);
$conn->close();
?>
