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

    // CHAPITRES et ARTICLES
    if(isset($currentArticle)) {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeursMLA($currentArticle['ARTICLE_AUTEUR']);

        // Init
        $citation       = "";
        $auteurs        = $auteurs_et_contributeurs["AUTEURS"];
        $contributeurs  = $auteurs_et_contributeurs["CONTRIBUTEURS"];

        // Récupération et formatage des données
        $article_titre  = trim($currentArticle["ARTICLE_TITRE"]);
        $article_stitre = trim($currentArticle["ARTICLE_SOUSTITRE"]);
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
        $url = "http://www.cairn.info/".$numero_url."-page-".$page_debut.".htm";

        // MONOGRAPHIE ou OUVRAGE
        if ($typePub == "ouvrage" || $typePub == "encyclopedie" || $typePub == "encyclopédie") {
            // Récupération des contributeurs pour les articles
            // Souvent, les contributeurs des articles ne sont pas enregistrés, on doit donc récupérer les contributeurs du numéro
            $numero_auteurs = $numero["NUMERO_AUTEUR"];   
            // Formatage de la liste des auteurs (array to raw)         
            $numero_auteurs = formatNumeroAuteurs($numero_auteurs);
            // Formatage en deux tableaux (AUTEURS & CONTRIBUTEURS)
            $numero_auteurs = formatAuteurEtContributeursMLA($numero_auteurs);
            // Pour les chapitres d'ouvrage, on récupère l'auteur de l'ouvrage et on le "transforme" en contributeur du chapitre
            // Uniquement SI il n'y a pas de contributeurs
            if(($numero_auteurs["CONTRIBUTEURS"] == "") && ($numero_auteurs["AUTEURS"] != "") ) {
                // Les auteurs de l'ouvrage deviennent des contributeurs
                $numero_auteurs = formatAuteursToContributeur($numero["NUMERO_AUTEUR"], "sous la direction de");
                // On reforme le tableau des AUTEURS & CONTRIBUTEURS sur la nouvelle base
                $numero_auteurs = formatAuteurEtContributeursMLA($numero_auteurs);
            }
            // Récupération uniquement des contributeurs
            $contributeurs  = $numero_auteurs["CONTRIBUTEURS"];

            //if(($auteurs != "") && (count($numero_auteurs["CONTRIBUTEURS"]) == 0) && (count($numero_auteurs["AUTEURS"]) != 0) ) {$numero_auteurs["CONTRIBUTEURS"] = $numero_auteurs["AUTEURS"];}

            // CHAPITRE
            // [AUTEURS]. [« TITRE CHAPITRE »]. [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [EDITEUR], [ANNEE], pp. [PAGE DE DEBUT]-[PAGE DE FIN].
            if($auteurs != "")          {
                                        $citation .= removePonctuation(trim($auteurs)).". ";                          // Affichage des auteurs si ils existent, on supprime la virgule et on remplace par un point.
                                        }
            if($article_titre != "")    {
                                        if($article_stitre == "") {$citation .= "«&nbsp;".$article_titre."&nbsp;», ";}// Affichage du titre de l'article/chapitre entre guillemets
                                        else {$citation .= "«&nbsp;".formatTitre($article_titre)." ".$article_stitre."&nbsp;», ";} 
                                        }
            if($numero_titre != "")     {
                                        $citation .= "<i>".formatTitre($numero_titre)."</i>";                        // Affichage du titre de l'ouvrage (en italique)
                                        }
            if($numero_stitre != "")    {
                                        if($contributeurs != "") {$citation .= "<i>".removePonctuation($numero_stitre)."</i>";}  // On enlève la ponctuation si il y a des contributeurs (car contributeur commence par une virgule)
                                        else {$citation .= "<i>".formatTitre($numero_stitre)."</i>";}                // Sinon, on marque la ponctuation
                                        }
            if($contributeurs != "")    {
                                        if($numero_stitre != "") {$citation .= ", ";}                                // Si il y a un sous-titre, on commence avec une virgule
                                        $citation .= removePonctuation(trim($contributeurs)).". ";                                            // Dans tous les cas, on fini par un point
                                        }
            if($numero_editeur != "")   {
                                        $citation .= $numero_editeur.", ";                                           // Dans tous les cas, on fini par une virgule
                                        }
            if($numero_annee != "")     {
                                        $citation .= $numero_annee.", ";                                             // Année
                                        }
            if($page_debut != "")       {
                                        $citation .= "pp. ".$page_debut."-".$page_fin.". ";                          // Nombre de page
                                        }
        }
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
    // OUVRAGES / NUMERO
    else {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeursMLA($numero['NUMERO_AUTEUR']);

        // Init
        $citation       = "";
        $auteurs        = $auteurs_et_contributeurs["AUTEURS"];
        $contributeurs  = $auteurs_et_contributeurs["CONTRIBUTEURS"];

        // Récupération et formatage des données
        $revue_titre    = trim($revue["REVUE_TITRE"]);
        $numero_numero  = trim($numero["NUMERO_NUMERO"]);
        $numero_annee   = trim($numero["NUMERO_ANNEE"]);
        $numero_volume  = trim($numero["NUMERO_VOLUME"]);
        $numero_titre   = trim($numero["NUMERO_TITRE"]);
        $numero_stitre  = trim($numero["NUMERO_SOUS_TITRE"]);
        $numero_editeur = trim($revue["EDITEUR_NOM_EDITEUR"]);
        $numero_nbre_pages = trim($numero["NUMERO_NB_PAGE"]);
        $numero_isbn    = trim($numero["NUMERO_ISBN"]);
        $numero_doi     = trim($numero["NUMERO_DOI"]);
        $numero_url     = trim($numero["NUMERO_URL_REWRITING"]);

        // Définition de l'URL
        $url = "http://www.cairn.info/".$numero_url."--".$numero_isbn.".htm";

        // MONOGRAPHIE ou OUVRAGE
        if ($typePub == "ouvrage" || $typePub == "encyclopedie" || $typePub == "encyclopédie") {

            // OUVRAGES
            // [AUTEURS]. [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [EDITEUR], [ANNEE].
            if($auteurs != "")          {
                                        $citation .= removePonctuation(trim($auteurs)).". ";                        // Affichage des auteurs si ils existent, on supprime la virgule et on remplace par un point.
                                        }
            if($numero_titre != "")     {
                                        $citation .= "<i>".formatTitre($numero_titre)."</i>";                       // Affichage du titre de l'ouvrage (en italique)
                                        }
            if($numero_stitre != "")    {
                                        if($contributeurs != "") {$citation .= "<i>".removePonctuation($numero_stitre)."</i>"; }  // On enlève la ponctuation si il y a des contributeurs (car contributeur commence par une virgule)
                                        else {$citation .= "<i>".formatTitre($numero_stitre)."</i>"; }                            // Sinon, on marque la ponctuation
                                        }
            if($contributeurs != "")    {
                                        if($numero_stitre != "") {$citation .= ", ";}                               // Si il y a un sous-titre, on commence avec une virgule
                                        $citation .= removePonctuation(trim($contributeurs)).". ";                  // Dans tous les cas, on fini par un point
                                        }
            if($numero_editeur != "")   {
                                        $citation .= $numero_editeur.", ";                                          // Dans tous les cas, on fini par une virgule
                                        }
            if($numero_annee != "")     {
                                        $citation .= $numero_annee."";                                              // Année
                                        }
        }
    }

    echo $citation;
?>
