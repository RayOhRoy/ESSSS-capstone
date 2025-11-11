let isEditing = false; // Start in view mode
let originalValues = {};

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

// 🟢 Toggle Edit <-> Save/Cancel
async function toggleEditSave(event) {
  if (event) event.preventDefault();

  const form = document.getElementById("update_projectForm");
  const inputs = form.querySelectorAll("input:not([type='hidden'])");
  const selects = form.querySelectorAll("select");
  const attachIcons = form.querySelectorAll(".attach-icon");
  const saveBtn = document.getElementById("update-save-btn");
  const editBtn = document.getElementById("update-edit-btn");
  const deleteBtn = document.getElementById("update-delete-btn");
  const printQRBtn = document.getElementById("update-printqr-btn");
  const requestTypeField = document.getElementById("requestType");

  // Determine if Save was clicked
  const isSaveClick = event?.target?.id === "update-save-btn";

  // If not editing → enter edit mode
  if (!isEditing) {
    isEditing = true;
    toggleDocumentTableEditable(true);

    // Enable all inputs
    inputs.forEach(input => {
      if (input.type !== "file") input.readOnly = false;
      if (["radio", "checkbox"].includes(input.type)) input.disabled = false;
    });
    selects.forEach(select => select.disabled = false);
    attachIcons.forEach(icon => (icon.style.display = "inline-block"));

    if (saveBtn) saveBtn.style.display = "inline-block";
    if (printQRBtn) printQRBtn.style.display = "none";
    if (deleteBtn) deleteBtn.style.display = "none";

    if (editBtn) {
      editBtn.textContent = "Cancel";
      editBtn.classList.remove("btn-red");
      editBtn.classList.add("btn-gray");
    }

    // --- Populate dependent selects first ---
    repopulateMunicipalitySelect();
    repopulateBarangaySelect();
    updateApprovalSectionVisibility();

    requestTypeField?.addEventListener("change", updateApprovalSectionVisibility);

    const startInput = document.getElementById("surveyStartDate");
    if (startInput) startInput.addEventListener("change", updateSurveyEndDateMin);
    updateSurveyEndDateMin();

    // ✅ Store original values AFTER repopulating selects
    // Force select values to exist in the new options
    const provinceSelect = document.getElementById("provinceedit");
    const municipalitySelect = document.getElementById("municipalityedit");
    const barangaySelect = document.getElementById("barangayedit");

    if (provinceSelect) originalValues["province"] = provinceSelect.value;
    if (municipalitySelect && Array.from(municipalitySelect.options).some(o => o.value === municipalitySelect.value)) {
      originalValues["municipality"] = municipalitySelect.value;
    }
    if (barangaySelect && Array.from(barangaySelect.options).some(o => o.value === barangaySelect.value)) {
      originalValues["barangay"] = barangaySelect.value;
    }

    // Then store all other inputs/checkboxes/radios
    storeOriginalValues();

    return;
  }

  // Already editing → handle Save or Cancel
  if (isSaveClick) {
    // ✅ Save logic
    await saveChanges(); // your existing saveChanges function

    // After save, toggle back to view mode
    finalizeViewMode();

  } else {
    // ❌ Cancel
    finalizeViewMode(true); // reset to original
  }

  // 🔹 Shared: finalize view mode
  function finalizeViewMode(isCancel = false) {
    toggleDocumentTableEditable(false);

    inputs.forEach(input => {
      if (input.type !== "file") input.readOnly = true;
      if (["radio", "checkbox"].includes(input.type)) input.disabled = true;
    });

    selects.forEach(select => (select.disabled = true));
    attachIcons.forEach(icon => (icon.style.display = "none"));

    if (saveBtn) {
      saveBtn.style.display = "none";
      saveBtn.textContent = "Save Changes";
      saveBtn.disabled = false;
    }

    if (printQRBtn) printQRBtn.style.display = "inline-block";
    if (deleteBtn) deleteBtn.style.display = "inline-block";

    if (editBtn) {
      editBtn.textContent = "Edit";
      editBtn.classList.remove("btn-gray");
      editBtn.classList.add("btn-red");
    }

    requestTypeField?.removeEventListener("change", updateApprovalSectionVisibility);

    const startInput = document.getElementById("surveyStartDate");
    if (startInput) startInput.removeEventListener("change", updateSurveyEndDateMin);

    isEditing = false;

    if (isCancel) {
      // Restore all fields from originalValues
      for (const key in originalValues) {
        const field = form.querySelector(`[name="${key}"]`);
        if (!field) continue;

        if (field.tagName === "SELECT") {
          // Rebuild options first if needed
          if (key === "municipality") repopulateMunicipalitySelect();
          else if (key === "barangay") repopulateBarangaySelect();

          // Restore value
          field.value = originalValues[key] || "";

          // 🔒 Disable select after restoring value
          field.disabled = true;
        } else if (field.type === "checkbox") {
          field.checked = originalValues[key];
        } else if (field.type === "radio") {
          const radio = form.querySelector(`[name="${key}"][value="${originalValues[key]}"]`);
          if (radio) radio.checked = true;
        } else {
          field.value = originalValues[key];
        }
      }

      // Reset document-table checkboxes/files
      const table = document.querySelector(".document-table");
      if (table) {
        table.querySelectorAll("tr").forEach(row => {
          const cb = row.querySelector("input[type='checkbox']");
          const fileSpan = row.querySelector(".existing-file");
          const noFile = row.querySelector(".no-file");
          const fileInput = row.querySelector("input[type='file']");

          if (fileInput) fileInput.value = "";
          if (cb) cb.disabled = true;

          if (fileSpan) {
            const hasOldFile = fileSpan.textContent.trim() !== "";
            fileSpan.style.display = hasOldFile ? "inline" : "none";
            if (noFile) noFile.style.display = hasOldFile ? "none" : "inline";
          }

          row.classList.remove("file-removed", "new-row");
        });
      }

    } else {
      // ✅ Save successful → update originalValues
      storeOriginalValues();
    }
  }
}

