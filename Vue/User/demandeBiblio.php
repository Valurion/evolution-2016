<?php ?>
<div id="free_text">

    <a name="top"></a>
    <h1 class="main-title">Formulaire de demande à mon bibliothécaire</h1>

    <div class="clear">&nbsp;</div>
    <form action="javascript:ajax.envoiDemandeBiblio()" id="form_envoi">
    <div class="blue_milk left w40">
        <label for="PRENOM" class="prenom">Votre prénom <span class="red">*</span></label><br>
        <input type="text" value="<?= $authInfos['U']['PRENOM']?>" name="PRENOM" id="PRENOM" class="prenom" required="required">
    </div>

    <div class="blue_milk right w40">
        <label for="NOM" class="prenom">Votre nom <span class="red">*</span></label><br>
        <input type="text" value="<?= $authInfos['U']['NOM']?>" name="NOM" id="NOM" class="prenom" required="required">
    </div>
    <br>
    <br>
    <br>
    <div class="blue_milk left w40">
        <label for="Fonction">Votre fonction <span class="red">*</span></label><br>
        <input type="text" value="" name="FONCTION" id="FONCTION" required="required">
    </div>
    <br>
    <br>
    <br>
    <div class="blue_milk center w80">
        <label for="MOTIVATION">Motivation de votre demande </label>
        <textarea name="MOTIVATION" id="MOTIVATION" cols="85" rows="10" class="custom_textarea_bm"></textarea>
    </div>
    <br>
    <br>
    <br>
    <button class="continuer checkout-button">Confirmation</button>
    </form>
    <br/>
</div>

