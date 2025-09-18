<style>
#user-circle-icon:hover,
#notification-circle-icon:hover {
    filter: brightness(1.25);
    transform: scale(1.05);
    transition: filter 0.2s ease;
}

/* Add this style for the toggle buttons */
.doc-tab-button {
  background-color: #f1f1f1;
  color: #7B0302;
  border: 1px solid #ccc;
  padding: 10px 20px;
  font-size: 1.2vw;
  margin: 0 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 8px;
}

.doc-tab-button:hover {
  background-color: #ddd;
}

.active-tab {
  background-color: #7B0302;
  color: white;
  border-color: #7B0302;
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

#project-back-btn {
    font-size: 2vw;
    border: none;
    color: #7B0302;
    cursor: pointer;
    transition: color 0.3s;
}

#project-back-btn:hover {
    filter: brightness(1.25);
    transform: scale(1.05);
    transition: filter 0.2s ease;
    background-color: transparent;
}

  .document-section {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ccc;
    background-color: #f9f9f9;
  }

  .document-section h3 {
    margin-bottom: 10px;
    color: #7B0302;
  }

  .document-section ul {
    list-style: none;
    padding-left: 0;
  }

  .document-section li {
    margin-bottom: 8px;
  }

  .document-section a {
    text-decoration: none;
  }

  .document-section a:hover {
    text-decoration: underline;
  }

  .image-modal {
  display: none;
  position: fixed;
  z-index: 9999;
  padding-top: 60px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.8);
}

.image-modal-content {
  margin: auto;
  display: block;
  max-width: 90%;
  max-height: 80vh;
  box-shadow: 0 0 20px #000;
  border-radius: 8px;
}

.close-image-modal {
  position: absolute;
  right: 2vw;
  color: #fff;
  font-size: 2vw;
  font-weight: bold;
  cursor: pointer;
}

.download-image-modal {
  position: absolute; 
  margin-top: 4vw !important;
  right: 2.2vw;
  color: #fff;
  font-size: 1vw;
  font-weight: bold;
  cursor: pointer;
}

.folderName {
    font-size: 1.5vw;
    font-weight: 700;
}

.file-name {
  cursor: pointer;
  color: inherit;
  transition: color 0.2s;
}

.file-name:hover {
  color: #007BFF;
  text-decoration: underline;
}

