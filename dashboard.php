<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ES Santos Dashboard</title>
  <link rel="stylesheet" href="user_style.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
  <div class="sidebar" id="sidebar">
    <img src="picture/align.png" alt="Menu" id="toggleBtn" class="menu-btn" />
    <div class="logo">
      <img src="picture/logo.jpg" alt="Logo">
      <div class="logo-text">
        <div>ES Santos</div>
        <div>Surveying Services</div>
      </div>
    </div>
    

    <div class="menu-icons">
      <a href="#" class="menu-item active" data-page="user_dashboard.php">
        <img src="picture/home.png" alt="Dashboard">
        <span>Dashboard</span>
      </a>
      <a href="#" class="menu-item" data-page="user_upload.php">
        <img src="picture/Upload.png" alt="Upload">
        <span>Upload</span>
      </a>
      <a href="#" class="menu-item" data-page="user_projectlist.php">
        <img src="picture/List.png" alt="Project List">
        <span>Project List</span>
      </a>
      <div class="menu-item">
        <img src="picture/scanner.png" alt="QR Toggle">
        <span>QR Toggle</span>
      </div>
    </div>
  </div>

  <div class="main" id="content-area"></div>
</div>

<script>

  const toggleBtn = document.getElementById('toggleBtn');
  const sidebar = document.getElementById('sidebar');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
  });
  
  function loadPage(page) {
    $("#content-area").load(page, function () {

      if (page === "user_upload.php" && typeof populateDocumentTable === "function") {
        populateDocumentTable(); 
      }

     
      if (page === "user_upload.php") {
        const checkExist = setInterval(() => {
          if (typeof populateDocumentTable === "function") {
            populateDocumentTable();
            clearInterval(checkExist);
          }
        }, 100);
      }
    });

    localStorage.setItem("currentPage", page);
  }

  $(document).ready(function () {
    const savedPage = localStorage.getItem("currentPage") || "user_dashboard.php";
    loadPage(savedPage);

    $(".menu-item").removeClass("active");
    $(`.menu-item[data-page="${savedPage}"]`).addClass("active");

    $(".menu-item").click(function (e) {
      const page = $(this).attr("data-page");
      if (page) {
        e.preventDefault();
        $(".menu-item").removeClass("active");
        $(this).addClass("active");
        loadPage(page);
      }
    });
  });
</script>
</body>
</html>
