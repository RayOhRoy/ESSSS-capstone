<link rel="stylesheet" href="css/forgot_password.css">

<div class="mainContainer">
    <img src="picture/loginBG.png" alt="" class="LoginBg">
</div>

<div class="loginPanel">
    <div class="redPanel">
        <img src="picture/logoOutlined.png" alt="" class="LogoBG">
        <p class="LoginDesc">Reset your password here.</p>
    </div>
    <div class="whitePanel">
        <p class="SignIn">Forgot password?</p>
        <form id="forgot-password-form">
            <div id="form-fields">
                <!-- Initial email input -->
                <div class="inputClass">
                    <input class="Email" name="email" type="email" placeholder="Email" required />
                </div>
                <button class="Sendemailbtn" type="submit">Send Email</button>
                <button id="back-login" data-load="login.php" type="button">Cancel</button>
            </div>

            <!-- Shared error box -->
            <div id="forgot-error" class="alert-box" style="display:none;">
                <div class="alert-icon">
                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                </div>
                <div class="alert-text"></div>
            </div>
        </form>
    </div>