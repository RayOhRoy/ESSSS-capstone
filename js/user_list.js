function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

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
      } else if (page === 'qr_search.php') {
        initQRSearch();
      } else {
        initUserMenuDropdown();
      }
    })
    .catch(err => {
      console.error('Failed to load admin page:', err);
    });
}

function initPreviewModal() {
  const previewButtons = document.querySelectorAll('.preview-btn');
  const modal = document.getElementById('previewModal');
  const closeModalBtn = document.getElementById('closeModal');
  const documentsData = document.getElementById('documentsData');

  if (!modal || !closeModalBtn || !documentsData) return;

  const documentsByProject = JSON.parse(documentsData.getAttribute('data-documents') || '{}');

  previewButtons.forEach(button => {
    button.addEventListener('click', () => {
      const tr = button.closest('tr');
      if (!tr) return;

      // Extract data attributes from row
      const lotNo = tr.getAttribute('data-lotno') || '';
      const clientName = tr.getAttribute('data-clientfullname') || '';
      const agent = tr.getAttribute('data-agent') || 'not available';
      const surveyPeriod = tr.getAttribute('data-surveyperiod') || '';
      const address = tr.getAttribute('data-address') || '';
      const surveyType = tr.querySelector('td:nth-child(4)').textContent || '';
      const projectID = tr.querySelector('td:nth-child(1)').textContent || '';

      // Update modal content dynamically
      modal.querySelector('.preview-projectname').textContent = projectID;
      const details = modal.querySelector('.project-details');
      if (details) {
        details.innerHTML = `
          <p><strong>Lot No.:</strong> ${lotNo}</p>
          <p><strong>Address:</strong> ${address}</p>
          <p><strong>Survey Type:</strong> ${surveyType}</p>
          <p><strong>Client:</strong> ${clientName}</p>
          <p><strong>Physical Location:</strong> ${projectID}</p>
          <p><strong>Agent:</strong> ${agent}</p>
          <p><strong>Survey Period:</strong> ${surveyPeriod}</p>
        `;
      }

      // Build document rows dynamically based on documentsByProject data
      const docTableBody = modal.querySelector('.document-table tbody');
      if (docTableBody) {
        docTableBody.innerHTML = ''; // Clear previous rows

        const docs = documentsByProject[projectID] || [];

        if (docs.length === 0) {
          docTableBody.innerHTML = '<tr><td colspan="3">No documents found.</td></tr>';
        } else {
          docs.forEach(doc => {
            // Sample logic for physical/digital status classes (adjust as needed)
            const physicalStatusClass = doc.DocumentStatus === 'Stored' ? 'stored' : (doc.DocumentStatus === 'Released' ? 'released' : 'available');
            const digitalStatusClass = doc.DigitalLocation ? 'available' : 'released';

            docTableBody.innerHTML += `
              <tr>
                <td>${doc.DocumentName}</td>
                <td class="status ${physicalStatusClass.toLowerCase()}">${doc.DocumentStatus.toUpperCase()}</td>
                <td class="status ${digitalStatusClass}">${digitalStatusClass.toUpperCase()}</td>
              </tr>
            `;
          });
        }
      }

      modal.style.display = 'block';
    });
  });

  closeModalBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Optional: Close modal if clicking outside content
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

  if (requestType) {
    requestType.addEventListener('change', updateDocumentTableBasedOnSelection);
  }

  approvalRadios.forEach(radio => {
    radio.addEventListener('change', updateDocumentTableBasedOnSelection);
  });
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
