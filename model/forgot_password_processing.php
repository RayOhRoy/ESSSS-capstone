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

    // Generate new password
    $generatedPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);

    // Update password in DB
    $updateQuery = "UPDATE employee SET Password = '$hashedPassword' WHERE Email = '$email'";

    if (!$conn->query($updateQuery)) {
        echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again later.']);
        exit;
    }

    // Send email with new password
    require 'phpmailer/Exception.php';
    require 'phpmailer/PHPMailer.php';
    require 'phpmailer/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'essantossurveyingservices@gmail.com';  // your SMTP username
        $mail->Password = 'icwndbffbxctuxpt';                    // your SMTP password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('essantossurveyingservices@gmail.com', 'ES Santos Surveying Services');
        $mail->addAddress($email, $firstname . ' ' . $lastname);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - ESSSS Centralized Document Management System';
        $mail->Body = '
            <b>Dear ' . htmlspecialchars($firstname) . ' ' . htmlspecialchars($lastname) . ',</b><br><br>
            A password reset request was received for your account on the <b>ESSSS Centralized Document Management System</b>.<br><br>
            Your new temporary password is:<br>
            <b>' . htmlspecialchars($generatedPassword) . '</b><br><br>
            Please log in using this password and change it immediately to ensure your account\'s security.<br><br>
            If you did not request this reset, please contact support immediately.<br><br>
            Best regards,<br>
            <i>ES Santos Surveying Services</i>
        ';

        $mail->send();

        echo json_encode(['success' => true, 'message' => 'A new password has been sent to your email.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please contact support.']);
    }

    $conn->close();
    exit;
}
?>
