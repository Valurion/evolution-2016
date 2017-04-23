<?php 
$this->titre = "Password forgotten";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="body-content">
    <div id="free_text">
        <br>       
        
        <?php 
            if($token){?>
                <h1 class="main-title">Password forgotten? Fill out your new password!</h1> 
                    <br>
                    <form id="newPassForm" action="javascript:ajax.saveNewPassword();" method="post">
                        <input id="token" type="hidden">
                        <div class="blue_milk w45" style="display:block;">
                            <label for="email">Email Address
                                <input type="text" value="<?php echo $token[0]; ?>" required="required" id="email" name="email" size="40" disabled>
                            </label>
                        </div>
                        <br>
                        <div class="blue_milk w45" style="display:block;">
                            <label for="newPwd">New password
                                <input type="password" value="" required="required" id="newPwd" name="newPwd" size="20">
                            </label>
                        </div>
                        <br>
                        <div class="blue_milk w45" style="display:block;">
                            <label for="confirmPwd">Confirm new password
                                <input type="password" value="" required="required" id="confirmPwd" name="confirmPwd" size="20">
                            </label>
                        </div>
                        <br>
                        <div>
                            <input type="submit" value="Send" class="button">
                        </div>
                    </form> 
            <?php }
            else
            { ?>
                <h1 class="main-title">The link you received has expired.</h1>
                <p>Please reapply</p>';
            <?php }
        ?>
    </div>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
    &nbsp;<br>
</div>

<div style="display: none;" class="window_modal" id="modal_pwd_success">
    <div class="info_modal">
        <h1>Congratulations</h1>
        <p>Your password has been changed successfully.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
    </div>
</div>

<div style="display: none;" class="window_modal" id="modal_pwd_error">
    <div class="info_modal">
        <h1>Warning!</h1>
        <p>Passwords do not match.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
    </div>
</div>

<div style="display: none;" class="window_modal" id="modal_token_error">
    <div class="info_modal">
        <h1>Warning!</h1>
        <p>The link you use is no longer valid.<br/>Please reapply.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
    </div>
</div>
