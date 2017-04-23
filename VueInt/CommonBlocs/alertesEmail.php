<?php

if (isset($_POST['ID_USER'])) {
    $this->javascripts[] = '
        $(document).ready(function(){
            $("input#email").val("'.$_POST['ID_USER'].'");
            ajax.alertPopup();
        });';
}
?>

<div class="contrast-box  email_alertes">
    <h1>TOC Mail Alerts</h1>
    <p>
        <?php
        switch ($revue['TYPEPUB']) {
        case 1:
            echo 'When new issues are published';
            break;
        /*case 6:
        case 3:
            echo 'Veuillez indiquer votre adresse e-mail pour recevoir une alerte sur les parutions de cet collection';
            break;
        default:
            echo 'Veuillez indiquer votre adresse e-mail pour recevoir une alerte sur les parutions de ce magazine';
            break;*/
        }
        ?>
    </p>
    <form action="javascript:ajax.alertPopup()" method="post" name="ajoutalertes" id="ajoutalertes">
        <input id="email" type="email" name="email" value="<?php if(isset($authInfos['U'])){echo $authInfos['U']['EMAIL'];} ?>" required="required" placeholder="Your e-mail" revue="<?php echo (isset($revue['ID_REVUE'])?$revue['ID_REVUE']:$revue['REVUE_ID_REVUE']); ?>">
        <button id="inscriptionBtn" type="submit">Subscribe <span class="unicon unicon-round-arrow-black-right">&#10140;</span></button>
    </form>
</div>

<div id="div_modal_alert" class="window_modal">
    <div class="info_modal">
        <?php
        switch ($revue['TYPEPUB']) {
        case 1:
            echo '<h2>Email alert</h2>
                    <p>
                        Your alert request has been correctly taken into account.<br>
                        You will receive an email when new issues are put online.
                    </p>';
            break;
        /*case 6:
        case 3:
            echo '<h2>ALERTES EMAIL - COLLECTION '.(isset($revue['REVUE_TITRE'])?$revue['REVUE_TITRE']:$revue['TITRE']).'</h2>'
                . '<p>Votre alerte a bien été prise en compte.</p>'
                . '<p>Vous recevrez un email à chaque nouvelle parution d\'un numéro de cet collection</p>';
            break;
        default:
            echo '<h2>ALERTES EMAIL - MAGAZINE '.(isset($revue['REVUE_TITRE'])?$revue['REVUE_TITRE']:$revue['TITRE']).'</h2>'
                . '<p>Votre alerte a bien été prise en compte.</p>'
                . '<p>Vous recevrez un email à chaque nouvelle parution d\'un numéro de ce magazine</p>';
            break;*/
        }
        ?>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
    </div>
</div>
