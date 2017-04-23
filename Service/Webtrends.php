<?php
/*
Fonctions et classes permettant de parser, formatter et insérer les tags webtrends,
utilisé pour les statistiques de cairn

NOTES
=====
Il y a un grand nombre de tableaux de hashage dans la classe Webtrends.
Normalement, beaucoup aurait dû être des constantes définis dans une classe à part
(j'ai commencé dans le service Constants)
Mais je n'ai pas vraiment le temps de nettoyer le code et d'avoir un truc lisible.
Du coup, pour contourner le code de Pythagoria sur ce point là, je suis contraint d'utiliser des mappings.

*/
class Webtrends
{

    // Depuis la suite de lettre utilisé par Pythagoria pour identifier le format de publication
    // on normalise pour avoir quelque chose de plus lisible
    private $mappingLetterToNamePage = [
        'A' => 'article',
        'R' => 'resume',
        'C' => 'crossref',
        'Z' => 'zen',
        'PR' => 'print-resume',
        'PA' => 'print-article',
        'PDF' => 'landing_pdf',
        'FEUILA' => 'feuilleter',
        'FEUILC' => 'feuilleter',
    ];


    // Mapping entre le type de publication et son nom à envoyer à webtrends
    private $mappingTypepub = [
        1 => 'Revues',
        2 => 'Magazines',
        3 => 'Ouvrages',
        4 => 'État du monde',
        5 => 'Monographie',
        6 => 'Que sais-je ? / Repères',
    ];


    // Mapping pour rentre le type de publication plus lisible
    public $mappingTypepubDescPages = [
        1 => 'revue',
        2 => 'magazine',
        3 => 'ouvrage',
        4 => 'edm',
        5 => 'monographie',
        6 => 'encyclopedie',
    ];


    // Mapping contenant les tags fixes pour webtrends, par type de page
    public $mappingDescPages = [
        // Article HTML
        'article-payant' => [
            'type' => 'Produit',
            'category' => 'texte intégral HTML',
            'article-type_commercialisation' => 'payant',
            'content-category' => 'Article',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],
        'article-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'texte intégral HTML',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],
        'article-gratuit' => [
            'type' => 'Produit',
            'category' => 'texte intégral HTML',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],
        'article-unknown' => [
            'type' => 'Produit',
            'category' => 'texte intégral HTML',
            // 'subcategory' => 'unknown',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],


        // Article ZEN
        'zen-payant' => [
            'type' => 'Produit',
            'category' => 'texte intégral ZEN',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'payant',
            'format' => 'ZEN',
            'document-type' => 'texte intégral',
        ],
        'zen-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'texte intégral ZEN',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'ZEN',
            'document-type' => 'texte intégral',
        ],
        'zen-gratuit' => [
            'type' => 'Produit',
            'category' => 'texte intégral ZEN',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'ZEN',
            'document-type' => 'texte intégral',
        ],
        'zen-unknown' => [
            'type' => 'Produit',
            'category' => 'texte intégral ZEN',
            // 'subcategory' => 'unknown',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'ZEN',
            'document-type' => 'texte intégral',
        ],


        // Article imprimable
        'print-article-payant' => [
            'type' => 'Produit',
            'category' => 'texte intégral imprimable',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'payant',
            'format' => 'PRINT',
            'document-type' => 'texte intégral',
        ],
        'print-article-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'texte intégral imprimable',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'PRINT',
            'document-type' => 'texte intégral',
        ],
        'print-article-gratuit' => [
            'type' => 'Produit',
            'category' => 'texte intégral imprimable',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'PRINT',
            'document-type' => 'texte intégral',
        ],
        'print-article-unknown' => [
            'type' => 'Produit',
            'category' => 'texte intégral imprimable',
            // 'subcategory' => 'unknwon',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'PRINT',
            'document-type' => 'texte intégral',
        ],


