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

    .qr-search-content {
        border: 1px solid #EC221F;
        background: #FEE9E7;
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
    }

    .newmodal {
        display: none;
        /* visible */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }

    .newmodal .new-modal-content {
        border: 1px solid #EC221F;
        background: #FEE9E7;
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

    .oldmodal .old-modal-content {
        border: 1px solid #EC221F;
        background: #FEE9E7;
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
    .newmodal #closeqrsearchModal {
        opacity: 0;
        position: absolute;
        top: 1vw;
        right: 1vw;
        cursor: pointer;
        font-size: 2cqw;
        color: #7B0302;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .newmodal #closeqrsearchModal:hover {
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
        justify-content: center;
        /* center all buttons */
        gap: 0.5vw;
        /* reduce space between buttons */
        margin-top: 1vh;
        /* optional: reduce top margin */
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

    .close-btn {
        background-color: #C2C2C2;
        color: #7B0302;
    }

    .open-btn:hover,
    .close-btn:hover {
        filter: brightness(1.1);
        /* subtle visual lift */
    }

    .qr-section img {
        min-width: 250px;
        min-height: 250px;
    }

    .qr-section p {
        font-size: 1cqw;
        font-weight: 700;
    }

    .preview-projectname {
        margin-top: 5%;
    }

    .qr-indicator {
        display: flex;
        align-items: center;
        gap: 0.5vw;
    }

    .qr-indicator-title {
        color: #900B09;
        font-size: 1cqw;
        font-weight: 650;
    }

    .qr-indicator-text {
        color: #900B09;
        margin-left: 1.4vw;
    }

    select,
    input[type="text"],
    input[type="date"] {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        margin-bottom: 1%;
    }

    .search-input {
        width: 100%;
        padding: 1.2vh 1vw;
        border-radius: 0.26vw;
        border: 1px solid #ccc;
        color: #7B0302;
        background-color: #f5f5f5;
        margin-bottom: 1.5vh !important;
    }

    #user-circle-icon:hover,
    #notification-circle-icon:hover {
        filter: brightness(1.25);
        transform: scale(1.05);
        transition: filter 0.2s ease;
    }

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        border-radius: 5px;
        min-width: 6%;
        z-index: 1000;
        margin-top: 7%;
        margin-right: 1.75%;
        text-align: center;
    }

    .dropdown-menu a {
        display: block;
        padding: 7.5% 12%;
        color: #7B0302;
        text-decoration: none;
        font-size: 0.8cqw;
    }

    .dropdown-menu a:first-child:hover {
        background-color: #7B0302;
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .dropdown-menu a:last-child:hover {
        background-color: #7B0302;
        color: white;
        border-radius: 0 0 8px 8px;
    }

    .dropdown-menu a:not(:first-child):not(:last-child):hover {
        background-color: #7B0302;
        color: white;
    }

    .search-upload {
        width: 100%;
        padding: 1.2vh 1vw;
        border-radius: 0.26vw;
        border: 1px solid #ccc;
        color: #7B0302;
        background-color: #f5f5f5;
        box-shadow: 0 0.18vh 0.56vh rgba(0, 0, 0, 0.55);
        margin-bottom: 1.5vh;
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

    /* Search dropdown */
    .search-dropdown {
        margin-bottom: 10px;
    }

    .search-dropdown select {
        width: 300px;
        padding: 10px;
        border: 2px solid #800000;
        border-radius: 20px;
        font-size: 15px;
        color: #800000;
        outline: none;
    }

    .note {
        font-size: 14px;
        color: #555;
        margin-bottom: 20px;
    }

    /* qr image */

    /* Modal background */
    .form-wrapper {
        display: grid;
        grid-template-columns: 3fr 3fr 220px;
        /* left, right, qr */
        gap: 20px;
        align-items: start;
    }

    .form-grid {
        display: contents;
        /* let grid children flow */
    }

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
        margin-left: 10%;
        text-align: center;
    }

    .qr-preview h4 {
        margin-top: 8%;
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

    #qrToggleBtn {
        color: gray;
        font-size: 9vw;
        border: none;
        margin-bottom: -1vw;
    }

    .result-list {
        list-style: none;
        padding-left: 0;
    }

    .result-list li {
        padding: 5px;
        border-bottom: 1px solid #eee;
    }

    .result-list li:hover {
        background-color: #f9f9f9;
    }

    @media (max-width: 1080px) {
        .topbar span {
            font-size: 70px;
            color: #7B0302;
            font-weight: 700;
            padding: 20px 0px 20px 0px;
        }

        .search-dropdown select {
            width: 500px;
            height: 70px;
            padding: 10px;
            font-size: 30px;
        }

        .note {
            font-size: 30px;
            color: #555;
            margin-bottom: 20px;
        }

        .form-wrapper {
            display: flex;
            flex-direction: column;
            grid-template-columns: 1fr 1fr 220px;
            gap: 100px;
            align-items: start;
            font-size: 30px;
            margin-left: 120px;
        }

        .for-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: flex;
            flex-direction: column;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            flex: 1;
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

        #qrToggleBtn {
            color: gray;
            font-size: 35vw;
            border: none;
            margin-bottom: -1vw;
        }

        .qr-preview h4 {
            margin-top: 8%;
            font-size: 40px;
            color: black;
        }

        .result {
            font-size: 50px;
        }

        .result-list {
            list-style: none;
            padding-left: 0;
        }

        .result-list li {
            padding: 5px;
            border-bottom: 20px solid #ccc;
            font-size: 40px;
            gap: 20px
        }

        #liveResults {
            margin-top: 20px;
            padding: 10px;
            border: 10px solid #ccc;
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
    <span>Search</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<div class="search-dropdown">
    <select>
        <option>Search for</option>
        <option>ORIGINAL PLAN</option>
        <option>CERTIFIED TITLE</option>
        <option>REF PLAN</option>
        <option>LOT DATA</option>
        <option>TD</option>
        <option>TRANSMITAL</option>
        <option>FIELDNOTES</option>
        <option>TAX DECLARATION</option>
        <option>DOCUMENTS</option>
        <option>3 BP</option>
        <option>CM</option>
        <option>SURVEY AUTHORITY</option>
        <option>ZOONING</option>
        <option>LRA STATUS</option>
        <option>I'D S</option>
        <option>APPLICATION</option>
        <option>TAX CLEARANCE</option>
        <option>EXTRAJUDICIAL</option>
        <option>DEED OF SALE</option>
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
                <h4 id="qrStatusText" style="color: black; font-size: 0.75vw;">QR Code Search Disabled</h4>
            </div>

        </div>
    </div>
</form>
<span style="font-size: 1.5cqw; color: #7B0302; font-weight: 700; margin-top: 50px; display: inline-block;">
    Results
</span>
<div id="liveResults" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;">
    <p>Provide input or select from the list to initiate a search for matching project data...</p>
</div>

<div id="qrsearchModal" class="newmodal">
    <div class="new-modal-content">
        <span id="closeqrsearchModal">&times;</span>
        <div id="modalBody"><div>