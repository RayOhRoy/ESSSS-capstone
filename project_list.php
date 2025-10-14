<link rel="stylesheet" href="css/project_list.css">

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

<?php
include 'server/server.php'; // db connection

// Get the municipality from GET parameter safely
$municipality = isset($_GET['municipality']) ? $_GET['municipality'] : '';

// Base SQL with JOIN
$sql = "SELECT 
            p.ProjectID,
            p.LotNo,
            p.ClientFName,
            p.ClientLName,
            p.SurveyType,
            p.Agent,
            p.SurveyStartDate,
            p.SurveyEndDate,
            p.Approval,       
            p.RequestType,      
            a.Address AS StreetAddress,
            a.Barangay,
            a.Municipality,
            a.Province
        FROM project p
        JOIN address a ON p.AddressID = a.AddressID";

// Add WHERE clause if municipality is provided
if (!empty($municipality)) {
    $sql .= " WHERE a.Municipality = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $municipality);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // No filter, get all projects
    $result = $conn->query($sql);
}

$projects = [];
$projectIds = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
        $projectIds[] = $row['ProjectID'];
    }
}

// Fetch documents for the filtered projects
$documentsByProject = [];

if (!empty($projectIds)) {
    $placeholders = implode(',', array_fill(0, count($projectIds), '?'));

    // Prepare statement for IN clause with dynamic placeholders
    // Note: mysqli doesn't support binding an array directly; workaround needed
    // We'll dynamically bind params here:

    $types = str_repeat('i', count($projectIds)); // Assuming ProjectID is int

    $stmtDocs = $conn->prepare("SELECT DocumentName, DocumentStatus, ProjectID, DigitalLocation FROM document WHERE ProjectID IN ($placeholders)");

    // Bind params dynamically
    $stmtDocs->bind_param($types, ...$projectIds);

    $stmtDocs->execute();
    $resultDoc = $stmtDocs->get_result();

    while ($doc = $resultDoc->fetch_assoc()) {
        $documentsByProject[$doc['ProjectID']][] = $doc;
    }
}
?>

<?php
function formatAddress($street, $barangay, $municipality, $province)
{
    $parts = array_filter([
        $street,
        $barangay,
        $municipality,
        $province
    ]);
    return implode(', ', $parts);
}
?>