function removeFile(el) {
  const row = el.closest('tr');
  if (!row) return;

  const fileSpan = row.querySelector('.existing-file');
  const noFileIcon = row.querySelector('.no-file');
  const fileInput = row.querySelector('input[type="file"]');
  const removeBtn = row.querySelector('.remove-file'); // ✅ X button

  // Hide the file, show "No file"
  if (fileSpan) {
    fileSpan.textContent = '';
    fileSpan.style.display = 'none';
  }
  if (noFileIcon) noFileIcon.style.display = 'inline';

  // Clear file input
  if (fileInput) fileInput.value = '';

  // Hide the remove button immediately
  if (removeBtn) removeBtn.style.display = 'none';

  // Optionally mark row for deletion
  row.classList.add('file-removed');
}


function uploadFileedit(input, key) {
  const file = input.files[0];
  if (!file) return;

  const row = input.closest('tr');
  if (!row) return;

  const fileList = row.querySelector('.file-list');
  const existingFileSpan = fileList.querySelector('.existing-file');
  const noFileIcon = fileList.querySelector('.no-file');
  const removeBtn = row.querySelector('.remove-file'); // ✅ get X button

  // Update UI immediately
  existingFileSpan.textContent = file.name;
  existingFileSpan.style.display = 'inline';
  noFileIcon.style.display = 'none';

  // Show remove button if editing
  if (removeBtn) removeBtn.style.display = 'inline';

  // Mark row as having a new file
  row.classList.add('new-row');

  console.log(`File selected for ${key}:`, file.name);
}


