<?php
    // Définition de la navigation
    // Sur base de ./Vue/Pages/Blocs/navPage.php

    // Init
    $link_numero_precedent   = "";
    $link_numero_suivant     = "";
    $urlbase                 = "";
    $libelle                 = "Numéro";
    $anchor                  = "";

    // Configuration de la base d'URL
    if($numero["REVUE_TYPEPUB"] == "1") {$urlbase = "revue"; /*$anchor = "#page_revue";*/}
    if($numero["REVUE_TYPEPUB"] == "2") {$urlbase = "magazine"; /*$anchor = "#page_numero";*/}

    // Précédent
    if(!empty($numero["PREV_NUMERO"])) {
        $link_numero_precedent = $urlbase."-".$numero["PREV_NUMERO"][0]["URL_REWRITING"]."-".$numero["PREV_NUMERO"][0]["ANNEE"]."-".$numero["PREV_NUMERO"][0]["NUMERO"].".htm";
    }
    // Suivant
    if(!empty($numero["NEXT_NUMERO"])) {
        $link_numero_suivant = $urlbase."-".$numero["NEXT_NUMERO"][0]["URL_REWRITING"]."-".$numero["NEXT_NUMERO"][0]["ANNEE"]."-".$numero["NEXT_NUMERO"][0]["NUMERO"].".htm";
    }
?>
<div class="numero_navpages">
    <?php if ($link_numero_precedent != "") { ?>
        <a class="left blue_button" href="./<?php echo $link_numero_precedent.$anchor; ?>">
            <span class="icon-arrow-white-left icon"></span>
            <?php echo ucfirst($libelle); ?> précédent
        </a>
    <?php } ?>
    <span class="current_page"></span>
    <?php if ($link_numero_suivant != "") { ?>
        <a class="right blue_button" href="./<?php echo $link_numero_suivant.$anchor; ?>">
            <?php echo ucfirst($libelle); ?> suivant
            <span class="icon-arrow-white-right icon"></span>
        </a>
    <?php } ?>
</div>