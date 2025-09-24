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

.floating-add-project {
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

.floating-add-project:hover {
    transform: scale(1.2);
    /* increase size by 20% */
}

.projectlist-table {
    width: 100%;
    border-collapse: collapse;
}

.projectlist-table th,
.projectlist-table td {
    padding: 8px;
    text-align: left;
}

.projectlist-table td {
    text-align: center;
    /* center table body text */
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

.preview-btn {
    color: black;
    cursor: pointer;
    font-size: 1.25cqw;
    transition: color 0.3s;
    border: none;
    background-color: transparent;
}

.preview-btn:hover {
    color: #7B0302;
    transform: scale(1.05);
    transition: scale 0.2s ease;
    background-color: transparent;
}

.update-btn {
    color: black;
    cursor: pointer;
    font-size: 1.25cqw;
    transition: color 0.3s;
    border: none;
    background-color: transparent;
}

.update-btn:hover {
    color: #7B0302;
    transform: scale(1.05);
    transition: scale 0.2s ease;
    background-color: transparent;
}

#previewModal {
    display: none;
    /* visible */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

#previewModal .modal-content {
    background: white;
    padding: 2vw;
    border-radius: 1vw;
    max-width: 600px;
    width: 90%;
    position: fixed;
    /* fixed so it stays in viewport */
    top: 50%;
    /* vertical center */
    left: 50%;
    /* horizontal center */
    transform: translate(-50%, -50%);
    /* center exactly */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
}

/* Close button */
#previewModal #closeModal {
    position: absolute;
    top: 1vw;
    right: 1vw;
    cursor: pointer;
    font-size: 2cqw;
    color: #7B0302;
    transition: transform 0.2s ease, color 0.2s ease;
}

#previewModal #closeModal:hover {
    color: #a10000;
    transform: scale(1.2);
}

/* QR Code section */
.qr-section {
    text-align: center;
    margin-bottom: 2vh;
}

.qr-img {
    width: 120px;
    height: 120px;
}

.qr-code-text {
    font-weight: bold;
    margin-top: 1vh;
}

/* Project details */
.project-details p {
    font-size: 0.85cqw;
    margin: 0.5vh 0;
    color: black;
}

.document-table {
    width: 100%;
    overflow-x: auto;
    /* enables horizontal scroll on smaller screens */
}

.document-table table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5vh 0;
    font-size: 0.7cqw;
    /* smaller font */
    table-layout: fixed;
    /* make columns distribute evenly */
}

.document-table th,
.document-table td {
    border: 1px solid #ccc;
    padding: 0.5vh 0.3vw;
    text-align: center;
    word-wrap: break-word;
    white-space: normal;
}


.status {
    font-weight: bold;
    border-radius: 1vw;
    /* smaller pill shape */
    padding: 0.2vh 0.6vw;
    /* reduced padding */
    font-size: 0.65cqw;
    /* optional: slightly smaller text */
    text-align: center;
    min-width: 60px;
    /* optional: helps maintain pill look */
}


.status.stored {
    background-color: #7B0302;
    color: white;
}

.status.released {
    background-color: #c2c2c2;
    color: #7B0302;
}

.status.available {
    background-color: #7B0302;
    color: #fff;
}

/* Buttons */
.modal-buttons {
    display: flex;
    justify-content: space-around;
    margin-top: 2vh;
}

.open-btn,
.close-btn {
    background-color: #7B0302;
    color: white;
    padding: 0.5vw 2vw;
    border: none;
    border-radius: 0.5vw;
    font-size: 1cqw;
    cursor: pointer;
}

.open-btn:hover,
.close-btn:hover {
    background-color: #a10000;
}

.qr-section img {
    min-width: 250px;
    min-height: 250px;
}

.qr-section p {
    font-size: 1cqw;
    font-weight: 700;
}

#projectTable tbody tr {
    cursor: pointer;
    border-radius: 10cqw;
}

#projectTable tbody tr:hover {
    background-color: #ecdedeff;
    transition: background-color 0.2s;
    border-radius: 10cqw;
}

.row-first {
    border-top-left-radius: 5cqw;
    border-bottom-left-radius: 5cqw;
}

.row-last {
    border-top-right-radius: 5cqw;
    border-bottom-right-radius: 5cqw;
}

#list-back-btn {
    font-size: 2vw;
    border: none;
    color: #7B0302;
    cursor: pointer;
    transition: color 0.3s;
}

#list-back-btn:hover {
    filter: brightness(1.25);
    transform: scale(1.05);
    transition: filter 0.2s ease;
    background-color: transparent;
}

