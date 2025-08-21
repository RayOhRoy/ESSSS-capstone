<?php
include '../server/server.php';

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id']; // no intval, keep as string
    $status = $_POST['status'] === 'Active' ? 'Active' : 'Inactive';

    $stmt = $conn->prepare("UPDATE employee SET AccountStatus = ? WHERE EmployeeID = ?");
    $stmt->bind_param("ss", $status, $id); // both strings now

    if ($stmt->execute()) {
        echo "Status updated successfully! $id";
    } else {
        echo "Error updating status for ID: $id";
    }
} else {
    $receivedId = isset($_POST['id']) ? $_POST['id'] : 'none';
    echo "Invalid request. Received ID: $receivedId";
}
?>
