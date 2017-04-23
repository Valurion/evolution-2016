<?php
$this->titre = "My account - Update my password";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="./">Home</a> 
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./my_account.php">Mon compte</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Update my password</a>
</div>
<div id="mainContent">
    <div id="body-content">
        <div id="free_text">
            <h1 class="main-title">Update password</h1>
            <form method="POST" id="modifiercompte" name="modifiercompte" action="javascript:ajax.modifMdp()">
                <div class="wrapper">                        
                    <div class="blue_milk left w45">
                        <label class="mdp" for="mdp">Old password <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp" name="mdp" class="mdp">
                    </div>
                </div>                    
                <div class="wrapper mt1">
                    <div class="blue_milk left w45">
                        <label class="mdp2" for="mdp2">New password <small>(6 to 20 characters and/or numbers)</small> <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp2" name="mdp2" class="mdp2">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="mdp3" for="mdp3">Verify password <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp3" name="mdp3" class="mdp3">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <!-- Hidden field -->
                    <input type="hidden" required="required" value="<?= isset($authInfos["U"])?$authInfos["U"]["EMAIL"]:""?>" id="email" name="email" class="email">
                    <a class="link-underline" href="mdp_oublie.php">Forgot your password?</a>
                    <button class="button right bold">Update my password</button>
                </div>
            </form>
        </div>
    </div>
</div>

