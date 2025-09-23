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

.userlist-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 5%;
    margin: 5% 10% 1% 10%;
}

.user-card {
    background: #7B0302;
    padding: 1cqw;
    border-radius: 1cqw;
    box-shadow: 0.25cqw 0.25cqw 1cqw rgba(0, 0, 0, 0.3);
    height: 17cqw;
    margin-bottom: 5%;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
}

.user-status {
    position: absolute;
    top: 1cqw;
    left: 1cqw;
    font-size: 0.75cqw;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.3cqw 0.8cqw;
    border-radius: 0.5cqw;
    color: white;
}

.status-active {
    background-color: #00830F;
}

.status-inactive {
    background-color: black;
}

.user-name {
    font-size: 1cqw;
    font-weight: 600;
    margin: 0.3cqw 0;
}

.user-position {
    font-size: 0.75cqw;
    margin: 0.5cqw 0;
}

.iconEllipsis {
    position: absolute;
    top: 1cqw;
    right: 1cqw;
    font-size: 1cqw;
    cursor: pointer;
}

#iconUL {
    font-size: 3cqw;
    margin-bottom: 0.5cqw;
}

.status-dropdown {
    position: absolute;
    top: 3cqw;
    left: 11cqw;
    background: white;
    color: black;
    border-radius: 0.5cqw;
    box-shadow: 0 0.5cqw 1cqw rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
    z-index: 10;
}

.status-option {
    padding: 0.5cqw 1cqw;
    cursor: pointer;
}

.status-option:first-child {
    border-top-left-radius: 0.5cqw;
    border-top-right-radius: 0.5cqw;
}

.status-option:last-child {
    border-bottom-left-radius: 0.5cqw;
    border-bottom-right-radius: 0.5cqw;
}

.status-option:hover {
    background-color: #eee;
    border-radius: 0.5cqw;
}

.floating-add-user {
    position: fixed;
    bottom: 50px;
    right: 50px;
    background-color: #7a0000;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    z-index: 50;
    transition: transform 0.3s ease;
}

.floating-add-user:hover {
    transform: scale(1.2);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(0.1cqw);
    -webkit-backdrop-filter: blur(0.1cqw);
}

.modal-content {
    margin: 3cqw auto;
    max-width: 50cqw;
    max-height: 35cqw;
    border-radius: 1cqw !important;
    border: none;
}

label {
    color: #7B0302;
}

#employeeid_display {
    background-color: #e0e0e0;
    border: 1px solid #ccc;
    color: #444;
    font-weight: 600;
}

