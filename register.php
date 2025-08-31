<div class="main-container">
  <div class="form-container">
    <div class="image-side">
      <img src="picture/background.jpg" alt="Background" class="background-img"/>
    </div>
    <div class="form-side">
      <div class="form-box">
        <div class="header">
          <h2>Login</h2>
        </div>

<form id="register-form" class="form-box" method="POST" action="model/register_processing.php">
  <div class="header">
    <img src="picture/logo.png" alt="Logo" class="logo"/>
    <h2>Register</h2>
  </div>

  <div class="input-grid">
    <input type="text" name="username" placeholder="Username" required />

    <select name="position" required>
      <option value="" disabled selected>Select Position</option>
      <option value="Secretary">Secretary</option>
      <option value="Compliance Officer">Compliance Officer</option>
      <option value="CAD Operator">CAD Operator</option>
    </select>

    <input type="text" name="firstname" placeholder="First name" required />
    <input type="text" name="lastname" placeholder="Last name" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
  </div>

  <div class="link">
    <span data-load="login.php">Already have an account?</span>
  </div>

  <button type="submit">Register</button>
</form>
