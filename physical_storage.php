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

<style>
#user-circle-icon {
    font-size: 2.25cqw;
    color: #7B0302;
    z-index: 1000;
    transition: all 0.3s ease;
}

#user-circle-icon:hover {
    filter: brightness(1.25);
    transform: scale(1.05);
}

#user-circle-icon.active {
    color: white;
}

.user-menu-panel {
    display: none;
    position: absolute;
    background: white;
    top: 0;
    right: 0;
    width: 26%;
    height: 100%;
    z-index: 999;
    text-align: center;
}

.user-panel-top {
    background-color: #7B0302;
    height: 14rem;
}

.user-top-info {
    position: absolute;
    top: 15%;
    left: 5%;
    text-align: left;
    color: white;
}

.user-bottom-info {
    display: block;
    position: absolute;
    top: 40%;
    left: 10%;
    color: #7B0302;
    text-align: left;
    font-size: 1.5rem;
    font-weight: 700;
}

.user-bottom-info input {
    margin-bottom: 10%;
    width: 140%;
    height: 2.5rem;
    font-size: 1.5rem;
}

#changepassword-button {
    position: absolute;
    top: 95%;
    right: -40%;
    font-size: 1rem;
    text-decoration: underline;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

#changepassword-button:hover {
    color: #600202;
}

a.signout-button {
    position: absolute;
    top: 110%;
    left: 50%;
    background-color: #7B0302;
    color: white;
    padding: 10px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

a.signout-button:hover {
    background-color: #600202;
}

.user-forgot-password {
    display: none;
    position: absolute;
    top: 40%;
    left: 10%;
    color: #7B0302;
    text-align: left;
    font-size: 1.5rem;
    font-weight: 700;
    cursor: pointer;
}

.user-forgot-password input {
    color: #7B0302;
    border: 1px solid;
    margin-bottom: 10%;
    width: 140%;
    height: 2.5rem;
    font-size: 1.5rem;
}

#confirmchangepassword-button {
    background-color: #7B0302;
    color: white;
    padding: 10px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

#cancelchangepassword-button {
    background-color: #868886ff;
    color: #7B0302;
    padding: 10px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 400;
    transition: all 0.3s ease;
}

#confirmchangepassword-button:hover {
    background-color: #600202;
}

#cancelchangepassword-button:hover {
    background-color: #7B0302;
    color: white;
}

.card-container {
    display: flex;
    gap: 1rem;
}

.card {
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    padding: 30px 40px;
    text-align: center;
    width: 20%;
}

.card-title {
    font-size: 2rem;
    font-weight: bold;
    color: #7a0c0c;
    margin-bottom: 20px;
}

.open-button {
    background-color: #7B0302;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
}

.open-button:hover {
    background-color: #5e0909;
}
.envelope-columns {
    display: flex;
    justify-content: space-between;
    gap: 2rem;
    width: 100%;
    padding: 1rem;
}

/* Vertical container */
.envelope-container {
    display: flex;
    flex-direction: column-reverse;
    gap: 0.75rem;
    width: 48%;
}

/* Card style */
.envelope-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: white;
    border: 1px solid #7a0c0c;;   
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    padding: 10px 20px;
    height: 3rem;
    font-size: 0.9rem;
}

/* Title style */
.envelope-title {
    font-weight: bold;
    color: #7a0c0c;
}

/* Eye + Button group */
.envelope-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.fa-eye {
    color: #7a0c0c;
    cursor: pointer;
    font-size: 1.25rem;
}

.envelope-button {
    background-color: #7B0302;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: bold;
}

.envelope-button:hover {
    background-color: #5e0909;
}

.fa-eye:hover {
    color: #5e0909;
}
</style>

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
    <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Physical Storage</span>
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

<div class="envelope-columns"  style="display: none;">
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