<link rel="stylesheet" href="css/search.css">

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

// Load Projects
$projectQuery = $conn->query("SELECT ProjectID FROM project ORDER BY ProjectID ASC");
?>

<div class="user-menu-panel" id="userPanel">
    <div class="user-panel-top">
        <div class="user-top-info">
            <p><?= htmlspecialchars($empFName . ' ' . $empLName) ?></p>
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

<div class="topbar">
    <span>Report</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>


<hr class="top-line" />

<div class="search-dropdown">
    <select id="reportType" name="report_type">
        <option value="">Select Report Type</option>
        <option value="Project">Project</option>
        <option value="Original Plan">Original Plan</option>
        <option value="Title">Title</option>
        <option value="Certified Title">Certified Title</option>
        <option value="Reference Plan">Reference Plan</option>
        <option value="Lot Data">Lot Data</option>
        <option value="Technical Description">Technical Description</option>
        <option value="Fieldnotes">Fieldnotes</option>
        <option value="Transmital">Transmital</option>
        <option value="Cadastral Map">Cadastral Map</option>
        <option value="Survey Authority">Survey Authority</option>
        <option value="Blueprint">Blueprint</option>
        <option value="CAD File">CAD File</option>
    </select>
</div>

<div class="search-dropdown">
    <select id="reportProject" name="project_id">
        <option value="">Search Project Name...</option>
        <?php while($p = $projectQuery->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($p['ProjectID']) ?>">
                <?= htmlspecialchars($p['ProjectID']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<p class="note1">Enter a description below.</p>

<!-- Report Form -->
<form id="reportForm" enctype="multipart/form-data">
    <textarea id="reportDescription" name="report_description" placeholder="Enter report description here..."></textarea>
    <div class="report-buttons">
        <button type="submit" id="generateReportBtn">Generate Report</button>
    </div>
</form>

<?php if($jobPosition !== 'CAD Operator' && $jobPosition !== 'Compliance Officer'): ?>
<span class="result">Reports</span>
<div id="liveResults">
    <p>Type a description to begin...</p>
</div>
<?php endif; ?>