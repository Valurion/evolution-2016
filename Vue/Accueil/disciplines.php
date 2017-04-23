<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
$this->titre = ucfirst($currentDiscipline['discipline']) . " - Revues";

$arrayExcludeDisc = array();
if(isset($authInfos['I']) && $authInfos['I']['PARAM_INST'] !== false && isset($authInfos['I']['PARAM_INST']['D'])){
   $arrayExcludeDisc = explode(',', $authInfos['I']['PARAM_INST']['D']);
}
?>

<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="Accueil_Revues.php">Revues</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#"><?php echo ucfirst($currentDiscipline['discipline']); ?></a>
</div>

<div id="body-content">
    <h1 class="main-title"><?php echo $countRevues; ?> revues en <?php echo strtolower($currentDiscipline['discipline']); ?> <span class=""><a class="btn" href="listerev.php?editeur=&discipline=<?php echo $curDisciplinePos; ?>">Voir la liste</a></span></h1>
    
    <!-- NUMEROS RECENTS -->
    <div id="numeros-recents">
        <h1 class="main-title mt2">Numéros récemment ajoutés</h1>

        <!-- Set up your HTML -->
        <div class="owl-carousel owl-numeros-recents">
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

    <!-- ARTICLES LES PLUS CONSULTES -->
    <?php if($mostconsultated != null) { ?> 
        <hr class="grey"/>  
        <div id="articles_more_view" class="list_articles clearfix">
            <h1 class="main-title">Articles les plus consultés</h1>

            <!-- Liste des articles -->
            <?php 
                // Init
                $item = "";

                // Boucle
                foreach ($mostconsultated as $most) {
                    
                    // Contenu
                    $itemContent = "";
                    if($most['TITRE'] != "") {$itemContent .= "<div class=\"titre\"><a href=\"revue-".$most['URL_REWRITING']."-".$most['ANNEE']."-".$most['NUMERO']."-page-".$most['PAGE_DEBUT'].".htm\">".$most['TITRE']."</a></div>";}
                    if($most['SOUSTITRE'] != "") {$itemContent .= "<div class=\"sous-titre\">".$most['SOUSTITRE']."</div>";}
                    if($most['AUTEUR'] != "") {$itemContent .= "<div class=\"auteur\">".$most['AUTEUR']."</div>";}

                    // Canevas
                    $item .= "<div class=\"media media-2 greybox_hover\">
                                <div class=\"media-left\">
                                    <a href=\"revue-".$most['URL_REWRITING']."-".$most['ANNEE']."-".$most['NUMERO']."-page-".$most['PAGE_DEBUT'].".htm\">
                                        <img class=\"small_cover\" src=\"/".$vign_path."/".$most["ID_REVUE"]."/".$most["ID_NUMPUBLIE"]."_L61.jpg\" alt=\"".$most["ID_NUMPUBLIE"]."\">
                                    </a>
                                </div>
                                <div class=\"media-body\">
                                    ".$itemContent."
                                </div>
                              </div>";
                }
                echo $item;
            ?>
        </div>
    <?php } ?>
</div>


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