        // Article feuilleteur flash
        'feuilleter-payant' => [
            'type' => 'Produit',
            'category' => 'texte intégral feuilleteur',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'payant',
            'format' => 'SWF',
            'document-type' => 'texte intégral',
        ],
        'feuilleter-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'texte intégral feuilleteur',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'SWF',
            'document-type' => 'texte intégral',
        ],
        'feuilleter-gratuit' => [
            'type' => 'Produit',
            'category' => 'texte intégral feuilleteur',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'SWF',
            'document-type' => 'texte intégral',
        ],
        'feuilleter-unknown' => [
            'type' => 'Produit',
            'category' => 'texte intégral feuilleteur',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'SWF',
            'document-type' => 'texte intégral',
        ],



        // Article PDF
        'landing_pdf-payant' => [
            'type' => 'Produit',
            'category' => 'texte intégral PDF',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'payant',
            'format' => 'PDF',
            'document-type' => 'texte intégral',
        ],
        'landing_pdf-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'texte intégral PDF',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'PDF',
            'document-type' => 'texte intégral',
        ],
        'landing_pdf-gratuit' => [
            'type' => 'Produit',
            'category' => 'texte intégral PDF',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'PDF',
            'document-type' => 'texte intégral',
        ],
        'landing_pdf-unknown' => [
            'type' => 'Produit',
            'category' => 'texte intégral PDF',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'PDF',
            'document-type' => 'texte intégral',
        ],


        // Résumé HTML
        'resume-payant' => [
            'type' => 'Produit',
            'category' => 'résumé',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'payant',
            'format' => 'HTML',
            'document-type' => 'résumé',
        ],
        'resume-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'résumé',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'HTML',
            'document-type' => 'résumé',
        ],
        'resume-gratuit' => [
            'type' => 'Produit',
            'category' => 'résumé',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'HTML',
            'document-type' => 'résumé',
        ],
        'resume-unknown' => [
            'type' => 'Produit',
            'category' => 'résumé',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'HTML',
            'document-type' => 'résumé',
        ],



        'print-resume-payant' => [
            'type' => 'Produit',
            'category' => 'résumé imprimable',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'payant',
            'format' => 'PRINT',
            'document-type' => 'résumé',
        ],
        'print-resume-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'résumé imprimable',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'PRINT',
            'document-type' => 'résumé',
        ],
        'print-resume-gratuit' => [
            'type' => 'Produit',
            'category' => 'résumé imprimable',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'PRINT',
            'document-type' => 'résumé',
        ],
        'print-resume-unknown' => [
            'type' => 'Produit',
            'category' => 'résumé imprimable',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'PRINT',
            'document-type' => 'résumé',
        ],

        // Article quand on vient de crossref
        'crossref-payant' => [
            'type' => 'Produit',
            'category' => 'Landing Crossref',
            'article-type_commercialisation' => 'payant',
            'content-category' => 'Article',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],
        'crossref-post-movingwall' => [
            'type' => 'Produit',
            'category' => 'Landing Crossref',
            'article-type_commercialisation' => 'post barrière mobile',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],
        'crossref-gratuit' => [
            'type' => 'Produit',
            'category' => 'Landing Crossref',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'gratuit',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],
        'crossref-unknown' => [
            'type' => 'Produit',
            'category' => 'Landing Crossref',
            // 'subcategory' => 'unknown',
            'content-category' => 'Article',
            'article-type_commercialisation' => 'unknown',
            'format' => 'HTML',
            'document-type' => 'texte intégral',
        ],

        // Pages accueil
        'accueil-revue' => [
            'type' => 'Catalogue',
            'content-type' => 'Revues',
            'category' => 'accueil revues',
        ],
        'accueil-ouvrage' => [
            'type' => 'Catalogue',
            'content-type' => 'Ouvrages',
            'category' => 'accueil ouvrages',
        ],
        'accueil-encyclopedie' => [
            'type' => 'Catalogue',
            'content-type' => 'Que sais-je ? / Repères',
            'category' => 'accueil poche',
        ],
        'accueil-magazine' => [
            'type' => 'Catalogue',
            'content-type' => 'Magazines',
            'category' => 'accueil magazines',
        ],

        // Pages du niveau REVUE
        'revue' => [
            'type' => 'Catalogue',
            'content-category' => 'Revues',
            'content-type' => 'Revues',
            'category' => 'revue',
        ],
        'magazine' => [
            'type' => 'Catalogue',
            'content-category' => 'Magazines',
            'content-type' => 'magazine',
            'category' => 'magazine'
        ],
        'collection' => [
            'type' => 'Catalogue',
            'content-type' => 'Collection',
            'category' => "collection",
        ],

        // Pages du niveau NUMERO
        'revue-numero' => [
            'type' => 'Catalogue',
            'category' => 'numéro de revue',
        ],
        'ouvrage-numero' => [
            'type' => 'Catalogue',
            'category' => 'ouvrage',
        ],
        'magazine-numero' => [
            'type' => 'Catalogue',
            'category' => 'numéro de magazine',
        ],
        'encyclopedie-numero' => [
            'type' => 'Catalogue',
            'category' => 'encyclopédie',
        ],



        'revue-en-savoir-plus' => [
            'type' => 'Cairn',
            'content-category' => 'Revues',
            'content-type' => 'Revues',
            'category' => 'en savoir plus sur la revue',
        ],
        // Les pages "À propos", 'services-aux-*' et les conditions générales
        'corporate-*' => [
            'type' => 'Cairn Corporate',
            'category' => 'Cairn'
        ],
        'corporate-*-a-propos' => [
            'type' => 'Cairn Corporate',
            'category' => 'a propos',
            'subcategory' => 'a propos de cairn.info',
        ],
        'corporate-*-services-editeur' => [
            'type' => 'Cairn Corporate',
            'category' => 'a propos',
            'subcategory' => 'services aux éditeurs',
        ],
        'corporate-*-services-institutions' => [
            'type' => 'Cairn Corporate',
            'category' => 'a propos',
            'subcategory' => 'services aux institutions',
        ],
        'corporate-*-services-particuliers' => [
            'type' => 'Cairn Corporate',
            'category' => 'a propos',
            'subcategory' => 'services aux particuliers',
        ],
        'corporate-*-conditions' => [
            'type' => 'Cairn Corporate',
            'category' => 'mentions légales',
            'subcategory' => 'conditions d\'utilisation',
        ],
        'corporate-*-conditions-vente' => [
            'type' => 'Cairn Corporate',
            'category' => 'mentions légales',
            'subcategory' => 'conditions générales de vente',
        ],
        'corporate-*-vie-privee' => [
            'type' => 'Cairn Corporate',
            'category' => 'mentions légales',
            'subcategory' => 'vie privée',
        ],
        'corporate-*-plan-du-site' => [
            'type' => 'Cairn Corporate',
            'category' => 'plan du site',
            'subcategory' => '',
        ],
        'corporate-*-contact' => [
            'type' => 'Cairn Corporate',
            'category' => 'contact',
            'subcategory' => '',
        ],
        'compte-*' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'créer un compte',
        ],
        'compte-*-mdp' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'récupérer son mot de passe',
        ],
        'compte-*-mcpt' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'mon compte',
        ],
        'compte-*-panier' => [
            'type' => 'Compte',
//            'category' => 'mon cairn.info',
            'category' => 'vente en ligne',
            'subcategory' => 'mon panier',
        ],
        'confirm-*-achat-ogone' => [
            'category' => 'vente en ligne',
            'subcategory' => 'confirmation d\'achat',
        ],
        'erreur-*-achat-ogone' => [
            'category' => 'vente en ligne',
            'subcategory' => 'paiement annulé',
        ],
        'compte-*-achat' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'mes achats',
        ],
        'compte-*-biblio' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'ma bibliographie',
        ],
        'compte-*-alerte' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'mes alertes',
        ],
        'compte-*-mail-mdp' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'modification email ou mot de passe',
        ],
        'compte-*-acces-hors' => [
            'type' => 'Compte',
            'category' => 'accès hors campus',
        ],
        'compte-*-desinscription-newsletter' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'désinscription newsletter',
        ],
        //La page accueil des revues
        'disc-revue' => [
            'type' => 'Catalogue',
            'content-type' => 'Revues',
            'category' => 'discipline revues',
        ],
        'disc-ouvrage' => [
            'type' => 'Catalogue',
            'content-type' => 'Ouvrages',
            'category' => 'discipline ouvrages',
            // Les disciplines et sous disciplines sont insérés à la volée dans le template,
            // car le code est compliqué à cet endroit
            // De méme pour subcategory
        ],
        'disc-encyclopedie' => [
            'type' => 'Catalogue',
            'content-type' => 'Que sais-je ? / Repères',
            'category' => 'discipline encylopédie de poche',
        ],
