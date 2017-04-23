<?php

/**
 * CONTROLER - Control the navigation for consultation pages:
 *  - LISTE des collections
 *  - LISTE des revues
 *  - DETAIL d'un éditeur avec la LISTE de ses publications
 *  - DETAIL d'un auteur avec la LISTE de ses publications
 *  - DETAIL d'une collection avec la LISTE de ses publications
 *
 * @author ©Pythagoria - www.pythagoria.com
 * @author benjamin
 */
require_once 'Framework/Controleur.php';

require_once 'Modele/Content.php';
require_once 'Modele/ContentCom.php';

class ControleurListeDetail extends Controleur {

    private $content;

    // instantiate the Model Classes
    public function __construct() {
        $this->content = new Content();
        $this->contentCom = new ContentCom('dsn_com');
    }

    public function index() {
        $type = $this->requete->getParametre("TYPE");

        switch ($type) {
            case 'collections':
            case 'ouvrages':
            case 'revues':
            case 'encyclopedies':
                $this->liste($type);
                break;
            case 'auteur':
            case 'editeur':
            case 'collection':
            case 'citepar':
                $this->listeDetail($type);
                break;
        }
    }

    private function getValuesInArray($findme, $array) {
        $tmpArray = array();
        foreach($array as $arrayKey => $arrayValues) {
            foreach($arrayValues as $key => $value) {
                if($key == $findme) {
                    $tmpArray[] = $value;
                }
            }
        }
        return $tmpArray;
    }

