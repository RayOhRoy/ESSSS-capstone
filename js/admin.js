function initAdminPage() {
  const contentArea = document.getElementById('content-area');
  if (contentArea) {
    loadAdminPage('admin_dashboard.php', initUserMenuDropdown, initTestModal); // Run dropdown init after load
    loadScript('js/admin_upload.js');
    loadScript('js/admin_userlist.js');
    loadScript('js/admin_projectlist.js');
  }

  // Universal listener for any element with data-page
document.addEventListener('click', function (e) {
  const target = e.target.closest('[data-page]');
  if (!target) return;

  e.preventDefault();
  const page = target.getAttribute('data-page');

  if (page) {
    if (page === 'admin_dashboard.php') {
      loadAdminPage(page, initUserMenuDropdown, initTestModal);
    } else {
      loadAdminPage(page);
    }
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

function loadAdminPage(page, callback) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  // ✅ Automatically highlight the correct menu item before loading
  document.querySelectorAll(".menu-item").forEach(i => i.classList.remove("active"));
  const targetMenu = document.querySelector(`.menu-item[data-page="${page}"]`);
  if (targetMenu) targetMenu.classList.add("active");

  // Load the page
  fetch(page)
    .then(res => {
      if (!res.ok) throw new Error(`Failed to load ${page}: ${res.status}`);
      return res.text();
    })
    .then(html => {
      contentArea.innerHTML = html;

      // Rebind dropdown each time
      initUserMenuDropdown();
      initTestModal();

      // ✅ Run callback after page loads
      if (typeof callback === "function") callback();
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

function initTestModal() {
  const modal = document.getElementById('previewModal');
  const modalBody = document.getElementById('modalBody');
  const closeBtn = document.getElementById('closeModal');

  if (!modal || !modalBody || !closeBtn) return;

  // Remove old listeners by cloning elements (prevents duplicates)
  const newCloseBtn = closeBtn.cloneNode(true);
  closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);

  document.querySelectorAll('.test-btn').forEach(button => {
    // Remove previous listeners by cloning node
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);

    newButton.addEventListener('click', () => {
      modalBody.innerHTML = `<p>Test modal content loaded!</p>`;
      modal.style.display = 'block';
    });
  });

  // Close modal when clicking "×"
  newCloseBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Close modal when clicking outside content
  document.addEventListener('click', (e) => {
    if (!modal.contains(e.target) && e.target.classList.contains('test-btn')) return;
    if (e.target === modal) modal.style.display = 'none';
  });
}

document.getElementById('sidetoggle').addEventListener('click', function() {
  const sidebar = document.querySelector('.sidebar');
  sidebar.classList.toggle('collapsed');
});

// Fire-and-forget dropdown handler (added once)
(function setupUserMenuDelegation() {
  if (window.__userMenuDelegated) return; // prevent duplicates
  window.__userMenuDelegated = true;

  document.addEventListener('click', function (e) {
    const menu = document.getElementById('user-menu');
    if (!menu) return; // nothing to do if current page has no menu

    const clickedIcon = e.target.closest('#user-circle-icon');

    if (clickedIcon) {
      e.preventDefault();
      e.stopPropagation();
      menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
      return;
    }

    // Clicked outside the menu? close it.
    if (!e.target.closest('#user-menu')) {
      menu.style.display = 'none';
    }
  });
})();
