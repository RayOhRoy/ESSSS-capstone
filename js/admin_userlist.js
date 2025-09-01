function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  fetch(page)
    .then(res => res.text())
    .then(html => {
      contentArea.innerHTML = html;
      if (page === 'admin_userlist.php') {
        initUserListHandlers();
      } else if (page === 'profile.php') {
        initChangePassword();
      } else if (page === 'admin_projectlist.php') {
        initPreviewModal();
      } else if (page === 'admin_upload.php') {
        clearApproval();
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

function initUserListHandlers() {
  console.log("User list handlers initialized with event delegation");

  const container = document.querySelector('.userlist-grid');
  if (!container) {
    console.warn("User list container not found");
    return;
  }

  container.addEventListener('click', function(e) {
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
              // Remove user card from DOM
              const userCard = option.closest('.user-card');
              if (userCard) userCard.remove();
            } else {
              alert('Failed to delete account: ' + (data.message || 'Unknown error'));
            }
          })
          .catch(() => alert('Error deleting account. Please try again.'));
        }
      } else {
        // Handle Active / Inactive status update
        fetch("model/update_status.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${encodeURIComponent(accountId)}&status=${encodeURIComponent(newStatus)}`
        })
        .then(res => res.text())
        .then(response => {
          alert("Status update response: " + response);

          const statusBadge = container.querySelector(`.iconEllipsis[data-id="${accountId}"]`)
            .parentElement.querySelector('.user-status');

          statusBadge.textContent = newStatus.toUpperCase();
          statusBadge.className = "user-status " + (newStatus.toLowerCase() === "active" ? "status-active" : "status-inactive");

          const dropdown = document.getElementById(`dropdown-${accountId}`);
          if (dropdown) dropdown.style.display = 'none';
        })
        .catch(err => alert("Error updating status: " + err));
      }
      return;
    }
  });

  // Close dropdowns on outside click
  document.addEventListener('click', () => {
    container.querySelectorAll('.status-dropdown').forEach(dd => dd.style.display = 'none');
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