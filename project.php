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
?>

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

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; width: 100%;">
    <div style="display: flex; gap: 10px; justify-content: center; flex: 1;">
        <button id="btn-digital" class="doc-tab-button active-tab">Digital Documents</button>
        <button id="btn-physical" class="doc-tab-button">Physical Documents</button>
    </div>
    <button class="update-btn fa fa-edit" data-projectid="<?= htmlspecialchars($project['ProjectID'], ENT_QUOTES) ?>"
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
                    if ($file === '.' || $file === '..')
                        continue;
                    if (strpos($file, '-QR') !== false)
                        continue;

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
                        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                        $relativeWebPath = str_replace(['../', './'], '', $cleanFolderPath . '/' . $file);
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $downloadUrl = $basePath . '/' . ltrim($relativeWebPath, '/');
                        $isPreviewable = in_array($ext, $previewableExts);
                        ?>
                        <li style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <?php if ($isPreviewable): ?>
                                <div class="file-name preview-doc" data-file="<?= htmlspecialchars($relativeWebPath) ?>"
                                    title="Preview">
                                    <?= htmlspecialchars($file) ?>
                                </div>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($downloadUrl) ?>" class="file-name" title="Download"
                                    download="<?= htmlspecialchars($file) ?>">
                                    <?= htmlspecialchars($file) ?>
                                </a>
                            <?php endif; ?>

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

                    <form class="qr-validate-form" data-projectid="<?= htmlspecialchars($projectId) ?>"
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
    <a href="#" class="fa fa-download download-image-modal" title="Download" download></a>
    <div id="modalContent"
        style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
        <!-- Preview content injected here dynamically -->
    </div>
</div>