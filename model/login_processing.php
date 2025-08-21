<?php
session_start();
include '../server/server.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeid = mysqli_real_escape_string($conn, $_POST['employeeid']);
    $password   = $_POST['password'];

    $query  = "SELECT * FROM employee WHERE EmployeeID = '$employeeid' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (strtolower($user['AccountStatus']) === 'inactive') {
            echo json_encode(['success' => false, 'message' => 'Account is inactive']);
        } elseif (password_verify($password, $user['Password'])) {

            $_SESSION['employeeid']  = $user['EmployeeID'];
            $_SESSION['AccountType'] = $user['AccountType'];
            $_SESSION['role']        = strtolower($user['AccountType']); 

            $redirect = (strtolower($user['AccountType']) === 'admin') ? 'admin.php' : 'user.php';
            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }

    $conn->close();
    exit;
}
?>