//        'mes-recherches' => [
//            'type' => 'Compte',
//            'category' => 'résultats de recherche',
//        ],
        'mes-recherches' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'mes recherches',
        ],
//        'mon-historique' => [
//            'type' => 'Compte',
//            'category' => 'mon historique',
//        ],
        'mon-historique' => [
            'type' => 'Compte',
            'category' => 'mon cairn.info',
            'subcategory' => 'mon historique',
        ],
        'liste-revues' => [
            'type' => 'Catalogue',
            'content-type' => 'Revues',
            'category' => 'liste des revues',
        ],
        'liste-collections' => [
            'type' => 'Catalogue',
            'content-type' => 'Collections',
            'category' => 'liste des collections',
        ],
        'liste-editeur' => [
            'type' => 'Catalogue',
            'content-type' => 'Editeur',
            'category' => 'éditeur',
        ],
        'liste-auteur' => [
            'type' => 'Catalogue',
            'content-type' => 'Auteur',
            'category' => 'auteur',
        ],
        'liste-citepar' => [
            'type' => 'Catalogue',
            'content-type' => 'Cité Par',
            'category' => 'cité par',
        ],
//        'flux-rss' => [
//            'type' => 'Catalogue',
//            'content-type' => 'RSS',
//            'category' => 'liste des flux rss',
//        ],
        'flux-rss' => [
            'type' => 'Catalogue',
            'content-type' => 'RSS',
            'category' => 'flux rss',
            'subcategory' => '',
        ],
        // recherches
        'resultats-recherche' => [
            'type' => 'Recherche',
            'category' => 'résultats de recherche',
        ],
        'recherche-avancee' => [
            'type' => 'Recherche',
            'category' => 'recherche avancée',
        ],
        'sur-un-sujet-proche' => [
            'type' => 'Catalogue',
            'category' => 'sur un sujet proche',
        ],
