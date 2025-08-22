<?php
include 'model/phpqrcode/qrlib.php';

$text = "ni-scan ko yun gamit qr, bali nag-type siya mag-isa";

header('Content-Type: image/png');
QRcode::png($text);
?>
