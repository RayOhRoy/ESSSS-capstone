<?php
session_start();
include '../server/server.php'; // database connection

if (!isset($_SESSION['employeeid'])) {
    header('Location: login.php');
    exit();
}

$employeeid = $_SESSION['employeeid'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if ($new_password !== $confirm_password) {
      echo "<script>
        alert('New password do not match.');
        window.location.href = '../index.php';
    </script>";
    exit();
}

// Step 1: Fetch hashed password from database
$sql = "SELECT Password FROM employee WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employeeid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Employee not found.");
}

$row = $result->fetch_assoc();
$hashedPassword = $row['Password'];

// Step 2: Verify current password
if (!password_verify($current_password, $hashedPassword)) {
      echo "<script>
        alert('Current password is incorrect.');
        window.location.href = '../index.php';
    </script>";
    exit();
}

// Step 3: Hash and update new password
$newHashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE employee SET Password = ? WHERE EmployeeID = ?");
$update->bind_param("ss", $newHashedPassword, $employeeid);
$update->execute();

if ($update->affected_rows > 0) {
    echo "<script>
        alert('Password successfully updated.');
        window.location.href = '../index.php';
    </script>";
    exit();
} else {
    echo "<script>
        alert('No changes made or update failed.');
        window.location.href = '../index.php';
    </script>";
    exit();
}
?>