function toggleDocumentTableEditable(isEditable) {
  const table = document.querySelector('.document-table');
  if (!table) return;

  table.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = !isEditable);
  table.querySelectorAll('.attach-icon').forEach(icon => icon.style.display = isEditable ? 'inline-block' : 'none');
  table.querySelectorAll('.hidden-file').forEach(f => f.disabled = !isEditable);

  // Show/hide remove button
  table.querySelectorAll('.remove-file').forEach(xBtn => {
    const fileSpan = xBtn.closest('tr').querySelector('.existing-file');
    if (isEditable && fileSpan && fileSpan.textContent.trim() !== '') {
      xBtn.style.display = 'inline';
    } else {
      xBtn.style.display = 'none';
    }
  });

  // Maintain file / no-file visibility
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

// 💾 Save changes safely
async function saveChanges() {
  const form = document.getElementById('update_projectForm');
  if (!form) return alert('Form not found!');

  const saveBtn = document.getElementById('update-save-btn');
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
  }

  // --- Required fields validation
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
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Changes';
      }
      return;
    }
  }

  const formData = new FormData(form);

  // 🧩 Include all physical checkboxes, even if unchecked
  form.querySelectorAll('input[type="checkbox"][name^="physical_"]').forEach(cb => {
    const fieldName = cb.name; // e.g. physical_lotplan
    const value = cb.checked ? 'on' : 'off';
    formData.set(fieldName, value);
  });


  // 🔹 Include digital document names even if no new file is uploaded
  form.querySelectorAll('.document-table tr').forEach(row => {
    const key = row.dataset.docid || ''; // or use your dataset if needed
    const fileInput = row.querySelector('input[type="file"][name^="digital_"]');
    const fileSpan = row.querySelector('.existing-file');

    if (fileInput) {
      const fieldName = fileInput.name; // e.g. digital_lotplan

      // If no new file selected but an existing file is displayed
      if ((!fileInput.files || fileInput.files.length === 0) && fileSpan && fileSpan.textContent.trim() !== '') {
        formData.set(fieldName, fileSpan.textContent.trim()); // store existing filename
      }
    }
  });

  // Ensure projectId is included
  const projectIdInput = document.getElementById('projectId');
  if (!projectIdInput || !projectIdInput.value) {
    alert('Project ID missing!');
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Save Changes';
    }
    return;
  }
  
  const projectId = projectIdInput.value; // ✅ use the value, not the element
  formData.set('projectId', projectId);

  // --- Handle document table rows safely
  document.querySelectorAll('.document-table tr').forEach(row => {
    const docId = row.dataset.id;
    if (!docId) return;

    const fileInput = row.querySelector('input[type="file"]');
    const cb = row.querySelector('input[type="checkbox"]');

    // 1️⃣ Deleted files
    if (row.classList.contains('file-removed')) {
      formData.append('deleteDocs[]', docId);
    }

    // 2️⃣ New files
    if (fileInput && fileInput.files.length > 0) {
      formData.append(`file_${docId}`, fileInput.files[0]);
    }

    // 3️⃣ Track unchecked physical checkboxes
    if (cb && cb.type === 'checkbox' && !cb.checked) {
      formData.append('uncheckedDocs[]', docId);
    }
  });

  // 🧩 DEBUG: Log everything being sent to PHP
  console.log("📦 Data being sent to PHP:");
  for (const [key, value] of formData.entries()) {
    if (value instanceof File) {
      console.log(`→ ${key}: [File] name="${value.name}", size=${value.size} bytes`);
    } else {
      console.log(`→ ${key}: ${value}`);
    }
  }

  try {
    const res = await fetch('model/update_project.php', {
      method: 'POST',
      body: formData
    });

    if (!res.ok) throw new Error(`Network error: ${res.statusText}`);

    const text = await res.text();
    console.log('Raw server response:', text);
    let data;
    try {
      data = JSON.parse(text);
    } catch (err) {
      console.error('JSON parse failed:', err);
      alert('Server returned invalid JSON. Check console for details.');
      return;
    }

    console.log('Server response:', data);

    if (data.status === 'success') {
      alert('Changes saved successfully!');

      // Update document table UI
      const table = document.querySelector('.document-table');
      if (table && Array.isArray(data.updatedDocs)) {
        table.querySelectorAll('tr').forEach(row => {
          const docType = row.dataset.doctype?.trim();
          const updatedDoc = data.updatedDocs.find(d => d.DocumentType === docType);
          if (!updatedDoc) return;

          const fileSpan = row.querySelector('.existing-file');
          const noFile = row.querySelector('.no-file');
          const cb = row.querySelector('input[type="checkbox"]');

          if (fileSpan) {
            if (updatedDoc.DigitalLocation) {
              fileSpan.textContent = updatedDoc.DigitalLocation;
              fileSpan.style.display = 'inline';
              if (noFile) noFile.style.display = 'none';
            } else {
              fileSpan.textContent = '';
              fileSpan.style.display = 'none';
              if (noFile) noFile.style.display = 'inline';
            }
          }

          if (cb) cb.disabled = true;
          row.classList.remove('file-removed', 'new-row');
        });
      }

      storeCurrentValues();
      finalizeViewMode(false);

      loadAdminPage('project.php?projectId=' + encodeURIComponent(projectId));
    } else {
      alert('Error: ' + (data.message || 'Unknown error'));
    }

  } catch (err) {
    console.error(err);
    alert('Changes saved successfully');
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Save Changes';
    }
  }
}

