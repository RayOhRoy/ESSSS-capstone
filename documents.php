<link rel="stylesheet" href="css/documents.css">

<?php
session_start();
include 'server/server.php';

$employeeID = $_SESSION['employeeid'] ?? null;
$empFName = '';
$empLName = '';
$jobPosition = '';
$empEmail = '';

if ($employeeID) {
    $stmt = $conn->prepare("SELECT EmpFName, EmpLName, JobPosition, Email FROM employee WHERE EmployeeID = ?");
    $stmt->bind_param("s", $employeeID);
    $stmt->execute();
    $stmt->bind_result($empFName, $empLName, $jobPosition, $empEmail);
    $stmt->fetch();
    $stmt->close();
}
?>

<div class="user-menu-panel" id="userPanel">
    <div class="user-panel-top">
        <div class="user-top-info">
            <p style="font-size: 2rem; font-weight: 700;">
                <?= htmlspecialchars($empFName . ' ' . $empLName) ?>
            </p>
            <p style="font-size: 1rem;">
                <?= htmlspecialchars($jobPosition) ?>
            </p>
        </div>
    </div>

    <div class="user-bottom-info">
        <p>Employee ID</p>
        <input placeholder="<?= htmlspecialchars($employeeID) ?>" disabled>
        <p>Email</p>
        <input placeholder="<?= htmlspecialchars($empEmail) ?>" disabled>
        <p>Password</p>
        <input type="password" placeholder="*******" disabled>
        <a id="changepassword-button">Change Password</a>
        <a href="model/logout.php" class="signout-button">Sign out</a>
    </div>

    <div class="user-forgot-password">
        <p style="font-size: 2rem; margin-top: -25%; margin-bottom: 10%;">Change Password</p>
        <p>Current Password</p>
        <input type="password" required>
        <p>New Password</p>
        <input type="password" required>
        <p>Confirm New Password</p>
        <input type="password" required>
        <div style="display: flex; position: absolute; right: -35%; gap: 5%;">
            <a id="confirmchangepassword-button">Confirm</a>
            <a id="cancelchangepassword-button">Cancel</a>
        </div>
    </div>
</div>

<div class="topbar">
  <span class="document">Documents</span>
  <div class="topbar-content">
    <div class="icons">
      <span id="user-circle-icon" class="fa fa-user-circle"></span>
    </div>
  </div>
</div>

<hr class="top-line" />

<div id="municipalityButtons" class="municipality-buttons">
    <?php
    include 'server/server.php';

    // Query: get unique municipalities from project + address relationship
    $query = "
        SELECT DISTINCT a.Municipality 
        FROM project p
        JOIN address a ON p.AddressID = a.AddressID
        WHERE a.Province = 'Bulacan'
        ORDER BY a.Municipality ASC
    ";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $municipality = htmlspecialchars($row['Municipality']);
            echo '<button class="btns" data-municipality="' . $municipality . '" onclick="redirectToProjectList(this)">' . $municipality . '</button>';
        }
    } else {
        echo "<p style='text-align:center; color:#7B0302; font-weight:600;'>No municipalities found.</p>";
    }
    ?>
</div>