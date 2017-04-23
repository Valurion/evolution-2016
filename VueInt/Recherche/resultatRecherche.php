<?php

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */



function formatrewriting($chaine) {
    //les accents
    //$chaine=  strtolower(trim($chaine));

    $chaine = remove_accents($chaine);
    $chaine = strtolower(trim($chaine));
    //les caracètres spéciaux (aures que lettres et chiffres en fait)
    $chaine = preg_replace('/([^.a-z0-9]+)/i', '-', $chaine);
    if (substr($chaine, 0, 6) == 'revue-')
        $chaine = substr($chaine, 6);
    return $chaine;
}
?>

<style>
    .pth_gray
    {
        background-color: graytext;
    }
    .contexte b {
        background-color: rgba(200, 199, 46, 0.5);
        border-radius: 3px;
        padding: 0 4px;
    }

    #associated_keywords {
        margin-bottom : 1em;
    }
    #associated_keywords li {
        display : inline-block;
    }
    #associated_keywords .white_button {
        padding : 0 0.2em;
    }


</style>

<?php

$this->javascripts[] = <<<'EOD'

    $(document).ready(function() {

        // Select All/None Toggler
        jQuery(document).on("change", "input[data-id^='toggle-check-all']", function() {
            // Récupération du type
            var type = $(this).data("type");

            // On remet le compteur START à ZERO
            jQuery('#form-filter #START').val(0);

            // isCheck or Not ?
            var checkStatut = jQuery(this).prop("checked");

            // Toggler
            $("input[rel='"+type+"']").prop("checked", checkStatut);

            // On sélectionne TOUT
            if(checkStatut === true) {
                jQuery("#form-filter #"+type).val('ALL');
            }
            // On sélectionne une partie
            else {
                // Refresh des valeurs
                refreshFacettesValues(type);
            }
        });


        // Modification d'une valeur de facette
        jQuery(document).on("change", "input[data-id='facette']", function() {

            // Récupération du type
            var type = $(this).attr("rel");

            // On remet le compteur START à ZERO
            jQuery('#form-filter #START').val(0);

            // Refresh
            refreshFacettesValues(type);
        });

    });



    // Refresh Facettes Values
    function refreshFacettesValues(type) {
        // Init
        var values      = [];
        var nbreTotal   = $("#form-facettes input[rel='"+type+"']").length;
        var nbreSelect  = 0;

        // Parcours des valeurs
        $("#form-facettes input[rel='"+type+"']").each(function() {
            // isCheck or Not ?
            if(jQuery(this).prop("checked") === true) {
                values.push(jQuery(this).val());
                nbreSelect++;
            }
        });

        // Assignation
        jQuery("#form-filter #"+type).val(values.join());

        // Toggle All / None
        // Si on sélectionne manuellement les valeurs, le bouton All / Nones doit être modifiée en fonction du nombre d'élément selectionné
        if(nbreTotal != nbreSelect) { $("input[data-id^='toggle-check-all-"+type+"']").prop("checked", false); }
        if(nbreTotal == nbreSelect) { $("input[data-id^='toggle-check-all-"+type+"']").prop("checked", true); jQuery("#form-filter #"+type).val('ALL');}

    }

    // Changement de page
    function move(limit) {
        // Modification du point de départ
        $('#START').val(limit);
        // Soumission du formulaire
        $('form#form-filter').submit();
    }

    function cairn_search_deploy_pertinent_articles(idNumPublie, node){
        cairn_search_others_pertinent_articles(idNumPublie, node, '');
    }
    function cairn_search_others_pertinent_articles(idNumPublie, node, idArticle){
        if (idNumPublie != "0")
        {
            // Init
            var fd = new FormData();

            // Ajout de l'ID du numéro
            fd.append('ID_NUMPUBLIE',idNumPublie);

            // Ajout du terme de recherche
            if($("#searchTerm").val() !='') {
                fd.append('searchTerm',$("#searchTerm").val());
            }

            // Ajout de l'ID de l'article, si il existe
            if(idArticle && idArticle !=''){
                fd.append('ID_ARTICLE',idArticle);
            }

            // Récupération de la recherche booleen
            fd.append('booleanCondition', booleanCondition);

            $.ajax({
                url: 'index.php?controleur=Recherche&action=pertinent',
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function(data) {

                    //alert(data);
                    $('#__pertinent_' + idNumPublie).html(data);
                    $('#__pertinent_' + idNumPublie).slideToggle(400);
                }
            });

            $(node).attr("onclick", "$(this).toggleClass('active');cairn_search_toggle_pertinent_articles('" + idNumPublie + "')");

        }
        else
            $('#__pertinent_' + idNumPublie).slideToggle(400);
    }

    function cairn_search_toggle_pertinent_articles(idNumPublie)
    {
        $('#__pertinent_' + idNumPublie).slideToggle(400);
    }


