<?php
session_start();
include '../server/server.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $employeeid = trim($_POST['employeeid']);
    $password = $_POST['password'];

    if (empty($employeeid) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter Employee ID and Password']);
        exit;
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT EmployeeID, Password, AccountStatus, AccountType FROM employee WHERE EmployeeID = ? LIMIT 1");
    $stmt->bind_param("s", $employeeid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (strtolower($user['AccountStatus']) === 'inactive') {
            echo json_encode(['success' => false, 'message' => 'Account is inactive']);
            exit;
        }

        // Verify password hash
        if (password_verify($password, $user['Password'])) {
            // Set session variables
            $_SESSION['employeeid']  = $user['EmployeeID'];
            $_SESSION['AccountType'] = $user['AccountType'];
            $_SESSION['role']        = strtolower($user['AccountType']); 

            // Determine redirect page based on account type
            $redirect = ($_SESSION['role'] === 'admin') ? 'admin.php' : 'user.php';

            echo json_encode(['success' => true, 'redirect' => $redirect]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }
}
?>
