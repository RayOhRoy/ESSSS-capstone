function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  const cleanPage = page.split('?')[0];
  if (!contentArea) return;

  const currentPage = sessionStorage.getItem('currentPage');
  if (currentPage) {
    sessionStorage.setItem('lastPage', currentPage);
  }
  sessionStorage.setItem('currentPage', page);

  fetch(page)
    .then(res => res.text())
    .then(html => {
      contentArea.innerHTML = html;

      // Initialize correct handlers after loading content
      if (page === 'user_list.php') {
        initUserListHandlers();
      } else if (page === 'profile.php') {
        initChangePassword();
      } else if (page === 'project_list.php') {
        initPreviewModal();
      } else if (page === 'upload.php') {
        initUploadHandlers();
      } else if (page === 'search.php') {
        initQRSearch();
        initLiveProjectSearch();
      } else if (page === 'activity_log.php') {
        filterByDate();
      } else if (cleanPage  === 'project.php') {
        initBackButton();
        initImageModal();
        initDocumentTabSwitching();
        initQRFormHandler();
        initQRFormToggles();
      } else if (cleanPage  === 'edit_project.php') {
        initToggleEditSave();
      }else {
        initUserMenuDropdown();
      }
    })
    .catch(err => {
      console.error('Failed to load admin page:', err);
    });
}

function initBackButton() {
  const backBtn = document.getElementById('project-back-btn');
  if (!backBtn) return;

  backBtn.addEventListener('click', () => {
    const lastPage = sessionStorage.getItem('lastPage') || 'project_list.php';
    loadAdminPage(lastPage);
  });
}

function initToggleEditSave() {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  contentArea.removeEventListener('click', toggleClickHandler); // remove if exists

  contentArea.addEventListener('click', toggleClickHandler);
}

function toggleClickHandler(e) {
  const editBtn = e.target.closest('#update-edit-btn');
  const saveBtn = e.target.closest('#update-save-btn');

  if (editBtn) {
    handleEditButton();
  }

  if (saveBtn) {
    submitForm();
  }
}

function filterByDate() {
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    const dateFrom = dateFromInput.value;
    const dateTo = dateToInput.value;
    const employeeFilter = document.getElementById('employeeFilter').value.toLowerCase();

    // Ensure "To" date cannot be earlier than "From"
    if (dateFrom) {
        dateToInput.min = dateFrom;
    } else {
        dateToInput.removeAttribute('min');
    }

    // Optionally ensure "From" date cannot be later than "To"
    if (dateTo) {
        dateFromInput.max = dateTo;
    } else {
        dateFromInput.removeAttribute('max');
    }

    const table = document.getElementById('projectTable');
    const tbody = table.tBodies[0];
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        const employeeName = row.getAttribute('data-employee').toLowerCase();
        const timestampCell = row.cells[3].textContent.trim();

        let rowDate = new Date(timestampCell);
        if (isNaN(rowDate)) {
            const parts = timestampCell.split(' ');
            const day = parts[0];
            const month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"].indexOf(parts[1]);
            const year = parts[2];
            const timeParts = parts[3].split(':');
            rowDate = new Date(year, month, day, timeParts[0], timeParts[1]);
        }

        let show = true;

        if (employeeFilter && !employeeName.includes(employeeFilter)) {
            show = false;
        }

        if (show) {
            if (dateFrom) {
                const fromDate = new Date(dateFrom);
                if (rowDate < fromDate) show = false;
            }
            if (dateTo) {
                const toDate = new Date(dateTo);
                toDate.setHours(23, 59, 59, 999);
                if (rowDate > toDate) show = false;
            }
        }

        row.style.display = show ? '' : 'none';
    }
}

function filterByEmployee() {
    // Reuse filterByDate to apply combined filters
    filterByDate();
}


function clearFilter() {
    document.getElementById("employeeFilter").value = "";
    filterByEmployee();
}

