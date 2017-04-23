<?php
$this->titre = "Mot de passe oublié";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="body-content">
    <div id="free_text">
        <br>
        <h1 class="main-title">Mot de passe oublié ?</h1>
        <br>

        <form id="passForgetForm" action="javascript:ajax.sendPasswordMail();" method="post" action="./mdp_oublie.php">
            <div class="blue_milk w45 inbl">
                <label for="email">Adresse e-mail (votre identifiant)
                    <span class="red">*</span>
                    <input type="text" required="required" id="email" name="email" size="40" value="<?= isset($authInfos["U"]["EMAIL"]) ? $authInfos["U"]["EMAIL"] : '' ?>">
                </label>
            </div>
            <div class="inbl">
                <input type="submit" value="Envoyer" class="button">
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
        <h1>E-mail envoyé</h1>
        <p>Un e-mail vient de vous être envoyé.<span style="display:none;" id="email_ok"></span></p>
        <p>En cliquant sur le lien qu'il contient, vous pourrez ré-initialiser votre mot de passe.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span>
        </div>
    </div>
</div>

<div style="display: none;" class="window_modal" id="modal_mail_error_oblie">
    <div class="info_modal">
        <h1>Attention: mot de passe non envoyé</h1>
        <p>Cette adresse e-mail <i><span id="email_ko"> </span></i> ne figure pas dans notre base de données.</p>
        <p>Pour toute assistance, contacter notre <u>support utilisateurs</u>.</p>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span>
        </div>
    </div>
</div>
