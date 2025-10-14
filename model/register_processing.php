<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include '../server/server.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employeeid = mysqli_real_escape_string($conn, $_POST['employeeid']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $firstname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lastname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $generatedPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);

    // Check if Employee ID already exists
    $checkQuery = "SELECT * FROM employee WHERE EmployeeID = '$employeeid'";
    $checkResult = $conn->query($checkQuery);
    if ($checkResult && $checkResult->num_rows > 0) {
        echo "<script>alert('Employee ID already exists'); history.back();</script>";
        exit;
    }

    // Insert into Employee table
    $insertQuery = "INSERT INTO employee (EmployeeID, EmpLName, EmpFName, Email, Password, JobPosition, AccountType, AccountStatus)
                    VALUES ('$employeeid', '$lastname', '$firstname', '$email', '$hashedPassword', '$position', 'User', 'Active')";

    if ($conn->query($insertQuery) === TRUE) {

        require 'phpmailer/Exception.php';
        require 'phpmailer/PHPMailer.php';
        require 'phpmailer/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'essantossurveyingservices@gmail.com';
            $mail->Password = 'oiomosspudurwrbm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('essantossurveyingservices@gmail.com', 'ES Santos Surveying Services');
            $mail->addAddress($email, $firstname . ' ' . $lastname);

            $mail->isHTML(true);
            $mail->Subject = 'ESSSS Centralized Document Management System Login Credentials';
            $mail->Body = '
            <b>Dear ' . htmlspecialchars($firstname) . ' ' . htmlspecialchars($lastname) . ',</b><br><br>
            We are pleased to inform you that an account has been successfully created for you on the <b>ESSSS Centralized Document Management System</b> with the role of <b>' . htmlspecialchars($position) . '</b>.<br><br>
            To access your account, please open the following link: 
            <a href="' . htmlspecialchars($system_url) . '">' . htmlspecialchars($system_url) . '</a><br><br>
            Your login credentials are as follows:<br>
            &nbsp;&nbsp;&nbsp;<b>Employee ID:</b> ' . htmlspecialchars($employeeid) . '<br>
            &nbsp;&nbsp;&nbsp;<b>Password:</b> ' . htmlspecialchars($generatedPassword) . '<br><br>
            <b>Important:</b> For your security, please change your password immediately after your first login.<br><br>
            Best regards,<br>
            <i>ES Santos Surveying Services</i>
        ';

            $mail->send();

            echo "<script>alert('Registration successful! Credentials emailed to the user.'); window.location.href = '../index.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Registration successful, but email failed to send. Error: " . addslashes($mail->ErrorInfo) . "'); window.location.href = '../index.php';</script>";
        }

    } else {
        echo "<script>alert('Registration failed: " . addslashes($conn->error) . "'); history.back();</script>";
    }

    $conn->close();
    exit;
}
?>
