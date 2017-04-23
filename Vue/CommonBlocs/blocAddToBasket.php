<?php
    /*
        TODO: Les commentaires
    */
    const PURCHASE_NUMERO_ELEC  = 0;
    const PURCHASE_NUMERO_PAPER = 1;

    /* Fonctions déplacées dans Services/ParseDatas */
    // Récupération des données via Service
    $articlePurchase    = Service::get("ParseDatas")->getPurchasesArticle($typesAchat);
    $numeroPurchase     = Service::get("ParseDatas")->getPurchasesNumero($typesAchat, isset($accessElecOk) ? $accessElecOk : null);
    $revuePurchase      = Service::get("ParseDatas")->getPurchasesRevue($typesAchat);

    //var_dump($numeroPurchase);
    
    // Construction du tableau
    $purchases = array(
        'article' => $articlePurchase,
        'numero' => $numeroPurchase,
        'revue' => $revuePurchase
    );
?>


<?php if (count($purchases['article'])) { ?>
    <!-- Achat d'un numéro -->
    <div id="achat-article-slider" class="grid-g grid-3-head add-to-cart-slider mt1" style="display: none">

        <!-- Formulaire -->
        <form class="grid-u-3-4">            
            <div class="grid-g add-to-cart-revue">
                <div class="frame-grey grid-g">
                    <div class="grid-u-2-5 detail-abonnement">
                        <?php 
                            // Init
                            $items      = "";
                            $options    = "";
                            $paragraph  = "";

                            // Class (on cache les options si non-nécessaires)
                            $visibleClass = "";
                            if(count($purchases['article']) <= 1) {$visibleClass = "invisible";}

                            // Les offres disponibles 
                            // Tri sur le prix (le plus bas en 1er)
                            //usort($purchases['article'], function($a, $b) {return $a['price'] - $b['price'];});
                            foreach ($purchases['article'] as $index => $achat) {
                                // On affiche uniquement la 1ere option
                                if($index != 0) {$itemVisible = "invisible";} else {$itemVisible = "";}
                                
                                // Titres
                                $items .= "<span class=\"aItem aItem-$index title $itemVisible\">".$achat['title']."</span>";
                                $items .= "<span class=\"aItem aItem-$index price $itemVisible\">".number_format($achat['price'], 2, ",", "")."€</span>";

                                // Options du formulaire
                                $options .= "<option data-id=\"aItem-$index\" value=\"".$achat['url']."\">".$achat['version']."</option>";

                                // Paragraphe
                                $paragraph .= "<span class=\"aItem aItem-$index $itemVisible\">".$achat['desc']."</span>";
                            }                  
                        ?>

                        <!-- Offre -->
                        <?php echo $items; ?>
                        <span class="sub-title <?php echo $visibleClass; ?>">Version</span>
                        <span class="block text-center"><select id="purchase-article" name="purchase-article" class="<?php echo $visibleClass; ?>" onchange="changeArticleOption();"><?php echo $options; ?></select></span>

                    </div>
                    <div class="grid-u-3-5 texte-abonnement">
                        <span class="paragraph block">
                            <?= $paragraph ?>
                        </span>

                        <a id="add-to-cart-btn" class="add-to-cart-icon-plus" href="javascript:void(0);" onclick="window.location = $('#achat-article-slider #purchase-article').val();">
                            <span class="label">Ajouter au panier</span>
                        </a>
                    </div>                        
                </div>
            </div>            
        </form>

        <!-- Mention -->
        <div class="grid-u-1-4 mention">
            <h2>Attention :</h2>
            <p>Cette offre est exclusivement réservée aux particuliers.</p>
            <p>Si vous souhaitez abonner votre institution, veuillez vous adresser à votre libraire ou à votre fournisseur habituel.</p>
            <p>Les prix ici indiqués sont les prix TTC.</p>
            <p>Pour plus d'informations, veuillez consulter les <a href="./conditions-generales-de-vente.php">conditions générales de vente</a>.</p>
        </div>
    </div>

<?php
$this->javascripts[] = <<<'EOD'
    function changeArticleOption() {
        var value = $('#achat-article-slider #purchase-article option:selected').attr('data-id');
        $('.aItem').hide();
        $('.aItem').removeClass('invisible');
        $('.'+value).show();
    }
EOD;
?>
<?php } ?>



