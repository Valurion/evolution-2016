<?php 
$infos = $authInfos['U'];
if(isset($commandeTmp)){
    $infos = $commandeTmp;
}
?>
<div id="free_text">
    <h1 class="main-title">Billing Address</h1>
    <hr class="grey">

    <div class="Clearfix"></div>
    <div id="wrapper_breadcrumb_cart">
        <ol id="breadcrumb_cart">
            <li>My cart</li>
            <li class="black_button">Billing Address</li>
            <li>Payment method</li>
            <li>Payment</li>
            <li>Get Access</li>
        </ol>
    </div>

    <form id="coordo" action="javascript:ajax.panierCoord()">
        <input type="hidden" id="tmpCmdId" value="<?= $tmpCmdId ?>"/>
        
        <h2 class="section"> Your Billing Address </h2><br>
        
                <div style="display: block;" id="adressefact">
                
                <div class="blue_milk left w45">
                    <label for="fact_nom">First Name and Last Name, Company or Institution </label><br>
                    <input type="text" value="<?= $infos['FACT_NOM'] ?>" id="fact_nom" name="fact_nom" size="40">

                </div>
                <div class="blue_milk right w45">
                    <label for="fact_adr">Address <span class="red">*</span></label><br> <input required="required" type="text" value="<?= $infos['FACT_ADR'] ?>" id="fact_adr" name="fact_adr" size="50">
                </div><br><br><br><br>
                <div class="blue_milk left w22">
                    <label for="fact_cp">Zip Code <span class="red">*</span></label> <input required="required" type="text" value="<?= $infos['FACT_CP'] ?>" id="fact_cp" name="fact_cp">
                </div>
                &nbsp;&nbsp;&nbsp;
                <div class="blue_milk w22">
                    <label for="fact_ville">City <span class="red">*</span></label> <input required="required" type="text" value="<?= $infos['FACT_VILLE'] ?>" id="fact_ville" name="fact_ville">
                </div>
                <div class="right w45">
                    <label for="fact_pays">Country <span class="red">*</span></label> &nbsp;
                    <select id="fact_pays" name="fact_pays" required="required">
                    <?php
                    if(isset($infos['FACT_PAYS']) && $infos['FACT_PAYS'] != ''){ ?>
                        <option selected=""><?= $infos['FACT_PAYS'] ?></option>
                    <?php
                    }
                    ?> 
                    <option>France</option>
                    <option>Belgique</option>
                    <?php
                    foreach($listePays as $pays){
                        echo '<option>'.$pays.'</option>';
                    }?>
                    </select>

                </div>
                <br>
                <br>
                <div class="">
                    <input type="checkbox" id="checksvgfactadr" checked="" name="checksvgfactadr">&nbsp;
                    <label for="checksvgfactadr">Save this address for further purchases</label>
                </div>

            </div>
            
            
         <div class="checkout-bottom-section">        
            <button id="panierCoordButton" class="payer checkout-button">Continue</button>
            <a class="payer checkin-button" href="javascript:ajax.panierStart()">Back to cart</a>
        </div><br>
    </form>
</div>

