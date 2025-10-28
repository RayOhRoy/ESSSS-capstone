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

// 🧠 Store current values (inputs, selects, radios, checkboxes, document table)
function storeOriginalValues() {
    const form = document.getElementById('update_projectForm');
    const inputs = form.querySelectorAll('input:not([type="hidden"])');
    const selects = form.querySelectorAll('select');

    originalValues = {};
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

    for (const name in radioGroups) {
        originalValues[name] = radioGroups[name];
    }

    selects.forEach(select => {
        if (select.name) originalValues[select.name] = select.value;
    });

    // Store document table checkbox states
    const table = document.querySelector('.document-table');
    if (table) {
        table.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            originalValues[cb.name] = cb.checked;
        });
    }
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

// 📍 Municipality dropdown
function repopulateMunicipalitySelect() {
    const province = document.getElementById("province").value;
    const municipalitySelect = document.getElementById("municipality");
    const currentValue = municipalitySelect.value;
    municipalitySelect.innerHTML = "";

    let municipalities = [];

    if (province === "Bulacan") {
        municipalities = ["Hagonoy", "Calumpit", "Malolos City", "Baliuag"];
    } else if (province === "Pampanga") {
        municipalities = ["Angeles City", "Apalit", "Guagua", "Lubao"];
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

    // Bulacan
    if (municipality === "Hagonoy") {
        barangays = [
            "Abulalas", "Carillo", "Iba", "Iba-Ibayo", "Mercado", "Palapat", "Pugad",
            "San Agustin", "San Isidro", "San Juan", "San Miguel", "San Nicolas",
            "San Pablo", "San Pedro", "San Roque", "San Sebastian", "San Pascual",
            "Santa Cruz", "Santa Elena", "Santa Monica", "Santo Niño", "Santo Rosario",
            "Tampok", "Tibaguin"
        ];
    } else if (municipality === "Calumpit") {
        barangays = [
            "Balite", "Balungao", "Bugyon", "Calizon", "Calumpang", "Corazon", "Frances",
            "Gatbuca", "Gugu", "Iba Este", "Iba O’este", "Longos", "Malolos", "Meyto",
            "Palimbang", "Panducot", "Poblacion", "Pungo", "San Jose", "Santo Niño",
            "Sapang Bayan", "Suklayin", "Sunga", "Tinejero"
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
    // Pampanga
    else if (municipality === "Angeles City") {
        barangays = [
            "Anunas", "Balibago", "Capaya", "Cuayan", "Cutcut", "Cutud",
            "Lourdes North West", "Lourdes Sur", "Malabanias", "Margot",
            "Mining", "Pampang", "Pandan", "Pulungbulu", "San Jose",
            "Santo Rosario", "Sapangbato"
        ];
    } else if (municipality === "Apalit") {
        barangays = [
            "Balucuc", "Calantipe", "Cansinala", "Capalangan", "Colgante",
            "Paligui", "Sampaloc", "San Juan", "San Vicente", "Sucad",
            "Sulipan", "Tabuyuc"
        ];
    } else if (municipality === "Guagua") {
        barangays = [
            "Ascomo", "Bancal", "Betis", "Lambac", "Magsaysay", "Maquiapo",
            "Natividad", "Poblacion", "San Agustin", "San Antonio", "San Jose",
            "San Juan", "San Marcos", "San Matias", "Santa Filomena", "Santo Niño"
        ];
    } else if (municipality === "Lubao") {
        barangays = [
            "Bancal", "Balantacan", "Calangain", "Del Carmen", "Don Ignacio Dimson",
            "Prado Siongco", "Remedios", "San Antonio", "San Matias", "Santa Barbara",
            "Santa Catalina", "Santa Cruz", "Santo Domingo", "Santo Niño", "Santo Tomas"
        ];
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

// ✅ Load municipalities based on selected province
function loadMunicipalities() {
  const province = document.getElementById("province").value;
  const municipalitySelect = document.getElementById("municipality");
  const barangaySelect = document.getElementById("barangay");

  municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';
  barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
  barangaySelect.disabled = true;

  let municipalities = [];

  if (province === "Bulacan") {
    municipalities = ["Hagonoy", "Calumpit", "Malolos City", "Baliuag"];
  } else if (province === "Pampanga") {
    municipalities = ["Angeles City", "Apalit", "Guagua", "Lubao"];
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

  // 🏙️ Bulacan
  if (municipality === "Hagonoy") {
    barangays = [
      "Abulalas", "Carillo", "Iba", "Iba-Ibayo", "Mercado", "Palapat", "Pugad",
      "San Agustin", "San Isidro", "San Juan", "San Miguel", "San Nicolas",
      "San Pablo", "San Pedro", "San Roque", "San Sebastian", "San Pascual",
      "Santa Cruz", "Santa Elena", "Santa Monica", "Santo Niño", "Santo Rosario",
      "Tampok", "Tibaguin"
    ];
  } else if (municipality === "Calumpit") {
    barangays = [
      "Balite", "Balungao", "Bugyon", "Calizon", "Calumpang", "Corazon", "Frances",
      "Gatbuca", "Gugu", "Iba Este", "Iba O’este", "Longos", "Malolos", "Meyto",
      "Palimbang", "Panducot", "Poblacion", "Pungo", "San Jose", "Santo Niño",
      "Sapang Bayan", "Suklayin", "Sunga", "Tinejero"
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

  // 🏙️ Pampanga
  else if (municipality === "Angeles City") {
    barangays = [
      "Anunas", "Balibago", "Capaya", "Cuayan", "Cutcut", "Cutud",
      "Lourdes North West", "Lourdes Sur", "Malabanias", "Margot",
      "Mining", "Pampang", "Pandan", "Pulungbulu", "San Jose",
      "Santo Rosario", "Sapangbato"
    ];
  } else if (municipality === "Apalit") {
    barangays = [
      "Balucuc", "Calantipe", "Cansinala", "Capalangan", "Colgante",
      "Paligui", "Sampaloc", "San Juan", "San Vicente", "Sucad",
      "Sulipan", "Tabuyuc"
    ];
  } else if (municipality === "Guagua") {
    barangays = [
      "Ascomo", "Bancal", "Betis", "Lambac", "Magsaysay", "Maquiapo",
      "Natividad", "Poblacion", "San Agustin", "San Antonio", "San Jose",
      "San Juan", "San Marcos", "San Matias", "Santa Filomena", "Santo Niño"
    ];
  } else if (municipality === "Lubao") {
    barangays = [
      "Bancal", "Balantacan", "Calangain", "Del Carmen", "Don Ignacio Dimson",
      "Prado Siongco", "Remedios", "San Antonio", "San Matias", "Santa Barbara",
      "Santa Catalina", "Santa Cruz", "Santo Domingo", "Santo Niño", "Santo Tomas"
    ];
  }

  barangays.forEach(b => {
    const option = document.createElement("option");
    option.value = b;
    option.textContent = b;
    barangaySelect.appendChild(option);
  });

  barangaySelect.disabled = barangays.length === 0;
}