function initAdminPage() {
  const contentArea = document.getElementById('content-area');
  if (contentArea) {
    loadAdminPage('user_dashboard.php', initUserMenuDropdown);
    loadScript('https://cdn.jsdelivr.net/npm/chart.js');
    loadScript('https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels', 'initDocumentPieChart');
    loadScript('js/upload.js');
    loadScript('js/documents.js');
    loadScript('js/project_list.js');
    loadScript('js/qr_search.js');
    loadScript('js/edit_project.js');
    loadScript('js/project.js');
    loadScript('js/user_list.js');
    loadScript('js/physical_storage.js');
  }

  // Universal listener for any element with data-page
  document.addEventListener('click', function (e) {
    const target = e.target.closest('[data-page]');
    if (!target) return;

    e.preventDefault();
    const page = target.getAttribute('data-page');

    if (page === 'user_dashboard.php') {
      loadAdminPage(page, () => {
        initUserMenuDropdown();
        initDocumentPieChart(); // initialize pie chart
      });
    } else {
      loadAdminPage(page);
    }

    // --- Update sidebar highlight ---
    // First remove all highlights
    document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));

    // Case 1: clicked element *is* a sidebar menu item
    if (target.classList.contains('menu-item')) {
      target.classList.add('active');
    }
    // Case 2: clicked element is outside (e.g., floating btn),
    // find the matching sidebar menu with same data-page
    else {
      const matchingMenu = document.querySelector(`.menu-item[data-page="${page}"]`);
      if (matchingMenu) matchingMenu.classList.add('active');
    }
  });
}

