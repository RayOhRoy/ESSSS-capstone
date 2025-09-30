<link rel="stylesheet" href="css/admin.css">

<div class="container">
    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="sidebar">
        <img class="side-logo" src="picture/logoOutlined.png" alt="Logo">
        <div class="menu-icons">
            <a href="#" class="menu-item active" data-page="admin_dashboard.php">
                <span class="fa fa-home" alt="Dashboard"></span>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" data-page="search.php">
                <span class="fa fa-qrcode" alt="Search"></span>
                <span>Search</span>
            </a>
            <a href="#" class="menu-item" data-page="upload.php">
                <span class="fa fa-upload" alt="Upload"></span>
                <span>Upload</span>
            </a>
            <a href="#" class="menu-item" data-page="documents.php">
                <span class="fa fa-list" alt="Digital Documents"></span>
                <span>Digital Documents</span>
            </a>
            <a href="#" class="menu-item" data-page="physical_storage.php">
                <span class="fa fa-database" alt="Physical Documents"></span>
                <span>Physical Documents</span>
            </a>
            <a href="#" class="menu-item" data-page="user_list.php">
                <span class="fa fa-users" alt="User List"></span>
                <span>User List</span>
            </a>
            <a href="#" class="menu-item" data-page="activity_log.php">
                <span class="fa fa-clock-o" alt="Activity Log"></span>
                <span>Activity Log</span>
            </a>
        </div>
    </div>

    <div class="main" id="content-area"></div>

</div>