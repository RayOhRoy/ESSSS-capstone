<?php
$data = json_decode(file_get_contents("php://input"), true);
if (password_verify($data['password'], $data['hash'])) {
  echo "1";
} else {
  echo "0";
}
?>
