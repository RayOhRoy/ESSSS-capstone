<link rel="stylesheet" href="css/login.css">

<div class="mainContainer">
    <img src="picture/loginBG.png" alt="" class="LoginBg">
</div>

<div class="loginPanel">
    <div class="redPanel">
        <img src="picture/logoOutlined.png" alt="" class="LogoBG">
        <p class="Welcome">Welcome</p>
        <hr />
        <p class="LoginDesc">Sign in to continue to your account</p>
    </div>
    <div class="whitePanel">
        <p class="SignIn">Sign in</p>
        <form id="login-form">
            <div class="inputClass">
                <input class="EmployeeID" name="employeeid" type="text" placeholder="EmployeeID" required />
                <div class="form-row" style="position: relative;">
                <input class="Password" id="password" name="password" type="password" placeholder="Password" required />               
                <button type="button" id="togglePassword" style="
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0;
                    color: #7b0302;
                ">
                    <i class="fa fa-eye" id="toggleIcon"></i>
                </button>
                </div>
            </div>
            <div id="invalid-error" style="color:red;"></div>
            <p class="forgot" data-load="forgot_password.php">forgot password?</p>
            <button class="SignInBtn">Sign in</button>
        </form>
    </div>
</div>
