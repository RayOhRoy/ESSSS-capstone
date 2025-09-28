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

    select,
    input[type="text"],
    input[type="date"] {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        margin-bottom: 1%;
    }

    .form-wrapper {
        display: flex;
        justify-content: space-between;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        flex: 1;
    }

    .form-row label {
        width: 140px;
        font-size: 13px;
        font-weight: 500;
        color: #333;
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

    .note {
        margin-top: 20px;
        font-style: italic;
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
    }

    .footer-buttons {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    /* --- Digital Document column --- */
    .digital-cell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
    }

    .upload-form {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-width: 200px;
    }

    .file-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        flex: 1;
    }

    .file-preview {
        display: flex;
        align-items: center;
        gap: 4px;
        background: #f1f1f1;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .remove-file {
        cursor: pointer;
        color: red;
        font-weight: bold;
    }

    .attach-icon {
        cursor: pointer;
        font-size: 18px;
        flex-shrink: 0;
    }

    .hidden-file {
        display: none;
    }

    .attach-icon input[type="file"] {
        display: none;
    }

    .attach-icon i {
        transition: transform 0.2s ease, color 0.2s ease;
        display: none;
    }

    .attach-icon:hover i {
        transform: scale(1.2);
        color: #7B0302;
    }


    .table-container {
        max-height: 18rem;
        overflow-y: auto;
        overflow-x: auto;
    }

    .document-table thead th {
        position: sticky;
        top: 0;
        background: #fafafa;
        z-index: 2;
    }

    .document-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 13px;
        background-color: #fafafa;
        table-layout: fixed;
    }

    .document-table th,
    .document-table td {
        border: 1px solid #ccc;
        padding: 6px 10px;
        text-align: center;
        vertical-align: middle;
        word-wrap: break-word;
    }

    /* column widths */
    .document-table th:nth-child(1),
    .document-table td:nth-child(1) {
        width: 20%;
    }

    .document-table th:nth-child(2),
    .document-table td:nth-child(2) {
        width: 10%;
    }

    .document-table th:nth-child(3),
    .document-table td:nth-child(3) {
        width: 55%;
    }

    .document-table th:nth-child(4),
    .document-table td:nth-child(4) {
        width: 15%;
    }

    /* Custom radio base */
    .approval-group input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #7B0302;
        border-radius: 50%;
        cursor: pointer;
        position: relative;
    }

    /* Fully filled when checked */
    .approval-group input[type="radio"]:checked {
        background-color: #7B0302;
    }

    /* --- Custom checkbox --- */
    input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #7B0302;
        border-radius: 4px;
        /* square */
        cursor: pointer;
        position: relative;
    }

    input[type="checkbox"]:checked {
        background-color: #7B0302;
    }

    /* Optional: white checkmark inside checkbox */
    input[type="checkbox"]:checked::after {
        content: "✔";
        color: white;
        font-size: 0.6cqw;
        position: absolute;
        top: 0;
        left: 2px;
    }

    .btn-grey {
        background-color: gray;
        pointer-events: none;
    }

    .btn-cancel {
        background-color: #6c757d;
    }


    /* Modal background */
    .qr-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.3);
        /* smaller opacity for less harsh background */
        justify-content: center;
        align-items: center;
    }

    /* Modal content (QR image) */
    .qr-modal-content {
        min-width: 300px;
        /* bigger image for document QR */
        min-height: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .qr-preview {
        margin-left: 40px;
        text-align: center;
    }

    .qr-preview h4 {
        margin-bottom: 20px;
        font-size: 14px;
    }

    .qr-box {
        position: relative;
        width: 200px;
        height: 200px;
        border: 1px solid #ccc;
        /* optional */
        background: #800000;
        overflow: hidden;
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qr-box::before {
        content: "";
        width: 80%;
        height: 80%;
        border: 2px solid white;
    }

    .qr-box img {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 80%;
        /* smaller than container */
        height: 80%;
        transform: translate(-50%, -50%);
        /* center */
        z-index: 10;
    }

    .storage-status {
        margin-left: 8px;
        padding: 2px 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 60%;
        font-size: 0.6cqw;
    }

    #update-back-btn {
        font-size: 2vw;
        border: none;
        color: #7B0302;
        cursor: pointer;
        transition: color 0.3s;
    }

    #update-back-btn:hover {
        filter: brightness(1.25);
        transition: filter 0.2s ease;
        background-color: transparent;
    }

    #update-edit-btn,
    #update-save-btn {
        width: 50%;
        padding: 1.11vh 0.83vw;
        background-color: #7B0302;
        /* Dark red */
        color: #fff;
        border: none;
        border-radius: 0.26vw;
        cursor: pointer;
        margin-top: 2vh;
        transition: filter 0.2s ease, transform 0.2s ease, background-color 0.2s ease;
    }

    #update-edit-btn:hover,
    #update-save-btn:hover {
        filter: brightness(0.85);
        /* Darken */
        background-color: #5c0202;
        /* Darker red on hover */
    }

    /* Save button hidden by default */
    #update-save-btn {
        display: none;
    }

    /* Gray cancel button */
    #update-edit-btn.btn-gray {
        background-color: #6c757d;
        /* Bootstrap gray */
        color: #fff;
    }

    #update-edit-btn.btn-gray:hover {
        filter: brightness(0.85);
        /* Darken */
        background-color: #5a6268;
        /* Darker gray on hover */
        transition: filter 0.2s ease, background-color 0.2s ease;
    }


    .content {
        padding-bottom: 2.5%;

    }

    .hidden-file {
        display: none;

    }

    .remove-icon {
        color: red;
        cursor: pointer;
        font-weight: bold;
        margin-left: 5px;
        user-select: none;
    }

    .required-asterisk {
        color: red;
        font-weight: bold;
    }
