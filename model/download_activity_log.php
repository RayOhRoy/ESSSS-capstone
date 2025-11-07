<?php
require '../server/server.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// ✅ Get filter inputs
$dateFrom = isset($_GET['from']) ? $_GET['from'] : '';
$dateTo = isset($_GET['to']) ? $_GET['to'] : '';
$employee = isset($_GET['employee']) ? trim($_GET['employee']) : '';

// ✅ Build SQL query
$sql = "
    SELECT 
        al.ProjectID,
        al.DocumentID,
        al.Status,
        al.Time,
        e.EmpFName,
        e.EmpLName,
        e.JobPosition,
        d.DocumentName
    FROM activity_log al
    JOIN employee e ON al.EmployeeID = e.EmployeeID
    LEFT JOIN document d ON al.DocumentID = d.DocumentID
    WHERE 1=1
";

$params = [];
$types = '';

if (!empty($employee)) {
    $sql .= " AND CONCAT(e.EmpFName, ' ', e.EmpLName) LIKE ?";
    $params[] = '%' . $employee . '%';
    $types .= 's';
}

if (!empty($dateFrom)) {
    $sql .= " AND DATE(al.Time) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $sql .= " AND DATE(al.Time) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$sql .= " ORDER BY al.Time DESC";

// ✅ Execute query securely
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = '';
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = htmlspecialchars($row['EmpFName'] . ' ' . $row['EmpLName']);
        $documentInfo = empty($row['DocumentID']) ? htmlspecialchars($row['ProjectID']) : htmlspecialchars($row['DocumentName']);
        $status = strtoupper(htmlspecialchars($row['Status']));
        $time = date('d M Y H:i', strtotime($row['Time']));
        $position = htmlspecialchars($row['JobPosition']);

        $rows .= "
            <tr>
                <td>{$fullName}<br><small>{$position}</small></td>
                <td>{$documentInfo}</td>
                <td>{$status}</td>
                <td>{$time}</td>
            </tr>
        ";
    }
} else {
    $rows = "<tr><td colspan='4'>No activity logs found for the selected filters.</td></tr>";
}

$stmt->close();
$conn->close();

// ✅ Create filter summary & date range
$filterSummary = '';
if ($employee || $dateFrom || $dateTo) {
    $filterSummary .= "<p><strong>Filters:</strong> ";
    if ($employee) $filterSummary .= "Employee = " . htmlspecialchars($employee);
    if ($dateFrom || $dateTo) {
        $fromLabel = $dateFrom ? date('M d, Y', strtotime($dateFrom)) : 'All Time';
        $toLabel = $dateTo ? date('M d, Y', strtotime($dateTo)) : 'Present';
        $filterSummary .= " | Date Range: {$fromLabel} - {$toLabel}";
    }
    $filterSummary .= "</p>";
}

// ✅ Base64 encode logo (so Dompdf can display it reliably)
$logoPath = realpath(__DIR__ . '/../picture/logo.jpg');
$logoData = '';
if ($logoPath && file_exists($logoPath)) {
    $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoData = "data:image/{$logoType};base64,{$logoBase64}";
}

// ✅ Generate dynamic filename
$filename = 'activity_log';
if ($dateFrom && $dateTo) {
    $filename .= "_{$dateFrom}_to_{$dateTo}";
} elseif ($dateFrom) {
    $filename .= "_from_{$dateFrom}";
} elseif ($dateTo) {
    $filename .= "_until_{$dateTo}";
}
$filename .= '.pdf';

// ✅ Prepare HTML for PDF
$html = "
<html>
<head>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; margin: 30px; }
h2 { text-align: center; color: #7B0302; margin-bottom: 5px; }
p { text-align: center; font-size: 11px; color: #333; margin: 0; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #888; padding: 8px; text-align: left; }
th { background-color: #7B0302; color: white; }
small { color: #555; }
.logo-container { text-align: center; margin-bottom: 10px; }
.logo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #7B0302; }
</style>
</head>
<body>
    <div class='logo-container'>
        <img src='{$logoData}' class='logo' alt='Logo'>
    </div>
    <h2>Activity Log Report</h2>
    {$filterSummary}
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Document / Project</th>
                <th>Status</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>
</body>
</html>
";

// ✅ Generate and output PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream($filename, ['Attachment' => true]);
exit;
?>