.physical-toggle-btn {
  padding: 6px 14px;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.physical-toggle-btn.store {
  background-color: #7B0302;
}

.physical-toggle-btn.retrieve {
  background-color: #7B0302;
}

.update-btn {
  color: #7B0302;
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
</style>

<?php
include 'server/server.php';

if (!isset($_GET['projectId'])) {
    die("Project ID not provided.");
}

$projectId = $_GET['projectId'];

// --- Fetch Project Data ---
$sql = "SELECT 
            p.ProjectID, p.LotNo, p.ClientFName, p.ClientLName, p.SurveyType,
            p.SurveyStartDate, p.SurveyEndDate, p.Agent, p.RequestType,
            p.Approval, p.ProjectQR, a.Province, a.Municipality,
            a.Barangay, a.Address, p.ProjectStatus
        FROM project p
        JOIN address a ON p.AddressID = a.AddressID
        WHERE p.ProjectID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $projectId);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

// --- Fetch Documents ---
$documents = [];
$docSql = "SELECT DocumentType, DocumentStatus, DocumentQR, DocumentName, DigitalLocation 
           FROM document 
           WHERE ProjectID = ?";
$docStmt = $conn->prepare($docSql);
$docStmt->bind_param("s", $projectId);
$docStmt->execute();
$docResult = $docStmt->get_result();

while ($docRow = $docResult->fetch_assoc()) {
    $documents[] = $docRow;
}

// --- Group documents by folder ---
$groupedDocuments = [];
foreach ($documents as $doc) {
    if (!empty($doc['DigitalLocation']) && !empty($doc['DocumentName'])) {
        $parts = explode('-', $doc['DocumentName']);
        $folderName = str_replace('-', ' ', implode('-', array_slice($parts, 4))) ?: 'Uncategorized';
        $groupedDocuments[$folderName][] = $doc;
    }
}
?>

<!-- Top Bar -->
<div class="topbar">
  <button type="button" id="project-back-btn" class="fa fa-arrow-left"></button>
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;"><?= htmlspecialchars($projectId) ?></span>
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

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; width: 100%;">
  <div style="display: flex; gap: 10px; justify-content: center; flex: 1;">
    <button id="btn-digital" class="doc-tab-button active-tab">Digital Documents</button>
    <button id="btn-physical" class="doc-tab-button">Physical Documents</button>
  </div>
  <button 
    class="update-btn fa fa-edit" 
    data-projectid="<?= htmlspecialchars($project['ProjectID'], ENT_QUOTES) ?>"
    onclick="redirectToUpdate(this)">
  </button>
</div>


<?php
// Define previewable extensions
$previewableExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
?>

<!-- Digital Documents Section -->
<div id="digital-section" class="document-section">
  <?php if (!empty($groupedDocuments)): ?>
    <?php
    $baseDir = __DIR__ . '/uploads';
    foreach ($groupedDocuments as $folder => $docs):
      $firstDoc = reset($docs);
      $folderPathRaw = $firstDoc['DigitalLocation'];
      $folderPath = explode(';', $folderPathRaw)[0];

      $folderPathParts = explode('/', $folderPath);
      array_pop($folderPathParts);
      $cleanFolderPath = implode('/', $folderPathParts);

      $fullFolderPath = $baseDir . '/' . $cleanFolderPath;
      $fileList = [];

      if (is_dir($fullFolderPath)) {
          $files = scandir($fullFolderPath);
          foreach ($files as $file) {
              if ($file === '.' || $file === '..') continue;
              if (strpos($file, '-QR') !== false) continue;

              $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
              if (in_array($ext, array_merge($previewableExts, ['other_extensions_if_any']))) {
                  $fileList[] = $file;
              }
          }
      }
    ?>

    <div class="document-folder" style="margin-bottom: 20px;">
      <div class="folderName" style="color: #7B0302; font-weight: bold;">
        <?= htmlspecialchars($folder) ?>
      </div>
      <ul>
      <?php foreach ($fileList as $file): ?>
        <?php
          $relativeWebPath = str_replace(['../', './'], '', $cleanFolderPath . '/' . $file);
          $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          $downloadUrl = '/uploads/' . $relativeWebPath;
          $isPreviewable = in_array($ext, $previewableExts);
        ?>
        <li style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
          <?php if ($isPreviewable): ?>
            <div 
              class="file-name preview-doc"
              data-file="<?= htmlspecialchars($relativeWebPath) ?>"
              title="Preview"
            >
              <?= htmlspecialchars($file) ?>
            </div>
          <?php else: ?>
            <a 
              href="<?= htmlspecialchars($downloadUrl) ?>"
              class="file-name"
              title="Download"
              download="<?= htmlspecialchars($file) ?>"
            >
              <?= htmlspecialchars($file) ?>
            </a>
          <?php endif; ?>

          <div style="display: flex; gap: 10px;">
            <?php if ($isPreviewable): ?>
              <div 
                class="fa fa-eye preview-doc"
                data-file="<?= htmlspecialchars($relativeWebPath) ?>"
                title="Preview"
                style="cursor: pointer; color: #000000ff;"
                onmouseover="this.style.color='#007BFF';"
                onmouseout="this.style.color='#000000ff';"
              ></div>
            <?php endif; ?>
            <a 
              href="<?= htmlspecialchars($downloadUrl) ?>" 
              class="fa fa-download" 
              title="Download"
              download="<?= htmlspecialchars($file) ?>"
              style="color: #000000ff;"
              onmouseover="this.style.color='#007BFF';"
              onmouseout="this.style.color='#000000ff';"
            ></a>
          </div>
        </li>
      <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Physical Documents Section -->
<div id="physical-section" class="document-section" style="display: none;">
  <h3>Physical Documents</h3>

  <ul>
    <?php foreach ($documents as $doc): ?>
      <?php
        $statusRaw = $doc['DocumentStatus'];
        $statusUpper = strtoupper(trim($statusRaw));

        // Only show if DocumentStatus is STORED or RELEASE
        if (in_array($statusUpper, ['STORED', 'RELEASE'])):
          $toggleLabel = $statusUpper === 'RELEASE' ? 'Store' : 'Retrieve';
      ?>
    <li style="display: flex; justify-content: flex-start; align-items: center; gap: 15px; margin-bottom: 10px;">
  <span style="flex: 1;"><?= htmlspecialchars($doc['DocumentType']) ?></span>

  <form class="qr-validate-form"
        data-projectid="<?= htmlspecialchars($projectId) ?>"
        data-docname="<?= htmlspecialchars($doc['DocumentName']) ?>"
        data-newstatus="<?= $statusUpper === 'RELEASE' ? 'STORED' : 'RELEASE' ?>"
        style="margin: 0; display: flex; align-items: center; gap: 10px;">

    <!-- The toggle/cancel button -->
    <button type="button" class="toggle-qr-btn"
            style="padding: 6px 14px; border: none; border-radius: 5px; background-color: #7B0302; color: white; cursor: pointer;">
      <?= $toggleLabel ?>
    </button>

    <!-- Hidden input for QR scanning -->
    <input type="text" name="scannedQR" required autocomplete="off" autocorrect="off"
           style="opacity: 0; position: absolute; pointer-events: none; width: 1px; height: 1px;">

    <!-- The Scan QR instruction text, hidden initially -->
    <span class="scan-qr-text" style="display: none; font-style: italic; color: #555;">
      Scan QR Code to proceed
    </span>
  </form>
</li>


      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</div>


<!-- Image Preview Modal -->
<div id="imageModal" class="image-modal">
  <span class="close-image-modal">&times;</span>
  <a 
    href="#" 
    class="fa fa-download download-image-modal" 
    title="Download"
    download
  ></a>
  <div id="modalContent" style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
    <!-- Preview content injected here dynamically -->
  </div>
</div>