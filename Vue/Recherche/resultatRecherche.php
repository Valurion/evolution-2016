<?php
$ParseDatas = Service::get('ParseDatas');
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

function isTermEqualTr($searchTerm,$tr) {
    $searchTermForm = formatrewriting($searchTerm);
    $trForm = formatrewriting($tr);

    return ($searchTermForm==$trForm);
}

/**
 * Convertit un tableau en metadonnées coins
 * On utilise une balise span invisible avec une classe Z3988 auquel on fournit en attribut `title` les métadonnées sous la forme d'une query GET encodées correctement en encodage-pourcentage
 * Voir http://ocoins.info/
 *
 * TODO:: Vérifier toutes les métadonnées disponible
 *      Je n'ai fais que remplacer/corriger les balises non inteprétés déjà existante. Et je me suis rendu compte que les noms des métadonnées envoyés avant la refonte étaient fausse pour la plupart (joie...)
 *      Il faut aussi insérer les coins là où ils ne sont pas encore. Par exemple, les articles de revues
 */
function arrayToCoins($array, $type='article') {
    $array = array_map('trim', $array);
    $array = array_filter($array);
    $array['ctx_ver'] = 'Z39.88-2004';
    $array['rft_val_fmt'] = ($type === 'book') ? 'info:ofi/fmt:kev:mtx:book' : 'info:ofi/fmt:kev:mtx:journal';
    $query = http_build_query($array, null, '&', PHP_QUERY_RFC3986);
    return '<span class="Z3988" title="'.$query.'"></span>';
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
        // Si on sélectionne manuellement les valeurs, le bouton Toutes / Aucunes doit être modifiée en fonction du nombre d'élément selectionné
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
<?php
// Définition des paramètres de la page
$this->titre = "Résultats de recherche";
require_once  __DIR__ . '/../CommonBlocs/tabs.php';
?>

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
        <div class="search_count"><?php echo number_format((int) $stats->TotalFiles,0,'.',' '); ?> résultat<?php if($stats->TotalFiles >1) {echo "s";}?></div>

        <!-- Tri -->
        <div class="resultsOrder">
            <form id="form-filter" action="resultats_recherche.php" name="form-filter" method="POST" style="display: inline;">
                <label class="filter-order-label">Trié(s) par :</label>
                <select id="orderby" name="orderby" onchange="$('form#form-filter').submit();" >
                    <option value="ranking" <?php if ($sortMode == 'ranking') echo ' selected '; ?>>Pertinence</option>
                    <option value="byField" <?php if ($sortMode == 'byField') echo ' selected '; ?>>Date de parution</option>
                    <option value="mostRecent" <?php if ($sortMode == 'mostRecent') echo ' selected '; ?>>Date de mise en ligne </option>
                </select>

                <!-- Filtre Non-Visibles -->
                <input type="hidden" id="tp" name="tp" value="<?php echo implode(",", $facettesSelected["tp"]); ?>" />
                <input type="hidden" id="dr" name="dr" value="<?php echo implode(",", $facettesSelected["dr"]); ?>" />
                <input type="hidden" id="id_r" name="id_r" value="<?php echo implode(",", $facettesSelected["id_r"]); ?>" />
                <input type="hidden" id="dp" name="dp" value="<?php echo implode(",", $facettesSelected["dp"]); ?>" />
                <input type="hidden" id="searchTerm" name="searchTerm" value="<?php echo transformQuoteValue($_REQUEST["searchTerm"]); ?>" />
                <input type="hidden" id="searchTermAccess" name="searchTermAccess" value="<?php echo $searchTermAccess; ?>" />
                <input type="hidden" id="filter" name="filter" value="1" />
                <input type="hidden" id="START" name="START" value="<?php echo $limit; ?>" />
                <textarea style="display: none;" id="facettesToDisplay" name="facettesToDisplay"><?php echo serialize($facettesToDisplay); ?></textarea>

                <?php if($searchTermPlus["ID_REVUE"]) {?><input type="hidden" id="ID_REVUE" name="ID_REVUE" value="<?=$searchTermPlus["ID_REVUE"];?>" /><?php } ?>
                <?php if($searchTermPlus["ID_NUMPUBLIE"]) {?><input type="hidden" id="ID_NUMPUBLIE" name="ID_NUMPUBLIE" value="<?=$searchTermPlus["ID_REVUE"];?>" /><?php } ?>

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
            <a class="blue_button" onclick="$('#search_facettes').toggle();$(this).toggleClass('active')">Filtres</a>
        </div>
    </div>

    <!-- Facettes -->
    <div id="search_facettes">
        <div id="search_facettes_wrapper" class="container">
            <form action="javascript:void(0)" id="form-facettes" name="form-facettes">
                <div class="facette_column">
                    <div class="facette_title">Types</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["tp"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-tp" data-type="tp" type="checkbox" id="" name="" value="tp" /> <span>Toutes / Aucune</span></label></li>
                            <?php
                                // Affichage des facettes
                                foreach($facettesToDisplay["tp"] as $facette) {
                                    // Init
                                    $checked = "checked";
                                    // Facette sélectionnée
                                    if( (count($facettesSelected["tp"]) > 0) && (!in_array($facette["ID"], $facettesSelected["tp"])) ) {$checked = "";}

                                    // HTML
                                    echo "<li><label><input $checked data-id=\"facette\" type=\"checkbox\" id=\"tp_".$facette["ID"]."\" name=\"tp_".$facette["ID"]."\" value=\"".$facette["ID"]."\" rel=\"tp\" /> <span>".stripslashes(htmlspecialchars_decode($facette["LABEL"]))." <b>(".number_format($facette["NBRE"], 0, "", " ").")</b></span></label></li>";
                                }
                            ?>
                        </ul>
                    </div>

                    <!-- Nouvel élément : Non-défini -->
                    <!--
                    <div class="facette_title">Accès</div>
                    <div class="facette_content">
                        <ul>
                            <li><label><input type="radio" id="" name="" value="" /> <span>Valeur (xxx)</span></label></li>
                            <li><label><input type="radio" id="" name="" value="" /> <span>Valeur (xxx)</span></label></li>
                        </ul>
                    </div>
                    -->
                </div>
                <div class="facette_column">
                    <div class="facette_title">Disciplines</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["dr"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-dr" data-type="dr" type="checkbox" id="" name="" value="dr" /> <span>Toutes / Aucune</span></label></li>
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
                    <div class="facette_title">Revues / Collections</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["id_r"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-id_r" data-type="id_r" type="checkbox" id="" name="" value="id_r" /> <span>Toutes / Aucune</span></label></li>
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
                    <div class="facette_title">Année de parution</div>
                    <div class="facette_content">
                        <ul class="checkbox-list">
                            <li><label><input <?php if(count($facettesSelected["dp"]) == 0) {echo "checked"; } ?> data-id="toggle-check-all-dp" data-type="dp" type="checkbox" id="" name="" value="dp" /> <span>Toutes / Aucune</span></label></li>
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
                    <input class="button" id="submitFacettes" name="submitFacettes" type="submit" value="Filtrer les résultats" onclick="$('form#form-filter').submit();">
                </div>
            </form>
        </div>
    </div>

    <!-- Search Result : Liste -->
    <div class="results_list list_articles" style="margin-bottom: 20px;">
        <!-- Aucun résultat -->
        <?php if($stats->TotalFiles == 0) { ?>
            <div class="alert alert-warning">
                <p style="font-weight: bold;">
                    Désolé...<br />
                    Votre recherche n’a donné aucun résultat.
                </p>
                <p>
                    L’orthographe utilisée était-elle bien correcte, sans faute de frappe ?<br />
                    Peut-être pourriez-vous essayer notre <a style="color: #8a6d3b;text-decoration: underline;"; href="recherche_avancee.php">formulaire de recherche avancée</a>, ou élargir vos critères ?<br />
                    N’hésitez pas en tout cas à consulter nos <a style="color: #8a6d3b;text-decoration: underline;" href="http://aide.cairn.info/tag/recherche/">pages d'aide dédiées à la recherche</a> pour tout complément.
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
            if (isset($result->item->packed) && (int) $result->item->packed == '1') {
                if(isTermEqualTr($searchTerm,$result->userFields->tr)){
                    $pack = 2;
                }else{
                    $pack = 1;
                }
            } else {
                $pack = 0;
            }
            if ((int) $result->userFields->tp == 3) {
                $offset = (int) $result->userFields->tp + 2 * (int) $result->userFields->tnp;
            } else {
                $offset = (int) $result->userFields->tp;
            }
            $typePubTitle = $typeDocument[$pack][$offset];
            $typePub = $result->userFields->tp;
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

            //Correctif WebTrends, pour les auteurs.
            $auteurTemp = $result->userFields->auth0;
            $result->userFields->auth0 = '';
            foreach(explode('|', $auteurTemp) as $auteur) {
                $auteur = explode('#', $auteur);
                $nom = isset($auteur[1]) ? $auteur[1] : '';
                $prenom = isset($auteur[2]) ? $auteur[2] : '';
                $id = isset($auteur[3]) ? $auteur[3] : '';
                // list($vide ,$nom, $prenom, $id) = explode('#', $auteur);
                $result->userFields->auth0 .= '#' . $prenom . '#' . $nom . '#' . $id . '|';
            }
            $result->userFields->auth0 = trim($result->userFields->auth0, '|');
            //Fin du correctif webTrends des auteurs.

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
            $getDocUrlParameters = '&DocId=' . $result->item->docId . '&hits=' . urlencode($hitsStr);
            //$getDocUrlParameters = '&DocId=' . $result->item->docId . '&hits=' . urlencode($result->item->hits);
            $isPdf = (stripos($result->item->Filename, '.pdf') > 0);



            $NOM_EDITEUR = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NOM_EDITEUR'];
            $REVUE_TITRE = $result->userFields->rev0;
            $cfgaArr = explode(',', $result->userFields->cfg0);

            // On réduit le contexte des résultats à N mots, quoi qu'il arrive.
            // Le moteur de recherche de pythagoria renvoie N mots APRÈS la dernière occurence (ce qui est une autre logique)
            // Voir #69476
            $result->item->Synopsis = preg_split('/\s+(?!<BR>|&nbsp;)/i', $result->item->Synopsis);
            $result->item->Synopsis = array_slice($result->item->Synopsis, 0, $searchT['amountOfContext']);
            $result->item->Synopsis = implode(' ', $result->item->Synopsis);
            // En gros, j'essaye de rajouter 3 points à la fin d'un contexte.
            // Mais la regexp est un peu naze, ça provoquera certainement une demande dans le futur.
            if (!preg_match('/(...\s*<BR\s*\/?>\s*)$/i', $result->item->Synopsis)) {
                $result->item->Synopsis .= ' ...';
            }

            if($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'] != '' && strlen($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'])) {
                $NUMERO_MEMO = explode(' ', $metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO']);
                $NUMERO_MEMO = array_slice($NUMERO_MEMO, 0, min($contextForNumeroMemo, count($NUMERO_MEMO)));
                $NUMERO_MEMO = implode(' ', $NUMERO_MEMO);
            }else{
                $NUMERO_MEMO = $metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'];
            }
            $NUMERO_MEMO = strip_tags($NUMERO_MEMO);
            $CONTEXTE = strip_tags($result->item->Synopsis, '<b>');

            $ARTICLE_HREF = '';
            $NUMERO_HREF = '';
            $REVUE_HREF = "";
            switch ($typePub) {
                case "1":
                    $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
                    $NUMERO_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                    $REVUE_HREF;
                    $REVUE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                    break;
                case "2":
                    $ARTICLE_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
                    $ARTICLE_HREF = "./article.php?ID_ARTICLE=".urlencode($ARTICLE_ID_ARTICLE).$getDocUrlParameters;
                    $NUMERO_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING']. "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                    $REVUE_HREF;
                    $REVUE_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                    break;
                case "3":

                    $ARTICLE_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
                    $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
                    $NUMERO_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . ".htm";
                    $REVUE_HREF = "./collection-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                    break;

                case "6":

                    $ARTICLE_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
                    $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
                    $NUMERO_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . ".htm";
                    $REVUE_HREF = "./collection-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                    break;
            }


            $BLOC_AUTEUR = '';
            $BLOC_AUTEUR_PACK = '';
            if (sizeof($authors) > 2) {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> et al.";
            } else if (sizeof($authors) == 2) {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> et ";
                $authors2 = explode('#', $authors[1]);
                $BLOC_AUTEUR .= "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> ";
            } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a>";
            }

            $BLOC_AUTEUR = trim($BLOC_AUTEUR);

            if($BLOC_AUTEUR != ''){
                $BLOC_AUTEUR_PACK = $BLOC_AUTEUR;
            }else{
                $numeroAuteurs = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NUMERO_AUTEUR'];
                $authors = explode('|',$numeroAuteurs);
                if (sizeof($authors) > 2) {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[0] . '--' . $authors2[2] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . "</a> et al.";
                } else if (sizeof($authors) == 2) {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[0] . '--' . $authors2[2] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . "</a> et ";
                    $authors2 = explode(':', $authors[1]);
                    $BLOC_AUTEUR_PACK .= "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[0] . '--' . $authors2[2] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . "</a> ";
                } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[0] . '--' . $authors2[2] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . "</a>";
                }
                $BLOC_AUTEUR_PACK = trim($BLOC_AUTEUR_PACK);
            }
            //if($BLOC_AUTEUR == '- ')
            //  $BLOC_AUTEUR='';
            ?>

            <?php if ($typePub == 6) : ?>
                <?php if (!$pack || $pack == 2) : ?>
                    <!-- RECHERCHE D'ENCYCLOPÉDIE DE POCHE -->

                    <div class="result article encyclopedie" id="<?= $ARTICLE_ID_ARTICLE ?>">
                        <h2><?= $typePubTitle ?> </h2>
                        <div class="wrapper_meta">
                            <a href="<?= $ARTICLE_HREF ?>">
                                <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                            </a>
                            <div class="meta">
                                <a href="<?= $ARTICLE_HREF ?>">
                                    <div class="title"><strong><?= $ARTICLE_TITRE ?></strong></div>
                                </a>
                                <div class="authors">
                                    <span class="author">
                                        <?= $BLOC_AUTEUR ?>
                                    </span>
                                </div>
                                <div class="revue_title">Dans <a href="<?= $NUMERO_HREF ?>" class="title_little_blue"><span class="title_little_blue"><?= $NUMERO_TITRE ?></span></a> <strong><?= $NOM_EDITEUR ?>, (<?= $NUMERO_ANNEE ?>)</strong></div>
                            </div>
                        </div>
                        <div class="contexte"><?= $CONTEXTE ?></div>
                        <!--
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]

                        [BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                        <a href="[URL]&amp;AJOUTBIBLIO=[ARTICLE_ID_ARTICLE]#[ARTICLE_ID_ARTICLE]" class="icon icon-add-biblio">&#160;</a>
                        [/BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                        [BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                        <span class="infoajout">Ajout&eacute; &agrave; <a href="./biblio.php" class="yellow"><strong>ma bibliographie</strong></a> <span class="icon icon-remove-biblio"></span></span>
                        [/BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                    </div>
                        -->

                <?php
                    $meta = $metaNumero[$NUMERO_ID_NUMPUBLIE];
                    $firstAuthor = explode(':', explode('|', $meta['NUMERO_AUTEUR'])[0]);
                    $coins = [
                        'rft.atitle' => $ARTICLE_TITRE,
                        'rft.jtitle' => $NUMERO_TITRE,
                        'rft.title' => $NUMERO_TITRE,
                        'rft.volume' => preg_replace('/\s*n°\s*/', '', $NUMERO_VOLUME),
                        'rft.issue' => $NUMERO_ANNEE.'/'.$NUMERO_NUMERO,
                        'rft.isbn' => $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'],
                        'rft.aulast' => $firstAuthor[1],
                        'rft.aufirst' => $firstAuthor[0],
                        'rft.au' => $firstAuthor[1] . ' ' .$firstAuthor[0],
                        'rft.pub' => $meta['NOM_EDITEUR'],
                        'rft.date' => $NUMERO_ANNEE,
                        'rft.issn' => null,
                        'rft.eissn' => null,
                        'rft.genre' => 'article',
                    ];
                    echo arrayToCoins($coins);
                ?>
                        <div class="state">

                            <?php if ($cfgaArr[0] > 0) : ?>
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
                                        <a
                                            href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                            class="button"
                                            data-webtrends="goToPdfArticle"
                                            data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                            data-titre=<?=
                                                $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                            ?>
                                            data-authors=<?=
                                                $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )
                                                )
                                            ?>
                                        >
                                            Version PDF
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($cfgaArr[5] > 0) : ?>
                                        <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                                            <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                                        </a>
                                        <?php
                                    endif;
                                }else {
                                    if (isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2) {
                                        //WebTrends : "tracking sur les boutons d'ajout au panier"
                                        if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                            echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                                    . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                                    . 'data-webtrends="goToMonPanier" '
                                                    . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                                    . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                                    . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE) . ' '
                                                    . 'data-authors=' . $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )) . ' '
                                                    . '>'
                                                    . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']
                                                    . '</a>';
                                        } else {
                                            echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" ' . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '') . '>' . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'] . '</a>';
                                        }
                                    }
                                }
                                // Article EN
                                if($result->userFields->cairnArticleInt) {
                                    // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                                    $array              = $result->userFields->cairnArticleInt;
                                    $id_article_int     = $array["ID_ARTICLE"];
                                    $url_article_int    = $array["URL_REWRITING_EN"];

                                    echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                                }
                                // Article EN
                                if($result->userFields->cairnArticleInt) {
                                    // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                                    $array              = $result->userFields->cairnArticleInt;
                                    $id_article_int     = $array["ID_ARTICLE"];
                                    $url_article_int    = $array["URL_REWRITING_EN"];

                                    echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                                }

                                if($pack == 2){?>
                                    <div class="state">
                                        <a href="#" class="button" onclick="$(this).toggleClass('active');
                                                cairn_search_others_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this, '<?= $ARTICLE_ID_ARTICLE ?>');">Autres articles pertinents pour ce numéro</a>
                                    </div>
                                <?php }
                                ?>
                        </div>
                        <?php if($pack == 2){?>
                            <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>"></div>
                        <?php } ?>
                    </div>

                <?php else: ?>

                    <div class="result article encyclopedie" id="<?= $ARTICLE_ID_ARTICLE ?>">
                        <h2><?= $typePubTitle ?></h2>
                        <div class="wrapper_meta">
                            <a href="<?= $NUMERO_HREF ?>">
                                <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                            </a>
                            <div class="meta">
                                <div class="revue_title">
                                    <a href="<?= $NUMERO_HREF ?>" class="title_little_blue">
                                        <span class="title_little_blue"><?= $NUMERO_TITRE ?></span>
                                    </a>
                                    <strong>(<?= $NOM_EDITEUR ?>, <?= $NUMERO_ANNEE ?>)</strong>
                                </div>
                                <!-- <a href="resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=[NUMERO_HREF]&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]"> -->
                                <div class="title">dans <a href="./que-sais-je-et-reperes.php?ID_REVUE=<?= $REVUE_ID ?>"><strong><?= $REVUE_TITRE ?></strong></a></div>
                                <!-- </a> -->
                                <div class="authors">
                                    <?= $BLOC_AUTEUR_PACK ?>
                                </div>
                            </div>
                        </div>
                        <div class="contexteMemo"><?= $NUMERO_MEMO ?></div>
                        <div class="state">

                            <a href="<?= $NUMERO_HREF ?>"  class="button">Présentation/Sommaire</a>
                            <a href="#" class="button" onclick="$(this).toggleClass('active');
                                                cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                            <?php
                            // Article EN
                            if($result->userFields->cairnArticleInt) {
                                // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                                $array              = $result->userFields->cairnArticleInt;
                                $id_article_int     = $array["ID_ARTICLE"];
                                $url_article_int    = $array["URL_REWRITING_EN"];

                                echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                            }
                            require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                            ?>
                        </div>

                        <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>">

                            <div class="meta">
                                <div>
                                    <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                                    <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>

                                    <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                                </div>
                                <div class="contexte">[CONTEXTE]</div>
                                <div class="state">
                                    [LISTE_CONFIG_ARTICLE]
                                    <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                                       [ARTICLE_LIBELLE_LIBELLE]
                                </a>
                                [/LISTE_CONFIG_ARTICLE]

                                [BLOC_CREDIT_INST]
                                [BLOC_ARTICLE_ACHAT]
                                <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                                [/BLOC_ARTICLE_ACHAT]
                                [/BLOC_CREDIT_INST]

                                [BLOC_CAIRN_INST_ACHAT]
                                [BLOC_CREDIT_INST_OFF]
                                [BLOC_ARTICLE_ACHAT]
                                <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                                    <span class="button first">Consulter</span>
                                    <span class="icon icon-add-to-cart"></span>
                                    <span class="button last">[ARTICLE_PRIX] €</span>
                                </a>
                                [/BLOC_ARTICLE_ACHAT]
                                [/BLOC_CREDIT_INST_OFF]
                                [/BLOC_CAIRN_INST_ACHAT]
                            </div>
                        </div>
                        <hr class="grey" />
                    </div>
                </div>

                <!-- FIN DE RECHERCHE D'ENCYCLOPÉDIE DE POCHE -->

            <?php endif; ?>
        <?php endif; ?>







        <?php if ($typePub == 3) : ?>
            <?php if (!$pack || $pack == 2) : ?>
                <!-- RECHERCHE D'OUVRAGE -->

                <div class="result article ouvrage" id="<?= $ARTICLE_ID_ARTICLE ?>">
                    <h2><?= $typePubTitle ?></h2>
                    <div class="wrapper_meta">
                        <a href="<?= $ARTICLE_HREF ?>">
                            <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                        </a>
                        <div class="meta">
                            <a href="<?= $ARTICLE_HREF ?>">
                                <div class="title"><strong><?= $ARTICLE_TITRE ?></strong></div>
                            </a>
                            <div class="authors">

                                <?= $BLOC_AUTEUR ?>

                            </div>
                            <div class="revue_title">Dans <a href="<?= $NUMERO_HREF ?>" class="title_little_blue"><span class="title_little_blue"><?= $NUMERO_TITRE ?><?php if ($NUMERO_SOUS_TITRE != '') echo '. ' . $NUMERO_SOUS_TITRE; ?></span></a>  <strong>(<?= $NOM_EDITEUR ?>, <?= $NUMERO_ANNEE ?>)</strong></div>
                        </div>
                    </div>
                    <div class="contexte"><?= $CONTEXTE ?></div>
                    <!--
                    <div class="state">

                        <a href="[ARTICLE_LIBELLE_HREF]" class="button">
                            [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]

                        [BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                        <a href="[URL]&amp;AJOUTBIBLIO=[ARTICLE_ID_ARTICLE]#[ARTICLE_ID_ARTICLE]" class="icon icon-add-biblio">&#160;</a>
                        [/BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                        [BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                        <span class="infoajout">Ajout&eacute; &agrave; <a href="./biblio.php" class="yellow"><strong>ma bibliographie</strong></a> <span class="icon icon-remove-biblio"></span></span>
                        [/BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                    </div>
                    -->
                    <div class="state">

                        <?php if ($cfgaArr[0] > 0) : ?>
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
                                    <a
                                        href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                        class="button"
                                        data-webtrends="goToPdfArticle"
                                        data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                        data-titre=<?=
                                            $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                        ?>
                                        data-authors=<?=
                                            $ParseDatas->cleanAttributeString(
                                                $ParseDatas->stringifyRawAuthors(
                                                    str_replace(
                                                        '#',
                                                        $ParseDatas::concat_name,
                                                        implode($ParseDatas::concat_authors, $authors)
                                                    ), 0, ';'
                                                )
                                            )
                                        ?>
                                    >
                                        Version PDF
                                    </a>
                                <?php endif; ?>
                                <?php if ($cfgaArr[5] > 0) : ?>
                                    <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                                        <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                                    </a>
                                    <?php
                                endif;
                            }else {
                                if (isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2) {
                                    //WebTrends : "tracking sur les boutons d'ajout au panier"
                                    if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                        echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                            . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                            . 'data-webtrends="goToMonPanier" '
                                            . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                            . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                            . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE) . ' '
                                            . 'data-authors=' . $ParseDatas->cleanAttributeString(
                                            $ParseDatas->stringifyRawAuthors(
                                                str_replace(
                                                    '#',
                                                    $ParseDatas::concat_name,
                                                    implode($ParseDatas::concat_authors, $authors)
                                                ), 0, ';'
                                            )) . ' '
                                            . '>'
                                            . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']
                                            . '</a>';
                                    } else {
                                        echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" ' . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '') . '>' . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'] . '</a>';
                                    }
                                }
                            }
                            // Article EN
                            if($result->userFields->cairnArticleInt) {
                                // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                                $array              = $result->userFields->cairnArticleInt;
                                $id_article_int     = $array["ID_ARTICLE"];
                                $url_article_int    = $array["URL_REWRITING_EN"];

                                echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                            }
                            require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                            if($pack == 2){?>
                                <div class="state">
                                    <a href="#" class="button" onclick="$(this).toggleClass('active');
                                            cairn_search_others_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this, '<?= $ARTICLE_ID_ARTICLE ?>');">Autres articles pertinents pour ce numéro</a>
                                </div>
                            <?php }
                            ?>

                    </div>
                    <?php if($pack == 2){?>
                        <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>"></div>
                    <?php } ?>

                </div>

            <?php else: ?>

                <div class="result numero ouvrage" id="<?= $NUMERO_ID_NUMPUBLIE ?>">
                    <h2><?= $typePubTitle ?></h2>
                    <div class="wrapper_meta">
                        <a href="<?= $NUMERO_HREF ?>">
                            <img src="/<?= $vign_path ?>/<?= $NUMERO_ID_REVUE ?>/<?= $NUMERO_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                        </a>
                        <div class="meta">
                            <div class="revue_title"><a href="<?= $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] ?>--<?= $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] ?>.htm" class="title_little_blue"><span class="title_little_blue"><?= $NUMERO_TITRE . '. ' . $NUMERO_SOUS_TITRE ?> </span></a> <strong>(<?= $NOM_EDITEUR ?>, <?= $NUMERO_ANNEE ?>)</strong></div>
                            <div class="title">dans
                                <a href="<?= $REVUE_HREF ?>"><strong><?= $REVUE_TITRE ?></strong></a>
                            </div>

                            <div class="authors">
                                <?php if ($typeNumPublie == 1 && trim($BLOC_AUTEUR) <> '') : ?>
                                    Sous la direction de
                                <?php endif; ?>
                                <?= $BLOC_AUTEUR_PACK ?>
                            </div>
                        </div>
                    </div>

                    <div class="contexteMemo"><?= $NUMERO_MEMO ?> ...</div>
                    <div class="state">
                        <a href="<?= $NUMERO_HREF ?>"  class="button">Présentation/Sommaire</a>
                        <a href="#" class="button" onclick="$(this).toggleClass('active');
                                            cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                        <?php
                        // Article EN
                        if($result->userFields->cairnArticleInt) {
                            // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                            $array              = $result->userFields->cairnArticleInt;
                            $id_article_int     = $array["ID_ARTICLE"];
                            $url_article_int    = $array["URL_REWRITING_EN"];

                            echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                        }
                        require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                        checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                        ?>
                    </div>
                    <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>">
                        [LISTE_RESULTAT_ARTICLES_CONTRIB_OUVRAGE]
                        <div class="meta">
                            <div>
                                <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                                <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                                <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                            </div>
                            [BLOC_NUMERO_TYPE_NUMPUBLIE_1]
                            <div class="authors">
                                [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                                [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                            </div>
                            [/BLOC_NUMERO_TYPE_NUMPUBLIE_1]
                            <div class="contexte">[CONTEXTE]</div>
                            <div class="state">
                                [LISTE_CONFIG_ARTICLE]
                                <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                                   [ARTICLE_LIBELLE_LIBELLE]
                            </a>
                            [/LISTE_CONFIG_ARTICLE]

                            [BLOC_CREDIT_INST]
                            [BLOC_ARTICLE_ACHAT]
                            <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                            [/BLOC_ARTICLE_ACHAT]
                            [/BLOC_CREDIT_INST]

                            [BLOC_CAIRN_INST_ACHAT]
                            [BLOC_CREDIT_INST_OFF]
                            [BLOC_ARTICLE_ACHAT]
                            <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                                <span class="button first">Consulter</span>
                                <span class="icon icon-add-to-cart"></span>
                                <span class="button last">[ARTICLE_PRIX] €</span>
                            </a>
                            [/BLOC_ARTICLE_ACHAT]
                            [/BLOC_CREDIT_INST_OFF]
                            [/BLOC_CAIRN_INST_ACHAT]
                        </div>
                    </div>
                    <hr class="grey" />
                    [/LISTE_RESULTAT_ARTICLES_CONTRIB_OUVRAGE]
                </div>

                <?php
                    $meta = $metaNumero[$NUMERO_ID_NUMPUBLIE];
                    $firstAuthor = explode(':', explode('|', $meta['NUMERO_AUTEUR'])[0]);
                    $coins = [
                        'rft.btitle' => $NUMERO_TITRE,
                        'rft.title' => $NUMERO_TITRE,
                        'rft.isbn' => $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'],
                        'rft.aulast' => $firstAuthor[1],
                        'rft.aufirst' => $firstAuthor[0],
                        'rft.au' => $firstAuthor[1] . ' ' .$firstAuthor[0],
                        'rft.pub' => $meta['NOM_EDITEUR'],
                        'rft.date' => $NUMERO_ANNEE,
                        'rft.issn' => null,
                        'rft.genre' => 'book',
                    ];
                    echo arrayToCoins($coins, 'book');
                ?>
            </div>

            <!-- FIN DE RECHERCHE D'OUVRAGE -->

        <?php endif; ?>
    <?php endif; ?>



    <?php if ($typePub == 1) : ?>
        <?php if (!$pack || $pack == 2) : ?>
            <!-- RECHERCHE DE REVUE -->

            <div class="result article revue" id="<?= $ARTICLE_ID_ARTICLE ?>">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_meta">
                    <a href="<?= $ARTICLE_HREF ?>">
                        <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <div class="title"><a href="<?= $ARTICLE_HREF ?>"><strong><?= $ARTICLE_TITRE ?></strong></a></div>
                        <div class="authors">
                            <?= $BLOC_AUTEUR ?>
                        </div>
                        <div class="revue_title">Dans <a href="<?= $REVUE_HREF ?>" class="title_little_blue"><span class="title_little_blue"><?= $REVUE_TITRE ?></span></a> <strong><?= $NUMERO_ANNEE ?>/<?= $NUMERO_NUMERO ?> (<?= $NUMERO_VOLUME ?>)</strong></div>
                    </div>
                </div>
                <div class="contexte"><?= $CONTEXTE ?></div>
                <div class="state">
                    <!--
                        [LISTE_CONFIG_ARTICLE]
                        <a href="[ARTICLE_LIBELLE_HREF]" class="button">
                            [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last"><?= $ARTICLE_PRIX ?> €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]


                    <a href="<?= '$URL_BIBLIO' ?>" class="icon icon-add-biblio">&#160;</a>

                    [BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                    <span class="infoajout">Ajout&eacute; &agrave; <a href="./biblio.php" class="yellow"><strong>ma bibliographie</strong></a> <span class="icon icon-remove-biblio"></span></span>
                    [/BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                    -->

                </div>
                <div class="state">

                    <?php if ($cfgaArr[0] > 0) : ?>
                        <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                            <?php if ($cfgaArr[0] == 1) echo "Résumé"; else if ($cfgaArr[0] == 2) echo "Première page"; else if ($cfgaArr[0] == 3) echo "Premières lignes"; ?>
                        </a>
                    <?php endif; ?>
                    <?php if (isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 0) { ?>
                        <?php if($articlesButtons[$ARTICLE_ID_ARTICLE]['BEFORE']) {echo "<span style=\"margin-right: 5px;\">".$articlesButtons[$ARTICLE_ID_ARTICLE]['BEFORE']."</span>";} ?> <a class="<?php echo $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']; ?>" href="<?php echo $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF']; ?>" <?php if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['TARGET'])) {echo "target=\"".$articlesButtons[$ARTICLE_ID_ARTICLE]['TARGET']."\"";} ?> > <?php echo $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']; ?></a>
                    <?php } ?>
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
                                <a
                                    href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                    class="button"
                                    data-webtrends="goToPdfArticle"
                                    data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                    data-titre=<?=
                                        $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                    ?>
                                    data-authors=<?=
                                        $ParseDatas->cleanAttributeString(
                                            $ParseDatas->stringifyRawAuthors(
                                                str_replace(
                                                    '#',
                                                    $ParseDatas::concat_name,
                                                    implode($ParseDatas::concat_authors, $authors)
                                                ), 0, ';'
                                            )
                                        )
                                    ?>
                                >
                                    Version PDF
                                </a>
                            <?php endif; ?>

                            <!-- Partie webTrends, lien : "Consulter sur Revues.org" -->
                            <?php if (count($cfgaArr) > 5 && $cfgaArr[5] > 0) : ?>
                                <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button"
                                   data-webtrends="goToRevues.org"
                                   data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                   data-titre=<?= $ParseDatas->cleanAttributeString($ARTICLE_TITRE) ?>
                                   data-authors=<?=
                                        $ParseDatas->cleanAttributeString(
                                            $ParseDatas->stringifyRawAuthors(
                                                str_replace(
                                                    '#',
                                                    $ParseDatas::concat_name,
                                                    implode($ParseDatas::concat_authors, $authors)
                                                ), 0, ';'
                                            )
                                        )
                                    ?>
                                   >
                                    <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                                </a>
                                <?php
                            endif;
                        }else {
                            if (isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2) {
                                //WebTrends : "tracking sur les boutons d'ajout au panier"
                                if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                    echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                            . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                            . 'data-webtrends="goToMonPanier" '
                                            . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                            . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                            . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE)  . ' '
                                            . 'data-authors=' . $ParseDatas->cleanAttributeString(
                                            $ParseDatas->stringifyRawAuthors(
                                                str_replace(
                                                    '#',
                                                    $ParseDatas::concat_name,
                                                    implode($ParseDatas::concat_authors, $authors)
                                                ), 0, ';'
                                            )) . ' '
                                            . '>'
                                            . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']
                                            . '</a>';
                                } else {
                                    echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" ' . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '') . '>' . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'] . '</a>';
                                }
                            }
                        }
                        // Article EN
                        if($result->userFields->cairnArticleInt) {
                            // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                            $array              = $result->userFields->cairnArticleInt;
                            $id_article_int     = $array["ID_ARTICLE"];
                            $url_article_int    = $array["URL_REWRITING_EN"];

                            echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                        }
                        require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                        checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);

                        if($pack == 2){?>
                            <div class="state">
                                <a href="#" class="button" onclick="$(this).toggleClass('active');
                                        cairn_search_others_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this, '<?= $ARTICLE_ID_ARTICLE ?>');">Autres articles pertinents pour ce numéro</a>
                            </div>
                        <?php }
                        ?>

                </div>
                <?php if($pack == 2){?>
                    <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>"></div>
                <?php } ?>
            </div>

        <?php else: ?>

            <div class="result numero revue" id="<?= $NUMERO_ID_NUMPUBLIE ?>">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_contexte wrapper_meta">
                    <a href="<?= $NUMERO_HREF ?>">
                        <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <div class="revue_title">
                            <a href="<?= $REVUE_HREF ?>" class="title_little_blue"><span class="title_little_blue"><?= $REVUE_TITRE ?></span></a>
                            <strong><?= $NUMERO_ANNEE ?>/<?= $NUMERO_NUMERO ?> (<?= $NUMERO_VOLUME ?>)</strong>
                        </div>
                        <div class="numero_title">
                            <a href="<?= $NUMERO_HREF ?>">
                                <strong><?= $NUMERO_TITRE ?><?php if (trim($NUMERO_SOUS_TITRE) != '') echo ". $NUMERO_SOUS_TITRE"; ?></strong>
                            </a>
                        </div>
                        <div class="authors">

                        </div>
                    </div>
                    <div class="contexteMemo"><?= $NUMERO_MEMO ?></div>
                </div>

                <div class="state">
                    <a href="<?= $NUMERO_HREF ?>" class="button">Présentation/Sommaire</a>
                    <a href="#" class="button" onclick="$(this).toggleClass('active');
                                        cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                    <?php
                    // Article EN
                    if($result->userFields->cairnArticleInt) {
                        // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                        $array              = $result->userFields->cairnArticleInt;
                        $id_article_int     = $array["ID_ARTICLE"];
                        $url_article_int    = $array["URL_REWRITING_EN"];

                        echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                    }
                    require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                    ?>
                </div>
                <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>">

                    <div class="meta">
                        <div>
                            <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                            <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                            <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                            [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                        </div>
                        <div class="contexte">[CONTEXTE]</div>
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]
                    </div>
                </div>
                <hr class="grey" />

            </div>
                <?php
                    $meta = $metaNumero[$NUMERO_ID_NUMPUBLIE];
                    $firstAuthor = explode(':', explode('|', $meta['NUMERO_AUTEUR'])[0]);
                    $coins = [
                        'rft.btitle' => $NUMERO_TITRE,
                        'rft.title' => $NUMERO_TITRE,
                        'rft.isbn' => $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'],
                        'rft.aulast' => isset($firstAuthor[1]) ? $firstAuthor[1] : '',
                        'rft.aufirst' => $firstAuthor[0],
                        'rft.au' => (isset($firstAuthor[1]) ? $firstAuthor[1] : '') . ' ' .$firstAuthor[0],
                        'rft.pub' => $meta['NOM_EDITEUR'],
                        'rft.date' => $NUMERO_ANNEE,
                        'rft.issn' => null,
                        'rft.genre' => 'book',
                    ];
                    echo arrayToCoins($coins, 'book');
                ?>
            </div>

            <!-- FIN DE RECHERCHE DE REVUE -->

        <?php endif; ?>
    <?php endif; ?>

    <?php if ($typePub == 2) : ?>
        <?php if (!$pack || $pack == 2) : ?>
            <!-- RECHERCHE DE MAGAZINE -->

            <div class="result article magazine" id="<?= $ARTICLE_ID_ARTICLE ?>">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_meta">
                    <a href="<?= $ARTICLE_HREF ?>">
                        <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <a href="<?= $ARTICLE_HREF ?>">
                            <div class="title"><strong><?= $ARTICLE_TITRE ?></strong></div>
                        </a>
                        <div class="authors">
                            <?= $BLOC_AUTEUR ?>
                        </div>
                        <div class="revue_title">Dans <a href="<?= $REVUE_HREF ?>" class="title_little_blue"><span class="title_little_blue"><?= $REVUE_TITRE ?></span></a> <strong><?= $NUMERO_ANNEE ?>/<?= $NUMERO_NUMERO ?> (<?= $NUMERO_VOLUME ?>)</strong></div>
                    </div>
                </div>
                <div class="contexte"><?= $CONTEXTE ?></div>
                <!--
                <div class="state">
                    [LISTE_CONFIG_ARTICLE]
                    <a href="[ARTICLE_LIBELLE_HREF]" class="button">
                        [ARTICLE_LIBELLE_LIBELLE]
                    </a>
                    [/LISTE_CONFIG_ARTICLE]

                    [BLOC_CREDIT_INST]
                    [BLOC_ARTICLE_ACHAT]
                    <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                    [/BLOC_ARTICLE_ACHAT]
                    [/BLOC_CREDIT_INST]

                    [BLOC_CAIRN_INST_ACHAT]
                    [BLOC_CREDIT_INST_OFF]
                    [BLOC_ARTICLE_ACHAT]
                    <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                        <span class="button first">Consulter</span>
                        <span class="icon icon-add-to-cart"></span>
                        <span class="button last">[ARTICLE_PRIX] €</span>
                    </a>
                    [/BLOC_ARTICLE_ACHAT]
                    [/BLOC_CREDIT_INST_OFF]
                    [/BLOC_CAIRN_INST_ACHAT]

                    [BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                    <a href="[URL]&amp;AJOUTBIBLIO=[ARTICLE_ID_ARTICLE]#[ARTICLE_ID_ARTICLE]" class="icon icon-add-biblio">&#160;</a>
                    [/BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                    [BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                    <span class="infoajout">Ajout&eacute; &agrave; <a href="./biblio.php" class="yellow"><strong>ma bibliographie</strong></a> <span class="icon icon-remove-biblio"></span></span>
                    [/BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                </div>
                -->
                <div class="state">

                    <?php if ($cfgaArr[0] > 0) : ?>
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
                                <a
                                    href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                    class="button"
                                    data-webtrends="goToPdfArticle"
                                    data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                    data-titre=<?=
                                        $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                    ?>
                                    data-authors=<?=
                                        $ParseDatas->cleanAttributeString(
                                            $ParseDatas->stringifyRawAuthors(
                                                str_replace(
                                                    '#',
                                                    $ParseDatas::concat_name,
                                                    implode($ParseDatas::concat_authors, $authors)
                                                ), 0, ';'
                                            )
                                        )
                                    ?>
                                >
                                    Version PDF
                                </a>
                            <?php endif; ?>

                            <?php if ($cfgaArr[5] > 0) : ?>
                                <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                                    <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                                </a>
                                <?php
                            endif;
                        }else {
                            if (isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2) {
                                //WebTrends : "tracking sur les boutons d'ajout au panier"
                                if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                    echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                            . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                            . 'data-webtrends="goToMonPanier" '
                                            . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                            . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                            . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE)  . ' '
                                            . 'data-authors=' . $ParseDatas->cleanAttributeString(
                                            $ParseDatas->stringifyRawAuthors(
                                                str_replace(
                                                    '#',
                                                    $ParseDatas::concat_name,
                                                    implode($ParseDatas::concat_authors, $authors)
                                                ), 0, ';'
                                            )) . ' '
                                            . '>'
                                            . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']
                                            . '</a>';
                                } else {
                                    echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" ' . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '') . '>' . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'] . '</a>';
                                }
                            }
                        }
                        // Article EN
                        if($result->userFields->cairnArticleInt) {
                            // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                            $array              = $result->userFields->cairnArticleInt;
                            $id_article_int     = $array["ID_ARTICLE"];
                            $url_article_int    = $array["URL_REWRITING_EN"];

                            echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                        }
                        require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                        checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                        if($pack == 2){?>
                            <div class="state">
                                <a href="#" class="button" onclick="$(this).toggleClass('active');
                                        cairn_search_others_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this, '<?= $ARTICLE_ID_ARTICLE ?>');">Autres articles pertinents pour ce numéro</a>
                            </div>
                        <?php }
                        ?>

                </div>
                <?php if($pack == 2){?>
                    <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>"></div>
                <?php } ?>

                <?php
                    $meta = $metaNumero[$NUMERO_ID_NUMPUBLIE];
                    $firstAuthor = explode(':', explode('|', $meta['NUMERO_AUTEUR'])[0]);
                    $coins = [
                        'rft.atitle' => $ARTICLE_TITRE,
                        'rft.jtitle' => $NUMERO_TITRE,
                        'rft.title' => $NUMERO_TITRE,
                        'rft.volume' => preg_replace('/\s*n°\s*/', '', $NUMERO_VOLUME),
                        'rft.issue' => $NUMERO_ANNEE.'/'.$NUMERO_NUMERO,
                        'rft.isbn' => $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'],
                        'rft.aulast' => isset($firstAuthor[1]) ? $firstAuthor[1] : '',
                        'rft.aufirst' => $firstAuthor[0],
                        'rft.au' => (isset($firstAuthor[1]) ? $firstAuthor[1] : '') . ' ' .$firstAuthor[0],
                        'rft.pub' => $meta['NOM_EDITEUR'],
                        'rft.date' => $NUMERO_ANNEE,
                        'rft.issn' => null,
                        'rft.eissn' => null,
                        'rft.genre' => 'article',
                    ];
                    echo arrayToCoins($coins);
                ?>
            </div>

        <?php else: ?>

            <div class="result numero magazine" id="<?= $NUMERO_ID_NUMPUBLIE ?>">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_contexte wrapper_meta">
                    <a href="<?= $NUMERO_HREF ?>">
                        <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <div class="revue_title">
                            <a href="<?= $REVUE_HREF ?>" class="title_little_blue">
                                <span class="title_little_blue"><?= $REVUE_TITRE ?></span>
                            </a>
                            <strong><?= $NUMERO_ANNEE ?>/<?= $NUMERO_NUMERO ?> (<?= $NUMERO_VOLUME ?>)</strong>
                        </div>
                        <div class="numero_title">
                            <a href="<?= $NUMERO_HREF ?>">
                                <strong><?= $NUMERO_TITRE ?><?php if (trim($NUMERO_SOUS_TITRE) != '') echo ". $NUMERO_SOUS_TITRE"; ?></strong>
                            </a>
                        </div>
                        <div class="authors">

                        </div>
                    </div>
                    <div class="contexteMemo"><?= $NUMERO_MEMO ?></div>
                </div>
                <div class="state">
                    <a href="<?= $NUMERO_HREF ?>"  class="button">Présentation/Sommaire</a>
                    <a href="#" class="button" onclick="$(this).toggleClass('active');
                                        cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                    <?php
                    // Article EN
                    if($result->userFields->cairnArticleInt) {
                        // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                        $array              = $result->userFields->cairnArticleInt;
                        $id_article_int     = $array["ID_ARTICLE"];
                        $url_article_int    = $array["URL_REWRITING_EN"];

                        echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                    }
                    require_once(__DIR__ . '/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                    ?>
                </div>
                <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>">
                    [LISTE_RESULTAT_ARTICLES_MAGAZINE]
                    <div class="meta">
                        <div>
                            <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                            <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                            <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                            [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                        </div>
                        <div class="contexte">[CONTEXTE]</div>
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]
                    </div>
                </div>
                <hr class="grey" />
                [/LISTE_RESULTAT_ARTICLES_MAGAZINE]
            </div>

                <?php
                    $meta = $metaNumero[$NUMERO_ID_NUMPUBLIE];
                    $firstAuthor = explode(':', explode('|', $meta['NUMERO_AUTEUR'])[0]);
                    $coins = [
                        'rft.btitle' => $NUMERO_TITRE,
                        'rft.title' => $NUMERO_TITRE,
                        'rft.isbn' => $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'],
                        'rft.aulast' => $firstAuthor[1],
                        'rft.aufirst' => $firstAuthor[0],
                        'rft.au' => $firstAuthor[1] . ' ' .$firstAuthor[0],
                        'rft.pub' => $meta['NOM_EDITEUR'],
                        'rft.date' => $NUMERO_ANNEE,
                        'rft.issn' => null,
                        'rft.genre' => 'book',
                    ];
                    echo arrayToCoins($coins, 'book');
                ?>
            </div>

            <!-- FIN DE RECHERCHE DE MAGAZINE -->

        <?php endif; ?>
    <?php endif; ?>

    <?php if ($typePub == 4) : ?>
        <?php if (!$pack) : ?>
            <!-- RECHERCHE DE ETAT DU MONDE -->
            [BLOC_TYPEPUB_EDM]
            <div class="result article magazine" id="[ARTICLE_ID_ARTICLE]">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_meta">
                    <a href="[ARTICLE_HREF]">
                        <img src="./vign_rev/[ARTICLE_ID_REVUE]/[ARTICLE_ID_NUMPUBLIE]_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <a href="[ARTICLE_HREF]">
                            <div class="title"><strong>[ARTICLE_TITRE]</strong></div>
                        </a>
                        <div class="authors">
                            [BLOC_AUTEURS]
                            <span class="author">
                                [AUTEUR_PRENOM] [AUTEUR_NOM]
                                [BLOC_PLUSDEDEUX] <em>et al.</em> [/BLOC_PLUSDEDEUX]
                                [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX]
                            </span>
                            [/BLOC_AUTEURS]
                        </div>
                        <div class="revue_title">Dans
                            <a href="[REVUE_HREF]" class="title_little_blue"><span class="title_little_blue">[REVUE_TITRE]</span></a>
                            <strong>([EDITEUR_NOM_EDITEUR], [BLOC_NUMERO_VOLUME][NUMERO_VOLUME] [/BLOC_NUMERO_VOLUME][NUMERO_ANNEE])</strong>
                        </div>
                    </div>
                </div>
                <div class="contexte">[CONTEXTE]</div>
                <div class="state">
                    [LISTE_CONFIG_ARTICLE]
                    <a href="[ARTICLE_LIBELLE_HREF]" class="button">
                        [ARTICLE_LIBELLE_LIBELLE]
                    </a>
                    [/LISTE_CONFIG_ARTICLE]

                    [BLOC_CREDIT_INST]
                    [BLOC_ARTICLE_ACHAT]
                    <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                    [/BLOC_ARTICLE_ACHAT]
                    [/BLOC_CREDIT_INST]

                    [BLOC_CAIRN_INST_ACHAT]
                    [BLOC_CREDIT_INST_OFF]
                    [BLOC_ARTICLE_ACHAT]
                    <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                        <span class="button first">Consulter</span>
                        <span class="icon icon-add-to-cart"></span>
                        <span class="button last">[ARTICLE_PRIX] €</span>
                    </a>
                    [/BLOC_ARTICLE_ACHAT]
                    [/BLOC_CREDIT_INST_OFF]
                    [/BLOC_CAIRN_INST_ACHAT]

                    [BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                    <a href="[URL]&amp;AJOUTBIBLIO=[ARTICLE_ID_ARTICLE]#[ARTICLE_ID_ARTICLE]" class="icon icon-add-biblio">&#160;</a>
                    [/BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                    [BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                    <span class="infoajout">Ajout&eacute; &agrave; <a href="./biblio.php" class="yellow"><strong>ma bibliographie</strong></a> <span class="icon icon-remove-biblio"></span></span>
                    [/BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                </div>

                <?php
                    $meta = $metaNumero[$NUMERO_ID_NUMPUBLIE];
                    $firstAuthor = explode(':', explode('|', $meta['NUMERO_AUTEUR'])[0]);
                    $coins = [
                        'rft.atitle' => $ARTICLE_TITRE,
                        'rft.jtitle' => $NUMERO_TITRE,
                        'rft.title' => $NUMERO_TITRE,
                        'rft.volume' => preg_replace('/\s*n°\s*/', '', $NUMERO_VOLUME),
                        'rft.issue' => $NUMERO_ANNEE.'/'.$NUMERO_NUMERO,
                        'rft.isbn' => $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'],
                        'rft.aulast' => $firstAuthor[1],
                        'rft.aufirst' => $firstAuthor[0],
                        'rft.au' => $firstAuthor[1] . ' ' .$firstAuthor[0],
                        'rft.pub' => $meta['NOM_EDITEUR'],
                        'rft.date' => $NUMERO_ANNEE,
                        'rft.issn' => null,
                        'rft.eissn' => null,
                        'rft.genre' => 'article',
                    ];
                    echo arrayToCoins($coins);
                ?>
            </div>
            [/BLOC_TYPEPUB_EDM]
        <?php else: ?>
            [BLOC_TYPEPUB_EDM_NUM]
            <div class="result numero magazine" id="[NUMERO_ID_NUMPUBLIE]">
                <h2>Dossier de l'État du monde</h2>
                <div class="wrapper_meta">
                    <a href="resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=[NUMERO_HREF]&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]">
                        <img src="./vign_rev/[NUMERO_ID_REVUE]/[NUMERO_ID_NUMPUBLIE]_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <div class="numero_title">
                            <a href="[NUMERO_HREF]">
                                <strong>[NUMERO_TITRE][BLOC_NUMERO_SOUS_TITRE]. [NUMERO_SOUS_TITRE][/BLOC_NUMERO_SOUS_TITRE]</strong>
                            </a>
                        </div>
                        <div class="revue_title">dans
                            <a href="[REVUE_HREF]" class="title_little_blue">
                                <span class="title_little_blue">[REVUE_TITRE]</span>
                            </a>
                            <strong>([EDITEUR_NOM_EDITEUR], [BLOC_NUMERO_VOLUME][NUMERO_VOLUME] [/BLOC_NUMERO_VOLUME][NUMERO_ANNEE])</strong>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS]
                            <span class="author">
                                [AUTEUR_PRENOM] [AUTEUR_NOM]
                                [BLOC_PLUSDEDEUX] <em>et al.</em> [/BLOC_PLUSDEDEUX]
                                [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX]
                            </span>
                            [/BLOC_AUTEURS]
                        </div>
                    </div>
                </div>
                <div class="wrapper_contexte">
                    <div class="contexteMemo">[NUMERO_MEMO]</div>
                </div>
                <div class="state">
                    <a href="resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=[NUMERO_HREF]&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]"  class="button">Présentation/Sommaire</a>
                    [BLOC_NUMERO_BIBLIO_AJOUT_ON]
                    <a href="[URL]&amp;AJOUTBIBLIO=[NUMERO_ID_NUMPUBLIE]" class="icon icon-add-biblio">&#160;</a>
                    [/BLOC_NUMERO_BIBLIO_AJOUT_ON]
                    <a href="#" class="button" onclick="cairn_search.deploy_pertinent_articles('resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=LISTE_RESULTAT_ARTICLES_EDM&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]', '#__pertinent_[NUMERO_ID_NUMPUBLIE]', this);">Chapitres les plus pertinents</a>
                </div>
                <div class="pertinent_articles" id="__pertinent_[NUMERO_ID_NUMPUBLIE]">
                    [LISTE_RESULTAT_ARTICLES_EDM]
                    <div class="meta">
                        <div>
                            <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                            <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                            <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                            [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                        </div>
                        <div class="contexte">[CONTEXTE]</div>
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]
                    </div>
                </div>
                <hr class="grey" />
                [/LISTE_RESULTAT_ARTICLES_EDM]
            </div>
            <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=[ARTICLE_TITRE]&amp;rft.title=[REVUE_TITRE]&amp;rft.issn=[REVUE_ISSN]&amp;rft.date=[NUMERO_ANNEE]&amp;rft.volume=[NUMERO_VOLUME]&amp;rft.issue=[NUMERO_NUMERO]&amp;rft.spage=[ARTICLE_PAGE_DEBUT]&amp;rft.epage=[ARTICLE_PAGE_FIN]&amp;rft.au=[BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_AUTEURS]&amp;rft_id=info:doi/[ARTICLE_DOI]&amp;rft_id=[ARTICLE_HREF]"></span>-->
            </div>
            [/BLOC_TYPEPUB_EDM_NUM]
            <!-- FIN DE RECHERCHE DE ETAT DU MONDE -->
        <?php endif; ?>
    <?php endif; ?>

<?php endforeach; ?>
</div>




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
