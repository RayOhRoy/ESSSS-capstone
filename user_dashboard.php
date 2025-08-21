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
  <h1>Upload Project</h1>
  <div class="topbar-content">
    <div class="search-container">
      <input type="text" placeholder="Search Project" />
    </div>
    <div class="icons">
      <span class="notif"></span>
      <span class="user-icon">👤 User</span>
    </div>
  </div>
</div>

<hr class="top-line" />
<div class="welcome">Welcome, [Employee Name]!</div>


<div class="stats">
  <?php
  $stats = [
    ["label" => "TOTAL OF PROJECTS", "value" => $data["total_projects"], "percent" => 100, "icon" => "folder.png", "text" => "PROJECTS"],
    ["label" => "TOTAL OF DOCUMENTS", "value" => $data["total_documents"], "percent" => 100, "icon" => "File text.png", "text" => "DOCUMENTS"],
    ["label" => "MY STORED DOCUMENTS", "value" => $data["stored_documents"], "percent" => $data["percentage_stored"], "icon" => "Database.png", "text" => "STORED DOCUMENTS"],
    ["label" => "MY RELEASED DOCUMENTS", "value" => $data["released_documents"], "percent" => $data["percentage_released"], "icon" => "Box.png", "text" => "RELEASED DOCUMENTS"]
  ];

  foreach ($stats as $stat): ?>
    <div class="stat-box">
      <div class="stat-top">
        <p><?= $stat["label"] ?></p>
        <span class="percent"><?= $stat["percent"] ?>%</span>
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
          <span class="doc-type"><?= htmlspecialchars($activity["type"]) ?></span>
        </div>
        <span class="doc-status"><?= htmlspecialchars($activity["status"]) ?></span>
        <span class="doc-lot"><?= htmlspecialchars($activity["lot_no"]) ?></span>
        <span class="doc-date"><?= htmlspecialchars($activity["date"]) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
