<?php
session_start();
include '../server/server.php'; // database connection

header('Content-Type: application/json');

// Check logged in
if (!isset($_SESSION['employeeid'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Read JSON POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

$current_password = $data['currentPassword'] ?? '';
$new_password = $data['newPassword'] ?? '';
$confirm_password = $data['confirmPassword'] ?? '';

if (!$current_password || !$new_password || !$confirm_password) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'error' => 'New password does not match confirmation']);
    exit();
}

$employeeid = $_SESSION['employeeid'];

// Step 1: Fetch hashed password from database
$sql = "SELECT Password FROM employee WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database error: prepare failed']);
    exit();
}
$stmt->bind_param("s", $employeeid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Employee not found']);
    exit();
}

$row = $result->fetch_assoc();
$hashedPassword = $row['Password'];

// Step 2: Verify current password
if (!password_verify($current_password, $hashedPassword)) {
    echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
    exit();
}

// Step 3: Hash and update new password
$newHashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE employee SET Password = ? WHERE EmployeeID = ?");
if (!$update) {
    echo json_encode(['success' => false, 'error' => 'Database error: prepare failed']);
    exit();
}
$update->bind_param("ss", $newHashedPassword, $employeeid);
$update->execute();

if ($update->affected_rows > 0) {
    echo json_encode(['success' => true]);
    exit();
} else {
    echo json_encode(['success' => false, 'error' => 'No changes made or update failed']);
    exit();
}
