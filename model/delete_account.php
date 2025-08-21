<?php
include '../server/server.php';

header('Content-Type: application/json');

// Get the raw POST data (JSON)
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing account ID']);
    exit;
}

$account_id = $data['account_id']; // Keep as string — no intval()

// Optional: add authentication/authorization checks here before deletion

// Prepare and execute delete query
$sql = "DELETE FROM employee WHERE EmployeeID = ? AND AccountType = 'user'";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
    exit;
}

$stmt->bind_param('s', $account_id); // bind as string

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database execution failed']);
}

$stmt->close();
$conn->close();
?>