</style>

<?php
include 'server/server.php'; // DB connection

if (!isset($_GET['projectId'])) {
    die("Project ID not provided.");
}

$projectId = $_GET['projectId'];

// --- Fetch Project Data ---
$sql = "SELECT 
            p.ProjectID,
            p.LotNo,
            p.ClientFName,
            p.ClientLName,
            p.SurveyType,
            p.SurveyStartDate,
            p.SurveyEndDate,
            p.Agent,
            p.RequestType,
            p.Approval,
            p.ProjectQR,
            a.Province,
            a.Municipality,
            a.Barangay,
            a.Address,
            p.ProjectStatus
        FROM project p
        JOIN address a ON p.AddressID = a.AddressID
        WHERE p.ProjectID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $projectId);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

$requestType = $project['RequestType'];
$approvalType = $project['Approval'];

// Determine docsToRender based on requestType and approvalType
if ($requestType === "For Approval" && $approvalType === "PSD") {
    $docsToRender = [
        "Original Plan",
        "Certified Title",
        "Reference Plan",
        "Lot Data",
        "TD",
        "Transmital",
        "Fieldnotes",
        "Tax Declaration",
        "Blueprint",
        "CAD File",
        "Others"
    ];
} else if ($requestType === "For Approval" && $approvalType === "CSD") {
    $docsToRender = [
        "Original Plan",
        "3 BP",
        "Reference Plan",
        "Lot Data",
        "CM",
        "TD",
        "Transmital",
        "Fieldnotes",
        "Tax Declaration",
        "Survey Authority",
        "Blueprint",
        "CAD File",
        "Others"
    ];
} else if ($requestType === "For Approval" && $approvalType === "LRA") {
    $docsToRender = [
        "Original Plan",
        "Certified Title",
        "Reference Plan",
        "Lot Data",
        "TD",
        "Fieldnotes",
        "Blueprint",
        "CAD File",
        "Others"
    ];
} else if ($requestType === "Sketch Plan") {
    $docsToRender = [
        "Original Plan",
        "Xerox Title",
        "Reference Plan",
        "Lot Data",
        "Tax Declaration",
        "Blueprint",
        "CAD File",
        "Others"
    ];
} else {
    $docsToRender = [
        "Failed to load, refresh page.",
    ];
}

// --- Fetch Related Documents ---
$documents = [];
$docSql = "SELECT DocumentType, DocumentStatus, DocumentQR, DigitalLocation 
           FROM document 
           WHERE ProjectID = ?";
$docStmt = $conn->prepare($docSql);
$docStmt->bind_param("s", $projectId);
$docStmt->execute();
$docResult = $docStmt->get_result();

