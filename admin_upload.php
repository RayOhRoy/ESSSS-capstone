<style>
  * {
    overflow: hidden;
  }

  select,
  input[type="text"],
  input[type="date"] {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    margin-bottom: 1%;
  }

  .search-input {
    width: 100%;
    padding: 1.2vh 1vw;
    border-radius: 0.26vw;
    border: 1px solid #ccc;
    color: #7B0302;
    background-color: #f5f5f5;
    margin-bottom: 1.5vh !important;
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
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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

  .file-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .file-preview img {
    max-width: 50px;
    max-height: 50px;
    margin-bottom: 5px;
  }

  .file-icon {
    width: 40px;
    height: 40px;
  }

  .file-label {
    font-size: 11px;
    text-align: center;
    word-break: break-word;
    max-width: 80px;
  }

  .search-upload {
    width: 100%;
    padding: 1.2vh 1vw;
    border-radius: 0.26vw;
    border: 1px solid #ccc;
    color: #7B0302;
    background-color: #f5f5f5;
    box-shadow: 0 0.18vh 0.56vh rgba(0, 0, 0, 0.55);
    margin-bottom: 1.5vh;
  }

  .document-table {
    width: 80%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 13px;
    background-color: #fafafa;
  }

  .document-table th,
  .document-table td {
    border: 1px solid #ccc;
    padding: 6px 10px;
    text-align: center;
    vertical-align: middle;
  }

  .file-preview img {
    max-width: 80px;
    max-height: 80px;
    border: 1px solid #ccc;
    margin-top: 5px;
  }

  .note {
    margin-top: 20px;
    font-style: italic;
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
  }

  .footer-buttons {
    margin-top: 20px;
    display: flex;
    gap: 10px;
  }
</style>

<div class="topbar">
  <span style="font-size: 2cqw; color: #7B0302; font-weight: 700;">Upload Project</span>
  <div class="topbar-content">
    <div class="search-bar">
      <input class="search-input" type="text" placeholder="Search Project" />
    </div>
    <div class="icons">
      <span id="notification-circle-icon" class="fa fa-bell-o" style="font-size: 1.75cqw; color: #7B0302;"></span>
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

<div class="content">
  <form id="projectForm" enctype="multipart/form-data">
    <div class="form-wrapper">
      <div class="form-grid">
        <div class="column">
          <input type="hidden" id="project_name" name="project_name">

          <div class="form-row"><label>Lot Number:</label><input id="lotNumber" name="lot_no" type="text" required />
          </div>
          <div class="form-row"><label>Client First Name:</label><input id="clientName" name="client_name" type="text"
              required /></div>
          <div class="form-row"><label>Client Last Name:</label><input id="clientLastName" name="last_name" type="text"
              required /></div>

          <div class="form-row">
            <label>Province:</label>
            <select name="province" id="province" onchange="loadMunicipalities()">
              <option value="">Select Province</option>
              <option value="Bulacan">Bulacan</option>
            </select>
          </div>

          <div class="form-row">
            <label>Municipality:</label>
            <select name="municipality" id="municipality" onchange="loadBarangays()" disabled>
              <option value="">Select Municipality</option>
            </select>
          </div>

          <div class="form-row">
            <label>Barangay:</label>
            <select name="barangay" id="barangay" disabled>
              <option value="">Select Barangay</option>
            </select>
          </div>

          <div class="form-row"><label>Street/Subdivision:</label><input name="street" type="text" /></div>
        </div>

        <div class="column">
          <div class="form-row">
            <label>Survey Type:</label>
            <select name="survey_type" id="surveyType">
              <option value="">Select Survey Type</option>
              <option value="Relocation Survey ">Relocation Survey </option>
              <option value="Verification Survey">Verification Survey</option>
              <option value="Subdivision Survey ">Subdivision Survey </option>
              <option value="Consolidation Survey  ">Consolidation Survey </option>
              <option value="Topographic Survey ">Topographic Survey</option>
              <option value="AS-Built Survey ">AS-Built Survey</option>
              <option value="Sketch Plan / Vicinity Map">Sketch Plan / Vicinity Map</option>
              <option value="Land Titling/ Transfer">Land Titling/ Transfer</option>
              <option value="Real Estate">Real Estate</option>
            </select>
          </div>
          <div class="form-row"><label>Survey Start Date:</label><input id="startDate" name="survey_start"
              type="date" /></div>
          <div class="form-row"><label>Survey End Date:</label><input id="endDate" name="survey_end" type="date" />
          </div>
          <div class="form-row"><label>Agent:</label><input id="agent" name="agent" type="text" required /></div>

          <form id="projectForm">
            <div class="form-row">
              <label for="surveyType">Request Type:</label>
              <select id="requestType" name="requestType">
                <option value="For Approval">For Approval</option>
                <option value="Sketch Plan">Sketch Plan</option>
              </select>
            </div>
            <div id="toBeApprovedBy">
              <label>To be approved by:</label>
              <div class="approval-group" style="margin-left: 23.5%">
                <label><input type="radio" name="approval" value="LRA"> LRA</label>
                <label><input type="radio" name="approval" value="BUREAU"> BUREAU</label>
                <label><input type="radio" name="approval" value="CENRO"> CENRO</label>
              </div>
            </div>
        </div>
  </form>
</div>

<div class="qr-preview">
  <h4>PROJECT QR CODE</h4>
  <div class="qr-box"></div>
</div>
</div>

<div class="note">Select all that apply and upload digital document if applicable</div>

<table class="document-table" id="documentTable">
  <thead>
    <tr>
      <th>Document Name</th>
      <th>Physical Documents</th>
      <th>Digital Documents</th>
      <th>Preview / File Name</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Original Plan</td>
      <td><input type="checkbox" name="physical[]" value="Original Plan"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
    <tr>
      <td>Lot Title</td>
      <td><input type="checkbox" name="physical[]" value="Lot Title"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
    <tr>
      <td>Ref Plan/Lot Data</td>
      <td><input type="checkbox" name="physical[]" value="Ref Plan/Lot Data"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
    <tr>
      <td>TD</td>
      <td><input type="checkbox" name="physical[]" value="TD"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
    <tr>
      <td>Transmittal</td>
      <td><input type="checkbox" name="physical[]" value="Transmittal"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
    <tr>
      <td>Field Notes</td>
      <td><input type="checkbox" name="physical[]" value="Field Notes"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
    <tr>
      <td>Deed of Sale/Transfer</td>
      <td><input type="checkbox" name="physical[]" value="Deed of Sale/Transfer"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
    <tr>
      <td>Tax Declaration</td>
      <td><input type="checkbox" name="physical[]" value="Tax Declaration"></td>
      <td><input type="file" name="digital[]" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
          onchange="previewFile(this)"></td>
      <td class="file-preview"></td>
    </tr>
  </tbody>
</table>

<div class="footer-buttons">
  <button type="button" class="btn btn-red" onclick="generateQR()">Generate QR Code</button>
  <button type="button" class="btn btn-grey" onclick="submitForm()">Upload</button>
</div>
</form>
</div>