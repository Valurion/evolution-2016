// TODO: la même liste est utilisé coté serveur. Peut-être faudrait-il mieux la synchroniser...
// TODO: dégager ça du scope global

//Variable qui va permettre d'aller sur la page
//Méthode de paiement.
var goToMethodePaiement;

var goToPaiementAfterIdentification;

mappingAttributesWebtrends = {
    'action': 'WT.z_actiontype',
    'duration': 'DCSext.duree_action',
    'type': 'DCSext.pg_type',
    'category': 'WT.cg_n',
    'subcategory': 'WT.cg_s',
    'format': 'DCSext.doc_format',
    'document-type': 'DCSext.doc_type',
    'content-category': 'DCSext.pn-cat',
    'content-type': 'DCSext.pn_type',
    'content-uid': 'WT.pn_sku',
    'discipline': 'DCSext.ct_disc1',
    'sub-discipline': 'DCSext.ct_disc2',
    // Institution
    'institution-id': 'DCSext.inst_ID',
    'institution-name': 'DCSext.inst_nom',
    'is-connected-as-institution': 'DCSext.cnx_institution',
    // Revue
    'revue-id_revue': 'DCSext.pn_grid',
    'revue-titre': 'DCSext.pn_gr',
    // Numéro
    'numero-id_numpublie': 'DCSext.pn_nid',
    'numero-titre': 'DCSext.pn_ntit',
    'numero-auteurs': 'DCSext.authors',
    // Article
    'article-auteurs': 'DCSext.authors',
    'article-mot_cle': 'DCSext.articlekeywords',
    'article-titre': 'WT.pn_ID',
    'article-nb_pages': 'DCSext.doc_nb_pages',
    'article-type_commercialisation': 'DCSext.comm_art',
    // Résultat de recherche
    'recherche-terme': 'WT.oss',
    'recherche-count': 'WT.oss_r',
    'comm_rev': 'DCSext.comm_rev',
    'doc_temps_de_lecture': 'DCSext.doc_temps_de_lecture',
    'doc_pdf_dispo': 'DCSext.doc_pdf_dispo',
    'cleo': 'DCSext.cleo',
    'id_editeur': 'DCSext.id_editeur',
    'editeur': 'DCSext.editeur',
    'annee_mise_en_ligne': 'DCSext.annee_mise_en_ligne',
    'annee_tomaison': 'DCSext.annee_tomaison',
    'art_p1': 'DCSext.art_p1',
    'art_p2': 'DCSext.art_p2',
    'revue-discipline': 'DCSext.discipline',
    //Tag spécifique : tracking sur les boutons ajout au panier.
    'tx_e': 'WT.tx_e',
    'tx_u': 'WT.tx_u',
    'tx_s': 'WT.tx_s'

};

// Ouaip, j'aime pas les trucs dans le scope global, mais pas le choix à cause des machins en ajax
// Je ferais un autre système plus tard
window.wtCairn = {};


