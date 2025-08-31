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
.userlist-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 5%;
    margin-top: 5%;
    margin-left: 10%;
    margin-right: 10%;
    margin-bottom: 1%;
}

.user-card {
    background: #7B0302;
    padding: 1cqw;
    border-radius: 1cqw;
    box-shadow: 0.25cqw 0.25cqw 1cqw rgba(0, 0, 0, 0.3);
    height: 17cqw;
    margin-bottom: 5%;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center; 
    align-items: center;    
    text-align: center;
    position: relative;
}

.user-status {
    position: absolute;
    top: 1cqw;
    left: 1cqw;
    font-size: 0.75cqw;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.3cqw 0.8cqw;
    border-radius: 0.5cqw;
    color: white;
}

.status-active {
    background-color: #00830F;
}

.status-inactive {
    background-color: black;
}

.user-name {
    font-size: 1cqw;
    font-weight: 600;
    margin: 0.3cqw 0;
}

.user-position {
    font-size: 0.75cqw;
    margin: 0.5cqw 0;
}

.iconEllipsis {
    position: absolute;
    top: 1.5cqw;
    left: 12cqw;
    font-size: 1cqw;
    cursor: pointer;
}

#iconUL {
    font-size: 3cqw;
    margin-bottom: 0.5cqw;
}

.status-dropdown {
    position: absolute;
    top: 3cqw;
    left: 11cqw;
    background: white;
    color: black;
    border-radius: 0.5cqw;
    box-shadow: 0 0.5cqw 1cqw rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
    z-index: 10;    
}

.status-option {
    padding: 0.5cqw 1cqw;
    cursor: pointer;
}

.status-option:first-child {
    border-top-left-radius: 0.5cqw;
    border-top-right-radius: 0.5cqw;
}

.status-option:last-child {
    border-bottom-left-radius: 0.5cqw;
    border-bottom-right-radius: 0.5cqw;
}

.status-option:hover {
    background-color: #eee;
    border-radius: 0.5cqw; 
}

.floating-add-user {
    position: fixed;
    bottom: 50px;
    right: 50px; 
    background-color: #7a0000;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    z-index: 50;
    transition: transform 0.3s ease;
}

.floating-add-user:hover {
  transform: scale(1.2); /* increase size by 20% */
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(0.1cqw);
    -webkit-backdrop-filter: blur(0.1cqw);
}

.modal-content {
    margin: 3cqw auto;
    max-width: 50cqw;
    max-height: 35cqw;
    border-radius: 1cqw !important;
    border: none;
}

label {
    color: #7B0302;
}

input[disabled] {
  background-color: #e0e0e0;
  border: 1px solid #ccc;
  color: #444;
  font-weight: 600;
}

.form-section input {
border-radius: 0.26vw; 
      border: 1px solid #ccc;
      background-color: white;
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

button {
  width: 100%;
  padding: 1.11vh 0.83vw;
  background-color: #7B0302;
  color: #fff;
  border: none;
  border-radius: 0.26vw;
  cursor: pointer;
  margin-top: 2vh;
}

</style>

<?php
include 'server/server.php';

$nextEmployeeId = 'USR0001';
$sql = "SELECT employeeid FROM employee WHERE employeeid LIKE 'USR%' ORDER BY employeeid DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $lastId = intval(substr($row['employeeid'], 3)); // remove 'USR' and get number
    $nextId = $lastId + 1;
    $nextEmployeeId = 'USR' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}
?>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">User List</span>
  <div class="topbar-content">
     <div class="search-bar">
      <input type="text" placeholder="Search Project" />
    </div>
    <div class="icons">
      <span  id="notification-circle-icon" class="fa fa-bell-o" style="font-size: 1.75cqw; color: #7B0302;"></span>
      <span id="user-circle-icon" class="fa fa-user-circle" style="font-size: 2.25cqw; color: #7B0302;"></span>
      <div class="dropdown-menu" id="user-menu">
        <a data-page="profile.php">Profile</a>
        <a href="model/logout.php">Sign Out</a>
      </div>
    </div>
  </div>
</div>

<hr class="top-line" />

