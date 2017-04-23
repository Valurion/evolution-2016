<?php
$this->titre = "Modification de mon adresse e-mail";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./mon_compte.php">Mon compte</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Modification de mon adresse e-mail</a>
</div>
<div id="mainContent">
    <div id="body-content">
        <div id="free_text">
            <h1 class="main-title">Modification de mon adresse e-mail</h1>
            <form method="POST" id="modifiercompte" name="modifiercompte" action="javascript:ajax.modifEmail()">
                <div class="wrapper">
                    <div class="blue_milk left w45">
                        <label class="email" for="email">Nouvelle adresse e-mail <span class="red">*</span></label>
                        <input type="email" required="required" value="" id="email" name="email" class="email">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="cemail" for="cemail">Nouvelle adresse e-mail <small>(confirmation)</small> <span class="red">*</span></label>
                        <input type="email" required="required" value="" id="cemail" name="cemail" class="cemail">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <div class="blue_milk left w45">
                        <label class="mdp" for="mdp">Mon mot de passe <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp" name="mdp" class="mdp">
                    </div>                        
                </div>
                <div class="wrapper mt1">
                    <button class="button right bold">Modifier mon adresse e-mail</button>
                </div>
            </form>
        </div>
    </div>
</div>

