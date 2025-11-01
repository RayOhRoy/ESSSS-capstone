let isEditing = false; // Start in view mode
let originalValues = {}; // Store original field values

// Call this after the HTML is injected (e.g. via fetch in SPA)
function initializeEditForm() {
  storeOriginalValues();
  disableFormUI();
  updateApprovalSectionVisibility();

  const saveBtn = document.getElementById('update-save-btn');
  if (saveBtn) saveBtn.addEventListener('click', saveChanges);
}

function storeOriginalValues() {
  const form = document.getElementById('update_projectForm');
  originalValues = {};
  const radioGroups = {};

  // --- Handle all inputs (excluding hidden) ---
  const inputs = form.querySelectorAll('input:not([type="hidden"])');
  inputs.forEach(input => {
    if (!input.name) return; // must have a name

    if (input.type === 'radio') {
      if (input.checked) radioGroups[input.name] = input.value;
    } else if (input.type === 'checkbox') {
      // ✅ Save true/false using checkbox name
      originalValues[input.name] = input.checked;
    } else {
      originalValues[input.name] = input.value;
    }
  });

  // --- Add grouped radio values ---
  for (const name in radioGroups) {
    originalValues[name] = radioGroups[name];
  }

  // --- Handle selects ---
  const selects = form.querySelectorAll('select');
  selects.forEach(select => {
    if (select.name) originalValues[select.name] = select.value;
  });

  // --- Handle document-table checkboxes (by name) ---
  const table = document.querySelector('.document-table');
  if (table) {
    table.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      if (cb.name) {
        // ✅ Use name directly (like physical_barangay_clearance)
        originalValues[cb.name] = cb.checked;
      }
    });
  }

  // Optional: debug check
  // console.log("✅ Stored initial values:", originalValues);
}



// 🚫 Disable all UI initially
function disableFormUI() {
  const form = document.getElementById('update_projectForm');
  const inputs = form.querySelectorAll('input:not([type="hidden"])');
  const selects = form.querySelectorAll('select');
  const attachIcons = form.querySelectorAll('.attach-icon');
  const saveBtn = document.getElementById('update-save-btn');

  inputs.forEach(input => {
    if (input.type !== 'file') input.readOnly = true;
    if (['radio', 'checkbox'].includes(input.type)) input.disabled = true;
  });

  selects.forEach(select => select.disabled = true);
  attachIcons.forEach(icon => icon.style.display = 'none');
  if (saveBtn) saveBtn.style.display = 'none';
}

