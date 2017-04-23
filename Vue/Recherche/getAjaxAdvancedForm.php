<div id="advanced_search">
    <script>
        $(function() {
            search_adv = {};
            var $wrapper_subform = $('#wrapper_subform');
            var $subforms = $wrapper_subform.find('.subform');
            var $more_subform = $wrapper_subform.find('#more_subform');

            search_adv.remove_subform = function($subform) {
                $subform.hide();
                $subform.find('input').val('');
                $more_subform.appendTo($subforms.filter(':visible').last());
                search_adv.move_more_subform();
            }

            search_adv.append_subform = function() {
                if ($subforms.filter(':visible').length >= $subforms.length) {
                    return false;
                }
                $subforms.filter(':hidden').first().show(0, search_adv.move_more_subform);
            }

            search_adv.move_more_subform = function() {
                var $subforms_visible = $subforms.filter(':visible');
                if ($subforms_visible.length < $subforms.length) {
                    $more_subform.appendTo($subforms_visible.last());
                    $more_subform.show();
                } else {
                    $more_subform.hide();
                }
            }

            search_adv.update_filters = function(uid) {
                var $wrapper = wrappers[uid];
                var $checkbox = checkboxs[uid];

                if (uid == 'revue' || uid == 'mag') {
                    if (checkboxs.mag.is(':checked') && checkboxs.revue.is(':checked')) {
                        wrappers.mag.hide();
                        wrappers.revue.hide();
                        wrappers.revmag.show();
                        return true;
                    }
                    else {
                        wrappers.revmag.hide();
                        checkboxs.revue.is(':checked') ? wrappers.revue.show() : wrappers.revue.hide();
                        checkboxs.mag.is(':checked') ? wrappers.mag.show() : wrappers.mag.hide();
                        return true;
                    }
                }

                $checkbox.is(':checked') ? $wrapper.show() : $wrapper.hide();
            }

            var uid;
            var wrappers = {};
            var checkboxs = {};
            $.each(['revue', 'ouvrage', 'mag'], function(index, uid) {
                var $wrapper = $('#wrapper_' + uid);
                var $checkbox = $('#typepub_' + uid);
                wrappers[uid] = $wrapper;
                checkboxs[uid] = $checkbox;
                $checkbox.on('change', function() {
                    search_adv.update_filters(uid);
                });
            });
            wrappers.revmag = $('#wrapper_revmag');

            //var params = cairn.parse_GET();
            var params = advanceJson;
            console.log("start params");
            console.log(params);
            console.log("stop params");
            console.log(advanceJson);
            /*
            $.each(['chk_revue', 'chk_ouvref', 'chk_mag', 'chk_edm', 'chk_ouvcol'], function(index, key) {
                if (!params[key])
                    params[key] = 'off';
            });
            */
            cairn.autofill_form_from_GET({
                'root': $("#wrapper_subform"),
                'params':params
            });
            if(($('#word1').val()=='*') &&  !($('#searchTerm').val()=='') && ($('#dans1').val()=='') )
            {
            $('#word1').val($('#searchTerm').val());

            }
            cairn.autofill_form_from_GET({
                'root': $('#wrapper_filter'),
                  'params':params
            })
            // On affiche les subforms qui contiennent des valeurs. La première est affiché quoi qu'il arrive.
            $subforms.hide();
            $subforms.each(function() {
                var $this = $(this);
                !!($this.find('input[type="text"]').val()) ? $this.show() : $this.hide();
            });
            $subforms.first().show();
            $subforms.first().next().show();

            // Si aucune case de filtre de type n'est coché, on affiche toutes les cases cochés par défaut
            var $inputs_typepub = $('#wrapper_typepub .filter_content:first').find('input');
            if (!$inputs_typepub.filter(':checked').length)
                $inputs_typepub.each(function() {
                    var $this = $(this);
                    $this.prop('checked', true);
                    $this.change();
                });
            // Le filtre logique par défaut est le premier.
            var $inputs_logical = $('#wrapper_subform .logical_filter');
            $inputs_logical.each(function(index, input) {
                var $this = $(this);
                var $inputs = $this.find('input[type="radio"]');
                if (!$inputs.filter(':checked').length)
                    $inputs.first().prop('checked', true);
            });
            // On met à jour l'ajout d'un nouveau subform (si besoin)
            search_adv.move_more_subform();
        })
    </script>
    <style>
        #form_advanced_search hr.grey {
            margin : 1.7em 0;
        }
        #form_advanced_search #wrapper_subform, #form_advanced_search #wrapper_filter, #form_advanced_search #wrapper_submit {
            width : 80%;
            margin: auto;
        }
        #form_advanced_search .unicon {
            font-size : 1.08em;
        }
        #form_advanced_search #wrapper_subform a {
            cursor: pointer;
        }
        #form_advanced_search #wrapper_subform a + a {
            margin-left : 0.2em;
        }
        #form_advanced_search input[type="checkbox"] { margin-left:0;}
        #form_advanced_search input[type="text"], select {
            font: normal 16px Georgia;
            padding : 0.5em 0.9em;
            color : #626152;
            border : 1px solid #cccccc;
            border-radius: 3px;
            box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.05);
        }
        #form_advanced_search #wrapper_subform input[type="text"] {
            width : 40%;
            margin-right : 0.7em;
        }
        #form_advanced_search #wrapper_subform select {
            width : 33%;
            margin-left : 0.7em;
            margin-right : 1em;
        }
        #form_advanced_search .subform, #form_advanced_search .logical_filter {
            margin : 0.6em 0;
        }
        #form_advanced_search .logical_filter {
            margin-left : 1em;
        }
        #form_advanced_search .logical_filter label {
            margin-right : 1em;
        }
        #form_advanced_search .filter_title {
            font : bold 1em "Alegreya SC";
        }


        #form_advanced_search #wrapper_filter > div + div {
            margin-top : 1.75em;
        }
        #form_advanced_search #wrapper_filter .filter_title {
            width : 26%;
            display : inline-block;
        }
        #form_advanced_search #wrapper_filter select {
            width : 70%;
        }
        #form_advanced_search #wrapper_years .filter_title,
        #form_advanced_search #wrapper_typepub .filter_title,
        #form_advanced_search #wrapper_years .filter_content,
        #form_advanced_search #wrapper_typepub .filter_content {
            display : inline-block;
            vertical-align: top;
            line-height: 2;
        }
        #form_advanced_search #wrapper_years .filter_title,
        #form_advanced_search #wrapper_typepub .filter_title {
            width : 26%;
        }
        #form_advanced_search #wrapper_years .filter_content,
        #form_advanced_search #wrapper_typepub .filter_content {
            width : 73%;
        }
        #form_advanced_search #wrapper_years input { width : 5em; }
        #form_advanced_search #wrapper_years .filter_content label,
        #form_advanced_search #wrapper_years input
        {
            margin-right : 0.7em;
        }
        #form_advanced_search #wrapper_typepub .filter_content label {
            min-width : 28%;
            display : inline-block;
        }
        .wrapper_submit { overflow:hidden;}
        .wrapper_submit input {
            font : bold 17px "Alegreya SC";
            margin-right : 1.6em;
        }
    </style>
    <h1 class="main-title" style="position:relative">
        Recherche avancée
    </h1>
    <form id="form_advanced_search" method="GET" action="resultats_recherche.php" class="white_standard" name="rechercheavancee">
        <input type="hidden" name="soumettre" value="Lancer la recherche">
        <div id="wrapper_subform">
            <div id="subform1" class="subform active" style="display: block;">
                <input type="text" name="larech1" placeholder="Mot-clé" id="word1" value="*">
                <label for="">Dans</label>
                <select name="dans1" id="dans1">
                    <option value=""></option>
                    <option value="Tx">Texte intégral</option>
                    <option value="T">Titre de l'article ou du chapitre</option>
                    <option value="To">Titre de l'ouvrage ou du numéro</option>
                    <option value="Tr">Titre de la revue ou de la collection</option>
                    <option value="A">Auteur</option>
                    <option value="R">Résumé</option>
                    <option value="B">Bibliographie</option>
                </select>

                <a id="more_subform" onclick="search_adv.append_subform();">
                        <!-- <span class="unicon rounded blue">&#10133;</span> -->
                    <span class="icon icon-more"></span>
                </a></div>
            <div id="subform2" class="subform" style="display: block;">
                <div class="logical_filter">
                    <input type="radio" name="etou2" value="AND" id="and2" checked="">
                    <label for="and2">et</label>
                    <input type="radio" name="etou2" value="OR" id="or2">
                    <label for="or2">ou</label>
                    <input type="radio" name="etou2" value="BUT" id="but2">
                    <label for="but2">sauf</label>
                </div>
                <input type="text" name="larech2" placeholder="Mot-clé" id="word2">
                <label for="">Dans</label>
                <select name="dans2">
                    <option value=""></option>
                    <option value="Tx">Texte intégral</option>
                    <option value="T">Titre de l'article ou du chapitre</option>
                    <option value="To">Titre de l'ouvrage ou du numéro</option>
                    <option value="Tr">Titre de la revue ou de la collection</option>
                    <option value="A">Auteur</option>
                    <option value="R">Résumé</option>
                    <option value="B">Bibliographie</option>
                </select>
                <a id="remove_subform2" onclick="search_adv.remove_subform($('#subform2'));">
                    <!-- <span class="unicon rounded greyC">&#x274C;</span> -->
                    <span class="icon icon-remove"></span>
                </a>
            </div>
            <div id="subform3" class="subform" style="display: none;">
                <div class="logical_filter">
                    <input type="radio" name="etou3" value="AND" id="and3" checked="">
                    <label for="and3">et</label>
                    <input type="radio" name="etou3" value="OR" id="or3">
                    <label for="or3">ou</label>
                    <input type="radio" name="etou3" value="BUT" id="but3">
                    <label for="but3">sauf</label>
                </div>
                <input type="text" name="larech3" placeholder="Mot-clé" id="word3">
                <label for="">Dans</label>
                <select name="dans3">
                    <option value=""></option>
                    <option value="Tx">Texte intégral</option>
                    <option value="T">Titre de l'article ou du chapitre</option>
                    <option value="To">Titre de l'ouvrage ou du numéro</option>
                    <option value="Tr">Titre de la revue ou de la collection</option>
                    <option value="A">Auteur</option>
                    <option value="R">Résumé</option>
                    <option value="B">Bibliographie</option>
                </select>
                <a id="remove_subform3" onclick="search_adv.remove_subform($('#subform3'));">
                    <span class="icon icon-remove"></span>
                </a>
            </div>
            <div id="subform4" class="subform" style="display: none;">
                <div class="logical_filter">
                    <input type="radio" name="etou4" value="AND" id="and4" checked="">
                    <label for="and4">et</label>
                    <input type="radio" name="etou4" value="OR" id="or4">
                    <label for="or4">ou</label>
                    <input type="radio" name="etou4" value="BUT" id="but4">
                    <label for="but4">sauf</label>
                </div>
                <input type="text" name="larech4" placeholder="Mot-clé" id="word4">
                <label for="">Dans</label>
                <select name="dans4">
                    <option value=""></option>
                    <option value="Tx">Texte intégral</option>
                    <option value="T">Titre de l'article ou du chapitre</option>
                    <option value="To">Titre de l'ouvrage ou du numéro</option>
                    <option value="Tr">Titre de la revue ou de la collection</option>
                    <option value="A">Auteur</option>
                    <option value="R">Résumé</option>
                    <option value="B">Bibliographie</option>
                </select>
                <a id="remove_subform4" onclick="search_adv.remove_subform($('#subform4'));">
                    <span class="icon icon-remove"></span>
                </a>
            </div>
            <div id="subform5" class="subform" style="display: none;">
                <div class="logical_filter">
                    <input type="radio" name="etou5" value="AND" id="and5" checked="">
                    <label for="and5">et</label>
                    <input type="radio" name="etou5" value="OR" id="or5">
                    <label for="or5">ou</label>
                    <input type="radio" name="etou5" value="BUT" id="but5">
                    <label for="but5">sauf</label>
                </div>
                <input type="text" name="larech5" placeholder="Mot-clé" id="word5">
                <label for="">Dans</label>
                <select name="dans5">
                    <option value=""></option>
                    <option value="Tx">Texte intégral</option>
                    <option value="T">Titre de l'article ou du chapitre</option>
                    <option value="To">Titre de l'ouvrage ou du numéro</option>
                    <option value="Tr">Titre de la revue ou de la collection</option>
                    <option value="A">Auteur</option>
                    <option value="R">Résumé</option>
                    <option value="B">Bibliographie</option>
                </select>
                <a id="remove_subform5" onclick="search_adv.remove_subform($('#subform5'));">
                    <span class="icon icon-remove"></span>
                </a>
            </div>
            <div id="subform6" class="subform" style="display: none;">
                <div class="logical_filter">
                    <input type="radio" name="etou6" value="AND" id="and6" checked="">
                    <label for="and6">et</label>
                    <input type="radio" name="etou6" value="OR" id="or6">
                    <label for="or6">ou</label>
                    <input type="radio" name="etou6" value="BUT" id="but6">
                    <label for="but6">sauf</label>
                </div>
                <input type="text" name="larech6" placeholder="Mot-clé" id="word6">
                <label for="">Dans</label>
                <select name="dans6">
                    <option value=""></option>
                    <option value="Tx">Texte intégral</option>
                    <option value="T">Titre de l'article ou du chapitre</option>
                    <option value="To">Titre de l'ouvrage ou du numéro</option>
                    <option value="Tr">Titre de la revue ou de la collection</option>
                    <option value="A">Auteur</option>
                    <option value="R">Résumé</option>
                    <option value="B">Bibliographie</option>
                </select>
                <a id="remove_subform6" onclick="search_adv.remove_subform($('#subform6'));">
                    <span class="icon icon-remove"></span>
                </a>
            </div>
        </div>

        <hr class="grey">
        <div class="wrapper_submit">
            <input type="submit" value="Rechercher" class="blue_button right">
        </div>
        <div id="wrapper_filter">

            <div id="wrapper_years">
                <h2 class="filter_title">Années de publication</h2>
                <div class="filter_content">
                    <label for="begin_year">De</label>
                    <input type="text" name="annee1" value="" id="begin_year">
                    <label for="end_year">à</label>
                    <input type="text" name="annee2" value="" id="end_year">
                </div>
            </div>

            <div id="wrapper_typepub">
                <h2 class="filter_title">Type de publication</h2>
                <div class="filter_content">
                    <?php
                    $blacklist = array();
                    if(isset($authInfos['I']['PARAM_INST']['Y'])){
                        $blacklist = explode(',',$authInfos['I']['PARAM_INST']['Y']);
                    }
                    if(!in_array("1", $blacklist)){ ?>
                    <input name="chk_revue" id="typepub_revue" checked="" type="checkbox">
                    <label for="typepub_revue">Revues</label>
                    <?php }
                    if(!in_array("3", $blacklist)){ ?>
                    <input name="chk_ouvcol" id="typepub_ouvrage" checked="" type="checkbox">
                    <label for="typepub_ouvrage">Ouvrages</label>
                    <?php }
                    if(!in_array("2", $blacklist)){ ?>
                    <input name="chk_mag" id="typepub_mag" checked="" type="checkbox">
                    <label for="typepub_mag">Magazines</label><br>
                    <!--<input name="chk_edm" id="typepub_edm" checked="" type="checkbox">
                    <label for="typepub_edm">L'État du monde</label>-->
                    <?php }
                    if(!in_array("6", $blacklist)){ ?>
                    <input name="chk_ouvref" id="typepub_encyclo" checked="" type="checkbox">
                    <label for="typepub_encyclo">Que sais-je ? / Repères</label>
                    <?php } ?>
                </div>
            </div>

            <div id="wrapper_disc">
                <label for="disc" class="filter_title">Discipline</label>
                <select name="discipline" id="disc">
                    <option value=""></option>
                    <?php
                    $blacklist = array();
                    if(isset($authInfos['I']['PARAM_INST']['S'])){
                        $blacklist = explode(',',$authInfos['I']['PARAM_INST']['S']);
                    }
                    if(!in_array("70", $blacklist)){ ?>
                    <option value="70">Arts</option>
                    <?php }
                    if(!in_array("2", $blacklist)){ ?>
                    <option value="2">Droit</option>
                    <?php }
                    if(!in_array("1", $blacklist)){ ?>
                    <option value="1">Economie, Gestion</option>
                    <?php }
                    if(!in_array("30", $blacklist)){ ?>
                    <option value="30">Géographie</option>
                    <?php }
                    if(!in_array("3", $blacklist)){ ?>
                    <option value="3">Histoire</option>
                    <?php }
                    if(!in_array("9", $blacklist)){ ?>
                    <option value="9">Info. - Com.</option>
                    <?php }
                    if(!in_array("4", $blacklist)){ ?>
                    <option value="4">Intérêt général</option>
                    <?php }
                    if(!in_array("5", $blacklist)){ ?>
                    <option value="5">Lettres et linguistique</option>
                    <?php }
                    if(!in_array("6", $blacklist)){ ?>
                    <option value="6">Philosophie</option>
                    <?php }
                    if(!in_array("7", $blacklist)){ ?>
                    <option value="7">Psychologie</option>
                    <?php }
                    if(!in_array("8", $blacklist)){ ?>
                    <option value="8">Sciences&nbsp;de&nbsp;l’éducation</option>
                    <?php }
                    if(!in_array("10", $blacklist)){ ?>
                    <option value="10">Sciences&nbsp;politiques</option>
                    <?php }
                    if(!in_array("11", $blacklist)){ ?>
                    <option value="11">Sociologie et société</option>
                    <?php }
                    if(!in_array("12", $blacklist)){ ?>
                    <option value="12">Sport&nbsp;et&nbsp;société</option>
                    <?php } ?>
                </select>
            </div>

            <div id="wrapper_editor">
                <label for="editor" class="filter_title">Éditeur</label>
                <select name="editeur" id="editor">
                    <option value=""></option>
                    <?php
                        foreach($editeurs as $editeur){
                            echo '<option value="'.$editeur['EDITEUR_ID_EDITEUR'].'">'.$editeur['EDITEUR_NOM_EDITEUR'].'</option>';
                        }
                    ?>
                </select>
            </div>


            <div style="display: none;" id="wrapper_revue">
                <label for="revue" class="filter_title">Revue</label>
                <select name="revue" id="revue">
                    <option value=""></option>
                    <?php
                        foreach($revs as $rev){
                            echo '<option value="'.$rev['ID_REVUE'].'">'.$rev['TITRE'].'</option>';
                        }
                    ?>
                </select>
            </div>



            <div style="display: none;" id="wrapper_mag">
                <label for="magazine" class="filter_title">Magazine</label>
                <select name="mag" id="magazine">
                    <option value=""></option>
                    <?php
                        foreach($mags as $mag){
                            echo '<option value="'.$mag['ID_REVUE'].'">'.$mag['TITRE'].'</option>';
                        }
                    ?>

                </select>
            </div>



            <div style="display: block;" id="wrapper_revmag">
                <label for="revue_or_magazine" class="filter_title">Revue ou magazine</label>
                <select name="revmag" id="revue_or_magazine">
                    <option value=""></option>
                    <?php
                        foreach($revMags as $revMag){
                            echo '<option value="'.$revMag['ID_REVUE'].'">'.$revMag['TITRE'].'</option>';
                        }
                    ?>
                </select>
            </div>



            <div style="display: block;" id="wrapper_ouvrage">
                <label for="ouvrage" class="filter_title">Collection</label>
                <select name="recol" id="ouvrage">
                    <option value=""></option>
                    <?php
                        foreach($colls as $coll){
                            echo '<option value="'.$coll['ID_REVUE'].'">'.$coll['TITRE'].($coll['TITRE']=='Hors collection'?(' ('.$coll['NOM_EDITEUR'].')'):'').'</option>';
                        }
                    ?>

                </select>
            </div>

        </div>

        <hr class="grey">
        <div class="wrapper_submit">
            <input type="submit" value="Rechercher" class="blue_button right">
        </div>
    </form>
</div>