//        'credit' => [
//            'type' => 'Vente',
//            'category' => 'Ecommerce',
//        ],
        'credit' => [
            'type' => 'Vente',
            'category' => 'mon cairn.info',
            'subcategory' => 'mon crédit d\'articles',
        ],
        'page-error' => [
            'category' => 'erreur',
            'subcategory' => '',
        ],
    ];


    // Mapping entre le nom du tag webtrend utilisé dans le code php et le tag réel
    // Les tags réels webtrends sont complètement indigeste
    private $mappingDescWebtrendsTags = [
        'protocol' => 'DCSext.protocole_http',
        'type' => 'DCSext.pg_type',
        'category' => 'WT.cg_n',
        'subcategory' => 'WT.cg_s',
        'format' => 'DCSext.doc_format',
        'document-type' => 'DCSext.doc_type',
        'content-category' => 'DCSext.pn-cat',
        'content-type' => 'DCSext.pn_type',
        'content-uid' => 'WT.pn_sku',
        'mask-ip' => 'DCS.dcsipa', // Associer à un nombre masquant les N derniers chiffres de l'IP
        'discipline' => 'DCSext.ct_disc1',
        'sub-discipline' => 'DCSext.ct_disc2',
        // Institution
        'institution-id' => 'DCSext.inst_ID',
        'institution-name' => 'DCSext.inst_nom',
        'is-connected-as-institution' => 'DCSext.cnx_institution',
        // Éditeur
        'editeur-nom' => 'DCSext.editeur',
        'editeur-id_editeur' => 'DCSext.ID_editeur',
        // Revue
        'revue-id_revue' => 'DCSext.pn_grID',
        'revue-titre' => 'DCSext.pn_gr',
        'revue-is_free' => 'DCSext.comm_rev',
        'revue-affiliation-cleo' => 'DCSext.cleo',
        // Numéro
        'numero-id_numpublie' => 'DCSext.pn_nID',
        'numero-titre' => 'DCSext.pn_ntit',
        'numero-auteurs' => 'DCSext.authors',
        // Article
        'article-auteurs' => 'DCSext.authors',
        'article-mot_cle' => 'DCSext.articlekeywords',
        'article-titre' => 'WT.pn_ID',
        'article-nb_pages' => 'DCSext.doc_nb_pages',
        'article-type_commercialisation' => 'DCSext.comm_art',
        'article-has_notes' => 'DCSext.doc_notes',
        'article-has_images' => 'DCSext.doc_images',
        'article-estimate_reading_time' => 'DCSext.doc_temps_de_lecture',
        'article-has_pdf' => 'DCSext.doc_pdf_dispo',
        'article-has_abstract' => 'DCSext.doc_Abstract_available',
        'article-has_plan' => 'DCSext.doc_PlanOfArticle_available',
        'article-has_see_also' => 'DCSext.doc_SeeAlso_available',
        'article-has_english_version' => 'DCSext.doc_english_available',
        'numero-annee_parution' => 'DCSext.annee_tomaison',
        // Résultat de recherche
        'recherche-terme' => 'WT.oss',
        'recherche-count' => 'WT.oss_r',
        'institution-continent' => 'DCSext.inst_continent',
        'institution-pays' => 'DCSext.inst_pays',
        'institution-type' => 'DCSext.inst_p11',
        'institution-langue' => 'DCSext.inst_langue',
        'institution-consortium' => 'DCSext.inst_p12',
        'institution-relation_commerciale' => 'DCSext.inst_p13',
        'institution-cliente_revues' => 'DCSext.inst_p14',
        'institution-cliente_magazines' => 'DCSext.inst_p15',
        'institution-cliente_ouvrages' => 'DCSext.inst_p16',
        'institution-cliente_poches' => 'DCSext.inst_p17',
        'institution-categorie_tarifaire' => 'DCSext.inst_p18',
        'institution-anciennete' => 'DCSext.inst_p19',
        'nom-editeur-beneficiaire' => 'DCSext.art_p1',
        'numero-annee' => 'DCSext.annee_mise_en_ligne',
        'resume-anglais-disponible' => 'DCSext.doc_english_abstract_available',
        'consultation-a-prendre-en-compte' => 'DCSext.art_p2',
        //Utilisateur (moncairn)
        'utilisateur-mon-cairn' => 'DCSext.cnx_moncairn',
        'utilisateur-a-effectue-un-achat' => 'DCSext.mc_p2',
        'utilisateur-activite' => 'DCSext.mc_p1',
        'utilisateur-mail' => 'DCSext.mc_p3',
        'revue-discipline' => 'DCSext.discipline'
    ];


    public static function cleanString($string)
    {
        return strip_tags(trim($string));
    }
    public static function cleanArrayString($array)
    {
        return array_filter(array_map('self::cleanString', $array));
    }


    // Retourne les tags communs à l'ensemble des pages, et qui ne sont pas définis dans mappingDescPages
    public function getTagsForAllPages($namePage, $authInfos)
    {
        $meta = array();
        $meta['protocol'] = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
        $meta['mask-ip'] = '1';
        if (!isset($this->mappingDescPages[$namePage])) {
            return $meta;
        }
        $mapping = $this->mappingDescPages[$namePage];
        foreach ($mapping as $key => $value) {
            if (!isset($key)) {
                continue;
            }
            $meta[$key] = $value;
        }
        if (isset($authInfos['I'])) {
            $meta['institution-id'] = $authInfos['I']['ID_USER'];  // Id de l'institution
            $meta['institution-name'] = $authInfos['I']['NOM'];     // Nom de l'institution
            $meta['is-connected-as-institution'] = "En institution";
            $meta['institution-continent'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['CONTINENT'];
            $meta['institution-pays'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['PAYS'];
            $meta['institution-type'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['TYPE'];
            $meta['institution-langue'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['LANGUE'];
            $meta['institution-consortium'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['CONSORTIUM'];
            $meta['institution-relation_commerciale'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['RELATION_COMMERCIALE'];
            $meta['institution-cliente_revues'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['CLIENTE_REVUES'];
            $meta['institution-cliente_magazines'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['CLIENTE_MAGAZINES'];
            $meta['institution-cliente_ouvrages'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['CLIENTE_OUVRAGES'];
            $meta['institution-cliente_poches'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['CLIENTE_POCHES'];
            $meta['institution-categorie_tarifaire'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['CATEGORIE_TARIFAIRE'];
            $meta['institution-anciennete'] = $authInfos['I']['PARAM_INST_WEBTRENDS']['ANCIENNETE'];
        } else {
            $meta['is-connected-as-institution'] = "Hors institution";
        }

        //Pour les utilisateurs mon Cairn.
        if (isset($authInfos['U'])) {

            //Tableau des professions
            $tabProfession = Service::get('Constants')->USER_ACTIVITIES;

            $meta['utilisateur-mon-cairn'] = 'Connecté Mon Cairn.info';
            $meta['utilisateur-activite'] = isset($tabProfession[$authInfos['U']['PROFESSION']]) ? $tabProfession[$authInfos['U']['PROFESSION']] : '';
            $meta['utilisateur-a-effectue-un-achat'] = $authInfos['U']['ACHAT-PPV'] ? 1 : 0;
            $meta['utilisateur-mail'] = $authInfos['U']['ID_USER'];
        } else {
            $meta['utilisateur-mon-cairn'] = 'Non connecté Mon Cairn.info';
        }
        return $meta;
    }


    // Retourne les tags pour la page revue
    public function getTagsForRevuePage($revue, $dataDiscipline = null)
    {
        $meta = array();
        $meta['content-uid'] = $revue['ID_REVUE'];
        $meta['revue-id_revue'] = $revue['ID_REVUE'];
        $meta['revue-titre'] = self::cleanString($revue['TITRE']);
        $meta['editeur-id_editeur'] = self::cleanString($revue['ID_EDITEUR']);
        $meta['editeur-nom'] = self::cleanString($revue['NOM_EDITEUR']);
        if (isset($revue['MOVINGWALL'])) {
            $meta['revue-is_free'] = ($revue['MOVINGWALL'] == 0) ? 'gratuite' : 'payante';
        }
        if (isset($revue['AFFILIATION']) && (strpos($revue['AFFILIATION'], 'Revues.org') !== false)) {
            $meta['revue-affiliation-cleo'] = 'Affiliée au Cléo';
        }

        //Pour la partie discipline et sous-discipline.
        $meta['discipline'] = isset($dataDiscipline['DISCIPLINES']) ? implode(';', $dataDiscipline['DISCIPLINES']) : '';
        $meta['sub-discipline'] = isset($dataDiscipline['SOUS DISCIPLINE']) ? implode(';', $dataDiscipline['SOUS DISCIPLINE']) : '';

        //Pour la partie nom de l'éditeur bénéficiaire.
        $meta['nom-editeur-beneficiaire'] = isset($revue['BENEFICIAIRE']) ? $revue['BENEFICIAIRE'] : '';

        //Pour la discipline de la revue.
        $meta['revue-discipline'] = isset($revue['DISCIPLINE']) ? $revue['DISCIPLINE'] : '' ;

        return $meta;
    }


    public function getTagsForNumeroPage($numero, $revue, $dataDiscipline = null)
    {
        $authors = array();
        foreach (explode(',', $numero['NUMERO_AUTEUR']) as $author) {
            $author = explode(':', $author);
            $author = self::cleanArrayString($author);
            array_pop($author);
            array_push($authors, implode(' ', $author));
        }
        $authors = implode(';', $authors);

        $meta = [
            'content-type' => $this->mappingTypepub[$revue['TYPEPUB']],
            'content-uid' => $revue['REVUE_ID_REVUE'],
            'editeur-id_editeur' => $revue['REVUE_ID_EDITEUR'],
            'editeur-nom' => $revue['EDITEUR_NOM_EDITEUR'],
            'revue-id_revue' => $revue['REVUE_ID_REVUE'],
            'revue-titre' => self::cleanString($revue['REVUE_TITRE']),
            'numero-id_numpublie' => $numero['NUMERO_ID_NUMPUBLIE'],
            'numero-titre' => $numero['NUMERO_TITRE'],
            'numero-annee_parution' => $numero['NUMERO_ANNEE'],
        ];
        if (!!$authors) {
            $meta['numero-auteurs'] = $authors;
        }
        if (isset($revue['MOVINGWALL'])) {
            $meta['revue-is_free'] = ($revue['MOVINGWALL'] == 0) ? 'gratuite' : 'payante';
        }
        if (isset($revue['REVUE_AFFILIATION']) && (strpos($revue['REVUE_AFFILIATION'], 'Revues.org') !== false)) {
            $meta['revue-affiliation-cleo'] = 'Affiliée au Cléo';
        }

        //Pour la partie discipline et sous-discipline.
        $meta['discipline'] = isset($dataDiscipline['DISCIPLINES']) ? implode(';', $dataDiscipline['DISCIPLINES']) : '';
        $meta['sub-discipline'] = isset($dataDiscipline['SOUS DISCIPLINE']) ? implode(';', $dataDiscipline['SOUS DISCIPLINE']) : '';

        //Pour 'Année de mise en ligne sur Cairn'.
        $meta['numero-annee'] = isset($numero['NUMERO_DATE_MISEENLIGNE']) ? (substr($numero['NUMERO_DATE_MISEENLIGNE'], 0, 4) != '0000' ? substr($numero['NUMERO_DATE_MISEENLIGNE'], 0, 7) : '') : '';

        //Pour la partie nom de l'éditeur bénéficiaire.
        $meta['nom-editeur-beneficiaire'] = isset($revue['BENEFICIAIRE']) ? $revue['BENEFICIAIRE'] : '';

        //Pour la partie nombre de page.
        $meta['article-nb_pages'] = isset($revue['NUMERO_NB_PAGE']) ? $revue['NUMERO_NB_PAGE'] : '' ;

        //Pour la discipline de la revue.
        $meta['revue-discipline'] = isset($revue['DISCIPLINE']) ? $revue['DISCIPLINE'] : '' ;

        return $meta;
    }

    /**
     * Cette fonction permet de récupérer
     * les tags pour webTrends.
     */
    public function getTagsForArticlePage($article, $pageType, $licence = null, $html = null, $dataDiscipline = null)
    {
        $configArticle = explode(',', $article['ARTICLE_CONFIG_ARTICLE']);
        $keywords = explode(',', $article['ARTICLE_MOTS_CLES']);
        $keywords = self::cleanArrayString($keywords);
        $keywords = implode(';', $keywords);

        $authors = array();
        foreach (explode(',', $article['ARTICLE_AUTEUR']) as $author) {
            $author = explode(':', $author);
            array_pop($author);  // On dégage l'affiliation de l'auteur
            $author = self::cleanArrayString($author);
            array_pop($author);
            array_push($authors, implode(' ', $author));
        }
        $authors = implode(';', $authors);

        $beginPage = intval(trim($article['ARTICLE_PAGE_DEBUT']));
        $endPage = intval(trim($article['ARTICLE_PAGE_FIN']));

        $meta = [
            'article-auteurs' => $authors,
            'article-mot_cle' => $keywords,
            'article-titre' => self::cleanString($article['ARTICLE_TITRE']),
            'numero-id_numpublie' => $article['NUMERO_ID_NUMPUBLIE'],
            'numero-titre' => self::cleanString($article['NUMERO_TITRE']),
            'revue-id_revue' => $article['REVUE_ID_REVUE'],
            'revue-titre' => self::cleanString($article['REVUE_TITRE']),
            'editeur-nom' => self::cleanString($article['EDITEUR_NOM_EDITEUR']),
            'editeur-id_editeur' => self::cleanString($article['EDITEUR_ID_EDITEUR']),
            'content-type' => $this->mappingTypepub[$article['REVUE_TYPEPUB']],
            'content-uid' => $article['ARTICLE_ID_ARTICLE'],
            'numero-annee_parution' => $article['NUMERO_ANNEE'],
        ];
        // if (!!$licence) {
        //     $meta['DCSext'] = $licence;
        // }
        if (!!$beginPage && !!$endPage) {
            $meta['article-nb_pages'] = ($endPage - $beginPage) + 1;
        }
        // On vérifie si l'article possède des images et des notes/renvois
        if (!!$html) {
            $meta['article-has_notes'] = preg_match('/class\s*=\s*([\'"])\s*notes\s*\1/i', $html['CONTENUS']) ? 1: 0;
            $meta['article-has_images'] = preg_match('/loadimg\.php/', $html['CONTENUS']) ? 1 : 0;
            $meta['article-has_plan'] = preg_match('/id\s*=\s*([\'"])\s*plan-of-article\s*\1/', $html['CONTENUS']) ? 1 : 0;
        }
        $meta['article-has_see_also'] = isset($article['ARTICLE_SUJET_PROCHE']) && ($article['ARTICLE_SUJET_PROCHE'] == 1) ? 1 : 0;
        // On compte le temps de lecture estimé
        if (is_numeric($article['ARTICLE_PAGE_DEBUT']) && is_numeric($article['ARTICLE_PAGE_FIN'])) {
            $meta['article-estimate_reading_time'] = (intval($article['ARTICLE_PAGE_FIN']) - intval($article['ARTICLE_PAGE_DEBUT']) + 1) * 45;
        }
        if (isset($article['REVUE_MOVINGWALL'])) {
            $meta['revue-is_free'] = ($article['REVUE_MOVINGWALL'] == 0) ? 'gratuite' : 'payante';
        }

        $isCairnInt = Configuration::get('modeBoutons') === 'cairninter';
        //Dimitry (Cairn : 30/11/2015).
        // Serge (Cairn : 05/12/2016). Non Dimitry, on ne test jamais cairn/cairn-int avec le prefix de l'ID_REVUE...
        // Un bug à cause de ça... D'où la raison du présent commentaire :p
        if ($pageType == 'R' && $isCairnInt) {
            $meta['revue-affiliation-cleo'] = '';
        }

        if (isset($article['REVUE_AFFILIATION']) && (strpos($article['REVUE_AFFILIATION'], 'Revues.org') !== false)) {
            $meta['revue-affiliation-cleo'] = 'Affiliée au Cléo';
        }
        // On vérifie si l'article possède un pdf
        $meta['article-has_pdf'] = ($configArticle[3] == 1) ? 1 : 0;
        $meta['article-has_abstract'] = ($configArticle[0] != 0) ? 1 : 0;
        if (Configuration::get('mode', null) != 'cairninter') {
            $meta['article-has_english_version'] = (isset($article['ARTICLE_ID_ARTICLE_S']) && ($article['ARTICLE_ID_ARTICLE_S'] != null)) ? 1 : 0;
        }

        //Pour la partie discipline et sous-discipline.
        $meta['discipline'] = isset($dataDiscipline['DISCIPLINES']) ? implode(';', $dataDiscipline['DISCIPLINES']) : '';
        $meta['sub-discipline'] = isset($dataDiscipline['SOUS DISCIPLINE']) ? implode(';', $dataDiscipline['SOUS DISCIPLINE']) : '' ;

        //Pour l'année de mise en ligne.
        $meta['numero-annee'] = isset($article['NUMERO_DATE_MISEENLIGNE']) ? (substr($article['NUMERO_DATE_MISEENLIGNE'], 0, 4) != '0000' ? substr($article['NUMERO_DATE_MISEENLIGNE'], 0, 7) : '') : '';

        //Pour la partie Résumé anglais disponible
        $meta['resume-anglais-disponible'] = isset($article['RESUME_ANGLAIS_DISPONIBLE']) ? $article['RESUME_ANGLAIS_DISPONIBLE'] : 'non';

        //Pour la partie nom de l'éditeur bénéficiaire.
        $meta['nom-editeur-beneficiaire'] = isset($article['NOM_EDITEUR_BENEFICIAIRE']) ? $article['NOM_EDITEUR_BENEFICIAIRE'] : '' ;

        //Pour la partie discipline.
        $meta['revue-discipline'] = isset($article['REVUE_DISCIPLINE']) ? $article['REVUE_DISCIPLINE'] : '' ;

        return $meta;
    }


    // Retourne les tags pour les disciplines, et sous-disciplines
    public function getTagsForDisciplinePage($disciplines, $disciplinePos, $subDisciplines = null, $subDisciplinePos = null)
    {
        $tags = array();

        // On recherche le nom de la discipline
        foreach ($disciplines as $discipline) {
            if ($discipline['POS_DISC'] != $disciplinePos) {
                continue;
            }
            $tags['subcategory'] = $discipline['DISCIPLINE'];
            $tags['discipline'] = $discipline['DISCIPLINE'];
            break;
        }

        // Si la sous-discipline existe, on recherche son nom
        if (($subDisciplines !== null) && ($subDisciplinePos !== null)) {
            foreach ($subDisciplines as $discipline) {
                if ($discipline['POS_DISC'] != $subDisciplinePos) {
                    continue;
                }
                $tags['sub-discipline'] = $discipline['DISCIPLINE'];
                break;
            }
        }

        return $tags;
    }

    public function getTagsForResearchPage($count, $term)
    {
        return array('recherche-terme' => $term, 'recherche-count' => $count);
    }


    public function getTagsForCollection($collection)
    {
        $tags = $this->getTagsForRevuePage($collection);
        $tags['subcategory'] = $tags['revue-titre'];
        return $tags;
    }


    public function getTagsForEditeurPublications($editeur)
    {
        return [
            'subcategory' => Service::get('ParseDatas')->cleanString($editeur['EDITEUR_NOM_EDITEUR']),
            'editeur-id_editeur' => $editeur['EDITEUR_NOM_EDITEUR'],
            'editeur-nom' => $editeur['EDITEUR_ID_EDITEUR'],
        ];
    }


    public function getTagsForAuteurPublications($auteur)
    {
        return array('subcategory' => Service::get('ParseDatas')->cleanString(implode(' ', [$auteur['AUTEUR_PRENOM'], $auteur['AUTEUR_NOM']])));
    }


    public function getTagsForCitePar($article)
    {
        return array('subcategory' => Service::get('ParseDatas')->cleanString($article['ARTICLE_TITRE']));
    }


    // On formatte les tags webtrends vers leur nom réel et on transforme les tags vers
    // ce qu'attend le header de chaque template
    public function webtrendsTagsToHeadersTags($meta)
    {
        $headers = [
            [
                'tagname' => '!COMMENT',
                'content' => 'WEBTRENDS metadata starts here',
            ]
        ];
        foreach ($meta as $key => $value) {
            array_push($headers, array(
                'tagname' => 'meta',
                'attributes' => [
                    array('name' => 'name', 'value' => $this->mappingDescWebtrendsTags[$key]),
                    array('name' => 'content', 'value' => $value),
                ]
            ));
        }
        array_push($headers, [
            'tagname' => '!COMMENT',
            'content' => 'WEBTRENDS metadata ends here',
        ]);
        return $headers;
    }

    public function webtrendsHeaders($namePage, $authInfos)
    {
        $tags = $this->getTagsForAllPages($namePage, $authInfos);
        return $this->webtrendsTagsToHeadersTags($tags);
    }

    public function getLettersToNamePage($letters, $article)
    {
        $namePage = $this->mappingLetterToNamePage[$letters];
        $currentTimeStamp = time();
        $movingWallTimeStamp = strtotime($article['NUMERO_MOVINGWALL']);
        $limitTimeStamp = strtotime('2000-01-01');  // J'ignore pourquoi cette date précise, mais c'est précisé dans le ticket #51850
        if (in_array($namePage, ['article', 'zen', 'print-article', 'feuilleter', 'landing_pdf', 'resume', 'crossref'])) {
            if ($article['ARTICLE_PRIX'] > 0) {
                if (($movingWallTimeStamp > $currentTimeStamp) || ($movingWallTimeStamp <= $limitTimeStamp)) {
                    return $namePage . '-payant';
                } elseif (($movingWallTimeStamp <= $currentTimeStamp) && ($movingWallTimeStamp > $limitTimeStamp)) {
                    return $namePage . '-post-movingwall';
                }
            } else {
                return $namePage . '-gratuit';
            }
            return $namePage . '-unknown';
        }
        return $namePage;
    }

    public function getIntegerToTypePub($int)
    {
        return $this->mappingTypepub[$int];
    }

    /**
     * Cette fonction va permettre de savoir
     * si on doit prendre en compte une consultation
     * dans le cadre de webTrends.
     */
    public function getTagConsultation($institution, $currentArticle) {
        $reponse = 'non';
        switch ($currentArticle['REVUE_TYPEPUB']) {
            case 1: //Revues
                $dateW = date('Y', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 2));
                if (($currentArticle['REVUE_MOVINGWALL'] > 0) && ($currentArticle['NUMERO_ANNEE'] >= $dateW) && (strtolower($institution['PARAM_INST_WEBTRENDS']['CLIENTE_REVUES']) == 'oui')) {
                    $reponse = 'oui';
                }
                break;
            case 3: //Ouvrages
                if (strtolower($institution['PARAM_INST_WEBTRENDS']['CLIENTE_OUVRAGES']) == 'oui' && isset($currentArticle['NUMERO_GRILLEPRIX']) && $currentArticle['NUMERO_GRILLEPRIX'] !== '0') {
                    $reponse = 'oui';
                }
                break;
            case 6: //Encyclopédies poche
                if (strtolower($institution['PARAM_INST_WEBTRENDS']['CLIENTE_POCHES']) == 'oui') {
                    $reponse = 'oui';
                }
                break;
            case 2: //Magazines
                if (strtolower($institution['PARAM_INST_WEBTRENDS']['CLIENTE_MAGAZINES']) == 'oui') {
                    $reponse = 'oui';
                }
                break;
        }
        return $reponse;
    }
}
