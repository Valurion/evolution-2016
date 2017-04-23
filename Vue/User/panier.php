<?php 
if($tmpCmdIdFrom == ''){
$this->titre = 'Mon panier';
if($gabarit != 'gabaritAjax.php'){
    include (__DIR__ . '/../CommonBlocs/tabs.php');
}
}
$totalPrice = 0;

// Stockage des revues présentes dans le panier
$arrayAllRevuesInCart = array();
?>
<div class="biblio mon-panier" id="body-content">
    <div id="breadcrump">
        <a class="inactive" href="/">Accueil</a>
        <span class="icon-breadcrump-arrow icon"></span>
        <a href="#">Mon Panier</a>
    </div>

    <div class="list_articles">
       <h1 class="main-title">Mon panier d'achat</h1>
        
        <div id="wrapper_breadcrumb_cart">
            <ol id="breadcrumb_cart">
                <li class="black_button"><span>1</span> Panier</li>
                <li><span>2</span> Coordonnées</li>
                <li><span>3</span> Méthode de paiement</li>
                <li><span>4</span> Paiement</li>
                <li><span>5</span> Accès</li>
            </ol>
        </div>

        <?php if(!empty($credits) || !empty($abos) || !empty($numRev) || !empty($numRevElec) || !empty($artOuv) || !empty($artRev) || !empty($artMag)) { ?>
            <!-- Calcul du nombre total d'élement dans la bibliographie -->
            <form>
                <input type="hidden" id="nbreTotalPanier" name="nbreTotalPanier" value="<?php echo count($artOuv) + count($numRev) + count($numRevElec) + count($artRev) + count($artMag) + count($abos) ; ?>" />
                <input type="hidden" id="nbreArtOuv" name="nbreArtOuv" value="<?php echo count($artOuv); ?>" />
                <input type="hidden" id="nbreNumRev" name="nbreNumRev" value="<?php echo count($numRev); ?>" />
                <input type="hidden" id="nbreNumRevElec" name="nbreNumRevElec" value="<?php echo count($numRevElec); ?>" />
                <input type="hidden" id="nbreArtRev" name="nbreArtRev" value="<?php echo count($artRev); ?>" />
                <input type="hidden" id="nbreArtMag" name="nbreArtMag" value="<?php echo count($artMag); ?>" />
                <input type="hidden" id="nbreCredits" name="nbreCredits" value="<?php echo count($credits); ?>" />
                <input type="hidden" id="nbreAbos" name="nbreAbos" value="<?php echo count($abos); ?>" />
            </form>
        <?php } ?>
        <!--<h2 class="subTitle">Vos achats</h2>-->

        <?php            
            // PANIER VIDE
            if(empty($credits) && empty($abos) && empty($numRev) && empty($numRevElec) && empty($artOuv) && empty($artRev) && empty($artMag)) {
                echo "<p><b>Votre panier est vide</b></p>";
                echo "<p><a class=\"link-underline\" href=\"http://aide.cairn.info/comment-acheter-des-articles-sur-cairn/\">Comment acheter des articles sur Cairn.info ?</a></p>";
            }
            // PANIER REMPLI
            else {
            
                if(!empty($credits)){ ?>
                    <div id="Credits" class="mt2">
                        <h2 class="section"><span>Crédits d'article</span></h2>
                        <?php 
                        foreach($credits as $credit){ 
                            $totalPrice += $credit['PRIX'];
                            ?>
                            <div id="<?=$credit['PRIX'] ?>" class="specs article greybox_hover">
                                <img class="small_cover" alt="couverture" src="./static/images/credit<?= $credit['PRIX'] ?>.png">
                                <div class="meta">
                                    <div class="title">
                                        <strong>Crédit d'articles</strong>
                                    </div>
                                    <div class="revue_title">
                                        Valable jusqu'au 31-12-<?= $credit['EXPIRE']?>
                                    </div>
                                    <div class="prix">
                                        <strong><span id="price-<?=$credit['PRIX'] ?>"><?= number_format($credit['PRIX'], 2, ",", "") ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <input type="image" class="icon del-panier" src="static/images/del.png" alt="Supprimer de votre sélection" 
                                               onclick="ajax.removeFromBasket('CREDIT','<?= $credit['PRIX'] ?>')"
                                               data-webtrends="removeFromCart-credit-article" 
                                               data-prix_credit_article="<?= $credit['PRIX'] ?>"
                                               >                        
                                    </div>                    
                                </div>
                            </div>            
                        <?php }?>
                    </div>
                <?php }?>

                <?php if(!empty($abos)){ ?>
                    <div id="Abos" class="mt2">
                        <h2 class="section"><span>Abonnements</span></h2>
                        <?php foreach($abos as $abo){ 
                            $totalPrice += $abo['ABO']['PRIX'];
                            ?>
                            <div id="<?=$abo['ABO']['ID_ABON'].'-'.$abo['ABO']['ID_REVUE'].'-'.(isset($abo["ANNEE"])?$abo["ANNEE"]:$abo["FIRSTNUM"]['NUMERO_ID_NUMPUBLIE'])?>" class="specs article greybox_hover">
                                <a href="revue-<?php echo $abo["ABO"]["URL_REWRITING"]; ?>.htm">
                                    <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $abo['ABO']['ID_REVUE'] ?>/<?= $abo['ABO']['ID_NUMPUBLIE'] ?>_L61.jpg">
                                </a>
                                <div class="meta">
                                    <div class="title">
                                        <a href="revue-<?php echo $abo["ABO"]["URL_REWRITING"]; ?>.htm">
                                            <strong><?= $abo['ABO']['TITRE'] ?> - <?= $abo['ABO']['LIBELLE'] ?></strong>
                                        </a>
                                    </div>
                                    <div class="revue_title">
                                        <?php
                                        if(isset($abo['ANNEE'])){
                                            echo 'Année '.$abo["ANNEE"].' ('.$abo["REVUE"]["PERIODICITE"].')';
                                        }else if(isset($abo['FIRSTNUM'])){
                                            echo 'À partir du numéro '.$abo['FIRSTNUM']['NUMERO_ANNEE']."/".$abo['FIRSTNUM']['NUMERO_NUMERO'];
                                        }
                                        ?>
                                        <div class="format-revue">Format papier + électronique<br />(HTML et PDF)</div>
                                    </div>
                                    <div class="prix">
                                        <strong><span id="price-<?=$abo['ABO']['ID_ABON']?>-<?=$abo['ABO']['ID_REVUE'].'-'.(isset($abo["ANNEE"])?$abo["ANNEE"]:$abo["FIRSTNUM"]['NUMERO_ID_NUMPUBLIE'])?>"><?= number_format($abo['ABO']['PRIX'], 2, ",", "") ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <a
                                            id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                                            href="javascript:void(0);"
                                            class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                                            data-webtrends="removeFromCart-revue" 
                                            data-id_revue="<?= $abo['ABO']['ID_REVUE'] ?>" 
                                            data-id_numero="<?= $abo["FIRSTNUM"]['NUMERO_ID_NUMPUBLIE'] ?>" 
                                            data-prix_revue="<?= $abo['ABO']['PRIX'] ?>"
                                            data-titre=<?= Service::get('ParseDatas')->cleanAttributeString($abo['ABO']['TITRE'])?>
                                            onclick="ajax.removeFromBasket('ABO','<?= $abo['ABO']['ID_ABON'] ?>','<?= $abo['ABO']['ID_REVUE'] ?>','<?=(isset($abo["ANNEE"])?$abo["ANNEE"]:$abo["FIRSTNUM"]['NUMERO_ID_NUMPUBLIE'])?>')"
                                        >
                                        <label><span>Supprimer du panier</span></label>
                                        </a>
                                    </div>                    
                                </div>
                            </div>            
                        <?php }?>
                    </div>
                <?php }?>

                <?php if(!empty($numRev) || !empty($numRevElec)){ ?>
                    <div id="NumRev" class="mt2">
                        <h2 class="section"><span>Numéros de revues</span></h2>
                        <?php foreach($numRev as $rev){ 
                            $arrayAllRevuesInCart[] = $rev['NUMERO_ID_REVUE'];
                            $totalPrice += $rev['NUMERO_PRIX'];
                            ?>
                            <div id="<?=$rev['NUMERO_ID_NUMPUBLIE']?>" class="specs article greybox_hover">
                                <a href="revue-<?php echo $rev["REVUE_URL_REWRITING"]; ?>-<?php echo $rev["NUMERO_ANNEE"]; ?>-<?php echo $rev["NUMERO_NUMERO"]; ?>.htm">
                                    <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $rev['NUMERO_ID_REVUE'] ?>/<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>_L61.jpg">
                                </a>

                                <div class="meta">
                                    <div class="title">
                                        <a href="revue-<?php echo $rev["REVUE_URL_REWRITING"]; ?>-<?php echo $rev["NUMERO_ANNEE"]; ?>-<?php echo $rev["NUMERO_NUMERO"]; ?>.htm">
                                            <strong><?= $rev['NUMERO_TITRE'] ?></strong>
                                        </a>
                                    </div>
                                    <div class="revue_title">
                                        <span class="title_little_blue"><a href="revue-<?php echo $rev["REVUE_URL_REWRITING"]; ?>.htm"><?= $rev['REVUE_TITRE'] ?></a></span> <strong><?= $rev['NUMERO_ANNEE'] ?>/<?= $rev['NUMERO_NUMERO'] ?>
                                            <!--(n° 249) -->
                                        </strong>
                                        <div class="format-revue">Format papier + électronique<br />(HTML et PDF)</div>
                                    </div>

                                    <div class="prix">
                                        <strong><span id="price-<?=$rev['NUMERO_ID_NUMPUBLIE']?>"><?= number_format($rev['NUMERO_PRIX'], 2, ",", "") ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <a
                                            id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                                            href="javascript:void(0);"
                                            class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                                            onclick="ajax.removeFromBasket('NUM','<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>')"
                                            data-webtrends="removeFromCart-numero" 
                                            data-id_numero="<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>" 
                                            data-prix_numero="<?= $rev['NUMERO_PRIX'] ?>" 
                                            data-titre=<?= Service::get('ParseDatas')->cleanAttributeString($rev['REVUE_TITRE'])?>
                                        >
                                        <label><span>Supprimer du panier</span></label>
                                        </a>                   
                                    </div>
                                </div>
                            </div>
                        <?php }?>
                    </div>
                    
                    <div id="NumRevElec" class="">
                        <?php foreach($numRevElec as $rev){ 
                            $arrayAllRevuesInCart[] = $rev['NUMERO_ID_REVUE'];
                            $totalPrice += $rev['NUMERO_PRIX_ELEC'];
                            ?>
                            <div id="ELEC-<?=$rev['NUMERO_ID_NUMPUBLIE']?>" class="specs article greybox_hover">
                                <a href="revue-<?php echo $rev["REVUE_URL_REWRITING"]; ?>-<?php echo $rev["NUMERO_ANNEE"]; ?>-<?php echo $rev["NUMERO_NUMERO"]; ?>.htm">
                                    <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $rev['NUMERO_ID_REVUE'] ?>/<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>_L61.jpg">
                                </a>

                                <div class="meta">
                                    <div class="title">
                                        <a href="revue-<?php echo $rev["REVUE_URL_REWRITING"]; ?>-<?php echo $rev["NUMERO_ANNEE"]; ?>-<?php echo $rev["NUMERO_NUMERO"]; ?>.htm">
                                            <strong><?= $rev['NUMERO_TITRE'] ?></strong>
                                        </a>
                                    </div>
                                    <div class="revue_title">
                                        <span class="title_little_blue"><a href="revue-<?php echo $rev["REVUE_URL_REWRITING"]; ?>.htm"><?= $rev['REVUE_TITRE'] ?></a></span> <strong><?= $rev['NUMERO_ANNEE'] ?>/<?= $rev['NUMERO_NUMERO'] ?>
                                            <!--(n° 249) -->
                                        </strong>
                                        <div class="format-revue">Format électronique<br />(HTML et PDF)</div>
                                    </div>

                                    <div class="prix">
                                        <strong><span id="price-ELEC-<?=$rev['NUMERO_ID_NUMPUBLIE']?>"><?= number_format($rev['NUMERO_PRIX_ELEC'], 2, ",", "") ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <a
                                            id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                                            href="javascript:void(0);"
                                            class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                                            onclick="ajax.removeFromBasket('NUM','ELEC','<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>')"
                                            data-webtrends="removeFromCart-numero" 
                                            data-id_numero="<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>" 
                                            data-prix_numero="<?= $rev['NUMERO_PRIX_ELEC'] ?>"
                                            data-titre=<?= Service::get('ParseDatas')->cleanAttributeString($rev['REVUE_TITRE'])?>
                                        >
                                        <label><span>Supprimer du panier</span></label>
                                        </a>                      
                                    </div>
                                </div>

                            </div>
                        <?php }?>
                    </div>
                <?php }?>
                
                <?php if(!empty($artOuv)){ ?>
                    <div id="ArtOuv" class="mt2">
                        <h2 class="section"><span>Contributions d’ouvrages</span></h2>
                        <?php 
                            foreach($artOuv as $art){
                                $totalPrice += $art['ARTICLE_PRIX'];
                            }
                            $groupByImage = 1;
                            $arrayForList = $artOuv;
                            $currentPage = 'contrib';
                            $arrayFieldsToDisplay = array('ID', 'PRIX', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET', 'FORMAT');
                            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                        ?>
                    </div>
                <?php } ?>

                <?php if(!empty($artRev)){ ?>
                    <div id="ArtRev" class="mt2">
                        <h2 class="section">
                            <span>Articles de revues</span>
                        </h2>
                        <?php 
                            foreach($artRev as $art) {
                                $totalPrice += $art['ARTICLE_PRIX'];
                            }
                            $groupByImage = 1;
                            $arrayForList = $artRev;
                            $currentPage = 'contrib';
                            $arrayFieldsToDisplay = array('ID', 'PRIX', 'REVUE_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET', 'BUY_NUMERO_ELEC', 'FORMAT');
                            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                        ?>
                    </div>
                <?php } ?>
            
                <?php if(!empty($artMag)){ ?>
                    <div id="ArtMag" class="mt2">
                        <h2 class="section">
                            <span>Articles de magazines</span>
                        </h2>
                        <?php 
                            foreach($artMag as $art){
                                $totalPrice += $art['ARTICLE_PRIX'];
                            }
                            $groupByImage = 1;
                            $arrayForList = $artMag;
                            $currentPage = 'contrib';
                            $arrayFieldsToDisplay = array('ID', 'PRIX', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET', 'FORMAT');
                            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                        ?>
                    </div>
                <?php } ?>            
                
                <!-- Affichage du prix total -->
                <div class="prixtotal">
                    <span class="label">TOTAL :</span>
                    <span class="price">
                        <span id="totalPrice"><?= number_format($totalPrice, 2, ",", "") ?></span> €
                    </span>
                </div>

                <div class="checkout-bottom-section">
                    <?php if($tmpCmdIdFrom != ''){?>
                    <input type="hidden" id="tmpCmdIdFrom" value="<?= $tmpCmdIdFrom ?>"/>   
                    <?php } ?>
                    
                    <?php if($totalPrice > 0) { ?>
                        <a class="continuer checkout-button" href="javascript:ajax.panierAchat()">Suivant</a>
                        <?php 
                            if($returnLink != null){
                                //echo '<a class="payer checkin-button" href="./'.$returnLink.'">Retour à la page</a>';
                                echo '<a class="payer checkin-button" href="javascript:history.back()">Retour à la page</a>';
                            }
                        ?>                
                    <?php } ?>
                </div>
        <?php } ?>
    <br>
</div>


<?php if($addCartError != null) { ?>
<!-- Modal Erreur lors de l'ajout d'un article présent dans un numéro sauvegardé -->
<div style="display: block;" class="window_modal" id="modal_error_addCart">
    <div class="info_modal">
        <h1>Attention !</h1>
        <p>
            Le numéro de la revue dans laquelle se trouve cet article se trouve déjà dans votre panier. <br />
            Merci de supprimer le numéro du panier si vous ne souhaitez acheter que cet article.
        </p>
        <div class="buttons">
            <a href="./mon_panier.php" class="blue_button ok">Fermer</a>
        </div>
    </div>
</div>

<?php $this->javascripts[] = <<<'EOD'
    $(function() {
        // Déclenchement du modal
        cairn.show_modal('#modal_error_addCart');
    });
EOD;
?>

<?php } ?>


