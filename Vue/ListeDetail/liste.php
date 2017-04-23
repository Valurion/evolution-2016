<?php
$this->titre = 'Liste des ' . $type;

/* Ce fichier est utilisé pour lister les REVUES (listrev.php) et les OUVRAGES (collections.php)
 * certaines données doivent donc être modifiée en fonction de l'utilisation */

// Configuration
// Pour les revues
if ($type == 'revues') {
    // Tabs
    $typePub        = 'revue';

    // Liens
    $homeLink       = 'Accueil_Revues.php';
    $homeLinkLabel  = 'Revues';

    $script         = 'listerev.php';
    $prefix         = 'revue-';

    // Label du titre
    if($total > 1) {$label = "revues";}
    else {$label = "revue";}
} 
// Pour les ouvrages/collections
else if (($type == 'collections') || ($type == 'ouvrages'))  {
    // Tabs
    $typePub        = 'ouvrage';

    // Liens
    $homeLink       = 'ouvrages.php';
    $homeLinkLabel  = 'Ouvrages';

    $script         = 'liste-des-ouvrages.php';
    $prefix         = '';
    
    // Label du titre
    if($total > 1) {$label = "ouvrages";}
    else {$label = "ouvrage";}
}
// Pour les encyclopédies
else if ($type == 'encyclopedies')  {
    // Tabs
    $typePub        = 'encyclopedie';

    // Liens
    $homeLink       = 'que-sais-je-et-reperes.php';
    $homeLinkLabel  = 'Que sais-je ? / Repères';

    $script     = 'liste-des-que-sais-je-et-reperes.php';
    $prefix     = '';

    // Label du titre
    if($total > 1) {$label = "ouvrages";}
    else {$label = "ouvrage";}
}
else {
    $label = $type;
}

// Définition des éléments à afficher
$toAdd = $revues;
/*
$toAdd = array();
$toAddTitres = array();
var_dump(count($revues));
foreach ($revues as $row) {
    if(!in_array($row['ID_REVUE'], $toAddTitres)){
        $toAdd[] = $row;
        $toAddTitres[] = $row['ID_REVUE'];
    }
}
$typePub = ($type == "revues" ? 'revue' : 'ouvrage');*/
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="<?php echo $homeLink; ?>"><?php echo ucfirst($homeLinkLabel); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="<?php echo $script; ?>">Liste des <?php echo $label; ?></a>
</div>

