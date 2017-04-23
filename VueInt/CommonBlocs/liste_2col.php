<!--
Ce template sert à l'affichage d'une liste à 2 colonnes.
Il s'attend à recevoir:
    - $arrayForList = l'array qui contient les données, comprenant le champ TYPEPUB
    - $arrayFieldsToDisplay = un array qui contient les champs à afficher. Par défaut, seul le titre et l'image s'affichent
    - $prefix = un prefix pour le nom des champs de l'array (par ex: 'NUMERO_')
-->

<?php
if (!isset($prefix)) $prefix = null;

if (isset($arrayForList)) { ?>
    <div id="list_revue_suscriber"">
        <?php
        $x = 1;
        foreach ($arrayForList as $row) {
            if ($row[$prefix . 'TYPEPUB'] == '1') {
                $url = 'journal-' . $row[$prefix . 'URL_REWRITING'];
                $titre = $row[$prefix . 'TITRE'];
            } else if ($row[$prefix . 'TYPEPUB'] == '3' || $row[$prefix . 'TYPEPUB'] == '6') {
                if ($currentPage == 'liste') {
                    $url = 'collection-' . $row[$prefix . 'URL_REWRITING'];
                    $titre = $row[$prefix . 'TITRE'];
                } else if ($currentPage == 'editeur') {
                    $url = 'collection-' . $row[$prefix . 'URL_REWRITING'];
                    $titre = 'Collection « ' . $row[$prefix . "TITRE"] . ' »';
                } else {
                    $url = $row[$prefix . 'URL_REWRITING'] . "--" . $row[$prefix . "ISBN"];
                    $titre = $row[$prefix . 'TITRE'];
                }
            } else if ($row[$prefix . 'TYPEPUB'] == '2') {
                $url = 'magazine-' . $row[$prefix . 'URL_REWRITING'];
                $titre = $row[$prefix . 'TITRE'];
            }
            if (in_array("NOM_AUTEUR", $arrayFieldsToDisplay) || in_array("NOM_AUTEUR-ANNEE", $arrayFieldsToDisplay)) {
                if (count(explode(',', $row[$prefix . "NOM"])) > 2) {
                    $etAl = " <em>et al.</em>";
                    $noms = explode(',', $row[$prefix . "NOM"]);
                    $nom = $noms[0];
                    //$nom = $row[$prefix."NOM"];
                } else {
                    $etAl = "";
                    $nom = $row[$prefix . "NOM"];
                }
            }
            $x++;
            if (($x % 2) == 0) {
                echo '<div class="grid-g grid-2-list">';
            }
            ?>
            <div class="grid-u-1-2 greybox_hover revue">
                <a  href="./<?= $url ?>.htm">
                    <img src="/<?= $vign_path ?>/<?= $row[$prefix . 'ID_REVUE'] ?>/<?= $row[$prefix . 'ID_NUMPUBLIE'] ?>_L62.jpg" class="small_cover">
                </a>
                <div class="meta">
                    <h2 class="title_little_blue numero_title"><a  href="./<?= $url ?>.htm"><?= $titre ?></a></h2>
                    <?php if (in_array("STITRE", $arrayFieldsToDisplay) && $row[$prefix . 'STITRE'] != '') { ?>
                    <p class="research-subtitle"><em><?= $row[$prefix . 'STITRE'] ?></em></p>
                    <?php } ?>
                    <?php if (in_array("PERIODICITE_EN", $arrayFieldsToDisplay) && $row[$prefix . 'PERIODICITE_EN'] != '') { ?>
                        <div><?= $row[$prefix . 'PERIODICITE_EN'] ?></div>
                    <?php } ?>
                    <?php if (in_array("ISSN", $arrayFieldsToDisplay) && $row[$prefix . 'ISSN'] != '') { ?>
                        <div>ISSN : <?= $row[$prefix . 'ISSN'] ?></div>
                    <?php } ?>
                    <?php if (in_array("ISSN_NUM", $arrayFieldsToDisplay) && $row[$prefix . 'ISSN_NUM'] != '') { ?>
                        <div>ISSN online : <?= $row[$prefix . 'ISSN_NUM'] ?></div>
                    <?php } ?>
                    <?php if (in_array("NOM_AUTEUR", $arrayFieldsToDisplay) && $row[$prefix . 'NOM'] != '') {
                        ?>
                        <h2 class='text_small title'><?= $nom . $etAl ?></h2>
                    <?php } ?>
                    <?php if (in_array("NOM_AUTEUR-ANNEE", $arrayFieldsToDisplay) && $row[$prefix . 'NOM'] != '' && $row[$prefix . 'ANNEE'] != '') { ?>
                        <div class="yellow-bold"><?= $nom . $etAl ?><b style="color: black"> - <?= $row[$prefix . "ANNEE"] ?></b></div>
                    <?php } ?>
                    <?php if (in_array("NOM_EDITEUR", $arrayFieldsToDisplay) && $row[$prefix . 'NOM_EDITEUR'] != '') { ?>
                        <div class="editeur"><?= $row[$prefix . 'NOM_EDITEUR'] ?></div>
                    <?php } ?>
                    <?php if (in_array("SAVOIR_PLUS_EN", $arrayFieldsToDisplay) && $row[$prefix . 'SAVOIR_PLUS_EN'] != '') { ?>
                        <p><?= tidy_repair_string($row[$prefix . 'SAVOIR_PLUS_EN'],array(),'utf8') ?></p>
                    <?php } ?>
                </div>
            </div>
            <?php
            if (($x % 2) == 1) {
                echo '</div>';
            }
        }
        if (($x % 2) == 0) {
            echo '</div>';
        }
        ?>
    </div>
    <?php
}
?>
