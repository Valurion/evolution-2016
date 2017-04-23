<?php
    /*  /!\ Nécessite les fonctions présentes dans citations-tools.php  */

    /************************************************************************************************************************************************************************************************************************** 
    Définition des schémas des citations : #91883
    MONOGRAPHIE
        OUVRAGE     : [AUTEURS]. ([ANNEE]). [TITRE OUVRAGE]: [SOUS-TITRE OUVRAGE] ([VOLUME]). [LIEU]: [EDITEUR]. [DOI]
        CHAPITRE    : [AUTEURS]. ([ANNEE]). [TITRE CHAPITRE]. In [CONTRIBUTEURS], [TITRE OUVRAGE]: [SOUS-TITRE OUVRAGE] (pp. [PAGE DE DEBUT]-[PAGE DE FIN]). [LIEU]: [EDITEUR]. [DOI]

    OUVRAGES
        OUVRAGE     : [AUTEURS], [CONTRIBUTEURS]. ([ANNEE]). [TITRE OUVRAGE]: [SOUS-TITRE OUVRAGE]. [LIEU]: [EDITEUR]. [DOI]
        CHAPITRE    : [AUTEURS]. ([ANNEE]). [TITRE CHAPITRE]. In [CONTRIBUTEURS], [TITRE OUVRAGE]: [SOUS-TITRE OUVRAGE] (pp. [PAGE DE DEBUT]-[PAGE DE FIN]). [LIEU]: [EDITEUR]. [DOI]
     
    REVUES
        NUMERO      : pas de citation
        ARTICLE     : [AUTEURS]. ([ANNEE]). [TITRE ARTICLE]. [TITRE DE LA REVUE], [VOLUME]([NUMERO]), [PAGE DE DEBUT]-[PAGE DE FIN]. [DOI]
     
    Remarques :
    Les schémas es schémas des chapitres d'ouvrage sont identiques, les schémas des ouvrages sont légèrement différents et le schéma des articles est unique. Il n'y a donc 4 schémas différents.
    **************************************************************************************************************************************************************************************************************************/

    // Valeurs générales
    $villesConnues  = array("Paris", "Lyon", "Marseille", "Bruxelles");

    // CHAPITRES et ARTICLES
    if(isset($currentArticle)) {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeursAPA($currentArticle['ARTICLE_AUTEUR']);

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
        $numero_pays    = trim($currentArticle["EDITEUR_PAYS"]);
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

        // REVUE
        if($typePub == "revue" || $typePub == "magazine") {

            // ARTICLES
            // [AUTEURS]. ([ANNEE]). [TITRE ARTICLE]. [TITRE DE LA REVUE], [VOLUME]([NUMERO]), [PAGE DE DEBUT]-[PAGE DE FIN]. [DOI].
            if($auteurs != "")          {
                                        $citation .= removePonctuation(trim($auteurs))." ";                         // Affichage des auteurs si ils existent, on supprime la virgule et on remplace par un point.
                                        }
            if($numero_annee != "")     {
                                        $citation .= "(".$numero_annee."). ";                                       // Année
                                        }
            if($article_titre != "")    {
                                        if($article_stitre == "") {$citation .= $article_titre.". ";}               // Affichage du titre de l'article/chapitre entre guillemets
                                        else {$citation .= removePonctuation($article_titre).": ".$article_stitre.". ";} 
                                        }
            if($revue_titre != "")      {
                                        $citation .= "<i>".removePonctuation($revue_titre)."</i>, ";                // Affichage du titre de la revue (en italique)
                                        }
            if($numero_volume != "")    {
                                        $citation .= str_replace("n°", "", strtolower($numero_volume));             // Affichage du numéro du volume (en supprimant le préfixe, si possible)
                                        if($numero_numero != "") {$citation .= ",(".$numero_numero.")";}
                                        }
            
            if($page_debut != "")       {
                                        $citation .= ", ".$page_debut."-".$page_fin.". ";                           // Nombre de page
                                        }
            if($doi != "")              {
                                        $citation .= "doi:".$doi.". ";                                              // DOI
                                        }
            if($url != "" && 
                        $doi == "")     {
                                        $citation .= "".$url.". ";                                                  // URL
                                        }
        }
    }
    // PAS D'OUVRAGE sur CAIRN-INT (au besoin, juste C/C cette partie de citation-format-iso-690 de cairn.info (FR))

    echo $citation;
?>