// 🟢 Toggle Edit <-> Cancel
function toggleEditSave() {
  const form = document.getElementById('update_projectForm');
  const inputs = form.querySelectorAll('input:not([type="hidden"])');
  const selects = form.querySelectorAll('select');
  const attachIcons = form.querySelectorAll('.attach-icon');
  const saveBtn = document.getElementById('update-save-btn');
  const editBtn = document.getElementById('update-edit-btn');
  const requestTypeField = document.getElementById('requestType');

  isEditing = !isEditing;

  if (isEditing) {
    // ✅ Enable edit mode
    toggleDocumentTableEditable(true);

    inputs.forEach(input => {
      if (input.type !== 'file') input.readOnly = false;
      if (['radio', 'checkbox'].includes(input.type)) input.disabled = false;
    });

    selects.forEach(select => select.disabled = false);
    attachIcons.forEach(icon => icon.style.display = 'inline-block');
    if (saveBtn) saveBtn.style.display = 'inline-block';

    if (editBtn) {
      editBtn.textContent = 'Cancel';
      editBtn.classList.remove('btn-red');
      editBtn.classList.add('btn-gray');
    }

    repopulateMunicipalitySelect();
    repopulateBarangaySelect();
    updateApprovalSectionVisibility();

    if (requestTypeField) {
      requestTypeField.addEventListener('change', updateApprovalSectionVisibility);
    }

    const startInput = document.getElementById('surveyStartDate');
    if (startInput) startInput.addEventListener('change', updateSurveyEndDateMin);
    updateSurveyEndDateMin();
  } else {
    // 🚫 Cancel: restore all original values
    toggleDocumentTableEditable(false);

    const startInput = document.getElementById('surveyStartDate');
    if (startInput) startInput.removeEventListener('change', updateSurveyEndDateMin);

    // Restore all inputs
    inputs.forEach(input => {
      if (!input.name) return;

      if (input.type === 'radio') {
        input.checked = (originalValues[input.name] === input.value);
      } else if (input.type === 'checkbox') {
        input.checked = !!originalValues[input.name];
      } else if (input.type !== 'file') {
        input.value = originalValues[input.name] || '';
      }

      if (input.type !== 'file') input.readOnly = true;
      if (['radio', 'checkbox'].includes(input.type)) input.disabled = true;
    });

    selects.forEach(select => {
      if (originalValues[select.name] !== undefined)
        select.value = originalValues[select.name];
      select.disabled = true;
    });

    // 🧹 Clear file uploads
    form.querySelectorAll('input[type="file"]').forEach(file => file.value = '');

    // 🧹 Restore document table UI
    const table = document.querySelector('.document-table');
    if (table) {
      table.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        if (cb.name && originalValues[cb.name] !== undefined) {
          cb.checked = !!originalValues[cb.name];
        }
        cb.disabled = true;
      });

      table.querySelectorAll('.digital-cell').forEach(cell => {
        const fileSpan = cell.querySelector('.existing-file');
        const noFile = cell.querySelector('.no-file');
        if (fileSpan && fileSpan.textContent.trim() === '') {
          if (noFile) {
            noFile.style.display = 'inline';
            fileSpan.style.display = 'none';
          }
        } else {
          if (fileSpan) fileSpan.style.display = 'inline';
          if (noFile) noFile.style.display = 'none';
        }
      });
    }

    attachIcons.forEach(icon => icon.style.display = 'none');
    if (saveBtn) saveBtn.style.display = 'none';

    if (editBtn) {
      editBtn.textContent = 'Edit';
      editBtn.classList.remove('btn-gray');
      editBtn.classList.add('btn-red');
    }

    updateApprovalSectionVisibility();

    if (requestTypeField) {
      requestTypeField.removeEventListener('change', updateApprovalSectionVisibility);
    }
  }
}

// 🧩 Document table editable toggle
function toggleDocumentTableEditable(isEditable) {
  const table = document.querySelector('.document-table');
  if (!table) return;

  table.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = !isEditable);
  table.querySelectorAll('.attach-icon').forEach(icon => icon.style.display = isEditable ? 'inline-block' : 'none');
  table.querySelectorAll('.hidden-file').forEach(f => f.disabled = !isEditable);

  table.querySelectorAll('.digital-cell').forEach(cell => {
    const fileSpan = cell.querySelector('.existing-file');
    const noFile = cell.querySelector('.no-file');
    if (isEditable) {
      if (fileSpan && fileSpan.textContent.trim() === '') {
        noFile.style.display = 'inline';
        fileSpan.style.display = 'none';
      } else {
        noFile.style.display = 'none';
        fileSpan.style.display = 'inline';
      }
    } else {
      if (fileSpan && fileSpan.textContent.trim() === '') {
        noFile.style.display = 'inline';
        fileSpan.style.display = 'none';
      } else {
        fileSpan.style.display = 'inline';
        noFile.style.display = 'none';
      }
    }
  });
}

// 📅 Date validation
function updateSurveyEndDateMin() {
  const start = document.getElementById('surveyStartDate');
  const end = document.getElementById('surveyEndDate');
  if (!start || !end) return;

  if (start.value) {
    end.min = start.value;
    if (end.value && end.value < start.value) end.value = start.value;
  } else end.min = '';
}

// 👁️ Approval visibility
function updateApprovalSectionVisibility() {
  const requestType = document.getElementById('requestType')?.value;
  const toBeApprovedBy = document.getElementById('toBeApprovedBy');
  if (!toBeApprovedBy) return;

  if (requestType === 'Sketch Plan') {
    toBeApprovedBy.style.display = 'none';
    const psdRadio = toBeApprovedBy.querySelector('input[name="approval"][value="PSD"]');
    if (psdRadio) psdRadio.checked = true;
  } else {
    toBeApprovedBy.style.display = 'block';
    const approvalRadios = toBeApprovedBy.querySelectorAll('input[name="approval"]');
    const originalApproval = originalValues['approval'] || null;
    approvalRadios.forEach(r => r.checked = (r.value === originalApproval));
  }
}

