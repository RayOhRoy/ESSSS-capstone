<link rel="stylesheet" href="css/edit_project.css">

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
                                <?= $type ?></option>
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
                "FOR PRINT", "FOR DELIVER", "FOR SIGN",
                "FOR ENTRY (PSD)", "FOR ENTRY (CSD)", "FOR ENTRY (LRA)",
                "FOR RESEARCH", "FOR FINAL", "CANCELED",
                "APPROVED", "COMPLETED"
              ];
              foreach ($statusOptions as $status):
                $selected = ($project['ProjectStatus'] === $status) ? 'selected' : '';
                echo "<option value=\"$status\" $selected>$status</option>";
              endforeach;
              ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="surveyStartDate"><span class="required-asterisk">* </span>Survey Start Date:</label>
                        <input type="date" id="surveyStartDate" name="surveyStartDate"
                            value="<?= htmlspecialchars($project['SurveyStartDate']) ?>" readonly />
                    </div>

                    <div class="form-row">
                        <label for="surveyEndDate">Survey End Date:</label>
                        <input type="date" id="surveyEndDate" name="surveyEndDate"
                            value="<?= htmlspecialchars($project['SurveyEndDate']) ?>" readonly />
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