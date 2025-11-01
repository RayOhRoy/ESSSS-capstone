<link rel="stylesheet" href="css/upload.css">

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
    <span>Upload Project</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<input type="hidden" id="generatedProjectId">

<div class="content">
    <form id="projectForm" enctype="multipart/form-data">
        <div class="form-wrapper">
            <div class="form-grid">
                <div class="column">
                    <input type="hidden" id="project_name" name="project_name">

                    <div class="form-row"><label><span class="required-asterisk">* </span>Lot Number:</label><input
                            id="lotNumber" name="lot_no" type="text" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();" required />
                    </div>
                    <div class="form-row"><label><span class="required-asterisk">* </span>Client First
                            Name:</label><input id="clientName" name="client_name" type="text"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();"
                            required /></div>
                    <div class="form-row"><label><span class="required-asterisk">* </span>Client Last
                            Name:</label><input id="clientLastName" name="last_name" type="text"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();"
                            required /></div>

                    <div class="form-row">
                        <label><span class="required-asterisk">* </span>Province:</label>
                        <select name="province" id="province" onchange="loadMunicipalities()">
                            <option value="">Select Province</option>
                            <option value="Bulacan">Bulacan</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label><span class="required-asterisk">* </span>Municipality:</label>
                        <select name="municipality" id="municipality" onchange="loadBarangays()" disabled>
                            <option value="">Select Municipality</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label><span class="required-asterisk">* </span>Barangay:</label>
                        <select name="barangay" id="barangay" disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>

                    <div class="form-row"><label>Street/Subdivision:</label><input name="street" type="text"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" /></div>

                </div>

                <div class="column">
                    <div class="form-row">
                        <label><span class="required-asterisk">* </span>Survey Type:</label>
                        <select name="survey_type" id="surveyType">
                            <option value="">Select Survey Type</option>
                            <option value="Relocation Survey ">Relocation Survey </option>
                            <option value="Verification Survey">Verification Survey</option>
                            <option value="Subdivision Survey ">Subdivision Survey </option>
                            <option value="Consolidation Survey  ">Consolidation Survey </option>
                            <option value="Topographic Survey ">Topographic Survey</option>
                            <option value="AS-Built Survey ">AS-Built Survey</option>
                            <option value="Sketch Plan / Vicinity Map">Sketch Plan / Vicinity Map</option>
                            <option value="Land Titling / Transfer">Land Titling / Transfer</option>
                            <option value="Real Estate">Real Estate</option>
                        </select>
                    </div>

                    <div class="form-row"><label>Agent:</label><input id="agent" name="agent" type="text"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" /></div>

                    <div class="form-row">
                        <label for="approvalStatusThing"><span class="required-asterisk">* </span>Project
                            Status:</label>
                        <select id="projectStatus" name="projectStatus">
                            <option value="">Select Project Status</option>
                            <option value="PENDING">Pending</option>
                            <option value="COMPLETED">Completed</option>
                        </select>
                    </div>

                    <div class="form-row"><label><span class="required-asterisk">* </span>Survey Start
                            Date:</label><input id="startDate" name="survey_start" type="date" /></div>
                    <div class="form-row"><label>Survey End Date:</label><input id="endDate" name="survey_end"
                            type="date" />
                    </div>

                    <div class="form-row">
                        <label for="surveyType"><span class="required-asterisk">* </span>Request Type:</label>
                        <select id="requestType" name="requestType">
                            <option value="For Approval">For Approval</option>
                            <option value="Sketch Plan">Sketch Plan</option>
                        </select>
                    </div>

                    <div id="toBeApprovedBy">
                        <label>To be approved by:</label>
                        <div class="approval-group" style="margin-left: 23.5%">
                            <label><input type="radio" name="approval" value="PSD" checked> PSD (BUREAU)</label>
                            <label><input type="radio" name="approval" value="CSD"> CSD (CENRO)</label>
                            <label><input type="radio" name="approval" value="LRA"> LRA</label>
                        </div>
                    </div>
                </div>

            </div>

            <div class="qr-preview">
                <h4>PROJECT QR CODE</h4>
                <div class="qr-box"></div>
            </div>
        </div>

        <div class="note">Select all that apply and upload digital document if applicable</div>

        <div class="table-container">
            <table class="document-table" id="documentTable">
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Physical Documents</th>
                        <th>Digital Documents</th>
                        <th>QR Code</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Original Plan</td>
                        <td>
                            <input type="checkbox" name="physical[]" value="Original Plan"
                                onchange="toggleStorageStatus(this)">
                            <select class="storage-status" name="status[original_plan]" style="display:none;">
                                <option value="Stored">Stored</option>
                                <option value="Released">Released</option>
                            </select>
                        </td>
                        <td>
                            <div class="digital-cell">
                                <div class="file-list"></div>
                                <label class="attach-icon">
                                    <i class="fa fa-paperclip"></i>
                                    <input type="file" name="digital[original_plan][]" multiple
                                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                        onchange="handleFileUpload(this)" disabled>
                                </label>
                            </div>
                        </td>
                        <td class="qr-code"></td>
                    </tr>

                    <tr>
                        <td>Lot Title</td>
                        <td>
                            <input type="checkbox" name="physical[]" value="Lot Title"
                                onchange="toggleStorageStatus(this)">
                            <select class="storage-status" name="status[lot_title]" style="display:none;">
                                <option value="Stored">Stored</option>
                                <option value="Released">Released</option>
                            </select>
                        </td>
                        <td>
                            <div class="digital-cell">
                                <div class="file-list"></div>
                                <label class="attach-icon">
                                    <i class="fa fa-paperclip"></i>
                                    <input type="file" name="digital[lot_title][]" multiple
                                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                        onchange="handleFileUpload(this)" disabled>
                                </label>
                            </div>
                        </td>
                        <td class="qr-code"></td>
                    </tr>

                    <tr>
                        <td>Deed of Sale</td>
                        <td>
                            <input type="checkbox" name="physical[]" value="Deed of Sale"
                                onchange="toggleStorageStatus(this)">
                            <select class="storage-status" name="status[deed_of_sale]" style="display:none;">
                                <option value="Stored">Stored</option>
                                <option value="Released">Released</option>
                            </select>
                        </td>
                        <td>
                            <div class="digital-cell">
                                <div class="file-list"></div>
                                <label class="attach-icon">
                                    <i class="fa fa-paperclip"></i>
                                    <input type="file" name="digital[deed_of_sale][]" multiple
                                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                        onchange="handleFileUpload(this)" disabled>
                                </label>
                            </div>
                        </td>
                        <td class="qr-code"></td>
                    </tr>

                    <tr>
                        <td>Tax Declaration</td>
                        <td>
                            <input type="checkbox" name="physical[]" value="Tax Declaration"
                                onchange="toggleStorageStatus(this)">
                            <select class="storage-status" name="status[tax_declaration]" style="display:none;">
                                <option value="Stored">Stored</option>
                                <option value="Released">Released</option>
                            </select>
                        </td>
                        <td>
                            <div class="digital-cell">
                                <div class="file-list"></div>
                                <label class="attach-icon">
                                    <i class="fa fa-paperclip"></i>
                                    <input type="file" name="digital[tax_declaration][]" multiple
                                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                        onchange="handleFileUpload(this)" disabled>
                                </label>
                            </div>
                        </td>
                        <td class="qr-code"></td>
                    </tr>

                    <tr>
                        <td>Building Permit</td>
                        <td>
                            <input type="checkbox" name="physical[]" value="Building Permit"
                                onchange="toggleStorageStatus(this)">
                            <select class="storage-status" name="status[building_permit]" style="display:none;">
                                <option value="Stored">Stored</option>
                                <option value="Released">Released</option>
                            </select>
                        </td>
                        <td>
                            <div class="digital-cell">
                                <div class="file-list"></div>
                                <label class="attach-icon">
                                    <i class="fa fa-paperclip"></i>
                                    <input type="file" name="digital[building_permit][]" multiple
                                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                        onchange="handleFileUpload(this)" disabled>
                                </label>
                            </div>
                        </td>
                        <td class="qr-code"></td>
                    </tr>

                    <tr>
                        <td>Authorization Letter</td>
                        <td>
                            <input type="checkbox" name="physical[]" value="Authorization Letter"
                                onchange="toggleStorageStatus(this)">
                            <select class="storage-status" name="status[authorization_letter]" style="display:none;">
                                <option value="Stored">Stored</option>
                                <option value="Released">Released</option>
                            </select>
                        </td>
                        <td>
                            <div class="digital-cell">
                                <div class="file-list"></div>
                                <label class="attach-icon">
                                    <i class="fa fa-paperclip"></i>
                                    <input type="file" name="digital[authorization_letter][]" multiple
                                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                        onchange="handleFileUpload(this)" disabled>
                                </label>
                            </div>
                        </td>
                        <td class="qr-code"></td>
                    </tr>

                    <tr>
                        <td>Others</td>
                        <td>
                            <input type="checkbox" name="physical[]" value="Others"
                                onchange="toggleStorageStatus(this)">
                            <select class="storage-status" name="status[others]" style="display:none;">
                                <option value="Stored">Stored</option>
                                <option value="Released">Released</option>
                            </select>
                        </td>
                        <td>
                            <div class="digital-cell">
                                <div class="file-list"></div>
                                <label class="attach-icon">
                                    <i class="fa fa-paperclip"></i>
                                    <input type="file" name="digital[others][]" multiple
                                        accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                                        onchange="handleFileUpload(this)" disabled>
                                </label>
                            </div>
                        </td>
                        <td class="qr-code"></td>
                    </tr>
                </tbody>

            </table>
        </div>


        <div class="footer-buttons">
            <button type="button" id="generateQRBtn" class="btn-red" onclick="toggleGenerateQR()">Generate QR
                Code</button>
            <button type="button" id="uploadBtn" class="btn-grey" onclick="submitForm()" disabled>Upload</button>
        </div>
    </form>
</div>

<div id="qrModal" class="qr-modal">
    <span class="close">&times;</span>
    <img id="qrModalImg" class="qr-modal-content">
</div>