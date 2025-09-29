<?php
session_start();
include '../server/server.php'; // Adjust path as needed

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method.'); history.back();</script>";
    exit;
}

// Sanitize and validate input
$employeeID = trim($_POST['employeeid'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');

if (empty($employeeID) || empty($firstName) || empty($lastName)) {
    echo "<script>alert('All fields are required.'); history.back();</script>";
    exit;
}

// Update employee record
$stmt = $conn->prepare("UPDATE employee SET EmpFName = ?, EmpLName = ? WHERE EmployeeID = ?");
if (!$stmt) {
    echo "<script>alert('Failed to prepare statement.'); history.back();</script>";
    exit;
}

$stmt->bind_param("sss", $firstName, $lastName, $employeeID);

if ($stmt->execute()) {
    echo "<script>alert('Employee updated successfully.'); window.location.href = '../index.php';</script>";
} else {
    echo "<script>alert('Failed to update employee.'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
