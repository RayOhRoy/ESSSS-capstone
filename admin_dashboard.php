<link rel="stylesheet" href="css/admin_dashboard.css">

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
            <p ><?= htmlspecialchars($empFName . ' ' . $empLName) ?></p>
            <p style="font-size: 1rem;"><?= htmlspecialchars($jobPosition) ?></p>
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
    <span>Dashboard</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />
<div class="welcome">Welcome, Admin!</div>

<?php
include 'server/server.php';

// Fetch stats
$total_projects = $conn->query("SELECT COUNT(*) as total FROM project")->fetch_assoc()['total'];
$total_documents = $conn->query("SELECT COUNT(*) as total FROM document WHERE documentstatus IS NOT NULL")->fetch_assoc()['total'];
$total_digital = $conn->query("SELECT COUNT(*) as total FROM document WHERE DigitalLocation IS NOT NULL")->fetch_assoc()['total'];
$released_documents = $conn->query("SELECT COUNT(*) as total FROM document WHERE LOWER(documentstatus) LIKE '%release%'")->fetch_assoc()['total'];

// Stats array
$stats = [
    ["label" => "TOTAL PROJECTS", "value" => $total_projects, "icon" => "Folder.png", "text" => "PROJECTS"],
    ["label" => "TOTAL PHYSICAL DOCUMENTS", "value" => $total_documents, "icon" => "File text.png", "text" => "PHYSICAL DOCUMENTS"],
    ["label" => "TOTAL DIGITAL DOCUMENTS", "value" => $total_digital, "icon" => "Database.png", "text" => "DIGITAL DOCUMENTS"],
    ["label" => "TOTAL RETRIEVED DOCUMENTS", "value" => $released_documents, "icon" => "Box.png", "text" => "RETRIEVED DOCUMENTS"]
];

// Prepare Pie Chart data
$chart_data = [
    "Physical" => $total_documents,
    "Digital" => $total_digital,
    "Released" => $released_documents
];
$chart_data_json = json_encode($chart_data);

$conn->close();
?>

<div class="stats">
    <?php foreach ($stats as $stat): ?>
        <div class="stat-box">
            <div class="stat-top">
                <p><?= $stat["label"] ?></p>
            </div>
            <div class="stat-bottom">
                <div class="stat-info">
                    <h2><?= $stat["value"] ?></h2>
                    <span class="label"><?= $stat["text"] ?></span>
                </div>
                <div class="icon-container">
                    <img src="picture/<?= $stat["icon"] ?>" alt="<?= $stat["text"] ?> Icon" />
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
include 'server/server.php';

// Initialize counters
$chart_data = [
    'Sketch Completed' => 0,
    'Sketch Pending' => 0,
    'LRA Approval Completed' => 0,
    'LRA Approval Pending' => 0,
    'PSD Approval Completed' => 0,
    'PSD Approval Pending' => 0,
    'CSD Approval Completed' => 0,
    'CSD Approval Pending' => 0
];

// Fetch projects
$sql = "SELECT Approval, ProjectStatus FROM project";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $approval = strtoupper(trim($row['Approval'] ?? ''));
        $status = strtoupper(trim($row['ProjectStatus'] ?? ''));

        $completed = ($status === 'COMPLETED') ? 'Completed' : 'Pending';

        if (empty($approval)) {
            // Sketch Plan
            $chart_data["Sketch $completed"]++;
        } else {
            // Approval types with "Approval" in legend
            if (in_array($approval, ['LRA', 'PSD', 'CSD'])) {
                $chart_data["$approval Approval $completed"]++;
            }
        }
    }
}

$conn->close();
?>

<div class="mid-section">
    <div class="chart-section">
        <h3>PROJECT STATUS</h3>
        <canvas class="piechart" id="docPieChart" data-sketch-completed="<?= $chart_data['Sketch Completed'] ?>"
            data-sketch-pending="<?= $chart_data['Sketch Pending'] ?>"
            data-lra-approval-completed="<?= $chart_data['LRA Approval Completed'] ?>"
            data-lra-approval-pending="<?= $chart_data['LRA Approval Pending'] ?>"
            data-psd-approval-completed="<?= $chart_data['PSD Approval Completed'] ?>"
            data-psd-approval-pending="<?= $chart_data['PSD Approval Pending'] ?>"
            data-csd-approval-completed="<?= $chart_data['CSD Approval Completed'] ?>"
            data-csd-approval-pending="<?= $chart_data['CSD Approval Pending'] ?>">
        </canvas>
    </div>

    <?php
    include 'server/server.php';
    $userRole = $_SESSION['role'] ?? null;
    $userID = $_SESSION['employeeid'] ?? null;
    $recent_activities = [];

    if ($userRole === 'admin') {
        $sql = "SELECT al.ActivityLogID, al.ProjectID, al.DocumentID, al.Status, al.Time, e.EmployeeID, e.JobPosition, CONCAT(SUBSTRING(e.EmpFName,1,1),'*** ',SUBSTRING(e.EmpLName,1,1),'***') AS masked_employee_name, d.DocumentName FROM activity_log al JOIN employee e ON al.EmployeeID=e.EmployeeID LEFT JOIN document d ON al.DocumentID=d.DocumentID ORDER BY al.Time DESC LIMIT 5";
    } else if ($userRole === 'user' && $userID) {
        $sql = "SELECT al.ActivityLogID, al.ProjectID, al.DocumentID, al.Status, al.Time, e.EmployeeID, e.JobPosition, CONCAT(SUBSTRING(e.EmpFName,1,1),'*** ',SUBSTRING(e.EmpLName,1,1),'***') AS masked_employee_name, d.DocumentName FROM activity_log al JOIN employee e ON al.EmployeeID=e.EmployeeID LEFT JOIN document d ON al.DocumentID=d.DocumentID WHERE al.EmployeeID=? ORDER BY al.Time DESC LIMIT 5";
    } else {
        $recent_activities = [];
    }

    if (!empty($sql)) {
        if ($userRole === 'user' && $userID) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $display_info = empty($row['DocumentID']) ? $row['ProjectID'] : $row['DocumentName'];
                $recent_activities[] = [
                    'activity_log_id' => $row['ActivityLogID'],
                    'project_id' => $row['ProjectID'],
                    'document_id' => $row['DocumentID'],
                    'status' => $row['Status'],
                    'time' => $row['Time'],
                    'masked_employee_name' => $row['masked_employee_name'],
                    'JobPosition' => $row['JobPosition'],
                    'display_info' => $display_info
                ];
            }
        }
    }
    $conn->close();
    ?>

    <div class="recent-list">
        <h3>Recent Activity</h3>
        <?php if (empty($recent_activities)): ?>
            <p>No recent activities to display.</p>
        <?php else: ?>
            <?php foreach ($recent_activities as $activity): ?>
                <div class="recent-item">
                    <div class="status"><?= htmlspecialchars(strtoupper($activity['status'])) ?></div>
                    <div class="display-info"><?= htmlspecialchars($activity['display_info']) ?></div>
                    <div class="datetime"><?= date('d M Y H:i', strtotime($activity['time'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>