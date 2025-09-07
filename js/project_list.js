function initPreviewModal() {
  previewModalInitialized = true;

  const documentsDataElement = document.getElementById('documentsData');
  let documentsByProject = {};
  if (documentsDataElement) {
    try {
      documentsByProject = JSON.parse(documentsDataElement.getAttribute('data-documents'));
      console.log("documentsByProject:", documentsByProject);
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

  body.addEventListener('click', function (e) {
    const previewBtn = e.target.closest('.preview-btn');
    if (previewBtn) {
      const row = previewBtn.closest('tr');
      if (!row) return;

      const projectId = row.cells[0].innerText.trim();
      const clientName = row.getAttribute('data-clientfullname') || row.cells[1].innerText.trim();
      const municipality = row.cells[2].innerText.trim();
      const surveytype = row.cells[3].innerText.trim();
      const lotNo = row.getAttribute('data-lotno') || projectId;
      const address = row.getAttribute('data-address') || 'not available';
      const agent = row.getAttribute('data-agent') || 'not available';
      const surveyPeriod = row.getAttribute('data-surveyperiod') || 'not available';

      // Use requestType and approvalType from the correct columns (index 10 and 11)
      const requestType = row.cells[10] ? row.cells[10].innerText.trim() : '';
      const approvalType = row.cells[11] ? row.cells[11].innerText.trim() : '';

      modal.querySelector('.preview-projectname').textContent = projectId;
      modal.querySelector('.project-details').innerHTML = `
        <p><strong>Lot No.:</strong> ${lotNo}</p>
        <p><strong>Address:</strong> ${address}</p>
        <p><strong>Survey Type:</strong> ${surveytype}</p>
        <p><strong>Client:</strong> ${clientName}</p>
        <p><strong>Agent:</strong> ${agent}</p>
        <p><strong>Survey Period:</strong> ${surveyPeriod}</p>
      `;

      const qrImage = modal.querySelector('.qr-section img');
      if (qrImage) {
        qrImage.src = `uploads/${projectId}/${projectId}-QR.png`;
        qrImage.alt = `${projectId} QR Code`;
      }

      const docTableBody = modal.querySelector('.document-table tbody');
      docTableBody.innerHTML = ''; // Clear existing rows

      // Get docs for project
      const docs = documentsByProject[projectId] || [];

      // Normalize doc names map
      const docsMap = {};
      docs.forEach(doc => {
        if (doc.DocumentName) {
          const normalizedName = doc.DocumentName
            .replace(new RegExp(`^${projectId}[-_]`, 'i'), '')
            .replace(/[-_]/g, ' ')
            .trim()
            .toLowerCase();
          docsMap[normalizedName] = doc;
        }
      });

      // Helper for physical status
      const statusClassMap = {
        'STORED': 'stored',
        'RELEASED': 'released',
        'NULL': 'released',
        null: 'released',
        '': 'released'
      };

      function getPhysicalStatus(doc) {
        const rawStatus = doc.DocumentStatus ? doc.DocumentStatus.toUpperCase() : 'NULL';
        if (rawStatus === 'NULL' || rawStatus === '') {
          return { text: '', css: '' };
        }
        return { text: rawStatus, css: statusClassMap[rawStatus] || 'released' };
      }

      // Helper for digital status
      function getDigitalStatus(doc) {
        if (doc.DigitalLocation && doc.DigitalLocation.trim() !== '') {
          return { text: 'AVAILABLE', css: 'stored' };
        }
        return { text: '', css: '' };
      }

      // Determine docs to render based on requestType and approvalType
      let docsToRender = [];

      if (requestType === "For Approval" && approvalType === "PSD") {
        docsToRender = [
          "Original Plan",
          "Certified Title",
          "Ref Plan",
          "Lot Data",
          "TD",
          "Transmital",
          "Fieldnotes",
          "Tax Declaration",
          "Blueprint",
          "Others"
        ];
      } else if (requestType === "For Approval" && approvalType === "CSD") {
        docsToRender = [
          "Original Plan",
          "3 BP",
          "Ref Plan",
          "Lot Data",
          "CM",
          "TD",
          "Transmital",
          "Fieldnotes",
          "Tax Declaration",
          "Survey Authority",
          "Blueprint",
          "Others"
        ];
      } else if (requestType === "For Approval" && approvalType === "LRA") {
        docsToRender = [
          "Original Plan",
          "Certified Title",
          "Ref Plan",
          "Lot Data",
          "TD",
          "Fieldnotes",
          "Blueprint",
          "Others"
        ];
      } else if (requestType === "Sketch Plan") {
        docsToRender = [
          "Original Plan",
          "Others"
        ];
      } else {
        docsToRender = [
          "Original Plan",
          "Lot Title",
          "Deed of Sale",
          "Tax Declaration",
          "Building Permit",
          "Authorization Letter",
          "Others"
        ];
      }

      // Render documents into table
      docsToRender.forEach(docName => {
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
