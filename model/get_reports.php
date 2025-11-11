<?php
include '../server/server.php';
header('Content-Type: application/json');

// ✅ Optional filter by report status
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = "
    SELECT 
        r.ReportID,
        r.ProjectID,
        r.DocumentID,
        r.EmployeeID,
        r.ReportDesc,
        r.ReportStatus,
        r.Time,
        e.EmpFname,
        e.EmpLName,
        e.JobPosition
    FROM report r
    LEFT JOIN employee e ON r.EmployeeID = e.EmployeeID
";

// ✅ Add WHERE clause only if status filter is provided
if ($statusFilter !== '') {
    $safeStatus = $conn->real_escape_string($statusFilter);
    $query .= " WHERE r.ReportStatus = '$safeStatus'";
}

$query .= " ORDER BY r.Time DESC";

$result = $conn->query($query);
$reports = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = [
            'reportID' => $row['ReportID'],
            'projectID' => $row['ProjectID'],
            'documentID' => $row['DocumentID'],
            'reportDesc' => $row['ReportDesc'],
            'reportStatus' => $row['ReportStatus'],
            'employeeID' => $row['EmployeeID'],
            'employeeName' => trim($row['EmpFname'] . ' ' . $row['EmpLName']),
            'employeePosition' => $row['JobPosition'],
            'time' => date("d M Y H:i", strtotime($row['Time']))
        ];
    }
}

// ✅ Return consistent JSON structure
echo json_encode([
    'status' => 'success',
    'reports' => $reports
]);

$conn->close();
?>