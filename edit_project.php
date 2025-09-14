<style>
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

  /* --- Scrollable table container --- */
  .table-container {
    max-height: 400px;
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
  border-radius: 4px; /* square */
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
  background-color: rgba(0,0,0,0.3); /* smaller opacity for less harsh background */
  justify-content: center;
  align-items: center;
}

/* Modal content (QR image) */
.qr-modal-content {
  min-width: 300px;   /* bigger image for document QR */
  min-height: 300px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
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
  border: 1px solid #ccc; /* optional */
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
  width: 80%;   /* smaller than container */
  height: 80%;
  transform: translate(-50%, -50%); /* center */
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
    transform: scale(1.05);
    transition: filter 0.2s ease;
    background-color: transparent;
}

#update-edit-btn {
  width: 50%;
  padding: 1.11vh 0.83vw;
  background-color: #7B0302;
  color: #fff;
  border: none;
  border-radius: 0.26vw;
  cursor: pointer;
  margin-top: 2vh;
}

.content {
    padding-bottom: 2.5%;
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
        JOIN Address a ON p.AddressID = a.AddressID
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

<!-- Top Bar and Page Heading -->
<div class="topbar">
  <button type="button" id="update-back-btn" class="fa fa-arrow-left" data-page="project_list.php"></button>
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Update <?= htmlspecialchars($projectId) ?></span>
  <div class="topbar-content">
    <div class="icons">
      <span id="notification-circle-icon" class="fa fa-bell-o" style="font-size: 1.75cqw; color: #7B0302;"></span>
      <span id="user-circle-icon" class="fa fa-user-circle" style="font-size: 2.25cqw; color: #7B0302;"></span>
      <div class="dropdown-menu" id="user-menu">
        <a data-page="profile.php">Profile</a>
        <a href="model/logout.php">Sign Out</a>
      </div>
    </div>
  </div>
</div>

<hr class="top-line" />

<!-- Project Info Form (Read-only) -->
<div class="content">
  <form id="update_projectForm">
    <div class="form-wrapper">
      <div class="form-grid">
        <div class="column">

          <div class="form-row">
            <label>Lot Number:</label>
            <input type="text" value="<?= htmlspecialchars($project['LotNo']) ?>" readonly />
          </div>

          <div class="form-row">
            <label>Client First Name:</label>
            <input type="text" value="<?= htmlspecialchars($project['ClientFName']) ?>" readonly />
          </div>

          <div class="form-row">
            <label>Client Last Name:</label>
            <input type="text" value="<?= htmlspecialchars($project['ClientLName']) ?>" readonly />
          </div>

          <div class="form-row">
            <label>Province:</label>
            <select disabled>
              <option value="Bulacan" <?= ($project['Province'] === 'Bulacan') ? 'selected' : '' ?>>Bulacan</option>
            </select>
          </div>

          <div class="form-row">
            <label>Municipality:</label>
            <select disabled>
              <option selected><?= htmlspecialchars($project['Municipality']) ?></option>
            </select>
          </div>

          <div class="form-row">
            <label>Barangay:</label>
            <select disabled>
              <option selected><?= htmlspecialchars($project['Barangay']) ?></option>
            </select>
          </div>

          <div class="form-row">
            <label>Street/Subdivision:</label>
            <input type="text" value="<?= htmlspecialchars($project['Address']) ?>" readonly />
          </div>
        </div>

        <div class="column">
          <div class="form-row">
            <label>Survey Type:</label>
            <select disabled>
              <?php
              $types = ["Relocation Survey", "Verification Survey", "Subdivision Survey", "Consolidation Survey", "Topographic Survey", "AS-Built Survey", "Sketch Plan / Vicinity Map", "Land Titling/ Transfer", "Real Estate"];
              foreach ($types as $type): ?>
                <option value="<?= $type ?>" <?= ($project['SurveyType'] === $type) ? 'selected' : '' ?>><?= $type ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <label>Agent:</label>
            <input type="text" value="<?= htmlspecialchars($project['Agent']) ?>" readonly />
          </div>

          <div class="form-row">
            <label for="approvalStatusThing">Status:</label>
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
            <label>Survey Start Date:</label>
            <input type="date" value="<?= htmlspecialchars($project['SurveyStartDate']) ?>" readonly />
          </div>

          <div class="form-row">
            <label>Survey End Date:</label>
            <input type="date" value="<?= htmlspecialchars($project['SurveyEndDate']) ?>" readonly />
          </div>
      
          <div class="form-row">
            <label>Request Type:</label>
            <select disabled>
              <option <?= ($requestType === 'For Approval') ? 'selected' : '' ?>>For Approval</option>
              <option <?= ($requestType === 'Sketch Plan') ? 'selected' : '' ?>>Sketch Plan</option>
            </select>
          </div>

          <div id="toBeApprovedBy">
            <label>To be approved by:</label>
            <div class="approval-group">
              <?php
              $approvals = ["LRA", "BUREAU", "CENRO"];
              foreach ($approvals as $a): ?>
                <label>
                  <input type="radio" disabled <?= ($approvalType === $a) ? 'checked' : '' ?> />
                  <?= $a ?>
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
          // Normalize docsToRender keys for matching documents array keys
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
              <select class="storage-status" disabled style="<?= $doc ? '' : 'display:none;' ?>">
                <option value="Stored" <?= strtoupper($status) === 'STORED' ? 'selected' : '' ?>>Stored</option>
                <option value="Released" <?= strtoupper($status) === 'RELEASED' ? 'selected' : '' ?>>Released</option>
              </select>
            </td>

            <td>
              <div class="digital-cell">
                <div class="file-list">
                  <?= $fileName ? htmlspecialchars($fileName) : '<i>No file</i>' ?>
                </div>
              </div>
            </td>

            <td class="qr-code">
              <?php if (!empty($qr)): ?>
                <span class="view-qr-text" 
                      style="cursor:pointer;color:#7B0302;text-decoration:underline;" 
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
      <button type="button" id="update-edit-btn" class="btn-red" onclick="toggleEditSave()">Edit</button>
    </div>
  </form>
</div>

<div id="qrModal" class="qr-modal">
  <span class="close">&times;</span>
  <img id="qrModalImg" class="qr-modal-content">
</div>
