window.onload = () => {
  console.log("SESSION_ROLE:", SESSION_ROLE);

  const lastPage = localStorage.getItem('lastLoadedPage');

  if (SESSION_ROLE === 'admin' || SESSION_ROLE === 'user') {
    if (lastPage) {
      console.log(`Loading last visited page: ${lastPage}`);
      loadForm(lastPage);
    } else {
      const defaultPage = SESSION_ROLE === 'admin' ? 'admin.php' : 'user.php';
      console.log(`No last page. Loading default: ${defaultPage}`);
      loadForm(defaultPage);
    }
  } else {
    console.warn("No valid session role found. Loading login page.");
    loadForm('login.php');
  }
};

function loadForm(path) {
  fetch(path)
    .then(res => {
      if (!res.ok) throw new Error(`Failed to load ${path}: ${res.status}`);
      return res.text();
    })
      .then(html => {
      const container = document.getElementById('form-content');
      container.innerHTML = html;

      console.log(`Loaded: ${path}`);
      localStorage.setItem('lastLoadedPage', path);

      attachClickHandlers();
      attachSubmitHandler();
      attachRegisterHandler();

  const adminPages = [
  'admin.php',
  'admin_activitylog.php',
  'admin_dashboard.php',
  'admin_projectlist.php',
  'admin_upload.php',
  'admin_userlist.php'
];

const userPages = [
  'user.php',
  'user_dashboard.php',
  'user_projectlist.php',
  'user_upload.php',
  'user_profile.php'
];

if (adminPages.includes(path)) {
  loadCSS('css/admin_style.css');

  if (path === 'admin_userlist.php') {
    loadScript('js/admin_userlist.js');
  } else {
    loadScript('js/admin.js');
  }
}

if (userPages.includes(path)) {
  loadCSS('css/admin_style.css');

  if (path === 'user_projectlist.php') {
    loadScript('js/user_projectlist.js');
  } else {
    loadScript('js/user.js');
  }
}

    })
    .catch(err => {
      console.error('Error loading form:', err);
      document.getElementById('form-content').innerHTML =
        `<p style="color:red;">Failed to load: ${path}</p>`;
    });
}

function attachClickHandlers() {
  const loadLinks = document.querySelectorAll('[data-load]');
  loadLinks.forEach(el => {
    el.addEventListener('click', function () {
      const file = el.getAttribute('data-load');
      loadForm(file);
    });
  });
}

function attachSubmitHandler() {
  const loginForm = document.getElementById('login-form');
  if (!loginForm) return;

  loginForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const data = new FormData(loginForm);

    fetch('model/login_processing.php', {
      method: 'POST',
      body: data
    })
      .then(res => res.json())
      .then(result => {
        const errorBox = document.getElementById('invalid-error');
        if (result.success) {
  
          loadForm(result.redirect); 
        } else {
          if (errorBox) {
            errorBox.textContent = result.message;
          } else {
            const div = document.createElement('div');
            div.id = 'invalid-error';
            div.style.color = 'red';
            div.textContent = result.message;
            loginForm.insertBefore(div, loginForm.querySelector('button'));
          }
        }
      })
      .catch(error => {
        console.error('Login error:', error);
      });
  });
}

function attachRegisterHandler() {
  const registerForm = document.getElementById('register-form');
  if (!registerForm) return;

  registerForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(registerForm);

    try {
      const response = await fetch(registerForm.action, {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        alert(result.message);
        loadForm('login.php');
      } else {
        alert(result.message);
      }
    } catch (error) {
      console.error('Registration error:', error);
    }
  });
}

function loadScript(src) {
  if (document.querySelector(`script[src="${src}"]`)) return;

  const script = document.createElement('script');
  script.src = src;
  script.onload = () => {
    if (typeof initAdminPage === 'function') {
      initAdminPage();
    }
  };
  document.body.appendChild(script);
}

function loadCSS(href) {
  if (document.querySelector(`link[href="${href}"]`)) return;

  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = href;
  document.head.appendChild(link);
}