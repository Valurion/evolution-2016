<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */

/* La partie OUVRAGES ne fonctionne pas tout à fait de la même manière que les REVUES.
 * Dans les revues, un fichier (disciplines.php) existe pour afficher le résultat du choix effectue dans le tableau des disciplines.
 * Dans les ouvrages, c'est le même fichier ouvrages.php qui est appelée sous un autre nom (ouvrages-en-xxx). Il faut donc modifier ce
 * fichier en fonction de la présence où non d'une valeur de discipline (ID) */
?>
<?php
$typePub = 'ouvrage';
include (__DIR__ . '/../CommonBlocs/tabs.php');
$curDisc = array();
$this->titre = "Ouvrages";

/*if (isset($currentDiscipline['sub-discipline'])) {
    $this->titre = ucfirst($currentDiscipline['sub-discipline']) . ' - ' . $this->titre;
} elseif (isset($currentDiscipline['discipline'])) {
    $this->titre = ucfirst($currentDiscipline['discipline']) . ' - ' . $this->titre;
}
if(!isset($curDisciplinePosRoot)){
    $curDisciplinePosRoot = -1;
}*/

$arrayExcludeDisc = array();
if(isset($authInfos['I']) && $authInfos['I']['PARAM_INST'] !== false && isset($authInfos['I']['PARAM_INST']['D'])){
   $arrayExcludeDisc = explode(',', $authInfos['I']['PARAM_INST']['D']);
}
?>


<?php
    //$key = array_search($currentDiscipline['discipline'], array_column($disciplines, 'DISCIPLINE'));

    //echo "KEY: ".$key."<br />";
?>

<!-- Breadcrumb -->
<?php if(isset($disciplinePos)) { ?>
    <div id="breadcrump">
        <a class="inactive" href="./">Accueil</a>
        <span class="icon-breadcrump-arrow icon"></span>
        <a class="inactive" href="ouvrages.php">Ouvrages</a>

        <?php if($currentSousDiscipline != "") { ?>
            <span class="icon-breadcrump-arrow icon"></span>
            <a class="inactive" href="ouvrages-en-<?php echo $currentDiscipline['URL_REWRITING']; ?>.htm"><?php echo ucfirst($currentDiscipline['discipline']); ?></a>
            <span class="icon-breadcrump-arrow icon"></span>
            <a href="#"><?php echo ucfirst($currentSousDiscipline['DISCIPLINE']); ?></a>
        <?php } else { ?>
            <span class="icon-breadcrump-arrow icon"></span>
            <a href="#"><?php echo ucfirst($currentDiscipline['discipline']); ?></a>
        <?php } ?>
    </div>
<?php } ?>

