<style> 
  #sidetoggle {
    font-size: 1.5cqw;
    position: absolute;
    margin-left: 10cqw;
    cursor: pointer;
    transition: filter 0.3s ease;
  }

  #sidetoggle:hover {
    filter: brightness(0.7);
  }
  
  .sidebar.collapsed {
    width: 4.5cqw; 
    overflow: hidden;
  }

  .sidebar.collapsed .logo-text {
    display: none;
  }

  .sidebar.collapsed .menu-item span:nth-child(2) {
    display: none;
  }

  .sidebar.collapsed .logo img {
    width: 3cqw;       /* reduce the logo size */
    margin-left: 3.75cqw; /* move it a bit to the right */
    transition: all 0.3s ease;
  }

  .sidebar.collapsed #sidetoggle  {
    width: 3cqw;       /* reduce the logo size */
    margin-left: 5.5cqw; /* move it a bit to the right */
    font-size: 0.75cqw;
    margin-top: -1cqw;
    transition: all 0.3s ease;
  }

  .side-logo {
    max-width: 10cqw;
  }
</style>

<div class="container">
  <div class="sidebar">
      <img class="side-logo" src="picture/logoOutlined.png" alt="Logo">

    <div class="menu-icons">
      <a href="#" class="menu-item active" data-page="admin_dashboard.php">
        <span  class="fa fa-home" alt="Dashboard"></span>
        <span>Dashboard</span>
      </a>
      <a href="#" class="menu-item" data-page="upload.php">
        <span  class="fa fa-upload" alt="Upload"></span>
        <span>Upload</span>
      </a>
      <a href="#" class="menu-item" data-page="documents.php">
        <span  class="fa fa-list" alt="Documents"></span>
        <span>Documents</span>
      </a>
      <a href="#" class="menu-item" data-page="physical_storage.php">
        <span  class="fa fa-database" alt="Physical Storage"></span>
        <span>Physical Storage</span>
      </a>
      <a href="#" class="menu-item" data-page="search.php">
        <span  class="fa fa-qrcode" alt="Search"></span>
        <span>Search</span>
      </a>
      <a href="#" class="menu-item" data-page="user_list.php">
        <span  class="fa fa-users" alt="User List"></span>
        <span>User List</span>
      </a>
      <a href="#" class="menu-item" data-page="activity_log.php">
        <span  class="fa fa-clock-o" alt="Activity Log"></span>
        <span>Activity Log</span>
      </a>
    </div>
    
  </div>

  <div class="main" id="content-area"></div>
  
</div>
