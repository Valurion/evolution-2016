/*
    Script pour la recherche et sa fonction d'auto-complétion.
*/
$(function() {
    "use strict";
    var cairn = window.cairn;
    /*
        CONSTANTES
    */
    var URLS = {
        // L'api de suggestions
        'pythagoria_sugg': window.location.protocol + '//dev.pythagoria.com/' + ((!window.IS_CAIRN_INT) ? 'pth_cairn_labo' : 'pth_cairnint_labo'),
        // Lien vers le proxy du front-office qui s'occupe de rediriger vers la bonne page quand on clique
        // sur une entrée de l'autocomplete
        'redirect': './index.php?controleur=Recherche&action=redirectFromAutocomplete&searchIn={2}&term={0}&category={1}',
    };
    /*
        Les réponses renvoyés par pythagoria contiennent le type de catégories.
        Par raison d'optimisation, cette catégorie est représenté par une lettre.
        La table qui suit réalise la correspondance entre cette lettre et la string
        qui sera affichée à l'utilisateur.
    */
    if (!window.IS_CAIRN_INT) {
        var CATEGORIES = {
            'A': 'Auteurs',
            'R': 'Revues/Collections',
            'O': 'Titres',
            'E': 'Expressions'
        };
    } else {
        var CATEGORIES = {
            'A': 'Authors',
            'R': 'Journals',
            'O': 'Titles',
            'E': 'Expressions'
        };
    }


    function sourcePythagoria($element, config) {
        return function(req, add) {
            // Préparation de la requête à envoyer chez pythagorio
            var request = JSON.stringify({
                'params': [
                    req.term,
                ],
                'id': 1,
                'jsonrpc': '2.0',
                'method': 'myCpl',
            });

            $.post(URLS['pythagoria_sugg'], request, function(response) {
                /*
                    Pour chaque item présent dans la réponse, on capitalise la première lettre
                    et on ajoute un @id
                    Ces items modifiés sont ensuite renvoyés vers le plugin d'autocompletion.
                */
                var suggestions = [];
                var filterCategories = $element.data('autocomplete-categories');
                if (filterCategories) {
                    filterCategories = filterCategories.split(',');
                }

                for (var i = 0; i < response.result.length; i++) {
                    var suggestion = response.result[i];
                    if (filterCategories) {
                        if (filterCategories.indexOf(suggestion.category) === -1) {
                            continue;
                        }
                    }
                    suggestion.id = 'pth_' + i;
                    suggestion.label = suggestion.value.charAt(0).toUpperCase() + suggestion.value.slice(1);
                    suggestion.displayCategories = config.displayCategories;
                    suggestions.push(suggestion)
                }
                add(suggestions);
            }, "json");
        }
    }


    function resolveCairnUrl(e, ui) {
        var term = ui.item.value.trim();
        var category = ui.item.category;
        /*
            À partir des paramètres fournis, renvoie vers la page correspondante.
        */
        var location = document.location;
        term = term.replace(String.fromCharCode(160), ' '); // FIX: pour harmoniser les nbsp en blanc
        // TODO: harmoniser les termes renvoyés par pythagoria
        term = term.replace('%20', '+');
        // TODO: Dans le cas de plusieurs autocomplete sur la même page, peut-être revoir ces trois lignes
        var currentAccessMode = 'all';
        if($("#searchInAccess:checked").length > 0){
            currentAccessMode = 'access';
        }
        location.href = URLS['redirect'].format(encodeURIComponent(term), category, currentAccessMode);
    }



    // Personnalisation du widget d'autocomplétion.
    $.widget("custom.catcomplete", $.ui.autocomplete, {
        _renderMenu: function(ul, items) {
            var lastCategory, nbCategories = 0;

            // Pour chaque item, on ajoute sa catégorie, si pas encore existante dans le menu d'autocomplétion.
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                if (item.displayCategories && (item.category !== lastCategory)) {
                    nbCategories++;
                    var catElem = $('<li class="ui-autocomplete-category">{0}</li>'.format(CATEGORIES[item.category]));
                    ul.append(catElem);
                    lastCategory = item.category;
                }
                var itemElem = $('<li><a>{0}</li></a>'.format(cairn.highlightTerm(item.label, this.term)));
                itemElem.data('ui-autocomplete-item', item);
                ul.append(itemElem);
            }
        }
    });



    cairn.autocomplete = function(selector, config) {
        "use strict";
        // Définition de la configuration par défaut
        config = config || {};
        if (!config.hasOwnProperty('redirectOnClick')) config.redirectOnClick = false;
        if (!config.hasOwnProperty('displayCategories')) config.displayCategories = true;

        var $searchFields = $(selector);
        // sur certaines pages, jquery.ui est déjà défini et provoque des conflits de versions. En attendant de résoudre les problèmes de conflit sur toutes les pages, on vérifie si autocomplete est défini dans les prototypes de nodes jquery.
        if (!$searchFields.autocomplete) return false;

        // Configuration du plugin d'auto-completion
        $searchFields.each(function(index, $searchField) {
            $searchField = $($searchField);
            var catcompleteParams = {
                delay: 100,
                minLength: 2,
                source: sourcePythagoria($searchField, config),
            };
            if (config.redirectOnClick === true) {
                catcompleteParams['select'] = resolveCairnUrl;
            }
            $searchField.catcomplete(catcompleteParams);
        });
    }
});
