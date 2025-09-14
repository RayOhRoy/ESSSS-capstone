let toggleEditInitialized = false;
let initialFormState = {}; // To store original values before editing

// Address data hierarchy (unchanged)
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

  const firstInput = form.querySelector('input, select');
  const isEditable = firstInput && !firstInput.hasAttribute('readonly') && !firstInput.hasAttribute('disabled');

  if (!isEditable) {
    // Enter edit mode
    saveInitialFormState();

    form.querySelectorAll('input[readonly], select[disabled], input[type="radio"][disabled], input[type="checkbox"][disabled]')
      .forEach(el => {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
      });

    // Enable cascading address dropdowns explicitly
    ['province', 'municipality', 'barangay'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.removeAttribute('disabled');
    });

    handleProvinceChange();
    handleMunicipalityChange();

    editBtn.textContent = 'Cancel';
    editBtn.style.backgroundColor = 'gray';
    saveBtn.style.display = 'inline-block';
  } else {
    // Cancel edit mode, revert values and disable inputs
    restoreInitialFormState();

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
  }
}

function saveInitialFormState() {
  const form = document.getElementById('update_projectForm');
  if (!form) return;

  initialFormState = {};

  form.querySelectorAll('input, select').forEach(el => {
    const key = el.id || el.name;
    if (!key) return; // skip if no id or name

    if (el.type === 'checkbox' || el.type === 'radio') {
      initialFormState[key] = el.checked;
    } else {
      initialFormState[key] = el.value;
    }
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

// function toggleStorageStatus(checkbox) {
//   const selectElem = checkbox.nextElementSibling;
//   if (checkbox.checked) {
//     selectElem.style.display = '';
//   } else {
//     selectElem.style.display = 'none';
//   }
// }
