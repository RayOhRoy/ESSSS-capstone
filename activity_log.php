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

  .dropdown-menu a:first-child:hover,
  .dropdown-menu a:last-child:hover,
  .dropdown-menu a:not(:first-child):not(:last-child):hover {
    background-color: #7B0302;
    color: white;
    border-radius: 8px;
  }

  .activitylist-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    font-size: 0.8vw;
  }

  .activitylist-table th, .activitylist-table td {
    padding: 8px;
    text-align: center;
    min-width: 10cqw;
  }

  .activitylist-table td {
    text-align: center;
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

  #employeeFilter {
    width: 100%;
    padding: 6px;
    font-weight: bold;
    color: #7B0302;
    border-radius: 4px;
  }

  .filter-clear {
    margin: 10px 0;
    padding: 6px 10px;
    background-color: #eee;
    border: 1px solid #ccc;
    color: #7B0302;
    cursor: pointer;
    border-radius: 4px;
  }

  .filter-clear:hover {
    background-color: #7B0302;
    color: white;
  }
</style>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Activity Log</span>
  <div class="topbar-content">
    <div class="icons">
      <span  id="notification-circle-icon" class="fa fa-bell-o" style="font-size: 1.75cqw; color: #7B0302;"></span>
      <span id="user-circle-icon" class="fa fa-user-circle" style="font-size: 2.25cqw; color: #7B0302;"></span>
      <div class="dropdown-menu" id="user-menu">
        <a href="profile.php">Profile</a>
        <a href="model/logout.php">Sign Out</a>
      </div>
    </div>
  </div>
</div>
<hr class="top-line" />

<?php
include 'server/server.php';

$employeeOptions = [];
$employeeResult = $conn->query("SELECT EmployeeID, EmpFName, EmpLName FROM employee ORDER BY EmpFName, EmpLName");
if ($employeeResult && $employeeResult->num_rows > 0) {
    while ($emp = $employeeResult->fetch_assoc()) {
        $fullName = $emp['EmpFName'] . ' ' . $emp['EmpLName'];
        $employeeOptions[$emp['EmployeeID']] = $fullName;
    }
}

$activity_logs = [];
$sql = "
    SELECT 
        al.ActivityLogID,
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
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = $row['EmpFName'] . ' ' . $row['EmpLName'];
        $documentInfo = empty($row['DocumentID']) ? $row['ProjectID'] : $row['DocumentName'];
        $activity_logs[] = [
            'employee_name' => $fullName,
            'job_position' => $row['JobPosition'],
            'document_info' => $documentInfo,
            'status' => strtoupper($row['Status']),
            'time' => date('d M Y H:i', strtotime($row['Time']))
        ];
    }
}
$conn->close();
?>

<label for="dateFrom">From:</label>
<input type="date" id="dateFrom" onchange="filterByDate()" />

<label for="dateTo">To:</label>
<input type="date" id="dateTo" onchange="filterByDate()" />

<table class="activitylist-table" id="projectTable">
  <thead>
    <tr>
      <th>
        <select id="employeeFilter" onchange="filterByEmployee()" style="min-width: 100%; font-weight: bold; color: #7B0302; border-radius: 4px;">
          <option value="">All Employees</option>
          <?php foreach ($employeeOptions as $id => $name): ?>
            <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
          <?php endforeach; ?>
        </select>
      </th>
      <th><button class="sort-btn" onclick="sortTable(1, this)">Document</button></th>
      <th><button class="sort-btn" onclick="sortTable(2, this)">Action</button></th>
      <th><button class="sort-btn" onclick="sortTable(3, this)">Timestamp</button></th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($activity_logs)): ?>
      <tr><td colspan="4">No activity logs available.</td></tr>
    <?php else: ?>
      <?php foreach ($activity_logs as $log): ?>
        <tr data-employee="<?= htmlspecialchars($log['employee_name']) ?>">
          <td>
            <div style="color: #7B0302; font-weight: bold; text-transform: uppercase;">
              <?= htmlspecialchars($log['employee_name']) ?>
            </div>
            <div style="color: #7B0302; font-weight: normal;">
              <?= htmlspecialchars($log['job_position']) ?>
            </div>
          </td>
          <td><?= htmlspecialchars($log['document_info']) ?></td>
          <td><?= htmlspecialchars($log['status']) ?></td>
          <td><?= htmlspecialchars($log['time']) ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>