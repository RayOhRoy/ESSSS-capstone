// ===== GLOBAL VARIABLES =====
let lastClickedRow = null;
let clickTimer = null;

// ===== ROW CLICK HANDLER (SINGLE + DOUBLE) =====
function handleRowClick(row) {
  // If a click occurs before timer finishes → it's a double-click
  if (clickTimer) {
    clearTimeout(clickTimer);
    clickTimer = null;

    // === DOUBLE CLICK ACTION ===
    const projectId = row.getAttribute("data-projectid");
    if (projectId) {
      loadAdminPage("project.php?projectId=" + encodeURIComponent(projectId));
    }
  } else {
    // === SINGLE CLICK ACTION ===
    clickTimer = setTimeout(() => {
      // Highlight the selected row
      if (lastClickedRow && lastClickedRow !== row) {
        lastClickedRow.classList.remove("highlighted-row");
      }

      row.classList.toggle("highlighted-row");
      lastClickedRow = row.classList.contains("highlighted-row") ? row : null;

      // Reset timer
      clickTimer = null;
    }, 250); // 250ms delay to detect double-click
  }
}

// ===== BUTTON ACTIONS =====
function redirectToUpdate(button) {
  const projectId = button.getAttribute("data-projectid");
  loadAdminPage("edit_project.php?projectId=" + encodeURIComponent(projectId));
}

// ===== SORTING =====
let sortDirection = {};
let lastSortedCol = null;

function sortTable(col, btn) {
  const table = document.getElementById("projectTable");
  const tbody = table.querySelector("tbody");
  let rows = Array.from(tbody.rows);

  // Toggle sort direction
  if (lastSortedCol !== col) {
    sortDirection[col] = true;
  } else {
    sortDirection[col] = !sortDirection[col];
  }
  lastSortedCol = col;

  // Sort rows
  rows.sort((a, b) => {
    const x = a.cells[col].innerText.trim().toLowerCase();
    const y = b.cells[col].innerText.trim().toLowerCase();
    return sortDirection[col] ? x.localeCompare(y) : y.localeCompare(x);
  });

  // Re-append sorted rows
  rows.forEach(row => tbody.appendChild(row));

  // Reset button styles
  document.querySelectorAll(".sort-btn").forEach(b => {
    b.classList.remove("active-sort");
    b.innerHTML = b.innerHTML.replace(/<i.*<\/i>/, '');
  });

  // Add active arrow
  btn.classList.add("active-sort");
  const arrow = sortDirection[col]
    ? '<i class="fa fa-long-arrow-up" style="margin-left:5px;"></i>'
    : '<i class="fa fa-long-arrow-down" style="margin-left:5px;"></i>';
  btn.innerHTML = btn.textContent + arrow;

  attachRowAndButtonHandlers();
}

// ===== BUTTON CLICK BLOCKERS =====
function attachButtonClickBlockers() {
  const buttons = document.querySelectorAll(".update-btn, .preview-btn");
  buttons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation(); // prevent triggering row click
      const projectId = btn.getAttribute("data-projectid");

      if (btn.classList.contains("update-btn")) {
        redirectToUpdate(btn);
      }
      // .preview-btn handled by modal separately
    });
  });
}

// ===== REATTACH ROW & BUTTON HANDLERS =====
function attachRowAndButtonHandlers() {
  const rows = document.querySelectorAll("#projectTable tbody tr");

  rows.forEach((row) => {
    // No need to re-add onclick since it's already inline in HTML
    // This ensures programmatic re-bind after sorting (if needed)
    row.onclick = () => handleRowClick(row);
  });

  attachButtonClickBlockers();
}