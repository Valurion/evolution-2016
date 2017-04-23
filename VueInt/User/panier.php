<?php 
if($tmpCmdIdFrom == ''){
$this->titre = 'My cart';
if($gabarit != 'gabaritAjax.php'){
    include (__DIR__ . '/../CommonBlocs/tabs.php');
}
}
$totalPrice = 0;
?>

<div class="biblio mon-panier" id="body-content">
    <div class="list_articles">
       <h1 class="main-title">My cart</h1>
        <hr class="grey">
        <div class="Clearfix"></div>
        <div id="wrapper_breadcrumb_cart">
            <ol id="breadcrumb_cart">
                <li class="black_button">My cart</li>
                <li>Billing Address</li>
                <li>Payment method</li>
                <li>Payment</li>
                <li>Get Access</li>
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


        <?php            
            // PANIER VIDE
            if(empty($credits) && empty($abos) && empty($numRev) && empty($numRevElec) && empty($artOuv) && empty($artRev) && empty($artMag)) {
                echo "<p><b>Your shopping cart is empty</b></p>";
                echo "<p>Click on one of the Add to cart links on article abstract pages or click <a class=\"link-underline\" href=\"http://www.cairn-int.info/help.php\">here</a> to see support resources for Cairn international edition</p>";
            }
            // PANIER REMPLI
            else {
                if(!empty($credits)){ ?>
                    <div id="Credits" class="mt2">
                        <h2 class="section"><span>Article credits</span></h2>
                        <?php 
                        foreach($credits as $credit){ 
                            $totalPrice += $credit['PRIX'];
                            ?>
                            <div id="<?=$credit['PRIX'] ?>" class="specs article greybox_hover">
                                <img class="small_cover" alt="couverture" src="./static/images/credit<?= $credit['PRIX'] ?>.png">
                                <div class="meta">
                                    <div class="title">
                                        <strong>Article credits</strong>
                                    </div>
                                    <div class="revue_title">
                                        Valid until 31-12-<?= $credit['EXPIRE']?>
                                    </div>
                                    <div class="prix">
                                        <strong><span id="price-<?=$credit['PRIX'] ?>"><?= $credit['PRIX'] ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <input type="image" class="icon del-panier" src="static/images/del.png" alt="Supprimer de votre sélection" onclick="ajax.removeFromBasket('CREDIT','<?= $credit['PRIX'] ?>')">                        
                                    </div>                    
                                </div>
                            </div>            
                        <?php }?>
                    </div>
                <?php }?>

                

                <?php if(!empty($abos)){ ?>
                    <div id="Abos" class="mt2">
                        <h2 class="section"><span>Subscriptions</span></h2>
                        <?php foreach($abos as $abo){ 
                            $totalPrice += $abo['ABO']['PRIX'];
                            ?>
                            <div id="<?=$abo['ABO']['ID_ABON'].'-'.$abo['ABO']['ID_REVUE'].'-'.(isset($abo["ANNEE"])?$abo["ANNEE"]:$abo["FIRSTNUM"]['NUMERO_ID_NUMPUBLIE'])?>" class="specs article greybox_hover">
                                <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $abo['ABO']['ID_REVUE'] ?>/<?= $abo['ABO']['ID_NUMPUBLIE'] ?>_L61.jpg">
                                <div class="meta">
                                    <div class="title">
                                        <strong><?= $abo['ABO']['TITRE'] ?> - <?= $abo['ABO']['LIBELLE'] ?></strong>
                                    </div>
                                    <div class="revue_title">
                                        <?php
                                        if(isset($abo['ANNEE'])){
                                            echo 'Year '.$abo["ANNEE"].' ('.$abo["REVUE"]["PERIODICITE"].')';
                                        }else if(isset($abo['FIRSTNUM'])){
                                            echo 'From number '.$abo['FIRSTNUM']['NUMERO_ANNEE']."/".$abo['FIRSTNUM']['NUMERO_NUMERO'];
                                        }
                                        ?>
                                    </div>
                                    <div class="prix">
                                        <strong><span id="price-<?=$abo['ABO']['ID_ABON']?>-<?=$abo['ABO']['ID_REVUE'].'-'.(isset($abo["ANNEE"])?$abo["ANNEE"]:$abo["FIRSTNUM"]['NUMERO_ID_NUMPUBLIE'])?>"><?= $abo['ABO']['PRIX'] ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <input type="image" class="icon del-panier" src="static/images/del.png" alt="Supprimer de votre sélection" onclick="ajax.removeFromBasket('ABO','<?= $abo['ABO']['ID_ABON'] ?>','<?= $abo['ABO']['ID_REVUE'] ?>','<?=(isset($abo["ANNEE"])?$abo["ANNEE"]:$abo["FIRSTNUM"]['NUMERO_ID_NUMPUBLIE'])?>')">                        
                                    </div>                    
                                </div>
                            </div>            
                        <?php }?>
                    </div>
                <?php }?>

                <?php if(!empty($numRev) || !empty($numRevElec)){ ?>
                    <div id="NumRev" class="mt2">
                        <h2 class="section"><span>Journals editions</span></h2>
                        <?php foreach($numRev as $rev){ 
                            $totalPrice += $rev['NUMERO_PRIX'];
                            ?>
                            <div id="<?=$rev['NUMERO_ID_NUMPUBLIE']?>" class="specs article greybox_hover">
                                <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $rev['NUMERO_ID_REVUE'] ?>/<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>_L61.jpg">

                                <div class="meta">
                                    <div class="title">
                                        <strong><?= $rev['NUMERO_TITRE'] ?></strong>
                                    </div>
                                    <div class="revue_title">
                                        <span class="title_little_blue"><?= $rev['REVUE_TITRE'] ?></span> <strong><?= $rev['NUMERO_ANNEE'] ?>/<?= $rev['NUMERO_NUMERO'] ?>
                                            <!--(n° 249) -->
                                        </strong>
                                    </div>

                                    <div class="prix">
                                        <strong><span id="price-<?=$rev['NUMERO_ID_NUMPUBLIE']?>"><?= $rev['NUMERO_PRIX'] ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <input type="image" class="icon del-panier" src="static/images/del.png" alt="Supprimer de votre sélection" onclick="ajax.removeFromBasket('NUM','<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>')">                        
                                    </div>
                                </div>
                            </div>
                        <?php }?>
                    </div>
                    
                    <div id="NumRevElec" class="">
                        <?php foreach($numRevElec as $rev){ 
                            $totalPrice += $rev['NUMERO_PRIX_ELEC'];
                            ?>
                            <div id="ELEC-<?=$rev['NUMERO_ID_NUMPUBLIE']?>" class="specs article greybox_hover">
                                <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $rev['NUMERO_ID_REVUE'] ?>/<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>_L61.jpg">

                                <div class="meta">
                                    <div class="title">
                                        <strong><?= $rev['NUMERO_TITRE'] ?></strong>
                                    </div>
                                    <div class="revue_title">
                                        <span class="title_little_blue"><?= $rev['REVUE_TITRE'] ?></span> <strong><?= $rev['NUMERO_ANNEE'] ?>/<?= $rev['NUMERO_NUMERO'] ?>
                                            <!--(n° 249) -->
                                        </strong>
                                        <br/><br/>
                                        <strong>electronic format</strong>
                                    </div>

                                    <div class="prix">
                                        <strong><span id="price-ELEC-<?=$rev['NUMERO_ID_NUMPUBLIE']?>"><?= $rev['NUMERO_PRIX_ELEC'] ?></span> €</strong>
                                    </div>
                                    <div class="state">
                                        <input type="image" class="icon del-panier" src="static/images/del.png" alt="Supprimer de votre sélection" onclick="ajax.removeFromBasket('NUM','ELEC','<?= $rev['NUMERO_ID_NUMPUBLIE'] ?>')">                        
                                    </div>
                                </div>

                            </div>
                        <?php }?>
                    </div>
                <?php }?>
                
                <?php if(!empty($artOuv)){ ?>
                    <div id="ArtOuv" class="mt2">
                        <h2 class="section">
                            <span>Books contribution</span>
                        </h2>
                        <?php 
                            foreach($artOuv as $art){
                                $totalPrice += $art['ARTICLE_PRIX'];
                            }
                            $arrayForList = $artOuv;
                            $currentPage = 'contrib';
                            $arrayFieldsToDisplay = array('ID', 'PRIX', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET');
                            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                        ?>
                    </div>
                <?php } ?>

                <?php if(!empty($artRev)){ ?>
                    <div id="ArtRev" class="mt2">
                        <h2 class="section">
                            <span>Journal articles</span>
                        </h2>
                        <?php 
                            foreach($artRev as $art){
                                $totalPrice += $art['ARTICLE_PRIX'];
                            }
                            $arrayForList = $artRev;
                            $currentPage = 'contrib';
                            $arrayFieldsToDisplay = array('ID', 'PRIX', 'REVUE_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET');
                            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                        ?>
                    </div>
                <?php } ?>
            
                <?php if(!empty($artMag)){ ?>
                    <div id="ArtMag" class="mt2">
                        <h2 class="section">
                            <span>Magazine articles</span>
                        </h2>
                        <?php 
                            foreach($artMag as $art){
                                $totalPrice += $art['ARTICLE_PRIX'];
                            }
                            $arrayForList = $artMag;
                            $currentPage = 'contrib';
                            $arrayFieldsToDisplay = array('ID', 'PRIX', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET');
                            include (__DIR__ . '/../CommonBlocs/liste_1col.php');                        
                        ?>
                    </div>
                <?php } ?>
                
                <!-- Affichage du prix total -->
                <div class="prixtotal">
                    <span class="label">TOTAL :</span>
                    <span class="price">
                        <span id="totalPrice"><?= number_format($totalPrice, 2, ",", "") ?></span> €
                    </span>
                </div>

                <div class="checkout-bottom-section">
                    <?php if($tmpCmdIdFrom != '') { ?>
                    <input type="hidden" id="tmpCmdIdFrom" value="<?= $tmpCmdIdFrom ?>"/>   
                    <?php } ?>
                    <?php if($totalPrice > 0){ ?>
                    <a class="continuer checkout-button" href="javascript:ajax.panierAchat()">Continue</a>
                    <?php 
                        //if($returnLink != null){
                            //echo '<a class="payer checkin-button" href="./'.$returnLink.'">Back to page</a>';
                            echo '<a class="payer checkin-button" href="javascript:history.back()">Back</a>';
                        //}
                    
                    } ?>
                </div>
        <?php } ?>
    <br>
</div>