// 💾 Save changes
async function saveChanges() {
  const form = document.getElementById('update_projectForm');
  if (!form) return alert('Form not found!');

  const requiredFields = [
    'lotNumber', 'clientFirstName', 'clientLastName',
    'province', 'municipality', 'barangay',
    'surveyType', 'projectStatus', 'surveyStartDate', 'requestType'
  ];

  for (const f of requiredFields) {
    const field = form.querySelector(`[name="${f}"]`);
    if (!field || !field.value.trim()) {
      alert('Please fill in all required fields.');
      field?.focus();
      return;
    }
  }

  const formData = new FormData(form);
  if (!formData.has('projectId')) {
    const id = document.getElementById('projectId')?.value;
    if (!id) return alert('Project ID missing!');
    formData.append('projectId', id);
  }

  try {
    const res = await fetch('model/update_project.php', { method: 'POST', body: formData });
    if (!res.ok) return alert(`Network error: ${res.statusText}`);
    const data = await res.json();

    if (data.status === 'success') {
      alert('Changes saved successfully!');
      storeCurrentValues();
      toggleEditSave();
    } else {
      alert('Error: ' + (data.message || 'Unknown error'));
    }
  } catch (err) {
    alert('Error: ' + err.message);
  }
}

// 🧠 Store new values after saving
function storeCurrentValues() {
  const form = document.getElementById('update_projectForm');
  const inputs = form.querySelectorAll('input:not([type="hidden"])');
  const selects = form.querySelectorAll('select');
  const radioGroups = {};

  inputs.forEach(input => {
    if (!input.name) return;
    if (input.type === 'radio') {
      if (input.checked) radioGroups[input.name] = input.value;
    } else if (input.type === 'checkbox') {
      originalValues[input.name] = input.checked;
    } else {
      originalValues[input.name] = input.value;
    }
  });

  for (const name in radioGroups) originalValues[name] = radioGroups[name];
  selects.forEach(select => { if (select.name) originalValues[select.name] = select.value; });

  const table = document.querySelector('.document-table');
  if (table) {
    table.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      originalValues[cb.name] = cb.checked;
    });
  }
}


// 📍 Municipality dropdown
function repopulateMunicipalitySelect() {
  const province = document.getElementById("province").value;
  const municipalitySelect = document.getElementById("municipality");
  const currentValue = municipalitySelect.value;
  municipalitySelect.innerHTML = "";

  let municipalities = [];

  if (province === "Bulacan") {
    municipalities = [
      "Angat", "Balagtas", "Baliuag", "Bocaue", "Bulacan", "Bustos", "Calumpit",
      "Doña Remedios Trinidad", "Guiguinto", "Hagonoy", "Malolos City", "Marilao",
      "Meycauayan City", "Norzagaray", "Obando", "Pandi", "Paombong", "Plaridel",
      "Pulilan", "San Ildefonso", "San Jose del Monte City", "San Miguel",
      "San Rafael", "Santa Maria"
    ];
  }

  municipalities.forEach(m => {
    const option = document.createElement("option");
    option.value = m;
    option.textContent = m;
    municipalitySelect.appendChild(option);
  });

  municipalitySelect.value = currentValue;
  municipalitySelect.disabled = false;
}

