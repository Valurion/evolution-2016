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

    
    // CHAPITRES et ARTICLES
    if(isset($currentArticle)) {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeurs($currentArticle['ARTICLE_AUTEUR']);

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
            $numero_auteurs = formatAuteurEtContributeurs($numero_auteurs);
            // Pour les chapitres d'ouvrage, on récupère l'auteur de l'ouvrage et on le "transforme" en contributeur du chapitre
            // Uniquement SI il n'y a pas de contributeurs
            if(($numero_auteurs["CONTRIBUTEURS"] == "") && ($numero_auteurs["AUTEURS"] != "") ) {
                // Les auteurs de l'ouvrage deviennent des contributeurs
                $numero_auteurs = formatAuteursToContributeur($numero["NUMERO_AUTEUR"], "sous la direction de");
                // On reforme le tableau des AUTEURS & CONTRIBUTEURS sur la nouvelle base
                $numero_auteurs = formatAuteurEtContributeurs($numero_auteurs);
            }
            // Récupération uniquement des contributeurs
            $contributeurs  = $numero_auteurs["CONTRIBUTEURS"];

            // CHAPITRE
            // [AUTEURS], [« TITRE CHAPITRE »], dans [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [LIEU][, EDITEUR], [« COLLECTION »], [ANNEE], p. [PAGE DE DEBUT]-[PAGE DE FIN]. DOI : [DOI]. URL : [URL]
            if($auteurs != "")          {
                                        $citation .= $auteurs;                                                      // Affichage des auteurs si ils existent
                                        }
            if($article_titre != "")    {
                                        if($article_stitre == "") {$citation .= "«&nbsp;".$article_titre."&nbsp;», ";}// Affichage du titre de l'article/chapitre entre guillemets
                                        else {$citation .= "«&nbsp;".formatTitre($article_titre)." ".$article_stitre."&nbsp;», ";} 
                                        }
            if($numero_titre != "")     {
                                        $citation .= "dans <i>".formatTitre($numero_titre)."</i>";                  // Affichage du titre de l'ouvrage (en italique)
                                        }
            if($numero_stitre != "")    {
                                        if($contributeurs != "") {$citation .= "<i>".removePonctuation($numero_stitre)."</i>";}  // On enlève la ponctuation si il y a des contributeurs (car contributeur commence par une virgule)
                                        else {$citation .= "<i>".formatTitre($numero_stitre)."</i>";}               // Sinon, on marque la ponctuation
                                        }
            if($contributeurs != "")    {
                                        if($numero_stitre != "") {$citation .= ", ";}                               // Si il y a un sous-titre, on commence avec une virgule
                                        $citation .= removePonctuation(trim($contributeurs)).". ";                                           // Dans tous les cas, on fini par un point
                                        }
            if($numero_ville != "")     {
                                        $citation .= $numero_ville;                                                 // Affichage de la ville
                                        if($numero_editeur == "") {$citation .= ", ";}                              // Si il n'y a pas de nom d'éditeur, on ajoute une virgule
                                        }
            if($numero_editeur != "")   {
                                        if($numero_ville != "") {$citation .= ", ";}                                // Si il y a une ville, on commence avec une virgule
                                        $citation .= $numero_editeur.", ";                                          // Dans tous les cas, on fini par une virgule
                                        }
            if($revue_titre != "")      {
                                        $citation .= "«&nbsp;".$revue_titre."&nbsp;», ";                            // Collection
                                        }
            if($numero_annee != "")     {
                                        $citation .= $numero_annee.", ";                                            // Année
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
    // OUVRAGES / NUMERO
    else {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeurs($numero['NUMERO_AUTEUR']);

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
            // [AUTEURS], [TITRE OUVRAGE]. [SOUS-TITRE OUVRAGE][, CONTRIBUTEURS]. [LIEU][, EDITEUR], [« COLLECTION »], [ANNEE], [NBRE_PAGE] pages. ISBN : [ISBN]. DOI : [DOI]. URL : [URL]
            if($auteurs != "")          {
                                        $citation .= $auteurs;                                                      // Affichage des auteurs si ils existent
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
                                        $citation .= removePonctuation(trim($contributeurs)).". ";                                           // Dans tous les cas, on fini par un point
                                        }
            if($numero_ville != "")     {
                                        $citation .= $numero_ville;                                                 // Affichage de la ville
                                        if($numero_editeur == "") {$citation .= ", ";}                              // Si il n'y a pas de nom d'éditeur, on ajoute une virgule
                                        }
            if($numero_editeur != "")   {
                                        if($numero_ville != "") {$citation .= ", ";}                                // Si il y a une ville, on commence avec une virgule
                                        $citation .= $numero_editeur.", ";                                          // Dans tous les cas, on fini par une virgule
                                        }
            if($revue_titre != "")      {
                                        $citation .= "«&nbsp;".$revue_titre."&nbsp;», ";                            // Collection
                                        }
            if($numero_annee != "")     {
                                        $citation .= $numero_annee.", ";                                            // Année
                                        }
            if($numero_nbre_pages != "")       {
                                        $citation .= $numero_nbre_pages." pages. ";                                 // Nombre de page
                                        }
            if($numero_isbn != "")              {
                                        $citation .= "ISBN : ".$numero_isbn.". ";                                   // ISBN
                                        }
            if($numero_doi != "")              {
                                        $citation .= "DOI : ".$numero_doi.". ";                                     // DOI
                                        }
            if($url != "")              {
                                        $citation .= "URL : ".$url." ";                                             // URL
                                        }
        }
    }

    echo $citation;
?>
