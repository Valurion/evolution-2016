<?php
$this->titre = "My account - Update my email address";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="./">Home</a> 
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./my_account.php">My account</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Update my email address</a>
</div>
<div id="mainContent">
    <div id="body-content">
        <div id="free_text">
            <h1 class="main-title">Update email address</h1>
            <form method="POST" id="modifiercompte" name="modifiercompte" action="javascript:ajax.modifEmail()">
                <div class="wrapper">
                    <div class="blue_milk left w45">
                        <label class="email" for="email">New email address <span class="red">*</span></label>
                        <input type="email" required="required" value="" id="email" name="email" class="email">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="cemail" for="cemail">New email address <small>(confirmation)</small> <span class="red">*</span></label>
                        <input type="email" required="required" value="" id="cemail" name="cemail" class="cemail">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <div class="blue_milk left w45">
                        <label class="mdp" for="mdp">Password <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp" name="mdp" class="mdp">
                    </div>                        
                </div>
                <div class="wrapper mt1">
                    <button class="button right bold">Update my email address</button>
                </div>
            </form>
        </div>
    </div>
</div>

