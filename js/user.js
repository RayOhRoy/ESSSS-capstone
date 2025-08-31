// user.js

// Keeps track of loaded external scripts
const loadedScripts = new Set();

function initAdminPage() {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  // Load the initial dashboard page with callback
  loadAdminPage('user_dashboard.php', () => {
    initUserMenuDropdown();
    loadExternalScripts();
  });

  // Universal listener for navigation clicks (delegation)
  document.addEventListener('click', (e) => {
    const target = e.target.closest('[data-page]');
    if (!target) return;

    e.preventDefault();
    const page = target.getAttribute('data-page');
    if (!page) return;

    // Load page, run initUserMenuDropdown only for dashboard or specific pages if needed
    if (page === 'user_dashboard.php') {
      loadAdminPage(page, () => {
        initUserMenuDropdown();
        loadExternalScripts();
      });
    } else {
      loadAdminPage(page, () => {
        initUserMenuDropdown();
        loadExternalScripts();
      });
    }

    // Update sidebar active highlight
    updateSidebarActive(target, page);
  });

  // Setup user menu dropdown once globally
  setupUserMenuDelegation();
}
function loadAdminPage(page, callback) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  fetch(page)
    .then(res => {
      if (!res.ok) throw new Error(`Failed to load ${page}: ${res.status}`);
      return res.text();
    })
    .then(html => {
      contentArea.innerHTML = html;

      initUserMenuDropdown(); // Always rebind dropdown

      // Load scripts and call specific init function depending on the page
      switch (page) {
        case 'user_dashboard.php':
          loadScript('js/admin_upload.js');
          loadScript('js/admin_userlist.js');
          loadScript('js/admin_projectlist.js');
          break;

        case 'profile.php':
          loadScript('js/profile.js', 'initProfilePage');
          break;

        // Add other cases as needed
        default:
          loadExternalScripts(); // fallback (if you want shared scripts)
          break;
      }

      if (typeof callback === 'function') callback();
    })
    .catch(err => {
      console.error('Failed to load admin page:', err);
      contentArea.innerHTML = `<p style="color:red;">Failed to load: ${page}</p>`;
    });
}



function runInlineScripts(container) {
  // Find inline script tags in the loaded content and evaluate them
  const scripts = container.querySelectorAll('script:not([src])');
  scripts.forEach(script => {
    try {
      // Using Function constructor instead of eval for better scope control
      const fn = new Function(script.textContent);
      fn();
    } catch (e) {
      console.error('Error running inline script:', e);
    }
  });
}

function loadExternalScripts() {
  // List your external JS files here with their init function names
  const scriptsToLoad = [
    { src: 'js/admin_upload.js', initFn: null },
    { src: 'js/admin_userlist.js', initFn: null },
    { src: 'js/admin_projectlist.js', initFn: null },
    { src: 'js/profile.js', initFn: 'initProfilePage' }  // ✅ THIS is the fix
  ];

  scriptsToLoad.forEach(({ src, initFn }) => {
    loadScript(src, initFn);
  });
}


function loadScript(src, initFunctionName) {
  if (document.querySelector(`script[src="${src}"]`)) {
    // Script already exists
    if (typeof window[initFunctionName] === 'function') {
      window[initFunctionName]();  // Just call it again
    }
    return;
  }

  const script = document.createElement('script');
  script.src = src;
  script.onload = () => {
    if (typeof window[initFunctionName] === 'function') {
      window[initFunctionName]();
    } else {
      console.warn(`Function ${initFunctionName} is not defined after loading ${src}`);
    }
  };
  document.body.appendChild(script);
}

function updateSidebarActive(target, page) {
  // Remove all active highlights first
  document.querySelectorAll('.menu-item.active').forEach(el => el.classList.remove('active'));

  // Highlight clicked sidebar menu item if applicable
  if (target.classList.contains('menu-item')) {
    target.classList.add('active');
  } else {
    // If clicked outside sidebar, highlight menu with matching data-page
    const matchingMenu = document.querySelector(`.menu-item[data-page="${page}"]`);
    if (matchingMenu) matchingMenu.classList.add('active');
  }
}

// User menu dropdown setup with event delegation and cleanup
function initUserMenuDropdown() {
  const userIcon = document.getElementById("user-circle-icon");
  const userMenu = document.getElementById("user-menu");

  if (!userIcon || !userMenu) return;

  // Remove old listeners by cloning the icon element
  const newUserIcon = userIcon.cloneNode(true);
  userIcon.parentNode.replaceChild(newUserIcon, userIcon);

  newUserIcon.addEventListener("click", (event) => {
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

// Setup user menu dropdown with global delegation, prevents duplicates
function setupUserMenuDelegation() {
  if (window.__userMenuDelegated) return;
  window.__userMenuDelegated = true;

  document.addEventListener('click', (e) => {
    const menu = document.getElementById('user-menu');
    if (!menu) return;

    const clickedIcon = e.target.closest('#user-circle-icon');

    if (clickedIcon) {
      e.preventDefault();
      e.stopPropagation();
      menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
      return;
    }

    if (!e.target.closest('#user-menu')) {
      menu.style.display = 'none';
    }
  });
}

// Expose the init function globally for the app to call on startup
window.initAdminPage = initAdminPage;
