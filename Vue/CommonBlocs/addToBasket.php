<?php
    // Pour qu'une institution puisse acheter un ouvrage, il faut que les achats ne soient pas désactivés et qu'il y ait un crédit d'article
    $isAllowPurchaseForInstitution = ($typesAchat['MODE'] != 2) || (
        ($authInfos['I']['PARAM_INST']['A'] != 1)
        && (isset($authInfos['I']['PARAM_INST']['H']) && (intval($authInfos['I']['PARAM_INST']['H']) <= 1))
    );
    $isPurchaseArticle = isset($typesAchat['ARTICLE']);
    $isPurchaseNumero = isset($typesAchat['NUMERO'])
        && ($typesAchat['NUMERO'][0]['REVUE_ACHAT_PAPIER'] == 1)
        && $isAllowPurchaseForInstitution
        && ($typesAchat['NUMERO'][0]['NUMERO_EPUISE'] != 1)
        && ($typesAchat['NUMERO'][0]['NUMERO_PRIX'] > 0);
    // $isPurchaseNumero = isset($typesAchat['NUMERO']);
    $isPurchaseNumeroElec = isset($typesAchat['NUMERO_ELEC'])
        && $isAllowPurchaseForInstitution
        && ($typesAchat['NUMERO_ELEC'][0]['REVUE_ACHAT_ELEC'] == 1)
        && !$accessElecOk;
    $isPurchaseRevue = isset($typesAchat['REVUE'])
        && $isAllowPurchaseForInstitution;
    // Le verbe change suivant si on est connecté en institution
    $verbPurchase = $typesAchat['MODE'] == 2 ? 'Demander' : 'Acheter';
