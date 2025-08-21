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

  .floating-add-project {
      position: fixed;
      bottom: 50px;
      right: 50px; 
      background-color: #7a0000;
      border-radius: 50%;
      width: 70px;
      height: 70px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      z-index: 50;
      transition: transform 0.3s ease;
  }

  .floating-add-project:hover {
    transform: scale(1.2); /* increase size by 20% */
  }

  .projectlist-table {
    width: 100%;
    border-collapse: collapse;
  }

  .projectlist-table th, .projectlist-table td {
    padding: 8px;
    text-align: left;
  }

  .projectlist-table td {
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

<?php
$dataFile = 'data.json';
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$projects = $data['projects'] ?? [];
?>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Project List</span>
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
include 'server/server.php'; // db connection

// Fetch joined project + address info
$sql = "SELECT 
            p.ProjectID,
            p.ClientFName,
            p.ClientLName,
            p.SurveyType,
            p.PhysicalLocation,
            a.Municipality
        FROM project p
        JOIN address a ON p.AddressID = a.AddressID";

$result = $conn->query($sql);

$projects = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
?>

<table class="projectlist-table" id="projectTable">
  <thead>
    <tr>
      <th><button class="sort-btn active-sort" onclick="sortTable(0, this)">Project Name <i class="fa fa-long-arrow-down" style="margin-left:5px;"></i></button></th>
      <th><button class="sort-btn" onclick="sortTable(1, this)">Client Name</button></th>
      <th><button class="sort-btn" onclick="sortTable(2, this)">Municipality</button></th>
      <th><button class="sort-btn" onclick="sortTable(3, this)">Physical Storage Location</button></th>
      <th><button class="sort-btn" onclick="sortTable(4, this)">Survey Type</button></th>
      <th><button class="sort-btn">Preview</button></th>
      <th><button class="sort-btn">Update</button></th>
    </tr>
  </thead>
<tbody>
  <?php foreach ($projects as $project): ?>
    <tr>
      <td><?= htmlspecialchars($project['ProjectID']) ?></td> <!-- ProjectID as Project Name -->
      <td><?= htmlspecialchars($project['ClientFName'] . ' ' . $project['ClientLName']) ?></td>
      <td><?= htmlspecialchars($project['Municipality']) ?></td>
      <td><?= htmlspecialchars($project['PhysicalLocation'] ?? '') ?></td>
      <td><?= htmlspecialchars($project['SurveyType']) ?></td>
      <td>Preview</td>
      <td><button class="update-btn" data-id="<?= $project['ProjectID'] ?>">Update Here</button></td>
    </tr>
  <?php endforeach; ?>
</tbody>

</table>


<div class="floating-add-project" data-page="admin_upload.php">
  <span class="fa fa-plus" style="font-size: 1.5cqw; color: white;"></span>
</div>