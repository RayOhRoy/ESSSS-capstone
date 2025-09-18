function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  fetch(page)
    .then(res => res.text())
    .then(html => {
      contentArea.innerHTML = html;

      // Initialize user list handlers if on user list page
      if (page === 'search.php') {
        initQRSearch();
      }
      // You can add other page-specific init calls here
    })
    .catch(err => {
      console.error('Failed to load admin page:', err);
    });
}

function searchProjectByQR(scannedCode) {
  // Remove "uploads/" prefix if present
  const prefix = "uploads/";
  let projectId = scannedCode;
  if (scannedCode.startsWith(prefix)) {
    projectId = scannedCode.substring(prefix.length);
  }

  // Show loading indicator or clear previous data
  const modalBody = document.getElementById('modalBody');
  if (!modalBody) return;

  modalBody.innerHTML = '<p>Loading project info...</p>';

  fetch('model/get_project_info.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ projectId })
  })
    .then(res => res.json())
.then(data => {
  if (data.success) {
    selectedProjectIdFromQR = data.project.ProjectID;
    modalBody.innerHTML = generateProjectHTML(data.project);

    // ✅ Attach event listener AFTER the button is inserted
    const openBtn = modalBody.querySelector('.open-btn');
    if (openBtn) {
      openBtn.addEventListener('click', () => {
        if (selectedProjectIdFromQR) {
          loadAdminPage('project.php?projectId=' + encodeURIComponent(selectedProjectIdFromQR));
        } else {
          alert("No project loaded from QR.");
        }
      });
    }
  } else {
    modalBody.innerHTML = `<p style="color:red;">${data.message || "Project not found."}</p>`;
  }
})
    .catch(err => {
      console.error("Error fetching project info:", err);
      modalBody.innerHTML = `<p style="color:red;">Network error. Please try again.</p>`;
    });
}

function generateProjectHTML(project) {
  return `
    <div class="qr-section">
      <img src="${project.ProjectQR || 'picture/project_qr.png'}" alt="QR Code" class="qr-img">
      <p class="preview-projectname">${project.ProjectID || ''}</p>
    </div>

    <div class="project-details">
      <p><strong>Lot No.:</strong> ${project.LotNo || ''}</p>
      <p><strong>Address:</strong> ${project.FullAddress || ''}</p>
      <p><strong>Survey Type:</strong> ${project.SurveyType || ''}</p>
      <p><strong>Client:</strong> ${project.ClientFName || ''} ${project.ClientLName || ''}</p>
      <p><strong>Agent:</strong> ${project.Agent || ''}</p>
      <p><strong>Survey Period:</strong> ${project.SurveyStartDate || ''} - ${project.SurveyEndDate || ''}</p>
    </div>

    <div class="document-table" style="max-height: 10vw; min-width: 100%; overflow-y: auto;">
      <table style="border-collapse: collapse; width: 100%;">
        <thead>
          <tr>
            <th style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
              Document Name
            </th>
            <th style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
              Physical Documents
            </th>
            <th style="position: sticky; top: 0; background: white; z-index: 2; border-bottom: 2px solid #000; padding: 8px; border: 1px solid #ddd;">
              Digital Documents
            </th>
          </tr>
        </thead>
        <tbody>
          ${generateDocumentRows(project.documents || [])}
        </tbody>
      </table>
    </div>

    <div class="modal-buttons">
        <button class="open-btn">OPEN</button>
    </div> 
  `;
}

function openModal() {
  modal.style.display = 'flex';
  disableAllInputs();
  qrInput.focus();
}

// Generate document rows for table
function generateDocumentRows(documents) {
  return documents.map(doc => `
    <tr>
      <td>${doc.name}</td>
      <td class="status ${doc.physical_status.toLowerCase()}">${doc.physical_status}</td>
      <td class="status ${doc.digital_status.toLowerCase()}">${doc.digital_status.toUpperCase()}</td>
    </tr>
  `).join('');
}

let qrSearchInitialized = false;

