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

function initReportFilter() {
  const reportType = document.getElementById("reportType");
  const reportProject = document.getElementById("reportProject");

  if (!reportType || !reportProject) return;

  reportType.addEventListener("change", async () => {
    const selectedType = reportType.value;

    // Always start with a placeholder
    reportProject.innerHTML = `<option value="">Search Project Name...</option>
                               <option disabled>Loading...</option>`;

    const projects = await getProjectsByType(selectedType);

    if (!projects || projects.length === 0) {
      // Show placeholder for no projects
      reportProject.innerHTML = `<option value="">No projects found</option>`;
      return;
    }

    // Always include the first option (no value)
    const optionsHTML = [`<option value="">Search Project Name...</option>`]
      .concat(projects.map(p => `<option value="${p}">${p}</option>`))
      .join("");

    reportProject.innerHTML = optionsHTML;

    // Refresh select2 if used
    if (window.$ && $.fn.select2) {
      $(reportProject).trigger("change.select2");
    }
  });
}