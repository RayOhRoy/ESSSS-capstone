<link rel="stylesheet" href="css/user.css">

<div class="container">
  <div class="hamburger" id="hamburger" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
  </div>

  <div class="sidebar">
      <img class="side-logo" src="picture/logoOutlined.png" alt="Logo"  data-page="user_dashboard.php">

      <div class="menu-icons">
          <?php
          session_start();
          $jobPosition = strtolower($_SESSION['jobposition'] ?? '');

          $menuItems = [
              ['page' => 'user_dashboard.php', 'icon' => 'fa-home', 'label' => 'Dashboard'],
              ['page' => 'search.php', 'icon' => 'fa-qrcode', 'label' => 'Search'],
              ['page' => 'upload.php', 'icon' => 'fa-upload', 'label' => 'Upload'],
              ['page' => 'documents.php', 'icon' => 'fa-list', 'label' => 'Digital Documents'],
              ['page' => 'physical_storage.php', 'icon' => 'fa-database', 'label' => 'Physical Documents'],
              ['page' => 'activity_log.php', 'icon' => 'fa-clock-o', 'label' => 'Activity Log']
          ];

          if ($jobPosition === 'cad operator' || $jobPosition === 'compliance officer') {
              $menuItems = array_filter($menuItems, function($item) {
                  return !in_array($item['page'], ['upload.php', 'activity_log.php']);
              });
          }

          foreach ($menuItems as $item) {
              echo '<a href="#" class="menu-item' . 
                   ($item['page'] === 'user_dashboard.php' ? ' active' : '') . 
                   '" data-page="' . htmlspecialchars($item['page']) . '">
                        <span class="fa ' . htmlspecialchars($item['icon']) . '" alt="' . htmlspecialchars($item['label']) . '"></span>
                        <span>' . htmlspecialchars($item['label']) . '</span>
                    </a>';
          }
          ?>
      </div>
  </div>

  <div class="main" id="content-area"></div>
</div>
