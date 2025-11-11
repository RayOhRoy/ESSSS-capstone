// 🔹 Fetch projects dynamically by type
async function getProjectsByType(type) {
  try {
    const response = await fetch(`model/get_projectbytype.php?type=${encodeURIComponent(type)}`);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const projects = await response.json();
    return projects; // array of ProjectIDs
  } catch (err) {
    console.error("Error fetching projects:", err);
    return [];
  }
}

// 🔹 Filter project list when report type changes
function initReportFilter() {
  const reportType = document.getElementById("reportType");
  const reportProject = document.getElementById("reportProject");
  if (!reportType || !reportProject) return;

  reportType.addEventListener("change", async () => {
    const selectedType = reportType.value;
    reportProject.innerHTML = `<option value="">Search Project Name...</option>
                               <option disabled>Loading...</option>`;

    const projects = await getProjectsByType(selectedType);

    if (!projects || projects.length === 0) {
      reportProject.innerHTML = `<option value="">No projects found</option>`;
      return;
    }

    const optionsHTML = [`<option value="">Search Project Name...</option>`]
      .concat(projects.map(p => `<option value="${p}">${p}</option>`))
      .join("");

    reportProject.innerHTML = optionsHTML;

    // Refresh select2 if applicable
    if (window.$ && $.fn.select2) {
      $(reportProject).trigger("change.select2");
    }
  });
}

// 🔹 Handle form submission
function reportForm() {
  const form = document.getElementById("reportForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const projectId = document.getElementById("reportProject").value.trim();
    const description = document.getElementById("reportDescription").value.trim();
    const reportType = document.getElementById("reportType")?.value.trim() || "Project"; // ✅ dynamic type

    if (!projectId) {
      alert("Please select a project first.");
      return;
    }
    if (!description) {
      alert("Please enter a report description.");
      return;
    }

    const formData = new FormData();
    formData.append("report_type", reportType);
    formData.append("project_id", projectId);
    formData.append("report_description", description);

    console.log("🔹 Data being sent to PHP:");
    for (const [key, value] of formData.entries()) {
      console.log(`${key}:`, value);
    }

    try {
      const response = await fetch("model/insert_report.php", {
        method: "POST",
        body: formData
      });

      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const result = await response.json();

      if (result.status === "success") {
        alert("Report successfully added!");
        await loadReports(); // ✅ Refresh list automatically
      } else {
        alert(result.message || "Error inserting report.");
      }

      // Reset fields
      document.getElementById("reportDescription").value = "";
      document.getElementById("reportProject").selectedIndex = 0;
      if (window.$ && $.fn.select2) {
        $("#reportProject").trigger("change.select2");
      }

    } catch (err) {
      console.error("Error submitting report:", err);
      alert("Failed to generate report. Please try again.");
    }
  });
}

