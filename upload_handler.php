<?php
if ($_FILES['file']['error'] == 0) {
  $target = "uploads/" . basename($_FILES['file']['name']);
  if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
    echo "Upload Success";
  } else {
    echo "Upload Failed";
  }
} else {
  echo "No file uploaded.";
}
?>