<div class="floating-add-user" id="add-account-btn" data-next-id="<?= $nextEmployeeId ?>">
  <span class="fa fa-plus" style="font-size: 1.5cqw; color: white;"></span>
</div>

<div id="modalAddUser" class="modal">
  <div class="modal-content" style="max-width: 20cqw; margin-top: 10cqw; padding: 2cqw; background: white; border-radius: 1cqw;">
    <span class="close" style="cursor:pointer; font-size: 2cqw; position: relative; top: -2cqw; left: 19rem; color: #7B0302;">&times;</span>
    <div class="form-section" style="display: flex; gap: 3cqw;">
      <div style="flex: 1;">
        <form id="adduser-form" action="model/register_processing.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5cqw;">

          <div style="display: flex; align-items: flex-start; gap: 0cqw;">
            <label for="employeeid_display" style="min-width: 6cqw; font-weight: 700; margin-top: 0.3cqw;">EMPLOYEE ID:</label>
            <input type="text" id="employeeid_display" value="<?= $nextEmployeeId ?>" disabled style="flex: 1;" />
            <input type="hidden" id="employeeid" name="employeeid" value="<?= $nextEmployeeId ?>" />
          </div>

          <div style="display: flex; align-items: flex-start; gap: 0cqw;">
            <label for="first_name" style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">First Name:</label>
            <input type="text" id="first_name" name="first_name" required class="adduser-firstname" style="flex: 1;" />
          </div>

          <div style="display: flex; align-items: flex-start; gap: 0cqw;">
            <label for="last_name" style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required class="adduser-lastname" style="flex: 1;" />
          </div>

          <div style="display: flex; align-items: flex-start; gap: 0cqw;">
            <label for="email" style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">Email:</label>
            <input type="email" id="email" name="email" required style="flex: 1;" />
          </div>

          <div style="display: flex; align-items: flex-start; gap: 0cqw;">
            <label for="position" style="min-width: 6cqw; font-weight: 600; margin-top: 0.3cqw;">Position:</label>
            <select id="position" name="position" required style="flex: 1; max-width: 16cqw;">
              <option value="" disabled selected>Select Position</option>
              <option value="Secretary">Secretary</option>
              <option value="Compliance Officer">Compliance Officer</option>
              <option value="CAD Operator">CAD Operator</option>
            </select>
          </div>

          <button id="signup-button" type="submit" style="margin-left: 4cqw; max-width: 8cqw;">Add Employee</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$sql = "SELECT EmployeeID, CONCAT(EmpFName, ' ', EmpLName) AS fullname, JobPosition, AccountStatus
        FROM employee
        WHERE AccountType = 'User'
        ORDER BY EmployeeID ASC";

$result = $conn->query($sql);

echo '<section id="user-section">';
echo '<div class="userlist-wrapper">';
echo '<div class="userlist-grid">';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accountstatusClass = strtolower($row['AccountStatus']) === 'active' ? 'status-active' : 'status-inactive';

        echo '<div class="user-card">';
        echo '<div class="user-status ' . $accountstatusClass . '">' . strtoupper(htmlspecialchars($row['AccountStatus'])) . '</div>';
        echo '<div class="fa fa-ellipsis-h iconEllipsis" data-id="' . $row['EmployeeID'] . '"></div>';
        echo '<div class="status-dropdown" id="dropdown-' . $row['EmployeeID'] . '">
                <div class="status-option" data-id="' . $row['EmployeeID'] . '" data-status="Active">Active</div>
                <div class="status-option" data-id="' . $row['EmployeeID'] . '" data-status="Inactive">Deactivate</div>
                <div class="status-option" data-id="' . $row['EmployeeID'] . '" data-status="Delete">Delete</div>
              </div>';
        echo '<div class="fa fa-user-circle" id="iconUL"></div>';
        echo '<div class="user-name">' . htmlspecialchars($row['fullname']) . '</div>';
        echo '<div class="user-position">' . htmlspecialchars($row['JobPosition']) . '</div>';
        echo '<div class="user-id">' . htmlspecialchars($row['EmployeeID']) . '</div>';
        echo '</div>';
    }
}

echo '</div></div></section>';
