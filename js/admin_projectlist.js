function sortTable(col, btn) {
  const table = document.getElementById("projectTable");
  const tbody = table.querySelector("tbody");

  let rows = Array.from(tbody.rows); // only body rows
  rows.sort((a, b) => {
    const x = a.cells[col].innerText.trim().toLowerCase();
    const y = b.cells[col].innerText.trim().toLowerCase();
    return x.localeCompare(y);
  });

  rows.forEach(row => tbody.appendChild(row)); // re-append sorted rows

  document.querySelectorAll(".sort-btn").forEach(b => b.classList.remove("active-sort"));
  btn.classList.add("active-sort");
}

let sortDirection = {};   // store ascending/descending state per column
let lastSortedCol = null; // track last sorted column

function sortTable(col, btn) {
  const table = document.getElementById("projectTable");
  const tbody = table.querySelector("tbody");
  let rows = Array.from(tbody.rows);

  // If we clicked a new column, reset to ascending
  if (lastSortedCol !== col) {
    sortDirection[col] = true; // ascending
  } else {
    sortDirection[col] = !sortDirection[col]; // toggle
  }
  lastSortedCol = col;

  // Sort rows
  rows.sort((a, b) => {
    const x = a.cells[col].innerText.trim().toLowerCase();
    const y = b.cells[col].innerText.trim().toLowerCase();
    return sortDirection[col] ? x.localeCompare(y) : y.localeCompare(x);
  });

  // Append sorted rows back
  rows.forEach(row => tbody.appendChild(row));

  // Reset all sort button styles and arrows
  document.querySelectorAll(".sort-btn").forEach(b => {
    b.classList.remove("active-sort");
    b.innerHTML = b.innerHTML.replace(/<i.*<\/i>/, ''); // remove arrow
  });

  // Add active style and arrow to clicked button
  btn.classList.add("active-sort");
  const arrow = sortDirection[col]
    ? '<i class="fa fa-long-arrow-up" style="margin-left:5px;"></i>'
    : '<i class="fa fa-long-arrow-down" style="margin-left:5px;"></i>';
  btn.innerHTML = btn.textContent + arrow;
}
const defaultBtn = document.querySelector(".sort-btn.active-sort");
if (defaultBtn) {
  sortTable(0, defaultBtn);
}

function redirectToUpdate(button) {
  const projectId = button.getAttribute('data-projectid');
  loadAdminPage('admin_update.php?projectId=' + encodeURIComponent(projectId));
}

let previewModalInitialized = false;

function initPreviewModal() {
  previewModalInitialized = true;

  // Get documentsByProject from hidden div
  const documentsDataElement = document.getElementById('documentsData');
  let documentsByProject = {};
  if (documentsDataElement) {
    try {
      documentsByProject = JSON.parse(documentsDataElement.getAttribute('data-documents'));
      console.log("documentsByProject:", documentsByProject); // <-- This line logs the JSON
    } catch (e) {
      console.error("Failed to parse documents data:", e);
    }
  }

  const body = document.body;
  const modal = document.getElementById('previewModal');
  const closeBtn = document.getElementById('closeModal');

  if (!modal || !closeBtn) {
    console.warn("Preview modal or close button not found.");
    return;
  }

  // Predefined document types to always show in the modal
  const allDocuments = [
    "Original Plan",
    "Lot Title",
    "Deed of Sale",
    "Tax Declaration",
    "Building Permit",
    "Authorization Letter",
    "Others"
  ];

  // Map physical document status to CSS classes
  const statusClassMap = {
    'STORED': 'stored',
    'RELEASED': 'released',
    'NULL': 'released',
    null: 'released',
    '': 'released'
  };

  body.addEventListener('click', function (e) {
    const previewBtn = e.target.closest('.preview-btn');
    if (previewBtn) {
      const row = previewBtn.closest('tr');
      if (!row) return;

      const projectId = row.cells[0].innerText.trim();
      const clientName = row.getAttribute('data-clientfullname') || row.cells[1].innerText.trim();
      const municipality = row.cells[2].innerText.trim();
      const physicalLocation = row.cells[3].innerText.trim();
      const surveyType = row.cells[4].innerText.trim();
      const lotNo = row.getAttribute('data-lotno') || projectId;
      const address = row.getAttribute('data-address') || 'not available';
      const agent = row.getAttribute('data-agent') || 'not available';
      const surveyPeriod = row.getAttribute('data-surveyperiod') || 'not available';


      modal.querySelector('.preview-projectname').textContent = projectId;
      modal.querySelector('.project-details').innerHTML = `
        <p><strong>Lot No.:</strong> ${lotNo}</p>
        <p><strong>Address:</strong> ${address}</p>
        <p><strong>Survey Type:</strong> ${surveyType}</p>
        <p><strong>Client:</strong> ${clientName}</p>
        <p><strong>Physical Location:</strong> ${physicalLocation || '<span class="status released">UNAVAILABLE</span>'}</p>
        <p><strong>Agent:</strong> ${agent}</p>
        <p><strong>Survey Period:</strong> ${surveyPeriod}</p>
      `;


      // ✅ Replace QR image source
      const qrImage = modal.querySelector('.qr-section img');
      if (qrImage) {
        qrImage.src = `uploads/${projectId}/${projectId}-qr.png`;
        qrImage.alt = `${projectId} QR Code`;
      }
      // Document table tbody
      const docTableBody = modal.querySelector('.document-table tbody');
      docTableBody.innerHTML = ''; // Clear existing rows

      // Get docs for project (array of docs)
      const docs = documentsByProject[projectId] || [];

      // Build map from normalized doc name (without project prefix) to doc object
      const docsMap = {};
      docs.forEach(doc => {
        if (doc.DocumentName) {
          // Normalize: remove project prefix and dashes/underscores, lowercase
          const normalizedName = doc.DocumentName
            .replace(new RegExp(`^${projectId}[-_]`, 'i'), '') // remove project ID + dash or underscore prefix
            .replace(/[-_]/g, ' ') // replace dashes or underscores with space
            .trim()
            .toLowerCase();
          docsMap[normalizedName] = doc;
        }
      });

      // Helper to get physical status display text and CSS class
      function getPhysicalStatus(doc) {
        const rawStatus = doc.DocumentStatus ? doc.DocumentStatus.toUpperCase() : 'NULL';
        if (rawStatus === 'NULL' || rawStatus === '') {
          return { text: '', css: '' };
        }
        return { text: rawStatus, css: statusClassMap[rawStatus] || 'released' };
      }

      // Helper to get digital status display text and CSS class
      function getDigitalStatus(doc) {
        if (doc.DigitalLocation && doc.DigitalLocation.trim() !== '') {
          return { text: 'AVAILABLE', css: 'stored' };
        }
        return { text: '', css: '' };
      }

      // Loop over allDocuments to populate modal rows
      allDocuments.forEach(docName => {
        const lookupName = docName.toLowerCase();
        const doc = docsMap[lookupName];

        let physical = { text: '', css: '' };
        let digital = { text: '', css: '' };

        if (doc) {
          physical = getPhysicalStatus(doc);
          digital = getDigitalStatus(doc);
        }

        docTableBody.insertAdjacentHTML('beforeend', `
          <tr>
            <td>${docName}</td>
            <td class="status ${physical.css}">${physical.text}</td>
            <td class="status ${digital.css}">${digital.text}</td>
          </tr>
        `);
      });

      modal.style.display = 'flex';
      return;
    }

    if (e.target === closeBtn || e.target === modal) {
      modal.style.display = 'none';
      return;
    }
  });
}
