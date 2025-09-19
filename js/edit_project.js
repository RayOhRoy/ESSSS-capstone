let toggleEditInitialized = false;
let initialFormState = {}; // Store original values before editing

// Address data hierarchy
const dataMap = {
  'Bulacan': {
    'Hagonoy': [
      "Abulalas", "Carillo", "Iba", "Iba-Ibayo", "Mercado", "Palapat", "Pugad",
      "San Agustin", "San Isidro", "San Juan", "San Miguel", "San Nicolas",
      "San Pablo", "San Pedro", "San Roque", "San Sebastian", "San Pascual",
      "Santa Cruz", "Santa Elena", "Santa Monica", "Santa Niño", "Santa Rosario",
      "Santo Niño", "Santo Rosario", "Tampok", "Tibaguin"
    ],
    'Calumpit': [
      "Balite", "Balungao", "Bugyon", "Calizon", "Calumpang", "Corazon", "Frances",
      "Gatbuca", "Gugu", "Iba Este", "Iba O’este", "Longos", "Malolos",
      "Meyto", "Palimbang", "Panducot", "Poblacion", "Pungo", "San Jose",
      "Santo Niño", "Sapang Bayan", "Suklayin", "Sunga", "Tinejero"
    ]
  }
};

function handleEditButton() {
  const form = document.getElementById('update_projectForm');
  const editBtn = document.getElementById('update-edit-btn');
  const saveBtn = document.getElementById('update-save-btn');

  if (!form || !editBtn || !saveBtn) {
    console.warn('Form, Edit button, or Save button not found.');
    return;
  }

  // Check if currently in edit mode by looking at the first input's readonly/disabled state
  const firstInput = form.querySelector('input, select');
  const isEditable = firstInput && !firstInput.hasAttribute('readonly') && !firstInput.hasAttribute('disabled');

  if (!isEditable) {
    // --- Enter Edit Mode ---
    saveInitialFormState();

    // Enable inputs for editing
    form.querySelectorAll('input[readonly], select[disabled], input[type="radio"][disabled], input[type="checkbox"][disabled]')
      .forEach(el => {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
      });

    ['province', 'municipality', 'barangay'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.removeAttribute('disabled');
    });

    // Initialize handlers after enabling inputs
    handleProvinceChange();
    handleMunicipalityChange();
    // Attach change listener for requestType only once
    const requestTypeSelect = document.getElementById('requestType');
    if (requestTypeSelect && !requestTypeSelect.hasAttribute('data-listener-attached')) {
      requestTypeSelect.addEventListener('change', updateApprovalVisibility);
      requestTypeSelect.setAttribute('data-listener-attached', 'true');  // prevent duplicates
    }

    updateApprovalVisibility();

    editBtn.textContent = 'Cancel';
    editBtn.style.backgroundColor = 'gray';
    saveBtn.style.display = 'inline-block';

    document.querySelectorAll('.attach-icon').forEach(label => {
      label.style.display = 'inline-block';
      const input = label.querySelector('input[type="file"]');
      if (input) input.style.display = 'none';
    });

    addRemoveIcons();

  } else {
    // --- Cancel Edit Mode ---
    restoreInitialFormState();

    // Disable inputs again
    form.querySelectorAll('input, select').forEach(el => {
      if (el.type === 'checkbox' || el.type === 'radio') {
        el.setAttribute('disabled', 'disabled');
      } else {
        el.setAttribute('readonly', 'readonly');
      }
    });

    editBtn.textContent = 'Edit';
    editBtn.style.backgroundColor = '#7B0302';
    saveBtn.style.display = 'none';

    document.querySelectorAll('.attach-icon').forEach(label => {
      label.style.display = 'none';
      const input = label.querySelector('input[type="file"]');
      if (input) input.style.display = 'none';
    });

    document.querySelectorAll('.remove-icon').forEach(icon => icon.remove());

    // Optionally reset survey date inputs disabled state manually here if needed
    const startDateInput = document.getElementById('survey_start_date');
    const endDateInput = document.getElementById('survey_end_date');
    if (startDateInput && endDateInput) {
      if (!startDateInput.value) {
        endDateInput.disabled = true;
        endDateInput.value = '';
      }
    }
  }
}

