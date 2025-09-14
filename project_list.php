<style>
  input {
    width: 100%;
      padding: 1.2vh 1vw;
      border-radius: 0.26vw; 
      border: 1px solid #ccc;
      color: #7B0302;
      background-color: #f5f5f5;
      margin-bottom: 1.5vh;
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
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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
    transform: scale(1.2); /* increase size by 20% */
  }

  .projectlist-table {
    width: 100%;
    border-collapse: collapse;
  }

  .projectlist-table th, .projectlist-table td {
    padding: 8px;
    text-align: left;
  }

  .projectlist-table td {
    text-align: center; /* center table body text */
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
  display: none; /* visible */
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 9999;
}

#previewModal .modal-content {
  background: white;
  padding: 2vw;
  border-radius: 1vw;
  max-width: 600px;
  width: 90%;
  position: fixed;       /* fixed so it stays in viewport */
  top: 50%;              /* vertical center */
  left: 50%;             /* horizontal center */
  transform: translate(-50%, -50%); /* center exactly */
  box-shadow: 0 4px 10px rgba(0,0,0,0.25);
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
  overflow-x: auto; /* enables horizontal scroll on smaller screens */
}

.document-table table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5vh 0;
  font-size: 0.7cqw;  /* smaller font */
  table-layout: fixed; /* make columns distribute evenly */
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
  border-radius: 1vw; /* smaller pill shape */
  padding: 0.2vh 0.6vw; /* reduced padding */
  font-size: 0.65cqw;  /* optional: slightly smaller text */
  text-align: center;
  min-width: 60px; /* optional: helps maintain pill look */
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
</style>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Project List</span>
  <div class="topbar-content">
    <div class="icons">
      <span  id="notification-circle-icon" class="fa fa-bell-o" style="font-size: 1.75cqw; color: #7B0302;"></span>
      <span id="user-circle-icon" class="fa fa-user-circle" style="font-size: 2.25cqw; color: #7B0302;"></span>
      <div class="dropdown-menu" id="user-menu">
        <a data-page="profile.php">Profile</a>
        <a href="model/logout.php">Sign Out</a>
      </div>
    </div>
  </div>
</div>
</div>

<hr class="top-line" />

<?php
include 'server/server.php'; // db connection

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


$result = $conn->query($sql);

$projects = [];
$projectIds = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
        $projectIds[] = $row['ProjectID'];
    }
}

// Fetch documents for all projects
$documentsByProject = [];

if (!empty($projectIds)) {
    $placeholders = implode(',', array_fill(0, count($projectIds), '?'));

    $stmt = $conn->prepare("SELECT DocumentName, DocumentStatus, ProjectID, DigitalLocation FROM document WHERE ProjectID IN ($placeholders)");

    $types = str_repeat('s', count($projectIds));
    $stmt->bind_param($types, ...$projectIds);

    $stmt->execute();
    $resultDoc = $stmt->get_result();

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
    $maskedF = strlen($fname) > 2
        ? $fname[0] . str_repeat('*', strlen($fname)-2) . $fname[strlen($fname)-1]
        : $fname;
    $maskedL = strlen($lname) > 1
        ? $lname[0] . str_repeat('*', strlen($lname)-1)
        : $lname;
    return $maskedF . ' ' . $maskedL;
}
?>

<table class="projectlist-table" id="projectTable">
  <thead>
    <tr>
      <th><button class="sort-btn active-sort" onclick="sortTable(0, this)">Project Name <i class="fa fa-long-arrow-down" style="margin-left:5px;"></i></button></th>
      <th><button class="sort-btn" onclick="sortTable(1, this)">Client Name</button></th>
      <th><button class="sort-btn" onclick="sortTable(2, this)">Municipality</button></th>
      <th><button class="sort-btn" onclick="sortTable(3, this)">Survey Type</button></th>
      <th><button class="sort-btn">Preview</button></th>
      <th><button class="sort-btn">Update</button></th>
    </tr>
  </thead>
<tbody>
  <?php foreach ($projects as $project): ?>
<tr 
  ondblclick="handleRowDoubleClick(this)" 
  onclick="highlightRow(this)"
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
  <td><?= htmlspecialchars($project['Municipality']) ?></td>
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

<div id="documentsData" style="display:none;" data-documents='<?= htmlspecialchars(json_encode($documentsByProject), ENT_QUOTES, 'UTF-8') ?>'></div>

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
      <div class="document-table">
        <table>
          <thead>
            <tr>
              <th>Document Name</th>
              <th>Physical Documents</th>
              <th>Digital Documents</th>
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
