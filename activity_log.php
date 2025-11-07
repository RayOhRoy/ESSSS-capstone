<link rel="stylesheet" href="css/activity_log.css">

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
<div class="inputdate" style="display: flex; align-items: center; justify-content: space-between;">
    <div>
        <label for="dateFrom">From:</label>
        <input type="date" id="dateFrom" onchange="filterByDate()" />

        <label for="dateTo">To:</label>
        <input type="date" id="dateTo" onchange="filterByDate()" />
    </div>

    <button id="downloadPDF" class="download-btn" onclick="downloadFilteredActivityLog()">
        <i class="fa fa-download" style="margin-right: 5px;"></i> Download Activity Log
    </button>
</div>

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