function addRemoveIcons() {
  // Only add remove icons in edit mode (button text = 'Cancel')
  const isEditMode = document.getElementById('update-edit-btn').textContent === 'Cancel';
  if (!isEditMode) return;

  // Remove existing remove icons first to prevent duplicates
  document.querySelectorAll('.remove-icon').forEach(icon => icon.remove());

  // Add remove icon to each existing file span that does not already have one
  document.querySelectorAll('.digital-cell .file-list .existing-file').forEach(span => {
    const removeIcon = document.createElement('span');
    removeIcon.className = 'remove-icon';
    removeIcon.textContent = '×';
    removeIcon.title = 'Remove file';
    removeIcon.style.color = 'red';
    removeIcon.style.cursor = 'pointer';
    removeIcon.style.fontWeight = 'bold';
    removeIcon.style.marginLeft = '5px';

    removeIcon.addEventListener('click', () => removeAttachedFile(removeIcon));
    span.appendChild(removeIcon);
  });
}

function saveInitialFormState() {
  const form = document.getElementById('update_projectForm');
  if (!form) return;

  initialFormState = {};

  form.querySelectorAll('input, select').forEach(el => {
    const key = el.id || el.name;
    if (!key) return;

    if (el.type === 'checkbox' || el.type === 'radio') {
      initialFormState[key] = el.checked;
    } else {
      initialFormState[key] = el.value;
    }
  });

  // Store original filenames for digital files
  document.querySelectorAll('.digital-cell').forEach(cell => {
    const fileNameEl = cell.querySelector('.existing-file');
    cell.setAttribute('data-original-filename', fileNameEl ? fileNameEl.textContent : '');
  });

  console.log('Initial form state saved:', initialFormState);
}

function restoreInitialFormState() {
  const form = document.getElementById('update_projectForm');
  if (!form) return;

  form.querySelectorAll('input, select').forEach(el => {
    const key = el.id || el.name;
    if (!key) return;

    if (key in initialFormState) {
      if (el.type === 'checkbox' || el.type === 'radio') {
        el.checked = initialFormState[key];
      } else {
        el.value = initialFormState[key];
      }
    }
  });

  const province = initialFormState['province'] || "";
  const municipality = initialFormState['municipality'] || "";
  const barangay = initialFormState['barangay'] || "";

  if (province) {
    handleProvinceChange();
    const municipalitySelect = document.getElementById('municipality');
    if (municipalitySelect && municipality) {
      municipalitySelect.value = municipality;
      handleMunicipalityChange();
    }

    const barangaySelect = document.getElementById('barangay');
    if (barangaySelect && barangay) {
      barangaySelect.value = barangay;
    }
  }

  // Restore filenames and remove hidden removal inputs
  document.querySelectorAll('.digital-cell').forEach(cell => {
    // Remove hidden inputs that mark removal
    cell.querySelectorAll('input[type="hidden"][name^="remove_file_"]').forEach(input => input.remove());

    const fileList = cell.querySelector('.file-list');
    if (!fileList) return;

    // Clear everything inside fileList to avoid duplicates
    fileList.innerHTML = '';

    const originalFilename = cell.getAttribute('data-original-filename')?.trim();

    if (originalFilename) {
      // Create file span
      const fileSpan = document.createElement('span');
      fileSpan.className = 'existing-file';
      fileSpan.textContent = originalFilename;

      // Only add remove icon if in edit mode (edit button text = 'Cancel')
      if (document.getElementById('update-edit-btn').textContent === 'Cancel') {
        const removeIcon = document.createElement('span');
        removeIcon.className = 'remove-icon';
        removeIcon.textContent = '×';

        // Style remove icon explicitly (same as your CSS or better)
        removeIcon.style.color = 'red';
        removeIcon.style.cursor = 'pointer';
        removeIcon.style.fontWeight = 'bold';
        removeIcon.style.marginLeft = '5px';
        removeIcon.title = 'Remove file';

        // Attach click handler
        removeIcon.addEventListener('click', () => removeAttachedFile(removeIcon));

        fileSpan.appendChild(removeIcon);
      }

      fileList.appendChild(fileSpan);
    } else {
      // No file present, show no file text
      const noFile = document.createElement('i');
      noFile.className = 'no-file';
      noFile.textContent = 'No file';
      fileList.appendChild(noFile);
    }
  });
}

