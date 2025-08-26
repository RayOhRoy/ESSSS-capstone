function loadAdminPage(page) {
  const contentArea = document.getElementById('content-area');
  if (!contentArea) return;

  fetch(page)
    .then(res => res.text())
    .then(html => {
      contentArea.innerHTML = html;

      // Initialize user list handlers if on user list page
      if (page === 'qr_search.php') {
        initQRSearch();
      }
      // You can add other page-specific init calls here
    })
    .catch(err => {
      console.error('Failed to load admin page:', err);
    });
}

let qrSearchInitialized = false;

function initQRSearch() {
  if (qrSearchInitialized) return;
  qrSearchInitialized = true;

  console.log("Preview modal handlers initialized with event delegation");

  const body = document.body;
  const modal = document.getElementById('testModal');
  const closeBtn = document.getElementById('closeTestModal'); // get close button
  const openBtn = document.getElementById('qr-search-btn'); // no '#'

  if (!modal || !closeBtn || !openBtn) {
    console.warn("Required modal elements not found");
    return;
  }

  body.addEventListener('click', function (e) {
    // Open modal on clicking qr-search-btn
    if (e.target === openBtn) {
      e.preventDefault();
      modal.style.display = 'flex';  // or 'block' based on your CSS
      return;
    }

    // Close modal on clicking close button or clicking outside modal content (modal background)
    if (e.target === closeBtn || e.target === modal) {
      modal.style.display = 'none';
      return;
    }
  });
}
