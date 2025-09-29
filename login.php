<link rel="stylesheet" href="css/login.css">

<div class="mainContainer">
    <img src="picture/loginBG.png" alt="" class="LoginBg">
</div>

<div class="loginPanel">
    <div class="redPanel">
        <img src="picture/logoOutlined.png" alt="" class="LogoBG">
        <p class="LoginDesc">Sign in to continue to your account</p>
    </div>
    <div class="whitePanel">
        <p class="SignIn">Sign in</p>
        <form id="login-form">
            <div class="inputClass">
                <div class="form-row">
                <input class="EmployeeID" name="employeeid" type="text" placeholder="EmployeeID" required />
            </div>
            <div class="form-row">
                <div class="form-row" style="position: relative;">
                <input class="Password" id="password" name="password" type="password" placeholder="Password" required />               
                <button type="button" id="togglePassword">
                    <i class="fa fa-eye" id="toggleIcon"></i>
                </button>
                </div>
                </div>
            </div>
            <div id="invalid-error" style="color:red;"></div>
            <p class="forgot" data-load="forgot_password.php">forgot password?</p>
            <button class="SignInBtn">Sign in</button>
        </form>
    </div>
</div>