function handleProvinceChange() {
  const province = document.getElementById('province').value;
  const municipalitySelect = document.getElementById('municipality');
  const barangaySelect = document.getElementById('barangay');

  const selectedMunicipality = municipalitySelect.value;
  const selectedBarangay = barangaySelect.value;

  municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';

  if (dataMap[province]) {
    Object.keys(dataMap[province]).forEach(municipality => {
      const option = document.createElement('option');
      option.value = municipality;
      option.textContent = municipality;
      municipalitySelect.appendChild(option);
    });
    municipalitySelect.disabled = false;

    if (selectedMunicipality && Object.keys(dataMap[province]).includes(selectedMunicipality)) {
      municipalitySelect.value = selectedMunicipality;
    } else {
      municipalitySelect.value = "";
    }
  } else {
    municipalitySelect.value = "";
    municipalitySelect.disabled = true;
  }

  updateBarangays(province, municipalitySelect.value, selectedBarangay);
}

function handleMunicipalityChange() {
  const province = document.getElementById('province').value;
  const municipality = document.getElementById('municipality').value;
  const barangaySelect = document.getElementById('barangay');

  const selectedBarangay = barangaySelect.value;
  updateBarangays(province, municipality, selectedBarangay);
}

function updateBarangays(province, municipality, selectedBarangay) {
  const barangaySelect = document.getElementById('barangay');

  barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

  const barangays = dataMap[province]?.[municipality] || [];

  barangays.forEach(barangay => {
    const option = document.createElement('option');
    option.value = barangay;
    option.textContent = barangay;
    barangaySelect.appendChild(option);
  });

  barangaySelect.disabled = barangays.length === 0;

  if (selectedBarangay && barangays.includes(selectedBarangay)) {
    barangaySelect.value = selectedBarangay;
  } else {
    barangaySelect.value = "";
  }
}

// Remove attached file (red × icon)
function removeAttachedFile(element) {
  const fileSpan = element.closest('.existing-file');
  if (!fileSpan) return;

  const container = element.closest('.digital-cell');
  const fileList = container.querySelector('.file-list');

  // Remove the file name span
  fileSpan.remove();

  // Show "No file" text if none present
  if (!fileList.querySelector('.existing-file')) {
    const noFile = document.createElement('i');
    noFile.className = 'no-file';
    noFile.textContent = 'No file';
    fileList.appendChild(noFile);
  }

  // Add hidden input to mark file for removal on submit
  const hiddenInput = document.createElement('input');
  hiddenInput.type = 'hidden';
  hiddenInput.name = `remove_file_${Date.now()}`;
  hiddenInput.value = '1';
  container.appendChild(hiddenInput);
}

function uploadFile(input, key) {
  const container = input.closest('.digital-cell');
  const fileList = container.querySelector('.file-list');

  if (!container || !fileList || !input.files.length) return;

  // Hide "No file" text if present
  const noFileText = fileList.querySelector('.no-file');
  if (noFileText) noFileText.style.display = 'none';

  // Loop through selected files and append each one
  Array.from(input.files).forEach(file => {
    const fileSpan = document.createElement('span');
    fileSpan.className = 'existing-file';
    fileSpan.textContent = file.name;

    // Add remove icon
    const removeIcon = document.createElement('span');
    removeIcon.className = 'remove-icon';
    removeIcon.textContent = '×';
    removeIcon.title = 'Remove file';
    removeIcon.style.color = 'red';
    removeIcon.style.cursor = 'pointer';
    removeIcon.style.fontWeight = 'bold';
    removeIcon.style.marginLeft = '5px';

    removeIcon.addEventListener('click', () => {
      fileSpan.remove();

      // Show "No file" if no files remain
      if (!fileList.querySelector('.existing-file')) {
        if (noFileText) {
          noFileText.style.display = 'inline';
        } else {
          const noFile = document.createElement('i');
          noFile.className = 'no-file';
          noFile.textContent = 'No file';
          fileList.appendChild(noFile);
        }
      }
    });

    fileSpan.appendChild(removeIcon);
    fileList.appendChild(fileSpan);
  });

  // Clear the input so same file can be re-uploaded if needed
  input.value = '';
}

function updateApprovalVisibility() {
  const requestTypeSelect = document.getElementById('requestType');
  const toBeApprovedByDiv = document.getElementById('toBeApprovedBy');

  if (!requestTypeSelect || !toBeApprovedByDiv) return;

  const selectedType = requestTypeSelect.value;

  if (selectedType === 'Sketch Plan') {
    toBeApprovedByDiv.style.display = 'none';
  } else {
    toBeApprovedByDiv.style.display = 'block';
  }
}
