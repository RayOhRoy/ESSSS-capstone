<link rel="stylesheet" href="css/physical_storage.css">

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

// === Pagination Logic ===
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max(1, min($page, 5)); // Limit between 1 and 5

$leftStart = ($page - 1) * 10 + 1;
$rightStart = $leftStart + 50;
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
    <span>Physical Storage</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<div class="card-container">
    <div class="card">
        <div class="card-title">HAG-01</div>
        <button class="open-button">OPEN</button>
    </div>
    <div class="card">
        <div class="card-title">CAL-01</div>
        <button class="open-button">OPEN</button>
    </div>
</div>

<div class="envelope-columns" style="display: none;">
    <!-- Left Column: 001–010 -->
    <div class="envelope-container">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <?php $num = str_pad($i, 3, '0', STR_PAD_LEFT); ?>
            <div class="envelope-card">
                <div class="envelope-title">HAG-01-<?= $num ?></div>
                <div class="envelope-right">
                    <div class="fa fa-eye"></div>
                    <button class="envelope-button">RETRIEVE</button>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <!-- Right Column: 051–060 -->
    <div class="envelope-container">
        <?php for ($i = 51; $i <= 60; $i++): ?>
            <?php $num = str_pad($i, 3, '0', STR_PAD_LEFT); ?>
            <div class="envelope-card">
                <div class="envelope-title">HAG-01-<?= $num ?></div>
                <div class="envelope-right">
                    <div class="fa fa-eye"></div>
                    <button class="envelope-button">RETRIEVE</button>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>