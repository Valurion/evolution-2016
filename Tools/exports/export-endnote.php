<?php
/*
 * Programme d'export des données, anciennement placé sur DEDI dans le dossier ENDNOTE
 * Refonte et déplacement du programme à partir du 28/12/2016 lors de la réfonte du Front-office
 * Développeur : Ibrahima
 * Remise en ordre : Julien CADET
 * Base de export-zotero.php
 */
include_once("../includes/config.php");
include_once("../includes/modeles.php");

// CAIRN ou CAIRN-INT
$domain = $_SERVER['SERVER_NAME'];
if($domain == "www.cairn.info") {$dbName = DBNAME; $dbNameAlt = DBINTNAME; $lang = "FR"; $siteBaseLink = "http://www.cairn.info/";}
else if($domain == "www.cairn-int.info") {$dbName = DBINTNAME; $dbNameAlt = DBNAME; $lang = "EN"; $siteBaseLink = "http://www.cairn-int.info/";}
else {$dbName = DBNAME; $dbNameAlt = DBINTNAME; $lang = "FR"; $siteBaseLink = "http://www.cairn.info/";}

// DEBUG
if(isset($_GET["cairnint"])) {$dbName = DBINTNAME; $dbNameAlt = DBNAME; $lang = "EN"; $siteBaseLink = "http://www.cairn-int.info/";}

// CONFIGURATION
set_time_limit(4000);
$lejour = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")));

// EXPORT FILENAME
if (isset($_GET["t"])) {
    // Export unique (depuis un article ou un numéro)
    $filename = "Cairn-".$_GET["ID_ARTICLE"]."-".date("Ymd", time()).".enw";
} else {
    // Export depuis la bibliographie
    $filename = 'Cairn-'
        .(($lang !== 'EN') ? 'MaBibliographie' : 'MySelection')
        .'-'
        .date("Ymd", time()) . '.enw';
}

// Auto-load
if (!isset($_GET['debug'])) {
    header('Content-type: application/x-research-info-systems');
    header('Content-disposition: filename='.$filename);
} else {
    header('Content-type: text/plain');
}

// DOUBLE CONNEXION PDO
$pdo = new PDO(DBMS . ":host=" . DBHOST . "; dbname=" . $dbName, DBUSER, DBPASS, array (PDO::ATTR_PERSISTENT => true ));
$pdo->exec('SET NAMES "utf8"');

$pdoAlt = new PDO(DBMS . ":host=" . DBHOST . "; dbname=" . $dbNameAlt, DBUSER, DBPASS, array (PDO::ATTR_PERSISTENT => true ));
$pdoAlt->exec('SET NAMES "utf8"');

// PARCOURS DES REFERENCES
$articles = $_GET["ID_ARTICLE"];

