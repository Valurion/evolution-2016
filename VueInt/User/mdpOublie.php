<?php
$this->titre = "Password forgotten";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="body-content">
    <div id="free_text">
        <br>
        <h1 class="main-title">Password forgotten?</h1>
        <br>

        <form id="passForgetForm" action="javascript:ajax.sendPasswordMail();" method="post" action="./password_forgotten.php">
            Enter your email address below and we will send you an email to reset your password.<br/><br/>
            <div class="blue_milk w45 inbl">
                <label for="email">Your email address
                    <span class="red">*</span>
                    <input type="text" required="required" id="email" name="email" size="40" value="<?= isset($authInfos["U"]["EMAIL"]) ? $authInfos["U"]["EMAIL"] : '' ?>">
                </label>
            </div>
            <div class="inbl">
                <input type="submit" value="Send" class="button">
            </div>
        </form>
    </div>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
</div>

<div style="display: none;" class="window_modal" id="modal_mail_success_oblie">
    <div class="info_modal">
        <h1>Email sent</h1>
        <p>An email has been sent to your email address <i><span id="email_ok"></span></i>. </p>
         <p> Please click on the link provided in this email to reset your password.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
    </div>
</div>

<div style="display: none;" class="window_modal" id="modal_mail_error_oblie">
    <div class="info_modal">
        <h1>Email cannot be sent.</h1>
        <p>Sorry, this email address <i><span id="email_ko"></span></i> does not exist in our database.</p>
        <p>If you need help using our website, please <a href="./contact.php">contact us</a>.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
    </div>
</div>
