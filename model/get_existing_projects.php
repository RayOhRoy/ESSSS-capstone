<?php
include '../server/server.php';
header('Content-Type: application/json');

// Query all full project IDs
$query = "SELECT ProjectID FROM project";
$result = $conn->query($query);

$existing = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projectId = trim($row['ProjectID']);
        if (!empty($projectId)) {
            $existing[] = $projectId; // Store the full ProjectID like HAG-01-100-ABC
        }
    }
}

echo json_encode($existing);
$conn->close();
?>
