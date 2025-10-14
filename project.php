<link rel="stylesheet" href="css/project.css">

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

// Define previewable extensions
$previewableExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'dwg'];
?>

<?php
$view = isset($_GET['view']) && $_GET['view'] === 'physical' ? 'physical' : 'digital';
?>

<div id="view-flag" data-view="<?= $view ?>" style="display:none;"></div>

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
    <button type="button" id="project-back-btn" class="fa fa-arrow-left"></button>
    <span><?= htmlspecialchars($projectId) ?></span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<?php
$jobPosition = strtolower($_SESSION['jobposition'] ?? '');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; width: 100%;">
    <div style="display: flex; gap: 10px; justify-content: center; flex: 1;">
        <button id="btn-digital" class="doc-tab-button active-tab">Digital Documents</button>
        <button id="btn-physical" class="doc-tab-button">Physical Documents</button>
    </div>

    <?php if ($jobPosition !== 'cad operator' && $jobPosition !== 'compliance officer'): ?>
        <button class="update-btn fa fa-edit"
            data-projectid="<?= htmlspecialchars($project['ProjectID'], ENT_QUOTES) ?>"
            onclick="redirectToUpdate(this)">
        </button>
    <?php endif; ?>
</div>


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

            // CAD Operator restriction
            if (strtolower($jobPosition) === 'cad operator') {
                $allowedTypes = ['cad file', 'certified title', 'original plan', 'tax declaration'];
                if (!in_array(strtolower($folder), $allowedTypes)) continue;
            }
        ?>
            <div class="document-folder" style="margin-bottom: 20px;">
                <div class="folderName" style="color: #7B0302; font-weight: bold;">
                    <?= htmlspecialchars($folder) ?>
                </div>
                <ul>
                    <?php foreach ($fileList as $file): ?>
                        <?php
                        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                        $relativeWebPath = str_replace(['../', './'], '', $cleanFolderPath . '/' . $file);
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $downloadUrl = $basePath . '/' . ltrim($relativeWebPath, '/');
                        $isPreviewable = in_array($ext, $previewableExts);
                        ?>

                        <li style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <div class="file-name <?= $isPreviewable ? 'preview-doc' : '' ?>"
                                 data-file="<?= htmlspecialchars($relativeWebPath) ?>"
                                 title="<?= $isPreviewable ? 'Preview' : 'File' ?>">
                                <?= htmlspecialchars($file) ?>
                            </div>

                            <?php
                            $showButtons = true;
                            if (strtolower($jobPosition) === 'cad operator' && strtolower($folder) !== 'cad file') {
                                $showButtons = false;
                            }
                            ?>

                            <?php if ($showButtons): ?>
                                <div style="display: flex; gap: 10px;">
                                    <?php if ($isPreviewable): ?>
                                        <div class="fa fa-eye preview-doc" data-file="<?= htmlspecialchars($relativeWebPath) ?>"
                                            title="Preview" style="font-size: 1.25rem; cursor: pointer; color: #000000ff;"
                                            onmouseover="this.style.color='#7B0302';" onmouseout="this.style.color='#000000ff';"></div>
                                    <?php endif; ?>
                                    <a href="<?= htmlspecialchars($downloadUrl) ?>" class="fa fa-download" title="Download"
                                        download="<?= htmlspecialchars($file) ?>" style="font-size: 1.25rem; color: #000000ff;"
                                        onmouseover="this.style.color='#7B0302';" onmouseout="this.style.color='#000000ff';"></a>
                                </div>
                            <?php endif; ?>
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
        <?php
        $allowedTypes = ['cad file', 'certified title', 'original plan', 'tax declaration'];
        foreach ($documents as $doc):
            $statusRaw = $doc['DocumentStatus'];
            $statusUpper = strtoupper(trim($statusRaw));

            // Skip if not STORED/RELEASE
            if (!in_array($statusUpper, ['STORED', 'RELEASE'])) continue;

            // CAD Operator filtering
            if (strtolower($jobPosition) === 'cad operator' &&
                !in_array(strtolower($doc['DocumentType']), $allowedTypes)) continue;

            $toggleLabel = $statusUpper === 'RELEASE' ? 'Store' : 'Retrieve';
        ?>
            <li style="display: flex; justify-content: flex-start; align-items: center; gap: 15px; margin-bottom: 10px;">
                <span style="flex: 1;"><?= htmlspecialchars($doc['DocumentType']) ?></span>

                <?php
                $showButtons = true;
                if (strtolower($jobPosition) === 'cad operator' &&
                    strtolower($doc['DocumentType']) !== 'cad file') {
                    $showButtons = false;
                }
                ?>

                <?php if ($showButtons): ?>
                    <form class="qr-validate-form" data-projectid="<?= htmlspecialchars($projectId) ?>"
                        data-docname="<?= htmlspecialchars($doc['DocumentName']) ?>"
                        data-newstatus="<?= $statusUpper === 'RELEASE' ? 'STORED' : 'RELEASE' ?>"
                        style="margin: 0; display: flex; align-items: center; gap: 10px;">

                        <button type="button" class="toggle-qr-btn"><?= $toggleLabel ?></button>
                        <input type="text" name="scannedQR" required autocomplete="off" autocorrect="off"
                            style="opacity: 0; position: absolute; pointer-events: none; width: 1px; height: 1px;">
                        <span class="scan-qr-text" style="display: none; font-style: italic; color: #555;">
                            Scan QR Code to proceed
                        </span>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Image Preview Modal -->
<div id="imageModal" class="image-modal">
    <span class="close-image-modal">&times;</span>
    <a href="#" class="fa fa-download download-image-modal" title="Download" download></a>
    <div id="modalContent"
        style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
    </div>
</div>
