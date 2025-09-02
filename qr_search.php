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


.qr-search-content {
  border: 1px solid #EC221F;
  background: #FEE9E7;
  padding: 2vw;
  border-radius: 1vw;
  max-width: 600px;
  width: 90%;
  position: fixed;       /* fixed so it stays in viewport */
  top: 50%;              /* vertical center */
  left: 50%;             /* horizontal center */
  transform: translate(-50%, -50%); /* center exactly */
}

.newmodal {
  display: none; /* visible */
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;
}

.newmodal .new-modal-content {
  border: 1px solid #EC221F;
  background: #FEE9E7;
  padding: 2vw;
  border-radius: 1vw;
  max-width: 600px;
  width: 90%;
  position: fixed;       /* fixed so it stays in viewport */
  top: 50%;              /* vertical center */
  left: 50%;             /* horizontal center */
  transform: translate(-50%, -50%); /* center exactly */
  box-shadow: 0 4px 10px rgba(0,0,0,0.25);
}

.oldmodal .old-modal-content {
  border: 1px solid #EC221F;
  background: #FEE9E7;
  padding: 2vw;
  border-radius: 1vw;
  max-width: 600px;
  width: 90%;
  position: fixed;       /* fixed so it stays in viewport */
  top: 50%;              /* vertical center */
  left: 50%;             /* horizontal center */
  transform: translate(-50%, -50%); /* center exactly */
  box-shadow: 0 4px 10px rgba(0,0,0,0.25);
}

/* Close button */
.newmodal #closeqrsearchModal {
  opacity: 0;
  position: absolute;
  top: 1vw;
  right: 1vw;
  cursor: pointer;
  font-size: 2cqw;
  color: #7B0302;
  transition: transform 0.2s ease, color 0.2s ease;
}

.newmodal #closeqrsearchModal:hover {
  color: #a10000;
  transform: scale(1.2);
}

/* QR Code section */
.qr-section {
  text-align: center;
  margin-bottom: 2vh;
}

.qr-img {
  width: 120px;
  height: 120px;
}

.qr-code-text {
  font-weight: bold;
  margin-top: 1vh;
}

/* Project details */
.project-details p {
  font-size: 0.85cqw;
  margin: 0.5vh 0;
  color: black;
}

.document-table {
  width: 100%;
  overflow-x: auto; /* enables horizontal scroll on smaller screens */
}

.document-table table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5vh 0;
  font-size: 0.7cqw;  /* smaller font */
  table-layout: fixed; /* make columns distribute evenly */
}

.document-table th,
.document-table td {
  border: 1px solid #ccc;
  padding: 0.5vh 0.3vw;
  text-align: center;
  word-wrap: break-word;
  white-space: normal;
}


.status {
  font-weight: bold;
  border-radius: 1vw; /* smaller pill shape */
  padding: 0.2vh 0.6vw; /* reduced padding */
  font-size: 0.65cqw;  /* optional: slightly smaller text */
  text-align: center;
  min-width: 60px; /* optional: helps maintain pill look */
}


.status.stored {
  background-color: #7B0302;
  color: white;
}

.status.released {
  background-color: #c2c2c2;
  color: #7B0302;
}

.status.available {
  background-color: #7B0302;
  color: #fff;
}

/* Buttons */
.modal-buttons {
  display: flex;
  justify-content: center; /* center all buttons */
  gap: 0.5vw;              /* reduce space between buttons */
  margin-top: 1vh;         /* optional: reduce top margin */
}

.open-btn,
.close-btn {
  background-color: #7B0302;
  color: white;
  padding: 0.5vw 2vw;
  border: none;
  border-radius: 0.5vw;
  font-size: 1cqw;
  cursor: pointer;
}

.close-btn {
  background-color: #C2C2C2;
  color: #7B0302;
}

.open-btn:hover,
.close-btn:hover {
  filter: brightness(1.1);   /* subtle visual lift */
}

.qr-section img {
  min-width: 250px;
  min-height: 250px;
}

.qr-section p {
  font-size: 1cqw;
  font-weight: 700;
}

.preview-projectname {
  margin-top: 5%;
}

.qr-indicator {
  display: flex;
  align-items: center;
  gap: 0.5vw;
}

.qr-indicator-title {
  color: #900B09;
  font-size: 1cqw;
  font-weight: 650;
}

.qr-indicator-text {
  color: #900B09;
  margin-left: 1.4vw;
}

</style>

 <input id="qrInput" type="text" autocomplete="off" style="position:absolute; left:-9999px;" />

  <div class="qr-search-content">
       <div class="qr-indicator">
      <div class="fa fa-info-circle" style="color: #900B09; font-size: 1cqw;"></div>
      <p class="qr-indicator-title">QR SEARCH ENABLED</p>
     </div>
     <small class="qr-indicator-text">You can now search by scanning QR on physical document.</small>
</div>


<div id="qrsearchModal" class="newmodal">
  <div class="new-modal-content">
    <span id="closeqrsearchModal">&times;</span>
    <div id="modalBody">

<div>