function initPreviewModal() {
  const previewButtons = document.querySelectorAll('.preview-btn');
  const modal = document.getElementById('previewModal');
  const closeModalBtn = document.getElementById('closeModal');
  const openBtn = modal.querySelector('.open-btn');

  let selectedProjectIdForPreview = null; // <-- used for OPEN button

  if (!modal || !closeModalBtn || !openBtn) return;

  previewButtons.forEach(button => {
    button.addEventListener('click', () => {
      const tr = button.closest('tr');
      if (!tr) return;

      const projectID = tr.querySelector('td:nth-child(1)').textContent.trim();
      if (!projectID) return;

      // Store project ID for use in OPEN button
      selectedProjectIdForPreview = projectID;

      // Show loading or clear modal content
      modal.querySelector('.preview-projectname').textContent = projectID;
      const details = modal.querySelector('.project-details');
      const docTableBody = modal.querySelector('.document-table tbody');
      if (details) details.innerHTML = '<p>Loading project details...</p>';
      if (docTableBody) docTableBody.innerHTML = '<tr><td colspan="3">Loading documents...</td></tr>';

      // Fetch project info from server
      fetch('model/get_project_info.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ projectId: projectID })
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          if (details) details.innerHTML = `<p>Error: ${data.message}</p>`;
          if (docTableBody) docTableBody.innerHTML = '<tr><td colspan="3">No documents found.</td></tr>';
          return;
        }

        const project = data.project;

        if (details) {
          details.innerHTML = `
            <p><strong>Lot No.:</strong> ${project.LotNo || ''}</p>
            <p><strong>Address:</strong> ${project.FullAddress || ''}</p>
            <p><strong>Survey Type:</strong> ${project.SurveyType || ''}</p>
            <p><strong>Client:</strong> ${project.ClientName || ''}</p>
            <p><strong>Agent:</strong> ${project.Agent || 'not available'}</p>
            <p><strong>Survey Period:</strong> ${project.SurveyStartDate || ''} - ${project.SurveyEndDate || ''}</p>
          `;
        }

        const qrImage = modal.querySelector('.qr-img');
        if (qrImage && project.ProjectID) {
          qrImage.src = `uploads/${project.ProjectID}/${project.ProjectID}-QR.png`;
          qrImage.alt = `QR Code for ${project.ProjectID}`;
        }

        if (docTableBody) {
          docTableBody.innerHTML = ''; // Clear table body

          if (!project.documents || project.documents.length === 0) {
            docTableBody.innerHTML = '<tr><td colspan="3">No documents found.</td></tr>';
          } else {
            project.documents.forEach(doc => {
              let physicalStatusClass = '';
              if (doc.physical_status === 'STORED') {
                physicalStatusClass = 'stored';
              } else if (doc.physical_status === 'RELEASED') {
                physicalStatusClass = 'released';
              }

              let digitalStatusClass = '';
              let digitalStatusText = '';
              if (doc.digital_status === 'available') {
                digitalStatusClass = 'available';
                digitalStatusText = 'AVAILABLE';
                if (doc.physical_status === 'RELEASED') {
                  digitalStatusClass += ' released';
                }
              }

              docTableBody.innerHTML += `
                <tr>
                  <td>${doc.name}</td>
                  <td class="status ${physicalStatusClass}">${doc.physical_status || ''}</td>
                  <td class="status ${digitalStatusClass}">${digitalStatusText}</td>
                </tr>
              `;
            });
          }
        }

        modal.style.display = 'block'; // Show modal
      })
      .catch(error => {
        if (details) details.innerHTML = `<p>Error fetching data</p>`;
        if (docTableBody) docTableBody.innerHTML = '<tr><td colspan="3">Error loading documents.</td></tr>';
        console.error('Error:', error);
      });
    });
  });

  // Handle OPEN button click
  openBtn.addEventListener('click', () => {
    if (selectedProjectIdForPreview) {
      loadAdminPage('project.php?projectId=' + encodeURIComponent(selectedProjectIdForPreview));
    } else {
      alert("No project selected.");
    }
  });

  closeModalBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  window.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
}

function initUploadHandlers() {
  updateDocumentTableBasedOnSelection();
  clearApproval();

  const requestType = document.getElementById('requestType');
  const approvalRadios = document.querySelectorAll('input[name="approval"]');
  const startDateInput = document.getElementById('startDate');
  const endDateInput = document.getElementById('endDate');

  // Request type change listener
  if (requestType) {
    requestType.addEventListener('change', updateDocumentTableBasedOnSelection);
  }

  // Approval radio buttons change listener
  approvalRadios.forEach(radio => {
    radio.addEventListener('change', updateDocumentTableBasedOnSelection);
  });

  // Disable end date by default
  if (endDateInput) {
    endDateInput.disabled = true;
  }

  // Enable and constrain end date once start date is selected
  if (startDateInput && endDateInput) {
    startDateInput.addEventListener('change', function () {
      const selectedDate = this.value;

      if (selectedDate) {
        endDateInput.disabled = false;
        endDateInput.min = selectedDate;

        // Clear end date if it's before start date
        if (endDateInput.value && endDateInput.value < selectedDate) {
          endDateInput.value = '';
        }
      } else {
        endDateInput.disabled = true;
        endDateInput.value = '';
      }
    });
  }
}

