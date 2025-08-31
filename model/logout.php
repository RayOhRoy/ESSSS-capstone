<?php
session_start();
session_unset();
session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Expires: 0");
header("Pragma: no-cache");

header("Location: ../index.php");
exit();
?>
