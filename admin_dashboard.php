
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
</style>

<?php
$dataFile = 'dashboard_data.json';
$data = [
  "total_projects" => 0,
  "total_documents" => 0,
  "stored_documents" => 0,
  "released_documents" => 0,
  "percentage_stored" => 0,
  "percentage_released" => 0,
  "recent_activities" => []
];



if (file_exists($dataFile)) {
  $json = file_get_contents($dataFile);
  $decoded = json_decode($json, true);
  if (is_array($decoded)) {
    $data = array_merge($data, $decoded);
  }
}
?>


<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Dashboard</span>
  <div class="topbar-content">
     <div class="search-bar">
      <input type="text" placeholder="Search Project" />
    </div>
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
</div>

<hr class="top-line" />
<div class="welcome" style="font-size: 1.5cqw; color: #7B0302;">Welcome, Admin!</div>


<?php
include 'server/server.php';

// Fetch total projects
$total_projects_query = "SELECT COUNT(*) as total FROM project";
$total_projects_result = $conn->query($total_projects_query);
$total_projects = $total_projects_result->fetch_assoc()['total'];

// Fetch total documents
$total_documents_query = "SELECT COUNT(*) as total FROM document";
$total_documents_result = $conn->query($total_documents_query);
$total_documents = $total_documents_result->fetch_assoc()['total'];

// Fetch stored documents
$stored_documents_query = "SELECT COUNT(*) as total FROM document WHERE documentstatus='stored'";
$stored_documents_result = $conn->query($stored_documents_query);
$stored_documents = $stored_documents_result->fetch_assoc()['total'];

// Fetch released documents
$released_documents_query = "SELECT COUNT(*) as total FROM document WHERE documentstatus='released'";
$released_documents_result = $conn->query($released_documents_query);
$released_documents = $released_documents_result->fetch_assoc()['total'];

// Calculate percentages safely
$percentage_stored = $total_documents > 0 ? round(($stored_documents / $total_documents) * 100) : 0;
$percentage_released = $total_documents > 0 ? round(($released_documents / $total_documents) * 100) : 0;

// Prepare stats array
$stats = [
    ["label" => "TOTAL OF PROJECTS", "value" => $total_projects, "icon" => "folder.png", "text" => "PROJECTS"],
    ["label" => "TOTAL OF DOCUMENTS", "value" => $total_documents, "icon" => "File text.png", "text" => "DOCUMENTS"],
    ["label" => "MY STORED DOCUMENTS", "value" => $stored_documents, "percent" => $percentage_stored, "icon" => "Database.png", "text" => "STORED DOCUMENTS"],
    ["label" => "MY RELEASED DOCUMENTS", "value" => $released_documents, "percent" => $percentage_released, "icon" => "Box.png", "text" => "RELEASED DOCUMENTS"]
];

$conn->close();
?>

<div class="stats">
  <?php foreach ($stats as $stat): ?>
    <div class="stat-box">
      <div class="stat-top">
        <p><?= $stat["label"] ?></p>
        <?php if (isset($stat["percent"])): ?>
          <span class="percent"><?= $stat["percent"] ?>%</span>
        <?php endif; ?>
      </div>
      <div class="stat-bottom">
        <div class="stat-info">
          <h2><?= $stat["value"] ?></h2>
          <span class="label"><?= $stat["text"] ?></span>
        </div>
        <div class="icon-container">
          <img src="picture/<?= $stat["icon"] ?>" alt="<?= $stat["text"] ?> Icon" />
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="mid-section">

  <div class="chart-section">
    <h3>DOCUMENT MOVEMENT OVER TIME</h3>
    <div class="graph-box">
      <div class="y-labels"><div>200</div><div>150</div><div>100</div><div>50</div><div>0</div></div>
      <div class="graph-area">
        <div class="graph-grid"></div>
        <div class="x-labels">
          <span>January</span><span>March</span><span>May</span>
          <span>July</span><span>September</span><span>November</span>
        </div>
      </div>
    </div>


    <div class="breakdown-section">
      <h3>DOCUMENT TYPE BREAKDOWN</h3>
      <div class="legend">
        <span><span class="dot" style="background:#8B5E5E"></span>Tax Declaration</span>
        <span><span class="dot" style="background:#C27C7C"></span>Lot Title</span>
        <span><span class="dot" style="background:#D88F8F"></span>Survey Plan</span>
        <span><span class="dot" style="background:#B25454"></span>CAD File</span>
        <span><span class="dot" style="background:#E0BABA"></span>Lot Data</span>
        <span><span class="dot" style="background:#F2DCDC"></span>Others</span>
      </div>
    </div>
  </div>


  <div class="recent-list">
    <h3>Recent Activity</h3>
   <?php foreach ($data["recent_activities"] as $activity): ?>
  <?php if (!is_array($activity)) continue; ?>
  <div class="recent-item">
    <div class="icon-label">
      <img src="picture/File text.png" alt="Document Icon" class="doc-icon" />
      <div>
        <span class="doc-type"><?= htmlspecialchars($activity["type"]) ?></span><br />
        <small><?= htmlspecialchars($activity["status"]) ?></small>
      </div>
    </div>
    <span class="doc-lot"><?= htmlspecialchars($activity["project_name"]) ?></span>
    <span class="doc-date"><?= htmlspecialchars($activity["date"]) ?></span>

    <?php

      $file = !empty($activity["file"])
        ? $activity["file"]
        : (!empty($activity["lot_no"]) && $activity["lot_no"] !== "N/A"
            ? 'documents/' . $activity["lot_no"] . '.pdf'
            : '');
    ?>

    <?php if (!empty($file)): ?>
      <a href="<?= htmlspecialchars($file) ?>" target="_blank" class="view-doc-link">View file</a>
    <?php else: ?>
      <span class="view-file" style="color: gray; pointer-events: none;">view file</span>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

  </div>
</div>