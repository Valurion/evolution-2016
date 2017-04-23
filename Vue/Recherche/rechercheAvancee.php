<?php
// On doit forcer le refresh de cette page pour pouvoir prendre en compte le cookie lors d'un retour en arrière
// @http://stackoverflow.com/a/24942000
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");   // any valid date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified right now
header("Cache-Control: private, no-store, max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0


// Définition des paramètres de la page
$this->titre = "Recherche avancée";
require_once  __DIR__ . '/../CommonBlocs/tabs.php';


// Paramètres des valeurs du formulaire
// Année de à
$anneeValueFrom = date("Y", time());
$anneeValueTo   = 1946;

// Discipline
$valueDiscipline = "";
$blacklist = "[]";
if(isset($authInfos['I']['PARAM_INST']['S'])){ $blacklist = implode(',', $authInfos['I']['PARAM_INST']['S']); }

// Editeur
// Données pré-chargée pour ne pas surcharger le javascript (cf.: date)
$listeEditeur = "";
foreach ($editeurs as $editeur) { $listeEditeur .= "<option value=\"".$editeur["EDITEUR_ID_EDITEUR"]."\">".addslashes($editeur["EDITEUR_NOM_EDITEUR"])."</option>"; }

// Lors de l'envoi du formulaire, un cookie est créé (en JS) avec les paramètres du formulaire (formElement), on récupère
// les valeurs du cookie au chargement de la page. On détecte ensuite la présence du cookie pour réafficher le formulaire en l'état.
$AdvFormCookieValues = isset($_COOKIE["Cairn_AdvForm"]) ? $_COOKIE["Cairn_AdvForm"] : null;
if($AdvFormCookieValues != "") {$advFormCookieExist  = 1;} else {$advFormCookieExist = 0;}

// Définition du javascript de la page
// Le code est placé dans un tableau afin d'être appelé plus bas, sous les dépendances JS et jQuery
$this->javascripts[] = "

    // Vérifie si un tableau est vide
    function isEmpty(obj) {
        for(var key in obj) {
            if(obj.hasOwnProperty(key))
                return false;
        }
        return true;
    }

    // Vérifie les données du formulaire avant l'envoie
    function jsCheckDataForm() {
        // Init
        var formValide = 1;

        // Récupération du nombre d'élément
        nbre = formElement.length;

        // Vérification de tous les éléments
        for(i = 0; i < nbre; i++) {
            // Récupération des sources
            var src     = document.getElementById('src'+(i+1)).value;
            // Récupération des valeurs
            // On récupère les valeurs des champs SAUF de la date (les listes déroulantes ne peuvent pas ne pas avoir de valeur, le champ date étant en plus à double valeur, on ne s'en occupe pas)
            if(src != 'Year') {
                // Valeur
                var value   = document.getElementById('word'+(i+1)).value;

                // Champs invalide, formulaire invalide
                if(value == '') {
                    formValide = 0;
                    $('#word'+(i+1)).addClass('error_empty');
                }
                else {
                    $('#word'+(i+1)).removeClass('error_empty');
                }
            }
        }

        // Le formulaire n'est pas valide
        if(formValide == 0) {
            return false;
        }
        // Validation
        else {
            jsSaveFormValues();
        }
    }

    // Lors de la soumission du formulaire, on sauvegarde les paramètres de recherche
    function jsSaveFormValues() {
        // Sauvegarde du formulaire (mise à jour des données)
        search_adv.save();
        // Configuration du cookie (expiration dans 1h)
        var d = new Date();
        d.setTime(d.getTime() + (1*60*60*1000));
        var expires = 'expires='+ d.toUTCString();
        // Sauvegarde des données
        document.cookie = 'Cairn_AdvForm='+JSON.stringify(formElement)+';'+expires;
        // Validation du formulaire
        return true;
    }

    function addOrRemoveQuotes(fieldId) {
        // Récupération de la valeur
        var value       = document.getElementById('word'+fieldId).value;
        var length      = value.length;
        var isChecked   = document.getElementById('exact'+fieldId).checked;

        // Si l'utilisateur place des guillemets autour, on coche la case
        if((value.charAt(0) == '\"') && (value.charAt(length-1) == '\"')) {
            // On enlève les guillemets
            var nValue = value.substring(1, length-1);
            document.getElementById('word'+fieldId).value = nValue;
            // On coche la case si ce n'est pas fait
            if(isChecked === false) {
                document.getElementById('exact'+fieldId).checked='checked';
            }
        }
    }

    // Exécution automatique
    $(function() {

        // ####### Initialisation
        search_adv          = {};

        // Récupération des paramètres en GET (prioritaire sur cookie) = (ex.: ?src1=Tx&word1=Maison&operator1=AND&src2=Editeur&word2=AGON&operator2=&nparams=2&submitAdvForm=Rechercher)
        params              = cairn.parse_GET();
        // On nettoie les paramètres usuels des urls internes
        delete params['controleur'];
        delete params['action'];
        paramsNoExist       = isEmpty(params);

        // Vérification de l'existence d'un cookie (secondaire sur les valeurs GET)
        paramasCookieExist  = '".$advFormCookieExist."';

        // Définition des types de champs et de leurs options
        var TypeFields = [{
                           'Tx': {'id':'Tx', 'label':'Texte intégral', 'bool': 'AND'},
                           'R': {'id':'R', 'label':'Resumé', 'bool': 'AND'},
                           'B': {'id':'B', 'label':'Bibliographie', 'bool': 'AND'},
                           'Tr': {'id':'Tr', 'label':'Titre de revue / collection', 'bool': 'OR'},
                           'T': {'id':'T', 'label':'Titre d\'article / chapitre', 'bool': 'AND'},
                           'To': {'id':'To', 'label':'Titre d\'ouvrage / numéro', 'bool': 'AND'},
                           'Disc': {'id':'Disc', 'label':'Discipline', 'bool': 'OR'},
                           'Year': {'id':'Year', 'label':'Année de parution', 'bool': 'OR'},
                           'A': {'id':'A', 'label':'Auteur', 'bool': 'OR'},
                           'Editeur': {'id':'Editeur', 'label':'Maison d\'édition', 'bool': 'OR'},
                           'TypePub': {'id':'TypePub', 'label':'Type de publication', 'bool': 'OR'},
                           'ISBN': {'id':'ISBN', 'label':'ISBN', 'bool': 'OR'},
                           'ISSN': {'id':'ISSN', 'label':'ISSN', 'bool': 'OR'},
                           'DOI': {'id':'DOI', 'label':'DOI', 'bool': 'OR'}
                        }];

        // Range des données (préchargement sinon anomalie lors du chargement du formulaire)
        year_range = '';
        for(i = ".$anneeValueFrom."; i >= ".$anneeValueTo."; i--) { year_range += '<option value=\"'+i+'\">'+i+'</option>'; }


        // ####### Configuration des éléments du formulaire
        // Définition des différents champs
        // Le champ TYPE - Défini le type de contenu à remplir
        search_adv.addFieldType = function(field_id, field_values) {
            // Options
            var options         = '';
            var arrayType       = TypeFields[0];

            // Boucle
            $.each(arrayType, function(i, ivalue) {
                var selected = '';
                if(field_values.type == i) {selected = 'selected';}
                options += '<option value=\"'+i+'\" '+selected+'>'+ivalue['label']+'</option>';
            });

            // Concatenation et Return
            //var fieldType     = '<input type=\"hidden\" id=\"larech'+field_id+'\" name=\"larech'+field_id+'\" value=\"larech'+field_id+'\" />';
            var fieldType       = '<label>Dans</label> <select id=\"src'+field_id+'\" name=\"src'+field_id+'\" onchange=\"search_adv.changeFieldType(this.value, \''+field_id+'\');\">'+options+'</select>';
            return fieldType;
        }

        // Définition du prochain choix
        search_adv.addFieldNextChoice = function(field_id, field_values) {
            // Options
            var options         = '';
            var arrayChoice     = {'':'', 'AND':'Et', 'OR':'Ou', 'NEAR':'Près de', 'BUT':'Sauf'};

            // Boucle
            $.each(arrayChoice, function(i, ivalue) {
                var selected = '';
                if(field_values.next == i) {selected = 'selected';}
                options += '<option value=\"'+i+'\" '+selected+'>'+ivalue+'</option>';
            });

            // Concatenation et Return
            var fieldNextChoice = '<select id=\"operator'+field_id+'\" name=\"operator'+field_id+'\">'+options+'</select>';
            return fieldNextChoice;
        }

        // Champ de texte
        search_adv.addFieldKeyword = function(field_id, field_values) {
            // Options
            var checked         = '';
            if(field_values.value2 == 1) {checked = 'checked';}
            var fieldKeyword    = '<input class=\"fieldText\" type=\"text\" id=\"word'+field_id+'\" name=\"word'+field_id+'\" value=\"'+field_values.value1+'\" onKeyUp=\"addOrRemoveQuotes('+field_id+')\" />' +
                                  '<span class=\"word_option\"><label><input type=\"checkbox\" id=\"exact'+field_id+'\" name=\"exact'+field_id+'\" '+checked+' value=\"1\" /> Expression exacte</label></span>';
            return fieldKeyword;
        }

        // Champ de texte
        search_adv.addFieldText = function(field_id, field_values, autocompleteCategories) {

            var fieldText       = '<input class=\"fieldText\" type=\"text\" id=\"word'+field_id+'\" name=\"word'+field_id+'\" value=\"'+field_values.value1+'\" ';
            if (autocompleteCategories) {
                fieldText += 'data-autocomplete-categories=\"'+autocompleteCategories+'\"';
            }
            fieldText += ' />';
            return fieldText;
        }

        // Champ Date
        search_adv.addFieldAnnee = function(field_id, field_values) {
            // Options
            var options1        = year_range;
            var options2        = year_range;

            // Assignation des valeurs
            if(field_values.value1 != '') {options1 = options1.replace('value=\"'+field_values.value1+'\"', 'value=\"'+field_values.value1+'\" selected');}
            if(field_values.value2 != '') {options2 = options2.replace('value=\"'+field_values.value2+'\"', 'value=\"'+field_values.value2+'\" selected');}

            // Concatenation et Return
            var fieldAnnee      = '<label class=\"range\">Entre</label> <select class=\"year\" id=\"from'+field_id+'\" name=\"from'+field_id+'\">'+options1+'</select> <label class=\"range\">et</label> <select class=\"year\" id=\"to'+field_id+'\" name=\"to'+field_id+'\">'+options2+'</select>';
            return fieldAnnee;
        }

        // Champ des Disciplines
        search_adv.addFieldDisc = function(field_id, field_values) {
            // Options
            var options          = '';
            //var arrayDiscipline  = {'70':'Arts', '2':'Droit', '1':'Economie, Gestion', '30':'Géographie', '3':'Histoire', '9':'Info. - Com.', '4':'Intérêt général', '5':'Lettres et linguistique', '139':'Médecine', '6':'Philosophie', '7':'Psychologie', '141':'Santé publique', '8':'Sciences&nbsp;de&nbsp;l’éducation', '10':'Sciences&nbsp;politiques', '11':'Sociologie et société', '12':'Sport&nbsp;et&nbsp;société'};
            var arrayDiscipline  = [['70','Arts'],['2','Droit'],['1','Economie, Gestion'],['30','Géographie'],['3','Histoire'],['9','Info. - Com.'],['4','Intérêt général'],['5','Lettres et linguistique'],['139','Médecine'],['6','Philosophie'],['7','Psychologie'],['141','Santé publique'],['8','Sciences&nbsp;de&nbsp;l’éducation'],['10','Sciences&nbsp;politiques'],['11','Sociologie et société'],['12','Sport&nbsp;et&nbsp;société']]
            var exclude          = ".$blacklist.";
            //var exclude          = ['70', '2'];

            // Boucle
            $.each(arrayDiscipline, function(i, ivalue) {
                if(exclude.indexOf(ivalue[0]) == -1) {
                    var selected = '';
                    if(field_values.value1 == ivalue[0]) {selected = 'selected';}
                    options += '<option value=\"'+ivalue[0]+'\" '+selected+'>'+ivalue[1]+'</option>';
                }
            });

            var fieldDiscipline = '<select class=\"disc\" id=\"word'+field_id+'\" name=\"word'+field_id+'\">'+options+'</select>';
            return fieldDiscipline;
        }

        // Champ Editeur
        search_adv.addFieldEditeur = function(field_id, field_values) {
            // Options
            var options = '".$listeEditeur."';

            // Assignation des valeurs
            if(field_values.value1 != '') {options = options.replace('value=\"'+field_values.value1+'\"', 'value=\"'+field_values.value1+'\" selected');}

            var fieldEditeur      = '<select class=\"\" id=\"word'+field_id+'\" name=\"word'+field_id+'\">'+options+'</select>';
            return fieldEditeur;
        }

        // Type de publication
        search_adv.addFieldTypePub = function(field_id, field_values) {
            // Options
            var options         = '';
            var arrayType       = {'1':'Revue', '3':'Ouvrage', '2':'Magazine', '6':'Que sais-je ? / Repères'};

            // Boucle
            $.each(arrayType, function(i, ivalue) {
                var selected = '';
                if(field_values.value1 == i) {selected = 'selected';}
                options += '<option value=\"'+i+'\" '+selected+'>'+ivalue+'</option>';
            });

            // Concatenation et Return
            var fieldTypePub = '<select id=\"word'+field_id+'\" name=\"word'+field_id+'\">'+options+'</select>';
            return fieldTypePub;
        }

        // ISBN
        search_adv.addFieldISBN = function(field_id, field_values) {
            var fieldText       = '<input class=\"fieldText\" type=\"text\" id=\"word'+field_id+'\" name=\"word'+field_id+'\" value=\"'+field_values.value1+'\" placeholder=\"XXX-XXXXXXXXX-X\" />';
            return fieldText;
        }

        // ISSN
        search_adv.addFieldISSN = function(field_id, field_values) {
            var fieldText       = '<input class=\"fieldText\" type=\"text\" id=\"word'+field_id+'\" name=\"word'+field_id+'\" value=\"'+field_values.value1+'\" placeholder=\"XXXX-XXXX\" />';
            return fieldText;
        }

        // ####### Actions sur le formulaire et les champs du formulaire
        // Insertion (HTML) du champ correspond au type
        search_adv.addFormField = function(type, id, values) {
            // Init
            if(type == 'Tx') {return this.addFieldKeyword(id, values);}
            if(type == 'R') {return this.addFieldText(id, values);}
            if(type == 'B') {return this.addFieldText(id, values);}
            if(type == 'Tr') {return this.addFieldText(id, values, 'R');}
            if(type == 'T') {return this.addFieldText(id, values);}
            if(type == 'To') {return this.addFieldText(id, values);}
            if(type == 'Disc') {return this.addFieldDisc(id, values);}
            if(type == 'Year') {return this.addFieldAnnee(id, values);}
            if(type == 'A') {return this.addFieldText(id, values, 'A');}
            if(type == 'Editeur') {return this.addFieldEditeur(id, values);}
            if(type == 'TypePub') {return this.addFieldTypePub(id, values);}
            if(type == 'ISBN') {return this.addFieldISBN(id, values);}
            if(type == 'ISSN') {return this.addFieldISSN(id, values);}
            if(type == 'DOI') {return this.addFieldText(id, values);}
        }

        // Changement de type de champ via le menu TYPE
        // Remplacement du champ de valeur
        search_adv.changeFieldType = function(type, id) {
            // Sauvegarde des valeurs (remise à jour des valeurs nécessaire pour la comparaison)
            search_adv.save();

            // Par défault, récupération de la valeur du booleen...
            var next   = document.getElementById('operator'+id).value;

            // ...mais si mon booleen n'est pas défini, on vérifie les autres
            if(next == '' && id != '1') {
                // Parcours des éléments du formulaire
                $.each(formElement, function(formKey, formValues) {
                    // On retrouve un élément similaire, on lui attribue le booleen par defaut
                    if((formValues.type == type) && (formValues.id != id)) {
                        // Récupération du booleen par défaut
                        defaultNext = TypeFields[0][type]['bool'];

                        // Assignation du booleen
                        formValues.next = defaultNext;
                        document.getElementById('operator'+(formKey+1)).value=defaultNext;
                    }
                });
            }

            // Redéfinition des valeurs
            var values = {'type' : type, 'value1' : '', 'value2' : '', 'next': next}

            // Remplacement du champ de valeur
            document.getElementById('cont_value_'+id).innerHTML = search_adv.addFormField(type, id, values);
            search_adv.reload_autocomplete();
        }


        // ####### Action sur les données du formaire ###
        // Ajout d'un élément dans le tableau des données à la position souhaitée
        search_adv.add = function(position) {

            // Définition du nouvel ID
            var id   = formElement.length + 1;
            var next = '';

            // Booleen par défaut lors de l'ajout d'un élément au milieu du tableau
            if((position > '0') && (position < formElement.length)) {
                next = 'AND';
            }
            // Lors de l'ajout du champ, si l'opérateur précédent n'est pas défini, on le défini
            if((position != '0') && (document.getElementById('operator'+position).value == '')) {
                document.getElementById('operator'+position).value = 'AND';
            }

            // Insertion des nouvelles données dans le tableau des données
            var add = {'id': id, 'type' : '', 'value1' : '', 'value2' : '', 'next':next, 'new': '1'};
            formElement.splice(position, 0, add);

            // Sauvegarde des valeurs
            search_adv.save();

            // Remise en ordre du tableau
            search_adv.refresh();

            // Counter
            document.getElementById('nparams').value=formElement.length;
        }

        // Suppression d'un élément dans le tableau des données
        search_adv.remove = function(key) {
            // Si on supprime le dernier élément du formulaire, l'opérateur par défaut revient à null juste avant la sauvegarde
            if(key == '1' && formElement.length == 2) { document.getElementById('operator1').value = ''; }

            // Sauvegarde des valeurs
            search_adv.save();

            // Suppression de l'élément du tableau en le parcourant
            formElement.splice(key, 1);

            // Réorganisation des IDs du tableau de données
            var i = 1;
            $.each(formElement, function(formKey, formValues) {
                formValues.id = i;
                i++;
            });

            // Remise en ordre du tableau
            search_adv.refresh();

            // Counter
            document.getElementById('nparams').value=formElement.length;
        }

        // Sauvegarde des valeurs dans le tableau des données (lors d'un ajout et/ou d'une suppression)
        search_adv.save = function() {

            // Boucle sur les éléments du formulaire
            $.each(formElement, function(formKey, formValues) {

                // Valeur par défaut
                var id   = formValues.id;
                var type = 'Tx';
                var next = formValues.next; // Récupération de la valeur enregistrée
                var val1 = '';
                var val2 = '';

                // Element déjà affiché uniquement
                if(formValues.new != 1) {

                    // Récupération des valeurs fixes
                    var type = $('#src'+id).val();
                    var next = $('#operator'+id).val();

                    // Récupération des valeurs variables en fonction du type
                    // Tx
                    if(type == 'Tx') { var val1 = $('#word'+id).val(); if($('#exact'+id).is(':checked')) {val2 = '1';} else {val2 = '0';} }
                    // Date de publication
                    else if(type == 'Year') { var val1 = $('#from'+id).val(); var val2 = $('#to'+id).val(); }
                    // Resume (R), Biblio (B), Tr, T, To, Auteur (A), ISBN, ISSN, DOI, Discipline, Editeur, Typepub
                    else {var val1 = $('#word'+id).val();}
                }

                // Construction
                formValues.type     = type;
                formValues.value1   = val1;
                formValues.value2   = val2;
                formValues.next     = next;
                formValues.new      = 0;
            });
        }

        // Refill du formulaire suivant les paramètres récupéré en GET
        search_adv.refill = function(params) {
            // Récupération du markeur
            var nbre = params.nparams;

            // Récupération des valeurs
            for(i = 1; i <= nbre; i++) {
                var id   = i;
                var type = params['src'+i];
                var next = params['operator'+i];
                var val1 = '';
                var val2 = '';

                // Récupération des valeurs variables
                // Keyword
                if(type == 'Tx') {
                    var val1 = params['word'+i];
                    if(params['exact'+i]) {var val2 = params['exact'+i];}
                }
                // Annee
                else if(type == 'Year') {
                    var val1 = params['from'+i];
                    var val2 = params['to'+i];
                }
                // Resume (R), Biblio (B), Tr, T, To, Auteur (A), ISBN, ISSN, DOI, Discipline, Editeur, Typepub
                else {
                    var val1 = params['word'+i];
                }

                // Insertion des nouvelles données
                formElement.push({'id': id, 'type' : type, 'value1' : val1, 'value2' : val2, 'next': next});
            }

            // Reconstruction du formulaire
            search_adv.refresh();

            // Counter
            document.getElementById('nparams').value=formElement.length;
        }

        // Refresh
        search_adv.refresh = function() {
            document.getElementById('nparams').value=formElement.length;
            document.getElementById('form_advance_content').innerHTML = search_adv.build(0, formElement.length);
            search_adv.reload_autocomplete();
        }

        // ####### Construction (HTML) des éléments du formulaire
        search_adv.build = function(from, to) {
            // Initialisation des éléments du formulaire
            var form = '';

            // Récupération des éléments
            for(i = from; i < to; i++) {
                formKey    = i;
                formValues = formElement[i];

                // Définition des valeurs
                var id      = formValues.id;
                var type    = formValues.type;
                var values  = formValues;

                // Définition des boutons
                var btn_add = '<a href=\"javascript:void(0);\" onclick=\"search_adv.add('+(i+1)+');\"><span>+</span></a>';
                var btn_del = '<a href=\"javascript:void(0);\" onclick=\"search_adv.remove('+formKey+');\"><span>-</span></a>';

                // Le 1er élément ne peut être supprimé
                if(formKey == 0) {btn_del = '';}

                // Création des champs du formulaire
                form += '<div class=\"wrapper\" id=\"cont_fields_'+id+'\">';
                form += '<span class=\"adv_field_type\" id=\"cont_type_'+id+'\">'+search_adv.addFieldType(id, values)+'</span>';
                form += '<span class=\"adv_field_value\" id=\"cont_value_'+id+'\">'+search_adv.addFormField(type, id, values)+'</span>';
                form += '<span class=\"adv_field_next\" id=\"cont_next_'+id+'\">'+search_adv.addFieldNextChoice(id, values)+'</span>';
                form += '<span class=\"adv_field_action\" id=\"cont_btn_'+id+'\">'+btn_add+btn_del+'</span>';
                form += '</div>';
            }
            return form;
        }

        // ###### Méthode de feignasse
        // Théoriquement, je devrais appeler la fonction cairn.autocomplete à chaque cas.
        // Mais le code est bizarre. Il reconstruit l'intégralité du formulaire si on ajoute un élement, MAIS
        // modifie en place un élement si on switch de type d'input.
        // Ça me demanderait de refactoriser pas mal de trucs ici, et je n'ai pas le courage et le temps.
        search_adv.reload_autocomplete = function() {
            // TODO: il faudrait supprimer les autocompletes
            // On active l'autocomplete sur certains champs
            cairn.autocomplete('#form_advance_content input[data-autocomplete-categories]', {displayCategories: false});
        }

        // Définition du tableau des données
        formElement    = [];

        // Initialisation du formulaire
        // Remplissage du formulaire suivant les éléments en GET (prioritaire)
        if(paramsNoExist !== true) {
            search_adv.refill(params);
        }
        // Remplissage du formulaire avec les données du cookie
        else if(paramasCookieExist == 1) {
            formElement = JSON.parse('".$AdvFormCookieValues."');
            search_adv.refresh();
        }
        // Sinon, nouveau formulaire
        else {
            search_adv.add(0);
        }
    });
";

?>
<div id="body-content">
    <h1 class="main-title" style="position:relative">
        Recherche avancée
    </h1>
    <form id="form_advanced_search" name="form_advanced_search" action="resultats_recherche.php" method="GET" onsubmit="return jsCheckDataForm();">
        <div id="form_advance_content" class="wrapper"></div>
        <div class="wrapper mt1 center">
            <input type="hidden" id="nparams" name="nparams" value="0" />
            <input class="button" id="submitAdvForm" name="submitAdvForm" type="submit" value="Chercher">
        </div>
    </form>
</div>


