<?php
include '../server/server.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $otp = mysqli_real_escape_string($conn, $_POST['otp'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($otp) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Check OTP and expiry
    $query = "SELECT PasswordCode, CodeExpiry FROM employee WHERE Email = '$email' LIMIT 1";
    $result = $conn->query($query);

    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Email not found.']);
        exit;
    }

    $user = $result->fetch_assoc();
    $storedOTP = $user['PasswordCode'];
    $expiry = $user['CodeExpiry'];

    if ($otp !== $storedOTP) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
        exit;
    }

    if (strtotime($expiry) < time()) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        exit;
    }

    // Hash and update new password
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
    $update = "UPDATE employee 
               SET Password = '$hashedPassword', PasswordCode = NULL, CodeExpiry = NULL 
               WHERE Email = '$email'";

    if ($conn->query($update)) {
        echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reset password. Try again.']);
    }

    $conn->close();
    exit;
}
?>