<div id="body-content" class="list-ouvrages">

    <!-- DISCIPLINES -->
    <div id="liste-discipline">
        <h1 class="main-title mt2"><?php echo number_format($countOuvrages, 0, '', ' '); ?> ouvrages <?php if(isset($disciplinePos) && $currentSousDiscipline == "") {echo "en ".strtolower($currentDiscipline['discipline']);} if(isset($disciplinePos) && $currentSousDiscipline != "") {echo "en ".strtolower($currentSousDiscipline['DISCIPLINE']);} ?> <span class=""><a class="btn" href="liste-des-ouvrages.php<?php if(isset($disciplinePos)) { echo "?editeur=&discipline=".$disciplinePos; } ?>">Voir la liste</a></span></h1>

        <!-- Tableau des disciplines -->
        <?php if(!isset($disciplinePos)) { ?>
            <div class="table-button-grey mt2 clearfix">
                <?php
                    // Init
                    $item       = "";
                    $nbreColumn = 5;
                    $nbreDisc   = count($disciplines);
                    $ratio      = ceil($nbreDisc / $nbreColumn);

                    // Liste des disciplines
                    $i = 1;
                    foreach ($disciplines as $discipline) {
                        // Affichage de la discipline
                        // Active
                        if(!in_array($discipline['POS_DISC'], $arrayExcludeDisc)) {
                            // Discipline active
                            if($curDiscipline == $discipline['URL_REWRITING']) {$activeClass = "active";} else {$activeClass = "";}

                            // Lien actif avec ou sans class supplémentaire
                            $item .= "<span class=\"grid-item cell\"><a class=\"$activeClass\" href=\"./ouvrages-en-".$discipline['URL_REWRITING'].".htm\">".$discipline["DISCIPLINE"]."</a></span>";
                        }
                        // Inactive
                        else {
                            // Lien désactivé
                            $item .= "<span class=\"grid-item cell\"><a class=\"inactive\" href=\"javascript:void(0);\">".$discipline["DISCIPLINE"]."</a></span>";
                        }

                        // Nouvelle colonne
                        if($i % $ratio == 0) {$item .= "</div><div class=\"grid-column\">";}
                        $i++;
                    }

                    // Rendu HTML
                    echo "<div class=\"grid-column\">".$item."</div>";
                ?>
            </div>
        <?php } ?>

        <!-- Tableau des Sous-disciplines -->
        <?php if((isset($disciplinePos)) && ($sousdisciplines)) { ?>
            <div class="table-button-grey table-subdisc mt2 clearfix">
                <?php
                    // Init
                    $item       = "";
                    $nbreColumn = 3;
                    $nbreDisc   = count($sousdisciplines);
                    $ratio      = ceil($nbreDisc / $nbreColumn);

                    // Liste des disciplines
                    $i = 1;
                    foreach ($sousdisciplines as $sousdiscipline) {
                        // Affichage de la discipline
                        // Active
                        if(!in_array($sousdiscipline['POS_DISC'], $arrayExcludeDisc)) {
                            // Lien actif avec ou sans class supplémentaire
                            $item .= "<span class=\"grid-item cell\"><a class=\"$activeClass\" href=\"./ouvrages-en-".$sousdiscipline['URL_REWRITING'].".htm\">".$sousdiscipline["DISCIPLINE"]."</a></span>";
                            //$item .= "<span class=\"grid-item cell\"><a class=\"$activeClass\" href=\"./liste-des-ouvrages.php?discipline=".$disciplinePos."&sousdiscipline=".$sousdiscipline['POS_DISC']."\">".$sousdiscipline["DISCIPLINE"]."</a></span>";
                        }
                        // Inactive
                        else {
                            // Lien désactivé
                            $item .= "<span class=\"grid-item cell\"><a class=\"inactive\" href=\"javascript:void(0);\">".$sousdiscipline["DISCIPLINE"]."</a></span>";
                        }

                        // Nouvelle colonne
                        if($i % $ratio == 0) {$item .= "</div><div class=\"grid-column\">";}
                        $i++;
                    }

                    // Rendu HTML
                    echo "<div class=\"grid-column\">".$item."</div>";
                ?>
            </div>
        <?php } ?>
    </div>

    <!-- NUMEROS RECENTS -->
    <div id="numeros-recents">
        <h1 class="main-title mt2">Ouvrages récemment ajoutés</h1>

        <!-- Set up your HTML -->
        <div class="owl-carousel owl-numeros-recents">
            <?php
                // Init
                $item = "";

                // Boucle
                foreach ($lastpubs as $lastpub) {
                    $item .= "<div class=\"owl-item\">
                                <a href=\"".$this->nettoyer($lastpub['URL_REWRITING'])."-".$this->nettoyer($lastpub['NUMERO'])."-".$this->nettoyer($lastpub['ISBN']).".htm\">
                                    <img src=\"/".$vign_path."/".$this->nettoyer($lastpub['ID_REVUE'])."/".$this->nettoyer($lastpub['ID_NUMPUBLIE'])."_L204.jpg\" alt=\"couverture de ".$lastpub['ID_NUMPUBLIE']."\" />
                                </a>
                                <h2>".$this->nettoyer($lastpub['NUMERO_TITRE_ABREGE'])."</h2>
                                <p>".Service::get('ParseDatas')->stringifyRawAuthors($lastpub['NOM'], 2, null, null, null, false)."</p>
                              </div>";
               }
               echo $item;
            ?>
        </div>
    </div>

    <!-- Ouvrages les plus consultés -->
    <?php if($mostconsultated != null): ?>
        <hr class="grey"/>
        <div id="articles_more_view" class="list_articles clearfix">
            <h1 class="main-title">
                Ouvrages les plus consultés
                <?php if ($currentSousDiscipline != ''): ?>
                    en <?= strtolower($currentSousDiscipline['DISCIPLINE']) ?>
                <?php endif; ?>
                <?php if ($currentSousDiscipline == '' && $currentDiscipline['discipline'] != ''): ?>
                    en <?= strtolower($currentDiscipline['discipline']) ?>
                <?php endif; ?>
            </h1>

            <!-- Liste des articles -->
            <?php foreach ($mostconsultated as $most): ?>
                <?php $lien = $most["NUMERO_URL_REWRITING"]."--".$most["NUMERO_ISBN"].".htm"; ?>
                <div class="media media-2 greybox_hover">
                    <div class="media-left">
                        <a href="<?= $lien ?>">
                            <img class="small_cover" src="<?= '/'.$vign_path.'/'.$most["ID_REVUE"].'/'.$most["ID_NUMPUBLIE"].'_L61.jpg' ?>" alt='<?= $most["ID_NUMPUBLIE"] ?>'>
                        </a>
                    </div>
                    <div class="media-body">
                        <div class="titre" style="color: #323232;">
                            <a href="<?= $lien ?>">
                                <?= $most['NUMERO_TITRE'] ?>
                            </a>
                        </div>
                        <?php if ($most['NUMERO_SOUSTITRE'] != ''): ?>
                            <div class="sous-titre" style="margin-bottom: 5px;color: #323232;">
                                <?= $most['NUMERO_SOUSTITRE'] ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($most['NUMERO_AUTEUR'] != ''): ?>
                            <div class="auteur" style="margin-top: 0;color: #a5a524;">
                                <?= $most['NUMERO_AUTEUR'] ?>
                            </div>
                        <?php endif; ?>
                        <?php if (Configuration::get('allow_backoffice', false)): ?>
                            <div class="bo-content"><?= $most['NUMERO_NB_CONSULTATIONS'] ?> consultations</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Qu'est-ce que Cairn ? -->