while ($docRow = $docResult->fetch_assoc()) {
    $typeKey = strtolower(str_replace(' ', '_', $docRow['DocumentType']));
    $documents[$typeKey] = $docRow;
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
    <button type="button" id="update-back-btn" class="fa fa-arrow-left"></button>
    <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Update <?= htmlspecialchars($projectId) ?></span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<div class="content">
    <form id="update_projectForm">

        <input type="hidden" id="projectId" name="projectID" value="<?= htmlspecialchars($project['ProjectID']) ?>" />

        <div class="form-wrapper">
            <div class="form-grid">
                <div class="column">

                    <div class="form-row">
                        <label for="lotNumber"><span class="required-asterisk">* </span>Lot Number:</label>
                        <input type="text" id="lotNumber" name="lotNumber"
                            value="<?= htmlspecialchars($project['LotNo']) ?>" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();" required readonly />
                    </div>

                    <div class="form-row">
                        <label for="clientFirstName"><span class="required-asterisk">* </span>Client First Name:</label>
                        <input type="text" id="clientFirstName" name="clientFirstName"
                            value="<?= htmlspecialchars($project['ClientFName']) ?>" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();" required readonly />
                    </div>

                    <div class="form-row">
                        <label for="clientLastName"><span class="required-asterisk">* </span>Client Last Name:</label>
                        <input type="text" id="clientLastName" name="clientLastName"
                            value="<?= htmlspecialchars($project['ClientLName']) ?>" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();" required readonly />
                    </div>

                    <div class="form-row">
                        <label for="province"><span class="required-asterisk">* </span>Province:</label>
                        <select id="province" name="province" disabled onchange="handleProvinceChange()">
                            <option value="Bulacan" <?= ($project['Province'] === 'Bulacan') ? 'selected' : '' ?>>
                                Bulacan</option>
                            <!-- Add more provinces here if needed -->
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="municipality"><span class="required-asterisk">* </span>Municipality:</label>
                        <select id="municipality" name="municipality" disabled onchange="handleMunicipalityChange()">
                            <option selected><?= htmlspecialchars($project['Municipality']) ?></option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="barangay"><span class="required-asterisk">* </span>Barangay:</label>
                        <select id="barangay" name="barangay" disabled>
                            <option selected><?= htmlspecialchars($project['Barangay']) ?></option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="address">Street/Subdivision:</label>
                        <input type="text" id="address" name="address"
                            value="<?= htmlspecialchars($project['Address']) ?>" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();" required readonly />
                    </div>

                </div>

                <div class="column">
                    <div class="form-row">
                        <label for="surveyType"><span class="required-asterisk">* </span>Survey Type:</label>
                        <select id="surveyType" name="surveyType" disabled>
                            <?php
                            $types = ["Relocation Survey", "Verification Survey", "Subdivision Survey", "Consolidation Survey", "Topographic Survey", "AS-Built Survey", "Sketch Plan / Vicinity Map", "Land Titling/ Transfer", "Real Estate"];
                            foreach ($types as $type): ?>
                                <option value="<?= $type ?>" <?= ($project['SurveyType'] === $type) ? 'selected' : '' ?>>
                                    <?= $type ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="agent">Agent:</label>
                        <input type="text" id="agent" name="agent" value="<?= htmlspecialchars($project['Agent']) ?>"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" required
                            readonly />
                    </div>

                    <div class="form-row">
                        <label for="projectStatus"><span class="required-asterisk">* </span>Status:</label>
                        <select id="projectStatus" name="projectStatus" disabled>
                            <?php
                            $statusOptions = [
                                "FOR PRINT",
                                "FOR DELIVER",
                                "FOR SIGN",
                                "FOR ENTRY (PSD)",
                                "FOR ENTRY (CSD)",
                                "FOR ENTRY (LRA)",
                                "FOR RESEARCH",
                                "FOR FINAL",
                                "CANCELED",
                                "APPROVED",
                                "COMPLETED"
                            ];
                            foreach ($statusOptions as $status):
                                $selected = ($project['ProjectStatus'] === $status) ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>$status</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>

                    <?php
                    // Prepare sanitized dates for the form
                    $surveyStartDate = ($project['SurveyStartDate'] ?? '') === '0000-00-00' ? '' : $project['SurveyStartDate'];
                    $surveyEndDate = ($project['SurveyEndDate'] ?? '') === '0000-00-00' ? '' : $project['SurveyEndDate'];
                    ?>

                    <div class="form-row">
                        <label for="surveyStartDate"><span class="required-asterisk">* </span>Survey Start Date:</label>
                        <input type="date" id="surveyStartDate" name="surveyStartDate"
                            value="<?= htmlspecialchars($surveyStartDate) ?>" readonly />
                    </div>

                    <div class="form-row">
                        <label for="surveyEndDate">Survey End Date:</label>
                        <input type="date" id="surveyEndDate" name="surveyEndDate"
                            value="<?= htmlspecialchars($surveyEndDate) ?>" readonly />
                    </div>


                    <div class="form-row">
                        <label for="requestType"><span class="required-asterisk">* </span>Request Type:</label>
                        <select id="requestType" name="requestType" disabled>
                            <option <?= ($requestType === 'For Approval') ? 'selected' : '' ?>>For Approval</option>
                            <option <?= ($requestType === 'Sketch Plan') ? 'selected' : '' ?>>Sketch Plan</option>
                        </select>
                    </div>

                    <div id="toBeApprovedBy" style="<?= ($requestType === 'Sketch Plan') ? 'display:none;' : '' ?>">
                        <label>To be approved by:</label>
                        <div class="approval-group">
                            <?php
                            $approvals = [
                                'PSD' => 'PSD (BUREAU)',
                                'CSD' => 'CSD (CENRO)',
                                'LRA' => 'LRA'
                            ];
                            foreach ($approvals as $value => $label): ?>
                                <label for="approval_<?= strtolower($value) ?>">
                                    <input type="radio" id="approval_<?= strtolower($value) ?>" name="approval"
                                        value="<?= $value ?>" disabled <?= ($approvalType === $value) ? 'checked' : '' ?> />
                                    <?= $label ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qr-preview">
                <h4>PROJECT QR CODE</h4>
                <div class="qr-box">
                    <?php if (!empty($project['ProjectQR'])): ?>
                        <img src="<?= htmlspecialchars($project['ProjectQR']) ?>" alt="QR Code" />
                    <?php else: ?>
                        <span style="color:white;">No QR Code</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="note">Attached documents:</div>

        <!-- Document Table -->
        <div class="table-container">
            <table class="document-table">
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Physical Documents</th>
                        <th>Digital Documents</th>
                        <th>QR Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($docsToRender as $docLabel):
                        $key = strtolower(str_replace([' ', '/'], ['_', ''], $docLabel));
                        $doc = $documents[$key] ?? null;

                        $isChecked = $doc ? 'checked' : '';
                        $status = $doc['DocumentStatus'] ?? '';
                        $qr = $doc['DocumentQR'] ?? '';
                        $fileName = $doc && $doc['DigitalLocation'] ? basename($doc['DigitalLocation']) : null;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($docLabel) ?></td>

                            <td>
                                <input type="checkbox" disabled <?= $isChecked ?> />
                            </td>

                            <td>
                                <div class="digital-cell">
                                    <div class="file-list">
                                        <?php if ($fileName): ?>
                                            <span class="existing-file">
                                                <?= htmlspecialchars($fileName) ?>
                                            </span>
                                            <i class="no-file" style="display:none;">No file</i>
                                        <?php else: ?>
                                            <span class="existing-file" style="display:none;"></span>
                                            <i class="no-file">No file</i>
                                        <?php endif; ?>
                                    </div>
                                    <label class="attach-icon" title="Attach file"
                                        style="display:none; cursor:pointer; font-size: 15px;">
                                        📎
                                        <input type="file" name="digital_<?= $key ?>" class="hidden-file" multiple
                                            accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.dwg"
                                            onchange="uploadFile(this, '<?= $key ?>')" style="display:none;" />
                                    </label>
                                </div>
                            </td>


                            <td class="qr-code">
                                <?php if (!empty($qr)): ?>
                                    <span class="view-qr-text" style="cursor:pointer;color:#7B0302;text-decoration:underline;"
                                        onclick="showQRPopup('<?= htmlspecialchars($qr) ?>')">
                                        View
                                    </span>
                                <?php else: ?>
                                    <span style="color:gray; font-style: italic;"></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="footer-buttons">
            <button type="button" id="update-save-btn" class="btn-red" style="display:none;">Save Changes</button>
            <button type="button" id="update-edit-btn" class="btn-red" onclick="toggleEditSave()">Edit</button>
        </div>
    </form>
</div>

<div id="qrModal" class="qr-modal">
    <span class="close" onclick="closeQRPopup()">&times;</span>
    <img id="qrModalImg" class="qr-modal-content" alt="QR Code Image">
</div>