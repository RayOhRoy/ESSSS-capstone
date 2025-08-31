function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  fetch(page)
    .then(res => res.text())
    .then(html => {
      contentArea.innerHTML = html;

      // Initialize user list handlers if on user list page
      if (page === 'qr_search.php') {
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
        // Populate modal with project info from server
        modalBody.innerHTML = generateProjectHTML(data.project);
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
      <p><strong>Address:</strong> ${project.FullAddress  || ''}</p>
      <p><strong>Survey Type:</strong> ${project.SurveyType || ''}</p>
      <p><strong>Client:</strong> ${project.ClientFName || ''} ${project.ClientLName || ''}</p>
      <p><strong>Physical Location:</strong> ${project.Physicallocation || ''}</p>
      <p><strong>Agent:</strong> ${project.Agent || ''}</p>
      <p><strong>Survey Period:</strong> ${project.SurveyStartDate || ''} - ${project.SurveyEndDate || ''}</p>
    </div>

    <div class="document-table">
      <table>
        <thead>
          <tr>
            <th>Document Name</th>
            <th>Physical Documents</th>
            <th>Digital Documents</th>
          </tr>
        </thead>
        <tbody>
          ${generateDocumentRows(project.documents || [])}
        </tbody>
      </table>
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
      <td class="status ${doc.physical_status.toLowerCase()}">${doc.physical_status.toUpperCase()}</td>
      <td class="status ${doc.digital_status.toLowerCase()}">${doc.digital_status.toUpperCase()}</td>
    </tr>
  `).join('');
}

let qrSearchInitialized = false;

function initQRSearch() {
  qrSearchInitialized = true;
  const qrInput = document.getElementById('qrInput');
  if (qrInput) {
    qrInput.focus();
  }
  console.log("QR Search modal handlers initialized with event delegation");

  const body = document.body;
  const modal = document.getElementById('qrsearchModal');
  const closeBtn = document.getElementById('closeqrsearchModal');

  if (!modal || !closeBtn || !qrInput) {
    console.warn("Required modal elements not found");
    return;
  }

  // Disable/enable all inputs except modal-related when modal open/close
  function disableAllInputs() {
    document.querySelectorAll("input, select, textarea, button").forEach(el => {
      if (!modal.contains(el)) el.disabled = true;
    });
  }

  function enableAllInputs() {
    document.querySelectorAll("input, select, textarea, button").forEach(el => {
      el.disabled = false;
    });
  }

  function openModal() {
    modal.style.display = 'flex';
    disableAllInputs();
  }

  function closeModal() {
    modal.style.display = 'none';
    enableAllInputs();
  }

  // Close modal on clicking close button or outside modal content
  body.addEventListener('click', function (e) {
    if (e.target === closeBtn || e.target === modal) {
      closeModal();
    }
  });

  qrInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      const scannedCode = qrInput.value.trim();
      if (scannedCode !== '') {
        console.log("🔍 Scanned QR Code:", scannedCode);
        openModal();  // Open modal immediately on scan
        searchProjectByQR(scannedCode);
        qrInput.value = '';
      }
    }
  });
}
