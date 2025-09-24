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

select,
input[type="text"],
input[type="date"] {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    margin-bottom: 1%;
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

.document-table {
    width: 80%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 13px;
    background-color: #fafafa;
}

.document-table th,
.document-table td {
    border: 1px solid #ccc;
    padding: 6px 10px;
    text-align: center;
    vertical-align: middle;
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

.content {
    padding-bottom: 2.5%;
}

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

/* --- Scrollable table container --- */
.table-container {
    /* max-height: 18rem; */
    overflow-y: auto;
    overflow-x: auto;
}

/* keep header visible while scrolling */
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

button {
    width: 100%;
    padding: 1.11vh 0.83vw;
    background-color: #7B0302;
    color: #fff;
    border: none;
    border-radius: 0.26vw;
    cursor: pointer;
    margin-top: 2vh;
}

.required-asterisk {
    color: red;
    font-weight: bold;
}

@media (max-width: 1080px) {
    .form-grid {
        display: flex;
        flex-direction: column;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        flex: 1;
    }

    .topbar span {
        font-size: 70px;
        color: #7B0302;
        font-weight: 700;
        padding: 20px 0px 20px 0px;
    }

    .qr-preview {
        text-align: center;
    }

    select,
    input[type="text"],
    input[type="date"] {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        margin-bottom: 5%;
        height: 70px;
        margin-top: 5%;
        font-size: 30px;
        width: auto;
    }


    .form-row label {
        width: 140px;
        font-size: 26px;
        font-weight: 700;
        color: #333;
    }

    .form-wrapper {
        justify-content: space-between;
        display: flex;
        flex-direction: column;
    }

    #toBeApprovedBy label {
        font-size: 35px;
        padding: 20px 10px 80px 0px;

    }

    .qr-preview h4 {
        margin-bottom: 20px;
        font-size: 50px;
    }

    .qr-box {
        position: relative;
        width: 400px;
        height: 400px;
        border: 1px solid #ccc;
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
        height: 80%;
        transform: translate(-50%, -50%);
        z-index: 10;
    }

    .note {
        margin-top: 20px;
        font-style: italic;
        font-size: 30px;
        color: #666;
        margin-bottom: 10px;
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
        font-size: 25px;
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
        width: 50px;
        height: 100px;

    }

    .document-table th:nth-child(1),
    .document-table td:nth-child(1) {
        width: 18%;
    }

    .document-table th:nth-child(2),
    .document-table td:nth-child(2) {
        width: 15%;
    }

    .document-table th:nth-child(3),
    .document-table td:nth-child(3) {
        width: 18%;
    }

    .document-table th:nth-child(4),
    .document-table td:nth-child(4) {
        width: 9%;
    }

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

    input[type="checkbox"] {
        width: 40px;
        height: 40px;
    }

    input[type="checkbox"]:checked::after {
        font-size: 2.6cqw;
    }

    .storage-status {
        margin-left: 8px;
        padding: 2px 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 70%;
        font-size: 2.6cqw;
    }

    .attach-icon {
        cursor: pointer;
        font-size: 30px;
        flex-shrink: 0;
    }

    button {
        font-size: 30px;
    }

    .fa.fa-user-circle {
        font-size: 70px;
        color: #7B0302;
    }
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
                            <option value="FOR PRINT">FOR PRINT</option>
                            <option value="FOR DELIVER">FOR DELIVER</option>
                            <option value="FOR SIGN">FOR SIGN</option>
                            <option value="FOR ENTRY (PSD)">FOR ENTRY (PSD)</option>
                            <option value="FOR ENTRY (CSD)">FOR ENTRY (CSD)</option>
                            <option value="FOR ENTRY (LRA)">FOR ENTRY (LRA)</option>
                            <option value="FOR RESEARCH">FOR RESEARCH</option>
                            <option value="FOR FINAL">FOR FINAL</option>
                            <option value="CANCELED">CANCELED</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="COMPLETED">COMPLETED</option>
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