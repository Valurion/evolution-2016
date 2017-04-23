<?php
/*
 * Programme d'export des données, anciennement placé sur DEDI dans le dossier ENDNOTE
 * Refonte et déplacement du programme à partir du 28/12/2016 lors de la réfonte du Front-office
 * Développeur : Ibrahima
 * Remise en ordre : Julien CADET
 */
include_once("../includes/config.php");
include_once("../includes/modeles.php");

// CAIRN ou CAIRN-INT
$domain = $_SERVER['SERVER_NAME'];
if($domain == "www.cairn.info") {$dbName = DBNAME; $lang = "FR"; $siteBaseLink = "http://www.cairn.info/"; $revueBaseLink = "revue";}
else if($domain == "www.cairn-int.info") {$dbName = DBINTNAME; $lang = "EN"; $siteBaseLink = "http://www.cairn-int.info/"; $revueBaseLink = "journal";}
else {$dbName = DBNAME; $lang = "FR"; $siteBaseLink = "http://www.cairn.info/"; $revueBaseLink = "revue";}

// DEBUG
if(isset($_GET["cairnint"])) {$dbName = DBINTNAME; $lang = "EN"; $siteBaseLink = "http://www.cairn-int.info/"; $revueBaseLink = "journal";}

// CONFIGURATION
set_time_limit(4000);
$lejour = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")));

// EXPORT FILENAME
if (isset($_GET["t"])) {
    // Export unique (depuis un article ou un numéro)
    $filename = "Cairn-".$_GET["ID_ARTICLE"]."-".date("Ymd", time()).".ris";
} else {
    // Export depuis la bibliographie
    $filename = 'Cairn-'
        .(($lang !== 'EN') ? 'MaBibliographie' : 'MySelection')
        .'-'
        .date("Ymd", time()) . '.ris';
}

// Auto-load
if (!isset($_GET['debug'])) {
    header('Content-type: application/x-research-info-systems');
    header('Content-disposition: filename='.$filename);
} else {
    header('Content-type: text/plain');
}

