<style>
    #sidetoggle {
        font-size: 1.5cqw;
        position: absolute;
        margin-left: 10cqw;
        cursor: pointer;
        transition: filter 0.3s ease;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 300px;
        height: 100vh;
        background: #7B0302;
        color: white;
        padding: 30px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        overflow-y: auto;
        z-index: 1000;
        transition: left 0.3s ease;
    }

    .sidebar.closed {
        display: none;
        transition: transform 0.3s ease;
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
        width: 3cqw;
        /* reduce the logo size */
        margin-left: 3.75cqw;
        /* move it a bit to the right */
        transition: all 0.3s ease;
    }

    .sidebar.collapsed #sidetoggle {
        width: 3cqw;
        /* reduce the logo size */
        margin-left: 5.5cqw;
        /* move it a bit to the right */
        font-size: 0.75cqw;
        margin-top: -1cqw;
        transition: all 0.3s ease;
    }

    .side-logo {
        width: 100%;
        max-width: 10cqw;

    }


    .hamburger {
        gap: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        position: absolute;
        left: 12%;
        top: 2%;
        transition: transform 0.3s ease;
        z-index: 2000;
        color: blue;
        padding: 10px 5px 10px 5px;
    }


    .hamburger span {
        display: block;
        height: 3px;
        background-color: #ffffffff;
        border-radius: 2px;
        width: 45px;
        height: 5px;
    }

    .hamburger.closed span {
        background-color: #7B0302;
    }

    .hamburger.closed {
        top: 0;
        left: 0;
        transition: transform 0.3s ease;
        padding: 20px 5px 10px 15px;

    }

    .sidebar.closed .menu-item a {
        /* display:none; */
        background-color: black;
    }

    @media (max-width: 1080px) {
        .side-logo {
            max-width: 60cqw;

        }

        .hamburger {
            left: 68%;
            position: fixed;
        }

        .hamburger span {
            width: 90px;
            height: 10px;
        }

        .sidebar {
            width: 80%;
            height: 100%
        }
    }
</style>

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