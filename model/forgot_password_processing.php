<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../server/server.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }

    // Check if email exists
    $query = "SELECT EmployeeID, EmpFName, EmpLName FROM employee WHERE Email = '$email' LIMIT 1";
    $result = $conn->query($query);

    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No account found with that email address.']);
        exit;
    }

    $user = $result->fetch_assoc();
    $employeeid = $user['EmployeeID'];
    $firstname = $user['EmpFName'];
    $lastname = $user['EmpLName'];

    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Store OTP and expiry in DB
    $updateQuery = "UPDATE employee SET PasswordCode = '$otp', CodeExpiry = '$expiry' WHERE Email = '$email'";
    if (!$conn->query($updateQuery)) {
        echo json_encode(['success' => false, 'message' => 'Failed to set OTP. Please try again later.']);
        exit;
    }

    // Send email with OTP
    require 'phpmailer/Exception.php';
    require 'phpmailer/PHPMailer.php';
    require 'phpmailer/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'essantossurveyingservices@gmail.com';
        $mail->Password = 'icwndbffbxctuxpt';  // Use environment variables in production!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('essantossurveyingservices@gmail.com', 'ES Santos Surveying Services');
        $mail->addAddress($email, $firstname . ' ' . $lastname);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code - ESSSS Centralized Document Management System';
        $mail->Body = '
            <b>Dear ' . htmlspecialchars($firstname) . ' ' . htmlspecialchars($lastname) . ',</b><br><br>
            You have requested to reset your password. Please use the OTP code below:<br><br>
            <h2>' . $otp . '</h2>
            <p>This code will expire in <b>15 minutes</b>.</p>
            <br>If you did not request this reset, please contact support immediately.<br><br>
            Best regards,<br>
            <i>ES Santos Surveying Services</i>
        ';

        $mail->send();

        echo json_encode(['success' => true, 'message' => 'OTP has been sent to your email.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please contact support.']);
    }

    $conn->close();
    exit;
}
?>