<?php if (count($purchases['numero'])) { ?>
    <!-- Achat d'un numéro -->
    <div id="achat-numero-slider" class="grid-g grid-3-head add-to-cart-slider mt1" style="display: none">

        <!-- Formulaire -->
        <form class="grid-u-3-4">            
            <div class="grid-g add-to-cart-revue">
                <div class="frame-grey grid-g">
                    <div class="grid-u-2-5 detail-abonnement">
                        <?php 
                            // Init
                            $items      = "";
                            $options    = "";
                            $paragraph  = "";

                            // Class (on cache les options si non-nécessaires)
                            $visibleClass = "";
                            if(count($purchases['numero']) <= 1) {$visibleClass = "invisible";}

                            // Les offres disponibles 
                            // Tri sur le prix (le plus bas en 1er)
                            usort($purchases['numero'], function($a, $b) {return $a['price'] - $b['price'];});
                            foreach ($purchases['numero'] as $index => $achat) {
                                // On affiche uniquement la 1ere option
                                if($index != 0) {$itemVisible = "invisible";} else {$itemVisible = "";}
                                
                                // Titres
                                $items .= "<span class=\"nItem nItem-$index title $itemVisible\">".$achat['title']."</span>";
                                $items .= "<span class=\"nItem nItem-$index price $itemVisible\">".number_format($achat['price'], 2, ",", "")."€</span>";

                                // Options du formulaire
                                $options .= "<option data-id=\"nItem-$index\" value=\"".$achat['url']."\">".$achat['version']."</option>";

                                // Paragraphe
                                $paragraph .= "<span class=\"nItem nItem-$index $itemVisible\">".$achat['desc']."</span>";
                            }                  
                        ?>

                        <!-- Offre -->
                        <?php echo $items; ?>
                        <span class="sub-title <?php echo $visibleClass; ?>">Version</span>
                        <span class="block text-center"><select id="purchase-numero" name="purchase-numero" class="<?php echo $visibleClass; ?>" onchange="changeNumeroOption();"><?php echo $options; ?></select></span>

                    </div>
                    <div class="grid-u-3-5 texte-abonnement">
                        <span class="paragraph block">
                            <?= $paragraph ?>
                        </span>

                        <a id="add-to-cart-btn" class="add-to-cart-icon-plus" href="javascript:void(0);" onclick="window.location = $('#achat-numero-slider #purchase-numero').val();">
                            <span class="label">Ajouter au panier</span>
                        </a>
                    </div>                        
                </div>
            </div>
            <?php if ($isPurchaseRevue && !$isPurchaseArticle) { ?>
            <div class="tips">
                <?php
                    // Récupération des valeurs (via service)
                    $prices = Service::get("ParseDatas")->MinAndMaxPriceOfRevue($typesAchat);      
                ?>
                Pour <b><?php echo number_format($prices["minPrice"], 2, ",", "");?>€</b>, <a class="link-underline" href="revue-<?php echo $revue["REVUE_URL_REWRITING"]; ?>.htm">souscrivez un abonnement</a> d’un an à tous les numéros de cette revue en version papier + électronique. 
            </div> 
            <?php } ?>          
        </form>

        <!-- Mention -->
        <div class="grid-u-1-4 mention">
            <?php
                // Si achat du numéro uniquement en format électronique
                $isPurcharsesPaper = false;
                foreach ($purchases['numero'] as $key => $value) {if ($value['type'] === PURCHASE_NUMERO_PAPER) {$isPurcharsesPaper = true;break;}}
                if ($isPurcharsesPaper) {$mentionPlus = ", hors frais de livraison";}
            ?>
            <h2>Attention :</h2>
            <p>Cette offre est exclusivement réservée aux particuliers.</p>
            <p>Si vous souhaitez abonner votre institution, veuillez vous adresser à votre libraire ou à votre fournisseur habituel.</p>
            <p>Les prix ici indiqués sont les prix TTC<?php echo $mentionPlus; ?>.</p>
            <p>Pour plus d'informations, veuillez consulter les <a href="./conditions-generales-de-vente.php">conditions générales de vente</a>.</p>
        </div>
    </div>

<?php
$this->javascripts[] = <<<'EOD'
    function changeNumeroOption() {
        var value = $('#achat-numero-slider #purchase-numero option:selected').attr('data-id');
        $('.nItem').hide();
        $('.nItem').removeClass('invisible');
        $('.'+value).show();
    }
EOD;
?>
<?php } ?>


