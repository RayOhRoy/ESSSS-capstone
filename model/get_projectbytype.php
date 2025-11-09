<?php
include '../server/server.php';
header("Content-Type: application/json; charset=UTF-8");

$type = $_GET['type'] ?? '';

if ($type === '' || $type === 'Project') {
    $query = $conn->query("SELECT ProjectID FROM project ORDER BY ProjectID ASC");
    $projects = [];
    while ($row = $query->fetch_assoc()) {
        $projects[] = $row['ProjectID'];
    }
    echo json_encode($projects);
    exit;
}

$stmt = $conn->prepare("
    SELECT DISTINCT p.ProjectID
    FROM project p
    INNER JOIN document d ON p.ProjectID = d.ProjectID
    WHERE d.DocumentType = ? AND d.DocumentStatus IS NOT NULL
    ORDER BY p.ProjectID ASC
");
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row['ProjectID'];
}

$stmt->close();
$conn->close();

echo json_encode($projects);
