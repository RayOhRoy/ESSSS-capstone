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

function populateDocumentTable() {
  const table = document.getElementById("documentTable");
  const tbody = table.querySelector("tbody") || table;

  const docs = [
    "Original Plan",
    "Lot Title",
    "Deed of Sale",
    "Tax Declaration",
    "Building Permit",
    "Authorization Letter",
    "Others"
  ];

  docs.forEach(doc => {
    const id = doc.toLowerCase().replace(/[^a-z0-9]/g, "_");
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${doc}</td>
      <td>
        <input type="checkbox" name="physical_${id}" onchange="toggleStorageStatus(this)" />
        <select class="storage-status" name="status_${id}" style="display:none;">
          <option value="Stored">Stored</option>
          <option value="Released">Released</option>
        </select>
      </td>
      <td>
        <div class="upload-form">
          <div class="file-list"></div>
          <label class="attach-icon">📎
            <input type="file" name="digital_${id}" class="hidden-file" multiple
                   accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                   onchange="uploadFile(this, '${doc}')" disabled>
          </label>
        </div>
      </td>
      <td class="upload-status">⬆</td>
    `;
    tbody.appendChild(row);
  });
}

function setupStorageStatusHandlers() {
  document.querySelectorAll("#documentTable tbody tr").forEach(row => {
    const cb = row.querySelector('td:nth-child(2) input[type="checkbox"]');
    const sel = row.querySelector('.storage-status');
    if (cb && sel) {
      toggleStorageStatus(cb);
      cb.addEventListener("change", () => toggleStorageStatus(cb));
    }
  });
}

function submitForm() {
  const form = document.getElementById("projectForm");
  const formData = new FormData(form);
  const rows = document.querySelectorAll("#documentTable tbody tr");

  // Collect status and files
  rows.forEach(row => {
    const docName = row.cells[0].innerText.trim().toLowerCase().replace(/[^a-z0-9]/g, "_");

    const statusSelect = row.querySelector(".storage-status");
    if (statusSelect) formData.append(`status_${docName}`, statusSelect.value || "");

    if (row.filesArray && row.filesArray.length > 0) {
      row.filesArray.forEach(file => {
        formData.append(`digital_${docName}[]`, file);
      });
    }
  });

  // Collect disabled checkboxes/radios
  rows.forEach(row => {
    const checkbox = row.querySelector("input[type='checkbox']");
    const docName = row.cells[0].innerText.trim().toLowerCase().replace(/[^a-z0-9]/g, "_");

    if (checkbox && checkbox.disabled && checkbox.checked) {
      formData.append(`physical_${docName}`, checkbox.value);
    }

    const radios = row.querySelectorAll("input[type='radio']");
    radios.forEach(r => {
      if (r.disabled && r.checked) formData.append(r.name, r.value);
    });
  });

  // Submit via fetch
  fetch("model/upload_project.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.text())
    .then(data => {
      alert(data);

      if (data.includes("successfully")) {
        // Reset form
        form.reset();
        document.querySelector("#documentTable tbody").innerHTML = "";

        // Reset QR button state
        qrGenerated = false;
        const generateQRBtn = document.getElementById("generateQRBtn");
        if (generateQRBtn) {
          generateQRBtn.textContent = "Generate QR Code";
          generateQRBtn.classList.remove("btn-cancel");
          generateQRBtn.classList.add("btn-red");
        }
        // Simulate click on the sidebar menu item
        const menuItem = document.querySelector('.menu-item[data-page="admin_projectlist.php"]');
        if (menuItem) {
          menuItem.click();  // <-- triggers your existing click listener
        }
      }
    })
    .catch(err => console.error(err));
}



function generateQR() {
  const projectForm = document.getElementById("projectForm");

  // Validate required fields
  const requiredFields = [
    { id: "lotNumber", name: "Lot Number" },
    { id: "clientName", name: "Client First Name" },
    { id: "clientLastName", name: "Client Last Name" },
    { id: "province", name: "Province" },
    { id: "municipality", name: "Municipality" },
    { id: "barangay", name: "Barangay" },
    { id: "surveyType", name: "Survey Type" },
    { id: "startDate", name: "Survey Start Date" },
    { id: "endDate", name: "Survey End Date" }
  ];

  const missingFields = [];
  requiredFields.forEach(f => {
    const el = document.getElementById(f.id);
    if (!el || el.value.trim() === "") missingFields.push(f.name);
  });

  const requestType = document.getElementById("requestType")?.value;
  if (requestType !== "Sketch Plan") {
    const approvalRadios = document.querySelectorAll("input[name='approval']");
    if (![...approvalRadios].some(r => r.checked)) missingFields.push("Approval (select one)");
  }

  const rows = document.querySelectorAll("#documentTable tbody tr");
  let anyDocSelected = false;
  rows.forEach(row => {
    const checkbox = row.querySelector("input[type='checkbox']");
    if ((row.filesArray && row.filesArray.length > 0) || (checkbox && checkbox.checked)) {
      anyDocSelected = true;
    }
  });
  if (!anyDocSelected) missingFields.push("At least one document selected or file uploaded");

  // ✅ If there are missing fields, show error and stop
  if (missingFields.length > 0) {
    alert("Please fill/select the following before generating QR Code:\n- " + missingFields.join("\n- "));
    return false;
  }

  // ✅ Check date validity before proceeding
  const startDate = new Date(document.getElementById("startDate").value);
  const endDate = new Date(document.getElementById("endDate").value);

  if (startDate > endDate) {
    alert("Start date cannot be later than end date.");
    return false;
  }

  // ✅ Continue with QR generation
  const formData = new FormData(projectForm);
  rows.forEach(row => {
    const docName = row.cells[0].innerText.trim().toLowerCase().replace(/[^a-z0-9]/g, "_");
    const checkbox = row.querySelector("input[type='checkbox']");
    if ((checkbox && checkbox.checked) || (row.filesArray && row.filesArray.length > 0)) {
      if (!row.filesArray || row.filesArray.length === 0) {
        formData.append(`digital_${docName}[]`, new Blob(), "");
      } else {
        row.filesArray.forEach(file => formData.append(`digital_${docName}[]`, file));
      }
    }
  });

  return fetch('model/generate_qr.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      const projectQRBox = document.querySelector(".qr-box");
      if (projectQRBox && data.projectQR) {
        projectQRBox.innerHTML = `<img src="${data.projectQR}">`;
      }
      rows.forEach(row => {
        const qrCell = row.querySelector(".qr-code");
        const checkbox = row.querySelector("input[type='checkbox']");
        const docName = row.cells[0].innerText.trim();
        const hasDigitalFile = row.filesArray && row.filesArray.length > 0;
        const hasPhysical = checkbox && checkbox.checked;
        if ((hasDigitalFile || hasPhysical) && data.documentQRs && data.documentQRs[docName]) {
          qrCell.innerHTML = `<span class="view-qr-text" 
            style="cursor:pointer;color:#7B0302;text-decoration:underline;" 
            onclick="showQRPopup('${data.documentQRs[docName]}')">View</span>`;
        } else if (qrCell) qrCell.innerHTML = '';
      });

      const uploadBtn = document.getElementById("uploadBtn");
      if (uploadBtn) {
        uploadBtn.disabled = false;
        uploadBtn.classList.remove("btn-grey");
        uploadBtn.classList.add("btn-red");
        uploadBtn.style.cursor = "pointer";
      }

      return true;
    })
    .catch(err => { console.error("Error generating QR codes:", err); return false; });
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

    // Reset storage-status dropdown
    const statusSelect = row.querySelector(".storage-status");
    if (statusSelect) {
      statusSelect.style.display = "none";
      statusSelect.value = "Stored";
    }

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


// Prevent changes to locked selects
function preventSelectChange(e) {
  if (e.target.getAttribute("data-locked") === "true") e.preventDefault();
}

function sequentialInputs() {
  const form = document.getElementById("projectForm");
  const inputs = Array.from(form.querySelectorAll("input, select")).filter(
    el => !el.closest(".radio-group") && !el.closest(".approval-group") && el.id !== "surveyType"
  );

  inputs.forEach((input, index) => {
    if (index !== 0) input.disabled = true;

    input.addEventListener("input", () => {
      if (input.value.trim() && inputs[index + 1]) {
        inputs[index + 1].disabled = false;
      } else if (inputs[index + 1]) {
        inputs[index + 1].disabled = true;
        for (let i = index + 2; i < inputs.length; i++) {
          inputs[i].disabled = true;
        }
      }
    });
  });
}

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

function toggleStorageStatus(checkbox) {
  const row = checkbox.closest("tr");
  const select = row.querySelector(".storage-status");
  const fileInput = row.querySelector("input[type='file']");
  const paperclipIcon = row.querySelector(".attach-icon i.fa-paperclip");
  const fileListDiv = row.querySelector(".file-list");

  if (checkbox.checked) {
    select.style.display = "inline-block";
    select.required = true;

    // Enable file input
    fileInput.disabled = false;
    fileInput.closest(".attach-icon").style.opacity = "1"; // Optional visual indicator

    // Show paperclip icon
    if (paperclipIcon) {
      paperclipIcon.style.display = "inline-block";
    }
  } else {
    select.style.display = "none";
    select.required = false;
    select.value = "Stored";

    // Disable file input
    fileInput.disabled = true;
    fileInput.closest(".attach-icon").style.opacity = "0.4"; // Optional dim

    // Hide paperclip icon
    if (paperclipIcon) {
      paperclipIcon.style.display = "none";
    }

    // Clear file array and UI
    if (row.filesArray) {
      row.filesArray = [];
    }
    if (fileListDiv) {
      fileListDiv.innerHTML = "";
    }
  }
}


window.addEventListener("DOMContentLoaded", function () {
  populateDocumentTable();
  sequentialInputs();

  document.getElementById("province").addEventListener("change", loadMunicipalities);
  document.getElementById("municipality").addEventListener("change", loadBarangays);

});
