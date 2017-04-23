<?php
    /*  /!\ Nécessite les fonctions présentes dans citations-tools.php  */

    /************************************************************************************************************************************************************************************************************************** 
    Définition des schémas des citations : #91816
    MONOGRAPHIE
        OUVRAGE     : [AUTEURS], [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [LIEU][, EDITEUR], [« COLLECTION »], [ANNEE], [NBRE_PAGE] pages. ISBN : [ISBN]. DOI : [DOI]. URL : [URL]
        CHAPITRE    : [AUTEURS], [« TITRE CHAPITRE »], dans [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [LIEU][, EDITEUR], [« COLLECTION »], [ANNEE], p. [PAGE DE DEBUT]-[PAGE DE FIN]. URL : [URL]
     
    OUVRAGES
        OUVRAGE     : [AUTEURS], [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [LIEU][, EDITEUR], [« COLLECTION »], [ANNEE], [NBRE_PAGE] pages. ISBN : [ISBN]. DOI : [DOI]. URL : [URL]
        CHAPITRE    : [AUTEURS], [« TITRE CHAPITRE »], dans [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [LIEU][, EDITEUR], [« COLLECTION »], [ANNEE], p. [PAGE DE DEBUT]-[PAGE DE FIN]. DOI : [DOI]. URL : [URL]
     
    REVUES
        NUMERO      : pas de citation
        ARTICLE     : [AUTEURS]. [« TITRE CHAPITRE »], [TITRE DE LA REVUE], [[ANNEE]/[VOLUME]] [NUMERO], p. [PAGE DE DEBUT]-[PAGE DE FIN]. DOI : [DOI]. URL : [URL]
     
    Remarques :
    Les schémas des ouvrages (mono & ouvrage) sont identiques, les schémas des chapitres d'ouvrage sont pratiquement identique (le DOI est en plus pour un chapitre d'ouvrage),
    et le schéma des articles est unique. Il n'y a donc "que" 3 schémas différents.
    **************************************************************************************************************************************************************************************************************************/


    // ARTICLES
    if(isset($currentArticle)) {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeurs($currentArticle['ARTICLE_AUTEUR']);

        // Init
        $citation       = "";
        $auteurs        = $auteurs_et_contributeurs["AUTEURS"];
        $contributeurs  = $auteurs_et_contributeurs["CONTRIBUTEURS"];

        // Récupération et formatage des données
        $article_id     = trim($currentArticle["ARTICLE_ID_ARTICLE"]);
        //$article_titre  = trim($currentArticle["ARTICLE_TITRE"]);
        //$article_stitre = trim($currentArticle["ARTICLE_SOUSTITRE"]);
        $article_titre  = trim($numero["META_ARTICLE_CAIRN"]["TITRE"]);
        $article_stitre = trim($numero["META_ARTICLE_CAIRN"]["SOUSTITRE"]);
        $revue_titre    = trim($revue["REVUE_TITRE"]);
        $revue_url      = trim($revue["REVUE_URL_REWRITING"]);
        $revue_annee    = trim($revue["NUMERO_ANNEE"]);
        $revue_numero   = trim($revue["NUMERO_NUMERO"]);
        $numero_numero  = trim($numero["NUMERO_NUMERO"]);
        $numero_annee   = trim($numero["NUMERO_ANNEE"]);
        $numero_volume  = trim($numero["NUMERO_VOLUME"]);
        $numero_titre   = trim($numero["NUMERO_TITRE"]);
        $numero_stitre  = trim($numero["NUMERO_SOUS_TITRE"]);
        $numero_auteurs = trim($numero["NUMERO_AUTEUR"]);
        $numero_ville   = trim($currentArticle["EDITEUR_VILLE"]);
        $numero_editeur = trim($currentArticle["EDITEUR_NOM_EDITEUR"]);
        $page_debut     = trim($currentArticle["ARTICLE_PAGE_DEBUT"]);
        $page_fin       = trim($currentArticle["ARTICLE_PAGE_FIN"]);
        $doi            = trim($currentArticle["ARTICLE_DOI"]);

        // Définition de l'URL
        // Texte EN
        if ($currentArticle['ARTICLE_LANGUE'] == 'en') {
            $url = Service::get('ParseDatas')->getCrossDomainUrl()."/".$typePub."-".$revue_url."-".$revue_annee."-".$revue_numero."-page-".$page_debut.".htm";
        }        
        // Texte FR
        else {
            $url = Service::get('ParseDatas')->getCrossDomainUrl()."/".$typePub."-".$revue_url."-".$revue_annee."-".$revue_numero."-page-".$metaArticle["PAGE_DEBUT"].".htm";
        }

        // PAS D'OUVRAGE, donc pas de CHAPITRE sur CAIRN-INT (au besoin, juste C/C cette partie de citation-format-iso-690 de cairn.info (FR))
        // REVUE
        if($typePub == "revue" || $typePub == "magazine") {

            // ARTICLES
            // [AUTEURS]. [« TITRE CHAPITRE »], [TITRE DE LA REVUE], [[ANNEE]/[VOLUME]] [NUMERO], p. [PAGE DE DEBUT]-[PAGE DE FIN]. DOI : [DOI]. URL : [URL]
            if($auteurs != "")          {
                                        $citation .= $auteurs;                                                      // Affichage des auteurs si ils existent
                                        }
            if($article_titre != "")    {
                                        if($article_stitre == "") {$citation .= "«&nbsp;".$article_titre."&nbsp;», ";}// Affichage du titre de l'article/chapitre entre guillemets
                                        else {$citation .= "«&nbsp;".formatTitre($article_titre)." ".$article_stitre."&nbsp;», ";} 
                                        }
            if($revue_titre != "")      {
                                        $citation .= "<i>".removePonctuation($revue_titre)."</i>, ";                // Affichage du titre de la revue (en italique)
                                        }
            if($numero_annee != "" && $numero_volume != "")   {
                                        $citation .= $numero_annee."/".$numero_numero;                              // Affichage du de l'année et du volume
                                        if($numero_volume == "") {$citation .= ", ";}                               // On ajoute une virgule si il n'y pas de volume ensuite...
                                        else {$citation .= " ";}                                                    // ...sinon on ajoute un espace
                                        }
            if($numero_volume != "")    {
                                        $citation .= "(".$numero_volume."), ";                                      // Affichage du numéro du volume
                                        }
            if($page_debut != "")       {
                                        $citation .= "p. ".$page_debut."-".$page_fin.". ";                          // Nombre de page
                                        }
            if($doi != "")              {
                                        $citation .= "DOI : ".$doi.". ";                                            // DOI
                                        }
            if($url != "")              {
                                        $citation .= "URL : ".$url." ";                                             // URL
                                        }
        }
    }
    // PAS D'OUVRAGE sur CAIRN-INT (au besoin, juste C/C cette partie de citation-format-iso-690 de cairn.info (FR))

    echo $citation;
?>
