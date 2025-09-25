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
    .topbar span {
        font-size: 2cqw;
        color: #7B0302;
        font-weight: 700;
    }

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

    .activitylist-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        font-size: 0.8vw;
    }

    .activitylist-table th,
    .activitylist-table td {
        padding: 8px;
        text-align: center;
        min-width: 10cqw;
    }

    .activitylist-table td {
        text-align: center;
        padding: 8px;
    }

    .sort-btn {
        width: 100%;
        background: transparent;
        border: none;
        color: #7B0302;
        font-weight: bold;
        cursor: pointer;
        padding: 6px;
        transition: background 0.3s, color 0.3s;
    }

    .sort-btn.active-sort {
        background-color: #7B0302;
        color: white;
        border-radius: 4cqw;
    }

    #employeeFilter {
        width: 100%;
        padding: 6px;
        font-weight: bold;
        color: #7B0302;
        border-radius: 4px;
    }

    .filter-clear {
        margin: 10px 0;
        padding: 6px 10px;
        background-color: #eee;
        border: 1px solid #ccc;
        color: #7B0302;
        cursor: pointer;
        border-radius: 4px;
    }

    .filter-clear:hover {
        background-color: #7B0302;
        color: white;
    }

    @media (max-width: 1080px) {
        #employeeFilter {
            width: 119%;
            height: 60px;
            padding: 6px 7px 6px 5px;
            font-weight: bold;
            color: #7B0302;
            border-radius: 4px;
            margin-top: 40px;
            font-size: 20px;
        }

        .topbar span {
            font-size: 70px;
            color: #7B0302;
            font-weight: 700;
            padding: 20px 0px 20px 0px;
        }

        .fa.fa-user-circle {
            font-size: 70px;
            color: #7B0302;
        }

        .sort-btn {

            font-size: 35px;
        }

        .activitylist-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            font-size: 22px;
        }

        .activitylist-table td {
            text-align: center;
            padding: 20px 10px 20px 5px;
        }

        .dropdown-menu {
            margin-top: 250px;
        }

        .dropdown-menu a {
            font-size: 40px;
            width: 250px;
            height: auto;
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
    <span>Activity Log</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<?php
include 'server/server.php';

$employeeOptions = [];
$employeeResult = $conn->query("SELECT EmployeeID, EmpFName, EmpLName FROM employee ORDER BY EmpFName, EmpLName");
if ($employeeResult && $employeeResult->num_rows > 0) {
    while ($emp = $employeeResult->fetch_assoc()) {
        $fullName = $emp['EmpFName'] . ' ' . $emp['EmpLName'];
        $employeeOptions[$emp['EmployeeID']] = $fullName;
    }
}

$activity_logs = [];
$sql = "
    SELECT 
        al.ActivityLogID,
        al.ProjectID,
        al.DocumentID,
        al.Status,
        al.Time,
        e.EmpFName,
        e.EmpLName,
        e.JobPosition,
        d.DocumentName
    FROM activity_log al
    JOIN employee e ON al.EmployeeID = e.EmployeeID
    LEFT JOIN document d ON al.DocumentID = d.DocumentID
    ORDER BY al.Time DESC
";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = $row['EmpFName'] . ' ' . $row['EmpLName'];
        $documentInfo = empty($row['DocumentID']) ? $row['ProjectID'] : $row['DocumentName'];
        $activity_logs[] = [
            'employee_name' => $fullName,
            'job_position' => $row['JobPosition'],
            'document_info' => $documentInfo,
            'status' => strtoupper($row['Status']),
            'time' => date('d M Y H:i', strtotime($row['Time']))
        ];
    }
}
$conn->close();
?>

<label for="dateFrom">From:</label>
<input type="date" id="dateFrom" onchange="filterByDate()" />

<label for="dateTo">To:</label>
<input type="date" id="dateTo" onchange="filterByDate()" />

<table class="activitylist-table" id="projectTable">
    <thead>
        <tr>
            <th>
                <select id="employeeFilter" onchange="filterByEmployee()"
                    style="min-width: 100%; font-weight: bold; color: #7B0302; border-radius: 4px;">
                    <option value="">All Employees</option>
                    <?php foreach ($employeeOptions as $id => $name): ?>
                        <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </th>
            <th><button class="sort-btn" onclick="sortTable(1, this)">Document</button></th>
            <th><button class="sort-btn" onclick="sortTable(2, this)">Action</button></th>
            <th><button class="sort-btn" onclick="sortTable(3, this)">Timestamp</button></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($activity_logs)): ?>
            <tr>
                <td colspan="4">No activity logs available.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($activity_logs as $log): ?>
                <tr data-employee="<?= htmlspecialchars($log['employee_name']) ?>">
                    <td>
                        <div style="color: #7B0302; font-weight: bold; text-transform: uppercase;">
                            <?= htmlspecialchars($log['employee_name']) ?>
                        </div>
                        <div style="color: #7B0302; font-weight: normal;">
                            <?= htmlspecialchars($log['job_position']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($log['document_info']) ?></td>
                    <td><?= htmlspecialchars($log['status']) ?></td>
                    <td><?= htmlspecialchars($log['time']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>