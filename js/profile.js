let changePasswordInitialized = false;

function initChangePassword() {
  console.log("Change password event delegation initialized");

  const body = document.body;
  const modal = document.getElementById('changePasswordModal');
  const cancelBtn = document.getElementById('cancelChangePassword');

  if (!modal || !cancelBtn) {
    console.warn("Change password modal elements missing!");
    return;
  }

  // Remove any previous click handler to avoid stacking multiple listeners
  body.removeEventListener('click', handleBodyClick); // clean slate
  body.addEventListener('click', handleBodyClick);

  function handleBodyClick(e) {
    const changePassBtn = e.target.closest('.edit-profile-link');
    if (changePassBtn) {
      modal.style.display = 'flex';
      return;
    }

    if (e.target === cancelBtn || e.target === modal) {
      modal.style.display = 'none';
      return;
    }
  }
}