function initQRSearch() {
  
  qrSearchInitialized = true;

  const qrInput = document.getElementById('qrInput');
  const qrToggleBtn = document.getElementById('qrToggleBtn');
  const qrStatusText = document.getElementById('qrStatusText');
  const modal = document.getElementById('qrsearchModal');
  const closeBtn = document.getElementById('closeqrsearchModal');
  const body = document.body;

  if (!qrInput || !qrToggleBtn || !qrStatusText || !modal || !closeBtn) {
    console.warn("Required QR search elements not found.");
    return;
  }

  qrInput.focus();
  
  let qrActive = false;

  // QR toggle button logic
  qrToggleBtn.addEventListener('click', () => {
    qrActive = !qrActive;

    if (qrActive) {
      qrInput.focus();
      qrToggleBtn.style.color = '#7B0302';
      qrStatusText.textContent = 'QR Code Search Enabled';
    } else {
      qrInput.blur();
      qrToggleBtn.style.color = 'gray';
      qrStatusText.textContent = 'QR Code Search Disabled';
    }
  });

  document.addEventListener('focusin', (e) => {
    const isQRInput = qrInput.contains(e.target);
    const isQRButton = qrToggleBtn.contains(e.target);

    if (qrActive && !isQRInput && !isQRButton) {
      qrActive = false;
      qrToggleBtn.style.color = 'gray';
      qrStatusText.textContent = 'QR Code Search Disabled';
      qrInput.blur();
    }
  });

  // Open modal and focus input
  function openModal() {
    modal.style.display = 'flex';
    qrInput.focus();

    if (modal.classList.contains('newmodal')) {
      modal.style.background = 'rgba(0,0,0,0.5)';
      const content = modal.querySelector('.new-modal-content');
      if (content) {
        content.style.backgroundColor = 'white';
        content.style.border = 'none';
      }
    }

    disableAllInputs();
  }

  // Close modal and restore inputs
  function closeModal() {
    modal.style.display = 'none';
    enableAllInputs();
    qrInput.focus();
  }

  // Disable all inputs outside the modal
  function disableAllInputs() {
    document.querySelectorAll("input, select, textarea, button").forEach(el => {
      if (!modal.contains(el)) el.disabled = true;
    });
  }

  // Enable all inputs
  function enableAllInputs() {
    document.querySelectorAll("input, select, textarea, button").forEach(el => {
      el.disabled = false;
    });
  }

  // Click outside or on close button to close modal
  body.addEventListener('click', function (e) {
    if (e.target === closeBtn || e.target === modal) {
      closeModal();
    }
  });

  // Handle Enter key to scan QR
  qrInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      const scannedCode = qrInput.value.trim();
      if (scannedCode !== '') {
        console.log("🔍 Scanned QR Code:", scannedCode);
        openModal();
        searchProjectByQR(scannedCode);
        qrInput.value = '';
      }
    }
  });

  // Re-focus qrInput every 100ms if active
  setInterval(() => {
    if (qrActive && document.activeElement !== qrInput) {
      qrInput.focus();
    }
  }, 100);
}

function initLiveProjectSearch() {
  const inputIds = [
    'projectName',
    'lotNumber',
    'clientFName',
    'clientLName',
    'province',
    'municipality',
    'barangay',
    'surveyType',
    'agent',
    'processingType',
    'projectStatus',
    'startDate',
    'endDate'
  ];
  const resultContainerId = 'liveResults';
  const endpoint = 'model/search_processing.php';

  function fetchResults() {
    // Collect all current input/select values
    const data = {
      projectName: document.getElementById('projectName')?.value || '',
      lotNumber: document.getElementById('lotNumber')?.value || '',
      clientFName: document.getElementById('clientFName')?.value || '',
      clientLName: document.getElementById('clientLName')?.value || '',
      province: document.getElementById('province')?.value || '',
      municipality: document.getElementById('municipality')?.value || '',
      barangay: document.getElementById('barangay')?.value || '',
      surveyType: document.getElementById('surveyType')?.value || '',
      agent: document.getElementById('agent')?.value || '',
      processingType: document.getElementById('processingType')?.value || '',
      projectStatus: document.getElementById('projectStatus')?.value || '',
      startDate: document.getElementById('startDate')?.value || '',
      endDate: document.getElementById('endDate')?.value || ''
    };

    const params = new URLSearchParams(data);

    fetch(`${endpoint}?${params.toString()}`)
      .then(response => response.text())
      .then(html => {
        const container = document.getElementById(resultContainerId);
        if (container) {
          container.innerHTML = html;
        }
      })
      .catch(err => console.error('Search fetch error:', err));
  }

  // Attach input/select listeners for real-time search
  inputIds.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('input', fetchResults);
    }
  });

  // Click event delegation on liveResults for selecting a project
  const resultContainer = document.getElementById(resultContainerId);
  if (resultContainer) {
    resultContainer.addEventListener('click', (e) => {
      const row = e.target.closest('.result-item');
      if (row && row.dataset.projectid) {
        handleRowDoubleClick(row);
      }
    });
  }

  // Initial fetch on load (optional)
  fetchResults();
}
