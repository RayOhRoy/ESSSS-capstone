let imageModalInitialized = false;

function initImageModal() {
  imageModalInitialized = true;

  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('modalImage');
  const closeBtn = modal.querySelector('.close-image-modal');
  const body = document.body;

  if (!modal || !modalImg || !closeBtn) {
    console.warn('Image modal elements not found.');
    return;
  }

  // Delegate event to handle dynamically loaded preview buttons
  document.body.addEventListener('click', (e) => {
    const target = e.target.closest('.preview-doc');
    if (target) {
      const file = target.getAttribute('data-file');
      if (!file) return;

      modalImg.src = file;
      modal.style.display = 'block';
      disableAllInputsExceptModal();
    }
  });

  // Close modal
  function closeModal() {
    modal.style.display = 'none';
    modalImg.src = ''; // Clear image
    enableAllInputs();
  }

  // Close modal on click outside or on close
  body.addEventListener('click', (e) => {
    if (e.target === closeBtn || e.target === modal) {
      closeModal();
    }
  });

  // Disable all inputs except modal
  function disableAllInputsExceptModal() {
    document.querySelectorAll('input, select, textarea, button').forEach(el => {
      if (!modal.contains(el)) el.disabled = true;
    });
  }

  // Enable all inputs
  function enableAllInputs() {
    document.querySelectorAll('input, select, textarea, button').forEach(el => {
      el.disabled = false;
    });
  }
}
