<?php
    /*  /!\ Nécessite les fonctions présentes dans citations-tools.php  */

    /************************************************************************************************************************************************************************************************************************** 
    Définition des schémas des citations : #91883
    MONOGRAPHIE
        OUVRAGE     : [AUTEURS]. [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [EDITEUR], [ANNEE].
        CHAPITRE    : [AUTEURS]. [« TITRE CHAPITRE »]. [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [EDITEUR], [ANNEE], pp. [PAGE DE DEBUT]-[PAGE DE FIN].

    OUVRAGES
        OUVRAGE     : [AUTEURS]. [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [EDITEUR], [ANNEE].
        CHAPITRE    : [AUTEURS]. [« TITRE CHAPITRE »]. [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [EDITEUR], [ANNEE], pp. [PAGE DE DEBUT]-[PAGE DE FIN].
     
    REVUES
        NUMERO      : pas de citation
        ARTICLE     : [AUTEURS]. [« TITRE CHAPITRE »], [TITRE DE LA REVUE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [VOLUME], [NUMERO], [ANNEE], pp. [PAGE DE DEBUT]-[PAGE DE FIN].
     
    Remarques :
    Les schémas des ouvrages (mono & ouvrage) et les schémas des chapitres d'ouvrage sont identiques, le schéma des articles est unique. Il n'y a donc "que" 3 schémas différents.
    **************************************************************************************************************************************************************************************************************************/

    // ARTICLES
    if(isset($currentArticle)) {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeursMLA($currentArticle['ARTICLE_AUTEUR']);

        // Init
        $citation       = "";
        $auteurs        = $auteurs_et_contributeurs["AUTEURS"];
        $contributeurs  = $auteurs_et_contributeurs["CONTRIBUTEURS"];

        // Récupération et formatage des données
        //$article_titre  = trim($currentArticle["ARTICLE_TITRE"]);
        //$article_stitre = trim($currentArticle["ARTICLE_SOUSTITRE"]);
        $article_titre  = trim($numero["META_ARTICLE_CAIRN"]["TITRE"]);
        $article_stitre = trim($numero["META_ARTICLE_CAIRN"]["SOUSTITRE"]);
        $revue_titre    = trim($revue["REVUE_TITRE"]);
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

        // PAS D'OUVRAGE, donc pas de CHAPITRE sur CAIRN-INT (au besoin, juste C/C cette partie de citation-format-mla de cairn.info (FR))
        // REVUE
        if($typePub == "revue" || $typePub == "magazine") {

            // ARTICLES
            // [AUTEURS]. [« TITRE CHAPITRE »], [TITRE DE LA REVUE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [VOLUME], [NUMERO], [ANNEE], pp. [PAGE DE DEBUT]-[PAGE DE FIN].
            if($auteurs != "")          {
                                        $citation .= removePonctuation(trim($auteurs)).". ";                        // Affichage des auteurs si ils existent, on supprime la virgule et on remplace par un point.
                                        }
            if($article_titre != "")    {
                                        if($article_stitre == "") {$citation .= "«&nbsp;".$article_titre."&nbsp;», ";}// Affichage du titre de l'article/chapitre entre guillemets
                                        else {$citation .= "«&nbsp;".formatTitre($article_titre)." ".$article_stitre."&nbsp;», ";} 
                                        }
            if($revue_titre != "")      {
                                        $citation .= "<i>".removePonctuation($revue_titre)."</i>, ";                // Affichage du titre de la revue (en italique)
                                        }
            if($numero_volume != "")    {
                                        $citation .= "vol. ".str_replace("n°", "", strtolower($numero_volume)).", ";             // Affichage du numéro du volume (en supprimant le préfixe, si possible)
                                        }
            if($numero_numero != "")    {
                                        $citation .= "no. ".$numero_numero.", ";                                     // Affichage du numéro du numéro...
                                        }
            if($numero_annee != "")     {
                                        $citation .= $numero_annee.", ";                                            // Affichage du de l'année
                                        }
            
            if($page_debut != "")       {
                                        $citation .= "pp. ".$page_debut."-".$page_fin.". ";                         // Nombre de page
                                        }
        }
    }
    // PAS D'OUVRAGE sur CAIRN-INT (au besoin, juste C/C cette partie de citation-format-mla de cairn.info (FR))

    echo $citation;
?>
