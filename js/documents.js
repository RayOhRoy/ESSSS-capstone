function redirectToProjectList(button) {
  const municipality = button.getAttribute("data-municipality");
  loadAdminPage("project_list.php?municipality=" + encodeURIComponent(municipality));
}
