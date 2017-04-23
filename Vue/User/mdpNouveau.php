<?php 
$this->titre = "Mot de passe oublié";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="body-content">
    <div id="free_text">
        <br>       
        
        <?php 
            if($token){?>
                <h1 class="main-title">Mot de passe oublié ? Encodez votre nouveau mot de passe !</h1> 
                    <br>
                    <form id="newPassForm" action="javascript:ajax.saveNewPassword();" method="post">
                        <input id="token" type="hidden">
                        <div class="blue_milk w45" style="display:block;">
                            <label for="email">Adresse e-mail
                                <input type="text" value="<?php echo $token[0]; ?>" required="required" id="email" name="email" size="40" disabled>
                            </label>
                        </div>
                        <br>
                        <div class="blue_milk w45" style="display:block;">
                            <label for="newPwd">Nouveau mot de passe
                                <input type="password" value="" required="required" id="newPwd" name="newPwd" size="20">
                            </label>
                        </div>
                        <br>
                        <div class="blue_milk w45" style="display:block;">
                            <label for="confirmPwd">Confirmer le nouveau mot de passe
                                <input type="password" value="" required="required" id="confirmPwd" name="confirmPwd" size="20">
                            </label>
                        </div>
                        <br>
                        <div>
                            <input type="submit" value="Envoyer" class="button">
                        </div>
                    </form> 
            <?php }
            else
            { ?>
                <h1 class="main-title">Le lien que vous avez reçu n'est plus valide.</h1>
                <p>Veuillez faire une nouvelle demande</p>';
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
        <h1>Félicitations</h1>
        <p>Votre mot de passe a été changé avec succès.</p>
        <div class="buttons">
            <a href="./Accueil_Revues.php" class="blue_button ok">Fermer</a>
        </div>
    </div>
</div>

<div style="display: none;" class="window_modal" id="modal_pwd_error">
    <div class="info_modal">
        <h1>Attention!</h1>
        <p>Les mots de passe ne correspondent pas.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span>
        </div>
    </div>
</div>

<div style="display: none;" class="window_modal" id="modal_token_error">
    <div class="info_modal">
        <h1>Attention!</h1>
        <p>Le lien que vous utilisez n'est plus valide.<br/>Veuillez faire une nouvelle demande.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span>
        </div>
    </div>
</div>