if (isset($_GET["ID_ARTICLE"])) {

    // On place les références dans un tableau
    $arrayArticles = explode('/', $articles);

    foreach($arrayArticles as $id_article)    {

        // Préparation de la requête de vérification (Article ou Numéro ?)
        $sql = "SELECT ARTICLE.ID_ARTICLE FROM ARTICLE WHERE ID_ARTICLE = :id_article";

        // Exécution de la requête
        $checkQuery = $pdo->prepare($sql);
        $checkQuery->bindValue(':id_article', $id_article, PDO::PARAM_STR);
        $checkQuery->execute();
        $CheckResult = $checkQuery->fetchAll(PDO::FETCH_ASSOC);
        $CheckCount  = count($CheckResult);

        // Il s'agit d'un article => traitement des articles identifié
        if($CheckCount != 0) {
            exportData($id_article, $pdo, $pdoAlt);
        }
        // Si aucun article n'a été trouvé, il s'agit peut-être d'un numéro
        else {
            // On renomme la variable
            $id_numpublie = $id_article;

            // Récupération du typepub du numéro
            $sql = "SELECT REVUE.TYPEPUB 
                    FROM REVUE 
                    INNER JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                    WHERE NUMERO.ID_NUMPUBLIE = :id_numpublie
                    GROUP BY REVUE.ID_REVUE
                    LIMIT 1";

            // Exécution de la requête
            $tpQuery = $pdo->prepare($sql);
            $tpQuery->bindValue(':id_numpublie', $id_numpublie, PDO::PARAM_STR);
            $tpQuery->execute();
            $tpResult = $tpQuery->fetchAll(PDO::FETCH_ASSOC);
            $tpCount  = count($tpResult);

            // Définition du typepub
            $typepub = $tpResult[0]["TYPEPUB"];

            // Si il s'agit d'un ouvrage, on sort un export...
            if($typepub == 3 || $typepub == 6) {
                // Export
                exportNumeroData($id_article, $pdo, $pdoAlt);
            }
            // ...sinon, on récupère la liste des articles
            else {
                // On récupère la liste des articles du numéro
                $sql = "SELECT ARTICLE.ID_ARTICLE FROM ARTICLE WHERE ID_NUMPUBLIE = :id_numpublie";

                // Exécution de la requête
                $articlesFromNumero = $pdo->prepare($sql);
                $articlesFromNumero->bindValue(':id_numpublie', $id_numpublie, PDO::PARAM_STR);
                $articlesFromNumero->execute();
                $articlesFromNumeroResult = $articlesFromNumero->fetchAll(PDO::FETCH_ASSOC);
                $articlesFromNumeroCount  = count($articlesFromNumeroResult);

                // On parcours la liste des articles
                foreach($articlesFromNumeroResult as $article) {
                    
                    // Définition de la variable
                    $id_article = $article["ID_ARTICLE"];
                    
                    // Export
                    exportData($id_article, $pdo, $pdoAlt);
                }
            }
        }
    }
}

