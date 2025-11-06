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
  const viewFlag = document.getElementById("view-flag");
  const defaultView = viewFlag?.dataset.view || "digital";

  const btnDigital = document.getElementById("btn-digital");
  const btnPhysical = document.getElementById("btn-physical");
  const digitalSection = document.getElementById("digital-section");
  const physicalSection = document.getElementById("physical-section");

  if (!btnDigital || !btnPhysical || !digitalSection || !physicalSection) return;

  const isPhysical = defaultView === "physical";

  digitalSection.style.display = isPhysical ? "none" : "block";
  physicalSection.style.display = isPhysical ? "block" : "none";
  btnDigital.classList.toggle("active-tab", !isPhysical);
  btnPhysical.classList.toggle("active-tab", isPhysical);

  // Normal tab switching
  btnDigital.addEventListener("click", () => {
    digitalSection.style.display = "block";
    physicalSection.style.display = "none";
    btnDigital.classList.add("active-tab");
    btnPhysical.classList.remove("active-tab");
  });

  btnPhysical.addEventListener("click", () => {
    digitalSection.style.display = "none";
    physicalSection.style.display = "block";
    btnPhysical.classList.add("active-tab");
    btnDigital.classList.remove("active-tab");
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

const espIP = "https://unostentatious-unconfected-marya.ngrok-free.dev"; // Replace with your ESP32 IP
let lastRelayTime = 0; // timestamp of last relay trigger in ms

// 🔹 Toggle relay (send unlock signal only)
function Relay(lockNumber) {
  const now = Date.now();

  // Check 10-second cooldown
  if (now - lastRelayTime < 10000) {
    console.log(`Relay is on cooldown. Try again in ${Math.ceil((10000 - (now - lastRelayTime)) / 1000)}s`);
    return;
  }

  lastRelayTime = now;

  fetch(`${espIP}/relay?lock=${lockNumber}&action=unlock`)
    .then(response => response.text())
    .then(data => console.log(`ESP [Relay ${lockNumber}] triggered:`, data))
    .catch(err => console.error("ESP connection failed:", err));
}

// 🔹 Initialize QR scanning logic
function initQRFormToggles() {
  const forms = document.querySelectorAll('.qr-validate-form');

  forms.forEach(form => {
    const toggleBtn = form.querySelector('.toggle-qr-btn');
    const qrInput = form.querySelector('input[name="scannedQR"]');

    // Create the "Scan QR" hint text
    const scanText = document.createElement('span');
    scanText.textContent = 'Scan QR Code to proceed';
    scanText.style.marginRight = '10px';
    scanText.style.fontStyle = 'italic';
    scanText.style.color = '#555';
    scanText.style.display = 'none';
    toggleBtn.parentNode.insertBefore(scanText, toggleBtn);

    // Save original button label
    if (!toggleBtn.dataset.originalLabel) {
      toggleBtn.dataset.originalLabel = toggleBtn.textContent;
    }

    // Hide QR input initially
    Object.assign(qrInput.style, {
      position: 'fixed',
      left: '-9999px',
      top: 'auto',
      width: '1px',
      height: '1px',
      opacity: '0',
      pointerEvents: 'none',
    });

    let scanning = false;

    // 🔹 Toggle button click
    toggleBtn.addEventListener('click', () => {
      if (!scanning) {
        // Open this form for scanning

        // 🔹 Trigger relay only when opening
        const projectId = form.dataset.projectid?.trim() || '';
        if (projectId.startsWith('HAG')) {
          Relay(1);
        } else if (projectId.startsWith('CAL')) {
          Relay(2);
        } else {
          console.log(`No relay assigned for project ${projectId}`);
        }

        // Close all other open QR forms
        forms.forEach(otherForm => {
          if (otherForm !== form) {
            const otherBtn = otherForm.querySelector('.toggle-qr-btn');
            const otherInput = otherForm.querySelector('input[name="scannedQR"]');
            const otherScanText = otherBtn.previousSibling;
            otherBtn.textContent = otherBtn.dataset.originalLabel || 'Toggle';
            otherScanText.style.display = 'none';
            Object.assign(otherInput.style, {
              position: 'fixed',
              left: '-9999px',
              opacity: '0',
              pointerEvents: 'none',
            });
            otherInput.value = '';
          }
        });

        toggleBtn.textContent = 'Cancel';
        scanText.style.display = 'inline';
        Object.assign(qrInput.style, {
          position: 'fixed',
          left: '-9999px',
          opacity: '0',
          pointerEvents: 'auto',
        });
        qrInput.value = '';
        qrInput.focus();
        scanning = true;
      } else {
        // Close current form (Cancel) — do NOT trigger relay
        toggleBtn.textContent = toggleBtn.dataset.originalLabel;
        scanText.style.display = 'none';
        Object.assign(qrInput.style, {
          position: 'fixed',
          left: '-9999px',
          opacity: '0',
          pointerEvents: 'none',
        });
        qrInput.value = '';
        scanning = false;
      }
    });

    // 🔹 QR Enter key handler (still submits the form)
    qrInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (!qrInput.value.trim()) return;

        form.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });
  });
}
