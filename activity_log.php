
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
.activitylist-table {
    width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px; /* Adds vertical space between rows */
  font-size: 0.8vw;
  }

  .activitylist-table th, .activitylist-table td {
    padding: 8px;
    text-align: center;
    min-width: 10cqw;
  }

  .activitylist-table td {
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
  </style>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Activity Log</span>
  <div class="topbar-content">
     <div class="search-bar">
      <input type="text" placeholder="Search Project" />
    </div>
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
</div>

<hr class="top-line" />

<?php
include 'server/server.php';

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
        d.DocumentType
    FROM activity_log al
    JOIN employee e ON al.EmployeeID = e.EmployeeID
    LEFT JOIN document d ON al.DocumentID = d.DocumentID
    ORDER BY al.Time DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $maskedName = substr($row['EmpFName'], 0, 1) . '*** ' . substr($row['EmpLName'], 0, 1) . '***';
        $documentInfo = empty($row['DocumentID']) ? $row['ProjectID'] : $row['DocumentType'];
      $activity_logs[] = [
          'employee_name' => $maskedName,
          'job_position' => $row['JobPosition'],
          'document_info' => $documentInfo,
          'status' => strtoupper($row['Status']),
          'time' => date('d M Y H:i', strtotime($row['Time']))
      ];
    }
}

$conn->close();
?>

<!-- Activity Log Table -->
<table class="activitylist-table" id="projectTable">
  <thead>
    <tr>
      <th><button class="sort-btn active-sort" onclick="sortTable(0, this)">Employee<i class="fa fa-long-arrow-down" style="margin-left:5px;"></i></button></th>
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
        <tr>
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
