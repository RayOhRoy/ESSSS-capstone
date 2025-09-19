window.onload = () => {
  // ✅ Clear only localStorage (keep PHP sessions untouched)
  localStorage.clear();

  console.log("SESSION_ROLE:", SESSION_ROLE);
  console.log("SESSION_ID:", SESSION_ID);
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
  // ✅ Add cache-busting query parameter to fetch
  fetch(`${path}?_=${Date.now()}`)
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
      attachForgotPasswordHandler();
      attachRegisterHandler();
      attachPasswordToggle();

      const adminPages = [
        'admin.php',
        'activity_log.php',
        'user_list',
        'admin_dashboard.php'
      ];

      const userPages = [
        'user.php',
        'user_dashboard.php'
      ];

      if (adminPages.includes(path)) {
        loadCSS('css/admin_style.css');

        if (path === 'userlist.php') {
          loadScript('js/user_list.js');
        } else {
          loadScript('js/admin.js');
        }
      }

      if (userPages.includes(path)) {
        loadCSS('css/admin_style.css');

        if (path === 'project_list.php') {
          loadScript('js/project_list.js');
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

function attachPasswordToggle() {
  const passwordInput = document.getElementById('password');
  const toggleButton = document.getElementById('togglePassword');
  const toggleIcon = document.getElementById('toggleIcon');

  if (!passwordInput || !toggleButton || !toggleIcon) return;

  toggleButton.addEventListener('click', function () {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';

    toggleIcon.classList.toggle('fa-eye');
    toggleIcon.classList.toggle('fa-eye-slash');
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

function attachForgotPasswordHandler() {
  const forgotForm = document.getElementById('forgot-password-form');
  const emailInput = document.querySelector('.Email');
  const formFields = document.getElementById('form-fields');

  if (!forgotForm || !emailInput || !formFields) return;

  const handleSendOTP = function (e) {
    e.preventDefault();

    const sendBtn = forgotForm.querySelector('button[type="submit"]');
    if (sendBtn) {
      sendBtn.disabled = true;
      sendBtn.textContent = "Sending...";
    }

    const data = new FormData(forgotForm);

    fetch('model/forgot_password_processing.php', {
      method: 'POST',
      body: data
    })
      .then(res => res.json())
      .then(result => {
        const errorBox = document.getElementById('forgot-error') || createErrorBox(forgotForm, 'forgot-error');
        const errorText = errorBox.querySelector('.alert-text');

        if (result.success) {
          errorBox.style.display = 'flex';
          errorBox.style.borderColor = 'green';
          errorBox.style.color = 'green';
          errorBox.querySelector('.alert-icon i').className = 'fa fa-check-circle';
          errorText.textContent = result.message;

          formFields.innerHTML = `
          <style>
            #form-fields .inputClass {
              margin-bottom: 3%;
            }
            #form-fields .inputClass input {
              padding: 8px;
            }
          </style>

          <input type="hidden" name="email" value="${emailInput.value}" />

          <div class="inputClass">
            <input type="text" name="otp" placeholder="6-digit OTP" maxlength="6" required />
          </div>
          <div class="inputClass">
            <input type="password" name="new_password" placeholder="New Password" required />
          </div>
          <div class="inputClass">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required />
          </div>
          <div style="display: flex; justify-content: center;">
            <button class="Sendemailbtn" type="submit">Reset Password</button>
            <button type="button" id="cancel-reset" class="back-login">Cancel</button>
          </div>
        `;

          forgotForm.removeEventListener('submit', handleSendOTP);
          forgotForm.addEventListener('submit', handleVerifyOTP);

          const cancelBtn = document.getElementById('cancel-reset');
          if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
              location.reload(); // reset the form
            });
          }
        } else {
          errorBox.style.display = 'flex';
          errorBox.style.borderColor = 'red';
          errorBox.style.color = 'red';
          errorBox.querySelector('.alert-icon i').className = 'fa fa-exclamation-circle';
          errorText.textContent = result.message;

          // Re-enable button after 5 seconds
          if (sendBtn) {
            setTimeout(() => {
              sendBtn.disabled = false;
              sendBtn.textContent = "Send Email";
            }, 5000);
          }
        }
      })
      .catch(error => {
        console.error('Forgot password error:', error);

        // Re-enable button after error
        if (sendBtn) {
          setTimeout(() => {
            sendBtn.disabled = false;
            sendBtn.textContent = "Send Email";
          }, 5000);
        }
      });
  };

  // OTP Verification: Reset Password
  const handleVerifyOTP = function (e) {
    e.preventDefault();

    const data = new FormData(forgotForm);

    fetch('model/verify_otp.php', {
      method: 'POST',
      body: data
    })
      .then(res => res.json())
      .then(result => {
        const errorBox = document.getElementById('forgot-error');
        const errorText = errorBox.querySelector('.alert-text');

        if (result.success) {
          errorBox.style.display = 'flex';
          errorBox.style.borderColor = 'green';
          errorBox.style.color = 'green';
          errorBox.querySelector('.alert-icon i').className = 'fa fa-check-circle';
          errorText.textContent = result.message;

          setTimeout(() => {
            window.location.href = 'index.php'; // Go back to login
          }, 2000);
        } else {
          errorBox.style.display = 'flex';
          errorBox.style.borderColor = 'red';
          errorBox.style.color = 'red';
          errorBox.querySelector('.alert-icon i').className = 'fa fa-exclamation-circle';
          errorText.textContent = result.message;
        }
      })
      .catch(error => {
        console.error('OTP verification error:', error);
      });
  };

  // Attach only the first listener
  forgotForm.addEventListener('submit', handleSendOTP);

  function createErrorBox(form, id) {
    const alertBox = document.createElement('div');
    alertBox.className = 'alert-box';
    alertBox.id = id;
    alertBox.style.display = 'flex';

    const iconDiv = document.createElement('div');
    iconDiv.className = 'alert-icon';
    iconDiv.innerHTML = '<i class="fa fa-exclamation-circle" aria-hidden="true"></i>';

    const textDiv = document.createElement('div');
    textDiv.className = 'alert-text';

    alertBox.appendChild(iconDiv);
    alertBox.appendChild(textDiv);

    form.appendChild(alertBox);
    return alertBox;
  }
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
  // ✅ Prevent duplicate script + bust cache
  if (document.querySelector(`script[src^="${src}"]`)) return;

  const script = document.createElement('script');
  script.src = `${src}?_=${Date.now()}`;
  script.onload = () => {
    if (typeof initAdminPage === 'function') {
      initAdminPage();
    }
  };
  document.body.appendChild(script);
}

function loadCSS(href) {
  // ✅ Prevent duplicate stylesheet + bust cache
  if (document.querySelector(`link[href^="${href}"]`)) return;

  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = `${href}?_=${Date.now()}`;
  document.head.appendChild(link);
}
