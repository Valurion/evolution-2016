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
        $numero_pays    = trim($currentArticle["EDITEUR_PAYS"]);
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
            $numero_auteurs = formatAuteurEtContributeursAPA($numero_auteurs);
            // Pour les chapitres d'ouvrage, on récupère l'auteur de l'ouvrage et on le "transforme" en contributeur du chapitre
            // Uniquement SI il n'y a pas de contributeurs
            if(($numero_auteurs["CONTRIBUTEURS"] == "") && ($numero_auteurs["AUTEURS"] != "") ) {
                // Les auteurs de l'ouvrage deviennent des contributeurs
                $numero_auteurs = formatAuteursToContributeur($numero["NUMERO_AUTEUR"], "sous la direction de");
                // On reforme le tableau des AUTEURS & CONTRIBUTEURS sur la nouvelle base
                $numero_auteurs = formatAuteurEtContributeursAPA($numero_auteurs);
            }
            // Récupération uniquement des contributeurs
            $contributeurs  = $numero_auteurs["CONTRIBUTEURS"];

            // CHAPITRE
            // [AUTEURS]. ([ANNEE]). [TITRE CHAPITRE]. In [CONTRIBUTEURS], [TITRE OUVRAGE]: [SOUS-TITRE OUVRAGE] (pp. [PAGE DE DEBUT]-[PAGE DE FIN]). [LIEU]: [EDITEUR]. [DOI].
            if($auteurs != "")          {
                                        $citation .= removePonctuation(trim($auteurs))." ";                             // Affichage des auteurs si ils existent, on supprime la virgule et on remplace par un point.
                                        }
            if($numero_annee != "")     {
                                        $citation .= "(".$numero_annee."). ";                                           // Année
                                        }
            if($article_titre != "")    {
                                        if($article_stitre == "") {$citation .= $article_titre.". ";}                   // Affichage du titre de l'article/chapitre entre guillemets
                                        else {$citation .= removePonctuation($article_titre).": ".$article_stitre.". ";} 
                                        }
            if($contributeurs != "" || $numero_titre != "") {$citation .= "Dans ";}
            if($contributeurs != "")    {
                                        $citation .= removePonctuation(trim($contributeurs)).", ";                      // Dans tous les cas, on fini par un point
                                        }
            if($numero_titre != "")     {
                                        if($numero_stitre == "") {$citation .= "<i>".removePonctuation($numero_titre)."</i>";} // Affichage du titre (et du sous-titre) de l'ouvrage (en italique)
                                        else {$citation .= "<i>".removePonctuation($numero_titre).": ".$numero_stitre."</i>"; }
                                        $citation .= " (pp. ".$page_debut."-".$page_fin."). ";
                                        }

            if($numero_ville != "")     {
                                        $citation .= $numero_ville;                                                     // Affichage de la ville
                                        if(($numero_pays != "") && (!in_array($numero_ville, $villesConnues))) {$citation .= ", ".$numero_pays;} // Affichage du pays SAUF si il s'agit des villes de Paris, Lyon, Marseille ou Bruxelles
                                        if($numero_editeur == "") {$citation .= ".";}                                   // Si il n'y a pas de nom d'éditeur, on ajoute une virgule
                                        }
            if($numero_editeur != "")   {
                                        if($numero_ville != "") {$citation .= ": ";}                                    // Si il y a une ville, on commence avec une virgule
                                        $citation .= $numero_editeur.". ";                                              // Dans tous les cas, on fini par une virgule
                                        }
            if($doi != "")              {
                                        $citation .= "doi:".$doi.". ";                                                  // DOI
                                        }
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
    // OUVRAGES / NUMERO
    else {
        // Récupération des auteurs
        $auteurs_et_contributeurs = formatAuteurEtContributeursAPA($numero['NUMERO_AUTEUR']);

        // Init
        $citation       = "";
        $auteurs        = $auteurs_et_contributeurs["AUTEURS"];
        $contributeurs  = $auteurs_et_contributeurs["CONTRIBUTEURS"];
        $contributeurs  = formatContributeursAtFirstAPA($numero['NUMERO_AUTEUR']);

        // Récupération uniquement des contributeurs
        $traducteurs    = $contributeurs["TRADUCTEURS"];
        $contributeurs  = $contributeurs["CONTRIBUTEURS"];        

        // Récupération et formatage des données
        $revue_titre    = trim($revue["REVUE_TITRE"]);
        $numero_numero  = trim($numero["NUMERO_NUMERO"]);
        $numero_annee   = trim($numero["NUMERO_ANNEE"]);
        $numero_volume  = trim($numero["NUMERO_VOLUME"]);
        $numero_titre   = trim($numero["NUMERO_TITRE"]);
        $numero_stitre  = trim($numero["NUMERO_SOUS_TITRE"]);
        $numero_editeur = trim($revue["EDITEUR_NOM_EDITEUR"]);
        $numero_ville   = trim($revue["EDITEUR_VILLE"]);
        $numero_pays    = trim($revue["EDITEUR_PAYS"]);
        $numero_nbre_pages = trim($numero["NUMERO_NB_PAGE"]);
        $numero_isbn    = trim($numero["NUMERO_ISBN"]);
        $numero_doi     = trim($numero["NUMERO_DOI"]);
        $numero_url     = trim($numero["NUMERO_URL_REWRITING"]);

        // Définition de l'URL
        $url = "http://www.cairn.info/".$numero_url."--".$numero_isbn.".htm";

        // MONOGRAPHIE ou OUVRAGE
        if ($typePub == "ouvrage" || $typePub == "encyclopedie" || $typePub == "encyclopédie") {

            // OUVRAGES MONO
            // [AUTEURS]. ([ANNEE]). [TITRE OUVRAGE]: [SOUS-TITRE OUVRAGE] ([VOLUME]). [LIEU]: [EDITEUR]. [DOI].
            // OUVRAGES COLLECTIF
            // [AUTEURS], [CONTRIBUTEURS]. ([ANNEE]). [TITRE OUVRAGE]: [SOUS-TITRE OUVRAGE]. [LIEU]: [EDITEUR]. [DOI].
            if($auteurs != "" && 
                $contributeurs == "")   {
                                        $citation .= removePonctuation(trim($auteurs))." ";                         // Affichage des auteurs si ils existent, on supprime la virgule et on remplace par un point.
                                        }
            if($contributeurs != "")    {
                                        $citation .= removePonctuation(trim($contributeurs))." ";                   // Dans tous les cas, on fini par un point
                                        }
            if($numero_annee != "")     {
                                        $citation .= "(".$numero_annee."). ";                                       // Année
                                        }
            if($numero_titre != "")     {
                                        if($numero_stitre == "") {$citation .= $numero_titre.". ";}                 // Affichage du titre de l'article/chapitre entre guillemets
                                        else {$citation .= removePonctuation($numero_titre).": ".$numero_stitre.". ";}
                                        } 
            if($traducteurs != "")      {
                                        $citation .= '('.removePonctuation(trim($traducteurs)).") ";                // Dans tous les cas, on fini par un point
                                        }          
            if($numero_ville != "")     {
                                        $citation .= $numero_ville;                                                     // Affichage de la ville
                                        if(($numero_pays != "") && (!in_array($numero_ville, $villesConnues))) {$citation .= ", ".$numero_pays;} // Affichage du pays SAUF si il s'agit des villes de Paris, Lyon, Marseille ou Bruxelles
                                        if($numero_editeur == "") {$citation .= ".";}                                   // Si il n'y a pas de nom d'éditeur, on ajoute une virgule
                                        }
            if($numero_editeur != "")   {
                                        if($numero_ville != "") {$citation .= ": ";}                                    // Si il y a une ville, on commence avec une virgule
                                        $citation .= $numero_editeur.". ";                                              // Dans tous les cas, on fini par une virgule
                                        }
            if($numero_doi != "")       {
                                        $citation .= "doi:".$numero_doi.". ";                                       // DOI
                                        }
        }
    }

    echo $citation;
?>
