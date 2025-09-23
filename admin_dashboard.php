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
body,
html {
    overflow-y: hidden;
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

.recent-list {
    min-width: 40%;
    margin: 20px auto;
    font-family: Arial, sans-serif;
}

.recent-list h3 {
    color: #7B0302;
}

.recent-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #ddd;
}

.employee-info {
    display: flex;
    flex-direction: column;
    font-size: 0.9rem;
    min-width: 180px;
    margin-left: 20px;
}

.employee-info strong {
    font-weight: 900;
}

.employee-role {
    font-size: 0.8rem;
    margin-top: 3px;
}

.status {
    margin-left: 2%;
    flex: 1;
    text-align: left;
    font-size: 0.9rem;
}

.display-info {
    min-width: 25%;
    font-size: 0.7rem;
    text-align: left;
}

.datetime {
    font-size: 0.8rem;
    text-align: right;
    margin-right: 2%;
    font-size: 0.9rem;
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
    <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Dashboard</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />
<div class="welcome" style="font-size: 1.5cqw; color: #7B0302;">Welcome, Admin!</div>


<?php
include 'server/server.php';

// Fetch total projects
$total_projects_query = "SELECT COUNT(*) as total FROM project";
$total_projects_result = $conn->query($total_projects_query);
$total_projects = $total_projects_result->fetch_assoc()['total'];

// Fetch total documents
$total_documents_query = "SELECT COUNT(*) as total FROM document WHERE documentstatus IS NOT NULL";
$total_documents_result = $conn->query($total_documents_query);
$total_documents = $total_documents_result->fetch_assoc()['total'];

// Fetch stored documents
$stored_documents_query = "SELECT COUNT(*) as total FROM document WHERE documentstatus='stored'";
$stored_documents_result = $conn->query($stored_documents_query);
$stored_documents = $stored_documents_result->fetch_assoc()['total'];

// Fetch released documents
$released_documents_query = "SELECT COUNT(*) as total FROM document WHERE documentstatus='released'";
$released_documents_result = $conn->query($released_documents_query);
$released_documents = $released_documents_result->fetch_assoc()['total'];

// Calculate percentages safely
$percentage_stored = $total_documents > 0 ? round(($stored_documents / $total_documents) * 100) : 0;
$percentage_released = $total_documents > 0 ? round(($released_documents / $total_documents) * 100) : 0;

// Prepare stats array
$stats = [
    ["label" => "TOTAL OF PROJECTS", "value" => $total_projects, "icon" => "folder.png", "text" => "PROJECTS"],
    ["label" => "TOTAL OF DOCUMENTS", "value" => $total_documents, "icon" => "File text.png", "text" => "DOCUMENTS"],
    ["label" => "MY STORED DOCUMENTS", "value" => $stored_documents, "percent" => $percentage_stored, "icon" => "Database.png", "text" => "STORED DOCUMENTS"],
    ["label" => "MY RELEASED DOCUMENTS", "value" => $released_documents, "percent" => $percentage_released, "icon" => "Box.png", "text" => "RELEASED DOCUMENTS"]
];

$conn->close();
?>

<div class="stats">
    <?php foreach ($stats as $stat): ?>
    <div class="stat-box">
        <div class="stat-top">
            <p><?= $stat["label"] ?></p>
            <?php if (isset($stat["percent"])): ?>
            <span class="percent"><?= $stat["percent"] ?>%</span>
            <?php endif; ?>
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

<div class="mid-section">

    <div class="chart-section">
        <h3>DOCUMENT MOVEMENT OVER TIME</h3>
        <div class="graph-box">
            <div class="y-labels">
                <div>200</div>
                <div>150</div>
                <div>100</div>
                <div>50</div>
                <div>0</div>
            </div>
            <div class="graph-area">
                <div class="graph-grid"></div>
                <div class="x-labels">
                    <span>January</span><span>March</span><span>May</span>
                    <span>July</span><span>September</span><span>November</span>
                </div>
            </div>
        </div>


        <div class="breakdown-section">
            <h3>DOCUMENT TYPE BREAKDOWN</h3>
            <div class="legend">
                <span><span class="dot" style="background:#8B5E5E"></span>Tax Declaration</span>
                <span><span class="dot" style="background:#C27C7C"></span>Lot Title</span>
                <span><span class="dot" style="background:#D88F8F"></span>Survey Plan</span>
                <span><span class="dot" style="background:#B25454"></span>CAD File</span>
                <span><span class="dot" style="background:#E0BABA"></span>Lot Data</span>
                <span><span class="dot" style="background:#F2DCDC"></span>Others</span>
            </div>
        </div>
    </div>

    <?php
include 'server/server.php';

$userRole = $_SESSION['role'] ?? null;
$userID = $_SESSION['employeeid'] ?? null;

$recent_activities = [];

if ($userRole === 'admin') {
    // Admin sees all activities (limit 5)
    $sql = "
    SELECT 
      al.ActivityLogID,
      al.ProjectID,
      al.DocumentID,
      al.Status,
      al.Time,
      e.EmployeeID,
      e.JobPosition,
      CONCAT(SUBSTRING(e.EmpFName, 1, 1), '*** ', SUBSTRING(e.EmpLName, 1, 1), '***') AS masked_employee_name,
      d.DocumentName
    FROM activity_log al
    JOIN employee e ON al.EmployeeID = e.EmployeeID
    LEFT JOIN document d ON al.DocumentID = d.DocumentID
    ORDER BY al.Time DESC
    LIMIT 5
    ";
} else if ($userRole === 'user' && $userID) {
    // Regular user sees only their own activities (limit 5)
    $sql = "
    SELECT 
      al.ActivityLogID,
      al.ProjectID,
      al.DocumentID,
      al.Status,
      al.Time,
      e.EmployeeID,
      e.JobPosition,
      CONCAT(SUBSTRING(e.EmpFName, 1, 1), '*** ', SUBSTRING(e.EmpLName, 1, 1), '***') AS masked_employee_name,
      d.DocumentName
    FROM activity_log al
    JOIN employee e ON al.EmployeeID = e.EmployeeID
    LEFT JOIN document d ON al.DocumentID = d.DocumentID
    WHERE al.EmployeeID = ?
    ORDER BY al.Time DESC
    LIMIT 5
    ";
} else {
    // No valid role or user id, no activities
    $recent_activities = [];
}

if (!empty($sql)) {
    if ($userRole === 'user' && $userID) {
        // Prepare statement to avoid injection
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
            <div class="status">
                <?= htmlspecialchars(strtoupper($activity['status'])) ?>
            </div>
            <div class="display-info">
                <?= htmlspecialchars($activity['display_info']) ?>
            </div>
            <div class="datetime">
                <?= date('d M Y H:i', strtotime($activity['time'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</div>