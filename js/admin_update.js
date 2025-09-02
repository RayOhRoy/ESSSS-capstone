function toggleEditSave() {
  const form = document.getElementById('update_projectForm');
  const editBtn = document.getElementById('update-edit-btn');

  if (!form || !editBtn) {
    console.warn('Form or Edit button not found, toggleEditSave aborted.');
    return;
  }

  // Check if form is currently editable by looking at the first input/select
  const firstInput = form.querySelector('input, select');
  if (!firstInput) {
    console.warn('No input or select found in form.');
    return;
  }

  const isEditable = !firstInput.hasAttribute('readonly') && !firstInput.hasAttribute('disabled');

  if (!isEditable) {
    // Make all inputs editable
    form.querySelectorAll('input[readonly]').forEach(input => input.removeAttribute('readonly'));
    form.querySelectorAll('select[disabled]').forEach(select => select.removeAttribute('disabled'));
    form.querySelectorAll('input[type="radio"][disabled]').forEach(radio => radio.removeAttribute('disabled'));
    form.querySelectorAll('input[type="checkbox"][disabled]').forEach(checkbox => checkbox.removeAttribute('disabled'));

    editBtn.textContent = 'Save';
  } else {
    // Save logic here
    submitForm();

    // Disable fields after save
    form.querySelectorAll('input:not([type="checkbox"]), select, input[type="radio"], input[type="checkbox"]').forEach(el => {
      if (el.type === 'checkbox' || el.type === 'radio') {
        el.setAttribute('disabled', 'disabled');
      } else {
        el.setAttribute('readonly', 'readonly');
      }
    });

    editBtn.textContent = 'Edit';
  }
}

// Your submit function (customize as needed)
function submitForm() {
  alert('Form submitted! Implement saving logic here.');
  // You can do AJAX submission or form.submit() here
}

// Initialization function to attach event listener ONLY if button exists
function initToggleEditSave() {
  const editBtn = document.getElementById('update-edit-btn');
  if (editBtn) {
    editBtn.onclick = toggleEditSave;
  } else {
    console.warn('Edit button not found. toggleEditSave not initialized.');
  }
}