<div id="body-content">

    <h1 class="main-title"><?php echo number_format($total, 0, '', ' '); ?> <?php echo $label; ?></h1>

    <div class="disciplineSwitcher boxHome text-center">
        <form id="disciplineSwitcher" method="get" action="./<?php echo $script; ?>">
            <div>
                <!--<label for="editeur">Filtrer par</label>-->

                <div class="block">
                    <!-- Liste des éditeurs -->
                    <?php if($editeurs != null) { ?>
                    <select onchange="window.location.href = '<?=$script;?>?editeur='+this.value+''" id="editeur" name="editeur">
                        <option value="">Toutes les maisons d'édition</option>                        
                        <?php
                            // Liste des éditeurs pour les revues (Maison d'édition (1) + Autres structures éditoriale(0))
                            if ($type == 'revues') {
                                // Init
                                $editeurTypeVal1 = "1";
                                $editeurTypeVal2 = "0";

                                // Parcours des maisons d'édition
                                $editeursPrimaire   = [];
                                $editeursSecondaire = [];

                                // Définition des listes
                                foreach($editeurs as $editeur) {
                                    if($editeur["EDITEUR_TYPE"] == $editeurTypeVal1) {$editeursPrimaire[] = $editeur;}
                                    if($editeur["EDITEUR_TYPE"] == $editeurTypeVal2) {$editeursSecondaire[] = $editeur;}
                                }

                                // Liste des maisons d'édition
                                if(count($editeursPrimaire) != 0) { echo "<option class=\"group-select\" ". (($currentEditeur == "$editeurTypeVal1") ? "selected" : "") ." value=\"$editeurTypeVal1\">Maisons d’édition</option>"; }
                                foreach($editeursPrimaire as $editeur) {
                                    echo '<option ' . (($editeur["EDITEUR_ID_EDITEUR"] == $currentEditeur) ? "selected" : "") . ' value="' . $editeur["EDITEUR_ID_EDITEUR"] . '" data-display="' . $editeur["EDITEUR_NOM_EDITEUR"] . '">&nbsp;-&nbsp;' . $editeur["EDITEUR_NOM_EDITEUR"] . '</option>';
                                }

                                // Liste des maisons d'édition (alternative)
                                if(count($editeursSecondaire) != 0) { echo "<option class=\"group-select\" ". (($currentEditeur == "$editeurTypeVal2") ? "selected" : "") ." value=\"$editeurTypeVal2\">Autres structures éditoriales</option>"; }
                                foreach($editeursSecondaire as $editeur) {
                                    echo '<option ' . (($editeur["EDITEUR_ID_EDITEUR"] == $currentEditeur) ? "selected" : "") . ' value="' . $editeur["EDITEUR_ID_EDITEUR"] . '" data-display="' . $editeur["EDITEUR_NOM_EDITEUR"] . '">&nbsp;-&nbsp;' . $editeur["EDITEUR_NOM_EDITEUR"] . '</option>';
                                }
                            }
                            // Liste des éditeurs pour les ouvrages
                            if (($type == 'collections') || ($type == 'ouvrages'))  {
                                // Init
                                $editeurValueToFind = "(programme ReLIRE)";

                                // Parcours des maisons d'édition
                                $editeursPrimaire   = [];
                                $editeursSecondaire = [];

                                // Définition des listes
                                foreach($editeurs as $editeur) {
                                    // Maison d'édition
                                    if (strpos($editeur["EDITEUR_NOM_EDITEUR"], $editeurValueToFind) === false) {$editeursPrimaire[] = $editeur;}
                                    // Ouvrages du programme ReLIRE
                                    else {$editeursSecondaire[] = $editeur;}
                                }

                                // Liste des maisons d'édition
                                if(count($editeursPrimaire) != 0) { echo "<option class=\"group-select\" disabled=\"disabled\">Maisons d’édition</option>"; }
                                foreach($editeursPrimaire as $editeur) {
                                    echo '<option ' . (($editeur["EDITEUR_ID_EDITEUR"] == $currentEditeur) ? "selected" : "") . ' value="' . $editeur["EDITEUR_ID_EDITEUR"] . '" data-display="' . $editeur["EDITEUR_NOM_EDITEUR"] . '">&nbsp;-&nbsp;' . $editeur["EDITEUR_NOM_EDITEUR"] . '</option>';
                                }

                                // Liste des maisons d'édition (alternative)
                                if(count($editeursSecondaire) != 0) { echo "<option class=\"group-select\" disabled=\"disabled\">Ouvrages du programme ReLIRE</option>"; }
                                foreach($editeursSecondaire as $editeur) {
                                    echo '<option ' . (($editeur["EDITEUR_ID_EDITEUR"] == $currentEditeur) ? "selected" : "") . ' value="' . $editeur["EDITEUR_ID_EDITEUR"] . '" data-display="' . str_replace(" $editeurValueToFind", "", $editeur["EDITEUR_NOM_EDITEUR"]) . '">&nbsp;-&nbsp;' . str_replace(" $editeurValueToFind", "", $editeur["EDITEUR_NOM_EDITEUR"]) . '</option>';
                                }
                            }
                        ?>
                    </select>
                    <?php } ?>

                    <!-- Liste des collections -->
                    <?php if($collections != null && $currentEditeur != "") { ?>
                    <select onchange="this.form.submit()" id="collection" name="collection">
                        <option value="">Toutes les collections</option>
                        <?php
                            // Parcours des maisons d'éditions ayant au moins 2 revues
                            foreach ($collections as $collection) {
                                echo '<option ' . (($collection["ID_REVUE"] == $currentCollection) ? "selected" : "") . ' value="' . $collection["ID_REVUE"] . '">' . $collection["TITRE"] . '</option>';
                            }
                        ?>
                    </select>
                    <?php } ?> 
                </div>

                <div class="block mt1">
                    <!-- Liste des disciplines -->
                    <?php if($disciplines != null) { ?>
                    <select onchange="this.form.submit()" id="discipline" name="discipline">
                        <option value="">Toutes les disciplines</option>
                        <?php
                            // Parcours des maisons d'éditions ayant au moins 2 revues
                            foreach ($disciplines as $discipline) {
                                echo '<option ' . (($discipline["POS_DISC"] == $currentDiscipline) ? "selected" : "") . ' value="' . $discipline["POS_DISC"] . '">' . $discipline["DISCIPLINE"] . '</option>';
                            }
                        ?>
                    </select>
                    <?php } ?>

                    <!-- Liste des sous-disciplines -->
                    <?php if($sousdisciplines != null) { ?>
                    <select onchange="this.form.submit()" id="sousdiscipline" name="sousdiscipline">
                        <option value="">Toutes les sous-disciplines</option>
                        <?php
                            // Parcours des maisons d'éditions ayant au moins 2 revues
                            foreach ($sousdisciplines as $sousdiscipline) {
                                echo '<option ' . (($sousdiscipline["POS_DISC"] == $currentSousDiscipline) ? "selected" : "") . ' value="' . $sousdiscipline["POS_DISC"] . '">' . $sousdiscipline["DISCIPLINE"] . '</option>';
                            }
                        ?>
                    </select>
                    <?php } ?> 
                </div>
            </div>
        </form>
    </div>

    <div class="boxHome borderTop listerev mt3 mb2">
        <?php
            // Paramètres d'affichage des données
            $arrayForList           = $toAdd;
            $arrayFieldsToDisplay   = array(); 

            // REVUE
            if($type == "revues") {
                // Titre à afficher
                if(isset($authInfos["I"]) || isset($authInfos["U"]) ) {$arrayTitlesToDisplay   = array("field" => "REVUE_ABO", "values" => array("0" => "Autres revues", "1" => "Accès abonné"));}

                // Elements à afficher               
                $arrayFieldsToDisplay[] = 'PERIODICITE';
                if($currentEditeur == "") {$arrayFieldsToDisplay[] = 'NOM_EDITEUR';}
                $arrayFieldsToDisplay[] = 'LIMITES';
            }
            // OUVRAGES
            if($type == "ouvrages") {
                // Titre à afficher
                if(isset($authInfos["I"]) || isset($authInfos["U"]) ) {$arrayTitlesToDisplay   = array("field" => "REVUE_ABO", "values" => array("0" => "Autres ouvrages", "1" => "Accès abonné"));}

                // Elements à afficher
                if($currentEditeur == "") {$arrayFieldsToDisplay[] = 'EDITEUR_NOM_EDITEUR';}
            }
            // ENCYCLOPEDIES
            if($type == "encyclopedies") {
                // Titre à afficher
                if(isset($authInfos["I"]) || isset($authInfos["U"]) ) {$arrayTitlesToDisplay   = array("field" => "REVUE_ABO", "values" => array("0" => "Autres ouvrages", "1" => "Accès abonné"));}

                // Réattribution des valeurs
                foreach($arrayForList as $key => $revue) {
                    $arrayForList[$key]["COLLECTION"] = $revue["TITRE_ABREGE"];
                    $arrayForList[$key]["EDITEUR_ANNEE"] = $revue["EDITEUR_NOM_EDITEUR"].", ".$revue["ANNEE"];
                }
                // Elements à afficher          
                $arrayFieldsToDisplay[] = 'NOM_AUTEURS';
                $arrayFieldsToDisplay[] = 'COLLECTION';
                if($currentEditeur != "" || $currentCollection != "") {$arrayFieldsToDisplay[] = 'ANNEE';}
                else {$arrayFieldsToDisplay[] = 'EDITEUR_ANNEE';}
            }

            // Paramètres de la liste
            $currentPage    = '';
            $prefix         = '';
            include (__DIR__ . '/../CommonBlocs/liste_2col.php');

            // Aucun résultat
            if(count($arrayForList) == 0) {
                echo "<p class=\"text-center\"><em>Aucun résultat pour cette sélection.</em></p>";
            }
        ?>
    </div>

    <?php
        // Définition de l'URL
        $url        = "";
        $fullUrl    = explode("?", $_SERVER["REQUEST_URI"]);
        $paramsURL  = explode("&", $fullUrl[1]);        

        // Récupération des paramètres
        foreach($paramsURL as $value) {
            if(strpos($value, 'LIMIT=') === false) {
                $url .= $value."&";
            }
        }

        // Configuration du page
        $nbPerPage  = $nbreParPage;
        $nbAround   = 2;
        $urlPager   = $script."?".rtrim($url, "&");
        $limit      = $_GET["LIMIT"];
        $countNum   = $total;
        if ($countNum > $nbPerPage) {
            require_once __DIR__ . '/../CommonBlocs/pager.php';
        }
    ?>

    <?php include (__DIR__ . "/../CommonBlocs/invisible.php"); ?>

    <div class="CB"></div>
</div>


<?php
$this->javascripts[] = <<<'EOD'
    $(campaigns.shs_03_2014);
    $('select').niceSelect();
EOD;
?>