// 🔹 Load all reports dynamically into liveResults (with optional status filter)
async function loadReports(filterStatus = "") {
  const liveResults = document.getElementById("liveResults");
  if (!liveResults) return;

  try {
    // ✅ Pass status filter as query param
    const response = await fetch(`model/get_reports.php?status=${encodeURIComponent(filterStatus)}`);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();

    if (data.status !== "success" || !data.reports.length) {
      liveResults.innerHTML = `<p class="no-report">No reports found</p>`;
      return;
    }

    liveResults.innerHTML = ""; // clear before render

    data.reports.forEach(r => {
      // Button HTML container using flex
      let buttonHTML = `
        <div class="button-container" style="display: flex; justify-content: flex-end; margin-top: 10px;">
          ${r.reportStatus === "PENDING" ? `
            <button class="mark-resolve-btn" style="
              background-color: #28a745; 
              color: #fff; 
              border: none; 
              border-radius: 6px; 
              padding: 6px 12px; 
              cursor: pointer;
            ">MARK AS RESOLVE</button>
          ` : `
            <button disabled style="
              background-color: #6c757d; 
              color: #fff; 
              border: none; 
              border-radius: 6px; 
              padding: 6px 12px;
              cursor: not-allowed;
            ">RESOLVED</button>
          `}
        </div>
      `;

      const reportCardHTML = `
        <div class="report-card" style="
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        ">
          <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <tr>
              <td style="padding: 6px 10px; vertical-align: top; width: 25%;">${r.documentID}</td>
              <td style="padding: 6px 10px; vertical-align: top; width: 25%;">${r.projectID}</td>
              <td style="padding: 6px 10px; vertical-align: top; width: 25%;">
                <div style="display: flex; flex-direction: column; justify-content: flex-start;">
                  <div style="text-transform: uppercase; margin-bottom: 2px; margin-top: -4px;">
                    ${r.employeeName}
                  </div>
                  <div style="font-size: 13px; color: #555;">
                    ${r.employeePosition}
                  </div>
                </div>
              </td>
              <td style="padding: 6px 10px; text-align: right; vertical-align: top; width: 25%;">${r.time}</td>
            </tr>
          </table>

          <div class="report-desc" style="
              font-weight: 500;
              border: 1px solid #ddd;
              border-radius: 6px;
              padding: 10px;
              margin-top: 20px;
              min-height: 80px;
              background: #fafafa;
              white-space: pre-wrap;
              overflow-wrap: break-word;
              line-height: 1.4;
          ">
            ${r.reportDesc}
          </div>

          ${buttonHTML}
        </div>
      `;

      liveResults.insertAdjacentHTML("beforeend", reportCardHTML);

      const card = liveResults.lastElementChild;
      const btnContainer = card.querySelector(".button-container");

      // ✅ Function to attach the MARK AS RESOLVE click listener
      function attachMarkListener() {
        const markBtn = btnContainer.querySelector(".mark-resolve-btn");
        if (!markBtn) return;

        markBtn.addEventListener("click", () => {
          // Replace with CONFIRM + CANCEL buttons
          btnContainer.innerHTML = `
        <button class="confirm-btn" style="
          background-color: #7B0302; 
          color: #fff; 
          border: none; 
          border-radius: 6px; 
          padding: 6px 12px; 
          cursor: pointer; 
          margin-right: 6px;
        ">CONFIRM</button>
        <button class="cancel-btn" style="
          background-color: #6c757d; 
          color: #fff; 
          border: none; 
          border-radius: 6px; 
          padding: 6px 12px; 
          cursor: pointer;
        ">CANCEL</button>
      `;

          // CANCEL restores MARK AS RESOLVE button
          const cancelBtn = btnContainer.querySelector(".cancel-btn");
          cancelBtn.addEventListener("click", () => {
            btnContainer.innerHTML = `
          <button class="mark-resolve-btn" style="
            background-color: #28a745; 
            color: #fff; 
            border: none; 
            border-radius: 6px; 
            padding: 6px 12px; 
            cursor: pointer;
          ">MARK AS RESOLVE</button>
        `;
            attachMarkListener(); // ✅ Reattach listener
          });

          // CONFIRM updates status in backend
          const confirmBtn = btnContainer.querySelector(".confirm-btn");
          confirmBtn.addEventListener("click", async () => {
            try {
              const response = await fetch("model/update_report_status.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ reportID: r.reportID, status: "RESOLVED" })
              });
              const result = await response.json();
              if (result.status === "success") {
                alert(`Report ${r.ReportID} successfully resolved!`);
                btnContainer.innerHTML = `
              <button disabled style="
                background-color: #6c757d; 
                color: #fff; 
                border: none; 
                border-radius: 6px; 
                padding: 6px 12px;
                cursor: not-allowed;
              ">RESOLVED</button>
            `;
              } else {
                alert(result.message || "Failed to resolve report.");
              }
            } catch (err) {
              console.error(err);
              alert("Error updating report status.");
            }
          });
        });
      }
      attachMarkListener();
    });
  } catch (err) {
    console.error("Error loading reports:", err);
    liveResults.innerHTML = `<p class="error">Failed to load reports.</p>`;
  }
}

// 🔹 Filter by Report Status dropdown
function initReportStatusFilter() {
  const statusSelect = document.getElementById("reportStatus");
  if (!statusSelect) return;

  statusSelect.addEventListener("change", async () => {
    const selectedStatus = statusSelect.value.trim();
    await loadReports(selectedStatus); // ✅ Filter results dynamically
  });
}