// CONNEXION PDO
$pdo = new PDO(DBMS . ":host=" . DBHOST . "; dbname=" . $dbName, DBUSER, DBPASS, array (PDO::ATTR_PERSISTENT => true ));
$pdo->exec('SET NAMES "utf8"');

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
            // Préparation de la requête de récupération des données de l'articles
            $sql     = "SELECT
                            ARTICLE.ID_ARTICLE, ARTICLE.TITRE AS TITRE_ARTICLE, ARTICLE.SOUSTITRE as SOUSTITRE_ARTICLE, ARTICLE.MOT_CLE, ARTICLE.PAGE_DEBUT, ARTICLE.PAGE_FIN, ARTICLE.DOI,
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
                        $auteurs .= "A1  - ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n";
                    }
                    else {
                        if(strtolower($auteur["ATTRIBUT"]) == "Sous la direction de") { $auteurs .= "ED  - ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
                        if( (strpos(strtolower($auteur["ATTRIBUT"]), "tradu") !== false) || (strpos(strtolower($auteur["ATTRIBUT"]), "translate") !== false) ) { $auteurs .= "A4  - ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
                    }
                }
            }

            // Mots clés
            $keywords = "";
            if($result["MOT_CLE"] != "") {
                $tabKeywords = split(',', $result["MOT_CLE"]);
                foreach ($tabKeywords as $keyword) {
                    $keywords .= "KW  - ".formatData($keyword)."\n";
                }
            }

            // NUMERO ET NUMEROA
            if($result["NUMEROA"] != "") {
                $result["NUMERO"] .= "-".$result["NUMEROA"];
            }

            // TYPE DE PUBLICATION (1 = Revue, 2 = Magazine, 3 = Ouvrage, 6 = Poche)
            if($result["TYPEPUB"] == 1) {$typePub = "JOUR";}
            else if($result["TYPEPUB"] == 3) {$typePub = "CHAP";}
            else if($result["TYPEPUB"] == 2) {$typePub = "MGZN";}
            else {$typePub = "CHAP";}

            // DEBUT DU FICHIER
            # Source Format : http://referencemanager.com/sites/rm/files/m/direct_export_ris.pdf
            echo "TY  - $typePub\n";
            echo "ID  - $id_article\n";
            echo $auteurs;

            // Titre et Sous-Titre
            echo "ST  - ".formatData($result["TITRE_ARTICLE"])."\n";
            if(formatData($result["SOUSTITRE_ARTICLE"]) != "") {
                $sep = ". ";
                if(hasPonctuation(formatData($result["TITRE_ARTICLE"])) == 1) {$sep = "";} // On n'ajoute pas de POINT si le titre termine par un point de ponctuation
                echo "T1  - ".formatData($result["TITRE_ARTICLE"]).$sep.formatData($result["SOUSTITRE_ARTICLE"])."\n";
            }
            if($result["TYPEPUB"] == 3) {echo "T2  - ".formatData($result["TITRE_NUMERO"])."\n";} // Uniquement pour Ouvrages
            if(($result["TYPEPUB"] == 3) || ($result["TYPEPUB"] == 6)) {echo "T3  - ".formatData($result["TITRE_REVUE"])."\n";} // Uniquement pour Ouvrages et Poche
            if($result["TYPEPUB"] == 3) {
                if(formatData($result["SOUSTITRE_ARTICLE"]) != "") {
                    $sep = ". ";
                    if(hasPonctuation(formatData($result["TITRE_ARTICLE"])) == 1) {$sep = "";} // On n'ajoute pas de POINT si le titre termine par un point de ponctuation
                    echo "TI  - ".formatData($result["TITRE_ARTICLE"]).$sep.formatData($result["SOUSTITRE_ARTICLE"])."\n";
                }
                else {echo "TI  - ".formatData($result["TITRE_ARTICLE"])."\n";}
            }

            // Résumé
            if(formatData($result["MEMO"]) != "") {echo "AB  - ".formatData($result["MEMO"])."\n";}
            if(formatData($result["RESUME_FR"]) != "" && $lang == "FR") {echo "AB  - ".formatData(strip_tags($result["RESUME_FR"]))."\n";}
            if(formatData($result["RESUME_EN"]) != "" && $lang == "EN") {echo "AB  - ".formatData(strip_tags($result["RESUME_EN"]))."\n";}

            // Données de l'ouvrage
            if($result["TYPEPUB"] == 1) {echo "JO  - ".formatData($result["TITRE_REVUE"])."\n";} // Revues uniquement
            echo "Y1  - ".formatData($result["ANNEE"])."\n";
            echo "VL  - ".quenum($result["VOLUME"])."\n";            

            echo "IS  - ".$result["NUMERO"]."\n";
            echo "SP  - ".$result["PAGE_DEBUT"]."\n";
            echo "EP  - ".$result["PAGE_FIN"]."\n";

            // Méta-données
            echo "DB  - Cairn.info\n";
            echo "LA  - $lang\n";
            if($result["TYPEPUB"] != 2) {echo "L1  - http://www.cairn.info/load_pdf.php?ID_ARTICLE=$id_article\n";} // Tout sauf Magazine

            // URL
            // Pour les Revues
            if($result["TYPEPUB"] == 1) {
                if($result["URL_REVUE"] != "") {echo "L2  - ".$siteBaseLink.$revueBaseLink."-".$result["URL_REVUE"]."-".$result["ANNEE"]."-".$result["NUMERO"]."-page-".$result["PAGE_DEBUT"].".htm\n";}
                if($result["URL_REVUE"] != "") {echo "UR  - ".$siteBaseLink.$revueBaseLink."-".$result["URL_REVUE"]."-".$result["ANNEE"]."-".$result["NUMERO"]."-page-".$result["PAGE_DEBUT"].".htm\n";}
            }
            // Pour les Ouvrages et les Encyclopédies
            if($result["TYPEPUB"] == 3 || $result["TYPEPUB"] == 6) {
                if($result["URL_NUMERO"] != "") {echo "L2  - ".$siteBaseLink.$result["URL_NUMERO"]."--".$result["ISBN"]."-p-".$result["PAGE_DEBUT"].".htm\n";}
                if($result["URL_NUMERO"] != "") {echo "UR  - ".$siteBaseLink.$result["URL_NUMERO"]."--".$result["ISBN"]."-p-".$result["PAGE_DEBUT"].".htm\n";}
            }            

            // Mots-clés
            echo $keywords;

            // Données d'édition
            echo "PB  - ".formatData($result["NOM_EDITEUR"])."\n";
            echo "CY  - ".formatData($result["LIEU_PUBLICATION"])."\n";
            echo "DO  - ".$result["DOI"]."\n";
            //if(($result["TYPEPUB"] != 3) || ($result["TYPEPUB"] != 6)) {echo "SN  - ".$result["ISSN"]."\n";} // Tout sauf Ouvrages et Poche (pas de ISSN dans la table numero)
            //if(($result["TYPEPUB"] == 3) || ($result["TYPEPUB"] == 6)) {echo "SN  - ".$result["ISBN"]."\n";} // Uniquement pour Ouvrages et Poche 
            echo "SN  - ".$result["ISBN"]."\n";           

            echo 'ER  - '. "\n";
        }
        // Si aucun article n'a été trouvé, il s'agit peut-être d'un numéro
        else {
            // On renomme la variable
            $id_numpublie = $id_article;

            // Aucun article n'a été trouvé, on vérifie si il s'agit d'un numéro
            $sql     = "SELECT ID_NUMPUBLIE FROM NUMERO WHERE ID_NUMPUBLIE = :id_numpublie";

            // Exécution de la requête
            $checkQuery = $pdo->prepare($sql);
            $checkQuery->bindValue(':id_numpublie', $id_numpublie, PDO::PARAM_STR);
            $checkQuery->execute();
            $CheckResult = $checkQuery->fetchAll(PDO::FETCH_ASSOC);
            $CheckCount  = count($CheckResult);

            // Il s'agit bien d'un numéro
            if($CheckCount == 1) {

                // Préparation de la requête de récupération des données de l'articles
                $sql     = "SELECT
                                NUMERO.TITRE as TITRE_NUMERO, NUMERO.SOUS_TITRE as SOUSTITRE_NUMERO, NUMERO.MEMO, NUMERO.NB_PAGE, NUMERO.ANNEE, NUMERO.VOLUME, NUMERO.ISBN, NUMERO.NUMERO, NUMERO.NUMEROA, NUMERO.DATE_PARUTION, NUMERO.URL_REWRITING as URL_NUMERO,
                                REVUE.TITRE AS TITRE_REVUE, REVUE.ISSN, REVUE.ISSN, REVUE.TYPEPUB,
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

                // Récupération des auteurs
                $sqlAuteur = "SELECT AUTEUR_ART.ID_ARTICLE, AUTEUR_ART.ORDRE, AUTEUR_ART.ID_AUTEUR, AUTEUR.PRENOM, AUTEUR.NOM, AUTEUR_ART.ATTRIBUT
                              FROM AUTEUR_ART
                              LEFT JOIN AUTEUR ON (AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR)
                              WHERE (AUTEUR_ART.ID_NUMPUBLIE = :id_numpublie) AND (AUTEUR_ART.ID_ARTICLE = '')
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
                            $auteurs .= "A1  - ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n";
                        }
                        else {
                            if(strtolower($auteur["ATTRIBUT"]) == "Sous la direction de") { $auteurs .= "ED  - ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
                            if( (strpos(strtolower($auteur["ATTRIBUT"]), "tradu") !== false) || (strpos(strtolower($auteur["ATTRIBUT"]), "translate") !== false) ) { $auteurs .= "A4  - ".formatData($auteur["NOM"]).", ".formatData($auteur["PRENOM"])."\n"; }
                        }
                    }
                }

                // Formatage et transformation des données
                // NUMERO ET NUMEROA
                if($result["NUMEROA"] != "") {
                    $result["NUMERO"] .= "-".$result["NUMEROA"];
                }

                // TYPE DE PUBLICATION (1 : Revue, 2 = Magazine, 3 = Ouvrage, 6 = Poche)
                if($result["TYPEPUB"] == 1) {$typePub = "JFULL";}
                else if($result["TYPEPUB"] == 3 || $result["TYPEPUB"] == 6) {$typePub = "BOOK";}
                else if($result["TYPEPUB"] == 2) {$typePub = "MGZN";}
                else {$typePub = "BOOK";}

                // DEBUT DU FICHIER
                echo "TY  - $typePub\n";
                echo "ID  - $id_numpublie\n";
                echo $auteurs;

                // Titre et Sous-Titre
                // Pour les ouvrages et encyclopédies
                if($result["TYPEPUB"] == 3 || $result["TYPEPUB"] == 6) {
                    if(formatData($result["SOUSTITRE_NUMERO"]) != "") {
                       $sep = ". ";
                        if(hasPonctuation(formatData($result["TITRE_NUMERO"])) == 1) {$sep = "";} // On n'ajoute pas de POINT si le titre termine par un point de ponctuation
                        echo "TI  - ".formatData($result["TITRE_NUMERO"]).$sep.formatData($result["SOUSTITRE_NUMERO"])."\n";
                    }
                    else {echo "T1  - ".formatData($result["TITRE_NUMERO"])."\n";}

                    echo "T2  - ".formatData($result["TITRE_REVUE"])."\n";
                    echo "ST  - ".formatData($result["TITRE_ARTICLE"])."\n";
                }
                // Pour les revues
                else {
                    echo "T1  - ".formatData($result["TITRE_NUMERO"])."\n";
                    if(formatData($result["SOUSTITRE_NUMERO"]) != "") {echo "T2  - ".formatData($result["SOUSTITRE_NUMERO"])."\n";}
                }

                // Résumé
                if(formatData($result["MEMO"]) != "") {echo "AB  - ".formatData($result["MEMO"])."\n";}

                // Données de l'ouvrage
                echo "Y1  - ".formatData($result["ANNEE"])."\n";
                echo "VL  - ".quenum($result["VOLUME"])."\n";

                echo "IS  - ".$result["NUMERO"]."\n";
                echo "SP  - ".$result["NB_PAGE"]."\n";

                // Méta-données
                echo "DB  - Cairn.info\n";
                echo "LA  - $lang\n";
                if($result["URL_NUMERO"] != "") {echo "UR  - ".$siteBaseLink.$result["URL_NUMERO"]."--".$result["ISBN"].".htm\n";}

                // Données d'édition
                echo "PB  - ".formatData($result["NOM_EDITEUR"])."\n";
                echo "CY  - ".formatData($result["LIEU_PUBLICATION"])."\n";
                if(isset($result["DOI"])) {echo "DO  - ".$result["DOI"]."\n";}
                //if(($result["TYPEPUB"] != 3) || ($result["TYPEPUB"] != 6)) {echo "SN  - ".$result["ISSN"]."\n";} // Tout sauf Ouvrages et Poche (pas de ISSN dans la table numero)
                //if(($result["TYPEPUB"] == 3) || ($result["TYPEPUB"] == 6)) {echo "SN  - ".$result["ISBN"]."\n";} // Uniquement pour Ouvrages et Poche
                echo "SN  - ".$result["ISBN"]."\n";

                echo 'ER  - '. "\n";
            }
        }
    }
}
