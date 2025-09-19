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
    margin-right: 4%;
    transition: filter 0.3s ease;
}

#back-login,
.back-login {
    width: 7cqw;
    height: 2.3cqw;
    background-color: #A3A3A3;
    color: #7b0302;
    border-radius: 0.5cqw;
    border: none;
    border: none;
    cursor: pointer;
    color: white;
    font-size: 1cqw;
    transition: all 0.3s ease;
}

.Sendemailbtn:hover {
    filter: brightness(1.2);
}

#back-login:hover,
.back-login:hover {
    background-color: #7b0302;
    color: white;
}

.alert-box {
  display: flex;
  align-items: center;
  background-color: white;
  border: 2px solid red;
  color: red;
  padding: 10px 15px;
  font-family: Arial, sans-serif;
  border-radius: 4px;
  max-width: 60%;
  margin: 5% auto;
}

.alert-icon {
  font-size: 24px;
  margin-right: 15px;
}

.alert-text {
  flex: 1;
  font-size: 16px;
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
