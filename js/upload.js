function handleFileUpload(input) {
  const row = input.closest("tr");
  const fileListDiv = row.querySelector(".file-list");
  const attachIcon = row.querySelector(".attach-icon");

  if (!row.filesArray) row.filesArray = [];
  const newFiles = Array.from(input.files);
  row.filesArray.push(...newFiles);

  input.value = "";
  renderFileList(row);
}

function renderFileList(row) {
  const fileListDiv = row.querySelector(".file-list");
  fileListDiv.innerHTML = "";

  row.filesArray.forEach((file, index) => {
    const item = document.createElement("div");
    item.classList.add("file-preview");

    item.innerHTML = `
      <span class="file-label">${file.name}</span>
      <span class="remove-file">✖</span>
    `;

    item.querySelector(".remove-file").onclick = () => {
      row.filesArray.splice(index, 1);
      renderFileList(row);
    };

    fileListDiv.appendChild(item);
  });

  // Keep attach icon visible
  row.querySelector(".attach-icon").style.display = "inline-block";
}

function uploadFile(input, docName) {
  const row = input.closest("tr");
  if (!row.filesArray) row.filesArray = [];

  const newFiles = Array.from(input.files);
  row.filesArray.push(...newFiles);

  input.value = "";
  renderFileList(row);
}

