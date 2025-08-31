<?php
session_start();
include 'server/server.php';  // your database connection

if (!isset($_SESSION['employeeid'])) {
    header('Location: login.php');
    exit();
}

$employeeid = $_SESSION['employeeid'];

$sql = "SELECT EmployeeID, EmpFName, EmpLName, Email, JobPosition FROM employee WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employeeid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Employee not found.";
    exit;
}

$employee = $result->fetch_assoc();
?>
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
.profile-container {
  display: flex;
  flex-direction: column;
  margin: 2vh auto;    /* auto horizontal margin centers horizontally */
  padding: 0;
  color: #7B0302;
  font-family: 'Segoe UI', sans-serif;
  margin-top: 10%;   /* if you want some vertical spacing from top */
  max-width: 50%;    /* optional: limit width so it doesn't stretch too wide */
}



.profile-title-underline {
  width: 100%;
  height: 2px;
  background-color: #7B0302;
  margin-bottom: 2vh;
}


.profile-content {
  display: flex;
  align-items: flex-start;
  gap: 3vw;
}

.profile-pic-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 1vh;
}

.profile-pic {
  width: 350px;
  height: 350px;
  border-radius: 50%;
  border: 10px solid #7B0302;
  background-color: #fff;
  object-fit: cover;
  cursor: pointer;
  transition: transform 0.3s ease;
  margin-left:150px;
}

.profile-pic:hover {
  transform: scale(1.05);
}

.upload-btn {
  margin-top: 1vh;
  font-weight: bold;
  font-size: 0.95rem;
  color: #7B0302;
  text-decoration: underline;
  cursor: pointer;
   margin-left:150px;
}

.upload-btn:hover {
  color: #a00000;
}


.profile-details {
  display: flex;
  flex-direction: column;
  width: 45%;
  margin-left:150px;
}

.profile-details label {
  font-weight: bold;
  font-size: 1rem;
  margin-bottom: 0.3vh;
  color: #7B0302;
}

.profile-details input {
  width: 100%;
  padding: 1.2vh 1vw;
  border-radius: 5px;
  border: none;
  background-color: #eaeaea;
  color: #7B0302;
  font-size: 1rem;
  margin-bottom: 1.5vh;
  cursor: default;
}


.edit-profile-link {
  margin-top: 0.5vh;
  align-self: flex-end;
  font-weight: bold;
  font-size: 0.95rem;
  color: #7B0302;
  text-decoration: underline;
  cursor: pointer;
}

.edit-profile-link:hover {
  color: #a00000;
}

/* Modal Overlay */
#changePasswordModal {
  display: none;
  position: fixed;
  top: 0; 
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* Modal Content Box */
#changePasswordModal > div {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
}

/* Modal Heading */
#changePasswordModal h3 {
  color: #7B0302;
  margin-bottom: 1rem;
}

/* Form Labels */
#changePasswordModal label {
  font-weight: bold;
  color: #7B0302;
}

/* Form Inputs */
#changePasswordModal input[type="password"] {
  width: 100%;
  padding: 0.5rem;
  margin-top: 0.25rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

/* Buttons */
#changePasswordModal button {
  padding: 0.5rem 1rem;
  cursor: pointer;
  border-radius: 4px;
  border: none;
  font-weight: bold;
  font-size: 1rem;
}

#changePasswordModal button[type="submit"] {
  background-color: #7B0302;
  color: white;
}

#changePasswordModal button[type="button"] {
  background-color: #ccc;
  margin-left: 1rem;
  color: #333;
}

#changePasswordModal button[type="button"]:hover {
  background-color: #a00000;
  color: white;
}

#changePasswordModal button[type="submit"]:hover {
  background-color: #a00000;
}

@media (max-width: 900px) {
  .profile-content {
    flex-direction: column;
    align-items: center;
  }
  .profile-details {
    width: 90%;
    margin-top: 2vh;
  }
}
 
  </style>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Profile</span>
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
</div>

<hr class="top-line" />
<div class="profile-container">


  <div class="profile-content">
    


<div class="profile-details">
  <label>Employee ID</label>
  <input type="text" value="<?php echo htmlspecialchars($employee['EmployeeID']); ?>" readonly>

  <label>First Name</label>
  <input type="text" value="<?php echo htmlspecialchars($employee['EmpFName']); ?>" readonly>

  <label>Last Name</label>
  <input type="text" value="<?php echo htmlspecialchars($employee['EmpLName']); ?>" readonly>

  <label>Email</label>
  <input type="text" value="<?php echo htmlspecialchars($employee['Email']); ?>" readonly>

  <label>Position</label>
  <input type="text" value="<?php echo htmlspecialchars($employee['JobPosition']); ?>" readonly>

  <!-- Removed password for security -->

  <a id="change-pass-btn" class="edit-profile-link">Change Password</a>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal">
  <div>
    <h3 >Change Password</h3>
    <form id="changePasswordForm" method="post" action="model/change_password.php">
      <label for="current_password">Current Password</label><br/>
      <input type="password" id="current_password" name="current_password" required><br/><br/>

      <label for="new_password">New Password</label><br/>
      <input type="password" id="new_password" name="new_password" required><br/><br/>

      <label for="confirm_password">Confirm New Password</label><br/>
      <input type="password" id="confirm_password" name="confirm_password" required><br/><br/>

      <button type="submit">Proceed</button>
      <button type="button" id="cancelChangePassword" >Cancel</button>
    </form>
  </div>
</div>

  </div>
</div>

<!-- <script>

const uploadInput = document.getElementById("uploadProfile");
const profileImage = document.getElementById("profileImage");

uploadInput.addEventListener("change", function() {
  console.log("File input changed");  // <-- Add this for debugging
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      profileImage.src = e.target.result;
      console.log("Image src updated");  // <-- Also for debugging
    }
    reader.readAsDataURL(file);
  }
});
</script> -->