// 📍 Barangay dropdown
function repopulateBarangaySelect() {
  const municipality = document.getElementById("municipality").value;
  const barangaySelect = document.getElementById("barangay");
  const currentValue = barangaySelect.value;
  barangaySelect.innerHTML = "";

  let barangays = [];

  // 🏙️ Full barangay lists for first 4
  if (municipality === "Hagonoy") {
    barangays = [
      "Abulalas", "Carillo", "Iba", "Iba‑Ibayo", "Mercado", "Palapat", "Pugad",
      "Sagrada Familia", "San Agustin", "San Isidro", "San Jose", "San Juan",
      "San Miguel", "San Nicolas", "San Pablo", "San Pascual", "San Pedro",
      "San Roque", "San Sebastian", "Santa Cross", "Santa Elena", "Santa Monica",
      "Santo Niño", "Santo Rosario", "Tampok", "Tibaguin"
    ];
  } else if (municipality === "Calumpit") {
    barangays = [
      "Balite", "Balungao", "Buguion", "Bulusan", "Calizon", "Calumpang", "Caniogan", "Corazon", "Frances",
      "Gatbuca", "Gugo", "Iba Este", "Iba O’este", "Longos", "Meysulao", "Meyto",
      "Palimbang", "Panducot", "Pio Cruzcosa", "Poblacion", "Pungo", "San Jose", "San Marcos",
      "San Miguel", "Santa Lucia", "Santo Niño", "Sapang Bayan", "Sergio Bayan", "Sucol"
    ];
  } else if (municipality === "Malolos City") {
    barangays = [
      "Anilao", "Atlag", "Bagna", "Balayong", "Bangkong Malapad", "Barihan",
      "Bulihan", "Caingin", "Canalate", "Caniogan", "Catmon", "Cofradia",
      "Dakila", "Guinhawa", "Ligas", "Longos", "Mojon", "Pamarawan",
      "Santiago", "Santo Cristo", "Sumapang Bata", "Tikay"
    ];
  } else if (municipality === "Baliuag") {
    barangays = [
      "Bagong Nayon", "Barangca", "Calantipay", "Catulinan", "Concepcion",
      "Hinukay", "Makinabang", "Matangtubig", "Pagala", "Paitan", "Pinagbarilan",
      "Sabang", "San Jose", "Santa Barbara", "Subic", "Tangos", "Tiaong", "Tarcan"
    ];
  }

  // 🏙️ 5 sample barangays for other municipalities
  else if (municipality === "Angat") {
    barangays = ["Banaban", "Donacion", "Laog", "Marungko", "Niugan"];
  } else if (municipality === "Balagtas") {
    barangays = ["Borol 1st", "Borol 2nd", "Dalig", "Longos", "Panginay"];
  } else if (municipality === "Bocaue") {
    barangays = ["Antipona", "Biñang 1st", "Bundukan", "Sulucan", "Wakas"];
  } else if (municipality === "Bulacan") {
    barangays = ["Bagumbayan", "Bambang", "Matungao", "Perez", "Pitpitan"];
  } else if (municipality === "Bustos") {
    barangays = ["Bonga Mayor", "Bonga Menor", "Camachilihan", "Cambaog", "Poblacion"];
  } else if (municipality === "Doña Remedios Trinidad") {
    barangays = ["Bayabas", "Camachin", "Kalawakan", "Pulong Sampalok", "Sapang Bulak"];
  } else if (municipality === "Guiguinto") {
    barangays = ["Cutcot", "Duhat", "Poblacion", "Pulong Gubat", "Tuktukan"];
  } else if (municipality === "Marilao") {
    barangays = ["Abangan Norte", "Abangan Sur", "Ibayo", "Loma de Gato", "Nagbalon"];
  } else if (municipality === "Meycauayan City") {
    barangays = ["Bahay Pare", "Bancal", "Caingin", "Camalig", "Lawa"];
  } else if (municipality === "Norzagaray") {
    barangays = ["Bigte", "Bitungol", "Matictic", "Partida", "San Mateo"];
  } else if (municipality === "Obando") {
    barangays = ["Catanghalan", "Hulo", "Lawa", "Paliwas", "Panghulo"];
  } else if (municipality === "Pandi") {
    barangays = ["Bagong Barrio", "Baka-Bakahan", "Cacarong Bata", "Cupang", "Malibong Bata"];
  } else if (municipality === "Paombong") {
    barangays = ["Binakod", "Kapitangan", "Malumot", "Masukol", "San Roque"];
  } else if (municipality === "Plaridel") {
    barangays = ["Agnaya", "Bagong Silang", "Banga 1st", "Banga 2nd", "Poblacion"];
  } else if (municipality === "Pulilan") {
    barangays = ["Cutcot", "Lumbac", "Paltao", "Penabatan", "Sta. Peregrina"];
  } else if (municipality === "San Ildefonso") {
    barangays = ["Alagao", "Anyatam", "Bagong Barrio", "Basuit", "Bubulong Munti"];
  } else if (municipality === "San Jose del Monte City") {
    barangays = ["Bagong Buhay", "Citrus", "Dulong Bayan", "Fatima", "Graceville"];
  } else if (municipality === "San Miguel") {
    barangays = ["Bantog", "Camias", "Ilog-Bulo", "Poblacion", "Tartaro"];
  } else if (municipality === "San Rafael") {
    barangays = ["Caingin", "Cruz na Daan", "Lico", "Maronquillo", "Pantubig"];
  } else if (municipality === "Santa Maria") {
    barangays = ["Bagbaguin", "Balasing", "Buenavista", "Catmon", "Caypombo"];
  }

  barangays.forEach(b => {
    const option = document.createElement("option");
    option.value = b;
    option.textContent = b;
    barangaySelect.appendChild(option);
  });

  barangaySelect.value = currentValue;
  barangaySelect.disabled = false;
}

