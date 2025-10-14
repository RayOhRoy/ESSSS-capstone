<?php
require '../server/server.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Fetch all activity logs
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
    ORDER BY al.Time DESC
";

$result = $conn->query($sql);
$rows = '';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = htmlspecialchars($row['EmpFName'] . ' ' . $row['EmpLName']);
        $documentInfo = empty($row['DocumentID']) ? $row['ProjectID'] : $row['DocumentName'];
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
    $rows = "<tr><td colspan='4'>No activity logs found.</td></tr>";
}

$conn->close();

// Prepare HTML for PDF
$html = "
<html>
<head>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
h2 { text-align: center; color: #7B0302; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #888; padding: 8px; text-align: left; }
th { background-color: #7B0302; color: white; }
small { color: #555; }
</style>
</head>
<body>
    <h2>Activity Log Report</h2>
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

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('activity_log.pdf', ['Attachment' => true]);
exit;
?>