    private function liste($type) {

        // Définition du typePub
        switch ($type) {
            case 'collections':
            case 'ouvrages':
                $typepub = 3;
                break;
            case 'revues':
                $typepub = 1;
                break;
            case 'encyclopedies':
                $typepub = 6;
                break;
        }

        // Récupération des paramètres
        $editeur            = null;
        $disciplines        = null;
        $sousdisciplines    = null;
        $collections        = null;
        $abo                = null;
        $extraValues        = array();  // Tableau permettant de placer des valeurs indépendantes et/ou spécifique à un certain type de revue
        
        // Définition du multipage
        $nbreFrom           = 0;
        $nbreOffset         = 100;  // Nbre d'élément par page
        $currentPage        = 1;    // Page 

        // Editeur et/ou Discipline et/ou Sous-discipline définie
        if ($this->requete->existeParametre("editeur")) {
            $editeur        = $this->requete->getParametre("editeur");
            $disciplines    = $this->content->getDisciplinesOfEditeur($editeur, $typepub);
        }
        if ($this->requete->existeParametre("discipline")) {
            $discipline     = $this->requete->getParametre("discipline");
        }
        if ($this->requete->existeParametre("sousdiscipline")) {
            $sousdiscipline = $this->requete->getParametre("sousdiscipline");
        }
        if ($this->requete->existeParametre("collection")) {
            $collection     = $this->requete->getParametre("collection");
        }
        if($this->requete->existeParametre("abo")) {
            $abo = $this->requete->getParametre("abo");
        }
        if ($this->requete->existeParametre("LIMIT")) {
            $mpLimite       = $this->requete->getParametre("LIMIT");
            //$nbreFrom       = ($mpLimite / $nbreOffset);
            $nbreFrom       = $mpLimite;
        }

        // Récupération des valeurs
        // Editeurs
        $editeurs   = $this->content->getEditeurs(TRUE, $typepub);
        $editeursWR = array();
        //$editeursWR = $this->content->getEditeursByNbreRevue($typepub);        
        
        // Collections
        // Récupération des collections (pour les ouvrages uniquement) SSI un éditeur est sélectionné
        if($typepub == 3 && $editeur != null) { $collections = $this->content->getListCollectionFromEditeur($editeur); }
        
        // Disciplines et Sous-Disciplines
        // Récupération des disciplines générales SI aucun éditeur n'est sélectionné
        if(($disciplines == null) && ($editeur == null)) {$disciplines= $this->content->getDisciplines($typePub, 1);}
        // Récupération des SOUS-DISCIPLINES (pour les ouvrages uniquement)
        if(($disciplines != null) && ($sousdisciplines == null) && ($typepub == 3)) {
            // Si aucun éditeur n'est sélectionné, on récupère les sous-disciplines générales...
            if($editeur == null) {$sousdisciplines = $this->content->getSousDisciplines($discipline);}
            // ...sinon on récupère les sous-discplines réelles de l'éditeur selon la discipline sélectionnée
            else {$sousdisciplines = $this->content->getSousDisciplinesOfEditeur($editeur, $typepub, $discipline);}
        }

        // Si l'utilisateur est connecté, on vérifie si il a des abonnements actifs (#69456)
        $userNbreAbonnement = 0;
        if($this->authInfos["U"]) {
            $userNbreAbonnement = count($this->contentCom->getAchatsAbonnements($this->authInfos["U"]["ID_USER"]));
        }
        
        // Revues
        if($typepub == 1) {
            // L'utilisateur est connecté et/ou à des abonnements actifs
            if(($this->authInfos["I"]) || (($this->authInfos["U"]) && ($userNbreAbonnement != 0))) {
                // Calcul du nombre total
                $count  = $this->content->countTotalRevues($typepub, 1, $discipline, $editeur);

                // Récupération de la totalité des revues
                $revueFullList = $this->content->getRevuesByTitle($typepub, 1, 'ALL', $discipline, $editeur, true);

                // On doit ensuite placer les AUTRES revues en fin de tableau
                $revuesAbo      = array();
                $revuesAutres   = array();

                // Parcours de la liste
                foreach ($revueFullList as $revue) {
                    $access = Service::get('ControleAchat')->hasAccessToRevue($this->authInfos,$revue,1,1);
                    if ($access) {
                        $revue["REVUE_ABO"] = "1";
                        $revuesAbo[] = $revue;
                    }
                    else {
                        $revue["REVUE_ABO"] = "0";
                        $revuesAutres[] = $revue;
                    }
                }

                // ExtraValues
                $extraValues["TOTAL_REVUE_ABO"]     = count($revuesAbo);
                $extraValues["TOTAL_REVUE_AUTRES"]  = count($revuesAutres);

                // On assemble les deux tableaux
                $revues = array_merge($revuesAbo, $revuesAutres);

                // Gestion du multipage
                $revues = array_slice($revues, $nbreFrom, $nbreOffset);
            }            
            // Récupération des revues (standard)
            else {
                // Calcul du nombre total
                $count  = $this->content->countTotalRevues($typepub, 1, $discipline, $editeur);
                // Récupération des données
                $revues = $this->content->getRevuesByTitle($typepub, 1, 'ALL', $discipline, $editeur, true, $nbreFrom, $nbreOffset);
            }
        }
        // Ouvrages
        if($typepub == 3) {
            // Récupère la liste des sous-disciplines valides pour la discipline sélectionnée
            $listeValideSousDisciplines = $this->getValuesInArray("POS_DISC", $sousdisciplines);

            // Une sous-discipline est définie (et fait partie de la discipline), elle est utilisée comme référence pour rechercher l'ouvrage
            if(($sousdiscipline != null) && (in_array($sousdiscipline, $listeValideSousDisciplines))) { $discPos = $sousdiscipline; }
            // Sinon, on utilise la discipline définie
            else { $discPos = $discipline; }

            // L'utilisateur est connecté et/ou à des abonnements actifs
            if(($this->authInfos["I"]) || (($this->authInfos["U"]) && ($userNbreAbonnement != 0))) {
                // Calcul du nombre total
                //$count  = $this->content->countOuvrages($typepub, 1, $discPos, $editeur, $collection);

                // Récupération de la totalité des revues
                $revueFullList = $this->content->getOuvragesByTitle($typepub, '1', 'ALL', $discPos, $editeur, false, $collection);
                $count = count($revueFullList);

                // On doit ensuite placer les AUTRES revues en fin de tableau
                $revuesAbo      = array();
                $revuesAutres   = array();

                // Parcours de la liste
                foreach ($revueFullList as $revue) {
                    $access = Service::get('ControleAchat')->hasAccessToRevue($this->authInfos,$revue,1,1);
                    if ($access) {
                        $revue["REVUE_ABO"] = "1";
                        $revuesAbo[] = $revue;
                    }
                    else {
                        $revue["REVUE_ABO"] = "0";
                        $revuesAutres[] = $revue;
                    }
                }

                // ExtraValues
                $extraValues["TOTAL_REVUE_ABO"]     = count($revuesAbo);
                $extraValues["TOTAL_REVUE_AUTRES"]  = count($revuesAutres);

                // On assemble les deux tableaux
                $revues = array_merge($revuesAbo, $revuesAutres);

                // Gestion du multipage
                $revues = array_slice($revues, $nbreFrom, $nbreOffset);
            }            
            // Récupération des ouvrages (standard)
            else {
                // Calcul du nombre total
                $count  = $this->content->countOuvrages($typepub, 1, $discPos, $editeur, $collection);
                // Récupération des données
                $revues = $this->content->getOuvragesByTitle($typepub, '1', 'ALL', $discPos, $editeur, false, $collection, $nbreFrom, $nbreOffset);
            }
        }
        // Encyclopédies
        if($typepub == 6) {
            // Définition des deux collections (Repères et Que sais-je)
            $collections[] = array("ID_REVUE" => "QSJ", "TITRE" => "Que sais-je ?", );
            $collections[] = array("ID_REVUE" => "DEC_REP", "TITRE" => "Repères", );

            // Suppression des maisons d'édition
            $editeurs   = null;

            // L'utilisateur est connecté et/ou à des abonnements actifs
            if(($this->authInfos["I"]) || (($this->authInfos["U"]) && ($userNbreAbonnement != 0))) {
                // Calcul du nombre total
                //$count  = $this->content->countOuvrages($typepub, 1, $discipline, $editeur, $collection);

                // Récupération de la totalité des revues
                $revueFullList = $this->content->getOuvragesByTitle($typepub, '1', 'ALL', $discipline, $editeur, false, $collection);
                $count = count($revueFullList);

                // On doit ensuite placer les AUTRES revues en fin de tableau
                $revuesAbo      = array();
                $revuesAutres   = array();

                // Parcours de la liste
                foreach ($revueFullList as $revue) {
                    $access = Service::get('ControleAchat')->hasAccessToRevue($this->authInfos,$revue,1,1);
                    if ($access) {
                        $revue["REVUE_ABO"] = "1";
                        $revuesAbo[] = $revue;
                    }
                    else {
                        $revue["REVUE_ABO"] = "0";
                        $revuesAutres[] = $revue;
                    }
                }

                // ExtraValues
                $extraValues["TOTAL_REVUE_ABO"]     = count($revuesAbo);
                $extraValues["TOTAL_REVUE_AUTRES"]  = count($revuesAutres);

                // On assemble les deux tableaux
                $revues = array_merge($revuesAbo, $revuesAutres);

                // Gestion du multipage
                $revues = array_slice($revues, $nbreFrom, $nbreOffset);
            }
            // Récupération des encyclopédies (standard)
            else {
                // Calcul du nombre total
                $count  = $this->content->countOuvrages($typepub, 1, $discipline, $editeur, $collection);
                // Récupération des données
                $revues = $this->content->getOuvragesByTitle($typepub, '1', 'ALL', $discipline, $editeur, false, $collection, $nbreFrom, $nbreOffset);
            }

            // #96816 - On triche, on envoie une valeur pour $currentEditeur pour pouvoir afficher le menu collections
            $editeur = 1;
        }

        // Calcul du nombre de page
        //$nbrePage = ceil($count / $nbreOffset);        

        // Récupération des limites de chaque revue/ouvrage
        foreach($revues as $key => $revue) {
            $limites = $this->content->getMinMaxAnneeRevuesByIdOnCairn($revue["ID_REVUE"]);
            $revues[$key]["LIMITES"] = $limites;

            // Il existe une revue précédente
            if($revue['REVUE_PRECEDENTE'] != '') {
                // Récupération
                $anneesLimitesPrecedentes = $this->content->getMinMaxAnneeRevuesById($revue["REVUE_PRECEDENTE"]);
                // Réassignation
                if($anneesLimitesPrecedentes["MIN"] < $revues[$key]["LIMITES"]["MIN"]) {$revues[$key]["LIMITES"]["MIN"] = $anneesLimitesPrecedentes["MIN"];}
            }
        }

        // Webtrends
        $headers = Service::get('Webtrends')->webtrendsHeaders('liste-'.$type, $this->authInfos);

        // Appel à la vue
        $this->genererVue(array('editeurs' => $editeurs, 'editeursWR' => $editeursWR, 'disciplines' => $disciplines, 'sousdisciplines' => $sousdisciplines, 'revues' => $revues, 'collections' => $collections, 'currentEditeur' => $editeur, 'currentDiscipline' => $discipline, 'currentSousDiscipline' => $sousdiscipline, 'currentCollection' => $collection, 'type' => $type, 'total' => $count, 'nbreParPage' => $nbreOffset, 'extraValues' => $extraValues), 'liste.php', null, $headers);
    }

