
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
    
    <!-- <div class="profile-pic-section">
      <img src="picture/project_qr.png" id="profileImage" class="profile-pic" alt="Profile Picture">
      <input type="file" id="uploadProfile" accept="image/*" style="display:none;">
      <label for="uploadProfile" class="upload-btn">Upload Profile</label>
    </div> -->

    <div class="profile-details">
      <label>Employee ID</label>
      <input type="text" value="USR00001" readonly>

      <label>First Name</label>
      <input type="text" value="JUAN" readonly>

      <label>Middle Name</label>
      <input type="text" value="CRUZ" readonly>

      <label>Last Name</label>
      <input type="text" value="DELA CRUZ" readonly>

      <label>Position</label>
      <input type="text" value="SECRETARY" readonly>

      <label>Password</label>
      <input type="password" value="password123" readonly>

      <a href="edit-profile.php" class="edit-profile-link">Edit Profile</a>
    </div>
  </div>
</div>

<script>

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
</script>