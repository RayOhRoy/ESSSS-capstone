function initAdminPage() {
  const contentArea = document.getElementById('content-area');
  if (contentArea) {
    loadAdminPage('user_dashboard.php');
    loadScript('js/user.js');
    // loadScript('js/admin_upload.js');
    // loadScript('js/admin_userlist.js');

  }

  // Handle sidebar menu clicks
  document.querySelectorAll('.menu-item').forEach(menu => {
    menu.addEventListener('click', function (e) {
      e.preventDefault();

      const page = this.getAttribute('data-page');
      if (page) loadAdminPage(page);

      document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
      this.classList.add('active');
    });
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

      if (page === 'admin_upload.php') {
        // loadScript('js/admin_upload.js');
        // loadAdminPage('admin_upload.php')
      }
       
      else if (page === 'admin_userlist.php') {
        // loadScript('js/admin_userlist.js');
        // loadAdminPage('admin_userlist.php')
      }

      else {
        loadScript('js/admin.js');
      }
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

document.getElementById('sidetoggle').addEventListener('click', function() {
  const sidebar = document.querySelector('.sidebar');
  sidebar.classList.toggle('collapsed');
});