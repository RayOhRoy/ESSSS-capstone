window.onload = () => {
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
      attachForgotPasswordHandler();
      attachRegisterHandler();

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
  const otpModal = document.getElementById('otp-modal');
  const otpForm = document.getElementById('otp-verify-form');
  const closeOtpModal = document.getElementById('otp-modal-close');
  const emailInput = document.querySelector('.Email');

  if (!forgotForm) return;

  // Step 1: Send OTP to email
  forgotForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const data = new FormData(forgotForm);

    fetch('model/forgot_password_processing.php', {
      method: 'POST',
      body: data
    })
      .then(res => res.json())
      .then(result => {
        const errorBox = document.getElementById('forgot-error') || createErrorBox(forgotForm, 'forgot-error');

        if (result.success) {
          errorBox.style.color = 'green';
          errorBox.textContent = result.message;

          // Disable the email input and button
          emailInput.disabled = true;
          forgotForm.querySelector('button').disabled = true;

          // Show OTP modal
          if (otpModal) otpModal.style.display = 'block';
        } else {
          errorBox.style.color = 'red';
          errorBox.textContent = result.message;
        }
      })
      .catch(error => {
        console.error('Forgot password error:', error);
      });
  });

  // Step 2: Verify OTP and Reset Password
  if (otpForm) {
    otpForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(otpForm);
      formData.append('email', emailInput.value); // Reuse original email

      fetch('model/verify_otp.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(result => {
          const errorBox = document.getElementById('otp-error') || createErrorBox(otpForm, 'otp-error');

          if (result.success) {
            errorBox.style.color = 'green';
            errorBox.textContent = result.message;

            alert('Password reset successful! You can now log in.');

            // ✅ Auto-hide modal after success
            setTimeout(() => {
              otpModal.style.display = 'none';
              window.location.href = 'index.php';
            }, 2000); // 2-second delay
          }
          else {
            errorBox.style.color = 'red';
            errorBox.textContent = result.message;
          }
        })
        .catch(error => {
          console.error('OTP verification error:', error);
        });
    });
  }

  // Close modal when clicking the close button (×)
  if (closeOtpModal) {
    closeOtpModal.addEventListener('click', function () {
      otpModal.style.display = 'none';
    });
  }

  // Helper function to create reusable error box
  function createErrorBox(form, id) {
    let div = document.createElement('div');
    div.id = id;
    div.style.color = 'red';
    div.style.marginTop = '1rem';
    form.appendChild(div);
    return div;
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