function initDocumentPieChart() {
  const canvas = document.getElementById('docPieChart');
  if (!canvas) return;

  const chartData = {
    'Sketch Completed': parseInt(canvas.dataset.sketchCompleted || 0),
    'Sketch Pending': parseInt(canvas.dataset.sketchPending || 0),
    'LRA Approval Completed': parseInt(canvas.dataset.lraApprovalCompleted || 0),
    'LRA Approval Pending': parseInt(canvas.dataset.lraApprovalPending || 0),
    'PSD Approval Completed': parseInt(canvas.dataset.psdApprovalCompleted || 0),
    'PSD Approval Pending': parseInt(canvas.dataset.psdApprovalPending || 0),
    'CSD Approval Completed': parseInt(canvas.dataset.csdApprovalCompleted || 0),
    'CSD Approval Pending': parseInt(canvas.dataset.csdApprovalPending || 0)
  };

  const labels = Object.keys(chartData);
  const values = Object.values(chartData);

  // Maroon shades: lighter = completed, darker = pending
  const backgroundColors = [
    '#4A3B2C', // Sketch Completed
    '#3b3024ff', // Sketch Pending
    '#FFD966', // LRA Completed
    '#a8914bff', // LRA Pending
    '#1E3D59', // PSD Completed
    '#101e2bff', // PSD Pending
    '#A0ACAD', // CSD Completed
    '#707a7aff'  // CSD Pending
  ];

  const ctx = canvas.getContext('2d');

  if (window.docPieChartInstance) window.docPieChartInstance.destroy();

  window.docPieChartInstance = new Chart(ctx, {
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: backgroundColors,
        borderColor: '#fff',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'right' },
        tooltip: {
          callbacks: {
            label: function (context) {
              const label = context.label || '';
              const value = context.raw || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = total ? ((value / total) * 100).toFixed(1) : 0;
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
}

function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  fetch(page)
    .then(res => {
      if (!res.ok) throw new Error(`Failed to load ${page}: ${res.status}`);
      return res.text();
    })
    .then(html => {
      contentArea.innerHTML = html;

      // Rebind dropdown every time new content is loaded
      initUserMenuDropdown();
    })
    .catch(err => {
      console.error('Failed to load admin page:', err);
      contentArea.innerHTML = `<p style="color:red;">Failed to load: ${page}</p>`;
    });
}

function loadScript(src, initFunctionName) {
  if (document.querySelector(`script[src="${src}"]`)) return;

  const script = document.createElement('script');
  script.src = src;
  script.onload = () => {
    if (typeof window[initFunctionName] === 'function') {
      window[initFunctionName]();
    }
  };
  document.body.appendChild(script);
}

function initUserMenuDropdown() {
  const userIcon = document.getElementById("user-circle-icon");
  const userMenu = document.getElementById("user-menu");

  if (!userIcon || !userMenu) return;

  // Remove old listeners by cloning element
  const newUserIcon = userIcon.cloneNode(true);
  userIcon.parentNode.replaceChild(newUserIcon, userIcon);

  newUserIcon.addEventListener("click", function (event) {
    event.stopPropagation();
    userMenu.style.display = (userMenu.style.display === "block") ? "none" : "block";
  });

  // Close menu on outside click
  document.addEventListener("click", function (e) {
    if (!newUserIcon.contains(e.target) && !userMenu.contains(e.target)) {
      userMenu.style.display = "none";
    }
  });
}
(function setupUserMenuToggle() {
  if (window.__userMenuToggled) return;
  window.__userMenuToggled = true;

  document.addEventListener('click', function (e) {
    const menu = document.getElementById('userPanel');
    const userIcon = document.getElementById('user-circle-icon');
    const userBottomInfo = document.querySelector('.user-bottom-info');
    const userForgotPassword = document.querySelector('.user-forgot-password');

    if (!menu || !userIcon || !userBottomInfo || !userForgotPassword) return;

    const clickedIcon = e.target.closest('#user-circle-icon');

    if (clickedIcon) {
      e.preventDefault();
      e.stopPropagation();

      const isVisible = menu.style.display === 'block';
      menu.style.display = isVisible ? 'none' : 'block';

      // Toggle active class to switch icon color to white
      userIcon.classList.toggle('active', !isVisible);

      return;
    }

    // Click outside closes menu and removes active icon state
    if (!e.target.closest('#userPanel')) {
      menu.style.display = 'none';
      userIcon.classList.remove('active');

      // Reset panels to default state
      userBottomInfo.style.display = 'block';
      userForgotPassword.style.display = 'none';
    }
  });
})();


(function setupChangePasswordToggle() {
  if (window.__changePasswordSetup) return;
  window.__changePasswordSetup = true;

  document.addEventListener('click', function (e) {
    const changeBtn = e.target.closest('#changepassword-button');
    const cancelBtn = e.target.closest('#cancelchangepassword-button');
    const userBottomInfo = document.querySelector('.user-bottom-info');
    const userForgotPassword = document.querySelector('.user-forgot-password');

    if (!userBottomInfo || !userForgotPassword) return;

    if (changeBtn) {
      e.preventDefault();
      e.stopPropagation();
      userBottomInfo.style.display = 'none';
      userForgotPassword.style.display = 'block';
      return;
    }

    if (cancelBtn) {
      e.preventDefault();
      e.stopPropagation();
      userBottomInfo.style.display = 'block';
      userForgotPassword.style.display = 'none';
      return;
    }
  });
})();

(function setupChangePasswordConfirm() {
  if (window.__changePasswordConfirmSetup) return;
  window.__changePasswordConfirmSetup = true;

  document.addEventListener('click', function (e) {
    const confirmBtn = e.target.closest('#confirmchangepassword-button');
    if (!confirmBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const userForgotPassword = document.querySelector('.user-forgot-password');
    const userBottomInfo = document.querySelector('.user-bottom-info');
    const userPanel = document.getElementById('userPanel');
    const userIcon = document.getElementById('user-circle-icon');
    if (!userForgotPassword || !userBottomInfo || !userPanel || !userIcon) return;

    // Get inputs (assumes order fixed)
    const inputs = userForgotPassword.querySelectorAll('input[type="password"]');
    const currentPassword = inputs[0]?.value.trim();
    const newPassword = inputs[1]?.value.trim();
    const confirmPassword = inputs[2]?.value.trim();

    if (!currentPassword || !newPassword || !confirmPassword) {
      alert('Please fill out all fields.');
      return;
    }

    if (newPassword !== confirmPassword) {
      alert('New password and confirmation do not match.');
      return;
    }

    // Disable confirm button to prevent double submits
    confirmBtn.style.pointerEvents = 'none';

    fetch('model/change_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        currentPassword,
        newPassword,
        confirmPassword,
      }),
    })
      .then(res => res.json())
      .then(data => {
        confirmBtn.style.pointerEvents = 'auto';
        if (data.success) {
          alert('Password changed successfully.');
          // Reset inputs & switch back to bottom info panel
          inputs.forEach(i => i.value = '');
          userBottomInfo.style.display = 'block';
          userForgotPassword.style.display = 'none';

          // Close user panel
          userPanel.style.display = 'none';

          // Reset user icon active state
          userIcon.classList.remove('active');
        } else {
          alert(data.error || 'Failed to change password.');
        }
      })
      .catch(() => {
        confirmBtn.style.pointerEvents = 'auto';
        alert('An error occurred while changing password.');
      });
  });
})();

// para sa sidebar
let originalActive = null;

// isara lang sidebar pag load kung mobile view
if (window.matchMedia("(max-width: 1080px)").matches) {
  document.querySelector(".sidebar").classList.add("closed");
  document.querySelector(".hamburger").classList.add("closed");
}

function toggleMenu() {
  const sidebar = document.querySelector('.sidebar');
  const hamburger = document.querySelector('.hamburger');
  const main = document.querySelector('.main');
  const span = document.querySelector('.hamburger span');

  span.classList.toggle('closed');
  sidebar.classList.toggle('closed');
  hamburger.classList.toggle('closed');
  main.classList.toggle('expanded');

  if (sidebar.classList.contains('closed')) {
    if (activeLink) {
      originalActive = activeLink;
      activeLink.classList.remove('active');
    }
  } else {
    // open state logic
  }
}

// kapag nag click sa menu item, auto close sidebar (mobile view lang)
document.querySelectorAll('.menu-item').forEach(item => {
  item.addEventListener('click', () => {
    const sidebar = document.querySelector('.sidebar');
    if (window.matchMedia("(max-width: 1080px)").matches) {
      if (!sidebar.classList.contains('closed')) {
        toggleMenu();
      }
    }
  });
});