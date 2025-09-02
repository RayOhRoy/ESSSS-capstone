<?php
session_start();
$userRole = $_SESSION['role'] ?? null;
$userID = $_SESSION['employeeid'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ESSSS Document Management System</title>
  <link rel="icon" href="picture/logo.jpg" type="image/png">
  <link rel="stylesheet" href="css/style.css"/>
  <!-- <link rel="stylesheet" href="css/bootstrap.min.css"> -->
  <link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css">
</head>
<body>
  <div id="form-content"></div>
  <script>
    const SESSION_ROLE = <?php echo json_encode($userRole); ?>;
    const SESSION_ID = <?php echo json_encode($userID); ?>;
  </script>

  <script src="js/main.js"></script>
</body>
</html>
