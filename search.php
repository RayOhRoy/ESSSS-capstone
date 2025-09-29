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
    <span>Search</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<div class="search-dropdown">
    <select id="documentTypeFilter">
        <option value="">Search for</option>
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

<p class="note">Enter any available information to search for a project or document. You may leave other fields blank.
</p>

<form id="projectForm" enctype="multipart/form-data">
    <div class="form-wrapper">
        <div class="form-grid">
            <!-- Left Column -->
            <div class="column">
                <div class="form-row"><label>Project Name:</label><input id="projectName" name="project_name"
                        type="text" /></div>
                <div class="form-row"><label>Lot Number:</label><input id="lotNumber" name="lot_no" type="text" /></div>
                <div class="form-row"><label>Client First Name:</label><input id="clientFName" name="client_Fname"
                        type="text" /></div>
                <div class="form-row"><label>Client Last Name:</label><input id="clientLName" name="client_Lname"
                        type="text" /></div>
            </div>

            <!-- Right Column -->
            <div class="column">
                <div class="form-row">
                    <label>Survey Type:</label>
                    <select name="survey_type" id="surveyType">
                        <option value="">Select Survey Type</option>
                        <option value="Relocation Survey">Relocation Survey</option>
                        <option value="Verification Survey">Verification Survey</option>
                        <option value="Subdivision Survey">Subdivision Survey</option>
                        <option value="Consolidation Survey">Consolidation Survey</option>
                        <option value="Topographic Survey">Topographic Survey</option>
                        <option value="AS-Built Survey">AS-Built Survey</option>
                        <option value="Sketch Plan / Vicinity Map">Sketch Plan / Vicinity Map</option>
                        <option value="Land Titling">Land Titling</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Province:</label>
                    <select name="province" id="province" onchange="loadMunicipalities()">
                        <option value="">Select Province</option>
                        <option value="Bulacan">Bulacan</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Municipality:</label>
                    <select name="municipality" id="municipality" onchange="loadBarangays()" disabled>
                        <option value="">Select Municipality</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Barangay:</label>
                    <select name="barangay" id="barangay" disabled>
                        <option value="">Select Barangay</option>
                    </select>
                </div>
            </div>

            <div class="qr-preview">
                <button id="qrToggleBtn" type="button" class="fa fa-qrcode"></button>
                <input id="qrInput" type="text" autocomplete="off" style="position:absolute; left:-9999px;" />
                <h4 id="qrStatusText">QR Code Search Disabled</h4>
            </div>

        </div>
    </div>
</form>
<span class="result">
    Results
</span>
<div id="liveResults" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;">
    <p>Provide input or select from the list to initiate a search for matching project data...</p>
</div>

<div id="qrsearchModal" class="newmodal">
    <div class="new-modal-content">
        <span id="closeqrsearchModal">&times;</span>
        <div id="modalBody">
            <div>