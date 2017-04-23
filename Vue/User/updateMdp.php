<?php
$this->titre = "Modification de mon mot de passe";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a> 
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./mon_compte.php">Mon compte</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Modification de mon mot de passe</a>
</div>
<div id="mainContent">
    <div id="body-content">
        <div id="free_text">
            <h1 class="main-title">Modification de mon mot de passe</h1>
            <form method="POST" id="modifiercompte" name="modifiercompte" action="javascript:ajax.modifMdp()">
                <div class="wrapper">                        
                    <div class="blue_milk left w45">
                        <label class="mdp" for="mdp">Ancien mot de passe <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp" name="mdp" class="mdp">
                    </div>
                </div>                    
                <div class="wrapper mt1">
                    <div class="blue_milk left w45">
                        <label class="mdp2" for="mdp2">Nouveau mot de passe <small>(6 à 20 caractères et/ou chiffres)</small> <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp2" name="mdp2" class="mdp2">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="mdp3" for="mdp3">Nouveau mot de passe <small>(confirmation)</small> <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp3" name="mdp3" class="mdp3">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <!-- Hidden field -->
                    <input type="hidden" required="required" value="<?= isset($authInfos["U"])?$authInfos["U"]["EMAIL"]:""?>" id="email" name="email" class="email">
                    <a class="link-underline" href="mdp_oublie.php">Mot de passe oublié ?</a>
                    <button class="button right bold">Modifier mon mot de passe</button>
                </div>
            </form>
        </div>
    </div>
</div>