function loadMunicipalities() {
  const province = document.getElementById("province").value;
  const municipalitySelect = document.getElementById("municipality");
  const barangaySelect = document.getElementById("barangay");

  municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';
  barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
  barangaySelect.disabled = true;

  let municipalities = [];

  if (province === "Bulacan") {
    municipalities = [
      "Angat", "Balagtas", "Baliuag", "Bocaue", "Bulacan", "Bustos", "Calumpit",
      "Doña Remedios Trinidad", "Guiguinto", "Hagonoy", "Malolos City", "Marilao",
      "Meycauayan City", "Norzagaray", "Obando", "Pandi", "Paombong", "Plaridel",
      "Pulilan", "San Ildefonso", "San Jose del Monte City", "San Miguel",
      "San Rafael", "Santa Maria"
    ];
  }

  if (municipalities.length > 0) {
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
      "Abulalas", "Carillo", "Iba", "Iba‑Ibayo", "Mercado", "Palapat", "Pugad",
      "Sagrada Familia", "San Agustin", "San Isidro", "San Jose", "San Juan",
      "San Miguel", "San Nicolas", "San Pablo", "San Pascual", "San Pedro",
      "San Roque", "San Sebastian", "Santa Cross", "Santa Elena", "Santa Monica",
      "Santo Niño", "Santo Rosario", "Tampok", "Tibaguin"
    ];
  } else if (municipality === "Calumpit") {
    barangays = [
      "Balite", "Balungao", "Buguion", "Bulusan", "Calizon", "Calumpang", "Caniogan", "Corazon", "Frances",
      "Gatbuca", "Gugo", "Iba Este", "Iba O’este", "Longos", "Meysulao", "Meyto",
      "Palimbang", "Panducot", "Pio Cruzcosa", "Poblacion", "Pungo", "San Jose", "San Marcos",
      "San Miguel", "Santa Lucia", "Santo Niño", "Sapang Bayan", "Sergio Bayan", "Sucol"
    ];
  } else if (municipality === "Malolos City") {
    barangays = [
      "Anilao", "Atlag", "Bagna", "Balayong", "Bangkong Malapad", "Barihan",
      "Bulihan", "Caingin", "Canalate", "Caniogan", "Catmon", "Cofradia",
      "Dakila", "Guinhawa", "Ligas", "Longos", "Mojon", "Pamarawan",
      "Santiago", "Santo Cristo", "Sumapang Bata", "Tikay"
    ];
  } else if (municipality === "Baliuag") {
    barangays = [
      "Bagong Nayon", "Barangca", "Calantipay", "Catulinan", "Concepcion",
      "Hinukay", "Makinabang", "Matangtubig", "Pagala", "Paitan", "Pinagbarilan",
      "Sabang", "San Jose", "Santa Barbara", "Subic", "Tangos", "Tiaong", "Tarcan"
    ];
  }

  // 🏙️ Others (5 sample barangays each)
  else if (municipality === "Angat") {
    barangays = ["Banaban", "Donacion", "Laog", "Marungko", "Niugan"];
  } else if (municipality === "Balagtas") {
    barangays = ["Borol 1st", "Borol 2nd", "Dalig", "Longos", "Panginay"];
  } else if (municipality === "Bocaue") {
    barangays = ["Antipona", "Biñang 1st", "Bundukan", "Sulucan", "Wakas"];
  } else if (municipality === "Bulacan") {
    barangays = ["Bagumbayan", "Bambang", "Matungao", "Perez", "Pitpitan"];
  } else if (municipality === "Bustos") {
    barangays = ["Bonga Mayor", "Bonga Menor", "Camachilihan", "Cambaog", "Poblacion"];
  } else if (municipality === "Doña Remedios Trinidad") {
    barangays = ["Bayabas", "Camachin", "Kalawakan", "Pulong Sampalok", "Sapang Bulak"];
  } else if (municipality === "Guiguinto") {
    barangays = ["Cutcot", "Duhat", "Poblacion", "Pulong Gubat", "Tuktukan"];
  } else if (municipality === "Marilao") {
    barangays = ["Abangan Norte", "Abangan Sur", "Ibayo", "Loma de Gato", "Nagbalon"];
  } else if (municipality === "Meycauayan City") {
    barangays = ["Bahay Pare", "Bancal", "Caingin", "Camalig", "Lawa"];
  } else if (municipality === "Norzagaray") {
    barangays = ["Bigte", "Bitungol", "Matictic", "Partida", "San Mateo"];
  } else if (municipality === "Obando") {
    barangays = ["Catanghalan", "Hulo", "Lawa", "Paliwas", "Panghulo"];
  } else if (municipality === "Pandi") {
    barangays = ["Bagong Barrio", "Baka-Bakahan", "Cacarong Bata", "Cupang", "Malibong Bata"];
  } else if (municipality === "Paombong") {
    barangays = ["Binakod", "Kapitangan", "Malumot", "Masukol", "San Roque"];
  } else if (municipality === "Plaridel") {
    barangays = ["Agnaya", "Bagong Silang", "Banga 1st", "Banga 2nd", "Poblacion"];
  } else if (municipality === "Pulilan") {
    barangays = ["Cutcot", "Lumbac", "Paltao", "Penabatan", "Sta. Peregrina"];
  } else if (municipality === "San Ildefonso") {
    barangays = ["Alagao", "Anyatam", "Bagong Barrio", "Basuit", "Bubulong Munti"];
  } else if (municipality === "San Jose del Monte City") {
    barangays = ["Bagong Buhay", "Citrus", "Dulong Bayan", "Fatima", "Graceville"];
  } else if (municipality === "San Miguel") {
    barangays = ["Bantog", "Camias", "Ilog-Bulo", "Poblacion", "Tartaro"];
  } else if (municipality === "San Rafael") {
    barangays = ["Caingin", "Cruz na Daan", "Lico", "Maronquillo", "Pantubig"];
  } else if (municipality === "Santa Maria") {
    barangays = ["Bagbaguin", "Balasing", "Buenavista", "Catmon", "Caypombo"];

  }

  barangays.forEach(b => {
    const option = document.createElement("option");
    option.value = b;
    option.textContent = b;
    barangaySelect.appendChild(option);
  });

  barangaySelect.disabled = barangays.length === 0;
}