    private function listeDetail($type) {
        $webtrendsService = Service::get('Webtrends');
        $webtrendsTags = $webtrendsService->getTagsForAllPages(
            $type !== 'collection' ? 'liste-'.$type : 'collection',
            $this->authInfos
        );
        //on récupère les données et on paramètre la vue et ses blocs
        switch ($type) {
            case 'auteur':
                $id = $this->requete->getParametre("ID");
                $nom = $this->requete->getParametre("NOM");
                $auteur = $this->content->getAuteurById($id);
                $ouvrages = $this->content->getAuteurOuvrages($id);
                $contribsOuvrage = $this->content->getAuteurArticles($id, '3,6', TRUE);
                $typesAchat = array("MODE" => Service::get('ControleAchat')->getModeAchat($this->authInfos));
                Service::get('ContentArticle')
                        ->setTypesAchat($typesAchat)
                        ->readContentArticles($contribsOuvrage, '', $this->authInfos);
                $articlesRev = $this->content->getAuteurArticles($id, 1);
                $modeBoutons = Configuration::get('modeBoutons');
                if($modeBoutons == 'cairninter'){
                    Service::get('ContentArticle')
                            ->setTypesAchat($typesAchat)
                            ->readButtonsForInter($articlesRev, $this->authInfos);
                }else{
                    Service::get('ContentArticle')
                            ->setTypesAchat($typesAchat)
                            ->readContentArticles($articlesRev, 'revue', $this->authInfos);
                }
                $articlesMag = $this->content->getAuteurArticles($id, 2);
                Service::get('ContentArticle')
                        ->setTypesAchat($typesAchat)
                        ->readContentArticles($articlesMag, 'magazine', $this->authInfos);
                $webtrendsTags = array_merge(
                    $webtrendsTags,
                    $webtrendsService->getTagsForAuteurPublications($auteur)
                );
                $webtrendsTags['numero-auteurs'] = $auteur['AUTEUR_PRENOM'] . ' ' . $auteur['AUTEUR_NOM']; //Ajout de l'auteur (Dimitry : Cairn, le 30/11/2015).

                // Les auteurs ne sont pas récupéré, on doit donc parcourir chaque tableau et ajouter les auteurs contributeurs SANS l'auteur courant
                // OUVRAGES
                foreach($ouvrages as $key => $ouvrage) {
                    // Récupération des auteurs, sans l'auteur courant
                    $auteurs = $this->content->getAuteurFromReference($ouvrage["NUMERO_ID_NUMPUBLIE"], array('type' => 'numero', 'notThisAuthor' => $id, 'noAttribute' => 1));
                    // Ajout des auteurs au tableau
                    $ouvrages[$key]["AUTEURS"] = $auteurs["ARTICLE_AUTEUR"];
                }
                // CONTRIBUTIONS D'OUVRAGES
                foreach($contribsOuvrage as $key => $contribOuvrage) {
                    // Récupération des auteurs, sans l'auteur courant
                    $auteurs = $this->content->getAuteurFromReference($contribOuvrage["ARTICLE_ID_ARTICLE"], array('type' => 'article', 'notThisAuthor' => $id));
                    // Ajout des auteurs au tableau
                    $contribsOuvrage[$key]["AUTEURS"] = $auteurs["ARTICLE_AUTEUR"];
                }
                // ARTICLES REVUES
                foreach($articlesRev as $key => $articleRev) {
                    // Récupération des auteurs, sans l'auteur courant
                    $auteurs = $this->content->getAuteurFromReference($articleRev["ARTICLE_ID_ARTICLE"], array('type' => 'article', 'notThisAuthor' => $id));
                    // Ajout des auteurs au tableau
                    $articlesRev[$key]["AUTEURS"] = $auteurs["ARTICLE_AUTEUR"];
                }
                // ARTICLES MAGAZINES
                foreach($articlesMag as $key => $articleMag) {
                    // Récupération des auteurs, sans l'auteur courant
                    $auteurs = $this->content->getAuteurFromReference($articleMag["ARTICLE_ID_ARTICLE"], array('type' => 'article', 'notThisAuthor' => $id));
                    // Ajout des auteurs au tableau
                    $articlesMag[$key]["AUTEURS"] = $auteurs["ARTICLE_AUTEUR"];
                }

                // Récupération de la traduction (si elle existe)
                // Ajout des données récupérées dans le tableau
                // Version normale de cairn
                // Attention, l'ID de l'auteur sur Cairn et Cairn-Int n'est pas nécessairement le même !
                if(Configuration::get('mode') == 'normal') {
                    require_once 'Modele/ManagerIntPub.php';
                    $managerIntPub                  = new ManagerIntPub('dsn_int_pub');
                    //$idAuteur                     = $id; // ex.: $id = 2;
                    $auteur_ouvrages                = $managerIntPub->getListOuvragesAuteurOnCairnInt($auteur['AUTEUR_NOM'], $auteur['AUTEUR_PRENOM']);  // On utilise des valeurs pour identifier l'auteur
                    $auteur_articles                = $managerIntPub->getListArticlesAuteurOnCairnInt($auteur['AUTEUR_NOM'], $auteur['AUTEUR_PRENOM']);  // On utilise des valeurs pour identifier l'auteur
                    $auteur["LISTE_OUVRAGES"]       = $auteur_ouvrages;
                    $auteur["LISTE_ARTICLES"]       = $auteur_articles;
                    //var_dump($contributions);
                }

                $headers = $webtrendsService->webtrendsTagsToHeadersTags($webtrendsTags);
                $this->genererVue(array('auteur' => $auteur,
                    'ouvrages' => $ouvrages,
                    'contribs' => $contribsOuvrage,
                    'articlesRev' => $articlesRev,
                    'articlesMag' => $articlesMag,
                        ), 'auteur.php', null, $headers);
                break;
            case 'editeur':
                $id = $this->requete->getParametre("ID_EDITEUR");
                $editeur = $this->content->getEditeurById($id);
                $revues = $this->content->getRevuesByTitle(1, 1, '', null, $id, true);
                $countRev = count($revues);
                $colls = $this->content->getRevuesByTitle(3, 1, '', null, $id, true);
                $countColls = count($colls);
                $encycs = $this->content->getRevuesByTitle(6, 1, '', null, $id, true);
                $countEncycs = count($encycs);
                $mags = $this->content->getRevuesByTitle(2, 1, '', null, $id, true);
                $countMags = count($mags);
                // Récupération des limites de chaque revue/ouvrage
                foreach($revues as $key => $revue) {
                    $limites = $this->content->getMinMaxAnneeRevuesByIdOnCairn($revue["ID_REVUE"]);
                    $revues[$key]["LIMITES"] = $limites;
                }
                $webtrendsTags = array_merge(
                    $webtrendsTags,
                    $webtrendsService->getTagsForEditeurPublications($editeur)
                );
                $headers = $webtrendsService->webtrendsTagsToHeadersTags($webtrendsTags);
                $this->genererVue(array('editeur' => $editeur,
                    'countRev' => $countRev, 'revues' => $revues,
                    'countColls' => $countColls, 'colls' => $colls,
                    'countEncycs' => $countEncycs, 'encycs' => $encycs,
                    'countMags' => $countMags, 'mags' => $mags,
                        ), 'editeur.php', null, $headers);
                break;
            case 'collection':
                if ($this->requete->existeParametre("ID_REVUE")) {
                    $revueId = $this->requete->getParametre("ID_REVUE");
                    $revue = $this->content->getRevuesById($revueId);
                    $revueFilter = $revue[0]["URL_REWRITING"];
                } else {
                    $revueFilter = $this->requete->getParametre("REVUE");
                }
                $revues = $this->content->getRevuesByUrl($revueFilter, null, '3,6');
                $limit = $this->requete->existeParametre("LIMIT") ? $this->requete->getParametre("LIMIT") : 0;
                $numeros = $this->content->getNumeroRevuesById($revues[0]["ID_REVUE"], null, $limit, 20);
                $countNum = $this->content->countNumeroRevuesById($revues[0]["ID_REVUE"]);
                $webtrendsTags = array_merge(
                    $webtrendsTags,
                    $webtrendsService->getTagsForCollection($revues[0])
                );
                $headers = $webtrendsService->webtrendsTagsToHeadersTags($webtrendsTags);
                $this->genererVue(array('revue' => $revues[0],
                    'numeros' => $numeros,
                    'limit' => $limit,
                    'countNum' => $countNum
                        ), 'collection.php', null, $headers);
                break;
            case 'citepar':
                /* Redmine 41866 - on montre les 2 en même temps...
                switch ($this->requete->getParametre("T")) {
                    case 'O':
                        $type = 'in (3,6)';
                        break;
                    default:
                        $type = ' = 1';
                }*/
                if($this->requete->existeParametre("ID_ARTICLE")){
                    $id = $this->requete->getParametre("ID_ARTICLE");
                    $article = $this->content->getArticleFromId($id);
                    $referencedByR = $this->content->getReferencedBy($id, 'B', ' = 1');
                    $referencedByM = $this->content->getReferencedBy($id, 'B', ' = 2');
                    $referencedByO = $this->content->getReferencedBy($id, 'B', 'in (3,6)');
                    $auteurs = $this->content->getAuteursForReference($id);
                    $revue = $this->content->getRevuesById($article["ARTICLE_ID_REVUE"]);
                    $numero = $this->content->getNumpublieById($article["ARTICLE_ID_NUMPUBLIE"]);
                }else{
                    $id = $this->requete->getParametre("ID_NUMPUBLIE");
                    $referencedByR = $this->content->getNumReferencedBy($id, 'B', ' = 1');
                    $referencedByM = $this->content->getNumReferencedBy($id, 'B', ' = 2');
                    $referencedByO = $this->content->getNumReferencedBy($id, 'B', 'in (3,6)');
                    $numero = $this->content->getNumpublieById($id);
                    $revue = $this->content->getRevuesById($numero[0]["NUMERO_ID_REVUE"]);
                    $article = array();
                }
                $webtrendsTags = array_merge(
                    $webtrendsTags,
                    $webtrendsService->getTagsForCitePar($article)
                );
                $headers = $webtrendsService->webtrendsTagsToHeadersTags($webtrendsTags);

                $this->genererVue(array('article' => $article,
                    'revue' => $revue[0],
                    'numero' => $numero[0],
                    'referencedByR' => $referencedByR,
                    'referencedByM' => $referencedByM,
                    'referencedByO' => $referencedByO,
                    'auteurs' => $auteurs,
                    'type' => $type
                        ), 'cite-par.php', null, $headers);
                break;
        }

        //appel à la vue
    }

    public function setAlertes(){
        $id_user = $this->requete->getParametre("ID_USER");
        $id_alerte = $this->requete->getParametre("ID_ALERTE");
        $type = $this->requete->getParametre("TYPE");

        $this->content->addAlerts($id_user,$id_alerte,$type);
    }

}
