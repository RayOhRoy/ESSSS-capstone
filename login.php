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
                <input class="Password" name="password" type="password" placeholder="Password" required />
            </div>
            <div id="invalid-error" style="color:red;"></div>
            <!-- <p class="forgot">forgot password?</p> -->
            <button class="SignInBtn">Sign in</button>
        </form>
    </div>
</div>