EOD;
?>
<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//var_dump($results);
?>
<?php $this->titre = "Search results"; ?>

<?php
    // Cette fonction récupère la valeur reçue en URL contenant des doubles quotes (expression exacte) et transforme la valeur en
    // remplacant les espaces en +, ceci permet alors d'avoir un comportement sensiblement le même
    function transformQuoteValue($value) {

        // Détection des doubles quotes
        if( (substr($value, 0, 1) == "\"") && (substr($value, -1, 1) == "\"") ) {
            // Transformation de la valeur
            $value = str_replace(" ", "+", substr($value, 1, -1));
        }
        return $value;
    }
?>

<div id="body-content" class="searchResult">

    <!-- Search Result : Header -->
    <div id="search_header">
        <!-- Nbre de résultats -->
        <div class="search_count"><?php echo number_format((int) $stats->TotalFiles,0,'.',' '); ?> result<?php if($stats->TotalFiles >1) {echo "s";}?></div>

        <!-- Tri -->
        <div class="resultsOrder">
            <form id="form-filter" action="resultats_recherche.php" name="form-filter" method="POST" style="display: inline;">
                <label class="filter-order-label">Ordered by:</label>
                <select id="orderby" name="orderby" onchange="$('form#form-filter').submit();" >
                    <option value="ranking" <?php if ($sortMode == 'ranking') echo ' selected '; ?>>Relevance</option>
                    <option value="byField" <?php if ($sortMode == 'byField') echo ' selected '; ?>>Date of publication</option>
                    <option value="mostRecent" <?php if ($sortMode == 'mostRecent') echo ' selected '; ?>>Date of publication on Cairn</option>
                </select>

                <!-- Filtre Non-Visibles -->
                <input type="hidden" id="efta" name="efta" value="<?php echo implode(",", $facettesSelected["efta"]); ?>" />
                <input type="hidden" id="dr" name="dr" value="<?php echo implode(",", $facettesSelected["dr"]); ?>" />
                <input type="hidden" id="id_r" name="id_r" value="<?php echo implode(",", $facettesSelected["id_r"]); ?>" />
                <input type="hidden" id="dp" name="dp" value="<?php echo implode(",", $facettesSelected["dp"]); ?>" />
                <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo transformQuoteValue($_REQUEST["searchTerm"]); ?>" />
                <input type="hidden" id="searchTermAccess" name="searchTermAccess" value="<?php echo $searchTermAccess; ?>" />
                <input type="hidden" id="filter" name="filter" value="1" />
                <input type="hidden" id="START" name="START" value="<?php echo $limit; ?>" />
                <textarea style="display: none;" id="facettesToDisplay" name="facettesToDisplay"><?php echo serialize($facettesToDisplay); ?></textarea>

                <?php
                    // Reconstruction des valeurs du formulaire de recherche avancé
                    // REQUEST est utilisé car les valeurs peuvent venir en GET depuis le formulaire de recherche mais aussi en POST depuis le formulaire de filtre
                    // On ne peut pas utiliser le GET pour les filtres car les URLs seraient beaucoup trop longues.
                    if(isset($_REQUEST["submitAdvForm"])) {

                        // Element à ne pas reprendre (il se trouve déjà dans les filtres Non-visibles)
                        $SrcToExclude = array("TypePub", "Disc", "Tr", "Year");

                        // Boucle sur les paramètres
                        $nCount  = 0;                       // Recalcul du nParams sans les éléments excluts
                        $nParams = $_REQUEST["nparams"];    // Valeur de départ

                        for($i = 1; $i <= $nParams; $i++) {
                            // Récupération des valeurs
                            $src        = $_REQUEST["src".$i];
                            $value      = $_REQUEST["word".$i];
                            $operator   = $_REQUEST["operator".$i];

                            // La source n'est pas dans le tableau d'exclusion
                            if(!in_array($src, $SrcToExclude)) {
                                $nCount++;
                                echo "<input type=\"hidden\" id=\"src".$nCount."\" name=\"src".$nCount."\" value=\"".$src."\" />";
                                echo "<input type=\"hidden\" id=\"word".$nCount."\" name=\"word".$nCount."\" value=\"".$value."\" />";
                                echo "<input type=\"hidden\" id=\"operator".$nCount."\" name=\"operator".$nCount."\" value=\"".$operator."\" />";
                            }
                        }
                        echo "<input type=\"hidden\" id=\"nparams\" name=\"nparams\" value=\"".$nCount."\" />";
                        echo "<input type=\"hidden\" id=\"submitAdvForm\" name=\"submitAdvForm\" value=\"Rechercher\" />";
                    }
                ?>
                <?php
                    // Création d'une variable JS contenu la recherche
                    $this->javascripts[] = "var booleanCondition = '".$booleanCondition."';";
                ?>

            </form>
        </div>

        <!-- Facettes -->
        <div class="search_filter right">
            <a class="blue_button" onclick="$('#search_facettes').toggle();$(this).toggleClass('active')">Filters</a>
        </div>
    </div>

    <!-- Facettes -->
    <div id="search_facettes">
        <div id="search_facettes_wrapper" class="container">
            <form action="javascript:void(0)" id="form-facettes" name="form-facettes">
                <div class="facette_column">
                    <div class="facette_title">Text</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["efta"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-efta" data-type="efta" type="checkbox" id="" name="" value="efta" /> <span>All / None</span></label></li>
                            <?php
                                // Affichage des facettes
                                foreach($facettesToDisplay["efta"] as $facette) {
                                    // Init
                                    $checked = "checked";
                                    // Facette sélectionnée
                                    if( (count($facettesSelected["efta"]) > 0) && (!in_array($facette["ID"], $facettesSelected["efta"])) ) {$checked = "";}

                                    // HTML
                                    echo "<li><label><input $checked data-id=\"facette\" type=\"checkbox\" id=\"efta_".$facette["ID"]."\" name=\"efta_".$facette["ID"]."\" value=\"".$facette["ID"]."\" rel=\"efta\" /> <span>".stripslashes(htmlspecialchars_decode($facette["LABEL"]))." <b>(".number_format($facette["NBRE"], 0, "", " ").")</b></span></label></li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="facette_column">
                    <div class="facette_title">Research areas</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["dr"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-dr" data-type="dr" type="checkbox" id="" name="" value="dr" /> <span>All / None</span></label></li>
                            <?php
                                // Affichage des facettes
                                foreach($facettesToDisplay["dr"] as $facette) {
                                    // Init
                                    $checked = "checked";
                                    // Facette sélectionnée
                                    if( (count($facettesSelected["dr"]) > 0) && (!in_array($facette["ID"], $facettesSelected["dr"])) ) {$checked = "";}

                                    echo "<li><label><input $checked data-id=\"facette\" type=\"checkbox\" id=\"dr_".$facette["ID"]."\" name=\"dr_".$facette["ID"]."\" value=\"".$facette["ID"]."\" rel=\"dr\"  /> <span>".stripslashes(htmlspecialchars_decode($facette["LABEL"]))." <b>(".number_format($facette["NBRE"], 0, "", " ").")</b></span></label></li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="facette_column">
                    <div class="facette_title">Journals</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["id_r"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-id_r" data-type="id_r" type="checkbox" id="" name="" value="id_r" /> <span>All / None</span></label></li>
                            <?php
                                // Affichage des facettes
                                foreach($facettesToDisplay["id_r"] as $facette) {
                                    // Init
                                    $checked = "checked";
                                    // Facette sélectionnée
                                    if( (count($facettesSelected["id_r"]) > 0) && (!in_array($facette["ID"], $facettesSelected["id_r"])) ) {$checked = "";}

                                    echo "<li><label><input $checked data-id=\"facette\" type=\"checkbox\" id=\"id_r_".$facette["ID"]."\" name=\"id_r_".$facette["ID"]."\" value=\"".$facette["ID"]."\" rel=\"id_r\"  /> <span>".stripslashes(htmlspecialchars_decode($facette["LABEL"]))." <b>(".number_format($facette["NBRE"], 0, "", " ").")</b></span></label></li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="facette_column">
                    <div class="facette_title">Year</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["dp"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-dp" data-type="dp" type="checkbox" id="" name="" value="dp" /> <span>All / None</span></label></li>
                            <?php
                                // Affichage des facettes
                                foreach($facettesToDisplay["dp"] as $facette) {
                                    // Init
                                    $checked = "checked";
                                    // Facette sélectionnée
                                    if( (count($facettesSelected["dp"]) > 0) && (!in_array($facette["ID"], $facettesSelected["dp"])) ) {$checked = "";}

                                    echo "<li><label><input $checked data-id=\"facette\" type=\"checkbox\" id=\"dp_".$facette["ID"]."\" name=\"dp_".$facette["ID"]."\" value=\"".$facette["ID"]."\" rel=\"dp\"  /> <span>".stripslashes(htmlspecialchars_decode($facette["LABEL"]))." <b>(".number_format($facette["NBRE"], 0, "", " ").")</b></span></label></li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>

                <div class="center">
                    <input class="button" id="submitFacettes" name="submitFacettes" type="submit" value="Filter results" onclick="$('form#form-filter').submit();">
                </div>
            </form>
        </div>
    </div>

    <div class="results_list list_articles">
        <!-- Aucun résultat -->
        <?php if($stats->TotalFiles == 0) { ?>
            <div class="alert alert-warning">
                <p style="font-weight: bold;">
                    Sorry...<br />
                    Your search did not match any results.
                </p>
                <p>
                    Was the spelling correct, without any typing mistakes?<br />
                    Do not hesitate to check our <a style="color: #8a6d3b;text-decoration: underline;" href="http://www.cairn-int.info/help.php">help pages</a> if needed.
                </p>
            </div>
            <?php
                $this->javascripts[] = "$('#form-filter').hide();";
                $this->javascripts[] = "$('.search_filter').hide();";
            ?>
        <?php } ?>

        <?php foreach ($results as $result) : ?>
            <?php
            //recup variables
            /*if (isset($result->item->packed) && (int) $result->item->packed == '1') {
                $pack = 1;
            } else {
                $pack = 0;
            }*/
            /*if ((int) $result->userFields->tp == 3) {
                $offset = (int) $result->userFields->tp + 2 * (int) $result->userFields->tnp;
            } else {
                $offset = (int) $result->userFields->tp;
            }*/
            $offset = 1;
            $typePubTitle = $typeDocument[$pack][$offset];

            //$typePub = $result->userFields->tp;
            $typePub = 1;

            $typeNumPublie = $result->userFields->tnp;
            $ARTICLE_ID_ARTICLE = $result->userFields->id;
            $ARTICLE_ID_REVUE = $result->userFields->id_r;
            $NUMERO_ID_REVUE = $ARTICLE_ID_REVUE;
            $ARTICLE_PRIX = $result->userFields->px;

            $ARTICLE_ID_NUMPUBLIE = $result->userFields->np;
            $NUMERO_ID_NUMPUBLIE = $ARTICLE_ID_NUMPUBLIE;
            $ARTICLE_TITRE = $result->userFields->tr;
            $NUMERO_TITRE = $result->userFields->titnum;
            $NUMERO_SOUS_TITRE = $metaNumero[$NUMERO_ID_NUMPUBLIE]['SOUS_TITRE'];
            $ARTICLE_HREF = "revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd . ".htm";

            //$dateParution=$metaNumero[$NUMERO_ID_NUMPUBLIE]['DATE_PARUTION'];
            //echo"<p>$dateParution</p>";
            $REVUE_ID = $result->userFields->id_r;
            $authors = explode('|', $result->userFields->auth0);
            $NUMERO_ANNEE = $result->userFields->an;
            $NUMERO_NUMERO = $result->userFields->NUM0;
            $NUMERO_VOLUME = $result->userFields->vol;
            $ARTICLE_PAGE = $result->userFields->pgd;
            if(isset($result->userFields->idp)){
                $PORTAIL = $result->userFields->idp;
            }
            //echo $result->item->hits;
            $arrHits = explode(' ',$result->item->hits);
            $arrHits = array_slice($arrHits,(count($arrHits)-250));
            $hitsStr = implode(' ',$arrHits);
            //echo '<br/>'.$hitsStr;
            $getDocUrlParameters = 'DocId=' . $result->item->docId . '&hits=' . urlencode($hitsStr);
            //$getDocUrlParameters = 'DocId=' . $result->item->docId . '&hits=' . urlencode($result->item->hits);
            $isPdf = (stripos($result->item->Filename, '.pdf') > 0);



            $NOM_EDITEUR = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NOM_EDITEUR'];
            $REVUE_TITRE = $result->userFields->rev0;
            $cfgaArr = explode(',', $result->userFields->cfg0);

            if($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'] != '' && strlen($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'])){
                $NUMERO_MEMO = substr($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'], 0, strpos($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'], " ", 200));
            }else{
                $NUMERO_MEMO = $metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'];
            }
            $CONTEXTE = strip_tags($result->item->Synopsis, '<b>');

            $ARTICLE_HREF = '';
            $NUMERO_HREF = '';
            $REVUE_HREF = "";
            switch ($typePub) {
                case "1":
                    $english = $articlesButtons[$ARTICLE_ID_ARTICLE][2];
                    if($english == ''){
                        $ARTICLE_HREF = "./abstract.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE&" . $getDocUrlParameters;
                    }else{
                        $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE&" . $getDocUrlParameters;
                    }
                    $NUMERO_HREF = "./journal-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                    $REVUE_HREF;
                    $REVUE_HREF = "./journal-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                    break;
            }


            $BLOC_AUTEUR = '';
            $BLOC_AUTEUR_PACK = '';
            if (sizeof($authors) > 2) {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-of-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> and al.";
            } else if (sizeof($authors) == 2) {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-of-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> and ";
                $authors2 = explode('#', $authors[1]);
                $BLOC_AUTEUR .= "<a class=\"yellow\" href=\"publications-of-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> ";
            } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-of-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a>";
            }

            $BLOC_AUTEUR = trim($BLOC_AUTEUR);

            if($BLOC_AUTEUR != ''){
                $BLOC_AUTEUR_PACK = $BLOC_AUTEUR;
            }else{
                $numeroAuteurs = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NUMERO_AUTEUR'];
                $authors = explode('|',$numeroAuteurs);
                if (sizeof($authors) > 2) {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = "<a class=\"yellow\" href=\"publications-of-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> et al.";
                } else if (sizeof($authors) == 2) {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = "<a class=\"yellow\" href=\"publications-of-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> et ";
                    $authors2 = explode(':', $authors[1]);
                    $BLOC_AUTEUR_PACK .= "<a class=\"yellow\" href=\"publications-of-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> ";
                } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = "<a class=\"yellow\" href=\"publications-of-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a>";
                }
                $BLOC_AUTEUR_PACK = trim($BLOC_AUTEUR_PACK);
            }
            //if($BLOC_AUTEUR == '- ')
            //  $BLOC_AUTEUR='';
            ?>

    <?php if ($typePub == 1) : ?>
        <?php if (!$pack) : ?>
            <!-- RECHERCHE DE REVUE -->

            <div class="result article revue" id="<?= $ARTICLE_ID_ARTICLE ?>">
                <!--h2><?= $typePubTitle ?></h2-->
                <div class="pages_article vign_small">
                    <a href="<?= $ARTICLE_HREF ?>">
                        <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L62.jpg" alt="" class="small_cover"/>
                    </a>
                </div>
                <div class="metadata_article">
                    <div class="title"><a href="<?= $ARTICLE_HREF ?>"><strong><?= $ARTICLE_TITRE ?></strong></a></div>
                    <div class="authors">
                        <?= $BLOC_AUTEUR ?>
                    </div>
                    <div class="revue_title">in <a href="<?= $REVUE_HREF ?>" class="title_little_blue"><span class="title_little_blue"><?= $REVUE_TITRE ?></span></a> <strong><?= $NUMERO_ANNEE ?>/<?= $NUMERO_NUMERO ?> (<?= $NUMERO_VOLUME ?>)</strong></div>
                </div>

                <div class="state_article" style="margin-left: 45px;">
                    <?php
                    $abstract = $articlesButtons[$ARTICLE_ID_ARTICLE][0];
                    if($abstract == ''){
                        echo '<span class="button-grey2 w49 left">Abstract</span>';
                    }else{
                        echo '<a href="abstract-'.$ARTICLE_ID_ARTICLE.'--'.$articlesButtons[$ARTICLE_ID_ARTICLE][4].'.htm" class="button-blue2 w49 left">Abstract</a>';
                    }
                    $french = $articlesButtons[$ARTICLE_ID_ARTICLE][1];
                    if($french == ''){
                        echo '<span class="button-grey2 w49 right">French</span>';
                    }else{
                        echo '<a href="'.Service::get('ParseDatas')->getCrossDomainUrl().'/article.php?ID_ARTICLE='.$articlesButtons[$ARTICLE_ID_ARTICLE][5].'" class="button-blue2 w49 right">French</a>';
                    }
                    echo '<br>';
                    $english = $articlesButtons[$ARTICLE_ID_ARTICLE][2];
                    if($english == ''){
                        echo '<span class="button-grey2 w100">English
                                    <span data-article-title="'.$ARTICLE_TITRE.'" data-suscribe-on-translation="'.$ARTICLE_ID_ARTICLE.'" class="question-mark">
                                        <span class="tooltip">Why is this article not available in English?</span>
                                    </span>
                                </span>';
                    }else{
                        echo '<a href="'.$english.(strpos($english,'my_cart.php')===FALSE?('?'.$getDocUrlParameters):'').'" class="button-blue2 w100">';

                        if(strpos($english,'my_cart.php') !== FALSE){
                            echo 'English <span class="cart-icon">'.$articlesButtons[$ARTICLE_ID_ARTICLE][3].' € </span>';
                        }else{
                            if($articlesButtons[$ARTICLE_ID_ARTICLE][3] == 0){
                                echo 'English : Free';
                            }else{
                                echo 'English';
                            }
                        }
                        echo '</a>';
                    }?>


                    <?php
                        require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                        checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                    ?>


                    <?php /*if ($cfgaArr[0] > 0) : ?>
                        <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                            <?php if ($cfgaArr[0] == 1) echo "Résumé"; else if ($cfgaArr[0] == 2) echo "Première page"; else if ($cfgaArr[0] == 3) echo "Premières lignes"; ?>
                        </a>
                    <?php endif; ?>
                    <?php
                    if (isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1) {
                        ?>
                        <?php if ($cfgaArr[1] > 0) : ?>
                            <a href="<?= $ARTICLE_HREF ?>" class="button">
                                Version HTML
                            </a>
                        <?php endif; ?>
                        <?php if ($cfgaArr[2] > 0) : ?>
                            <?php if ($isPdf) : ?>
                                <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                            <?php else: ?>
                                <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                            <?php endif; ?>
                            Feuilleter en ligne
                            </a>
                        <?php endif; ?>

                        <?php if ($cfgaArr[3] > 0) : ?>
                            <a href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                                Version PDF
                            </a>
                        <?php endif; ?>

                        <?php if (count($cfgaArr) > 5 && $cfgaArr[5] > 0) : ?>
                            <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                                <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                            </a>
                            <?php
                        endif;
                    }else {
                        if (isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2) {
                            echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" ' . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '') . '>' . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'] . '</a>';
                        }
                    }
                    require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                    */?>
                </div>
                <div class="contexte"><?= $CONTEXTE ?></div>
            </div>

                    <!-- FIN DE RECHERCHE DE REVUE -->
        <?php endif; ?>
    <?php endif; ?>

<?php endforeach; ?>
</div>



<!--div class="right" style="float:right; padding-top:20px;"><a class="search_button" href="/redirect_to_french_research.php?searchTerm=<?= $searchTerm ?>">Extend your search on cairn.info</a></div-->
<div style="margin-bottom: 20px;text-align:right;"><a class="search_button" href="redirect_to_french_research.php?searchTerm=<?= $searchTerm ?>">Extend your search on cairn.info</a></div>
</div>

<?php
$nbPerPage = 20;
$nbAround = 2;
$jsPager = "move";
//$limit = 20;
$countNum = $stats->TotalFiles;
if ($countNum > $nbPerPage)
    require_once __DIR__ . '/../CommonBlocs/pager.php'; ?>

<?php
$this->javascripts[] = <<<'EOD'
    $('select').niceSelect();
EOD;
?>