?>
<div class="add_to_cart_trigger">
    <div class="add-to-cart-bloc grid-g">
        
        <?php 
            // Init
            $btn = "";

            // Bouton d'achat d'un article
            if ($isPurchaseArticle) {
                // Bouton de demande
                // Quand on est connecté en tant qu'utilisateur ET en tant qu'institution ET que l'utilisateur a activé la possibilité d'acheter n'importe quel article, la demande a quand même la priorité
                if ( ($typesAchat['MODE'] === 2) || (isset($authInfos['I']) && isset($authInfos['U']) && ($authInfos['U']['SHOWALL'] == 1)) ) {
                    
                    // Définition des labels
                    $btnLabel   = "Demander";
                    if (in_array($typesAchat['ARTICLE'][0]['REVUE_TYPEPUB'], [3, 5, 6])) {$btnSLabel = "ce chapitre";} else {$btnSLabel = "cet article";}

                    // Définition du bouton
                    $btn = "<a id=\"\" class=\"add-to-cart\" href=\"./mes_demandes.php?ID_ARTICLE=".$typesAchat['ARTICLE'][0]['ARTICLE_ID_ARTICLE']."\">
                                <span class=\"label\">".$btnLabel."</span>
                                <span class=\"subLabel\">".$btnSLabel."</span>
                            </a>";
                }
                // Bouton d'achat
                else {                    
                    // Récupération des valeurs (via service)
                    $nbre       = Service::get("ParseDatas")->countPurchaseArticle($typesAchat);
                    $prices     = Service::get("ParseDatas")->MinAndMaxPriceOfArticle($typesAchat);               

                    // Définition des labels
                    $btnLabel   = "Acheter";
                    if (in_array($typesAchat['ARTICLE'][0]['REVUE_TYPEPUB'], [3, 5, 6])) {$btnSLabel = "ce chapitre";} else {$btnSLabel = "cet article";}
                    //if($nbre > 1) {$btnSLabel = "À partir de";}
                    //if($prices["minPrice"] != $prices["maxPrice"]) {$btnSLabel = "À partir de";}
                    
                    $btnPrice   = number_format($prices["minPrice"], 2, ",", " ")."€"; if($prices["minPrice"] == 0) {$btnPrice = "";}

                    // Définition du bouton
                    $btn = "<a id=\"achat-article\" class=\"add-to-cart\" href=\"javascript:void(0);\" onclick=\"$(this).toggleClass('actif');\">
                                <span class=\"label\">".$btnLabel."</span>
                                <span class=\"subLabel\">".$btnSLabel." <span>".$btnPrice."</span></span>
                            </a>";
                }
            }
            echo $btn;
        ?>


        <?php 
            // Init
            $btn = "";

            // Bouton d'achat d'un numéro
            if ($isPurchaseNumero || $isPurchaseNumeroElec) {
                // Récupération des valeurs (via service)
                $nbre       = Service::get("ParseDatas")->countPurchaseNumero($typesAchat, isset($accessElecOk) ? $accessElecOk : null);
                $prices      = Service::get("ParseDatas")->MinAndMaxPriceOfNumero($typesAchat, isset($accessElecOk) ? $accessElecOk : null);                

                // Définition des labels
                $btnLabel   = "Acheter";
                if ($typesAchat['NUMERO'][0]['REVUE_TYPEPUB'] == 3) {$btnSLabel = "cet ouvrage";} else {$btnSLabel = "ce numéro";}
                //if($nbre > 1) {$btnSLabel = "À partir de";}
                
                $btnPrice   = number_format($prices["minPrice"], 2, ",", " ")."€"; if($prices["minPrice"] == 0) {$btnPrice = "";}

                // Définition du bouton
                $btn = "<a id=\"achat-numero\" class=\"add-to-cart\" href=\"javascript:void(0);\" onclick=\"$(this).toggleClass('actif');\">
                            <span class=\"label\">".$btnLabel."</span>
                            <span class=\"subLabel\">".$btnSLabel." <span>".$btnPrice."</span></span>
                        </a>";

            }
            echo $btn;
        ?>
        
        <?php 
            // Init
            $btn = "";

            // Bouton d'abonnement à une revue
            if ($isPurchaseRevue && !$isPurchaseArticle) {
                // Récupération des valeurs (via service)
                $nbre       = Service::get("ParseDatas")->countPurchaseRevue($typesAchat);
                $prices     = Service::get("ParseDatas")->MinAndMaxPriceOfRevue($typesAchat);    

                // Définition des labels
                $btnLabel   = "S'abonner";
                $btnSLabel  = "à partir de"; if($prices["minPrice"] == $prices["maxPrice"]) {$btnSLabel = "";}
                $btnPrice   = number_format($prices["minPrice"], 2, ",", " ")."€"; if($prices["minPrice"] == 0) {$btnPrice = "";}

                // Définition du bouton
                $btn = "<a id=\"achat-revue\" class=\"add-to-cart\" href=\"javascript:void(0);\" onclick=\"$(this).toggleClass('actif' );\">
                            <span class=\"label\">".$btnLabel."</span>
                            <span class=\"subLabel\">".$btnSLabel." <span>".$btnPrice."</span></span>
                        </a>";

            }
            echo $btn;
        ?>

    </div>
</div>

<?php
$this->javascripts[] = <<<'EOD'
    $(function() {
        cairn.triggerMenu([
            {src: $('#achat-article'), dest: $('#achat-article-slider')},
            {src: $('#achat-numero'), dest: $('#achat-numero-slider')},
            {src: $('#achat-revue'), dest: $('#achat-revue-slider')},

        ]);
        // Si le flag est trouvé dans le hash de l'url, on ouvre le slide d'achat d'abonnement et on scroll jusqu'à lui
        if (window.location.hash.indexOf('open-purchase-revue-slider') >= 0) {
            $('#achat-revue').click();
            window.location.hash = window.location.hash.replace('open-purchase-revue-slider', '');
            $('html, body').animate({scrollTop: $('#page_header').offset().top}, 0);
        }
    
        if (window.location.hash.indexOf('achat-revue') >= 0) {
            $('#achat-revue').click();                     
        }
        if (window.location.hash.indexOf('achat-numero') >= 0) {
            $('#achat-numero').click();    
        }
        if (window.location.hash.indexOf('achat-article') >= 0) {
            $('#achat-article').click();      
        }          
      
    });
EOD;
?>