function submitForm() {
  const form = document.getElementById("projectForm");
  const uploadBtn = document.getElementById("uploadBtn");

  // Disable upload button
  if (uploadBtn) {
    uploadBtn.disabled = true;
    uploadBtn.textContent = "Uploading...";
  }

  const formData = new FormData(form);
  const rows = document.querySelectorAll("#documentTable tbody tr");

  rows.forEach(row => {
    const docName = row.cells[0].innerText.trim();
    const docKey = docName.toLowerCase().replace(/[^a-z0-9]/g, "_");

    const checkbox = row.querySelector("input[type='checkbox']");
    const hasPhysical = checkbox && checkbox.checked;
    const hasDigitalFiles = row.filesArray && row.filesArray.length > 0;

    if (hasPhysical) {
      formData.append(`status_${docKey}`, "Stored");
    }

    if (hasDigitalFiles) {
      row.filesArray.forEach(file => {
        formData.append(`digital_${docKey}[]`, file);
      });
    } else if (hasPhysical) {
      formData.append(`digital_${docKey}[]`, new Blob(), "");
    }

    if (checkbox && checkbox.disabled && checkbox.checked) {
      formData.append(`physical_${docKey}`, checkbox.value);
    }

    const radios = row.querySelectorAll("input[type='radio']");
    radios.forEach(r => {
      if (r.disabled && r.checked) {
        formData.append(r.name, r.value);
      }
    });
  });

  fetch("model/upload_project.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        const projectId = data.projectID || "UnknownProjectID";
        const municipality = data.municipality || "UnknownMunicipality";

        // Initial Modal
        const modal = document.createElement("div");
        modal.style = `
          position: fixed; top: 0; left: 0; width: 100%; height: 100%;
          background: rgba(0,0,0,0.5); display: flex; align-items: center;
          justify-content: center; z-index: 9999;
        `;
        modal.innerHTML = `
          <div style="background:white; padding:50px; border-radius:10px; text-align:center; max-width:320px;">
            <h3>Project uploaded successfully.</h3>
            <div style="margin-top:20px; display: flex; justify-content: space-between; gap: 20px;">
              <button id="printQRBtn" style="padding:8px 15px;">Print QR</button>
              <button id="okBtn" style="
                padding:8px 15px;
                background-color: #ccc;
                color: #666;
                border: none;
                cursor: not-allowed;
              " disabled>Next</button>
            </div>
          </div>
        `;
        document.body.appendChild(modal);

        const okBtn = document.getElementById("okBtn");
        const printBtn = document.getElementById("printQRBtn");

        printBtn.addEventListener("click", () => {
          const qrImages = [];

          const projectQR = document.getElementById("projectQRImg");
          if (projectQR) {
            qrImages.push({ src: projectQR.src, label: "Project QR" });
          }

          document.querySelectorAll(".doc-qr-img").forEach(img => {
            qrImages.push({ src: img.src, label: img.dataset.docname });
          });

          if (qrImages.length === 0) {
            alert("No QR codes found to print.");
            return;
          }

          let qrGridHTML = "";
          qrImages.forEach(qr => {
            qrGridHTML += `
              <div class="qr-block">
                <img src="${qr.src}" alt="QR Code">
                <div class="label">${qr.label}</div>
              </div>
            `;
          });

          const printHTML = `
            <html>
              <head>
                <title>Print QR Codes</title>
                <style>
                  @page { size: A4 portrait; margin: 5mm; }
                  body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                  .print-wrapper {
                    display: flex; flex-direction: column; align-items: center;
                  }
                  .project-id-header {
                    font-size: 14px; font-weight: bold; text-align: center;
                    margin: 5mm 0 3mm 0;
                  }
                  .qr-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 48mm);
                    grid-auto-rows: 55mm;
                    gap: 2mm;
                    padding: 5mm;
                  }
                  .qr-block {
                    width: 48mm; height: 55mm; border: 1px solid #000;
                    display: flex; flex-direction: column;
                    align-items: center; justify-content: center;
                    box-sizing: border-box; padding: 2mm;
                  }
                  .qr-block img {
                    width: 44mm; height: 36mm;
                  }
                  .label {
                    margin-top: 1.5mm; font-size: 11px;
                  }
                </style>
              </head>
              <body>
                <div class="print-wrapper">
                  <div class="project-id-header">${projectId}</div>
                  <div class="qr-grid">${qrGridHTML}</div>
                </div>
                <script>
                  window.onload = () => {
                    window.print();
                    window.onafterprint = () => window.close();
                  }
                </script>
              </body>
            </html>
          `;

          const iframe = document.createElement("iframe");
          iframe.style = "position: fixed; right: 0; bottom: 0; width: 0; height: 0; border: 0;";
          document.body.appendChild(iframe);

          const doc = iframe.contentWindow.document;
          doc.open();
          doc.write(printHTML);
          doc.close();

          // Enable "Next" button and style it
          okBtn.disabled = false;
          okBtn.style.backgroundColor = printBtn.style.backgroundColor || "";
          okBtn.style.color = printBtn.style.color || "";
          okBtn.style.cursor = "pointer";
        });

        okBtn.addEventListener("click", () => {
          document.body.removeChild(modal);

          // Build Digital Storage Path
          let digitalStorage = `${municipality}-${projectId}`;
          if (projectId.startsWith("CAL")) {
            digitalStorage = `Calumpit/${projectId}`;
          } else if (projectId.startsWith("HAG")) {
            digitalStorage = `Hagonoy/${projectId}`;
          }

          // Clean up Physical Storage ID (remove from 3rd dash onwards)
          let physicalStorage = projectId;
          const dashParts = projectId.split("-");
          if (dashParts.length >= 3) {
            physicalStorage = dashParts.slice(0, 3).join("-");
          }
          // Create Storage Modal
          const storageModal = document.createElement("div");
          storageModal.style = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; align-items: center;
            justify-content: center; z-index: 10000;
          `;
          storageModal.innerHTML = `
          <div style="background:white; padding:40px; border-radius:10px; max-width:450px; text-align:center;">
            <h2 style="color:#7B0302; font-size:24px; margin-bottom:20px;">Project Storage</h2>
            
            <div style="text-align: left; font-size:16px;">
              <p><strong>Digital Storage:</strong> ${digitalStorage}</p>
              <p><strong>Physical Storage:</strong> ${physicalStorage}</p>
            </div>

            <div style="background:#d9f2d9; border:2px solid #3c763d; color:#2d572d; 
                        padding:12px; margin-top:20px; border-radius:6px; display:flex; align-items:center; gap:10px;">
              <i class="fa fa-exclamation-circle" style="font-size:18px; color:#2d572d;"></i>
              <span style="font-size:14px;">Physical storage now open. Please place all physical documents in the assigned storage.</span>
            </div>

            <div style="margin-top:25px;">
              <button id="closeStorageModalBtn" 
                      style="padding:8px 15px; background:#7B0302; color:white; border:none; border-radius:5px; cursor:pointer;">
                Close
              </button>
            </div>
          </div>
          `;
          document.body.appendChild(storageModal);

          document.getElementById("closeStorageModalBtn").addEventListener("click", () => {
            document.body.removeChild(storageModal);

            // Reset form and UI
            form.reset();
            document.querySelector("#documentTable tbody").innerHTML = "";

            qrGenerated = false;
            const generateQRBtn = document.getElementById("generateQRBtn");
            if (generateQRBtn) {
              generateQRBtn.textContent = "Generate QR Code";
              generateQRBtn.classList.remove("btn-cancel");
              generateQRBtn.classList.add("btn-red");
            }

            // Highlight correct sidebar menu
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            const menuItem = document.querySelector('.menu-item[data-page="documents.php"]');
            if (menuItem) menuItem.classList.add('active');

            // ✅ Redirect to project.php with projectId
            loadAdminPage('project.php?projectId=' + encodeURIComponent(projectId));
          });
        });
      }

      // Re-enable upload button
      setTimeout(() => {
        if (uploadBtn) {
          uploadBtn.disabled = false;
          uploadBtn.textContent = "Upload";
        }
      }, 5000);
    })
    .catch(err => {
      console.error(err);
      if (uploadBtn) {
        uploadBtn.disabled = false;
        uploadBtn.textContent = "Upload";
      }
    });
}

function generateQR() {
  const projectForm = document.getElementById("projectForm");

  // Step 1: Validate required fields
  const requiredFields = [
    { id: "lotNumber", name: "Lot Number" },
    { id: "clientName", name: "Client First Name" },
    { id: "clientLastName", name: "Client Last Name" },
    { id: "province", name: "Province" },
    { id: "municipality", name: "Municipality" },
    { id: "barangay", name: "Barangay" },
    { id: "surveyType", name: "Survey Type" },
    { id: "startDate", name: "Survey Start Date" },
  ];

  const missingFields = [];

  requiredFields.forEach(f => {
    const el = document.getElementById(f.id);
    if (!el || el.value.trim() === "") missingFields.push(f.name);
  });

  const surveyType = document.getElementById("surveyType")?.value;
  const requestTypeEl = document.getElementById("requestType");

  // Conditionally require Request Type
  if (surveyType !== "Sketch Plan") {
    if (!requestTypeEl || requestTypeEl.value.trim() === "") {
      missingFields.push("Request Type");
    }
  }

  // If Sketch Plan, require approval radio
  if (surveyType === "Sketch Plan") {
    const approvalRadios = document.querySelectorAll("input[name='approval']");
    if (![...approvalRadios].some(r => r.checked)) {
      missingFields.push("Approval (select one)");
    }
  }

  // Require at least one document selected
  const rows = document.querySelectorAll("#documentTable tbody tr");
  let anyDocSelected = false;
  rows.forEach(row => {
    const checkbox = row.querySelector("input[type='checkbox']");
    if ((row.filesArray && row.filesArray.length > 0) || (checkbox && checkbox.checked)) {
      anyDocSelected = true;
    }
  });

  if (!anyDocSelected) {
    missingFields.push("At least one document selected or file uploaded");
  }

  if (missingFields.length > 0) {
    alert("Please complete the following before generating QR Code:\n- " + missingFields.join("\n- "));
    return false;
  }

  // Step 2: Validate date logic
  const startDate = new Date(document.getElementById("startDate").value);
  const endDateEl = document.getElementById("endDate");
  if (endDateEl && endDateEl.value) {
    const endDate = new Date(endDateEl.value);
    if (startDate > endDate) {
      alert("Start date cannot be later than end date.");
      return false;
    }
  }

  // Step 3: Prepare FormData
  const formData = new FormData(projectForm);
  const physicalDocs = [];
  const digitalDocs = [];

  rows.forEach(row => {
    const docName = row.cells[0].innerText.trim();
    const docKey = docName.toLowerCase().replace(/[^a-z0-9]/g, "_");

    const checkbox = row.querySelector("input[type='checkbox']");
    const hasPhysical = checkbox && checkbox.checked;
    const hasDigital = row.filesArray && row.filesArray.length > 0;

    if (hasPhysical) {
      physicalDocs.push(docName);
      formData.append(`status_${docKey}`, "Stored"); // auto-store status
    }

    if (hasDigital) {
      digitalDocs.push(docName);
      row.filesArray.forEach(file => {
        formData.append(`digital_${docKey}[]`, file);
      });
    } else if (hasPhysical) {
      // If physical-only, send placeholder file
      formData.append(`digital_${docKey}[]`, new Blob(), "");
    }
  });

  // Add doc name arrays
  physicalDocs.forEach(doc => formData.append("physical_docs[]", doc));
  digitalDocs.forEach(doc => formData.append("digital_docs[]", doc));

  // Step 4: Send to server
  return fetch("model/generate_qr.php", { method: "POST", body: formData })
    .then(res => res.text())
    .then(text => {
      try {
        const data = JSON.parse(text);

        // Inject main project QR
        const projectQRBox = document.querySelector(".qr-box");
        if (projectQRBox && data.projectQR) {
          projectQRBox.innerHTML = `<img id="projectQRImg" src="${data.projectQR}" alt="Project QR">`;
        }

        // Inject document QRs
        rows.forEach(row => {
          const docName = row.cells[0].innerText.trim();
          const qrCell = row.querySelector(".qr-code");
          const checkbox = row.querySelector("input[type='checkbox']");
          const hasDigital = row.filesArray && row.filesArray.length > 0;
          const hasPhysical = checkbox && checkbox.checked;

          const qrPath = data.documentQRs?.[docName];

          if ((hasDigital || hasPhysical) && qrPath) {
            qrCell.innerHTML = `
              <span class="view-qr-text"
                    style="cursor:pointer;color:#7B0302;text-decoration:underline;"
                    onclick="showQRPopup('${qrPath}')">View</span>
              <img src="${qrPath}"
                   alt="QR for ${docName}"
                   style="display:none;"
                   class="doc-qr-img"
                   data-docname="${docName}">
            `;
          } else {
            qrCell.innerHTML = "";
          }
        });

        // Enable Upload button
        const uploadBtn = document.getElementById("uploadBtn");
        if (uploadBtn) {
          uploadBtn.disabled = false;
          uploadBtn.classList.remove("btn-grey");
          uploadBtn.classList.add("btn-red");
          uploadBtn.style.cursor = "pointer";
        }

        return true;
      } catch (err) {
        console.error("❌ Invalid JSON from server:", text);
        alert("Error: Server did not return valid JSON. Check console.");
        return false;
      }
    })
    .catch(err => {
      console.error("❌ Fetch error:", err);
      alert("Network error while generating QR. Check console.");
      return false;
    });
}


function showQRPopup(path) {
  const modal = document.getElementById("qrModal");
  const modalImg = document.getElementById("qrModalImg");
  modal.style.display = "flex";
  modalImg.src = path;
  modal.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };
}

let qrGenerated = false;

function toggleGenerateQR() {
  if (!qrGenerated) {
    const result = generateQR();
    if (result instanceof Promise) {
      result.then(success => { if (success) afterGenerate(); });
    } else { if (result) afterGenerate(); }
  } else {
    if (confirm("Are you sure you want to cancel and reset the form? All progress will be lost.")) {
      cancelGenerate();
    }
  }
}

function afterGenerate() {
  const form = document.getElementById("projectForm");
  const tableRows = document.querySelectorAll("#documentTable tbody tr");
  const generateBtn = document.getElementById("generateQRBtn");

  // Lock text inputs & textareas
  form.querySelectorAll("input[type='text'], textarea, input[type='date']").forEach(el => {
    el.setAttribute("readonly", true);
  });

  // Lock selects
  form.querySelectorAll("select").forEach(sel => {
    sel.setAttribute("data-locked", "true");
    sel.addEventListener("mousedown", preventSelectChange);
  });

  // Lock table rows
  tableRows.forEach(row => {
    row.querySelectorAll("input").forEach(el => {
      if (el.type === "checkbox" || el.type === "file") {
        el.disabled = true;
      } else if (el.type !== "radio") {  // leave radios enabled
        el.setAttribute("readonly", true);
      }
    });
  });

  // Lock approval radios visually but keep them enabled
  const approvalRadios = document.querySelectorAll("#toBeApprovedBy input[type='radio']");
  approvalRadios.forEach(r => {
    r.style.pointerEvents = "none";  // prevent clicking
    r.style.opacity = 0.6;           // faded to indicate locked
  });

  // Change button to Cancel
  generateBtn.textContent = "Cancel";
  generateBtn.classList.remove("btn-red");
  generateBtn.classList.add("btn-cancel");

  qrGenerated = true;
}


function cancelGenerate() {
  const form = document.getElementById("projectForm");
  const tableRows = document.querySelectorAll("#documentTable tbody tr");
  const generateBtn = document.getElementById("generateQRBtn");
  const uploadBtn = document.getElementById("uploadBtn");

  // Reset form
  form.reset();

  // Unlock text inputs & textareas
  form.querySelectorAll("input[type='text'], textarea, input[type='date']").forEach(el => {
    el.removeAttribute("readonly");
  });

  // Unlock selects
  form.querySelectorAll("select").forEach(sel => {
    sel.removeAttribute("data-locked");
    sel.removeEventListener("mousedown", preventSelectChange);
  });

  // Unlock table rows
  tableRows.forEach(row => {
    row.querySelectorAll("input").forEach(el => {
      if (el.type === "checkbox" || el.type === "file") {
        el.disabled = false;
      } else if (el.type !== "radio") {  // leave radios enabled
        el.removeAttribute("readonly");
      }
    });

    // Clear uploaded files
    const fileListDiv = row.querySelector(".file-list");
    if (fileListDiv) fileListDiv.innerHTML = "";
    row.filesArray = [];
  });

  // Restore approval radios' interactivity and style
  const approvalRadios = document.querySelectorAll("#toBeApprovedBy input[type='radio']");
  approvalRadios.forEach(r => {
    r.style.pointerEvents = "";
    r.style.opacity = "";
    r.disabled = false; // ensure enabled if it was disabled before
  });

  // Clear project QR
  const projectQRBox = document.querySelector(".qr-box");
  if (projectQRBox) projectQRBox.innerHTML = "";

  // Reset buttons
  generateBtn.disabled = false;
  generateBtn.textContent = "Generate QR Code";
  generateBtn.classList.remove("btn-cancel");
  generateBtn.classList.add("btn-red");
  generateBtn.style.cursor = "pointer";

  uploadBtn.disabled = true;
  uploadBtn.classList.remove("btn-red");
  uploadBtn.classList.add("btn-grey");
  uploadBtn.style.cursor = "pointer";

  qrGenerated = false;
}
// ✅ Prevent locked selects from being changed
function preventSelectChange(e) {
  if (e.target.getAttribute("data-locked") === "true") {
    e.preventDefault();
  }
}

// ✅ Make form inputs sequentially enabled
function sequentialInputs() {
  const form = document.getElementById("projectForm");

  const inputs = Array.from(form.querySelectorAll("input, select")).filter(input => {
    return (
      !input.closest(".radio-group") &&
      !input.closest(".approval-group") &&
      input.id !== "surveyType"
    );
  });

  inputs.forEach((input, index) => {
    if (index !== 0) input.disabled = true;

    input.addEventListener("input", () => {
      if (input.value.trim() && inputs[index + 1]) {
        inputs[index + 1].disabled = false;
      } else {
        // Disable all next inputs
        for (let i = index + 1; i < inputs.length; i++) {
          inputs[i].disabled = true;
        }
      }
    });
  });
}

// ✅ Handle logic for approval section based on requestType
function clearApproval() {
  const requestType = document.getElementById('requestType');
  const toBeApprovedBy = document.getElementById('toBeApprovedBy');
  const psdRadio = document.querySelector('input[name="approval"][value="PSD"]');

  requestType.addEventListener('change', () => {
    if (requestType.value !== 'For Approval') {
      // Hide and clear approval radios
      const radios = toBeApprovedBy.querySelectorAll('input[type="radio"]');
      radios.forEach(r => r.checked = false);
      toBeApprovedBy.style.display = 'none';
    } else {
      // Show and default to PSD
      toBeApprovedBy.style.display = 'block';
      if (psdRadio) {
        psdRadio.checked = true;

        // Trigger change in case other logic listens to it
        const event = new Event('change', { bubbles: true });
        psdRadio.dispatchEvent(event);
      }
    }
  });
}

// ✅ Load municipalities based on selected province
function loadMunicipalities() {
  const province = document.getElementById("province").value;
  const municipalitySelect = document.getElementById("municipality");
  const barangaySelect = document.getElementById("barangay");

  municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';
  barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
  barangaySelect.disabled = true;

  if (province === "Bulacan") {
    const municipalities = ["Hagonoy", "Calumpit"];
    municipalities.forEach(m => {
      const option = document.createElement("option");
      option.value = m;
      option.textContent = m;
      municipalitySelect.appendChild(option);
    });
    municipalitySelect.disabled = false;
  } else {
    municipalitySelect.disabled = true;
    barangaySelect.disabled = true;
  }
}

// ✅ Load barangays based on selected municipality
function loadBarangays() {
  const municipality = document.getElementById("municipality").value;
  const barangaySelect = document.getElementById("barangay");

  barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
  let barangays = [];

  if (municipality === "Hagonoy") {
    barangays = [
      "Abulalas", "Carillo", "Iba", "Iba-Ibayo", "Mercado", "Palapat", "Pugad",
      "San Agustin", "San Isidro", "San Juan", "San Miguel", "San Nicolas",
      "San Pablo", "San Pedro", "San Roque", "San Sebastian", "San Pascual",
      "Santa Cruz", "Santa Elena", "Santa Monica", "Santa Niño", "Santa Rosario",
      "Santo Niño", "Santo Rosario", "Tampok", "Tibaguin"
    ];
  } else if (municipality === "Calumpit") {
    barangays = [
      "Balite", "Balungao", "Bugyon", "Calizon", "Calumpang", "Corazon", "Frances",
      "Gatbuca", "Gugu", "Iba Este", "Iba O’este", "Longos", "Malolos",
      "Meyto", "Palimbang", "Panducot", "Poblacion", "Pungo", "San Jose",
      "Santo Niño", "Sapang Bayan", "Suklayin", "Sunga", "Tinejero"
    ];
  }

  barangays.forEach(b => {
    const option = document.createElement("option");
    option.value = b;
    option.textContent = b;
    barangaySelect.appendChild(option);
  });

  barangaySelect.disabled = false;
}

function updateDocumentTableBasedOnSelection() {
  const requestType = document.getElementById("requestType")?.value;
  const approvalType = document.querySelector("input[name='approval']:checked")?.value;

  const tbody = document.querySelector("#documentTable tbody");
  tbody.innerHTML = ""; // Clear existing rows

  let docsToRender = [];

  if (requestType === "For Approval" && approvalType === "PSD") {
    docsToRender = [
      "Original Plan",
      "Certified Title",
      "Reference Plan",
      "Lot Data",
      "Technical Description",
      "Transmital",
      "Fieldnotes",
      "Tax Declaration",
      "Blueprint",
      "CAD File",
      "Others"
    ];
  } else if (requestType === "For Approval" && approvalType === "CSD") {
    docsToRender = [
      "Original Plan",
      "Reference Plan",
      "Lot Data",
      "Cadastral Map",
      "Technical Description",
      "Transmital",
      "Fieldnotes",
      "Tax Declaration",
      "Survey Authority",
      "Blueprint",
      "CAD File",
      "Others"
    ];
  } else if (requestType === "For Approval" && approvalType === "LRA") {
    docsToRender = [
      "Original Plan",
      "Certified Title",
      "Reference Plan",
      "Lot Data",
      "Technical Description",
      "Fieldnotes",
      "Blueprint",
      "CAD File",
      "Others"
    ];
  } else if (requestType === "Sketch Plan") {
    docsToRender = [
      "Original Plan",
      "Title",
      "Reference Plan",
      "Lot Data",
      "Tax Declaration",
      "Blueprint",
      "CAD File",
      "Others"
    ];
  } else {
    docsToRender = [
      "Failed to load, refresh page.",
    ];
  }

  docsToRender.forEach(doc => {
    const id = doc.toLowerCase().replace(/[^a-z0-9]/g, "_");
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${doc}</td>
      <td>
        <input type="checkbox" name="physical_${id}" />
      </td>
      <td>
        <div class="upload-form">
          <div class="file-list"></div>
          <label class="attach-icon" style="cursor:pointer;">📎
            <input type="file" name="digital_${id}" class="hidden-file" multiple
                   accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.dwg"
                   onchange="uploadFile(this, '${doc}')">
          </label>
        </div>
      </td>
      <td class="qr-code"></td>
    `;
    tbody.appendChild(row);
  });
}

