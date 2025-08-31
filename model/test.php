<?php
$path = __DIR__ . '/../uploads';

echo "Path: $path\n";

echo 'Exists: ' . (is_dir($path) ? 'Yes' : 'No') . "\n";
echo 'Writable: ' . (is_writable($path) ? 'Yes' : 'No') . "\n";
echo 'Permissions: ' . substr(sprintf('%o', fileperms($path)), -4) . "\n";