// Traitement des données
// ARTICLES
// On passe l'ID de l'article et les deux objets de connexion
function exportData($id_article, $pdo, $pdoAlt) {
    global $siteBaseLink;
    global $lang;

    // Préparation de la requête de récupération des données de l'articles
    $sql     = "SELECT
                    ARTICLE.ID_ARTICLE, ARTICLE.TITRE AS TITRE_ARTICLE, ARTICLE.SOUSTITRE as SOUSTITRE_ARTICLE, ARTICLE.MOT_CLE, ARTICLE.PAGE_DEBUT, ARTICLE.PAGE_FIN, ARTICLE.DOI, ARTICLE.URL_REWRITING_EN as URL_ARTICLE_EN,
                    NUMERO.TITRE as TITRE_NUMERO, NUMERO.ANNEE, NUMERO.VOLUME, NUMERO.ISBN, NUMERO.NUMERO, NUMERO.NUMEROA, NUMERO.DATE_PARUTION, NUMERO.MEMO, NUMERO.URL_REWRITING as URL_NUMERO,
                    REVUE.TITRE AS TITRE_REVUE, REVUE.ISSN, REVUE.ISSN, REVUE.URL_REWRITING as URL_REVUE, REVUE.TYPEPUB,
                    EDITEUR.NOM_EDITEUR, EDITEUR.VILLE as LIEU_PUBLICATION,
                    RESUMES.RESUME_FR, RESUMES.RESUME_EN
               FROM ARTICLE
               LEFT JOIN RESUMES ON (ARTICLE.ID_ARTICLE = RESUMES.ID_ARTICLE)
               LEFT JOIN NUMERO ON (ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE)
               LEFT JOIN REVUE ON (ARTICLE.ID_REVUE = REVUE.ID_REVUE)
               LEFT JOIN EDITEUR ON (REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR)
               WHERE (ARTICLE.ID_ARTICLE = :id_article)";

    // Exécution de la requête
    $query = $pdo->prepare($sql);
    $query->bindValue(':id_article', $id_article, PDO::PARAM_STR);
    $query->execute();
    $qResult = $query->fetchAll(PDO::FETCH_ASSOC);
    $qCount  = count($qResult);

    // Tableau des valeurs
    $result = $qResult[0];


    // TYPE DE PUBLICATION (1 = Revue, 2 = Magazine, 3 = Ouvrage, 6 = Poche)
    $revueBaseLink = "";
    if($result["TYPEPUB"] == 1) {$typePub = "Journal article"; if($lang == "FR") {$revueBaseLink = "revue-";} else {$revueBaseLink = "article-";} }
    else if($result["TYPEPUB"] == 3) {$typePub = "Book Section";}
    else if($result["TYPEPUB"] == 2) {$typePub = "Magazine article";$revueBaseLink = "magazine-";}
    else if($result["TYPEPUB"] == 6) {$typePub = "Book Section";}
    else {$typePub = "Book";}


    // Récupération des données traduites sur la base alternative
    $sqlAlt = "SELECT
                    ARTICLE.ID_ARTICLE, ARTICLE.TITRE, ARTICLE.PAGE_DEBUT, ARTICLE.URL_REWRITING_EN,
                    NUMERO.ANNEE, NUMERO.NUMERO,
                    REVUE.URL_REWRITING,
                    RESUMES.RESUME_FR, RESUMES.RESUME_EN
                FROM ARTICLE
                LEFT JOIN RESUMES ON (ARTICLE.ID_ARTICLE = RESUMES.ID_ARTICLE)
                LEFT JOIN NUMERO ON (ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE)
                LEFT JOIN REVUE ON (ARTICLE.ID_REVUE = REVUE.ID_REVUE)
                WHERE
                    ARTICLE.ID_ARTICLE_S = :id_article";

    // Exécution de la requête
    $queryAlt = $pdoAlt->prepare($sqlAlt);
    $queryAlt->bindValue(':id_article', $id_article, PDO::PARAM_STR);
    $queryAlt->execute();
    $qResultAlt = $queryAlt->fetchAll(PDO::FETCH_ASSOC);
    $qCountAlt  = count($qResultAlt);

    // Tableau des valeurs
    $resultAlt = $qResultAlt[0];

    // Traitement des valeurs
    $titreAlt = "";
    $urlHtml  = "";

    if($qCountAlt != 0) {
        // Définition du titre
        $titreAlt = $resultAlt["TITRE"];                              
    }

    // Définition de l'URL  
    // En français          
    if($lang == "FR") {
        // Pour les Revues et les magazines
        if($result["TYPEPUB"] == 1 || $result["TYPEPUB"] == 2) {
            $urlHtml   = "%U ".$siteBaseLink.$revueBaseLink.$result["URL_REVUE"]."-".$result["ANNEE"]."-".$result["NUMERO"]."-page-".$result["PAGE_DEBUT"].".htm";    
        }
        // Pour les Ouvrages et les Encyclopédies
        if($result["TYPEPUB"] == 3 || $result["TYPEPUB"] == 6) {
            $urlHtml   = "%U ".$siteBaseLink.$revueBaseLink.$result["URL_NUMERO"]."--".$result["ISBN"]."-page-".$result["PAGE_DEBUT"].".htm";    
        }                
    }  
    // En anglais
    if($lang == "EN") {
        $urlHtml   = "%U ".$siteBaseLink.$revueBaseLink.$result["ID_ARTICLE"]."--".$result["URL_ARTICLE_EN"].".htm";
    }

    // Récupération des auteurs
    $sqlAuteur = "SELECT AUTEUR_ART.ID_ARTICLE, AUTEUR_ART.ORDRE, AUTEUR_ART.ID_AUTEUR, AUTEUR.PRENOM, AUTEUR.NOM, AUTEUR_ART.ATTRIBUT
                  FROM AUTEUR_ART
                  LEFT JOIN AUTEUR ON (AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR)
                  WHERE (AUTEUR_ART.ID_ARTICLE = :id_article)
                  ORDER BY AUTEUR_ART.ORDRE";

    // Exécution de la requête
    $queryAuteur = $pdo->prepare($sqlAuteur);
    $queryAuteur->bindValue(':id_article', $id_article, PDO::PARAM_STR);
    $queryAuteur->execute();
    $qResultAuteur = $queryAuteur->fetchAll(PDO::FETCH_ASSOC);
    $qCountAuteur = count($qResultAuteur);

    // Formatage et transformation des données
    // Traitement des auteurs
    $auteurs = "";
    if($qCountAuteur > 0) {
        foreach($qResultAuteur as $key => $auteur) {
            if($auteur["ATTRIBUT"] == "") {
                $auteurs .= "%A ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n";
            }
            else {
                if(strtolower($auteur["ATTRIBUT"]) == "Sous la direction de") { $auteurs .= "*E ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
                if( (strpos(strtolower($auteur["ATTRIBUT"]), "tradu") !== false) || (strpos(strtolower($auteur["ATTRIBUT"]), "translate") !== false) ) { $auteurs .= "%Y ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
            }
        }
    }

    // Mots clés
    $keywords = "";
    if($result["MOT_CLE"] != "") {
        $tabKeywords = split(',', $result["MOT_CLE"]);
        foreach ($tabKeywords as $keyword) {
            $keywords .= "%K ".formatData($keyword)."\n";
        }
    }

    // Titre et Sous-Titre
    $titre_article = "%T ".formatData($result["TITRE_ARTICLE"])."\n";
    if(formatData($result["SOUSTITRE_ARTICLE"]) != "") {
        $sep = ". ";
        if(hasPonctuation(formatData($result["TITRE_ARTICLE"])) == 1) {$sep = "";} // On n'ajoute pas de POINT si le titre termine par un point de ponctuation
        $titre_article = "%T ".formatData($result["TITRE_ARTICLE"]).$sep.formatData($result["SOUSTITRE_ARTICLE"])."\n";
    }

    // Résumé
    if(formatData($result["MEMO"]) != "") {$resume = "%X ".formatData($result["MEMO"])."\n";}
    if(formatData($result["RESUME_FR"]) != "" && $lang == "FR") {$resume = "%X ".formatData(strip_tags($result["RESUME_FR"]))."\n";}
    if(formatData($result["RESUME_EN"]) != "" && $lang == "EN") {$resume = "%X ".formatData(strip_tags($result["RESUME_EN"]))."\n";}
    

    // DEBUT DU FICHIER
    echo "%0 $typePub\n";
    echo $auteurs;
    if(formatData($result["ANNEE"]) != "") {echo "%D ".formatData($result["ANNEE"])."\n";}
    echo "%G ".$lang."\n";
    echo "%I ".formatData($result["NOM_EDITEUR"])."\n";
    echo "%C ".formatData($result["LIEU_PUBLICATION"])."\n";
    echo "%J ".formatData($result["TITRE_REVUE"])."\n";
    echo $keywords;
    if(($result["TYPEPUB"] == 3 || $result["TYPEPUB"] == 6) && (formatData($result["TITRE_NUMERO"]) != "")) {echo "%B ".formatData($result["TITRE_NUMERO"])."\n";}
    if($result["NUMERO"] != "") {echo "%N ".$result["NUMERO"]."\n";}
    if($result["PAGE_FIN"] != "") {echo "%P ".$result["PAGE_FIN"]."\n";}
    if(formatData($titreAlt) != "") {echo "%Q ".formatData($titreAlt)."\n";}
    if($result["DOI"] != "") {echo "%R ".$result["DOI"]."\n";}
    if($titre_article != "") {echo $titre_article;}
    if($urlHtml != "") {echo $urlHtml."\n";}
    if(($result["TYPEPUB"] == 1 || $result["TYPEPUB"] == 2) && ($result["VOLUME"] != "")) {echo "%V ".quenum($result["VOLUME"])."\n";}
    echo "%W Cairn.info\n";
    echo $resume;
    if($result["ISBN"] != "") {echo "%@ ".$result["ISBN"]."\n";}
    if($result["ISBN"] == "" && $result["ISSN"] != "") {echo "%@ ".$result["ISSN"]."\n";}
    echo "%! ".formatData($result["TITRE_ARTICLE"])."\n";
    echo "%> http://www.cairn.info/load_pdf.php?ID_ARTICLE=".$id_article."\n";
    echo "%~ Cairn.info\n";
}


// Traitement des données
// NUMEROS
// On passe l'ID du numero et les deux objets de connexion
function exportNumeroData($id_numpublie, $pdo, $pdoAlt) {
    global $siteBaseLink;
    global $lang;

    // Préparation de la requête de récupération des données de l'articles
    $sql    = "SELECT
                    NUMERO.TITRE as TITRE_NUMERO, NUMERO.SOUS_TITRE as SOUSTITRE_NUMERO, NUMERO.ANNEE, NUMERO.VOLUME, NUMERO.ISBN, NUMERO.NUMERO, NUMERO.NUMEROA, NUMERO.DOI, NUMERO.NB_PAGE, NUMERO.DATE_PARUTION, NUMERO.MEMO, NUMERO.URL_REWRITING as URL_NUMERO,
                    REVUE.TITRE AS TITRE_REVUE, REVUE.ISSN, REVUE.ISSN, REVUE.URL_REWRITING as URL_REVUE, REVUE.TYPEPUB,
                    EDITEUR.NOM_EDITEUR, EDITEUR.VILLE as LIEU_PUBLICATION
               FROM NUMERO
               LEFT JOIN REVUE ON (NUMERO.ID_REVUE = REVUE.ID_REVUE)
               LEFT JOIN EDITEUR ON (REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR)
               WHERE (NUMERO.ID_NUMPUBLIE = :id_numpublie)";

    // Exécution de la requête
    $query = $pdo->prepare($sql);
    $query->bindValue(':id_numpublie', $id_numpublie, PDO::PARAM_STR);
    $query->execute();
    $qResult = $query->fetchAll(PDO::FETCH_ASSOC);
    $qCount  = count($qResult);

    // Tableau des valeurs
    $result = $qResult[0];

    // TYPE DE PUBLICATION (3 = Ouvrage, 6 = Poche)
    $revueBaseLink  = "";
    if($lang == "EN") {$revueBaseLink = "journal-";}
    $typePub        = "Book";

    // Récupération des données traduites sur la base alternative
    $sqlAlt = "SELECT
                    NUMERO.ANNEE, NUMERO.NUMERO,
                    REVUE.URL_REWRITING
                FROM NUMERO
                LEFT JOIN REVUE ON (NUMERO.ID_REVUE = REVUE.ID_REVUE)
                WHERE
                    NUMERO.ID_NUMPUBLIE_S = :id_numpublie";

    // Exécution de la requête
    $queryAlt = $pdoAlt->prepare($sqlAlt);
    $queryAlt->bindValue(':id_numpublie', $id_numpublie, PDO::PARAM_STR);
    $queryAlt->execute();
    $qResultAlt = $queryAlt->fetchAll(PDO::FETCH_ASSOC);
    $qCountAlt  = count($qResultAlt);

    // Tableau des valeurs
    $resultAlt = $qResultAlt[0];

    // Traitement des valeurs
    $titreAlt = "";
    $urlHtml  = "";

    if($qCountAlt != 0) {
        // Définition du titre
        $titreAlt = $resultAlt["TITRE"];                              
    }

    // Définition de l'URL  
    // En français          
    if($lang == "FR") {
        $urlHtml   = "%U ".$siteBaseLink.$revueBaseLink.$result["URL_NUMERO"]."--".$result["ISBN"].".htm";                    
    }  
    // En anglais
    if($lang == "EN") {
        $urlHtml   = "%U ".$siteBaseLink.$revueBaseLink.$result["URL_REVUE"]."-".$result["ANNEE"]."-".$result["NUMERO"].".htm";
    }

    // Récupération des auteurs
    $sqlAuteur = "SELECT AUTEUR_ART.ID_ARTICLE, AUTEUR_ART.ORDRE, AUTEUR_ART.ID_AUTEUR, AUTEUR.PRENOM, AUTEUR.NOM, AUTEUR_ART.ATTRIBUT
                  FROM AUTEUR_ART
                  LEFT JOIN AUTEUR ON (AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR)
                  WHERE AUTEUR_ART.ID_NUMPUBLIE = :id_numpublie AND AUTEUR_ART.ID_ARTICLE = ''
                  ORDER BY AUTEUR_ART.ORDRE";

    // Exécution de la requête
    $queryAuteur = $pdo->prepare($sqlAuteur);
    $queryAuteur->bindValue(':id_numpublie', $id_numpublie, PDO::PARAM_STR);
    $queryAuteur->execute();
    $qResultAuteur = $queryAuteur->fetchAll(PDO::FETCH_ASSOC);
    $qCountAuteur = count($qResultAuteur);

    // Formatage et transformation des données
    // Traitement des auteurs
    $auteurs = "";
    if($qCountAuteur > 0) {
        foreach($qResultAuteur as $key => $auteur) {
            if($auteur["ATTRIBUT"] == "") {
                $auteurs .= "%A ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n";
            }
            else {
                if(strtolower($auteur["ATTRIBUT"]) == "Sous la direction de") { $auteurs .= "*E ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
                if( (strpos(strtolower($auteur["ATTRIBUT"]), "tradu") !== false) || (strpos(strtolower($auteur["ATTRIBUT"]), "translate") !== false) ) { $auteurs .= "%Y ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
            }
        }
    }

    // Titre et Sous-Titre
    $titre_numero = "%T ".formatData($result["TITRE_NUMERO"])."\n";
    if(formatData($result["SOUSTITRE_NUMERO"]) != "") {
        $sep = ". ";
        if(hasPonctuation(formatData($result["TITRE_NUMERO"])) == 1) {$sep = "";} // On n'ajoute pas de POINT si le titre termine par un point de ponctuation        
        $titre_numero = "%T ".formatData($result["TITRE_NUMERO"]).$sep.formatData($result["SOUSTITRE_NUMERO"])."\n";
    }

    // Résumé
    if(formatData($result["MEMO"]) != "") {$resume = "%X ".formatData($result["MEMO"])."\n";}    

    // DEBUT DU FICHIER
    echo "%0 $typePub\n";
    echo $auteurs;
    if(formatData($result["ANNEE"]) != "") {echo "%D ".formatData($result["ANNEE"])."\n";}
    echo "%G ".$lang."\n";
    echo "%I ".formatData($result["NOM_EDITEUR"])."\n";
    echo "%C ".formatData($result["LIEU_PUBLICATION"])."\n";
    echo "%J ".formatData($result["TITRE_REVUE"])."\n";
    if($titre_numero != "") {echo $titre_numero;}
    if($result["NUMERO"] != "") {echo "%N ".$result["NUMERO"]."\n";}
    if($result["NB_PAGE"] != "") {echo "%P ".$result["NB_PAGE"]."\n";}
    if(formatData($titreAlt) != "") {echo "%Q ".formatData($titreAlt)."\n";}
    if($result["DOI"] != "") {echo "%R ".$result["DOI"]."\n";}    
    if($urlHtml != "") {echo $urlHtml."\n";}
    echo "%W Cairn.info\n";
    echo $resume;
    if($result["ISBN"] != "") {echo "%@ ".$result["ISBN"]."\n";}
    if($result["ISBN"] == "" && $result["ISSN"] != "") {echo "%@ ".$result["ISSN"]."\n";}
    echo "%~ Cairn.info\n";
}
