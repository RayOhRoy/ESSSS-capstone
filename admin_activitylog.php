
  <style>
  input {
      width: 100%;
      padding: 1.2vh 1vw;
      border-radius: 0.26vw; 
      border: 1px solid #ccc;
      color: #7B0302;
      background-color: #f5f5f5;
      margin-bottom: 1.5vh;
  }

  #user-circle-icon:hover,
  #notification-circle-icon:hover {
    filter: brightness(1.25); 
    transform: scale(1.05);
    transition: filter 0.2s ease; 
  }

  .dropdown {
    position: relative;
    display: inline-block;
  }

  .dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    border-radius: 5px;
    min-width: 6%;
    z-index: 1000;
    margin-top: 7%;
    margin-right: 1.75%;
    text-align: center;
  }

  .dropdown-menu a {
    display: block;
    padding: 7.5% 12%;
    color: #7B0302;
    text-decoration: none;
    font-size: 0.8cqw;

  }

 .dropdown-menu a:first-child:hover {
    background-color: #7B0302;
    color: white;
    border-radius: 8px 8px 0 0;
  }

  .dropdown-menu a:last-child:hover {
    background-color: #7B0302;
    color: white;
    border-radius: 0 0 8px 8px;
  }

  .dropdown-menu a:not(:first-child):not(:last-child):hover {
    background-color: #7B0302;
    color: white;
  }
    .main-content {
      margin-left: 220px; 
      padding: 30px;
    }

    h2 {
      color: #7a0e0e;
      font-weight: bold;
      border-bottom: 2px solid #7a0e0e;
      padding-bottom: 5px;
      margin-bottom: 20px;
    }

    .log-header,
    .log-entry {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
    }

    .column-title {
      flex: 1;
      padding: 12px;
      background: #fff;
      text-align: center;
      border-radius: 10px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.2);
      font-weight: bold;
    }

    .log-entry .cell {
      flex: 1;
      background-color: #ebdcdc;
      border-radius: 6px;
      padding: 14px;
      text-align: center;
      font-size: 14px;
    }

    .emp-name {
      color: #7a0e0e;
      font-weight: bold;
    }

    .emp-role {
      font-size: 12px;
      color: #444;
    }

 
  </style>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Activity Log</span>
  <div class="topbar-content">
     <div class="search-bar">
      <input type="text" placeholder="Search Project" />
    </div>
    <div class="icons">
      <span  id="notification-circle-icon" class="fa fa-bell-o" style="font-size: 1.75cqw; color: #7B0302;"></span>
      <span id="user-circle-icon" class="fa fa-user-circle" style="font-size: 2.25cqw; color: #7B0302;"></span>
      <div class="dropdown-menu" id="user-menu">
        <a href="profile.php">Profile</a>
        <a href="model/logout.php">Sign Out</a>
      </div>
    </div>
  </div>
</div>
</div>

<hr class="top-line" />

    <div class="log-header">
      <div class="column-title">Employee</div>
      <div class="column-title">File ID</div>
      <div class="column-title">Action</div>
      <div class="column-title">Timestamp</div>
    </div>

    <div class="log-entry">
      <div class="cell">
        <div class="emp-name">EMPLOYEE 1</div>
        <div class="emp-role">Secretary</div>
      </div>
      <div class="cell">TD-F3N-HG-001</div>
      <div class="cell">RETRIEVE</div>
      <div class="cell">25 APR 2025 20:03</div>
    </div>
  </div>