function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;

  const container = document.getElementById('toast-container');
  container.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 4000);
}

function initUserListHandlers() {
  console.log("User list handlers initialized with event delegation");

  const container = document.querySelector('.userlist-grid');
  if (!container) {
    console.warn("User list container not found");
    return;
  }

  container.addEventListener('click', function (e) {
    const ellipsis = e.target.closest('.iconEllipsis');
    if (ellipsis) {
      e.stopPropagation();
      const userId = ellipsis.getAttribute('data-id');
      const dropdown = document.getElementById(`dropdown-${userId}`);
      if (!dropdown) return;

      container.querySelectorAll('.status-dropdown').forEach(dd => {
        if (dd !== dropdown) dd.style.display = 'none';
      });

      dropdown.style.display = (dropdown.style.display === 'flex') ? 'none' : 'flex';
      return;
    }

    const option = e.target.closest('.status-option');
    if (option) {
      const accountId = option.getAttribute('data-id');
      const newStatus = option.getAttribute('data-status');

      if (newStatus === 'Delete') {
        if (confirm('Are you sure you want to delete this account? This action cannot be undone.')) {
          fetch('model/delete_account.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ account_id: accountId })
          })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                const userCard = option.closest('.user-card');
                const empId = userCard.querySelector('.user-id')?.textContent || 'Unknown ID';

                if (userCard) userCard.remove();
                showToast(`Account ${empId} deleted successfully.`, 'success');
              } else {
                showToast('Failed to delete account: ' + (data.message || 'Unknown error'), 'error');
              }
            })
            .catch(() => {
              showToast('Error deleting account. Please try again.', 'error');
            });
        }
      } else {
        // Confirm before status change
        const confirmMsg = `Are you sure you want to ${newStatus.toLowerCase()} this account?`;
        if (!confirm(confirmMsg)) return;

        fetch("model/update_status.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${encodeURIComponent(accountId)}&status=${encodeURIComponent(newStatus)}`
        })
          .then(res => res.text())
          .then(response => {
            const userCard = container.querySelector(`.iconEllipsis[data-id="${accountId}"]`).closest('.user-card');
            const empId = userCard.querySelector('.user-id')?.textContent || 'Unknown ID';

            showToast(`Account ${empId} ${newStatus.toLowerCase()}d successfully.`, 'success');

            const statusBadge = userCard.querySelector('.user-status');

            // Update status text & class
            statusBadge.textContent = newStatus.toUpperCase();
            statusBadge.className = "user-status " + (newStatus.toLowerCase() === "active" ? "status-active" : "status-inactive");

            // Update dropdown
            const dropdown = document.getElementById(`dropdown-${accountId}`);
            if (dropdown) {
              dropdown.innerHTML = '';

              if (newStatus === 'Active') {
                dropdown.innerHTML += `<div class="status-option" data-id="${accountId}" data-status="Inactive">Deactivate</div>`;
              } else {
                dropdown.innerHTML += `<div class="status-option" data-id="${accountId}" data-status="Active">Activate</div>`;
              }

              dropdown.innerHTML += `<div class="status-option" data-id="${accountId}" data-status="Delete">Delete</div>`;
              dropdown.style.display = 'none';
            }

          })
          .catch(err => {
            console.error("Error updating status:", err);
            showToast("Error updating status. Please try again.", 'error');
          });
      }

      return;
    }
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!container.contains(e.target)) {
      container.querySelectorAll('.status-dropdown').forEach(dd => dd.style.display = 'none');
    }
  });

  const addBtn = document.getElementById('add-account-btn');
  const modal = document.getElementById('modalAddUser');
  const closeBtn = modal.querySelector('.close');
  const accountIdInput = modal.querySelector('#employeeid');

  addBtn.addEventListener('click', () => {
    const nextId = addBtn.getAttribute('data-next-id');
    if (accountIdInput) {
      accountIdInput.value = nextId;
    }
    modal.style.display = 'block';
  });

  closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  window.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
}
