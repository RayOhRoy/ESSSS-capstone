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
    const reportType = document.getElementById("reportType")?.value.trim() || "Project"; // ✅ added dynamic report type

    if (!projectId) {
      alert("Please select a project first.");
      return;
    }
    if (!description) {
      alert("Please enter a report description.");
      return;
    }

    const formData = new FormData();
    formData.append("report_type", reportType);      // ✅ added
    formData.append("project_id", projectId);
    formData.append("report_description", description);

    // 🧩 Log everything being sent to PHP
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
      const result = await response.text();

      // Show result dynamically
      const liveResults = document.getElementById("liveResults");
      if (liveResults) liveResults.innerHTML = `<p>${result || "Report successfully added!"}</p>`;

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