.form-section input {
    border-radius: 0.26vw;
    border: 1px solid #ccc;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

button {
    width: 100%;
    padding: 1.11vh 0.83vw;
    background-color: #7B0302;
    color: #fff;
    border: none;
    border-radius: 0.26vw;
    cursor: pointer;
    margin-top: 2vh;
}

.toast {
    background-color: #333;
    color: #fff;
    padding: 1cqw 2cqw;
    border-radius: 0.5cqw;
    margin-bottom: 1cqw;
    min-width: 15cqw;
    max-width: 25cqw;
    font-size: 0.9cqw;
    opacity: 0.95;
    box-shadow: 0 0.25cqw 0.5cqw rgba(0, 0, 0, 0.2);
    animation: fadeInOut 4s ease forwards;
}

.toast-success {
    background-color: #28a745;
}

.toast-error {
    background-color: #dc3545;
}

@keyframes fadeInOut {
    0% {
        opacity: 0;
        transform: translateY(-10px);
    }

    10% {
        opacity: 1;
        transform: translateY(0);
    }

    90% {
        opacity: 1;
    }

    100% {
        opacity: 0;
        transform: translateY(-10px);
    }
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
    <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">User List</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<div class="floating-add-user" id="add-account-btn" data-next-id="<?= $nextEmployeeId ?>">
    <span class="fa fa-plus" style="font-size: 1.5cqw; color: white;"></span>
</div>

<div id="modalAddUser" class="modal">
    <div class="modal-content"
        style="max-width: 20cqw; margin-top: 10cqw; padding: 2cqw; background: white; border-radius: 1cqw;">
        <span class="close"
            style="cursor:pointer; font-size: 2cqw; position: relative; top: -2cqw; left: 19rem; color: #7B0302;">&times;</span>
        <div class="form-section" style="display: flex; gap: 3cqw;">
            <div style="flex: 1;">
                <form id="adduser-form" action="model/register_processing.php" method="POST"
                    style="display: flex; flex-direction: column; gap: 1.5cqw;">

                    <div style="display: flex; align-items: flex-start; gap: 0cqw;">
                        <label for="employeeid_display"
                            style="min-width: 6cqw; font-weight: 700; margin-top: 0.3cqw;">EMPLOYEE ID:</label>
                        <input type="text" id="employeeid_display" value="<?= $nextEmployeeId ?>" disabled
                            style="flex: 1;" />
                        <input type="hidden" id="employeeid" name="employeeid" value="<?= $nextEmployeeId ?>" />
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 0cqw;">
                        <label for="first_name" style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">First
                            Name:</label>
                        <input type="text" id="first_name" name="first_name" required class="adduser-firstname"
                            style="flex: 1;" />
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 0cqw;">
                        <label for="last_name" style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">Last
                            Name:</label>
                        <input type="text" id="last_name" name="last_name" required class="adduser-lastname"
                            style="flex: 1;" />
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 0cqw;">
                        <label for="email" style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">Email:</label>
                        <input type="email" id="email" name="email" required style="flex: 1;" />
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 0cqw;">
                        <label for="position"
                            style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">Position:</label>
                        <select id="position" name="position" required style="flex: 1; max-width: 16cqw;">
                            <option value="" disabled selected>Select Position</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Compliance Officer">Compliance Officer</option>
                            <option value="CAD Operator">CAD Operator</option>
                        </select>
                    </div>

                    <button id="signup-button" type="submit" style="margin-left: 4cqw; max-width: 8cqw;">Add
                        Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$sql = "SELECT EmployeeID, CONCAT(EmpFName, ' ', EmpLName) AS fullname, JobPosition, AccountStatus
        FROM employee
        WHERE AccountType = 'User'
        ORDER BY EmployeeID ASC";

$result = $conn->query($sql);

echo '<section id="user-section">';
echo '<div class="userlist-wrapper">';
echo '<div class="userlist-grid">';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['AccountStatus']);
        $accountstatusClass = $status === 'active' ? 'status-active' : 'status-inactive';

        // Conditionally render status options
        $activateOption = '';
        $deactivateOption = '';

        if ($status === 'inactive') {
            $activateOption = '<div class="status-option" data-id="' . $row['EmployeeID'] . '" data-status="Active">Activate</div>';
        } elseif ($status === 'active') {
            $deactivateOption = '<div class="status-option" data-id="' . $row['EmployeeID'] . '" data-status="Inactive">Deactivate</div>';
        }

        echo '<div class="user-card">';
        echo '<div class="user-status ' . $accountstatusClass . '">' . strtoupper(htmlspecialchars($row['AccountStatus'])) . '</div>';
        echo '<div class="fa fa-ellipsis-h iconEllipsis" data-id="' . $row['EmployeeID'] . '"></div>';
        echo '<div class="status-dropdown" id="dropdown-' . $row['EmployeeID'] . '">';
        echo $activateOption . $deactivateOption;
        echo '<div class="status-option" data-id="' . $row['EmployeeID'] . '" data-status="Delete">Delete</div>';
        echo '</div>';
        echo '<div class="fa fa-user-circle" id="iconUL"></div>';
        echo '<div class="user-name">' . htmlspecialchars($row['fullname']) . '</div>';
        echo '<div class="user-position">' . htmlspecialchars($row['JobPosition']) . '</div>';
        echo '<div class="user-id">' . htmlspecialchars($row['EmployeeID']) . '</div>';
        echo '</div>';
    }
}

echo '</div></div></section>';
?>
<div id="toast-container" style="position: fixed; top: 2vh; right: 2vw; z-index: 9999;"></div>