// 🔹 Finalize view mode (Save or Cancel)
function finalizeViewMode(isCancel = false) {
  const form = document.getElementById("update_projectForm");
  const inputs = form.querySelectorAll("input:not([type='hidden'])");
  const selects = form.querySelectorAll("select");
  const attachIcons = form.querySelectorAll(".attach-icon");
  const saveBtn = document.getElementById("update-save-btn");
  const editBtn = document.getElementById("update-edit-btn");
  const printQRBtn = document.getElementById("update-printqr-btn");
  const requestTypeField = document.getElementById("requestType");

  toggleDocumentTableEditable(false);

  inputs.forEach(input => {
    if (input.type !== "file") input.readOnly = true;
    if (["radio", "checkbox"].includes(input.type)) input.disabled = true;
  });

  selects.forEach(select => (select.disabled = true));
  attachIcons.forEach(icon => (icon.style.display = "none"));

  if (saveBtn) {
    saveBtn.style.display = "none";
    saveBtn.textContent = "Save Changes";
    saveBtn.disabled = false;
  }

  if (printQRBtn) printQRBtn.style.display = "inline-block";

  if (editBtn) {
    editBtn.textContent = "Edit";
    editBtn.classList.remove("btn-gray");
    editBtn.classList.add("btn-red");
  }

  requestTypeField?.removeEventListener("change", updateApprovalSectionVisibility);

  const startInput = document.getElementById("surveyStartDate");
  if (startInput) startInput.removeEventListener("change", updateSurveyEndDateMin);

  isEditing = false;

  if (isCancel) {
    // 🧹 Reset form to original state
    form.reset();

    const table = document.querySelector(".document-table");
    if (table) {
      table.querySelectorAll("tr").forEach(row => {
        const cb = row.querySelector("input[type='checkbox']");
        const fileSpan = row.querySelector(".existing-file");
        const noFile = row.querySelector(".no-file");
        const fileInput = row.querySelector("input[type='file']");

        if (fileInput) fileInput.value = "";
        if (cb) cb.disabled = true;

        if (fileSpan) {
          const hasOldFile = fileSpan.textContent.trim() !== "";
          fileSpan.style.display = hasOldFile ? "inline" : "none";
          if (noFile) noFile.style.display = hasOldFile ? "none" : "inline";
        }

        // Remove deletion/new markers
        row.classList.remove('file-removed', 'new-row');
      });
    }
  } else {
    // ✅ Save successful → update originalValues
    storeCurrentValues();
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
  const province = document.getElementById("provinceedit").value;
  const municipalitySelect = document.getElementById("municipalityedit");
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

  // ✅ Restore original value if exists
  const originalMunicipality = originalValues["municipality"] || "";
  if (originalMunicipality && Array.from(municipalitySelect.options).some(o => o.value === originalMunicipality)) {
    municipalitySelect.value = originalMunicipality;
  } else {
    municipalitySelect.value = "";
  }

  municipalitySelect.disabled = false;
}

// 📍 Barangay dropdown
function repopulateBarangaySelect() {
  const municipality = document.getElementById("municipalityedit").value;
  const barangaySelect = document.getElementById("barangayedit");
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

  // ✅ Restore original value if exists
  const originalBarangay = originalValues["barangay"] || "";
  if (originalBarangay && Array.from(barangaySelect.options).some(o => o.value === originalBarangay)) {
    barangaySelect.value = originalBarangay;
  } else {
    barangaySelect.value = "";
  }

  barangaySelect.disabled = false;
}

function loadMunicipalitiesedit() {
  const province = document.getElementById("provinceedit").value;
  const municipalitySelect = document.getElementById("municipalityedit");
  const barangaySelect = document.getElementById("barangayedit");

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
function loadBarangaysedit() {
  const municipality = document.getElementById("municipalityedit").value;
  const barangaySelect = document.getElementById("barangayedit");

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

  // Fetch documents
  fetch(`model/get_project_docs.php?projectId=${encodeURIComponent(projectId)}`)
    .then(res => res.json())
    .then(docs => {
      if (!docs.length && !projectQR) {
        console.warn("No QR codes to print.");
        return;
      }

      const documentTypes = docs.map(doc => doc.DocumentType);

      // Modal HTML
      const modalHTML = `
      <div id="qrModal" style="
          position: fixed; top: 0; left: 0; width: 100%; height: 100%;
          background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;
          z-index: 9999;
        ">
        <div style="
          background: #fff; padding: 20px; border-radius: 10px; max-width: 450px; width: 90%;
          text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        ">
          <style>
            #qrModal button {
              transition: all 0.2s ease;
            }
            #qrModal button:hover {
              filter: brightness(90%);
            }
          </style>

          <!-- Initial Choice -->
          <div id="qrChoice">
            <h3 style="margin-bottom: 20px;">Select QR Codes to Print</h3>
            <button id="printAllBtn" style="
              display:block; width: 80%; margin: 10px auto; padding: 12px; font-size:16px; cursor:pointer;
              border:none; border-radius:6px; background-color:#7B0302; color:#fff;
            ">Print All</button>
            <button id="selectDocsBtn" style="
              display:block; width: 80%; margin: 10px auto; padding: 12px; font-size:16px; cursor:pointer;
              border:none; border-radius:6px; background-color:#7B0302; color:#fff;
            ">Select Documents</button>
          </div>

          <!-- Document selection -->
          <div id="selectDocuments" style="display:none; margin-top: 10px; text-align:left;">
            <h3 style="margin-bottom: 15px;">Select Documents</h3>
            <div style="
              display: flex; flex-wrap: wrap; gap: 10px; max-height: 200px; overflow-y: auto;
            ">
              ${documentTypes.map(type => `
                <label style="
                  display:flex; align-items:center; gap:5px; background:#f0f0f0; padding:6px 10px; border-radius:5px;
                ">
                  <input type="checkbox" name="docType" value="${type}" checked> ${type}
                </label>
              `).join('')}
            </div>
            <div style="margin-top: 15px; text-align:center;">
              <button id="backBtn" style="
                padding:8px 16px; font-size:14px; margin-right:10px; cursor:pointer;
                border:none; border-radius:5px; background:#6c757d; color:#fff;
              ">Back</button>
              <button id="printSelectedBtn" style="
                padding:8px 16px; font-size:14px; cursor:pointer;
                border:none; border-radius:5px; background:#7B0302; color:#fff;
              ">Print Selected</button>
            </div>
          </div>
        </div>
      </div>
    `;

      const modalWrapper = document.createElement('div');
      modalWrapper.innerHTML = modalHTML;
      document.body.appendChild(modalWrapper);

      // Elements
      const qrChoice = modalWrapper.querySelector('#qrChoice');
      const selectDocumentsDiv = modalWrapper.querySelector('#selectDocuments');
      const printAllBtn = modalWrapper.querySelector('#printAllBtn');
      const selectDocsBtn = modalWrapper.querySelector('#selectDocsBtn');
      const backBtn = modalWrapper.querySelector('#backBtn');
      const printSelectedBtn = modalWrapper.querySelector('#printSelectedBtn');

      // Print All button
      printAllBtn.addEventListener('click', () => {
        modalWrapper.remove();
        printQR(docs, projectQR);
      });

      // Show document selection
      selectDocsBtn.addEventListener('click', () => {
        qrChoice.style.display = 'none';
        selectDocumentsDiv.style.display = 'block';
      });

      // Back button
      backBtn.addEventListener('click', () => {
        selectDocumentsDiv.style.display = 'none';
        qrChoice.style.display = 'block';
      });

      // Print Selected button
      printSelectedBtn.addEventListener('click', () => {
        const checkedTypes = Array.from(modalWrapper.querySelectorAll('input[name="docType"]:checked'))
          .map(cb => cb.value);
        const filteredDocs = docs.filter(doc => checkedTypes.includes(doc.DocumentType));

        modalWrapper.remove();
        printQR(filteredDocs, projectQR);
      });

      // Function to generate print HTML
      function printQR(selectedDocs, projectQRCode) {
        const qrImages = [];
        if (projectQRCode) {
          qrImages.push({ src: projectQRCode, label: projectId });
        }
        selectedDocs.forEach(doc => {
          if (doc.DocumentQR) {
            const labelText = doc.DocumentType === projectId
              ? doc.DocumentType
              : `${doc.DocumentType} (${projectId})`;
            qrImages.push({ src: doc.DocumentQR, label: labelText });
          }
        });

        if (qrImages.length === 0) {
          console.warn("No QR codes selected.");
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
                @page { size: A4 portrait; margin: 0mm; }
                body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                .print-wrapper { display: flex; flex-direction: column; align-items: center; }
                .qr-grid {
                  display: grid;
                  grid-template-columns: repeat(4, 50mm);
                  grid-template-rows: repeat(7, 38mm);
                  gap: 4mm 2mm;
                  justify-content: center;
                  padding: 0 3mm;
                }
                .qr-block {
                  width: 50mm;
                  height: 38mm;
                  border: none;
                  display: flex;
                  flex-direction: column;
                  align-items: center;
                  justify-content: center;
                  box-sizing: border-box;
                  padding: 1mm;
                }
                .qr-block img { width: 42mm; height: 26mm; object-fit: contain; }
                .label { margin-top: 1.5mm; font-size: 9px; text-align: center; word-wrap: break-word; }
              </style>
            </head>
            <body>
              <div class="print-wrapper">
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
      }
    })
    .catch(err => console.error("Failed to fetch document QR codes.", err));
}

function initDeleteProjectButton() {
  const deleteBtn = document.getElementById("update-delete-btn");
  const projectIdInput = document.getElementById("projectId");

  if (!deleteBtn || !projectIdInput) return;

  deleteBtn.onclick = () => {
    showConfirmModaldel("Are you sure you want to delete this project?", () => {
      showConfirmModaldel("This action is irreversible. Delete permanently?", async () => {
        const projectId = projectIdInput.value.trim();
        const formData = new FormData();
        formData.append("projectId", projectId);

        try {
          const res = await fetch("model/delete_project.php", {
            method: "POST",
            body: formData
          });

          if (res.ok) {
            showAlertModal("Project deleted successfully!", "success");
            setTimeout(() => location.reload(), 1500);
          } else {
            showAlertModal("Failed to delete project.", "error");
          }
        } catch (err) {
          console.error(err);
          showAlertModal("Error connecting to server.", "error");
        }
      }, true); // enable countdown
    });
  };
}

/* Confirmation Modal */
// Inject global modal media query styles once
if (!document.getElementById("modal-media-style")) {
  const style = document.createElement("style");
  style.id = "modal-media-style";
  style.innerHTML = `
    @media (max-width: 1080px) {
      .custom-modal-content {
        max-width: 80% !important;
        padding: 15px !important;
        height:300px;
      }
      .custom-modal-content p {
        font-size: 50px !important;
      }
     @media (max-width: 1080px) {
  .custom-modal-buttons {
    display: flex !important;       
    flex-direction: row !important;    
    gap: 12px !important;              
    justify-content: center;           
    width: 100%;                    
  }

  .custom-modal-buttons button {
    flex: 1;         
    max-width: 200px;   
    padding: 8px 0;      
    font-size:50px;
  }
}
    }
  `;
  document.head.appendChild(style);
}

/* Confirmation Modal */
function showConfirmModaldel(message, onConfirm, withCountdown = false) {
  const modal = document.createElement("div");
  modal.style = `
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;
    z-index: 9999;
  `;

  modal.innerHTML = `
    <div class="custom-modal-content" style="
      background: white; padding: 20px 25px; border-radius: 10px;
      text-align: center; max-width: 350px; width: 90%;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      animation: fadeIn 0.2s ease;
    ">
      <p>${message}</p>
      <div class="custom-modal-buttons" style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
        <button id="noBtn" style="
          background:#ccc; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;
        ">Cancel</button>
        <button id="yesBtn" style="
          background:#7B0302; color:white; border:none; padding:8px 15px; border-radius:5px;
          cursor:pointer; opacity:${withCountdown ? 0.6 : 1};
        ">Yes</button>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  const yesBtn = modal.querySelector("#yesBtn");
  const noBtn = modal.querySelector("#noBtn");

  noBtn.onclick = () => modal.remove();

  if (withCountdown) {
    let countdown = 2;
    yesBtn.disabled = true;
    yesBtn.textContent = `Yes (${countdown})`;

    const interval = setInterval(() => {
      countdown--;
      if (countdown > 0) {
        yesBtn.textContent = `Yes (${countdown})`;
      } else {
        clearInterval(interval);
        yesBtn.textContent = "Yes";
        yesBtn.disabled = false;
        yesBtn.style.opacity = "1";
      }
    }, 1000);
  }

  yesBtn.onclick = () => {
    if (yesBtn.disabled) return;
    modal.remove();
    if (typeof onConfirm === "function") onConfirm();
  };
}

/* Alert Modal */
function showAlertModal(message, type = "info") {
  const modal = document.createElement("div");
  modal.style = `
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;
    z-index: 9999;
  `;

  const color =
    type === "success" ? "#13572fff" :
      type === "error" ? "#e74c3c" :
        "#3498db";

  modal.innerHTML = `
    <div class="custom-modal-content" style="
      background: white; padding: 20px 25px; border-radius: 10px;
      text-align: center; max-width: 350px; width: 90%;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      animation: fadeIn 0.2s ease;
    ">
      <p style="color:${color};">${message}</p>
      <div class="custom-modal-buttons" style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
        <button style="
          margin-top: 15px; background:${color}; color:white; border:none; padding:8px 15px;
          border-radius:5px; cursor:pointer;
        ">OK</button>
      </div>
    </div>
  `;

  document.body.appendChild(modal);
  modal.querySelector("button").onclick = () => modal.remove();
}
