function uploadFile(input, docName) {
  const file = input.files[0];
  if (!file) return;

  const row = input.closest("tr");
  const fileNameSpan = row.querySelector(".file-name");
  const statusCell = row.querySelector(".upload-status");

  fileNameSpan.textContent = file.name;
  statusCell.textContent = "Uploaded";
  statusCell.style.color = "green";
}

function generateQR() {
  alert("Generate QR Code (demo)");
}

function submitForm() {
  const form = document.getElementById("projectForm");
  const formData = new FormData(form);

  fetch("upload_project.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    alert(data);
  })
  .catch(err => console.error(err));
}

function populateDocumentTable() {
  const table = document.getElementById("documentTable");
  table.innerHTML = `
      <tr>
        <th>Document Name</th>
        <th>Physical</th>
        <th>Digital</th>
        <th>QR</th>
      </tr>
    `;
  const docs = [
    "Original Plan", "Lot Title", "Ref Plan/Lot Data", "TD", "Transmittal",
    "Field Notes", "Deed of Sale/Transfer", "Tax Declaration"
  ];

  docs.forEach(doc => {
    const id = doc.toLowerCase().replace(/[^a-z0-9]/g, "_");
    const row = document.createElement("tr");
    row.innerHTML = `
        <td>${doc}</td>
        <td><input type="checkbox" name="physical_${id}" /></td>
        <td>
          <div class="upload-form">
            <label class="upload-icon">📎
              <input type="file" name="digital_${id}" class="hidden-file" onchange="uploadFile(this, '${doc}')">
            </label>
            <span class="file-name"></span>
          </div>
        </td>
        <td class="upload-status">⬆</td>
      `;
    table.appendChild(row);
  });
}

// ==========================
// SEQUENTIAL INPUT HANDLING
// ==========================
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

// ==========================
// MUNICIPALITY + BARANGAY
// ==========================
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
      "Abulalas","Carillo","Iba","Iba-Ibayo","Mercado","Palapat","Pugad",
      "San Agustin","San Isidro","San Juan","San Miguel","San Nicolas",
      "San Pablo","San Pedro","San Roque","San Sebastian","San Pascual",
      "Santa Cruz","Santa Elena","Santa Monica","Santa Niño","Santa Rosario",
      "Santo Niño","Santo Rosario","Tampok","Tibaguin"
    ];
  } else if (municipality === "Calumpit") {
    barangays = [
      "Balite","Balungao","Bugyon","Calizon","Calumpang","Corazon","Frances",
      "Gatbuca","Gugu","Iba Este","Iba O’este","Longos","Malolos",
      "Meyto","Palimbang","Panducot","Poblacion","Pungo","San Jose",
      "Santo Niño","Sapang Bayan","Suklayin","Sunga","Tinejero"
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

function previewFile(input) {
  const file = input.files[0];
  const previewCell = input.closest("tr").querySelector(".file-preview");
  previewCell.innerHTML = "";

  if (file) {
    const fileType = file.type;
    const fileName = file.name.toLowerCase();

    // Para sa image preview
    if (fileType.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = document.createElement("img");
        img.src = e.target.result;
        previewCell.appendChild(img);
      };
      reader.readAsDataURL(file);
    } 
    // Para sa PDF
    else if (fileType === "application/pdf" || fileName.endsWith(".pdf")) {
      const icon = document.createElement("img");
      icon.src = "https://cdn-icons-png.flaticon.com/512/337/337946.png";
      icon.alt = "PDF Icon";
      icon.classList.add("file-icon");
      previewCell.appendChild(icon);
      const label = document.createElement("div");
      label.textContent = file.name;
      label.classList.add("file-label");
      previewCell.appendChild(label);
    }
    // Para sa Word
    else if (
      fileType === "application/msword" || 
      fileType === "application/vnd.openxmlformats-officedocument.wordprocessingml.document" || 
      fileName.endsWith(".doc") || fileName.endsWith(".docx")
    ) {
      const icon = document.createElement("img");
      icon.src = "https://cdn-icons-png.flaticon.com/512/281/281760.png";
      icon.alt = "Word Icon";
      icon.classList.add("file-icon");
      previewCell.appendChild(icon);
      const label = document.createElement("div");
      label.textContent = file.name;
      label.classList.add("file-label");
      previewCell.appendChild(label);
    }
    // Para sa Excel
    else if (
      fileType === "application/vnd.ms-excel" || 
      fileType === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" || 
      fileName.endsWith(".xls") || fileName.endsWith(".xlsx")
    ) {
      const icon = document.createElement("img");
      icon.src = "https://cdn-icons-png.flaticon.com/512/732/732220.png";
      icon.alt = "Excel Icon";
      icon.classList.add("file-icon");
      previewCell.appendChild(icon);
      const label = document.createElement("div");
      label.textContent = file.name;
      label.classList.add("file-label");
      previewCell.appendChild(label);
    }
    // Default icon para sa ibang files
    else {
      const icon = document.createElement("img");
      icon.src = "https://cdn-icons-png.flaticon.com/512/565/565547.png";
      icon.alt = "File Icon";
      icon.classList.add("file-icon");
      previewCell.appendChild(icon);
      const label = document.createElement("div");
      label.textContent = file.name;
      label.classList.add("file-label");
      previewCell.appendChild(label);
    }
  }
}

function enforceApprovalRule() {
  const requestType = document.getElementById("requestType")?.value;
  const approvalRadios = document.querySelectorAll("input[name='forApproval']");

  if (requestType === "Sketch Plan") {
    approvalRadios.forEach(r => {
      if (r.checked) r.checked = false; // auto unselect
      r.disabled = true; // disable approval radios
    });
  } else {
    approvalRadios.forEach(r => r.disabled = false); // enable back if not sketch plan
  }
}

window.addEventListener("DOMContentLoaded", function () {
  populateDocumentTable();
  sequentialInputs();

  document.getElementById("province").addEventListener("change", loadMunicipalities);
  document.getElementById("municipality").addEventListener("change", loadBarangays);

    // 🔗 Bind rule enforcement
  const requestType = document.getElementById("requestType");
  if (requestType) {
    requestType.addEventListener("change", enforceApprovalRule);
  }
});
