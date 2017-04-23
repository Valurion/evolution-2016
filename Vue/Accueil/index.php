<?php
/**
 * Dedicated View [Coupled with the default method of the controler]
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "Revues et ouvrages en sciences humaines et sociales";
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');


$arrayExcludeDisc = array();
if(isset($authInfos['I']) && isset($authInfos['I']['PARAM_INST']) && $authInfos['I']['PARAM_INST'] !== false && isset($authInfos['I']['PARAM_INST']['D'])){
   $arrayExcludeDisc = explode(',', $authInfos['I']['PARAM_INST']['D']);
}

/*
 * Modification septembre 2016 : la page d'accueil peut être appelée avec un paramètre shib=1
 * Cela permet de déclencher une authentification sur la CorsUrl au retour d'un login Shibboleth...
 */

if(isset($corsURL) && isset($token) && $token != null){
    echo '<img style="display:none;" src="http://'.$corsURL.'/index.php?controleur=User&action=loginCorsShib&token='.urlencode($token).'"/>';
}

?>
<div id="body-content">

    <!-- DISCIPLINES -->
    <div id="liste-discipline">
        <h1 class="main-title mt2"><?php echo $countRevues; ?> revues <span class=""><a class="btn" href="listerev.php">Voir la liste</a></span></h1>

        <!-- Tableau des disciplines -->
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
                        $item .= "<span class=\"grid-item cell\"><a class=\"$activeClass\" href=\"./disc-".$discipline['URL_REWRITING'].".htm\">".$discipline["DISCIPLINE"]."</a></span>";
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
    </div>

    <!-- NUMEROS RECENTS -->
    <div id="numeros-recents">
        <h1 class="main-title mt1">Numéros récemment ajoutés</h1>

        <!-- Set up your HTML -->
        <div class="owl-carousel owl-numeros-recents mt2">
            <?php
                // Init
                $item = "";

                // Boucle
                foreach ($lastpubs as $lastpub) {
                    $item .= "<div class=\"owl-item\">
                                <a href=\"revue-".$this->nettoyer($lastpub['URL_REWRITING'])."-".$this->nettoyer($lastpub['ANNEE'])."-".$this->nettoyer($lastpub['NUMERO']).".htm\">
                                    <img src=\"/".$vign_path."/".$this->nettoyer($lastpub['ID_REVUE'])."/".$this->nettoyer($lastpub['ID_NUMPUBLIE'])."_L204.jpg\" alt=\"couverture de ".$lastpub['ID_NUMPUBLIE']."\" />
                                </a>
                                <h2>".$this->nettoyer($lastpub['TITRE_ABREGE'])."</h2>
                                <h3>".$this->nettoyer(strip_tags($lastpub['TITRE']))."</h3>
                                <p>".$this->nettoyer($lastpub['ANNEE'])."/".$this->nettoyer($lastpub['NUMERO'])." ".$this->nettoyer($lastpub['VOLUME'])."</p>
                              </div>";
               }
               echo $item;
            ?>
        </div>
    </div>

    <!-- ACTUALITE -->
    <?php if(count($lastNews) != 0) { ?>
    <hr class="grey" />
    <div id="news">
        <h1 class="main-title mt1">Actualité</h1>
        <h2 class="title_big_blue text-center">
            <?php 
                $dates = explode("-", $lastNews[0]["DATE_ACTU"]);
                $date  = $dates[2]." ".Service::get("ParseDatas")->getMonthLabel($dates[1], $lastNews["LANG"])." ".$dates[0];
            ?>
            <?= $date ?>
        </h2>
        
        <div class="media media-news">
            <?php if($lastNews[0]["IMAGE"] != "") {?>
            <!-- Image -->
            <div class="media-left">
                <img src="static/images/news/<?= $lastNews[0]["IMAGE"]; ?>">
            </div>
            <?php } ?>
            <!-- Corps de l'actualité -->
            <div class="media-body">
                <h3><?= $lastNews[0]["TITRE"]; ?></h3>
                <?= nl2br($lastNews[0]["DESCRIPTION"]); ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<!-- Qu'est-ce que Cairn ? -->
<?php include_once(__DIR__.'/../CommonBlocs/questcequecairn.php'); ?>

<?php $this->javascripts[] = <<<'EOD'
    $(function() {
        // Permet de remonter l'accès à toutes les revues dans la grille, sur le dernier item vide.
        var $empty = $("#body-content .empty:last");
        var $all_collec = $(".__all_collection");
        if (!$empty.length || !$all_collec.length)
            return false;
        $empty[0].outerHTML = $all_collec[0].outerHTML;
        $all_collec.parent().parent().hide();
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
