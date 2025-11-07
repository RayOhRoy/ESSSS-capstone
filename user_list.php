<link rel="stylesheet" href="css/user_list.css">

<?php
session_start();
include 'server/server.php';

// Initialize $nextEmployeeId before output
$nextEmployeeId = 'ES0001';
$sql = "SELECT employeeid FROM employee WHERE employeeid LIKE 'ES%' ORDER BY employeeid DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $lastId = intval(substr($row['employeeid'], 2)); // substring after 'ES' prefix
    $nextId = $lastId + 1;
    $nextEmployeeId = 'ES' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}

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
            <p>
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
    <span>User List</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<div class="floating-add-user" id="add-account-btn" data-next-id="<?= htmlspecialchars($nextEmployeeId) ?>">
    <span class="fa fa-plus"></span>
</div>

<div id="modalAddUser" class="modal">
    <div class="modal-content">
        <span class="close"
            style="cursor:pointer; font-size: 2cqw; position: relative; top: -2cqw; left: 19rem; color: #7B0302;">&times;</span>
        <div class="form-section" style="display: flex; gap: 3cqw;">
            <div style="flex: 1;">
                <form id="adduser-form" action="model/register_processing.php" method="POST"
                    style="display: flex; flex-direction: column; gap: 1.5cqw;">

                    <div style="display: flex; align-items: flex-start; gap: 0cqw;">
                        <label for="employeeid_display"
                            style="min-width: 6cqw; font-weight: 700; margin-top: 0.3cqw;">EMPLOYEE ID:</label>
                        <input type="text" id="employeeid_display" value="<?= htmlspecialchars($nextEmployeeId) ?>" disabled
                            style="flex: 1;" />
                        <input type="hidden" id="employeeid" name="employeeid" value="<?= htmlspecialchars($nextEmployeeId) ?>" />
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
                        <select id="position" name="position" required>
                            <option value="" disabled selected>Select Position</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Compliance Officer">Compliance Officer</option>
                            <option value="CAD Operator">CAD Operator</option>
                        </select>
                    </div>

                    <button id="signup-button" type="submit">Add Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Updated SQL to also fetch Email, EmpFName, EmpLName
$sql = "SELECT EmployeeID, EmpFName, EmpLName, Email, JobPosition, AccountStatus
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

        $activateOption = '';
        $deactivateOption = '';
        if ($status === 'inactive') {
            $activateOption = '<div class="status-option" data-id="' . htmlspecialchars($row['EmployeeID']) . '" data-status="Active">Activate</div>';
        } elseif ($status === 'active') {
            $deactivateOption = '<div class="status-option" data-id="' . htmlspecialchars($row['EmployeeID']) . '" data-status="Inactive">Deactivate</div>';
        }

        echo '<div class="user-card">';
        echo '<div class="user-status ' . $accountstatusClass . '">' . strtoupper(htmlspecialchars($row['AccountStatus'])) . '</div>';
        echo '<div class="fa fa-ellipsis-h iconEllipsis" data-id="' . htmlspecialchars($row['EmployeeID']) . '"></div>';
        echo '<div class="status-dropdown" id="dropdown-' . htmlspecialchars($row['EmployeeID']) . '">';
        echo $activateOption . $deactivateOption;
        echo '<div class="status-option edit-option" data-id="' . htmlspecialchars($row['EmployeeID']) . '" data-status="Edit">Edit</div>';
        echo '<div class="status-option" data-id="' . htmlspecialchars($row['EmployeeID']) . '" data-status="Delete">Delete</div>';
        echo '</div>';
        echo '<div class="fa fa-user-circle" id="iconUL"></div>';
        echo '<div class="user-name">' . htmlspecialchars($row['EmpFName'] . ' ' . $row['EmpLName']) . '</div>';
        echo '<div class="user-position-display">' . htmlspecialchars($row['JobPosition']) . '</div>';
        echo '<div class="user-id">' . htmlspecialchars($row['EmployeeID']) . '</div>';

        // Hidden inputs for data
        echo '<input type="hidden" class="user-email" value="' . htmlspecialchars($row['Email']) . '">';
        echo '<input type="hidden" class="user-position" value="' . htmlspecialchars($row['JobPosition']) . '">';
        echo '<input type="hidden" class="user-fname" value="' . htmlspecialchars($row['EmpFName']) . '">';
        echo '<input type="hidden" class="user-lname" value="' . htmlspecialchars($row['EmpLName']) . '">';

        echo '</div>'; // close user-card
    }
}

echo '</div></div></section>';
?>

<div id="toast-container" style="position: fixed; top: 2vh; right: 2vw; z-index: 9999;"></div>
