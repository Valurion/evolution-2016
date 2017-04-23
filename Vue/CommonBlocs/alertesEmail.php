<?php

if (isset($_POST['ID_USER'])) {
    $this->javascripts[] = '
        $(document).ready(function(){
            $("input#email").val("'.$_POST['ID_USER'].'");
            ajax.alertPopup();
        });';
}
?>

<?php if($revue['TYPEPUB'] == 1) { ?>
<div class="contrast-box email_alertes">
    <h1>Alertes e-mail</h1>
    <p>
        <?php
        switch ($revue['TYPEPUB']) {
        case 1:
            echo 'Sommaire des nouveaux numéros';
            break;
        case 6:
        case 3:
            echo 'Sommaire des nouvelles parutions';
            break;
        default:
            echo 'Sommaire des nouveaux numéros';
            break;
        }
        ?>
    </p>
    
    <form action="javascript:ajax.alertPopup()" method="post" name="ajoutalertes" id="ajoutalertes">
        <input id="email" type="email" name="email" value="<?php if(isset($authInfos['U'])){echo $authInfos['U']['EMAIL'];} ?>" required="required" placeholder="Votre e-mail" revue="<?php echo (isset($revue['ID_REVUE'])?$revue['ID_REVUE']:$revue['REVUE_ID_REVUE']); ?>">
        <a class="see-exemple left" href="javascript:void(0);" onclick="ajax.alertSample('<?php echo isset($numero['NUMERO_ID_NUMPUBLIE'])?$numero['NUMERO_ID_NUMPUBLIE']:$numero['ID_NUMPUBLIE']; ?>');">Voir un exemple</a>
        <button id="inscriptionBtn" type="submit">S'inscrire <span class="unicon unicon-round-arrow-black-right">&#10140;</span></button>
    </form>
</div>

<div id="div_modal_alert" class="window_modal">
    <div class="info_modal">
        <?php
        switch ($revue['TYPEPUB']) {
        case 1:
            echo '<h2>ALERTES EMAIL - REVUE '.(isset($revue['REVUE_TITRE'])?$revue['REVUE_TITRE']:$revue['TITRE']).'</h2>'
                . '<p class=\'msg msg-valide\'>Votre alerte a bien été prise en compte.</p>'
                . '<p class=\'msg msg-valide\'>Vous recevrez un email à chaque nouvelle parution d\'un numéro de cette revue.</p>';
            break;
        case 6:
        case 3:
            echo '<h2>ALERTES EMAIL - COLLECTION '.(isset($revue['REVUE_TITRE'])?$revue['REVUE_TITRE']:$revue['TITRE']).'</h2>'
                . '<p class=\'msg msg-valide\'>Votre alerte a bien été prise en compte.</p>'
                . '<p class=\'msg msg-valide\'>Vous recevrez un email à chaque nouvelle parution d\'un numéro de cette collection.</p>';
            break;
        default:
            echo '<h2>ALERTES EMAIL - MAGAZINE '.(isset($revue['REVUE_TITRE'])?$revue['REVUE_TITRE']:$revue['TITRE']).'</h2>'
                . '<p class=\'msg msg-valide\'>Votre alerte a bien été prise en compte.</p>'
                . '<p class=\'msg msg-valide\'>Vous recevrez un email à chaque nouvelle parution d\'un numéro de ce magazine.</p>';
            break;
        }

        echo '<p class=\'msg msg-invalide\' style=\'display: none;\'>Erreur lors de l\'enregistrement de votre alerte.</p>';
        echo '<p class=\'msg msg-already\' style=\'display: none;\'>Vous avez déjà enregistré cette alerte. Vous pouvez gérer vos alertes depuis le menu Mon cairn.info.</p>';
        ?>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span>
        </div>
    </div>
</div>

<?php include ("exemple-alerte-email.php"); ?>
<?php } ?>