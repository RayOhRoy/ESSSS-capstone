<link rel="stylesheet" href="css/physical_storage.css">

<?php
session_start();
include 'server/server.php';

// ✅ Get job position from session (don’t overwrite it later)
$jobPosition = strtolower($_SESSION['jobposition'] ?? '');

$employeeID = $_SESSION['employeeid'] ?? null;
$empFName = '';
$empLName = '';
$empEmail = '';

// Fetch employee data from DB
if ($employeeID) {
    $stmt = $conn->prepare("SELECT EmpFName, EmpLName, JobPosition, Email FROM employee WHERE EmployeeID = ?");
    $stmt->bind_param("s", $employeeID);
    $stmt->execute();
    $stmt->bind_result($empFName, $empLName, $dbJobPosition, $empEmail);
    $stmt->fetch();
    $stmt->close();

    // ✅ Use DB job position if available, otherwise session
    if (!empty($dbJobPosition)) {
        $jobPosition = strtolower($dbJobPosition);
    }
}
?>

<div id="userData" data-jobposition="<?= htmlspecialchars($jobPosition) ?>">
</div>

<div class="user-menu-panel" id="userPanel">
    <div class="user-panel-top">
        <div class="user-top-info">
            <p>
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
    <button class="fa fa-arrow-left"></button>
    <span>Physical Storage</span>
    <div class="topbar-content">
        <div class="icons">
            <span id="user-circle-icon" class="fa fa-user-circle"></span>
        </div>
    </div>
</div>

<hr class="top-line" />

<?php
$query = "SELECT municipality FROM address";
$result = $conn->query($query);

$cards = [];
$counter = [];

while ($row = $result->fetch_assoc()) {
    $municipality = $row['municipality'];
    $words = explode(' ', trim($municipality));

    // Determine prefix
    if (strcasecmp($municipality, 'Balagtas') === 0) {
        $short = 'BAS';
    } elseif (strcasecmp($municipality, 'Baliuag') === 0) {
        $short = 'BAG';
    } elseif (strcasecmp($words[0], 'San') === 0 && !empty($words[1])) {
        $short = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $words[1]), 0, 3));
    } else {
        $short = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $municipality), 0, 3));
    }

    if (!isset($counter[$short]))
        $counter[$short] = 1;

    $code = $short . '-' . str_pad($counter[$short], 2, '0', STR_PAD_LEFT);
    $counter[$short]++;

    $cards[] = [
        'municipality' => $municipality,
        'code' => $code
    ];
}
?>

<div class="card-container">
    <?php foreach ($cards as $card): ?>
        <div class="card">
            <div class="card-title"><?= htmlspecialchars($card['code']) ?></div>
            <div class="card-actions">
                <button class="open-button">VIEW</button>

                <?php
                $muni = strtoupper(substr($card['municipality'], 0, 3));
                if ($jobPosition !== 'cad operator' && $jobPosition !== 'compliance officer') {
                    if ($muni === 'HAG') {
                        echo '<i class="fa fa-unlock-alt" id="lock1" onclick="toggleRelay(1, this)"></i>';
                    } elseif ($muni === 'CAL') {
                        echo '<i class="fa fa-unlock-alt" id="lock2" onclick="toggleRelay(2, this)"></i>';
                    } else {
                        echo '<i class="fa fa-unlock-alt nolock" id="nolock"></i>';
                    }
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="envelope-section" style="display:none; flex-direction:column; align-items:center;">
    <button id="scrollUp" class="scroll-btn"><i class="fa fa-angle-up"></i></button>

    <div class="envelope-columns"></div>

    <button id="scrollDown" class="scroll-btn"><i class="fa fa-angle-down"></i></button>
</div>

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
                            <th
                                style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
                                Document Name
                            </th>
                            <th
                                style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
                                Physical Documents
                            </th>
                            <th
                                style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
                                Digital Documents
                            </th>
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
                <button class="open-btn">VIEW</button>
            </div>
        </div>
    </div>
</div>