<?php
include 'phpqrcode/qrlib.php';

if (!file_exists('qrcodes')) mkdir('qrcodes', 0777, true);

QRcode::png('Hello World', 'qrcodes/test.png', QR_ECLEVEL_L, 4);
echo "QR code generated!";