@media (max-width: 1080px) {
    .floating-add-project {
        width: 150px;
        height: 150px;
    }

    .fa.fa-plus {
        font-size: 4.5cqw;
        color: white;
    }

    .dropdown-menu {
        margin-top: 250px;
    }

    .dropdown-menu a {
        font-size: 40px;
        width: 250px;
        height: auto;
    }

    .topbar span {
        font-size: 70px;
        color: #7B0302;
        font-weight: 700;
        padding: 20px 0px 20px 0px;
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
        font-size: 20px;
    }

    .sort-btn.active-sort {
        background-color: #7B0302;
        color: white;
        border-radius: 4cqw;
    }

    .projectlist-table td {
        text-align: center;
        /* center table body text */
        padding: 8px;
        font-size: 25px;
    }

    .fa.fa-bell-o {
        font-size: 70px;
        color: #7B0302;
    }

    .fa.fa-user-circle {
        font-size: 70px;
        color: #7B0302;
        "

    }

    #previewModal .modal-content {
        max-width: 900px;
        width: 100%;
        position: absolute;
        /* fixed so it stays in viewport */
        top: 50%;
        /* vertical center */
        left: 50%;
        height: 1580px;
        /* horizontal center */
    }

    .document-table {
        width: 100%;
        overflow-x: auto;
        height: 800px;
        position: absolute;
        top: 700px;
        left: -.2px;
    }

    .document-table td:first-child {
        text-align: left;
        font-weight: 500;
        font-size: 2cqw;
    }

    .open-btn,
    .close-btn {

        width: 20%;
        border: none;
        border-radius: 0.5vw;
        font-size: 4cqw;
        cursor: pointer;
        position: absolute;
        top: 94%;
    }

    .qr-section {
        text-align: center;
        margin-bottom: 2vh;
    }

    .qr-section img {
        min-width: 380px;
        min-height: 380px;
    }

    .qr-section p {
        font-size: 4cqw;
    }

    .qr-img {
        width: 270px;
        height: 270px;
    }

    .qr-code-text {
        font-weight: bold;
        margin-top: 3vh;
    }

    .project-details p {
        font-size: 2cqw;
        margin: 0.5vh 0;
        color: black;
    }

    .document-table th {
        font-size: 2cqw;
    }

    .status.stored {
        background-color: #7B0302;
        color: white;
        font-size: 2cqw;
    }

    .status.released {
        background-color: #c2c2c2;
        color: #7B0302;
        font-size: 2cqw;
    }

    .status.available {
        background-color: #7B0302;
        color: #fff;
        font-size: 2cqw;
    }

    #previewModal #closeModal {
        top: -3vw;
        right: 1vw;
        font-size: 9cqw;

    }
}
</style>

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
function formatAddress($street, $barangay, $municipality, $province) {
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
function maskName($fname, $lname) {
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

<table class="projectlist-table" id="projectTable">
    <thead>
        <tr>
            <th><button class="sort-btn active-sort" onclick="sortTable(0, this)">Project Name <i
                        class="fa fa-long-arrow-down" style="margin-left:5px;"></i></button></th>
            <th><button class="sort-btn" onclick="sortTable(1, this)">Client Name</button></th>
            <th><button class="sort-btn" onclick="sortTable(2, this)">Survey Type</button></th>
            <th><button class="sort-btn">Preview</button></th>
            <th><button class="sort-btn">Update</button></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project): ?>
        <tr ondblclick="handleRowDoubleClick(this)" onclick="highlightRow(this)"
            data-projectid="<?= htmlspecialchars($project['ProjectID'], ENT_QUOTES) ?>"
            data-lotno="<?= htmlspecialchars($project['LotNo'], ENT_QUOTES) ?>"
            data-clientfullname="<?= htmlspecialchars($project['ClientFName'] . ' ' . $project['ClientLName'], ENT_QUOTES) ?>"
            data-agent="<?= htmlspecialchars($project['Agent'] ?? 'not available', ENT_QUOTES) ?>" data-surveyperiod="<?= htmlspecialchars(
        date('F j, Y', strtotime($project['SurveyStartDate'])) . ' - ' . date('F j, Y', strtotime($project['SurveyEndDate'])),
        ENT_QUOTES
    ) ?>" data-address="<?= htmlspecialchars(formatAddress(
        $project['StreetAddress'] ?? '',
        $project['Barangay'] ?? '',
        $project['Municipality'] ?? '',
        $project['Province'] ?? ''
    ), ENT_QUOTES) ?>" data-approval="<?= htmlspecialchars($project['Approval'] ?? '', ENT_QUOTES) ?>"
            data-requesttype="<?= htmlspecialchars($project['RequestType'] ?? '', ENT_QUOTES) ?>">
            <td class="row-first"><?= htmlspecialchars($project['ProjectID']) ?></td>
            <td><?= htmlspecialchars(maskName($project['ClientFName'], $project['ClientLName'])) ?></td>
            <!-- Municipality data cell removed -->
            <td><?= htmlspecialchars($project['SurveyType']) ?></td>
            <td><button class="preview-btn fa fa-eye"></button></td>
            <td class="row-last">
                <button class="update-btn fa fa-edit"
                    data-projectid="<?= htmlspecialchars($project['ProjectID'], ENT_QUOTES) ?>"
                    onclick="redirectToUpdate(this)">
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>

</table>

<div id="documentsData" style="display:none;"
    data-documents='<?= htmlspecialchars(json_encode($documentsByProject), ENT_QUOTES, 'UTF-8') ?>'></div>

<div class="floating-add-project" data-page="upload.php">
    <span class="fa fa-plus" style="font-size: 1.5cqw; color: white;"></span>
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
            <div class="document-table" style="max-height: 10vw; min-width: 100%; overflow-y: auto;">
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