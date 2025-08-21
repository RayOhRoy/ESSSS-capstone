
<div class="topbar">
  <h1>Upload Project</h1>
  <div class="topbar-content">
    <div class="search-container">
      <input type="text" placeholder="Search Project" />
    </div>
    <div class="icons">
      <span class="notif">🔔</span>
      <span class="user-icon">👤 User</span>
    </div>
  </div>
</div>
<hr class="top-line" />

<div class="content">
  <form id="projectForm" enctype="multipart/form-data">
    <div class="form-wrapper">
      <div class="form-grid">
        <div class="column">
          <div class="form-row"><label>Lot No.:</label><input name="lot_no" type="text" required /></div>
          <div class="form-row"><label>Agent:</label><input name="agent" type="text" required /></div>
          <div class="form-row"><label>Client Name:</label><input name="client_name" type="text" required /></div>

          <div class="form-row">
            <label>Province:</label>
            <select name="province" id="province" onchange="loadMunicipalities()">
              <option value="">Select Province</option>
              <option value="Bulacan">Bulacan</option>
            </select>
          </div>

          <div class="form-row">
            <label>Municipality:</label>
            <select name="municipality" id="municipality" onchange="loadBarangays()" disabled>
              <option value="">Select Municipality</option>
            </select>
          </div>

          <div class="form-row">
            <label>Barangay:</label>
            <select name="barangay" id="barangay" disabled>
              <option value="">Select Barangay</option>
            </select>
          </div>

          <div class="form-row"><label>Street/Subdivision:</label><input name="street" type="text" /></div>
        </div>

        <div class="column">
          <div class="form-row">
            <label>Survey Type:</label>
            <select name="survey_type">
              <option value="">Select Survey Type</option>
              <option value="Tax Declaration">Tax Declaration</option>
              <option value="Lot Title">Lot Title</option>
              <option value="Deed of Sale/Transfer">Deed of Sale/Transfer</option>
            </select>
          </div>
          <div class="form-row"><label>Survey Period:</label><input name="survey_period" type="date" /></div>
          <div class="form-row"><label>Last Name:</label><input name="last_name" type="text" /></div>

          <label style="margin-left: 140px;">Survey Plan Request Type:</label>
          <div class="radio-group">
            <label><input type="radio" name="requestType" value="Sketch Plan Only" /> Sketch Plan Only</label>
            <label><input type="radio" name="requestType" value="For Approval" checked /> For Approval</label>
          </div>

          <label style="margin-left: 140px;">To be approved by:</label>
          <div class="approval-group">
            <label><input type="radio" name="approval" value="LRA" checked /> LRA</label>
            <label><input type="radio" name="approval" value="BUREAU" /> BUREAU</label>
            <label><input type="radio" name="approval" value="CENRO" /> CENRO</label>
          </div>
        </div>
      </div>

      <div class="qr-preview">
        <h4>PROJECT QR CODE</h4>
        <div class="qr-box"></div>
      </div>
    </div>

    <div class="note">Select all that apply and upload digital document if applicable</div>

    <table class="document-table" id="documentTable"></table>

    <div class="footer-buttons">
      <button type="button" class="btn btn-red" onclick="generateQR()">Generate QR Code</button>
      <button type="button" class="btn btn-grey" onclick="submitForm()">Upload</button>
    </div>
  </form>
</div>

<style>
  .document-table {
    width: 60%;
    margin-right: auto;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 13px;
    background-color: #fafafa;
  }

  .document-table th,
  .document-table td {
    border: 1px solid #ccc;
    padding: 6px 10px;
    text-align: center;
  }

  .upload-icon {
    font-size: 18px;
    cursor: pointer;
    color: #007bff;
  }

  .file-name {
    font-size: 11px;
    color: #444;
    margin-top: 3px;
  }

  .hidden-file {
    display: none;
  }

  .upload-form {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .upload-status {
    font-size: 18px;
    color: #888;
  }

  .note {
    margin-top: 20px;
    font-style: italic;
    font-size: 13px;
    color: #666;
    text-align: center;
    margin-right: 1200px;
  }
</style>

<script>
  const data = {
    Bulacan: {
      "Hagonoy": [
        "Abulalas", "Carillo", "Iba", "Iba-Ibayo", "Mercado", "Palapat", "Pugad", "Sagrada Familia",
        "San Agustin", "San Isidro", "San Jose", "San Juan", "San Miguel", "San Nicolas", "San Pablo",
        "San Pascual", "San Pedro", "San Roque", "San Sebastian", "Santa Cruz", "Santa Elena",
        "Santa Monica", "Santo Niño", "Santo Rosario", "Tampok", "Tibaguin"
      ]
    }
  };

  function loadMunicipalities() {
    const province = document.getElementById("province").value;
    const muni = document.getElementById("municipality");
    const brgy = document.getElementById("barangay");

    muni.innerHTML = '<option value="">Select Municipality</option>';
    brgy.innerHTML = '<option value="">Select Barangay</option>';
    muni.disabled = true;
    brgy.disabled = true;

    if (province && data[province]) {
      muni.disabled = false;
      for (const m in data[province]) {
        const opt = document.createElement("option");
        opt.value = m;
        opt.textContent = m;
        muni.appendChild(opt);
      }
    }
  }

  function loadBarangays() {
    const province = document.getElementById("province").value;
    const muni = document.getElementById("municipality").value;
    const brgy = document.getElementById("barangay");

    brgy.innerHTML = '<option value="">Select Barangay</option>';
    brgy.disabled = true;

    if (province && muni && data[province][muni]) {
      brgy.disabled = false;
      data[province][muni].forEach(name => {
        const opt = document.createElement("option");
        opt.value = name;
        opt.textContent = name;
        brgy.appendChild(opt);
      });
    }
  }

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

    fetch("submit_form.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.text())
    .then(response => {
      alert("Success:\n" + response);
    })
    .catch(err => {
      alert("Upload failed: " + err);
    });
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
      const id = doc.replace(/\s+/g, "_");
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

  populateDocumentTable();
</script>
