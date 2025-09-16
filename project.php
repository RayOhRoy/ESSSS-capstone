<style>
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
    color: #007BFF;
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
?>

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

<?php
$groupedDocuments = [];

// Group documents by folder name
foreach ($documents as $doc) {
    if (!empty($doc['DigitalLocation']) && !empty($doc['DocumentName'])) {
        $parts = explode('-', $doc['DocumentName']);
        $folderName = str_replace('-', ' ', implode('-', array_slice($parts, 4)));
        $folderName = $folderName ?: 'Uncategorized';

        $groupedDocuments[$folderName][] = $doc;
    }
}
?>

<?php if (!empty($groupedDocuments)): ?>
  <div class="document-section">
    <?php
    $baseDir = __DIR__ . '/uploads';
    ?>

    <?php foreach ($groupedDocuments as $folder => $docs): ?>
      <div class="document-folder" style="margin-bottom: 20px;">
        <div class="folderName" style="color: #7B0302; font-weight: bold;">
          <?= htmlspecialchars($folder) ?>
        </div>
        <ul>
          <?php
          $firstDoc = reset($docs);
          $folderPathRaw = $firstDoc['DigitalLocation'];
          $folderPath = explode(';', $folderPathRaw)[0];

          $folderPathParts = explode('/', $folderPath);
          array_pop($folderPathParts);
          $cleanFolderPath = implode('/', $folderPathParts);

          $fullFolderPath = $baseDir . '/' . $cleanFolderPath;

          // Debug output (optional, remove in production)
          // echo "<div style='color: red; font-weight: bold;'>Folder for group '$folder' resolves to: " . htmlspecialchars($fullFolderPath) . "</div>";

          $fileList = [];

          if (is_dir($fullFolderPath)) {
              $files = scandir($fullFolderPath);
              foreach ($files as $file) {
                  if ($file === '.' || $file === '..') continue;
                  if (strpos($file, '-QR') !== false) continue;

                  $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                  if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
                      $fileList[] = $file;
                  }
              }
          }
          ?>

   <?php foreach ($fileList as $file): ?>
  <?php
    $relativeWebPath = str_replace(['../', './'], '', $cleanFolderPath . '/' . $file);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $downloadUrl = '/uploads/' . $relativeWebPath; // only for download
  ?>
  <li style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
    <div><?= htmlspecialchars($file) ?></div>
    <div style="display: flex; gap: 10px;">
      <?php if (!in_array($ext, ['pdf'])): ?>
        <div 
          class="fa fa-eye preview-doc" 
          data-file="<?= htmlspecialchars($relativeWebPath) ?>" 
          title="Preview"
          style="cursor: pointer; color: #007BFF;"
        ></div>
      <?php endif; ?>
      <a 
        href="<?= htmlspecialchars($downloadUrl) ?>" 
        class="fa fa-download" 
        title="Download"
        download="<?= htmlspecialchars($file) ?>"
        style="color: #007BFF;"
      ></a>
    </div>
  </li>
<?php endforeach; ?>

        </ul>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
  <span class="close-image-modal">&times;</span>
  <a 
    href="<?= htmlspecialchars($downloadUrl) ?>" 
    class="fa fa-download download-image-modal" 
    title="Download"
    download="<?= htmlspecialchars($file) ?>"
  ></a>
  <img class="image-modal-content" id="modalImage">
</div>