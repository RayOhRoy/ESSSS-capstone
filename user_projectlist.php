<?php
$dataFile = 'data.json';
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$projects = $data['projects'] ?? [];
?>

<div class="topbar">
  <h1>Upload Project</h1>
  <div class="topbar-content">
    <div class="search-container">
      <input type="text" placeholder="Search Project" />
    </div>
    <div class="icons">
      <span class="notif">🔔</span>
      <span class="user-icon">👤 User</span>
    </div>
  </div>
</div>

<hr class="top-line">

<div class="projectlist-header">
  <button class="sort-btn active-sort" onclick="sortTable(0, this)">Project Name ⬇</button>
  <button class="sort-btn" onclick="sortTable(1, this)">Client Name</button>
  <button class="sort-btn" onclick="sortTable(2, this)">Municipality</button>
  <button class="sort-btn" onclick="sortTable(3, this)">Physical Storage Location</button>
  <button class="sort-btn" onclick="sortTable(4, this)">Survey Type</button>
  <button class="sort-btn">Preview</button>
  <button class="sort-btn">Update</button>
</div>

<table class="projectlist-table" id="projectTable">
  <tbody>
    <?php foreach ($projects as $project): ?>
      <tr>
        <td><span class="entry-text"><?= htmlspecialchars($project['lot_number']) ?></span></td>
        <td><span class="entry-text"><?= htmlspecialchars($project['client_name']) ?></span></td>
        <td><span class="entry-text"><?= htmlspecialchars($project['municipality']) ?></span></td>
        <td><span class="entry-text"><?= htmlspecialchars($project['physical_location']) ?></span></td>
        <td><span class="entry-text"><?= htmlspecialchars($project['survey_type']) ?></span></td>
        <td><span class="preview-text">Preview</span></td>
        <td><button class="update-btn">Update Here</button></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="floating-add-btn">
  <img src="picture/Folder plus.png" alt="Add Folder" />
</div>

<script>
function sortTable(col, btn) {
  const table = document.getElementById("projectTable");
  let rows = Array.from(table.rows);
  rows.sort((a, b) => {
    const x = a.cells[col].innerText.trim().toLowerCase();
    const y = b.cells[col].innerText.trim().toLowerCase();
    return x.localeCompare(y);
  });
  rows.forEach(row => table.appendChild(row));

  document.querySelectorAll(".sort-btn").forEach(b => b.classList.remove("active-sort"));
  btn.classList.add("active-sort");
}


function filterTable() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const table = document.getElementById("projectTable");
  const rows = table.getElementsByTagName("tr");
  Array.from(rows).forEach(row => {
    const rowText = row.innerText.toLowerCase();
    row.style.display = rowText.includes(input) ? "" : "none";
  });
}
</script>
