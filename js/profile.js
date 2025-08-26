function initProfilePage() {
  const openBtn = document.querySelector('.edit-profile-link');
  const modal = document.getElementById('changePasswordModal');
  const cancelBtn = document.getElementById('cancelChangePassword');

  if (openBtn && modal && cancelBtn) {
    openBtn.addEventListener('click', () => {
      modal.style.display = 'flex';
    });

    cancelBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    // Close modal if clicking outside the modal content
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });
  } else {
    console.warn('Profile modal elements missing!');
  }

  console.log("Profile.js initialized!");
}

window.initProfilePage = initProfilePage;