$(function() {
    "use strict";
    // Les revues où le tracking de lecture est activée.
    // La liste est limitée pour des raisons de volume de données envoyés chez webtrends

    //WebTrends : Rafraîchissement des facettes de recherche
    $(".refresh").click(function() {
        sendEvent({'action' : 'refreshSearchFilters'});
    });
    //

    //WebTrends : "Procéder à l'achat" -> WT.cg_s = coordonnées
    $("[class='continuer checkout-button']").click(function() {
        sendEvent({'subcategory' : 'coordonnées'});
    });
    //

    //WebTrends : "Procéder au paiement" -> WT.cg_s = méthode de paiement
    var methodePaiementForWebTrends = function() {
        sendEvent({'subcategory' : 'méthode de paiement'});
        $('#coordo').attr('action', 'javascript:ajax.panierCoord()');
        ajax.panierCoord();
    };

    goToMethodePaiement = methodePaiementForWebTrends;
    //

    //WebTrends : "Lien site internet"
    $("#site-internet").click(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);
        sendEvent({'action' : 'clicOnExternalLinkToJournalSite'});
    });
    //

    //WebTrends : "Lien : aide" (Pour le header et pour le footer).
    $('[id*="aide_"]').click(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);
        sendEvent({'action' : 'clicOnExternalLinkToHelpSite'});
    });
    //

    //WebTrends : "Lien : english" (Pour le header et pour le footer).
    $('[id*="_link_cairnint"]').click(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);
        sendEvent({'action' : 'clicOnExternalLinkToCairnInt'});
    });
    //

    //WebTrends : "Lien : éditeur".
    $('#link-editeur').click(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);
        sendEvent({'action' : 'clicOnExternalLinkToPublisherSite'});
    });
    //

    //WebTrends : "Lien : Numéros antérieurs disponibles sur www.persee.fr".
    $('a[title*="Persee.fr"]').click(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);
        sendEvent({'action' : 'clicOnExternalLinkToPersee'});
    });
    //

    //WebTrends : "Lien : Revue affiliée à Revues.org" (sur le picto).
    $('a[title*="Revues.org"]').click(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);
        sendEvent({'action' : 'clicOnExternalLinkToRevuesOrg'});
    });
    //

    //WebTrends : "Lien : Consulter sur Revues.org".
    $('a[class="button"][data-webtrends="goToRevues.org"]').mouseup(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        //Récupération des informations manquantes sur l'article.
        var webTrend = JSON.parse($.ajax({ type: "POST",
                        url: "index.php?controleur=Revues&action=getInfosAboutArticleForWebTrends",
                        data: {id_article: $this.data('id_article')},
                        async: false
                      }).responseText);

        //Pour les consultations à prendre en compte.
        var w_art_p2 = 'non';
        if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
            if (webTrend.type_publication == '1') {
                if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '3') {
                if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '6') {
                if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '2') {
                if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            }
        }

        sendEvent({
            'content-uid': $this.data('id_article'),
            'article-titre': $this.data('titre'),
            'numero-auteurs': $this.data('authors'),
            'action': 'clicOnExternalLinkToRevuesOrg',
            'revue-id_revue': webTrend.pn_grid,
            'revue-titre': webTrend.pn_gr,
            'art_p1': webTrend.art_p1,
            'numero-id_numpublie': webTrend.pn_nid,
            'numero-titre': webTrend.pn_ntit,
            'article-nb_pages': webTrend.doc_nb_pages,
            'id_editeur': webTrend.id_editeur,
            'annee_tomaison': webTrend.annee_tomaison,
            'annee_mise_en_ligne': webTrend.annee_mise_en_ligne,
            'editeur': webTrend.editeur,
            'comm_rev' : webTrend.comm_rev,
            'doc_temps_de_lecture' : webTrend.doc_temps_de_lecture,
            'doc_pdf_dispo' : webTrend.doc_pdf_dispo,
            'revue-discipline' : webTrend.discipline_principale,
            'discipline' : webTrend.discipline,
            'sub-discipline' : webTrend.sub_discipline,
            'content-type' : webTrend.pn_type,
            'cleo' : webTrend.cleo,
            'art_p2' : w_art_p2,
            'article-type_commercialisation' : webTrend.comm_art
        });

    });
    //

    //WebTrends : "Identification dans la partie achat" -> WT.cg_s = 'identification' (login à partir du formulaire).
    var methodeIdentificationForWebTrends = function(url) {
        sendEvent({'subcategory' : 'identification'});
        $('#connectBlocForm').attr('action', url);
        setTimeout(function() { $("#connectBlocForm").submit(); }, 50);
    };

    goToPaiementAfterIdentification = methodeIdentificationForWebTrends;
    //

    //WebTrends : "Identification dans la partie achat" -> WT.cg_s = 'identification' (login header).
    $('#login_button').click(function() {
        if ($("meta[name='WT.cg_s']").attr('content') == 'mon panier') {
            sendEvent({'subcategory' : 'identification'});
        }
    });
    //

    //WebTrends : "Affichage des facettes de recherche" WT.z_actiontype='showSearchFilters'.
    $('#trigger_filtering').click(function() {
        var $this = $(this);
        if (!$this.data('hasAlreadySendEvent')){
            sendEvent({'action' : 'showSearchFilters'});
        }
        $this.data('hasAlreadySendEvent', true);
        $('#wrapper_filter_search').toggle();
    });
    //

    //WebTrends : "tracking sur les boutons d'ajout au panier"
    $('a[data-webtrends="goToMonPanier"]').mouseup(function() {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        //Récupération des informations manquantes sur l'article.
        var webTrend = JSON.parse($.ajax({ type: "POST",
                        url: "index.php?controleur=Revues&action=getInfosAboutArticleForWebTrends",
                        data: {id_article: $this.data('id_article')},
                        async: false
                      }).responseText);

        //Pour les consultations à prendre en compte.
        var w_art_p2 = 'non';
        if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
            if (webTrend.type_publication == '1') {
                if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '3') {
                if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '6') {
                if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '2') {
                if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            }
        }

        sendEvent({
            'content-uid': $this.data('id_article'),
            'article-titre': $this.data('titre'),
            'numero-auteurs': $this.data('authors'),
            'action': 'addToCart',
            'revue-id_revue': webTrend.pn_grid,
            'revue-titre': webTrend.pn_gr,
            'art_p1': webTrend.art_p1,
            'numero-id_numpublie': webTrend.pn_nid,
            'numero-titre': webTrend.pn_ntit,
            'article-nb_pages': webTrend.doc_nb_pages,
            'id_editeur': webTrend.id_editeur,
            'annee_tomaison': webTrend.annee_tomaison,
            'annee_mise_en_ligne': webTrend.annee_mise_en_ligne,
            'editeur': webTrend.editeur,
            'comm_rev' : webTrend.comm_rev,
            'doc_temps_de_lecture' : webTrend.doc_temps_de_lecture,
            'doc_pdf_dispo' : webTrend.doc_pdf_dispo,
            'revue-discipline' : webTrend.discipline_principale,
            'discipline' : webTrend.discipline,
            'sub-discipline' : webTrend.sub_discipline,
            'content-type' : webTrend.pn_type,
            'cleo' : webTrend.cleo,
            'art_p2' : w_art_p2,
            'article-type_commercialisation' : webTrend.comm_art,
            'tx_e' : 'a',
            'tx_u' : '1',
            'tx_s' : getPriceWebTrends($this.data('prix_article'))
        });

    });
    //

    //WebTrends : "Ajouter au panier, pour l'achat de ce numéro".
    $("a[class='block']").mouseup(function() {

        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        var prix = $('#add-to-cart-slider-purchase-numero input[type=\'radio\']:checked + label[class="grid-u-11-12"] span[class="price"]').text();

        sendEvent({
            'action': 'addToCart',
            'tx_e' : 'a',
            'tx_u' : '1',
            'tx_s' : getPriceWebTrends(prix)
        });

    });
    //

    //WebTrends : "Ajouter au panier, pour l'achat de ce numéro".
    $("a[id='achat-abonnement']").mouseup(function() {

        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        var prix = $('#add-to-cart-slider-purchase-revue input[type=\'radio\']:checked + label[class="grid-u-11-12"] span[class="price"]').text();

        sendEvent({
            'action': 'addToCart',
            'tx_e' : 'a',
            'tx_u' : '1',
            'tx_s' : getPriceWebTrends(prix)
        });

    });
    //

    //WebTrends : "Ajouter au panier, pour l'achat de cet article".
    $("a[id='achat-article']").mouseup(function() {

        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        var prix = $('#add-to-cart-slider-purchase-article span[class="price"]').text();

        sendEvent({
            'action': 'addToCart',
            'tx_e' : 'a',
            'tx_u' : '1',
            'tx_s' : getPriceWebTrends(prix)
        });

    });
    //

    //WebTrends : "Suppression d'articles du panier.
    $("input[class='icon del-panier'][data-webtrends='removeFromCart']").mouseup(function() {

        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        //Récupération des informations manquantes sur l'article.
        var webTrend = JSON.parse($.ajax({ type: "POST",
                        url: "index.php?controleur=Revues&action=getInfosAboutArticleForWebTrends",
                        data: {id_article: $this.data('id_article')},
                        async: false
                      }).responseText);

        //Pour les consultations à prendre en compte.
        var w_art_p2 = 'non';
        if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
            if (webTrend.type_publication == '1') {
                if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '3') {
                if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '6') {
                if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '2') {
                if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            }
        }

        sendEvent({
            'content-uid': $this.data('id_article'),
            'article-titre': $this.data('titre'),
            'numero-auteurs': $this.data('authors'),
            'action': 'removeFromCart',
            'revue-id_revue': webTrend.pn_grid,
            'revue-titre': webTrend.pn_gr,
            'art_p1': webTrend.art_p1,
            'numero-id_numpublie': webTrend.pn_nid,
            'numero-titre': webTrend.pn_ntit,
            'article-nb_pages': webTrend.doc_nb_pages,
            'id_editeur': webTrend.id_editeur,
            'annee_tomaison': webTrend.annee_tomaison,
            'annee_mise_en_ligne': webTrend.annee_mise_en_ligne,
            'editeur': webTrend.editeur,
            'comm_rev' : webTrend.comm_rev,
            'doc_temps_de_lecture' : webTrend.doc_temps_de_lecture,
            'doc_pdf_dispo' : webTrend.doc_pdf_dispo,
            'revue-discipline' : webTrend.discipline_principale,
            'discipline' : webTrend.discipline,
            'sub-discipline' : webTrend.sub_discipline,
            'content-type' : webTrend.pn_type,
            'cleo' : webTrend.cleo,
            'art_p2' : w_art_p2,
            'article-type_commercialisation' : webTrend.comm_art,
            'tx_e' : 'r',
            'tx_u' : '1',
            'tx_s' : getPriceWebTrends($this.data('prix_article'))
        });

    });
    //

    //WebTrends : "Suppression de numéros du panier.
    $("input[class='icon del-panier'][data-webtrends='removeFromCart-numero']").mouseup(function() {

        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        //Récupération des informations manquantes sur l'article.
        var webTrend = JSON.parse($.ajax({ type: "POST",
                        url: "index.php?controleur=Revues&action=getInfosAboutNumeroForWebTrends",
                        data: {id_numero: $this.data('id_numero')},
                        async: false
                      }).responseText);

        //Pour les consultations à prendre en compte.
        var w_art_p2 = 'non';
        if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
            if (webTrend.type_publication == '1') {
                if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '3') {
                if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '6') {
                if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '2') {
                if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            }
        }

        sendEvent({
            'action': 'removeFromCart',
            'art_p1': webTrend.art_p1,
            'content-uid': webTrend.pn_grid,
            'id_editeur': webTrend.id_editeur,
            'editeur': webTrend.editeur,
            'numero-id_numpublie': webTrend.pn_nid,
            'numero-titre': webTrend.pn_ntit,
            'revue-id_revue': webTrend.pn_grid,
            'revue-titre': webTrend.pn_gr,
            'annee_tomaison': webTrend.annee_tomaison,
            'comm_rev' : webTrend.comm_rev,
            'discipline' : webTrend.discipline,
            'sub-discipline' : webTrend.sub_discipline,
            'annee_mise_en_ligne': webTrend.annee_mise_en_ligne,
            'cleo' : webTrend.cleo,
            'revue-discipline' : webTrend.discipline_principale,
            'content-type' : webTrend.pn_type,
            'art_p2' : w_art_p2,
            'tx_e' : 'r',
            'tx_u' : '1',
            'tx_s' : getPriceWebTrends($this.data('prix_numero'))
        });

    });
    //

    //WebTrends : "Suppression d'abonnements dans le panier.
    $("input[class='icon del-panier'][data-webtrends='removeFromCart-revue']").mouseup(function() {

        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        //Récupération des informations manquantes sur l'article.
        var webTrend = JSON.parse($.ajax({ type: "POST",
                        url: "index.php?controleur=Revues&action=getInfosAboutRevueForWebTrends",
                        data: {id_revue: $this.data('id_revue'), id_numero: $this.data('id_numero')},
                        async: false
                      }).responseText);

        //Attention, pas de w_art_p2, au niveau des revues.
//        var w_art_p2 = 'non';
//        if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
//            if (webTrend.type_publication == '1') {
//                if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
//                    w_art_p2 = 'oui';
//                }
//            } else if (webTrend.type_publication == '3') {
//                if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
//                    w_art_p2 = 'oui';
//                }
//            } else if (webTrend.type_publication == '6') {
//                if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
//                    w_art_p2 = 'oui';
//                }
//            } else if (webTrend.type_publication == '2') {
//                if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
//                    w_art_p2 = 'oui';
//                }
//            }
//        }

        sendEvent({
            'action': 'removeFromCart',
            'content-uid': webTrend.pn_grid,
            'content-type' : webTrend.pn_type,
            'revue-id_revue': webTrend.pn_grid,
            'revue-titre': webTrend.pn_gr,
            'id_editeur': webTrend.id_editeur,
            'editeur': webTrend.editeur,
            'comm_rev' : webTrend.comm_rev,
            'art_p1': webTrend.art_p1,
            'discipline' : webTrend.discipline,
            'sub-discipline' : webTrend.sub_discipline,
            'cleo' : webTrend.cleo,
            'revue-discipline' : webTrend.discipline_principale,
            'tx_e': 'r',
            'tx_u': '1',
            'tx_s': getPriceWebTrends($this.data('prix_revue'))
        });

    });
    //

    //WebTrends : "Suppression du crédit d'articles".
    $("input[data-webtrends='removeFromCart-credit-article']").mouseup(function() {

        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);

        sendEvent({
            'action': 'removeFromCart',
            'tx_e' : 'a',
            'tx_u' : '1',
            'tx_s' : getPriceWebTrends($this.data('prix_credit_article'))
        });

    });
    //

    var listRevueToWatching = [
        // Pour cairn
        'ANNA', 'CDLE', 'CM', 'SPUB', 'STA', 'AMX', 'CRITI', 'ESPRI', 'INSO', 'LCI',
        // Pour cairn-int
        'E_ANNA', 'E_ESPRI'
    ];
    // Chaque composant de cette liste est elle-même une liste,
    // où le premier terme est le selecteur css des liens à tracker
    // et le second terme le nom de l'action qui sera envoyé à webtrends.
    // En gros, chaque composant est envoyé à la fonction mappingWatchersLinks
    // C'est surtout pour raccourcir le code, qui devenait lourdingue à maintenir
    var mappingWatchersLinks = [
        ['#link-cite-this-article', 'clickOnCiteThisArticle'], // Pour citer cet article
        ['#link-plan-of-article', 'clickOnPlanOfArticle'], // Plan de l'article
        ['#link-abstract', 'clickOnAbstract'], // Résumé
        ['#see-also', 'clickOnSeeAlso'], // Sur un sujet proche
        ['#article-cited-by .cited-by', 'clickOnCitedBy'],  // Cité par
        ['#article-english-version', 'clickOnEnglishVersion'], // Lien vers cairn-int (depuis cairn)
        ['#article-french-version', 'clickOnFrenchVersion'], // Lien vers cairn (depuis cairn-int)
        ['.wrapper_nom_auteur', 'clickOnArticleAuthor'],  // Auteur d'un article
        ['.icon-usermenu-tools-zen', 'clickOnToolboxZenArticle'],  // Version zen
        ['.icon-usermenu-tools-pdf', 'clickOnToolboxPdfArticle'], // Version pdf
        ['.icon-usermenu-tools-print', 'clickOnToolboxPrintArticle'], // Version print
        ['.icon-usermenu-tools-bigger-char', 'clickOnToolboxAddArticleToBiblio'], // Ajout à la biblio
        ['.amorce.note', 'clickOnAmorceDeNote'], // Amorces de notes
        ['.figure, .tableau', 'clickOnZoomImage'], // Carroussel
        ['#numero-cover', 'clickOnNumeroCover'], // Retour au numéro via la couverture
        // Les boutons pages précédentes/suivantes
        ['.article_navpages:first .blue_button.left', 'clickOnTopPreviousArticle'],
        ['.article_navpages:first .blue_button.right', 'clickOnTopNextArticle'],
        ['.article_navpages:last .blue_button.left', 'clickOnBottomPreviousArticle'],
        ['.article_navpages:last .blue_button.right', 'clickOnBottomNextArticle'],
        ['#ajoutalertes button', 'subscribeToRevue'],
    ];

    // Ce qui suit doit être revu car c'est vraiment loin d'être maintenable
    // J'avais fais ça peu de temps avant la refonte du système pour webtrends
    // Ça envoi un event au click sur un download de pdf
    // À noté que ce bout de code est copié-collé sur Vue/recherche/pertinent.php
    // à cause des requêtes ajax
    $('[data-webtrends="goToPdfArticle"]').mouseup(function(ev) {
        var $this = $(this);
        if ($this.data('hasAlreadySendEvent')) return;
        $this.data('hasAlreadySendEvent', true);
        // On désactive le comportement normal du lien, on le réactivera plus bas
        $this.click(false);

        //Récupération des informations manquantes sur l'article.
        var webTrend = JSON.parse(
            $.ajax({
                type: "POST",
                url: "index.php?controleur=Revues&action=getInfosAboutArticleForWebTrends",
                data: {
                    id_article: $this.data('id_article'),
                },
                async: false,
            }).responseText
        );

        //Pour les consultations à prendre en compte.
        var w_art_p2 = 'non';
        if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
            if (webTrend.type_publication == '1') {
                if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '3') {
                if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '6') {
                if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            } else if (webTrend.type_publication == '2') {
                if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                    w_art_p2 = 'oui';
                }
            }
        }

        sendEvent({
            'content-uid': $this.data('id_article'),
            'article-titre': $this.data('titre'),
            'numero-auteurs': $this.data('authors'),
            'action': 'pdfDownload',
            'revue-id_revue': webTrend.pn_grid,
            'revue-titre': webTrend.pn_gr,
            'art_p1': webTrend.art_p1,
            'numero-id_numpublie': webTrend.pn_nid,
            'numero-titre': webTrend.pn_ntit,
            'article-nb_pages': webTrend.doc_nb_pages,
            'id_editeur': webTrend.id_editeur,
            'annee_tomaison': webTrend.annee_tomaison,
            'annee_mise_en_ligne': webTrend.annee_mise_en_ligne,
            'editeur': webTrend.editeur,
            'comm_rev' : webTrend.comm_rev,
            'doc_temps_de_lecture' : webTrend.doc_temps_de_lecture,
            'doc_pdf_dispo' : webTrend.doc_pdf_dispo,
            'revue-discipline' : webTrend.discipline_principale,
            'discipline' : webTrend.discipline,
            'sub-discipline' : webTrend.sub_discipline,
            'content-type' : webTrend.pn_type,
            'cleo' : webTrend.cleo,
            'art_p2' : w_art_p2,
            'article-type_commercialisation' : webTrend.comm_art,
            'format' : 'PDF',
            'category' : 'texte intégral PDF'
        });

        // Safari pose problème lors du chargement d'un pdf. Il semble abandonner la requête ajax
        // lors de l'affichage du pdf.
        // On lui force la main en attendant suffisament de temps pour sendEvent de communiquer avec
        // webtrends.
        // Puis, on lance le téléchargement "normal" du pdf
        // L'idéal aurait été de fournir une fonction de callback à la fonction permettant
        // d'envoyer des données chez webtrends. Cela nécéssite de modifier le code de webtrends.
        setTimeout(function() {
            $this.unbind('click');
            window.location = $this.attr('href');
        }, 400);
        return false;
    });

    window.wtCairn.sendEvent = sendEvent;

    if (!window.hasOwnProperty('cairnPageMetadatas')) return;
    return webtrendsRouter(window.cairnPageMetadatas);



    function webtrendsRouter(metadatas) {
        // Tracking pour les pages articles
        /* MaJ du 18/11/2015: On désactive le tracking de ces events pour éviter d'exploser le quota */
        /* MaJ du 23/11/2015: Finalement, on les réactive... Je ne sais pas pourquoi */
        if (metadatas.page.category === 'article') {
            // Ce sont les données communes pour le tracking des articles
            var articleCommonDatas = {
                'format': getMetaContent('format'),
                'category': getMetaContent('category'),
                'subcategory': getMetaContent('subcategory'),
                'article-type_commercialisation': getMetaContent('article-type_commercialisation'),
                'content-uid': metadatas.article.id_article,
                'numero-id_numpublie': metadatas.numero.id_numpublie,
                'revue-id_revue': metadatas.revue.id_revue,
            };

            // Le tracking ne se fait que sur la page article fulltext ou zen, au format html
            // De plus, il est sur une liste réduite de revues
            var isTracking = (metadatas.page.subcategory === 'fulltext') || (metadatas.page.subcategory === 'zen-fulltext');
            isTracking = isTracking && (metadatas.page.format === 'html');
            isTracking = isTracking && (listRevueToWatching.indexOf(metadatas.revue.id_revue) >= 0);

            if (isTracking) {
                // Tracking de l'impression d'une page article
                watchPrintingArticle(articleCommonDatas);
                watchScrollArticle(articleCommonDatas);
                // On surveille les liens listés dans mappingWatchersLinks
                $.each(mappingWatchersLinks, function(index, mapping) {
                    watchClickOnLink($(mapping[0]), mapping[1], articleCommonDatas);
                });
                // Tracking de la sortie du zen
                watchClickOnLink($('#top-exit-zen'), 'clickOnTopExitZen', articleCommonDatas);
                watchClickOnLink($('#bottom-exit-zen'), 'clickOnBottomExitZen', articleCommonDatas);
                watchClickOnLink($('#right-side-exit-zen'), 'clickOnRightSideExitZen', articleCommonDatas);
            }
        }
    }



    /*
        Retourne l'attribut @content de la balise meta, qui a pour @name l'attribut name
    */
    function getMetaContent(name) {
        var meta = $('html > head > meta[name="' + mappingAttributesWebtrends[name] + '"]');
        if (meta.length === 0) {
            return null;
        }
        return meta.attr('content');
    }


    /*
        Envoi un event chez webtrends

        PARAMETERS
        ===========
        datasEvent: Object
            Un objet contenant les données à envoyer comme contexte pour cet event.
            Les clés peuvent être les clés présentes dans ``mappingAttributesWebtrends``, qui seront alors normalisés selon la nomenclature de webtrends.
    */
    function sendEvent(datasEvent) {
        // Certains bloqueurs de pubs empêchent l'utilisation de webtrend
        if (!window.hasOwnProperty('dcsMultiTrack')) {
            console.warn('dcsMultiTrack missing');
            return;
        }
        var args = [];
        for (var key in datasEvent) {
            if (!datasEvent.hasOwnProperty(key)) continue;
            if (datasEvent[key] === null) continue;
            args.push(mappingAttributesWebtrends[key] || key);
            args.push(datasEvent[key]);
        }
        if (window.hasOwnProperty('DEBUG') && (window.DEBUG === true)) {
            console.debug(args);
        }
        dcsMultiTrack.apply(dcsMultiTrack, args);
    }


    /*
        Vérifie si un nombre est entre deux bornes.
        Les bornes peuvent être dans n'importe quel ordre

        PARAMETERS
        ==========
        x: Integer
            Le nombre à vérifier
        bound1: Integer
            Première borne
        bound2: Integer
            Seconde borne

        RETURN
        ======
        Boolean
            Si bound1 <= x <= bound2 ou bound2 <= x <= bound1
    */
    function betweenBounds(x, bound1, bound2) {
        var vmin = Math.min(bound1, bound2);
        var vmax = Math.max(bound1, bound2);
        if ((vmin <= x) && (x <= vmax)) {
            return true;
        }
        return false;
    }


    /*
        Surveille si un article est lu

        On compte le nombre de fois où le haut du corps de l'article est dans l'intervalle de scrolling en haut de l'écran.
        Quand ce nombre est égale à 1, on envoie un event.
        On compte le nombre de fois où le milieu du corps de l'article est dans l'intervalle de scrolling au milieu de l'écran.
        Quand ce nombre est égale à 1, on envoie un event.
        On compte le nombre de fois où le bas du corps de l'article est dans l'intervalle de scrolling au bas de l'écran.
        Quand ce nombre est égale à 1, on envoie un event.

        Quand les trois compteurs sont différents de 0, on envoi un event pour signaler que l'article a été entièrement lu.


        L'intervalle de scrolling correspond à la position de l'écran lors du précédent scroll et la position actuelle de l'écran (les positions sont relatives au document).
        La présente fonction prend en charge le redimensionnement de la fenêtre en cours de lecture
    */
    function watchScrollArticle(datas) {
        datas = $.extend(true, {}, datas);
        var $articleCorps = $('#from_xml_bottom .corps');
        var $window = $(window);

        // Compteurs
        var counterShowTop = 0;
        var counterShowMiddle = 0;
        var counterShowBottom = 0;
        // Position précédente du scroll
        var previousScrollTop = $window.scrollTop();
        // Si les events ont déjà étés envoyés
        var hasSendTopEvent = false;
        var hasSendMiddleEvent = false;
        var hasSendBottomEvent = false;
        var hasSendReadEvent = false;
        // Chrono
        var wordsByMinute = 5000;  // 1000 pour un lecteur très rapide et qui lira le texte, mais on veut aussi chopper les gens qui lisent en diagonale, et en excluant les scrollers fous
        var wordsBySecond = wordsByMinute / 60;
        var articleText = ($articleCorps[0].innerText || $articleCorps[0].textContent);
        var countWordsInText = articleText.split(/\s+/).length;
        var estimatedReadingTime = countWordsInText / wordsBySecond;
        var initTimestamp = Date.now();
        // console.log("temps d'estimation pour 5000 mots/minute : ", estimatedReadingTime);
        // console.log("temps d'estimation selon le nombre de page : ", $('meta[name="DCSext.doc_temps_de_lecture"]').attr('content'));

        $window.scroll(function() {
            // Initialisation des variables permettant de calculer les positions et intervalles
            var currentScrollTop = $window.scrollTop();
            var corpsTop = $articleCorps.offset().top;
            var corpsMiddle = corpsTop + ($articleCorps.height() / 2);
            var corpsBottom = corpsTop + $articleCorps.height();
            var windowHeight = $window.height();
            var semiWindowHeight = windowHeight / 2;

            // On vérifie que la position de l'écran par rapport à chaque intervalle
            if (betweenBounds(corpsTop, previousScrollTop, currentScrollTop)) {
                counterShowTop += 1;
            }
            if (betweenBounds(corpsMiddle, previousScrollTop + semiWindowHeight, currentScrollTop + semiWindowHeight)) {
                counterShowMiddle += 1;
            }
            if (betweenBounds(corpsBottom, previousScrollTop + windowHeight, currentScrollTop + windowHeight)) {
                counterShowBottom += 1;
            }

            // On envoie les events si nécésaire
            if (!hasSendTopEvent && (counterShowTop > 0)) {
                hasSendTopEvent = true;
                datas['action'] = 'scrollTop';
                datas['duration'] = (Date.now() - initTimestamp) / 1000;
                sendEvent(datas);
            }
            if (!hasSendMiddleEvent && (counterShowMiddle > 0)) {
                hasSendMiddleEvent = true;
                datas['action'] = 'scrollMiddle';
                datas['duration'] = (Date.now() - initTimestamp) / 1000;
                sendEvent(datas);
            }
            if (!hasSendBottomEvent && (counterShowBottom > 0)) {
                hasSendBottomEvent = true;
                datas['action'] = 'scrollBottom';
                datas['duration'] = (Date.now() - initTimestamp) / 1000;
                sendEvent(datas);
            }
            if (!hasSendReadEvent && hasSendTopEvent && hasSendMiddleEvent && hasSendBottomEvent) {
                var currentDuration = (Date.now() - initTimestamp) / 1000;
                if (currentDuration >= estimatedReadingTime) {
                    hasSendReadEvent = true;
                    datas['action'] = 'hasReadArticle';
                    datas['duration'] = currentDuration;
                    sendEvent(datas);
                }
            }

            // La position de scroll actuelle est enregistrée
            previousScrollTop = currentScrollTop;
        });
    }

    /*
        Surveille si un utilisateur a cliqué sur un lien

        PARAMETERS
        ==========
        $elements: $.Element
            Les éléments jquery où le click lèvera un event.
        actionName: String
            Le nom de l'action qui sera envoyé au paramètre de l'event.
            Si le paramètre datas a déjà un attribut actionName, il ne sera pas remplacé
        datas: Object
            Les données envoyées en paramètre de l'event.
            Sera également attaché le paramètre actionName
            /!\ Une copie est faite, cette fonction ne travaille pas avec l'objet envoyé en paramètre
            Ceci pour éviter les effets de bords
    */
    function watchClickOnLink($elements, actionName, datas) {
        if (!$elements.length) return;
        datas = $.extend(true, {}, datas);
        if (!datas.hasOwnProperty('action')) {
            datas['action'] = actionName;
        }

        var hasAlreadyClick = false;
        // /!\ On n'utilise pas l'event click car il ne fonctionne pas avec le clic milieu
        $elements.mouseup(function() {
            if (hasAlreadyClick) return;
            hasAlreadyClick = true;
            sendEvent(datas);
        });
    }


    /*
        Surveille si un utilisateur a lancé une impression

        Le code de gestion de l'event est inspiré de : http://tjvantoll.com/2012/06/15/detecting-print-requests-with-javascript/

        /!\ Je ne peux que detecter l'ouverture de la boite modale d'impression, mais je ne peux pas savoir si l'utilisateur a validé ou annulé l'impression
    */
    function watchPrintingArticle(datas) {
        datas = $.extend(true, {}, datas);
        datas['action'] = 'printArticle';
        var hasAlreadyPrinted = false;

        if (window.hasOwnProperty('onbeforeprint') && window.hasOwnProperty('onafterprint')) {
            window.onbeforeprint = handleBeforePrintEvent;
            window.onafterprint = handleAfterPrintEvent;
        } else if (window.matchMedia) {
            var printEvent = window.matchMedia('print');
            printEvent.addListener(function(ev) {
                if (ev.matches) {
                    handleBeforePrintEvent();
                } else {
                    handleAfterPrintEvent();
                }
            });
        }


        function handleBeforePrintEvent() {
            if (hasAlreadyPrinted) return;
        }
        function handleAfterPrintEvent() {
            if (hasAlreadyPrinted) return;
            hasAlreadyPrinted = true;
            sendEvent(datas);
        }
    }

    /*
     Cette fonction permet d'avoir le prix formaté pour webTrends.
     */
    function getPriceWebTrends(price) {

        price = price.toString();

        price = price.replace(' €', '');

        if (price.indexOf('.') === -1 && price !== '') {
            price += '.00';
        }

        return price;
    }

})
