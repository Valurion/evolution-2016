<?php 
?>
<div id="free_text">
    <h1 class="main-title">Méthode de paiement</h1>
    <hr class="grey">

    <div class="Clearfix"></div>
    <div id="wrapper_breadcrumb_cart">
        <ol id="breadcrumb_cart">
            <li>Mon panier</li>
            <li>Coordonnées</li>
            <li class="black_button">Methode de paiement</li>
            <li>Paiement</li>
            <li>Accès</li>
        </ol>
    </div>

    <br />
    <div class="your-order">
        <input type="hidden" id="tmpCmdId" value="<?= $commandeTmp['ID_COMMANDE']?>"/>
        <ul class="orders">
            <li>Montant total TTC de votre panier d'achats :<span><b><?= $commandeTmp['PRIX']?> €</b></span></li>
            <li>Frais de port TTC : <span><b><?= $commandeTmp['FRAIS_PORT']?> €</b></span></li>
            <li class="last"><b>Montant total TTC de votre achat :</b> <span><b><?= $prixTotal ?> €</b></span></li>
            
            <!--li >Montant TTC de votre achat :<span><b>[TOTAL_TTC] €</b></span></li-->

        </ul>
    </div>
    <br /><br />

    <form id="ogone"
          action="<?= $ogone_url?>"
          method="post">
        <div class="center">
            <h2 class="main_subtitle"> Paiement par carte de crédit </h2><br />

            <span><img src="./img/visa-card.png" />&nbsp;&nbsp;<img src="./img/mastercard.png" /> </span>

            <?php foreach($ogoneOptions as $key => $value){
                echo '<input type="hidden" name="'.$key.'" value="'.$value.'">';
            }?>                        
        </div>
        <div class="checkout-bottom-section" style="border-top: 0;">

            <!-- Lecture des conditions générales de ventes -->
            <div class="checkout-cgv-acceptation">
                <div id="checkout-cgv-acceptation-error" name="checkout-cgv-acceptation-error"></div>
                <label>
                    <input type="checkbox" id="checkout-cgv-acceptation-status" name="checkout-cgv-acceptation-status" value="1" /> J'ai pris connaissance des <a href="conditions-generales-de-vente.php" onclick="window.open(this.href); return false;">Conditions Générales de vente</a> du site Cairn.info, et je les accepte.
                </label>
            </div>
            <a href="#" onclick="javascript:formCheckoutValidation(event, 'fr');" class="payercarte checkout-button">Payer par carte de crédit</a>
            <!--<a href="#" onclick="javascript:ajax.paiementOgone('<?= $commandeTmp['ID_COMMANDE']?>');" class="payercarte checkout-button">Payer par carte de crédit</a>-->
            <!--a href="./landing_ogone.php" class="payercarte checkout-button">Payer par carte de crédit</a-->
        </div>
        <br />
        <br />
    </form>    
    <?php if($creditDispo != 'N/A'){ ?>    
        <div class="center">
            <h2 class="main_subtitle"> Paiement par crédit d'achat</h2>
            <?php if($creditDispo > 0){
                if($creditDispo >= $prixTotal){
            ?>
            <p>Solde actuel de votre crédit&#160;: <?= number_format($creditDispo, 2, ",", ""); ?> €</p>
            <div class="checkout-bottom-section" style="border-top: 0;">
                <a href="javascript:ajax.panierCredit('<?= $commandeTmp['ID_COMMANDE']?>')" class="checkout-button">Déduire de votre crédit d'achat</a>
            </div>
                <?php }else{?>
            
            <p>Solde actuel de votre crédit&#160;: <?= $number_format($creditDispo, 2, ",", ""); ?> €</p>
            <p class="formMsg">
                <b>Le solde est insuffisant pour cet achat.</b>
            </p>
            <a href="./credit.php" class="deduire">Renouveler votre crédit d'articles</a> 
            <a href="./mon_credit.php" class="deduire">Voir le détail de votre crédit</a> 
                <?php }
            }else{?>            
            
            <p>Vous ne disposez pas pour l'instant d'un crédit d'articles
                valide.</p>
            <div class="checkout-bottom-section" style="border-top: 0;">
                <a href="credit.php" class="checkout-button">En savoir plus sur les crédits d'articles</a>
            </div>
            <br />
            <?php }?>
        </div>
    <?php }   
    if($prixTotal > 30){?>
        <br /><br />
        
        <div class="center">
            <h2 class="main_subtitle"> Paiement chèque ou virement </h2><br />

            <div id="adressefact">

                <p>Vous recevrez par email un bon de commande,
                    à nous retourner par courrier accompagné de votre chèque ou
                    de la preuve de votre virement bancaire ou postal.</p>
            </div>
            <div class="checkout-bottom-section" style="border-top: 0;">	
                <a href="javascript:ajax.panierCheque('<?= $commandeTmp['ID_COMMANDE']?>')" class="checkout-button">Payer par chèque ou virement</a>
            </div>
            <br/>
        </div>
    <?php } ?>
    <a class="payer checkin-button" href="javascript:ajax.panierAchat()">Retour aux coordonnées</a>
    <br/>
    <br/>
</div>