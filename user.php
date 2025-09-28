<link rel="stylesheet" href="css/user.css">

<div class="container">
    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="sidebar">
        <img class="side-logo" src="picture/logoOutlined.png" alt="Logo">

        <div class="menu-icons">
            <a href="#" class="menu-item active" data-page="user_dashboard.php">
                <span class="fa fa-home" alt="Dashboard"></span>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" data-page="upload.php">
                <span class="fa fa-upload" alt="Upload"></span>
                <span>Upload</span>
            </a>
            <a href="#" class="menu-item" data-page="documents.php">
                <span class="fa fa-list" alt="Documents"></span>
                <span>Documents</span>
            </a>
            <a href="#" class="menu-item" data-page="physical_storage.php">
                <span class="fa fa-database" alt="Physical Storage"></span>
                <span>Physical Storage</span>
            </a>
            <a href="#" class="menu-item" data-page="search.php">
                <span class="fa fa-qrcode" alt="Search"></span>
                <span>Search</span>
            </a>
        </div>

    </div>

    <div class="main" id="content-area"></div>

</div>