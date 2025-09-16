let imageModalInitialized = false;

function initImageModal() {
  imageModalInitialized = true;

  const modal = document.getElementById('imageModal');
  const modalContent = document.getElementById('modalContent'); // container to inject preview
  const closeBtn = modal.querySelector('.close-image-modal');
  const downloadLink = modal.querySelector('.download-image-modal');
  const body = document.body;

  if (!modal || !modalContent || !closeBtn || !downloadLink) {
    console.warn('Modal elements not found.');
    return;
  }

  document.body.addEventListener('click', (e) => {
    const target = e.target.closest('.preview-doc');
    if (target) {
      const file = target.getAttribute('data-file');
      if (!file) return;

      const ext = file.split('.').pop().toLowerCase();

      // Reset modal content before injecting new content
      modalContent.innerHTML = '';
      downloadLink.href = file;
      downloadLink.download = file.split('/').pop();
      if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
        // Show image
        const img = document.createElement('img');
        img.src = file;
        img.style.maxWidth = '80%';
        img.style.maxHeight = '80%';
        img.style.objectFit = 'contain'; // maintain aspect ratio and fit nicely
        modalContent.appendChild(img);
      } else if (ext === 'pdf') {
        // Show PDF using embed
        const embed = document.createElement('embed');
        embed.src = file;
        embed.type = 'application/pdf';
        embed.style.width = '80%';
        embed.style.height = '80%';
        modalContent.appendChild(embed);
      } else if (['doc', 'docx', 'xls', 'xlsx'].includes(ext)) {
        // Use Google Docs Viewer for docs and excel files
        const iframe = document.createElement('iframe');
        iframe.src = `https://docs.google.com/gview?url=${encodeURIComponent(window.location.origin + file)}&embedded=true`;
        iframe.style.width = '80%';
        iframe.style.height = '80%';
        iframe.frameBorder = 0;
        modalContent.appendChild(iframe);
      }
      else {
        // For unsupported files, just download (close modal)
        window.open(file, '_blank');
        return;
      }

      modal.style.display = 'block';
      disableAllInputsExceptModal();
    }
  });

  // Close modal function
  function closeModal() {
    modal.style.display = 'none';
    modalContent.innerHTML = ''; // clear preview content
    enableAllInputs();
  }

  body.addEventListener('click', (e) => {
    if (e.target === closeBtn || e.target === modal) {
      closeModal();
    }
  });

  function disableAllInputsExceptModal() {
    document.querySelectorAll('input, select, textarea, button').forEach(el => {
      if (!modal.contains(el)) el.disabled = true;
    });
  }

  function enableAllInputs() {
    document.querySelectorAll('input, select, textarea, button').forEach(el => {
      el.disabled = false;
    });
  }
}

function initDocumentTabSwitching() {
  const btnDigital = document.getElementById('btn-digital');
  const btnPhysical = document.getElementById('btn-physical');
  const digitalSection = document.getElementById('digital-section');
  const physicalSection = document.getElementById('physical-section');

  if (!btnDigital || !btnPhysical || !digitalSection || !physicalSection) return;

  btnDigital.addEventListener('click', () => {
    digitalSection.style.display = 'block';
    physicalSection.style.display = 'none';
    btnDigital.classList.add('active-tab');
    btnPhysical.classList.remove('active-tab');
  });

  btnPhysical.addEventListener('click', () => {
    digitalSection.style.display = 'none';
    physicalSection.style.display = 'block';
    btnPhysical.classList.add('active-tab');
    btnDigital.classList.remove('active-tab');
  });
}
