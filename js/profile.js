function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  // Reset page-specific flags before loading a new page
  changePasswordInitialized = false;

  fetch(page)
    .then(res => res.text())
    .then(html => {
      contentArea.innerHTML = html;

      // Initialize page-specific functionality
      if (page === 'profile.php') {
        initChangePassword();
      }
      // Add more initializers here as needed
    })
    .catch(err => {
      console.error('Failed to load admin page:', err);
    });
}


let changePasswordInitialized = false;

function initChangePassword() {
  if (changePasswordInitialized) return; // prevent duplicate listeners
  changePasswordInitialized = true;

  console.log("Change password event delegation initialized");

  const body = document.body;
  const modal = document.getElementById('changePasswordModal');
  const cancelBtn = document.getElementById('cancelChangePassword');

  if (!modal || !cancelBtn) {
    console.warn("Change password modal elements missing!");
    return;
  }

  body.addEventListener('click', function (e) {
    const changePassBtn = e.target.closest('.edit-profile-link');
    if (changePassBtn) {
      modal.style.display = 'flex';
      return;
    }

    if (e.target === cancelBtn || e.target === modal) {
      modal.style.display = 'none';
      return;
    }
  });
}

