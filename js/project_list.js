function sortTable(col, btn) {
  const table = document.getElementById("projectTable");
  const tbody = table.querySelector("tbody");

  let rows = Array.from(tbody.rows); // only body rows
  rows.sort((a, b) => {
    const x = a.cells[col].innerText.trim().toLowerCase();
    const y = b.cells[col].innerText.trim().toLowerCase();
    return x.localeCompare(y);
  });

  rows.forEach(row => tbody.appendChild(row)); // re-append sorted rows

  document.querySelectorAll(".sort-btn").forEach(b => b.classList.remove("active-sort"));
  btn.classList.add("active-sort");
}

let sortDirection = {};   // store ascending/descending state per column
let lastSortedCol = null; // track last sorted column

function sortTable(col, btn) {
  const table = document.getElementById("projectTable");
  const tbody = table.querySelector("tbody");
  let rows = Array.from(tbody.rows);

  // If we clicked a new column, reset to ascending
  if (lastSortedCol !== col) {
    sortDirection[col] = true; // ascending
  } else {
    sortDirection[col] = !sortDirection[col]; // toggle
  }
  lastSortedCol = col;

  // Sort rows
  rows.sort((a, b) => {
    const x = a.cells[col].innerText.trim().toLowerCase();
    const y = b.cells[col].innerText.trim().toLowerCase();
    return sortDirection[col] ? x.localeCompare(y) : y.localeCompare(x);
  });

  // Append sorted rows back
  rows.forEach(row => tbody.appendChild(row));

  // Reset all sort button styles and arrows
  document.querySelectorAll(".sort-btn").forEach(b => {
    b.classList.remove("active-sort");
    b.innerHTML = b.innerHTML.replace(/<i.*<\/i>/, ''); // remove arrow
  });

  // Add active style and arrow to clicked button
  btn.classList.add("active-sort");
  const arrow = sortDirection[col]
    ? '<i class="fa fa-long-arrow-up" style="margin-left:5px;"></i>'
    : '<i class="fa fa-long-arrow-down" style="margin-left:5px;"></i>';
  btn.innerHTML = btn.textContent + arrow;
}
const defaultBtn = document.querySelector(".sort-btn.active-sort");
if (defaultBtn) {
  sortTable(0, defaultBtn);
}

function redirectToUpdate(button) {
  const projectId = button.getAttribute('data-projectid');
  loadAdminPage('edit_project.php?projectId=' + encodeURIComponent(projectId));
}