<?php 
    if(!isset($disciplinePos)) {
        include_once(__DIR__.'/../CommonBlocs/questcequecairn.php');
    }
?>

<?php
// Permet de remonter l'accès à toutes les revues dans la grille, sur le dernier item vide.
$this->javascripts[] = <<<'EOD'
    $(function() {
        var $empty = $("#body-content .empty:last");
        var $all_collec = $(".__all_collection");
        if (!$empty.length || !$all_collec.length)
            return false;
        $empty[0].outerHTML = $all_collec[0].outerHTML;
        $all_collec.parent().parent().hide();
    });
EOD;

// On positionne la flèche svg  au centre de la discipline parente.
$this->javascripts[] = <<<'EOD'
    $(function() {
        var $svg, $disc, disc_posX, left_x;
        $svg = $('#sub_disc_arrow');
        if (!$svg.length) return;
        $disc = $('#__ACTIVE_DISCIPLINE');
        if (!$disc.length || !$disc.length)
            return false;
        $svg.show();
        disc_posX = $svg.parent().offset().left;
        left_x = ($disc.offset().left - disc_posX) + ($disc.outerWidth() / 2) - ($svg.outerWidth() / 2);
        $svg.css('left', left_x);
    });

    $('.owl-carousel').owlCarousel({
        items : 5,
        slideBy: 5,
        loop : true,
        nav : true,
        margin : 20,
        mouseDrag : false
    });
EOD;

?>
