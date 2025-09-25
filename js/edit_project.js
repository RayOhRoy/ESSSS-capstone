let isEditing = false; // Start in view mode
let originalValues = {}; // Store original field values

// Call this after the HTML is injected (e.g. via fetch in SPA)
function initializeEditForm() {
    storeOriginalValues();
    disableFormUI();
    updateApprovalSectionVisibility();

    // Attach save button listener here
    const saveBtn = document.getElementById('update-save-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveChanges);
    }
}


// 🧠 Store current values, fix radio group handling
function storeOriginalValues() {
    const form = document.getElementById('update_projectForm');
    const inputs = form.querySelectorAll('input:not([type="hidden"])');
    const selects = form.querySelectorAll('select');

    originalValues = {};

    // Store checked radio button values per group
    const radioGroups = {};

    inputs.forEach(input => {
        if (!input.name) return;

        if (input.type === 'radio') {
            if (input.checked) {
                radioGroups[input.name] = input.value;
            }
        } else {
            originalValues[input.name] = input.value;
        }
    });

    // Save radio group checked values
    for (const name in radioGroups) {
        originalValues[name] = radioGroups[name];
    }

    selects.forEach(select => {
        if (select.name) {
            originalValues[select.name] = select.value;
        }
    });
}

// 🚫 Disable inputs, selects, buttons initially
function disableFormUI() {
    const form = document.getElementById('update_projectForm');
    const inputs = form.querySelectorAll('input:not([type="hidden"])');
    const selects = form.querySelectorAll('select');
    const attachIcons = form.querySelectorAll('.attach-icon');
    const saveBtn = document.getElementById('update-save-btn');

    inputs.forEach(input => {
        if (input.type !== 'file') input.readOnly = true;
        if (input.type === 'radio') input.disabled = true;
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
        // Enable edit mode
        inputs.forEach(input => {
            if (input.type !== 'file') input.readOnly = false;
            if (input.type === 'radio') input.disabled = false;
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

        // ✅ Add live listener for request type
        if (requestTypeField) {
            requestTypeField.addEventListener('change', updateApprovalSectionVisibility);
        }

        const startInput = document.getElementById('surveyStartDate');
        const endInput = document.getElementById('surveyEndDate');

        if (startInput && endInput) {
            updateSurveyEndDateMin();  // Set initial min value
            startInput.addEventListener('change', updateSurveyEndDateMin);
        }

    } else {

        const startInput = document.getElementById('surveyStartDate');
        if (startInput) {
            startInput.removeEventListener('change', updateSurveyEndDateMin);
        }
        // Cancel: revert to original values (fix radio groups)
        inputs.forEach(input => {
            if (!input.name) return;

            if (input.type === 'radio') {
                input.checked = (originalValues[input.name] === input.value);
            } else {
                input.value = originalValues[input.name] || '';
            }
        });

        selects.forEach(select => {
            if (select.name && originalValues[select.name] !== undefined) {
                select.value = originalValues[select.name];
            }
            select.disabled = true;
        });

        inputs.forEach(input => {
            if (input.type !== 'file') input.readOnly = true;
            if (input.type === 'radio') input.disabled = true;
        });

        attachIcons.forEach(icon => icon.style.display = 'none');
        if (saveBtn) saveBtn.style.display = 'none';

        if (editBtn) {
            editBtn.textContent = 'Edit';
            editBtn.classList.remove('btn-gray');
            editBtn.classList.add('btn-red');
        }

        updateApprovalSectionVisibility();

        // ✅ Remove change listener to avoid duplicate calls
        if (requestTypeField) {
            requestTypeField.removeEventListener('change', updateApprovalSectionVisibility);
        }
    }
}

function updateSurveyEndDateMin() {
    const startInput = document.getElementById('surveyStartDate');
    const endInput = document.getElementById('surveyEndDate');
    if (!startInput || !endInput) return;

    if (startInput.value) {
        endInput.min = startInput.value;
        // If end date is before start date, reset it
        if (endInput.value && endInput.value < startInput.value) {
            endInput.value = startInput.value;
        }
    } else {
        endInput.min = ''; // Remove min if no start date
    }
}

function updateApprovalSectionVisibility() {
    const requestType = document.getElementById('requestType')?.value;
    const toBeApprovedBy = document.getElementById('toBeApprovedBy');

    if (!toBeApprovedBy) return;

    if (requestType === 'Sketch Plan') {
        // Hide the approval section
        toBeApprovedBy.style.display = 'none';

        // Set PSD radio checked by default inside this container
        const psdRadio = toBeApprovedBy.querySelector('input[type="radio"][value="PSD"]');
        if (psdRadio) {
            psdRadio.checked = true;
        }

    } else {
        // Show the approval section
        toBeApprovedBy.style.display = 'block';
    }
}

// 🔁 Reload Municipality dropdown while keeping selected value
function repopulateMunicipalitySelect() {
    const province = document.getElementById("province").value;
    const municipalitySelect = document.getElementById("municipality");
    const currentValue = municipalitySelect.value;

    municipalitySelect.innerHTML = "";

    let municipalities = [];

    if (province === "Bulacan") {
        municipalities = ["Hagonoy", "Calumpit"];
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

// 🔁 Reload Barangay dropdown while keeping selected value
function repopulateBarangaySelect() {
    const municipality = document.getElementById("municipality").value;
    const barangaySelect = document.getElementById("barangay");
    const currentValue = barangaySelect.value;

    barangaySelect.innerHTML = "";

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

    barangaySelect.value = currentValue;
    barangaySelect.disabled = false;
}

async function saveChanges() {
    const form = document.getElementById('update_projectForm');
    if (!form) {
        alert('Form not found!');
        return;
    }

    // Required fields to check
    const requiredFields = [
        'lotNumber',
        'clientFirstName',
        'clientLastName',
        'province',
        'municipality',
        'barangay',
        'surveyType',
        'projectStatus',        // Assuming 'status' field's name is 'projectStatus'
        'surveyStartDate',
        'requestType'
    ];

    // Validate required fields are not empty
    for (const fieldName of requiredFields) {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value.trim()) {
            alert('Please fill in all required fields.');
            field?.focus();
            return;  // Stop saving
        }
    }

    // Collect form data
    const formData = new FormData(form);

    // Check if projectId is present
    if (!formData.has('projectId')) {
        const projectIdInput = document.getElementById('projectId');
        if (!projectIdInput || !projectIdInput.value) {
            alert('Project ID is missing!');
            return;
        }
        formData.append('projectId', projectIdInput.value);
    }

    try {
        const response = await fetch('model/update_project.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            alert(`Network error: ${response.statusText}`);
            return;
        }

        const data = await response.json();

        if (data.status === 'success') {
            alert('Changes saved successfully!');

            const projectIdInput = document.getElementById('projectId');
            if (projectIdInput) {
                projectIdInput.value = data.projectID;
            }
            // Update originalValues with current form values to prevent revert to old data
            storeCurrentValues();

            // Switch back to view mode (disabled inputs, etc.)
            toggleEditSave();

        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}


// Helper: Store current form values into originalValues (similar to storeOriginalValues)
function storeCurrentValues() {
    const form = document.getElementById('update_projectForm');
    const inputs = form.querySelectorAll('input:not([type="hidden"])');
    const selects = form.querySelectorAll('select');

    // Store checked radio buttons per group
    const radioGroups = {};

    inputs.forEach(input => {
        if (!input.name) return;

        if (input.type === 'radio') {
            if (input.checked) {
                radioGroups[input.name] = input.value;
            }
        } else {
            originalValues[input.name] = input.value;
        }
    });

    // Save radio group checked values
    for (const name in radioGroups) {
        originalValues[name] = radioGroups[name];
    }

    selects.forEach(select => {
        if (select.name) {
            originalValues[select.name] = select.value;
        }
    });
}
