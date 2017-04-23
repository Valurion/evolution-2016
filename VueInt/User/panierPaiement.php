<?php 
?>
<div id="free_text">
    <h1 class="main-title">Payment method</h1>
    <hr class="grey">

    <div class="Clearfix"></div>
    <div id="wrapper_breadcrumb_cart">
        <ol id="breadcrumb_cart">
            <li>My cart</li>
            <li>Billing Address</li>
            <li class="black_button">Payment method</li>
            <li>Payment</li>
            <li>Get Access</li>
        </ol>
    </div>

    <br />
    <div class="your-order">
        <input type="hidden" id="tmpCmdId" value="<?= $commandeTmp['ID_COMMANDE']?>"/>
        <ul class="orders">
            <li>Amount VAT incl. :<span><b><?= $commandeTmp['PRIX']?> €</b></span></li>
            <li>Shipping VAT incl. : <span><b><?= $commandeTmp['FRAIS_PORT']?> €</b></span></li>
            <li class="last"><b>Total Amount :</b> <span><b><?= $prixTotal ?> €</b></span></li>
            
        </ul>
    </div>
    <br />

    <form id="ogone"
          action="<?= $ogone_url?>"
          method="post"> 
        <div class="center">
            <h2 class="main_subtitle"> Credit card </h2><br />

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
                    <input type="checkbox" id="checkout-cgv-acceptation-status" name="checkout-cgv-acceptation-status" value="1" /> I accept and fully understand the <a href="conditions.php" onclick="window.open(this.href); return false;">general sales conditions</a> of Cairn-int.info.
                </label>
            </div>

            <a href="#" onclick="javascript:formCheckoutValidation(event, 'en');" class="payercarte checkout-button">Continue</a>            
        </div>        
        
    </form>   
    
    <a class="payer checkin-button" href="javascript:ajax.panierAchat()">Back to Billing Address</a>
    <br/>
    <br/>
</div>
