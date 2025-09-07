<style>
.mainContainer {
    position: fixed; 
    top: 0;
    left: 0;
    width: 100%;
    height: 100%; 
    overflow: hidden; 
}

.LoginBg {
    width: 100%;
    height: 100%;
    object-fit: cover; 
}

.loginPanel{
    width: 60%;
    height: 80%;
    display: grid;
    grid-template-columns: 1fr 2fr;
    box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.3);
    position: absolute;
    top: 10%;
    bottom: 10%;
    left: 10%;
    font-family: Arial, Helvetica, sans-serif;
    border-radius: 0.8cqw;
    background-color: rgba(255, 255, 255, 0.2); 
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px); 
}

.redPanel{
    background-color: #7b0302;
    height: 100%;
    
    border-bottom-left-radius: 0.8cqw;
    border-top-left-radius: 0.8cqw;
}

.LogoBG{
    width: 100%;
}

.redPanel p,hr{
    color: white;
    position: absolute;
    bottom: 0.75cqw;
    left: 0.75cqw;
}
.Welcome{
    font-size: 1cqw;
    font-weight: 700;
    margin-bottom:4cqw;
}
.LoginDesc{
    font-size: 1cqw;
    margin-bottom:1cqw;
}

hr{
    width: 10cqw;
    height: 0.2cqw;
    background-color: white;
    margin-bottom:3cqw;
    font-size: 0.2cqw;
}

.whitePanel{
    color:#7b0302;
    text-align: center;
    height: 100%;
}

.SignIn{
    font-size: 2cqw;
    font-weight: 700;
    margin-bottom: 5cqw;
    margin-top: 5cqw;
    text-shadow: 0 4px 6px rgb(238, 237, 237);
}

.inputClass{
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.7cqw; 
    margin-bottom: 3cqw;
}
.whitePanel input{
    border-radius: 0.5cqw;
    border: none;
    background-color: rgb(222, 222, 222);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    height: 2.5cqw;
    width: 20cqw;
    color:#7b0302;
    padding-left: 15px;
    font-size: 1.3cqw;
}
.whitePanel input::placeholder {
    color:#7b0302;
    opacity: 1;
}

.Sendemailbtn {
    width: 7cqw;
    height: 2.3cqw;
    background-color: #7b0302 ;
    border-radius: 0.5cqw;
    border: none;
    border: none;
    cursor: pointer;
    color: white;
    font-size: 1cqw;
}

.forgot{
    font-size: 1cqw;
    text-decoration: underline;
    text-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
}

.forgot:hover{
    color: white
}

.SignInBtn:hover{
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
    background-color: #630303;
}

#back-login {
    position: absolute;
    margin-left: -30%;
    margin-top: 20%;
    font-size: 2cqw;
}

#back-login:hover {
    color: white;
}

#otp-modal {
    display: block; 
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5); 
    backdrop-filter: blur(4px);
}

#otp-modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}


.otp-modal-content {
    position: relative;
    background-color: white;
    margin: 10% auto;
    padding: 2cqw;
    border: 1px solid #888;
    width: 30%;
    border-radius: 1cqw;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
    font-family: Arial, Helvetica, sans-serif;
    color: #7b0302;
    text-align: center;
}

.otp-modal-content input {
    width: 80%;
    height: 2.5cqw;
    margin-bottom: 1.5cqw;
    padding: 0.5cqw 1cqw;
    border-radius: 0.5cqw;
    border: 1px solid #ccc;
    font-size: 1.2cqw;
}

#otp-modal-close {
    position: absolute;
    top: 0.5cqw;
    right: 0.5cqw;
    font-size: 2cqw;
    color: #7b0302;
    cursor: pointer;
    background: none;
    border: none;
}

</style>

<div class="mainContainer">
    <img src="picture/loginBG.png" alt="" class="LoginBg">
</div>

<div class="loginPanel">
    <div class="redPanel">
        <img src="picture/logoOutlined.png" alt="" class="LogoBG">
        <p class="Welcome">Trouble signing in?</p>
        <hr />
        <p class="LoginDesc">Reset your password here.</p>
    </div>
    <div class="whitePanel">
        <p class="SignIn">Forgot password?</p>
        <form id="forgot-password-form">
            <div class="inputClass">
                <input class="Email" name="email" type="email" placeholder="Email" required />
            </div>
            
            <button class="Sendemailbtn">Send Email</button>
            <div id="invalid-error" style="color:red;"></div>
        </form>
        <div id="back-login" class="fa fa-arrow-left" data-load="login.php"></div>
    </div>
</div>

<div id="otp-modal">
    <div class="otp-modal-content">
        <button id="otp-modal-close" title="Close OTP Modal">&times;</button>
        <form id="otp-verify-form">
            <h3>Enter OTP</h3>
            <input type="text" name="otp" placeholder="6-digit OTP" maxlength="6" required />
            <input type="password" name="new_password" placeholder="New Password" required />
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <button class="Sendemailbtn" type="submit">Reset Password</button>
            <div id="otp-error" style="color:red; margin-top: 1cqw;"></div>
        </form>
    </div>
</div>