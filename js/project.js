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
      } else {
        // For unsupported files, just download (close modal)
        window.open(file, '_blank');
        return;
      }

      modal.style.display = 'block';
      disableAllInputsExceptModal();
    }
  });

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

// ✅ Add QR Status Update Handler
function initQRFormHandler() {
  document.querySelectorAll('.qr-validate-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData();
      const scannedQR = form.querySelector('input[name="scannedQR"]').value.trim();

      if (!scannedQR) {
        alert('Please scan the QR code before submitting.');
        return;
      }

      formData.append('projectId', form.dataset.projectid);
      formData.append('documentName', form.dataset.docname);
      formData.append('newStatus', form.dataset.newstatus);
      formData.append('scannedQR', scannedQR);

      fetch('model/update_document_status.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          alert(data.message);
          if (data.status === 'success') {
            const lastPage = sessionStorage.getItem('lastPage') || window.location.href;
            loadAdminPage(lastPage);
          }
        })
        .catch(err => {
          alert('An error occurred. Please try again.');
          console.error(err);
        });
    });
  });
}

function initQRFormToggles() {
  const forms = document.querySelectorAll('.qr-validate-form');

  forms.forEach(form => {
    const toggleBtn = form.querySelector('.toggle-qr-btn');
    const qrInput = form.querySelector('input[name="scannedQR"]');

    // Create the scan text span
    const scanText = document.createElement('span');
    scanText.textContent = 'Scan QR Code to proceed';
    scanText.style.marginRight = '10px'; // space between text and button
    scanText.style.fontStyle = 'italic';
    scanText.style.color = '#555';
    scanText.style.display = 'none';

    // Insert scanText before the toggleBtn to appear on the left
    toggleBtn.parentNode.insertBefore(scanText, toggleBtn);

    // Store original button label
    if (!toggleBtn.dataset.originalLabel) {
      toggleBtn.dataset.originalLabel = toggleBtn.textContent;
    }

    // Hide input initially off-screen
    Object.assign(qrInput.style, {
      position: 'fixed',
      left: '-9999px',
      top: 'auto',
      width: '1px',
      height: '1px',
      opacity: '0',
      pointerEvents: 'none',
    });

    // Track toggle state
    let scanning = false;

    toggleBtn.addEventListener('click', () => {
      if (!scanning) {
        // Close all other forms first
        forms.forEach(otherForm => {
          if (otherForm !== form) {
            const otherToggleBtn = otherForm.querySelector('.toggle-qr-btn');
            const otherQrInput = otherForm.querySelector('input[name="scannedQR"]');
            const otherScanText = otherToggleBtn.previousSibling;

            otherToggleBtn.textContent = otherToggleBtn.dataset.originalLabel || 'Toggle';
            otherScanText.style.display = 'none';

            Object.assign(otherQrInput.style, {
              position: 'fixed',
              left: '-9999px',
              top: 'auto',
              width: '1px',
              height: '1px',
              opacity: '0',
              pointerEvents: 'none',
            });
            otherQrInput.value = '';
          }
        });

        // Open this form
        toggleBtn.textContent = 'Cancel';
        scanText.style.display = 'inline';
        Object.assign(qrInput.style, {
          position: 'fixed',
          left: '-9999px',
          top: 'auto',
          width: '1px',
          height: '1px',
          opacity: '0',
          pointerEvents: 'auto',
        });
        qrInput.value = '';
        qrInput.focus();
        scanning = true;
      } else {
        // Close this form
        toggleBtn.textContent = toggleBtn.dataset.originalLabel;
        scanText.style.display = 'none';
        Object.assign(qrInput.style, {
          position: 'fixed',
          left: '-9999px',
          top: 'auto',
          width: '1px',
          height: '1px',
          opacity: '0',
          pointerEvents: 'none',
        });
        qrInput.value = '';
        scanning = false;
      }
    });

    qrInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (!qrInput.value.trim()) return;
        form.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });
  });
}