<?php if (count($purchases['revue'])) { ?>
<?php
    // Init
    $formules = array();

    // Définition des formules (plusieurs type d'abonnement, défini par le champ "formule")
    foreach ($purchases['revue'] as $index => $formule) {
        // Ajout de la formule dans le tableau
        if (!in_array($formule["formule"], $formules)) {
            $formules[] = $formule['formule'];
        }
    }
?>


    <!-- Abonnement/Achat d'une revue -->
    <div id="achat-revue-slider" class="grid-g grid-3-head add-to-cart-slider mt1" style="display: none">

        <!-- Formulaire -->
        <form class="grid-u-3-4">            
            <div class="grid-g add-to-cart-revue">
                <div class="frame-grey grid-g">
                    <div class="grid-u-2-5 detail-abonnement">

                        <?php
                            // Init
                            $optionsFormule     = "";

                            // Il y a plusieurs formules, on affiche une liste déroulante permettant de sélectionner la meilleure formule
                            if(count($formules) > 1) { 
                                foreach($formules as $fkeySelect => $formuleSelect) {
                                    $optionsFormule .= "<option data-id=\"\" value=\"".$fkeySelect."\">".$formuleSelect."</option>";
                                }
                            }
                        ?>

                        <!-- Formules selecteur -->
                        <?php if(count($formules) > 1) { ?>
                            <span class="block text-center" style="margin-bottom: 20px;">
                                <select id="formule-<?=$fkey; ?>" name="formule-<?=$fkey; ?>" class="" onchange="changeFormuleOption('<?=$fkey; ?>');">
                                    <?php echo $optionsFormule; ?>
                                </select>
                            </span>
                        <?php } ?>


                        <?php
                            // Pour chaque formule, on affiche les options d'abonnement
                            foreach($formules as $fkey => $formule) { 
                                // Init
                                $items      = "";
                                $options    = "";

                                // Class (on cache les options si non-nécessaires)
                                $visibleClass = "";
                                if($fkey != 0) {$visibleFormule = "invisible";} else {$visibleFormule = "";}

                                // Les offres disponibles
                                // Tri sur le prix (le plus bas en 1er)
                                //usort($purchases['revue'], function($a, $b) {return $a['price'] - $b['price'];});
                                $i = 0;
                                foreach ($purchases['revue'] as $index => $achat) {
                                    // Récupération uniquement des valeurs de la formule
                                    if($achat["formule"] == $formule) {
                                        // On affiche uniquement la 1ere option
                                        //if($index != 0) {$itemVisible = "invisible";} else {$itemVisible = "";}
                                        if($i != 0) {$itemVisible = "invisible";} else {$itemVisible = "";}
                                        
                                        // Titres
                                        if(count($formules) == 1) {$items .= "<span class=\"rItem rItem-$fkey-$index title $itemVisible\">".$achat['title']."</span>";}
                                        $items .= "<span class=\"rItem-$fkey rItem-$fkey-$index price $itemVisible\">".number_format($achat['price'], 2, ",", "")."€</span>";

                                        // Options du formulaire
                                        $options .= "<option data-id=\"rItem-$fkey-$index\" value=\"".$achat['url']."\">".$achat['periode']."</option>";
                                        
                                        $i++;
                                    }
                                }                  
                                ?>
                                <!-- Offre -->
                                <div class="formule-option formule-option-<?=$fkey;?> <?=$visibleFormule;?>">
                                    <?php echo $items; ?>
                                    <span class="sub-title <?php echo $visibleClass; ?>">A partir du numéro</span>
                                    <span class="block text-center"><select id="purchase-revue-<?=$fkey;?>" name="purchase-revue-<?=$fkey;?>" class="<?php echo $visibleClass; ?>" onchange="changeRevueOption('<?=$fkey; ?>');"><?php echo $options; ?></select></span>
                                </div>
                        <?php } ?>
                    </div>
                    <div class="grid-u-3-5 texte-abonnement">
                        <?php 
                            // Pour chaque formule, on affiche les options d'abonnement
                            foreach($formules as $fkey => $formule) { 
                                // Init
                                $paragraph      = "";
                                
                                // Class (on cache les options si non-nécessaires)
                                $visibleClass = "";
                                //if(count($purchases['revue']) <= 1) {$visibleClass = "invisible";}
                                if($fkey != 0) {$visibleFormule = "invisible";} else {$visibleFormule = "";}

                                // Les offres disponibles
                                // Tri sur le prix (le plus bas en 1er)
                                //usort($purchases['revue'], function($a, $b) {return $a['price'] - $b['price'];});
                                $i = 0;
                                foreach ($purchases['revue'] as $index => $achat) {
                                    // Récupération uniquement des valeurs de la formule
                                    if($achat["formule"] == $formule) {
                                        // On affiche uniquement la 1ere option
                                        if($i != 0) {$itemVisible = "invisible";} else {$itemVisible = "";}
                                        
                                        // Paragraphe
                                        $paragraph .= "<span class=\"rItem-$fkey rItem-$fkey-$index $itemVisible\">".$achat['desc']."</span>";
                                        
                                        $i++;
                                    }
                                }                  
                                ?>

                                <!-- Paragraphe et bouton -->
                                <div class="formule-option formule-option-<?=$fkey;?> <?=$visibleFormule;?>">
                                    <span class="paragraph block">
                                        <?= $paragraph ?>
                                    </span>

                                    <a id="add-to-cart-btn" class="add-to-cart-icon-plus" href="javascript:void(0);" onclick="window.location = $('#achat-revue-slider #purchase-revue-<?=$fkey;?>').val();">
                                        <span class="label">Ajouter au panier</span>
                                    </a>
                                </div>
                        <?php } ?>                        
                    </div>                        
                </div>
            </div>            
        </form>

        <!-- Mention -->
        <div class="grid-u-1-4 mention">
            <h2>Attention :</h2>
            <p>Cette offre est exclusivement réservée aux particuliers.</p>
            <p>Si vous souhaitez abonner votre institution, veuillez vous adresser à votre libraire ou à votre fournisseur habituel.</p>
            <p>Les prix ici indiqués sont les prix TTC.</p>
            <p>Pour plus d'informations, veuillez consulter les <a href="./conditions-generales-de-vente.php">conditions générales de vente</a>.</p>
        </div>
    </div>
<?php
$this->javascripts[] = <<<'EOD'
    function changeRevueOption(fkey) {
        var value = $('#achat-revue-slider #purchase-revue-'+fkey+' option:selected').attr('data-id');
        $('.rItem-'+fkey+'').hide();
        $('.rItem-'+fkey+'').removeClass('invisible');
        $('.'+value).show();
    }

    function changeFormuleOption(fkey) {
        var value = $('#achat-revue-slider #formule-'+fkey+' option:selected').val();
        $('.formule-option').hide();
        $('.formule-option').removeClass('invisible');
        $('.formule-option-'+value).show();
        
    }

EOD;
?>
<?php } ?>

<?php
$this->javascripts[] = <<<'EOD'
    $('select').niceSelect();
EOD;
?>