<?php
function maskName($fname, $lname)
{
    $maskedF = strlen($fname) > 0 ? $fname[0] . '***' : '';
    $maskedL = strlen($lname) > 0 ? $lname[0] . '***' : '';
    return $maskedF . ' ' . $maskedL;
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
    <button type="button" id="list-back-btn" class="fa fa-arrow-left" data-page="documents.php"></button>
    <span>
        <?= !empty($municipality) ? htmlspecialchars($municipality) : "Project List" ?>
    </span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<?php
$jobPosition = strtolower($_SESSION['jobposition'] ?? '');
$hideUpdate = ($jobPosition === 'cad operator' || $jobPosition === 'compliance officer');
?>

<table class="projectlist-table" id="projectTable">
    <thead>
        <tr>
            <th>
                <button class="sort-btn active-sort" onclick="sortTable(0, this)">
                    Project Name <i class="fa fa-long-arrow-down" style="margin-left:5px;"></i>
                </button>
            </th>
            <th><button class="sort-btn" onclick="sortTable(1, this)">Client Name</button></th>
            <th><button class="sort-btn" onclick="sortTable(2, this)">Survey Type</button></th>
            <th><button class="sort-btn">Preview</button></th>

            <?php if (!$hideUpdate): ?>
                <th><button class="sort-btn">Update</button></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project): ?>
            <tr onclick="handleRowClick(this)"
                data-projectid="<?= htmlspecialchars($project['ProjectID'], ENT_QUOTES) ?>"
                data-lotno="<?= htmlspecialchars($project['LotNo'], ENT_QUOTES) ?>"
                data-clientfullname="<?= htmlspecialchars($project['ClientFName'] . ' ' . $project['ClientLName'], ENT_QUOTES) ?>"
                data-agent="<?= htmlspecialchars($project['Agent'] ?? 'not available', ENT_QUOTES) ?>"
                data-surveyperiod="<?= htmlspecialchars(
                    date('F j, Y', strtotime($project['SurveyStartDate'])) . ' - ' . date('F j, Y', strtotime($project['SurveyEndDate'])),
                    ENT_QUOTES
                ) ?>"
                data-address="<?= htmlspecialchars(formatAddress(
                    $project['StreetAddress'] ?? '',
                    $project['Barangay'] ?? '',
                    $project['Municipality'] ?? '',
                    $project['Province'] ?? ''
                ), ENT_QUOTES) ?>"
                data-approval="<?= htmlspecialchars($project['Approval'] ?? '', ENT_QUOTES) ?>"
                data-requesttype="<?= htmlspecialchars($project['RequestType'] ?? '', ENT_QUOTES) ?>"
            >
                <td class="row-first"><?= htmlspecialchars($project['ProjectID']) ?></td>
                <td><?= htmlspecialchars(maskName($project['ClientFName'], $project['ClientLName'])) ?></td>
                <td><?= htmlspecialchars($project['SurveyType']) ?></td>
                <td><button class="preview-btn fa fa-eye"></button></td>

                <?php if (!$hideUpdate): ?>
                    <td class="row-last">
                        <button class="update-btn fa fa-edit"
                            data-projectid="<?= htmlspecialchars($project['ProjectID'], ENT_QUOTES) ?>"
                            onclick="redirectToUpdate(this)">
                        </button>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="documentsData" style="display:none;"
    data-documents='<?= htmlspecialchars(json_encode($documentsByProject), ENT_QUOTES, 'UTF-8') ?>'></div>

<div class="floating-add-project" data-page="upload.php">
    <span class="fa fa-plus"></span>
</div>



<!-- Modal Structure -->
<div id="previewModal" class="modal">
    <div class="modal-content">
        <span id="closeModal">&times;</span>
        <div id="modalBody">

            <!-- QR Code & Reference -->
            <div class="qr-section">
                <img src="picture/project_qr.png" alt="QR Code" class="qr-img">
                <p class="preview-projectname">HAG-001</p>
            </div>

            <!-- Project Details -->
            <div class="project-details">
                <p><strong>Lot No.:</strong> LOT L - 11</p>
                <p><strong>Address:</strong> San Miguel, Calumpit, Bulacan</p>
                <p><strong>Survey Type:</strong> Sketch Plan</p>
                <p><strong>Client:</strong> Juan Dela Cruz</p>
                <p><strong>Physical Location:</strong> HAG-101</p>
                <p><strong>Agent:</strong> Juanito Cruz</p>
                <p><strong>Survey Period:</strong> April 3, 2025 - April 10, 2025</p>
            </div>

            <!-- Document Table -->
            <div class="document-table">
                <table>
                    <thead>
                        <tr>
                            <th
                                style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
                                Document Name
                            </th>
                            <th
                                style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
                                Physical Documents
                            </th>
                            <th
                                style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
                                Digital Documents
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Original Plan</td>
                            <td class="status stored">STORED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                        <tr>
                            <td>Lot Title</td>
                            <td class="status released">RELEASED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                        <tr>
                            <td>Ref Plan/Lot Data</td>
                            <td class="status stored">STORED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                        <tr>
                            <td>TD</td>
                            <td class="status stored">STORED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                        <tr>
                            <td>Transmittal</td>
                            <td class="status stored">STORED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                        <tr>
                            <td>Field Notes</td>
                            <td class="status released">RELEASED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                        <tr>
                            <td>Deed of Sale/Transfer</td>
                            <td class="status stored">STORED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                        <tr>
                            <td>Tax Declaration</td>
                            <td class="status stored">STORED</td>
                            <td class="status available">AVAILABLE</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Buttons -->
            <div class="modal-buttons">
                <button class="open-btn">OPEN</button>
            </div>
        </div>
    </div>
</div>