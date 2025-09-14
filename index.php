<?php
// Send no-cache headers to prevent browser caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Optional: send Last-Modified and ETag for extra cache validation
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("ETag: " . md5_file(__FILE__));

date_default_timezone_set("Asia/Manila");
session_start();

$userRole = $_SESSION['role'] ?? null;
$userID = $_SESSION['employeeid'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ESSSS Document Management System</title>
  <link rel="icon" href="picture/logo.jpg" type="image/png" />
  
  <!-- Cache-busted CSS -->
  <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>" />
  <link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css?v=<?= filemtime('css/font-awesome-4.7.0/css/font-awesome.min.css') ?>" />
</head>
<body>

<div id="form-content"></div>

<script>
  const SESSION_ROLE = <?= json_encode($userRole) ?>;
  const SESSION_ID = <?= json_encode($userID) ?>;

  // Example AJAX call with cache busting
  function fetchData() {
    fetch('your_data_endpoint.php?_=' + new Date().getTime())
      .then(response => response.json())
      .then(data => {
        console.log('Fresh data:', data);
        // Update your UI accordingly
      });
  }

  // Example: call fetchData every 30 seconds to always get fresh data
  setInterval(fetchData, 30000);
  fetchData(); // initial call
</script>

<!-- Cache-busted JS -->
<script src="js/main.js?v=<?= filemtime('js/main.js') ?>"></script>

</body>
</html>