function printProjectQRCodes(projectId, projectQR = null) {
  if (!projectId) {
    console.error("Project ID is required.");
    return;
  }

  // Fetch documents (including Project QR) for the project
  fetch(`model/get_project_docs.php?projectId=${encodeURIComponent(projectId)}`)
    .then(res => res.json())
    .then(docs => {
      const qrImages = [];

      // Include Project QR at the top with label "Project QR"
      if (projectQR) {
        qrImages.push({ src: projectQR, label: "Project QR" });
      }

      // Add QR codes for documents
      docs.forEach(doc => {
        if (doc.DocumentQR) {
          qrImages.push({
            src: doc.DocumentQR,
            label: doc.DocumentType // label for individual documents
          });
        }
      });

      if (qrImages.length === 0) {
        console.warn("No QR codes to print.");
        return;
      }

      // Build QR grid HTML
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
              .print-wrapper { display: flex; flex-direction: column; align-items: center; }
              .project-id-header { font-size: 14px; font-weight: bold; text-align: center; margin: 5mm 0 3mm 0; }
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
              .qr-block img { width: 44mm; height: 36mm; }
              .label { margin-top: 1.5mm; font-size: 11px; }
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

      // Create iframe and print
      const iframe = document.createElement("iframe");
      iframe.style = "position: fixed; right: 0; bottom: 0; width: 0; height: 0; border: 0;";
      document.body.appendChild(iframe);

      const doc = iframe.contentWindow.document;
      doc.open();
      doc.write(printHTML);
      doc.close();
    })
    .catch(err => {
      console.error("Failed to fetch document QR codes.", err);
    });
}