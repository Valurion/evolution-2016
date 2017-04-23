<?php

require_once 'Framework/Modele.php';
require_once 'Modele/RedisClient.php';

/**
 * Provide access services to Cairn DB contents
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @author Pierre-Yves THOMAS
 * @todo : standardise return types : PDOStatements => Arrays et adresse ; Factorization
 * @todo : documentation
 */
class Content extends Modele {

    private $redis = null;

    function __construct($dsn_name = null) {
        if (Configuration::get('cacheContent') == 1) {
            $this->redis = new RedisClient(Configuration::get('redis_db_sql'));
        }
        if ($dsn_name != null) {
            $this->selectDatabase($dsn_name);
        }
    }

    function __destruct() {
        $this->closeDatabase();
    }

    public function &getCollectionNamesFromListId($listid) {
        $sql = "SELECT ID_REVUE,TITRE FROM REVUE  WHERE ID_REVUE IN ($listid);";
        $data = $this->executerRequete($sql);
        return $data->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function &getPortalInfoFromArticleId($listid) {
        $sql = "SELECT ID_ARTICLE ,`ARTICLE`.`URL_PORTAIL`, `PORTAIL`.`NOM_PORTAIL`  FROM `ARTICLE` INNER JOIN `PORTAIL` ON (`ARTICLE`.`ID_PORTAIL` = `PORTAIL`.`ID_PORTAIL`) WHERE ID_ARTICLE IN ($listid);";
        $data = $this->executerRequete($sql);
        return $data->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }

    public function &getTermesassocies($listid) {
        $sql = "SELECT content from meta
        WHERE docname IN ($listid);";
        $data = $this->executerRequete($sql);
        return $data->fetchAll();
    }

    public function &getMetNumForRecherche($listNumPublies) {
        $sql = "SELECT DISTINCT NUMERO.ID_NUMPUBLIE,
    `NUMERO`.`EAN`
    , `NUMERO`.`ISBN`
    , `NUMERO`.`SOUS_TITRE`
    , `EDITEUR`.`ID_EDITEUR`
    , `EDITEUR`.`NOM_EDITEUR`
    , `NUMERO`.`URL_REWRITING`
    , NUMERO.MEMO
    , REVUE.URL_REWRITING AS REVUE_URL_REWRITING
    , REVUE.MOVINGWALL
    , GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', AUTEUR_ART.ATTRIBUT) ORDER BY ORDRE SEPARATOR '|' ) AS NUMERO_AUTEUR

FROM
    `NUMERO`
    INNER JOIN `REVUE`
        ON (`NUMERO`.`ID_REVUE` = `REVUE`.`ID_REVUE`)
    INNER JOIN `EDITEUR`
        ON (`REVUE`.`ID_EDITEUR` = `EDITEUR`.`ID_EDITEUR`)
    LEFT JOIN AUTEUR_ART
        ON (AUTEUR_ART.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE AND ID_ARTICLE = '')
    LEFT JOIN AUTEUR
        ON AUTEUR.ID_AUTEUR = AUTEUR_ART.ID_AUTEUR
   where NUMERO.ID_NUMPUBLIE IN ($listNumPublies)
    GROUP BY NUMERO.ID_NUMPUBLIE;
;";

        $data = $this->executerRequete($sql);
        return $data->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }

    public function &getAllDisciplinesLabels() {
        $sql = "select POS_DISC as name, " . Configuration::get('disciplineDiscipline') . " as value from DISCIPLINE where parent=0";
        $disciplines = $this->executerRequete($sql);
        return $disciplines->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function &getAllTypePubLabels() {
        $sql = "select TYPEPUB as name, NOM_TYPEPUB as value from TYPEPUB";
        $type_pub = $this->executerRequete($sql);
        return $type_pub->fetchAll(PDO::FETCH_KEY_PAIR);
    }


    // Récupère le typepub d'une revue
    public function getRevueTypepub($idRevue) {
        $sql = "SELECT REVUE.TYPEPUB
                FROM REVUE
                WHERE REVUE.ID_REVUE = ?";
        $typepub = $this->executerRequete($sql, array($idRevue));
        return $typepub->fetch(PDO::FETCH_ASSOC);
    }

    // Récupère le typepub d'une revue en fonction du numéro
    public function getRevueTypepubFromNumero($idNumPublie) {
        $sql = "SELECT REVUE.TYPEPUB
                FROM REVUE
                INNER JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                WHERE NUMERO.ID_NUMPUBLIE = ?";
        $typepub = $this->executerRequete($sql, array($idNumPublie));
        return $typepub->fetch(PDO::FETCH_ASSOC);
    }

    public function getArticleFromId($idArticle) {
        $sql = "SELECT ARTICLE.ID_REVUE as ARTICLE_ID_REVUE,
            ARTICLE.ID_NUMPUBLIE as ARTICLE_ID_NUMPUBLIE,
            ARTICLE.ID_ARTICLE as ARTICLE_ID_ARTICLE,
            ARTICLE.MENTION_SOMMAIRE as ARTICLE_MENTION_SOMMAIRE,
            ARTICLE.SECT_SOM as ARTICLE_SECT_SOM ,
            ARTICLE.SECT_SSOM as ARTICLE_SECT_SSOM,
            ARTICLE.ID_ARTICLE as  ARTICLE_ID_ARTICLE,
            ARTICLE.SURTITRE as ARTICLE_SURTITRE,
            ARTICLE.SOUSTITRE as ARTICLE_SOUSTITRE,
            ARTICLE.PAGE_DEBUT as ARTICLE_PAGE_DEBUT,
            ARTICLE.PAGE_FIN as ARTICLE_PAGE_FIN,
            ARTICLE.TITRE as  ARTICLE_TITRE,
            ARTICLE.URL_PORTAIL as ARTICLE_URL_PORTAIL,
            ARTICLE_EXTRAWEB.NOM_FICHIER as ARTICLE_EXTRAWEB_NOM_FICHIER,
            ARTICLE_EXTRAWEB.TITRE as ARTICLE_EXTRAWEB_TITRE,
            GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR,
            ARTICLE.CONFIG_ARTICLE as ARTICLE_CONFIG_ARTICLE,
            ARTICLE.PRIX as ARTICLE_PRIX,
            ARTICLE.TOUJOURS_PAYANT as ARTICLE_TOUJOURS_PAYANT,
            ARTICLE.ID_ARTICLE_S AS ARTICLE_ID_ARTICLE_S,
            ARTICLE.URL_REWRITING_EN AS ARTICLE_URL_REWRITING_EN,
            ARTICLE.LANGUE AS ARTICLE_LANGUE,
            ARTICLE.LANGUE_INTEGRALE AS ARTICLE_LANGUE_INTEGRALE,
            ARTICLE.MOT_CLE AS ARTICLE_MOTS_CLES,
            ARTICLE.MOTS_CLES_DE AS ARTICLE_MOTS_CLES_DE,
            ARTICLE.MOTS_CLES_PT AS ARTICLE_MOTS_CLES_PT,
            ARTICLE.DOI AS ARTICLE_DOI,
            ARTICLE.STATUT AS ARTICLE_STATUT,
            ARTICLE.MOVINGWALL AS MOVINGWALL,
            (SELECT 1 FROM docsRS WHERE docsRS.`docfilename` = ARTICLE.`ID_ARTICLE` AND proche = 1 LIMIT 1) AS ARTICLE_SUJET_PROCHE,
            NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
            NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
            NUMERO.ID_REVUE as NUMERO_ID_REVUE,
            NUMERO.TITRE as NUMERO_TITRE,
            NUMERO.ANNEE as NUMERO_ANNEE,
            NUMERO.NUMERO as NUMERO_NUMERO,
            REVUE.TYPEPUB as REVUE_TYPEPUB,
            REVUE.TITRE as REVUE_TITRE,
            REVUE.ACHAT_ARTICLE as REVUE_ACHAT_ARTICLE,
            REVUE.ID_REVUE as REVUE_ID_REVUE,
            EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR ,EDITEUR.VILLE as EDITEUR_VILLE
        FROM ARTICLE
        JOIN NUMERO
            ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
        JOIN REVUE
            ON REVUE.ID_REVUE = NUMERO.ID_REVUE
        LEFT JOIN  AUTEUR_ART
            ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
        LEFT JOIN AUTEUR
            ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
        LEFT JOIN ARTICLE_EXTRAWEB
            ON ( ARTICLE_EXTRAWEB.ID_ARTICLE = ARTICLE.ID_ARTICLE )
        LEFT JOIN EDITEUR
            ON ( EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR)
        WHERE ARTICLE.ID_ARTICLE = ?
        GROUP BY ARTICLE.ID_ARTICLE;";
        $article = $this->executerRequete($sql, array($idArticle));
        return $article->fetch(PDO::FETCH_ASSOC);
    }

    public function getArticlesFromIds($idArticles) {
        $sql = "SELECT ARTICLE.ID_REVUE as ARTICLE_ID_REVUE,
            ARTICLE.ID_NUMPUBLIE as ARTICLE_ID_NUMPUBLIE,
            ARTICLE.ID_ARTICLE as ARTICLE_ID_ARTICLE,
            ARTICLE.MENTION_SOMMAIRE as ARTICLE_MENTION_SOMMAIRE,
            TRIM(ARTICLE.SECT_SOM) as ARTICLE_SECT_SOM ,
            TRIM(ARTICLE.SECT_SSOM) as ARTICLE_SECT_SSOM,
            ARTICLE.ID_ARTICLE as  ARTICLE_ID_ARTICLE,
            ARTICLE.SURTITRE as ARTICLE_SURTITRE,
            ARTICLE.SOUSTITRE as ARTICLE_SOUSTITRE,
            ARTICLE.PAGE_DEBUT as ARTICLE_PAGE_DEBUT,
            ARTICLE.PAGE_FIN as ARTICLE_PAGE_FIN,
            ARTICLE.TITRE as  ARTICLE_TITRE,
            ARTICLE.URL_PORTAIL as ARTICLE_URL_PORTAIL,
            ARTICLE_EXTRAWEB.NOM_FICHIER as ARTICLE_EXTRAWEB_NOM_FICHIER,
            ARTICLE_EXTRAWEB.TITRE as ARTICLE_EXTRAWEB_TITRE,
            GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR,
            ARTICLE.CONFIG_ARTICLE as ARTICLE_CONFIG_ARTICLE,
            ARTICLE.PRIX as ARTICLE_PRIX,
            ARTICLE.TOUJOURS_PAYANT as ARTICLE_TOUJOURS_PAYANT,
            ARTICLE.LANGUE_INTEGRALE as ARTICLE_LANGUE_INTEGRALE,
            ARTICLE.URL_REWRITING_EN as ARTICLE_URL_REWRITING_EN,
            ARTICLE.ID_ARTICLE_S AS ARTICLE_ID_ARTICLE_S,
            ARTICLE.MOVINGWALL AS MOVINGWALL,
            (SELECT 1 FROM docsRS WHERE docsRS.`docfilename` = ARTICLE.`ID_ARTICLE` AND proche = 1 ) AS ARTICLE_SUJET_PROCHE,
            NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
            NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
            NUMERO.ID_REVUE as NUMERO_ID_REVUE,
            NUMERO.TITRE as NUMERO_TITRE,
            NUMERO.ANNEE as NUMERO_ANNEE,
            NUMERO.NUMERO as NUMERO_NUMERO,
            REVUE.TYPEPUB as REVUE_TYPEPUB,
            REVUE.TITRE as REVUE_TITRE,
            REVUE.ACHAT_ARTICLE as REVUE_ACHAT_ARTICLE,
            REVUE.ID_REVUE as REVUE_ID_REVUE,
            EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,EDITEUR.VILLE as EDITEUR_VILLE
        FROM ARTICLE
        JOIN NUMERO
            ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
        JOIN REVUE
            ON REVUE.ID_REVUE = NUMERO.ID_REVUE
        LEFT JOIN  AUTEUR_ART
            ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
        LEFT JOIN AUTEUR
            ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
        LEFT JOIN ARTICLE_EXTRAWEB
            ON ( ARTICLE_EXTRAWEB.ID_ARTICLE = ARTICLE.ID_ARTICLE )
        LEFT JOIN EDITEUR
            ON ( EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR)
        WHERE ARTICLE.ID_ARTICLE IN (" . $idArticles . ")
        GROUP BY ARTICLE.ID_ARTICLE;";
        $article = $this->executerRequete($sql);
        return $article->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArticlesIdFromNumero($idNumPublies, $statut = 1) {
        $sql = "SELECT ID_ARTICLE FROM ARTICLE WHERE ID_NUMPUBLIE IN (" . $idNumPublies . ") AND STATUT = " . $statut;
        $articles = $this->executerRequete($sql);
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getArticlesDocIdFromNumero($idNumPublies, $statut = 1) {
        $sql = "SELECT docsRS.id FROM ARTICLE JOIN docsRS ON docsRS.docfilename = ARTICLE.ID_ARTICLE WHERE ID_NUMPUBLIE IN (" . $idNumPublies . ") AND STATUT = " . $statut;
        $articles = $this->executerRequete($sql);
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllArticlesIdFromNumero($idNumPublies) {
        $sql = "SELECT ID_ARTICLE FROM ARTICLE WHERE ID_NUMPUBLIE IN (" . $idNumPublies . ")";
        $articles = $this->executerRequete($sql);
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllArticlesDocIdFromNumero($idNumPublies) {
        $sql = "SELECT docsRS.id FROM ARTICLE JOIN docsRS ON docsRS.docfilename = ARTICLE.ID_ARTICLE WHERE ID_NUMPUBLIE IN (" . $idNumPublies . "))";
        $articles = $this->executerRequete($sql);
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getArticlesFromlNumero($numPublie) {
        $sql = " SELECT ARTICLE.MENTION_SOMMAIRE as ARTICLE_MENTION_SOMMAIRE,
            TRIM(ARTICLE.SECT_SOM) as ARTICLE_SECT_SOM,
            TRIM(ARTICLE.SECT_SSOM) as ARTICLE_SECT_SSOM,
            ARTICLE.ID_ARTICLE as  ARTICLE_ID_ARTICLE,
            ARTICLE.SURTITRE as ARTICLE_SURTITRE,
            ARTICLE.SOUSTITRE as ARTICLE_SOUSTITRE,
            ARTICLE.PAGE_DEBUT as ARTICLE_PAGE_DEBUT,
            ARTICLE.PAGE_FIN as ARTICLE_PAGE_FIN,
            ARTICLE.TITRE as  ARTICLE_TITRE,
            GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR,
            ARTICLE.CONFIG_ARTICLE as ARTICLE_CONFIG_ARTICLE,
            ARTICLE.PRIX as ARTICLE_PRIX,
            ARTICLE.URL_PORTAIL as ARTICLE_URL_PORTAIL,
            ARTICLE.TOUJOURS_PAYANT as ARTICLE_TOUJOURS_PAYANT,
            ARTICLE.LANGUE AS ARTICLE_LANGUE,
            ARTICLE.LANGUE_INTEGRALE AS ARTICLE_LANGUE_INTEGRALE,
            ARTICLE.ID_ARTICLE_S AS ARTICLE_ID_ARTICLE_S,
            ARTICLE.URL_REWRITING_EN AS ARTICLE_URL_REWRITING_EN,
            ARTICLE.STATUT AS ARTICLE_STATUT,
            ARTICLE.MODEPAGE as ARTICLE_MODEPAGE,
            ARTICLE.MOVINGWALL as MOVINGWALL,
            PORTAIL.ID_PORTAIL as PORTAIL_ID_PORTAIL,
            PORTAIL.NOM_PORTAIL as PORTAIL_NOM_PORTAIL,
            NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
            NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
            REVUE.ACHAT_ARTICLE as REVUE_ACHAT_ARTICLE,
            REVUE.ID_REVUE as REVUE_ID_REVUE,
            REVUE.ACCES_EN_LIGNE as REVUE_ACCES_EN_LIGNE,
            EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,EDITEUR.VILLE as EDITEUR_VILLE
        FROM ARTICLE
        JOIN NUMERO
            ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
        JOIN REVUE
            ON REVUE.ID_REVUE = NUMERO.ID_REVUE
        LEFT JOIN  AUTEUR_ART
            ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
        LEFT JOIN AUTEUR
            ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
        LEFT JOIN PORTAIL
            ON ( PORTAIL.ID_PORTAIL = ARTICLE.ID_PORTAIL)
        LEFT JOIN EDITEUR
            ON ( EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR)
        WHERE ARTICLE.ID_NUMPUBLIE =?
        GROUP BY ARTICLE.ID_ARTICLE
        ORDER BY ARTICLE.TRISHOW;";

        $articles = $this->executerRequete($sql, array($numPublie));
        return $articles->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArticleFromUrl($idNumPublie, $pagePage) {
        $sql = "SELECT ARTICLE.ID_ARTICLE as ARTICLE_ID_ARTICLE,
            ARTICLE.MENTION_SOMMAIRE as ARTICLE_MENTION_SOMMAIRE,
            ARTICLE.SECT_SOM as ARTICLE_SECT_SOM ,
            ARTICLE.SECT_SSOM as ARTICLE_SECT_SSOM,
            ARTICLE.ID_ARTICLE as  ARTICLE_ID_ARTICLE,
            ARTICLE.SURTITRE as ARTICLE_SURTITRE,
            ARTICLE.SOUSTITRE as ARTICLE_SOUSTITRE,
            ARTICLE.PAGE_DEBUT as ARTICLE_PAGE_DEBUT,
            ARTICLE.PAGE_FIN as ARTICLE_PAGE_FIN,
            ARTICLE.TITRE as  ARTICLE_TITRE,
            ARTICLE.DOI as ARTICLE_DOI,
            ARTICLE.URL_PORTAIL as ARTICLE_URL_PORTAIL,
            ARTICLE_EXTRAWEB.NOM_FICHIER as ARTICLE_EXTRAWEB_NOM_FICHIER,
            ARTICLE_EXTRAWEB.TITRE as ARTICLE_EXTRAWEB_TITRE,
            ARTICLE.TOUJOURS_PAYANT as ARTICLE_TOUJOURS_PAYANT,
            GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR,
            ARTICLE.CONFIG_ARTICLE as ARTICLE_CONFIG_ARTICLE,
            ARTICLE.PRIX as ARTICLE_PRIX,
            ARTICLE.URL_REWRITING_EN AS ARTICLE_URL_REWRITING_EN,
            ARTICLE.ID_ARTICLE_S AS ARTICLE_ID_ARTICLE_S,
            ARTICLE.STATUT AS ARTICLE_STATUT,
            ARTICLE.LANGUE AS ARTICLE_LANGUE,
            ARTICLE.MOT_CLE AS ARTICLE_MOTS_CLES,
            ARTICLE.MOTS_CLES_DE AS ARTICLE_MOTS_CLES_DE,
            ARTICLE.MOTS_CLES_PT AS ARTICLE_MOTS_CLES_PT,
            ARTICLE.MOVINGWALL AS MOVINGWALL,
            (SELECT 1 FROM docsRS WHERE docsRS.`docfilename` = ARTICLE.`ID_ARTICLE` AND proche = 1 limit 1) AS ARTICLE_SUJET_PROCHE,
            NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
            NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
            REVUE.ACHAT_ARTICLE as REVUE_ACHAT_ARTICLE,
            REVUE.ID_REVUE as REVUE_ID_REVUE,
            REVUE.TYPEPUB as REVUE_TYPEPUB,
            REVUE.ACCES_EN_LIGNE as REVUE_ACCES_EN_LIGNE,
            EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,
            EDITEUR.VILLE as EDITEUR_VILLE,
            EDITEUR.PAYS as EDITEUR_PAYS
        FROM ARTICLE
        JOIN NUMERO
            ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
        JOIN REVUE
            ON REVUE.ID_REVUE = NUMERO.ID_REVUE
        LEFT JOIN  AUTEUR_ART
            ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
        LEFT JOIN AUTEUR
            ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
        LEFT JOIN ARTICLE_EXTRAWEB
            ON ( ARTICLE_EXTRAWEB.ID_ARTICLE = ARTICLE.ID_ARTICLE )
        LEFT JOIN EDITEUR
            ON ( EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR)
        WHERE ARTICLE.ID_NUMPUBLIE = ? AND ARTICLE.PAGE_DEBUT = ?
        GROUP BY ARTICLE.ID_ARTICLE;";
        $article = $this->executerRequete($sql, array($idNumPublie, $pagePage));
        return $article->fetch(PDO::FETCH_ASSOC);
    }

    public function countEnglishFullTextArticles($idRevue) {
        $sql = "SELECT COUNT(*) FROM ARTICLE
            JOIN NUMERO
                ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
            WHERE ARTICLE.ID_REVUE = ?
                AND ARTICLE.STATUT = 1
                AND LANGUE_INTEGRALE = 'en'
                AND NUMERO.STATUT = 1";
        $article = $this->executerRequete($sql, array($idRevue));
        return $article->fetch(PDO::FETCH_COLUMN);
    }

    public function getEnglishFullTextArticlesFromRevue($idRevue) {
        $sql = "SELECT ARTICLE.ID_REVUE as ARTICLE_ID_REVUE,
            ARTICLE.ID_NUMPUBLIE as ARTICLE_ID_NUMPUBLIE,
            ARTICLE.ID_ARTICLE as ARTICLE_ID_ARTICLE,
            ARTICLE.MENTION_SOMMAIRE as ARTICLE_MENTION_SOMMAIRE,
            TRIM(ARTICLE.SECT_SOM) as ARTICLE_SECT_SOM ,
            TRIM(ARTICLE.SECT_SSOM) as ARTICLE_SECT_SSOM,
            ARTICLE.ID_ARTICLE as  ARTICLE_ID_ARTICLE,
            ARTICLE.SURTITRE as ARTICLE_SURTITRE,
            ARTICLE.SOUSTITRE as ARTICLE_SOUSTITRE,
            ARTICLE.PAGE_DEBUT as ARTICLE_PAGE_DEBUT,
            ARTICLE.PAGE_FIN as ARTICLE_PAGE_FIN,
            ARTICLE.TITRE as  ARTICLE_TITRE,
            ARTICLE.URL_PORTAIL as ARTICLE_URL_PORTAIL,
            GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR,
            ARTICLE.CONFIG_ARTICLE as ARTICLE_CONFIG_ARTICLE,
            ARTICLE.PRIX as ARTICLE_PRIX,
            ARTICLE.TOUJOURS_PAYANT as ARTICLE_TOUJOURS_PAYANT,
            ARTICLE.ID_ARTICLE_S AS ARTICLE_ID_ARTICLE_S,
            ARTICLE.URL_REWRITING_EN AS ARTICLE_URL_REWRITING_EN,
            ARTICLE.LANGUE AS ARTICLE_LANGUE,
            ARTICLE.LANGUE_INTEGRALE AS ARTICLE_LANGUE_INTEGRALE,
            ARTICLE.MOT_CLE AS ARTICLE_MOTS_CLES,
            ARTICLE.MOTS_CLES_DE AS ARTICLE_MOTS_CLES_DE,
            ARTICLE.MOTS_CLES_PT AS ARTICLE_MOTS_CLES_PT,
            ARTICLE.DOI AS ARTICLE_DOI,
            ARTICLE.MOVINGWALL AS MOVINGWALL,
            NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
            NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
            NUMERO.ID_REVUE as NUMERO_ID_REVUE,
            NUMERO.TITRE as NUMERO_TITRE,
            NUMERO.ANNEE as NUMERO_ANNEE,
            CONVERT(NUMERO.NUMERO, UNSIGNED INT) as NUMERO_NUMERO,
            NUMERO.VOLUME as NUMERO_VOLUME,
            REVUE.TYPEPUB as REVUE_TYPEPUB,
            REVUE.TITRE as REVUE_TITRE,
            REVUE.ACHAT_ARTICLE as REVUE_ACHAT_ARTICLE,
            REVUE.ID_REVUE as REVUE_ID_REVUE
        FROM ARTICLE
        JOIN NUMERO
            ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
        JOIN REVUE
            ON REVUE.ID_REVUE = NUMERO.ID_REVUE
        LEFT JOIN  AUTEUR_ART
            ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
        LEFT JOIN AUTEUR
            ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
        WHERE ARTICLE.ID_REVUE = ?
        AND ARTICLE.STATUT = 1
        AND NUMERO.STATUT = 1
        AND LANGUE_INTEGRALE = 'en'
        GROUP BY ARTICLE.ID_ARTICLE
        ORDER BY ANNEE DESC, NUMERO_NUMERO DESC, ARTICLE.ID_ARTICLE;";
        $article = $this->executerRequete($sql, array($idRevue));
        return $article->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Retourne le contenu de la table ARTICLE_LIBELLE sous forme d'array ARTICLE_LIBELLE.KEYART => ARRAY[RECORD]
     */

    public function getArticlesLibelles() {
        $arrayReturn = array();

        $sql = "SELECT * from ARTICLE_LIBELLE";
        $ret = $this->executerRequete($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ret as $row) {
            $arrayReturn[$row["KEYART"]] = $row;
        }

        return $arrayReturn;
    }

    public function getAuteurById($id) {
        $sql = "SELECT AUTEUR.ID_AUTEUR AS AUTEUR_ID_AUTEUR,
                    AUTEUR.PRENOM AS AUTEUR_PRENOM,
                    AUTEUR.NOM AS AUTEUR_NOM
                FROM AUTEUR
                WHERE ID_AUTEUR = ?";
        $auteur = $this->executerRequete($sql, array($id));

        return $auteur->fetch(PDO::FETCH_ASSOC);
    }

    public function getAuteursNum($idNumPublie) {
        $sql = "SELECT AUTEUR.ID_AUTEUR AS AUTEUR_ID_AUTEUR,
                    AUTEUR.PRENOM AS AUTEUR_PRENOM,
                    AUTEUR.NOM AS AUTEUR_NOM,
                    AUTEUR_ART.ATTRIBUT AS AUTEUR_ATTRIBUT
                FROM AUTEUR
                JOIN AUTEUR_ART ON AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR
                WHERE ID_NUMPUBLIE = ? AND ID_ARTICLE = ''
                ORDER BY ORDRE";
        $auteur = $this->executerRequete($sql, array($idNumPublie));

        return $auteur->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAuteursArticle($idArticle) {
        $sql = "SELECT AUTEUR.ID_AUTEUR AS AUTEUR_ID_AUTEUR,
                    AUTEUR.PRENOM AS AUTEUR_PRENOM,
                    AUTEUR.NOM AS AUTEUR_NOM,
                    AUTEUR_ART.ATTRIBUT AS AUTEUR_ATTRIBUT
                FROM AUTEUR
                JOIN AUTEUR_ART ON AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR
                WHERE ID_ARTICLE = ?
                ORDER BY ORDRE";
        $auteur = $this->executerRequete($sql, array($idArticle));

        return $auteur->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Retourne les ouvrages d'un auteur
     */

    public function getAuteurOuvrages($id) {
        $sql = "SELECT REVUE.ID_REVUE as REVUE_ID_REVUE,
                    REVUE.CONFIG_ARTICLE as REVUE_CONFIG_ARTICLE,
                    REVUE.TITRE as REVUE_TITRE,
                    REVUE.TYPEPUB as REVUE_TYPEPUB,
                    REVUE.URL_REWRITING as REVUE_URL_REWRITING,
                    EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,
                    EDITEUR.VILLE as EDITEUR_VILLE,
                    NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
                    NUMERO.ANNEE as NUMERO_ANNEE,
                    NUMERO.TITRE as NUMERO_TITRE ,
                    NUMERO.SOUS_TITRE as NUMERO_SOUS_TITRE ,
                    NUMERO.ISBN as NUMERO_ISBN,
                    NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                    NUMERO.EAN AS NUMERO_EAN
                FROM NUMERO
                JOIN AUTEUR_ART ON AUTEUR_ART.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE AND AUTEUR_ART.ID_ARTICLE = ''
                JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                LEFT JOIN EDITEUR ON ( REVUE.ID_EDITEUR=EDITEUR.ID_EDITEUR)
                WHERE AUTEUR_ART.ID_AUTEUR = ?
                AND REVUE.TYPEPUB IN (3,6)
                AND REVUE.STATUT = 1 AND NUMERO.STATUT = 1
                AND (NUMERO.DERNIERE_EDITION = '' OR NUMERO.DERNIERE_EDITION IS NULL)
                ORDER BY NUMERO.ANNEE DESC";

        $revue = $this->executerRequete($sql, array($id));

        return $revue->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Retourne les articles d'un auteur, pour un ou plusieurs TYPEPUB
     */

    public function getAuteurArticles($id, $typePub, $excludeOuvAut = false) {
        $sql = " SELECT ARTICLE.ID_ARTICLE as  ARTICLE_ID_ARTICLE,
                    ARTICLE.PAGE_DEBUT as ARTICLE_PAGE_DEBUT,
                    ARTICLE.PAGE_FIN as ARTICLE_PAGE_FIN,
                    ARTICLE.TITRE as  ARTICLE_TITRE,
                    ARTICLE.SOUSTITRE as  ARTICLE_SOUSTITRE,
                    ARTICLE.CONFIG_ARTICLE as ARTICLE_CONFIG_ARTICLE,
                    ARTICLE.PRIX as ARTICLE_PRIX,
                    ARTICLE.TOUJOURS_PAYANT as ARTICLE_TOUJOURS_PAYANT,
                    ARTICLE.LANGUE AS ARTICLE_LANGUE,
                    ARTICLE.LANGUE_INTEGRALE AS ARTICLE_LANGUE_INTEGRALE,
                    ARTICLE.ID_ARTICLE_S AS ARTICLE_ID_ARTICLE_S,
                    ARTICLE.URL_REWRITING_EN AS ARTICLE_URL_REWRITING_EN,
                    ARTICLE.MOVINGWALL AS MOVINGWALL,
                    NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
                    NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
                    NUMERO.ANNEE AS NUMERO_ANNEE,
                    NUMERO.VOLUME AS NUMERO_VOLUME,
                    NUMERO.NUMERO AS NUMERO_NUMERO,
                    NUMERO.EAN AS NUMERO_EAN,
                    NUMERO.ISBN AS NUMERO_ISBN,
                    NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                    NUMERO.TITRE AS NUMERO_TITRE,
                    REVUE.ID_REVUE AS REVUE_ID_REVUE,
                    REVUE.TITRE AS REVUE_TITRE,
                    REVUE.URL_REWRITING AS REVUE_URL_REWRITING,
                    REVUE.URL_REWRITING AS REVUE_URL_REWRITING_EN,
                    REVUE.TYPEPUB AS REVUE_TYPEPUB,
                    REVUE.ACHAT_ARTICLE AS REVUE_ACHAT_ARTICLE,
                    EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,
                    EDITEUR.VILLE as EDITEUR_VILLE,
                    ARTICLE.URL_PORTAIL as ARTICLE_URL_PORTAIL,
                    PORTAIL.NOM_PORTAIL as PORTAIL_NOM_PORTAIL
                FROM AUTEUR_ART
                JOIN ARTICLE ON ARTICLE.ID_ARTICLE = AUTEUR_ART.ID_ARTICLE
                JOIN NUMERO ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
                JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                LEFT JOIN EDITEUR ON ( REVUE.ID_EDITEUR=EDITEUR.ID_EDITEUR)
                LEFT JOIN PORTAIL ON ( PORTAIL.ID_PORTAIL = ARTICLE.ID_PORTAIL)
                WHERE AUTEUR_ART.ID_AUTEUR = ?
                AND ARTICLE.STATUT = 1 AND NUMERO.STATUT = 1 AND REVUE.STATUT = 1" .
                " AND TYPEPUB " . (strpos($typePub, ',') ? "IN (" . $typePub . ")" : "= " . $typePub);
        if ($excludeOuvAut) {
            //On exclut les articles de numéros dont il est l'auteur principal
            $sql.= " AND NOT EXISTS (
                        SELECT * FROM AUTEUR_ART AA
                        WHERE AA.ID_AUTEUR = AUTEUR_ART.ID_AUTEUR
                        AND AA.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
                        AND AA.ID_ARTICLE = ''
                    )";
        }
        $sql .= " ORDER BY NUMERO.ANNEE DESC, NUMERO.NUMERO DESC;";

        $articles = $this->executerRequete($sql, array($id));
        return $articles->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBiblioNumeros($listIds) {
        $sql = "SELECT REVUE.ID_REVUE as REVUE_ID_REVUE,
                    REVUE.CONFIG_ARTICLE as REVUE_CONFIG_ARTICLE,
                    REVUE.TITRE as REVUE_TITRE,
                    REVUE.TYPEPUB as REVUE_TYPEPUB,
                    REVUE.URL_REWRITING as REVUE_URL_REWRITING,
                    EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,
                    EDITEUR.VILLE as EDITEUR_VILLE,
                    EDITEUR.VILLE as EDITEUR_VILLE_EDITEUR,
                    NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
                    NUMERO.ANNEE as NUMERO_ANNEE,
                    NUMERO.VOLUME AS NUMERO_VOLUME,
                    NUMERO.NUMERO AS NUMERO_NUMERO,
                    NUMERO.TITRE as NUMERO_TITRE ,
                    NUMERO.SOUS_TITRE as NUMERO_SOUS_TITRE ,
                    NUMERO.ISBN as NUMERO_ISBN,
                    NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                    NUMERO.EAN AS NUMERO_EAN,
                    NUMERO.ISBN AS NUMERO_ISBN,
                    NUMERO.NB_PAGE AS NUMERO_PAGES,
                    GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS BIBLIO_AUTEURS
                FROM NUMERO
                LEFT JOIN AUTEUR_ART ON (`AUTEUR_ART`.`ID_NUMPUBLIE`=  NUMERO.`ID_NUMPUBLIE` AND AUTEUR_ART.ID_ARTICLE = '')
                LEFT JOIN AUTEUR ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
                JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                LEFT JOIN EDITEUR ON ( REVUE.ID_EDITEUR=EDITEUR.ID_EDITEUR)
                WHERE (";
        for ($ind = 0; $ind < count($listIds); $ind++) {
            $sql .= ($ind > 0 ? ' OR ' : '') . ' NUMERO.ID_NUMPUBLIE = ?';
        }
        $sql .=")
                AND REVUE.STATUT = 1 AND NUMERO.STATUT = 1
                AND NUMERO.TITRE_ABREGE != '' AND REVUE.REVUE_COURANTE = ''
                AND NUMERO.DERNIERE_EDITION = ''
                GROUP BY NUMERO.ID_NUMPUBLIE
                ORDER BY NUMERO.TRISHOW";

        $numeros = $this->executerRequete($sql, $listIds);

        return $numeros->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBiblioArticles($listIds) {
        $sql = " SELECT ARTICLE.ID_ARTICLE as  ARTICLE_ID_ARTICLE,
                    ARTICLE.PAGE_DEBUT as ARTICLE_PAGE_DEBUT,
                    ARTICLE.PAGE_FIN as ARTICLE_PAGE_FIN,
                    ARTICLE.TITRE as  ARTICLE_TITRE,
                    ARTICLE.SOUSTITRE as  ARTICLE_SOUSTITRE,
                    ARTICLE.CONFIG_ARTICLE as ARTICLE_CONFIG_ARTICLE,
                    ARTICLE.PRIX as ARTICLE_PRIX,
                    ARTICLE.LANGUE AS ARTICLE_LANGUE,
                    ARTICLE.LANGUE_INTEGRALE AS ARTICLE_LANGUE_INTEGRALE,
                    ARTICLE.ID_ARTICLE_S AS ARTICLE_ID_ARTICLE_S,
                    ARTICLE.URL_REWRITING_EN AS ARTICLE_URL_REWRITING_EN,
                    ARTICLE.TOUJOURS_PAYANT AS ARTICLE_TOUJOURS_PAYANT,
                    ARTICLE.MOVINGWALL AS MOVINGWALL,
                    NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
                    NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
                    NUMERO.ANNEE AS NUMERO_ANNEE,
                    NUMERO.VOLUME AS NUMERO_VOLUME,
                    NUMERO.NUMERO AS NUMERO_NUMERO,
                    NUMERO.EAN AS NUMERO_EAN,
                    NUMERO.ISBN AS NUMERO_ISBN,
                    NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                    NUMERO.TITRE AS NUMERO_TITRE,
                    REVUE.ID_REVUE AS REVUE_ID_REVUE,
                    REVUE.TITRE AS REVUE_TITRE,
                    REVUE.URL_REWRITING AS REVUE_URL_REWRITING,
                    REVUE.URL_REWRITING AS REVUE_URL_REWRITING_EN,
                    REVUE.TYPEPUB AS REVUE_TYPEPUB,
                    REVUE.ACHAT_ARTICLE AS REVUE_ACHAT_ARTICLE,
                    EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,
                    EDITEUR.VILLE as EDITEUR_VILLE,
                    ARTICLE.URL_PORTAIL as ARTICLE_URL_PORTAIL,
                    GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS BIBLIO_AUTEURS,
                    PORTAIL.NOM_PORTAIL as PORTAIL_NOM_PORTAIL
                FROM ARTICLE
                JOIN NUMERO ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
                JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                LEFT JOIN AUTEUR_ART ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
                LEFT JOIN AUTEUR ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
                LEFT JOIN EDITEUR ON ( REVUE.ID_EDITEUR=EDITEUR.ID_EDITEUR)
                LEFT JOIN PORTAIL ON ( PORTAIL.ID_PORTAIL = ARTICLE.ID_PORTAIL)
                WHERE (";
        for ($ind = 0; $ind < count($listIds); $ind++) {
            $sql .= ($ind > 0 ? ' OR ' : '') . ' ARTICLE.ID_ARTICLE = ?';
        }
        /* REDMINE 46222 $sql .=")
                AND ARTICLE.STATUT = 1 AND NUMERO.STATUT = 1 AND REVUE.STATUT = 1
                AND REVUE.REVUE_COURANTE = ''";*/
        $sql .=") AND ARTICLE.STATUT = 1 AND NUMERO.STATUT = 1 AND REVUE.STATUT = 1";
        $sql .= " GROUP BY ARTICLE.ID_ARTICLE ORDER BY NUMERO.DATE_MISEENLIGNE DESC;";
        $articles = $this->executerRequete($sql, $listIds);
        return $articles->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Renvoie la liste des éditeurs, éventuellement filtrée sur l'existence de revues actives, d'un certain type ou non
     */

    public function getEditeurs($filter = TRUE, $typepub = NULL) {
        $redisKey = 'getEditeurs/' . $typepub . '/' . $filter;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT ID_EDITEUR AS EDITEUR_ID_EDITEUR,
                    NOM_EDITEUR AS EDITEUR_NOM_EDITEUR,
                    TYPE as EDITEUR_TYPE
                FROM EDITEUR";
            if ($filter == TRUE) {
                $sql .= " WHERE EXISTS (
                SELECT * FROM REVUE
                    WHERE REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR
                    AND REVUE.STATUT = 1
                    " . ($typepub != NULL ? (" AND TYPEPUB = " . $typepub) : "") . "
                    AND REVUE_COURANTE = ''
                    AND TITRE_ABREGE!=''
                    )";
            }
            $sql .= " ORDER BY NOM_EDITEUR";
            $ret = $this->executerRequete($sql)->fetchAll(PDO::FETCH_ASSOC);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $ret);
            }
            return $ret;
        }
    }

    public function getEditeursByNbreRevue($typepub) {
        $sql = "SELECT
                    EDITEUR.ID_EDITEUR AS EDITEUR_ID_EDITEUR, EDITEUR.NOM_EDITEUR AS EDITEUR_NOM_EDITEUR, count(REVUE.ID_REVUE) as EDITEUR_NBRE_REVUE
                FROM
                    EDITEUR
                INNER JOIN
                    REVUE
                    ON REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR
                WHERE
                    REVUE.STATUT = 1 AND TYPEPUB = ? AND REVUE_COURANTE = '' AND TITRE_ABREGE != ''
                GROUP BY
                    EDITEUR.ID_EDITEUR
                ORDER BY
                    EDITEUR_NOM_EDITEUR ASC";
        $liste = $this->executerRequete($sql, array($typepub));
        return $liste->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEditeurById($idEditeur) {
        $sql = "SELECT ID_EDITEUR AS EDITEUR_ID_EDITEUR,
                NOM_EDITEUR AS EDITEUR_NOM_EDITEUR,
                WEB AS EDITEUR_WEBSITE
            FROM EDITEUR
            WHERE ID_EDITEUR = ?";

        $ret = $this->executerRequete($sql, array($idEditeur))->fetchAll(PDO::FETCH_ASSOC);
        return $ret[0];
    }

    public function getNumpublie($idRevue, $annee, $numero) {
        $sql = "SELECT ID_REVUE AS NUMERO_ID_REVUE,
                ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                MOVINGWALL AS NUMERO_MOVINGWALL,
                VOLUME AS NUMERO_VOLUME,
                NUMERO AS NUMERO_NUMERO,
                TITRE AS NUMERO_TITRE,
                SOUS_TITRE AS NUMERO_SOUS_TITRE,
                ISBN AS NUMERO_ISBN,
                ISBN_NUMERIQUE AS NUMERO_ISBN_NUMERIQUE,
                EAN AS NUMERO_EAN,
                PRIX AS NUMERO_PRIX,
                PRIX_ELEC AS NUMERO_PRIX_ELEC,
                EPUB AS NUMERO_EPUB,
                STATUT AS NUMERO_STATUT,
                ANNEE AS NUMERO_ANNEE,
                MEMO AS NUMERO_MEMO,
                NB_PAGE AS NUMERO_NB_PAGE,
                DATE_PARUTION AS NUMERO_DATE_PARUTION,
                NUMEROA AS NUMERO_NUMEROA,
                TITRE_ABREGE AS NUMERO_TITRE_ABREGE,
                CONFIG_ARTICLE AS NUMERO_CONFIG_ARTICLE,
                URL_REWRITING AS NUMERO_URL_REWRITING,
                DERNIERE_EDITION AS NUMERO_DERNIERE_EDITION,
                EDITION_PRECEDENTE AS NUMERO_EDITION_PRECEDENTE,
                TYPE_NUMPUBLIE AS NUMERO_TYPE_NUMPUBLIE,
                ID_NUMPUBLIE_S AS NUMERO_ID_NUMPUBLIE_S,
                DOI AS NUMERO_DOI,
                PREPUB AS NUMERO_PREPUB,
                EPUISE AS NUMERO_EPUISE,
                DATE_MISEENLIGNE AS NUMERO_DATE_MISEENLIGNE,
                GRILLEPRIX AS NUMERO_GRILLEPRIX,
                NUMERO.MOV_WALL_PPV AS NUMERO_MOV_WALL_PPV
            from NUMERO
            where ID_REVUE=? AND ANNEE=? AND NUMERO=?";
        $id = $this->executerRequete($sql, array($idRevue, $annee, $numero));
        return $id->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNumpublieById($idNumPublie) {
        $sql = "SELECT NUMERO.ID_REVUE AS NUMERO_ID_REVUE,
                ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                NUMERO.MOVINGWALL AS NUMERO_MOVINGWALL,
                VOLUME AS NUMERO_VOLUME,
                NUMERO.NUMERO AS NUMERO_NUMERO,
                NUMERO.TITRE AS NUMERO_TITRE,
                NUMERO.SOUS_TITRE AS NUMERO_SOUS_TITRE,
                ISBN AS NUMERO_ISBN,
                EAN AS NUMERO_EAN,
                NUMERO.PRIX AS NUMERO_PRIX,
                PRIX_ELEC AS NUMERO_PRIX_ELEC,
                EPUB AS NUMERO_EPUB,
                NUMERO.STATUT AS NUMERO_STATUT,
                ANNEE AS NUMERO_ANNEE,
                MEMO AS NUMERO_MEMO,
                NB_PAGE AS NUMERO_NB_PAGE,
                NUMERO.DATE_PARUTION AS NUMERO_DATE_PARUTION,
                NUMEROA AS NUMERO_NUMEROA,
                NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE,
                NUMERO.CONFIG_ARTICLE AS NUMERO_CONFIG_ARTICLE,
                NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                NUMERO.EPUISE AS NUMERO_EPUISE,
                DERNIERE_EDITION AS NUMERO_DERNIERE_EDITION,
                EDITION_PRECEDENTE AS NUMERO_EDITION_PRECEDENTE,
                TYPE_NUMPUBLIE AS NUMERO_TYPE_NUMPUBLIE,
                ID_NUMPUBLIE_S AS NUMERO_ID_NUMPUBLIE_S,
                DOI AS NUMERO_DOI,
                REVUE.TITRE AS REVUE_TITRE,
                REVUE.TYPEPUB AS REVUE_TYPEPUB,
                REVUE.URL_REWRITING AS REVUE_URL_REWRITING,
                REVUE.ID_EDITEUR AS REVUE_ID_EDITEUR,
                GRILLEPRIX AS NUMERO_GRILLEPRIX
            from NUMERO
            join REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
            where ID_NUMPUBLIE = ? ";
        $id = $this->executerRequete($sql, array($idNumPublie));
        return $id->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNumpublieFromIsbnAndUrlRewriting($isbn, $urlRewriting) {
        $sql = "SELECT ID_REVUE AS NUMERO_ID_REVUE,
                ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                MOVINGWALL AS NUMERO_MOVINGWALL,
                VOLUME AS NUMERO_VOLUME,
                NUMERO AS NUMERO_NUMERO,
                TITRE AS NUMERO_TITRE,
                SOUS_TITRE AS NUMERO_SOUS_TITRE,
                ISBN AS NUMERO_ISBN,
                EAN AS NUMERO_EAN,
                PRIX AS NUMERO_PRIX,
                PRIX_ELEC AS NUMERO_PRIX_ELEC,
                EPUB AS NUMERO_EPUB,
                STATUT AS NUMERO_STATUT,
                ANNEE AS NUMERO_ANNEE,
                MEMO AS NUMERO_MEMO,
                NB_PAGE AS NUMERO_NB_PAGE,
                DATE_PARUTION AS NUMERO_DATE_PARUTION,
                NUMEROA AS NUMERO_NUMEROA,
                TITRE_ABREGE AS NUMERO_TITRE_ABREGE,
                CONFIG_ARTICLE AS NUMERO_CONFIG_ARTICLE,
                URL_REWRITING AS NUMERO_URL_REWRITING,
                DERNIERE_EDITION AS NUMERO_DERNIERE_EDITION,
                EDITION_PRECEDENTE AS NUMERO_EDITION_PRECEDENTE,
                TYPE_NUMPUBLIE AS NUMERO_TYPE_NUMPUBLIE,
                ID_NUMPUBLIE_S AS NUMERO_ID_NUMPUBLIE_S,
                DOI AS NUMERO_DOI,
                GRILLEPRIX AS NUMERO_GRILLEPRIX
            from NUMERO
            where ISBN=? AND URL_REWRITING = ?";
        $id = $this->executerRequete($sql, array($isbn, $urlRewriting));
        return $id->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIdRevueFromUrl($urlRewrite, $typepub=null) {
        $sql = "select ID_REVUE from REVUE where URL_REWRITING=?";
        $queryParams = [$urlRewrite];
        if ($typepub !== null) {
            $sql .= ' AND TYPEPUB = ?';
            $queryParams []= $typepub;
        }
        $id = $this->executerRequete($sql, $queryParams);
        return $id->fetch()['ID_REVUE'];
    }

    public function getIdRevueFromNumeroUrl($urlRewrite, $isbn) {
        $sql = "SELECT ID_REVUE FROM NUMERO WHERE URL_REWRITING = ? AND ISBN = ?";
        $id = $this->executerRequete($sql, array($urlRewrite, $isbn));
        return $id->fetch()['ID_REVUE'];
    }

    public function getAProposRevueFromUrl($urlRewrite) {
        $sql = "SELECT REVUE.ID_REVUE,
                    REVUE.TITRE,
                    REVUE.ISSN,
                    REVUE.ISSN_NUM,
                    REVUE.PERIODICITE,
                    REVUE.WEB,
                    REVUE.TYPEPUB,
                    REVUE.SAVOIR_PLUS,
                    REVUE.SAVOIR_PLUS2,
                    REVUE.URL_REWRITING,
                    REVUE.SAVOIR_PLUS_EN,
                    REVUE.SAVOIR_PLUS2_EN,
                    REVUE.URL_REWRITING_EN,
                    REVUE.AFFILIATION,
                    REVUE.STITRE,
                    REVUE.ID_REVUE_S,
                    REVUE.REVUE_PRECEDENTE,
                    EDITEUR.NOM_EDITEUR,
                    EDITEUR.ID_EDITEUR
                FROM REVUE
                LEFT JOIN EDITEUR ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
                WHERE REVUE.URL_REWRITING = ?";
        $revue = $this->executerRequete($sql, array($urlRewrite));
        return $revue->fetch(PDO::FETCH_ASSOC);
    }

    public function getRevueNumeroFromUrlAndIsbn($urlRewrite, $isbn) {
        $sql = "SELECT NUMERO.ID_REVUE AS NUMERO_ID_REVUE,
                NUMERO.ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                NUMERO.MOVINGWALL AS NUMERO_MOVINGWALL,
                NUMERO.VOLUME AS NUMERO_VOLUME,
                NUMERO.NUMERO AS NUMERO_NUMERO,
                NUMERO.TITRE AS NUMERO_TITRE,
                NUMERO.SOUS_TITRE AS NUMERO_SOUS_TITRE,
                NUMERO.ISBN AS NUMERO_ISBN,
                NUMERO.ISBN_NUMERIQUE AS NUMERO_ISBN_NUMERIQUE,
                NUMERO.EAN AS NUMERO_EAN,
                NUMERO.PRIX AS NUMERO_PRIX,
                NUMERO.PRIX_ELEC AS NUMERO_PRIX_ELEC,
                NUMERO.STATUT AS NUMERO_STATUT,
                NUMERO.ANNEE AS NUMERO_ANNEE,
                NUMERO.MEMO AS NUMERO_MEMO,
                NUMERO.NB_PAGE AS NUMERO_NB_PAGE,
                NUMERO.DATE_PARUTION AS NUMERO_DATE_PARUTION,
                NUMERO.NUMEROA AS NUMERO_NUMEROA,
                NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE,
                NUMERO.CONFIG_ARTICLE AS NUMERO_CONFIG_ARTICLE,
                NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                NUMERO.DERNIERE_EDITION AS NUMERO_DERNIERE_EDITION,
                NUMERO.EDITION_PRECEDENTE AS NUMERO_EDITION_PRECEDENTE,
                NUMERO.TYPE_NUMPUBLIE AS NUMERO_TYPE_NUMPUBLIE,
                NUMERO.ID_NUMPUBLIE_S AS NUMERO_ID_NUMPUBLIE_S,
                NUMERO.DOI AS NUMERO_DOI,
                NUMERO.PREPUB AS NUMERO_PREPUB,
                NUMERO.EPUISE AS NUMERO_EPUISE,
                NUMERO.MOV_WALL_PPV AS NUMERO_MOV_WALL_PPV,
                REVUE.ID_REVUE as REVUE_ID_REVUE,
                REVUE.TYPEPUB as REVUE_TYPEPUB,
                REVUE.URL_REWRITING as REVUE_URL_REWRITING,
                NUM_PREC.URL_REWRITING as PREV_NUM_URL_REWRITING,
                NUM_PREC.EAN as PREV_NUM_EAN,
                NUM_PREC.ISBN as PREV_NUM_ISBN,
                NUM_LAST.URL_REWRITING as LAST_NUM_URL_REWRITING,
                NUM_LAST.EAN as LAST_NUM_EAN,
                NUM_LAST.ISBN as LAST_NUM_ISBN,
                NUMERO.DATE_MISEENLIGNE as NUMERO_DATE_MISEENLIGNE,
                GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS NUMERO_AUTEUR
            from NUMERO
            join REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
            left join NUMERO NUM_PREC on NUM_PREC.ID_NUMPUBLIE = NUMERO.EDITION_PRECEDENTE
            left join NUMERO NUM_LAST on NUM_LAST.ID_NUMPUBLIE = NUMERO.DERNIERE_EDITION
            left join  AUTEUR_ART ON (`AUTEUR_ART`.`ID_NUMPUBLIE`=  NUMERO.`ID_NUMPUBLIE` and AUTEUR_ART.ID_ARTICLE = '')
            left join AUTEUR ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
            where NUMERO.URL_REWRITING=? and NUMERO.ISBN=?
            GROUP BY NUMERO.ID_NUMPUBLIE;";
        $id = $this->executerRequete($sql, array($urlRewrite, $isbn));
        return $id->fetch(PDO::FETCH_ASSOC);
    }

    public function getRevueNumeroFromId($idNumPublie) {
        $sql = "SELECT NUMERO.ID_REVUE AS NUMERO_ID_REVUE,
                NUMERO.ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                NUMERO.MOVINGWALL AS NUMERO_MOVINGWALL,
                NUMERO.VOLUME AS NUMERO_VOLUME,
                NUMERO.NUMERO AS NUMERO_NUMERO,
                NUMERO.TITRE AS NUMERO_TITRE,
                NUMERO.SOUS_TITRE AS NUMERO_SOUS_TITRE,
                NUMERO.ISBN AS NUMERO_ISBN,
                NUMERO.ISBN_NUMERIQUE AS NUMERO_ISBN_NUMERIQUE,
                NUMERO.EAN AS NUMERO_EAN,
                NUMERO.PRIX AS NUMERO_PRIX,
                NUMERO.PRIX_ELEC AS NUMERO_PRIX_ELEC,
                NUMERO.EPUB AS NUMERO_EPUB,
                NUMERO.STATUT AS NUMERO_STATUT,
                NUMERO.ANNEE AS NUMERO_ANNEE,
                NUMERO.MEMO AS NUMERO_MEMO,
                NUMERO.NB_PAGE AS NUMERO_NB_PAGE,
                NUMERO.DATE_PARUTION AS NUMERO_DATE_PARUTION,
                NUMERO.NUMEROA AS NUMERO_NUMEROA,
                NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE,
                NUMERO.CONFIG_ARTICLE AS NUMERO_CONFIG_ARTICLE,
                NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                NUMERO.DERNIERE_EDITION AS NUMERO_DERNIERE_EDITION,
                NUMERO.EDITION_PRECEDENTE AS NUMERO_EDITION_PRECEDENTE,
                NUMERO.TYPE_NUMPUBLIE AS NUMERO_TYPE_NUMPUBLIE,
                NUMERO.ID_NUMPUBLIE_S AS NUMERO_ID_NUMPUBLIE_S,
                NUMERO.DOI AS NUMERO_DOI,
                NUMERO.PREPUB AS NUMERO_PREPUB,
                NUMERO.EPUISE AS NUMERO_EPUISE,
                NUMERO.MOV_WALL_PPV AS NUMERO_MOV_WALL_PPV,
                REVUE.ID_REVUE as REVUE_ID_REVUE,
                REVUE.TYPEPUB as REVUE_TYPEPUB,
                REVUE.URL_REWRITING as REVUE_URL_REWRITING,
                NUM_PREC.URL_REWRITING as PREV_NUM_URL_REWRITING,
                NUM_PREC.EAN as PREV_NUM_EAN,
                NUM_PREC.ISBN as PREV_NUM_ISBN,
                NUM_LAST.URL_REWRITING as LAST_NUM_URL_REWRITING,
                NUM_LAST.EAN as LAST_NUM_EAN,
                NUM_LAST.ISBN as LAST_NUM_ISBN,
                NUMERO.DATE_MISEENLIGNE as NUMERO_DATE_MISEENLIGNE,
                GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS NUMERO_AUTEUR
            from NUMERO
            join REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
            left join NUMERO NUM_PREC on NUM_PREC.ID_NUMPUBLIE = NUMERO.EDITION_PRECEDENTE
            left join NUMERO NUM_LAST on NUM_LAST.ID_NUMPUBLIE = NUMERO.DERNIERE_EDITION
            left join  AUTEUR_ART ON (`AUTEUR_ART`.`ID_NUMPUBLIE`=  NUMERO.`ID_NUMPUBLIE` and AUTEUR_ART.ID_ARTICLE = '')
            left join AUTEUR ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
            where NUMERO.ID_NUMPUBLIE=?
            GROUP BY NUMERO.ID_NUMPUBLIE;";
        $id = $this->executerRequete($sql, array($idNumPublie));
        return $id->fetch(PDO::FETCH_ASSOC);
    }

    public function getNumeroRevuesById($id, $filterAnnee = null, $limitFrom = null, $limitTo = null) {
        $redisKey = 'getNumeroRevuesById/' . $id . '/' . $filterAnnee . '/' . $limitFrom . '/' . $limitTo;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT PORTAIL.NOM_PORTAIL as PORTAIL_NOM_PORTAIL ,
                    (SELECT COUNT(*) AS Nb FROM ARTICLE as ARTICLEBIS WHERE STATUT=1 AND ARTICLEBIS.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE ) as NUMERO_NB_ARTICLES,
                    NUMERO.ID_REVUE AS NUMERO_ID_REVUE,
                    NUMERO.ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                    NUMERO.MOVINGWALL AS NUMERO_MOVINGWALL,
                    NUMERO.VOLUME AS NUMERO_VOLUME,
                    NUMERO.NUMERO AS NUMERO_NUMERO,
                    NUMERO.TITRE AS NUMERO_TITRE,
                    NUMERO.SOUS_TITRE AS NUMERO_SOUS_TITRE,
                    NUMERO.ISBN AS NUMERO_ISBN,
                    NUMERO.EAN AS NUMERO_EAN,
                    NUMERO.PRIX AS NUMERO_PRIX,
                    NUMERO.STATUT AS NUMERO_STATUT,
                    NUMERO.ANNEE AS NUMERO_ANNEE,
                    NUMERO.MEMO AS NUMERO_MEMO,
                    NUMERO.NB_PAGE AS NUMERO_NB_PAGE,
                    NUMERO.DATE_PARUTION AS NUMERO_DATE_PARUTION,
                    NUMERO.NUMEROA AS NUMERO_NUMEROA,
                    NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE,
                    NUMERO.CONFIG_ARTICLE AS NUMERO_CONFIG_ARTICLE,
                    NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                    NUMERO.TYPE_NUMPUBLIE AS NUMERO_TYPE_NUMPUBLIE,
                    NUMERO.DOI AS NUMERO_DOI,
                    DERNIERE_EDITION AS NUMERO_DERNIERE_EDITION,
                    EDITION_PRECEDENTE AS NUMERO_EDITION_PRECEDENTE,
                    TYPE_NUMPUBLIE AS NUMERO_TYPE_NUMPUBLIE,
                    ID_NUMPUBLIE_S AS NUMERO_ID_NUMPUBLIE_S,
                    GROUP_CONCAT(distinct concat(AUTEUR.`PRENOM`, ' ', AUTEUR.`NOM`) order by ORDRE SEPARATOR ',&nbsp;' ) AS NUMERO_NOM,
                    REVUE.TYPEPUB aS NUMERO_TYPEPUB,
                    REVUE.URL_REWRITING AS REVUE_URL_REWRITING

                    FROM NUMERO

                    LEFT JOIN REVUE ON `NUMERO`.`ID_REVUE` = REVUE.ID_REVUE
                    LEFT JOIN PORTAIL
                    ON(PORTAIL.ID_PORTAIL = NUMERO.ID_PORTAIL)
                    LEFT JOIN AUTEUR_ART ON (AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` AND ID_ARTICLE = '')
                    LEFT JOIN AUTEUR ON (AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
                    WHERE  NUMERO.ID_REVUE=?
                    AND NUMERO.STATUT=1 ";
            if ($filterAnnee != null) {
                //$sql .= "AND NUMERO.ANNEE <= " . $filterAnnee;
                $sql .= "AND NUMERO.ANNEE = ?";
            }
            $sql .= " GROUP BY NUMERO.ID_NUMPUBLIE ORDER BY NUMERO.`DATE_PARUTION` DESC, NUMERO.NUMERO DESC ";
            if($limitFrom != null && !is_numeric($limitFrom)){
                $limitFrom=0;
            }
            if ($limitFrom >= 0 && $limitTo > 0) {
                $sql .= " LIMIT " . $limitFrom . "," . $limitTo;
                //$sql .= " LIMIT ?,?";
            }
            if ($filterAnnee != null) {
                $numeros = $this->executerRequete($sql, array($id,$filterAnnee));
            }else{
                $numeros = $this->executerRequete($sql, array($id));
            }
            $fetched = $numeros->fetchAll(PDO::FETCH_ASSOC);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    public function isCNRSRevue($idRevue) {
        $sql = "SELECT IF(EXISTS(SELECT * FROM NUMERO WHERE TYPE_NUMPUBLIE = '5' AND ID_REVUE = ? LIMIT 1),1,0) AS result";
        $isCNRS = $this->executerRequete($sql, array($idRevue));
        $fetched = $isCNRS->fetch()[0];
        return $fetched;
    }

    public function countNumeroRevuesById($idRevue) {
        $redisKey = 'countNumeroRevuesById/' . $idRevue;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT COUNT(*)
            FROM NUMERO
                WHERE  NUMERO.ID_REVUE=? AND NUMERO.STATUT=1";
            $numeros = $this->executerRequete($sql, array($idRevue));
            $fetched = $numeros->fetch()[0];
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    public function getRevuesByUrl($urlRewrite, $idNumPublie = null, $typepub = 1) {
        /* $redisKey = 'getRevuesByUrl/'.$urlRewrite.'/'.$idNumPublie.'/'.$typepub;
          if($this->redis != null && $this->redis->exists($redisKey)){
          return json_decode($this->redis->get($redisKey),true);
          }else{ */
        if ($idNumPublie == null) {
            $sql = "SELECT  REVUE.AFFILIATION,
                            REVUE.ID_REVUE_S ,
                            REVUE.ID_REVUE,
                            REVUE.CONFIG_ARTICLE,
                            REVUE.TITRE,
                            REVUE.STITRE,
                            REVUE.ISSN,
                            REVUE.ISSN_NUM ,
                            REVUE.PERIODICITE,
                            REVUE.WEB,
                            REVUE.ID_EDITEUR,
                            EDITEUR.NOM_EDITEUR,
                            REVUE.URL_REWRITING,
                            REVUE.URL_REWRITING_EN,
                            REVUE.TYPEPUB,
                            REVUE.ABO_ANNEE,
                            REVUE.ACHAT_NUMERO,
                            REVUE.ACHAT_NUMERO_ELEC,
                            REVUE.ACHAT_ARTICLE,
                            REVUE.GRACE,
                            REVUE.SAVOIR_PLUS,
                            REVUE.SAVOIR_PLUS_EN,
                            REVUE.REVUE_COURANTE,
                            REVUE.REVUE_PRECEDENTE,
                            REVUE.SOAP,
                            REVUE.STATUT,
                            REVUE.MOVINGWALL,
                            REVUE.ACCES_EN_LIGNE,
                            TBL2.ID_NUMPUBLIE,
                            TBL2.ANNEE,
                            TBL2.NUMERO,
                            TBL2.DATE_MISEENLIGNE as NUMERO_DATE_MISEENLIGNE,
                            REVUE.BENEFICIAIRE,
                            REVUE.DISCIPLINE
                    FROM REVUE  LEFT JOIN  EDITEUR
                            ON ( REVUE.ID_EDITEUR=EDITEUR.ID_EDITEUR) ";

            $sql .= "JOIN (SELECT  tt.ID_NUMPUBLIE, tt.TITRE, tt.ID_REVUE,tt.DATE_PARUTION, tt.ANNEE, tt.NUMERO, tt.DATE_MISEENLIGNE FROM  NUMERO tt
                                INNER JOIN  (SELECT DISTINCT NUMERO.ID_REVUE,   MAX(NUMERO.`DATE_PARUTION`) AS MaxDateTime FROM NUMERO WHERE STATUT = 1 GROUP BY NUMERO.`ID_REVUE`) groupedtt
                                    ON tt.ID_REVUE = groupedtt.ID_REVUE AND tt.DATE_PARUTION = groupedtt.MaxDateTime
                            ) AS TBL2
                            ON (TBL2.ID_REVUE=REVUE.`ID_REVUE`)";

            /* $sql .= " LEFT JOIN(SELECT ID_NUMPUBLIE, TITRE, ID_REVUE, DATE_PARUTION, ANNEE, NUMERO, STATUT FROM NUMERO ) AS TBL2"
              . " ON (REVUE.ID_REVUE=TBL2.ID_REVUE AND TBL2.STATUT = 1 AND TBL2.`DATE_PARUTION` = (SELECT MAX(DATE_PARUTION) FROM NUMERO N2 WHERE N2.STATUT = 1 AND N2.ID_REVUE = TBL2.ID_REVUE))";
             */

            $sql .= "WHERE URL_REWRITING=? "
                    . ($typepub != null ? ("AND TYPEPUB" . (strpos($typepub, ',') ? (" IN (" . $typepub . ")") : ("=" . $typepub))) : "");
            $revue = $this->executerRequete($sql, array($urlRewrite));
        } else {
            $sql = "SELECT REVUE.AFFILIATION as REVUE_AFFILIATION,
                            REVUE.ID_REVUE_S as REVUE_ID_REVUE_S ,
                        REVUE.ID_REVUE as REVUE_ID_REVUE,
                        REVUE.CONFIG_ARTICLE as REVUE_CONFIG_ARTICLE,
                        REVUE.TITRE as REVUE_TITRE,
                            REVUE.STITRE,
                            REVUE.ISSN,
                        REVUE.ISSN_NUM  as REVUE_ISSN_NUM ,
                        REVUE.PERIODICITE,
                        REVUE.WEB as REVUE_WEB,
                        REVUE.ID_EDITEUR as REVUE_ID_EDITEUR,
                            REVUE.ABO_ANNEE AS REVUE_ABO_ANNEE,
                            REVUE.ACHAT_NUMERO,
                            REVUE.ACHAT_NUMERO_ELEC,
                            REVUE.ACHAT_ARTICLE,
                            REVUE.TYPEPUB,
                            REVUE.GRACE,
                            REVUE.SAVOIR_PLUS,
                            REVUE.SAVOIR_PLUS_EN,
                            REVUE.URL_REWRITING_EN,
                            REVUE.REVUE_COURANTE,
                            REVUE.REVUE_PRECEDENTE,
                            REVUE.SOAP,
                            REVUE.STATUT,
                            REVUE.MOVINGWALL,
                            REVUE.ACCES_EN_LIGNE,
                        EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,
                        EDITEUR.VILLE as EDITEUR_VILLE,
                        EDITEUR.PAYS as EDITEUR_PAYS,
                            REVUE.URL_REWRITING as REVUE_URL_REWRITING,
                        TBL1.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
                        TBL1.VOLUME as NUMERO_VOLUME   ,
                            TBL1.ANNEE as NUMERO_ANNEE,
                        TBL1.NUMERO as NUMERO_NUMERO,
                            TBL1.TITRE as NUMERO_TITRE ,
                            TBL1.SOUS_TITRE as NUMERO_SOUS_TITRE ,
                            TBL1.NUMEROA as NUMERO_NUMEROA,
                            TBL1.NB_PAGE as NUMERO_NB_PAGE,
                            TBL1.ISBN as NUMERO_ISBN,
                            TBL1.EAN as NUMERO_EAN,
                            TBL1.MEMO as NUMERO_MEMO,
                            TBL1.DATE_MISEENLIGNE as NUMERO_DATE_MISEENLIGNE,
                            REVUE.BENEFICIAIRE,
                            REVUE.DISCIPLINE
                    FROM REVUE LEFT JOIN EDITEUR ON ( REVUE.ID_EDITEUR=EDITEUR.ID_EDITEUR)
                    INNER JOIN NUMERO TBL1 ON ( TBL1.ID_REVUE=REVUE.ID_REVUE)
                        WHERE TBL1.ID_NUMPUBLIE=?"
                    . ($typepub != null ? ("AND TYPEPUB" . (strpos($typepub, ',') ? (" IN (" . $typepub . ")") : ("=" . $typepub))) : "");

            $revue = $this->executerRequete($sql, array($idNumPublie));
        }
        $fetched = $revue->fetchAll(PDO::FETCH_ASSOC);
        /* if($this->redis != null){
          $this->redis->setex($redisKey, $fetched);
          } */
        return $fetched;
        //}
    }

    public function getRevuesById($IdRevue) {
        /* $redisKey = 'getRevuesById/'.$IdRevue;
          if($this->redis != null && $this->redis->exists($redisKey)){
          return json_decode($this->redis->get($redisKey),true);
          }else{ */
        //$sql = "SELECT * FROM REVUE WHERE ID_REVUE = ?";
        $sql = "SELECT REVUE.AFFILIATION,
                REVUE.ID_REVUE_S,
                REVUE.ID_REVUE,
                REVUE.CONFIG_ARTICLE,
                REVUE.TITRE,
                REVUE.STITRE,
                REVUE.ISSN,
                REVUE.ISSN_NUM ,
                REVUE.PERIODICITE,
                REVUE.WEB,
                REVUE.ID_EDITEUR,
                EDITEUR.NOM_EDITEUR,
                REVUE.URL_REWRITING,
                REVUE.URL_REWRITING_EN,
                REVUE.TYPEPUB,
                REVUE.ABO_ANNEE,
                REVUE.ACHAT_NUMERO,
                REVUE.ACHAT_NUMERO_ELEC,
                REVUE.ACHAT_ARTICLE,
                REVUE.GRACE,
                REVUE.SAVOIR_PLUS,
                REVUE.SAVOIR_PLUS_EN,
                REVUE.REVUE_COURANTE,
                REVUE.REVUE_PRECEDENTE,
                REVUE.LIBELLE,
                REVUE.STATUT,
                REVUE.MOVINGWALL,
                REVUE.ACCES_EN_LIGNE,
                TBL2.ID_NUMPUBLIE,
                TBL2.ANNEE,TBL2.NUMERO,
                REVUE.DISCIPLINE
            FROM REVUE
                LEFT JOIN EDITEUR ON ( REVUE.ID_EDITEUR=EDITEUR.ID_EDITEUR)
                JOIN (
                    SELECT
                        tt.ID_NUMPUBLIE,
                        tt.TITRE,
                        tt.ID_REVUE,
                        tt.DATE_PARUTION,
                        tt.ANNEE,
                        tt.NUMERO
                    FROM NUMERO tt
                    INNER JOIN (
                        SELECT DISTINCT NUMERO.ID_REVUE,   MAX(NUMERO.`DATE_PARUTION`) AS MaxDateTime
                        FROM NUMERO
                        WHERE STATUT = 1
                        GROUP BY NUMERO.`ID_REVUE`
                    ) groupedtt
                    ON tt.ID_REVUE = groupedtt.ID_REVUE AND tt.DATE_PARUTION = groupedtt.MaxDateTime
                ) AS TBL2 ON (TBL2.ID_REVUE=REVUE.`ID_REVUE`)
            WHERE REVUE.ID_REVUE=? ";
        $revue = $this->executerRequete($sql, array($IdRevue));
        $fetched = $revue->fetchAll(PDO::FETCH_ASSOC);
        /* if($this->redis != null){
          $this->redis->setex($redisKey, $fetched);
          } */
        return $fetched;
        //}
    }

    // Récupère la liste des Numéros d'une revue
    // On converti le champ numéro en décimal afin de trier dans l'odre (sinon 1, 12, 2, ...)
    public function getNumeroInOrder($idRevue) {
        $sql = "SELECT N.ID_NUMPUBLIE, N.NUMERO, N.ANNEE, R.URL_REWRITING
                FROM NUMERO as N
                INNER JOIN REVUE as R ON R.ID_REVUE = N.ID_REVUE
                WHERE N.ID_REVUE = ? AND N.STATUT = '1'
                ORDER BY N.ANNEE asc, CONVERT(N.NUMERO, decimal) asc";
        $numeros = $this->executerRequete($sql, array($idRevue));
        return $numeros->fetchAll(PDO::FETCH_GROUP);
    }

    public function getSousDisciplines($disciplinePos) {
        $redisKey = 'getSousDisciplines/' . $disciplinePos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "(SELECT URL_REWRITING,DISCIPLINE,POS_DISC FROM DISCIPLINE "
                    . "WHERE PARENT = ? and URL_REWRITING!='' )"
                    . " UNION  ( SELECT
                         `DISCIPLINE_2`.`URL_REWRITING` as URL_REWRITING
                         , `DISCIPLINE_2`.`DISCIPLINE` as DISCIPLINE
                         , `DISCIPLINE_2`.`POS_DISC`as `POS_DISC`


                    FROM
                        DISCIPLINE
    INNER JOIN `DISCIPLINE` AS `DISCIPLINE_1`
                            ON (`DISCIPLINE`.`PARENT` = `DISCIPLINE_1`.`POS_DISC`)
                        INNER JOIN `DISCIPLINE` AS `DISCIPLINE_2`
                            ON (`DISCIPLINE_1`.`POS_DISC` = `DISCIPLINE_2`.`PARENT`)
                            WHERE DISCIPLINE.POS_DISC=?);;

                    ORDER BY    DISCIPLINE";
            $sql = "SELECT URL_REWRITING,DISCIPLINE,POS_DISC FROM DISCIPLINE "
                    . "WHERE PARENT = ? and URL_REWRITING!=''  ORDER BY    DISCIPLINE";

            $sousdisciplines = $this->executerRequete($sql, array($disciplinePos));
            $fetched = $sousdisciplines->fetchAll(PDO::FETCH_ASSOC);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    public function getDsiciplineRoot($disciplinePos) {
        $redisKey = 'getDsiciplineRoot/' . $disciplinePos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "select PARENT from DISCIPLINE where DISCIPLINE.POS_DISC=?";

            $ret = $this->executerRequete($sql, array($disciplinePos))->fetch()['PARENT'];
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $ret);
            }
            return $ret;
        }
    }

    public function getDiscipline($disciplinePos) {
        $redisKey = 'getDiscipline/' . $disciplinePos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT
                        URL_REWRITING,DISCIPLINE,POS_DISC,PARENT
                    FROM
                        DISCIPLINE
                    WHERE
                        POS_DISC = ?";

            $result = $this->executerRequete($sql, array($disciplinePos));
            $fetched = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched[0]);
            }
            return $fetched[0];
        }
    }

    /** Renvoie la liste des disciplines pour un type pub et statut donnés
     *
     * @return PDOStatement La liste des disciplines
     */
    public function getDisciplines($typePub, $statut) {
        $redisKey = 'getDisciplines/' . $typePub . '/' . $statut;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            if ($typePub == null) {
                $sql = " SELECT POS_DISC,URL_REWRITING,  DISCIPLINE, DISCIPLINE_EN FROM DISCIPLINE WHERE PARENT =0";
                $sql.= " AND URL_REWRITING!=''";
                $sql.= " AND DISCIPLINE.POS_DISC IN (SELECT DISTINCT POS_DISC FROM DIS_REVUE,REVUE WHERE REVUE_COURANTE='' ";
                $sql.= " AND DIS_REVUE.ID_REVUE=REVUE.ID_REVUE AND STATUT=? ) ORDER BY DISCIPLINE";
                $disciplines = $this->executerRequete($sql, array($statut));
            } else if ($typePub == 1) {
                $sql = " SELECT POS_DISC, URL_REWRITING,  DISCIPLINE, DISCIPLINE_EN FROM DISCIPLINE WHERE PARENT =0";
                $sql.= " AND URL_REWRITING!=''";
                $sql.= " AND DISCIPLINE.POS_DISC IN (SELECT DISTINCT POS_DISC FROM DIS_REVUE,REVUE WHERE REVUE_COURANTE='' ";
                $sql.= " AND DIS_REVUE.ID_REVUE=REVUE.ID_REVUE AND TYPEPUB=? AND STATUT=? ) ORDER BY DISCIPLINE";
                $disciplines = $this->executerRequete($sql, array($typePub, $statut));
            } else if ($typePub == 3 || $typePub == 6) {
                $sql = " SELECT DISTINCT  URL_REWRITING, DISCIPLINE, DISCIPLINE_EN, DISCIPLINE.POS_DISC "
                        . "FROM DISCIPLINE JOIN (SELECT   REVUE.ID_REVUE,  DIS_REVUE.POS_DISC  AS POS_DISC, DISCIPLINE.`PARENT` AS PARENT "
                        . "                     FROM DIS_REVUE,DISCIPLINE,REVUE,NUMERO WHERE NUMERO.ID_NUMPUBLIE=DIS_REVUE.ID_REVUE "
                        . "                     AND NUMERO.ID_REVUE=REVUE.ID_REVUE "
                        . "                     AND TYPEPUB=? "
                        . "                     AND  DIS_REVUE.POS_DISC=DISCIPLINE.POS_DISC ) AS TBL1 "
                        . "                 ON ((TBL1.POS_DISC = DISCIPLINE.POS_DISC) OR(DISCIPLINE.POS_DISC=TBL1.PARENT))  "
                        . " WHERE DISCIPLINE.PARENT =0  AND URL_REWRITING!='' ORDER BY DISCIPLINE";
                $disciplines = $this->executerRequete($sql, array($typePub));
            }
            $discs = $disciplines->fetchAll(PDO::FETCH_ASSOC);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $discs);
            }
            return $discs;
        }
    }

    /** Renvoie la liste des professions
     *
     * @return PDOStatement La liste des professions
     */
    public function getProfessionList() {
        $sql = "SELECT
                    PROFESSION, PROFESSION_EN, POS_PROF, PARENT
                FROM
                    PROFESSION
                ORDER BY POS_PROF";
        $professions = $this->executerRequete($sql, array());
        return $professions->fetchAll(PDO::FETCH_ASSOC);
    }



    /** Renvoie la première lettre des contenus (revues) en fonction du typepub et de la discipline
     *
     * @return ARRAY alphabet complet avec statut pour chaque lettre si des titres existent : array('LET' => [A-Z] , 'A' => 0/1)0:non active ; 1:active ;
     * @todo : array key=> [A-Z] , value =>0/1; !! sous disciplines
     */
    public function getTitleFirstLetters($typePub, $statut, $discPos = null) {
        $redisKey = 'getTitleFirstLetters/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            if ($discPos == null) {
                $sql = "select distinct UPPER(SUBSTR(TRISHOW,1,1)) as name,'1' as value from REVUE,DIS_REVUE "
                        . "WHERE REVUE_COURANTE='' AND REVUE.ID_REVUE=DIS_REVUE.ID_REVUE AND TITRE_ABREGE!='' AND REVUE.STATUT=? AND TYPEPUB=? order by TRISHOW";
                $letters = $this->executerRequete($sql, array($statut, $typePub))->fetchAll(PDO::FETCH_KEY_PAIR);
            } else {
                $sql = "select distinct UPPER(SUBSTR(TRISHOW,1,1)) as name,'1' as value from REVUE,DIS_REVUE "
                        . "WHERE REVUE_COURANTE='' AND REVUE.ID_REVUE=DIS_REVUE.ID_REVUE AND TITRE_ABREGE!='' "
                        . "AND REVUE.STATUT=? "
                        . "AND TYPEPUB=? "
                        . "AND DIS_REVUE.POS_DISC=? "
                        . "order by TRISHOW";
                $letters = $this->executerRequete($sql, array($statut, $typePub, $discPos))->fetchAll(PDO::FETCH_KEY_PAIR);
            }
            $allLetters = $this->checkAlphabet($letters);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $allLetters);
            }
            return $allLetters;
        }
    }

    /** Renvoie la première lettre des contenus (ouvrages, encyclopédies) en fonction du typepub et de la discipline
     *
     * @return ARRAY alphabet complet avec statut pour chaque lettre si des titres existent : array('LET' => [A-Z] , 'A' => 0/1)0:non active ; 1:active ;
     * @todo : array key=> [A-Z] , value =>0/1
     */
    public function getOuvragesTitleFirstLetters($typePub, $statut, $discPos = null) {
        $redisKey = 'getOuvragesTitleFirstLetters/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            if ($discPos == null) {
                $sql = "SELECT DISTINCT UPPER(SUBSTR(NUMERO.TRISHOW,1,1))  AS name, '1' as value  FROM REVUE,NUMERO "
                        . " WHERE NUMERO.ID_REVUE=REVUE.ID_REVUE AND NUMERO.TITRE_ABREGE!=''  "
                        . " AND  NUMERO.TRISHOW <>'' "
                        . " AND TYPEPUB=? "
                        . " AND NUMERO.STATUT=?  "
                        . " ORDER BY NUMERO.TRISHOW;";
                $letters = $this->executerRequete($sql, array($typePub, $statut))->fetchAll(PDO::FETCH_KEY_PAIR);
            } else {
                if ($typePub == 3) {
                    $sql = "SELECT DISTINCT UPPER(SUBSTR(NUMERO.TRISHOW,1,1))  AS name, '1' as value "
                            . " FROM REVUE , NUMERO, DIS_REVUE "
                            . " WHERE NUMERO.ID_NUMPUBLIE = DIS_REVUE.ID_REVUE "
                            . " AND NUMERO.ID_REVUE=REVUE.ID_REVUE"
                            . " AND TYPEPUB=? "
                            . " AND NUMERO.STATUT=? "
                            . " AND (DIS_REVUE.POS_DISC=? "
                            . " OR DIS_REVUE.POS_DISC IN (SELECT POS_DISC FROM DISCIPLINE WHERE  PARENT=?)) "
                            . " ORDER BY NUMERO.TRISHOW";
                } else {
                    $sql = "SELECT DISTINCT UPPER(SUBSTR(NUMERO.TRISHOW,1,1))  AS name, '1' as value FROM REVUE , NUMERO,DIS_REVUE "
                            . " WHERE ID_NUMPUBLIE=DIS_REVUE.ID_REVUE AND NUMERO.ID_REVUE=REVUE.ID_REVUE"
                            . " AND TYPEPUB=? "
                            . " AND NUMERO.STATUT=? "
                            . " AND (DIS_REVUE.POS_DISC=? "
                            . " OR DIS_REVUE.POS_DISC IN (SELECT POS_DISC FROM DISCIPLINE WHERE  PARENT=?)) "
                            . " AND (DERNIERE_EDITION='' OR DERNIERE_EDITION IS NULL) AND NUMERO.TITRE_ABREGE!='' "
                            . " ORDER BY NUMERO.TRISHOW";
                }

                $letters = $this->executerRequete($sql, array($typePub, $statut, $discPos, $discPos))->fetchAll(PDO::FETCH_KEY_PAIR);
            }
            $allLetters = $this->checkAlphabet($letters);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $allLetters);
            }
            return $allLetters;
        }
    }

    /** Renvoie la première lettre des contenus (ouvrages, encyclopédies) en fonction du typepub et de la discipline
     *
     * @return ARRAY alphabet complet avec statut pour chaque lettre si des titres existent : array('LET' => [A-Z] , 'A' => 0/1)0:non active ; 1:active ;
     * @todo : array key=> [A-Z] , value =>0/1
     */
    public function getOuvragesAuthorsFirstLetters($typePub, $statut, $discPos = null) {
        $redisKey = 'getOuvragesAuthorsFirstLetters/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            if ($discPos == null) {
                $sql = "SELECT DISTINCT substr(AUTEUR.NOM,1,1) AS name ,'1' as value FROM REVUE , NUMERO  INNER JOIN AUTEUR_ART "
                        . " ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` ) "
                        . " INNER JOIN AUTEUR ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`) "
                        . " WHERE REVUE.STATUT=1 "
                        . " AND NUMERO.STATUT=? "
                        . " AND ID_ARTICLE='' AND NUMERO.ID_REVUE=REVUE.ID_REVUE AND REVUE.URL_REWRITING!=''"
                        . " AND NUMERO.URL_REWRITING!='' "
                        . " AND TYPEPUB=? "
                        . " ORDER BY name";
                $letters = $this->executerRequete($sql, array($statut, $typePub))->fetchAll(PDO::FETCH_KEY_PAIR);
            } else {

                $sql = "SELECT DISTINCT substr(AUTEUR.NOM,1,1) AS name ,'1' as value  FROM REVUE , NUMERO  LEFT JOIN AUTEUR_ART "
                        . " ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` ) "
                        . " LEFT JOIN AUTEUR ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`),"
                        . " DIS_REVUE "
                        . " WHERE REVUE.STATUT=1 "
                        . " AND DIS_REVUE.ID_REVUE=NUMERO.ID_NUMPUBLIE "
                        . " AND (DIS_REVUE.POS_DISC=?  OR DIS_REVUE.POS_DISC IN (SELECT POS_DISC FROM DISCIPLINE WHERE  PARENT=?)) "
                        . " AND NUMERO.STATUT=? "
                        . " AND ID_ARTICLE='' AND NUMERO.ID_REVUE=REVUE.ID_REVUE AND REVUE.URL_REWRITING!=''"
                        . " AND NUMERO.URL_REWRITING!='' "
                        . " AND TYPEPUB=? "
                        . " ORDER BY name";

                $letters = $this->executerRequete($sql, array($discPos, $discPos, $statut, $typePub))->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            $allLetters = $this->checkAlphabet2($letters);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $allLetters);
            }
            return $allLetters;
        }
    }

    /**
     * Retourne la liste des dernières publications de REVUES, peut être filtée relativement à une discipline
     * @return PDOSattement ;
     */
    public function getLastNumPublished($typePub, $statut, $posDisc=null) {
        $redisKey = 'getLastNumPublished/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        }
        $sql = '
            SELECT
                ANNEE,
                ID_NUMPUBLIE,
                ID_NUMPUBLIE_S,
                NUMERO,NUMEROA,
                NUMERO.TITRE,
                NUMERO.TITRE_ABREGE AS "NUMERO_TITRE_ABREGE",
                NUMERO.TRISHOW,
                REVUE.ID_REVUE,
                REVUE.ID_REVUE_S,
                REVUE.STITRE_EN,
                REVUE.TITRE_ABREGE,
                REVUE.TITRE_EN,
                REVUE.URL_REWRITING,
                REVUE.URL_REWRITING_EN,
                VOLUME
            FROM (
                    SELECT
                        NUMERO.ID_REVUE,
                        MAX(DATE_MISEENLIGNE) AS MAX_DATE
                    FROM NUMERO'
                        .($posDisc !== null ? ' LEFT JOIN DIS_REVUE ON NUMERO.ID_REVUE = DIS_REVUE.ID_REVUE ' : ' ')
                        .($posDisc !== null ? ' WHERE POS_DISC = :posDisc ' : ' ')
                    .'
                    GROUP BY ID_REVUE
                    ORDER BY MAX_DATE DESC
                ) GROUP_REVUE
                LEFT JOIN NUMERO ON GROUP_REVUE.ID_REVUE = NUMERO.ID_REVUE AND GROUP_REVUE.MAX_DATE = NUMERO.DATE_MISEENLIGNE
                LEFT JOIN REVUE ON GROUP_REVUE.ID_REVUE = REVUE.ID_REVUE
            WHERE
                REVUE_COURANTE = ""
                AND REVUE.TYPEPUB = :typepub
                AND REVUE.STATUT = :statut
                AND NUMERO.STATUT = :statut
                AND NUMERO.ANNEE >= YEAR(NOW()) - 1
            GROUP BY NUMERO.ID_REVUE
            ORDER BY DATE_MISEENLIGNE DESC
            LIMIT 20
        ';
        $lastNums = $this->executerRequete($sql, [
            'typepub' => $typePub,
            'statut' => $statut,
            'posDisc' => $posDisc,
        ])->fetchAll(PDO::FETCH_ASSOC);

        if ($this->redis != null) {
            $this->redis->setex($redisKey, $lastNums);
        }
        return $lastNums;
    }

    /**
     * Retourne la liste des dernières publications [OUVRAGES ou ENCYCLOPEDIES], peut être filtrée relativement à une discipline
     * @return PDOSattement
     */
    public function getOuvragesLastNumPublished($typePub, $statut, $discPos = null) {
        $redisKey = 'getOuvragesLastNumPublished/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        }

        $parseDatas = Service::get('ParseDatas');
        if ($discPos == null) {
            $sql2 = "SELECT DISTINCT  REVUE.ID_REVUE, GROUP_CONCAT(distinct concat(AUTEUR.`PRENOM`, '".$parseDatas::concat_name."', AUTEUR.`NOM`) order by ORDRE SEPARATOR '".$parseDatas::concat_authors."' ) AS NOM, "
                    . " ID_EDITEUR, NUMERO.ID_NUMPUBLIE, REVUE.TITRE_ABREGE,NUMERO.TITRE AS NUMERO_TITRE , NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE,  "
                    . " ANNEE,NUMERO,VOLUME, NUMERO.URL_REWRITING, NUMERO.ISBN, NUMERO.EAN "
                    . " FROM REVUE , NUMERO LEFT JOIN AUTEUR_ART "
                    . " ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` ) "
                    . " LEFT JOIN AUTEUR ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR` AND AUTEUR_ART.`ID_ARTICLE` = '') "
                    . " WHERE REVUE.STATUT=1 AND NUMERO.STATUT=1 AND NUMERO.ID_REVUE=REVUE.ID_REVUE AND REVUE.URL_REWRITING!='' AND NUMERO.URL_REWRITING!=''"
                    . " AND TYPEPUB=? "
                    . " AND NUMERO.ANNEE >= 2005 "
                    . " GROUP BY NUMERO.ID_NUMPUBLIE "
                    . " ORDER BY DATE_MISEENLIGNE DESC "
                    . " LIMIT 20";
            $lastNums = $this->executerRequete($sql2, array($typePub));
        } else {
            $sql2 = "SELECT DISTINCT REVUE.ID_REVUE,GROUP_CONCAT(distinct concat(AUTEUR.`PRENOM`, '".$parseDatas::concat_name."', AUTEUR.`NOM`) order by ORDRE SEPARATOR '".$parseDatas::concat_authors."' ) AS NOM, "
                    . " ID_EDITEUR, NUMERO.ID_NUMPUBLIE, REVUE.TITRE_ABREGE,NUMERO.TITRE AS NUMERO_TITRE , NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE, ANNEE,NUMERO,VOLUME, "
                    . " NUMERO.URL_REWRITING,NUMERO.ISBN "
                    . " FROM REVUE , NUMERO  LEFT JOIN AUTEUR_ART "
                    . " ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` AND AUTEUR_ART.`ID_ARTICLE` = '') "
                    . " LEFT JOIN AUTEUR ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`),"
                    . " DIS_REVUE "
                    . " WHERE REVUE.STATUT=1 "
                    //. " AND NUMERO.TYPE_NUMPUBLIE=1 "
                    . " AND DIS_REVUE.ID_REVUE=NUMERO.ID_NUMPUBLIE "
                    . " AND (DIS_REVUE.POS_DISC=?  OR DIS_REVUE.POS_DISC IN (SELECT POS_DISC FROM DISCIPLINE WHERE  PARENT=?)) "
                    . " AND NUMERO.STATUT=? "
                    . " AND NUMERO.ID_REVUE=REVUE.ID_REVUE AND REVUE.URL_REWRITING!=''"
                    . " AND NUMERO.URL_REWRITING!='' "
                    . " AND TYPEPUB=? "
                    . " AND NUMERO.ANNEE >= 2005 "
                    . " GROUP BY NUMERO.ID_NUMPUBLIE "
                    . " ORDER BY DATE_MISEENLIGNE DESC LIMIT 20";
            $lastNums = $this->executerRequete($sql2, array($discPos, $discPos, $statut, $typePub));
        }
        $fetched = $lastNums->fetchAll();
        if ($this->redis != null) {
            $this->redis->setex($redisKey, $fetched);
        }
        return $fetched;
    }

    /**
     * Retourne la valeur POSC_DISC associée à un libellé DISCIPLINE [URL_REWRITING]
     * @return int POSC_DISC ;
     */
    public function getPosDiscFromUrl($url) {
        $redisKey = 'getPosDiscFromUrl/' . $url;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT POS_DISC FROM DISCIPLINE WHERE " . Configuration::get('disciplineRewriting') . " = ?";
            $lastNums = $this->executerRequete($sql, array($url));
            $disc = $lastNums->fetch();
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $disc['POS_DISC']);
            }
            return $disc['POS_DISC'];
        }
    }

    /**
     * Retourne la valeur URL_REWRITING associée à un int POS_DISC de la table DISCIPLINES
     * @return string DISC ;
     */
    public function getUrlDiscFromPos($POS) {
        $redisKey = 'getUrlDiscFromPos/' . $POS;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT URL_REWRITING FROM DISCIPLINE  WHERE POS_DISC = ?";
            $lastNums = $this->executerRequete($sql, array($POS));
            $disc = $lastNums->fetch();
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $disc['URL_REWRITING']);
            }
            return $disc['URL_REWRITING'];
        }
    }

    /**
     * Retourne la liste des magazines avec infos du dernier numéro paru
     * @return ARRAY ;
     */
    public function getMagazines($typePub, $statut = 1, $LET = null) {
        $redisKey = 'getMagazines/' . $typePub . '/' . $statut . '/' . $LET;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT `REVUE`.ID_REVUE, REVUE.URL_REWRITING, `REVUE`.TITRE as REVUE_TITRE, TBL1.ID_NUMPUBLIE, TBL1.TITRE, TBL1.DATE_PARUTION ,TBL1.ANNEE, TBL1.NUMERO "
                    . " FROM  REVUE JOIN (SELECT  tt.ID_NUMPUBLIE, tt.TITRE, tt.ID_REVUE,tt.DATE_PARUTION, tt.ANNEE, tt.NUMERO FROM  NUMERO tt"
                    . " INNER JOIN  (SELECT DISTINCT NUMERO.ID_REVUE,   MAX(NUMERO.`DATE_PARUTION`) AS MaxDateTime FROM NUMERO WHERE STATUT = 1 GROUP BY NUMERO.`ID_REVUE`) groupedtt  "
                    . " ON tt.ID_REVUE = groupedtt.ID_REVUE AND tt.DATE_PARUTION = groupedtt.MaxDateTime   )  AS TBL1    "
                    . " ON (TBL1.ID_REVUE=REVUE.`ID_REVUE`) "
                    . " WHERE REVUE.`TYPEPUB`=?  "
                    . " AND TBL1.NUMERO<>'' "
                    //. " ORDER BY REVUE.TRISHOW ;";
                    . "ORDER BY field (REVUE.ID_REVUE, 'AE', 'DAE', 'AI', 'BOOKS', 'SNI_LHIST', 'SNI_LML', 'SNI_MDV', 'SNI_LMD', 'SH', 'GDSH');";

            $lastNums = $this->executerRequete($sql, array($typePub))->fetchAll();
            $mesMagazines = array();
            foreach ($lastNums as $lastNum) {
                $mesMagazines[$lastNum['ID_REVUE']] = $lastNum;
            }
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $mesMagazines);
            }
            return $mesMagazines;
        }
    }

    /*
     * Compte le nombre d'ouvrages, éventuellement filtré sur une discipline ou une lettre
     */

    public function countOuvrages($typePub, $statut, $discPos = null, $editeur = null, $collection = null) {
        $redisKey = 'countOuvrages/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $join  = "";
            $where = "";

            if ($discPos != null) {
                $where .= " AND (DIS_REVUE.POS_DISC= ? OR DIS_REVUE.POS_DISC IN (SELECT POS_DISC FROM DISCIPLINE WHERE  PARENT= ?)) ";
            }
            // Définition de l'éditeur, si il existe
            if($editeur != null) {
                //$where .= " AND REVUE.ID_EDITEUR = ?";
                if(($editeur == "1") || ($editeur == "0")) {$where .= " and EDITEUR.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
                else {$where .= " and REVUE.ID_EDITEUR = ?";}
                // Jointure
                $join .= "LEFT JOIN EDITEUR on REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR ";
            }
            // Définition de la collection, si elle existe
            if($collection != null) {
                $where .= " AND REVUE.ID_REVUE = ?";
            }

            /*$sql = "SELECT COUNT(DISTINCT ID_NUMPUBLIE)
                    FROM NUMERO, REVUE " .
                    ", DIS_REVUE, EDITEUR " .
                    "WHERE REVUE.ID_REVUE = NUMERO.ID_REVUE " .
                    "AND NUMERO.ID_NUMPUBLIE=DIS_REVUE.ID_REVUE " .
                    "AND NUMERO.TITRE_ABREGE!=''
                    AND REVUE_COURANTE=''
                    AND REVUE.STATUT=1 AND NUMERO.STATUT=?
                    AND (DERNIERE_EDITION='' OR DERNIERE_EDITION IS NULL)
                    AND REVUE.URL_REWRITING!=''  AND NUMERO.URL_REWRITING!=''
                    AND TYPEPUB=?"
                    . $where;*/
            $sql = "SELECT COUNT(DISTINCT ID_NUMPUBLIE)
                    FROM NUMERO " .
                    "LEFT JOIN REVUE on REVUE.ID_REVUE = NUMERO.ID_REVUE " .
                    "LEFT JOIN DIS_REVUE on NUMERO.ID_NUMPUBLIE = DIS_REVUE.ID_REVUE ".
                    $join.
                    "WHERE REVUE.ID_REVUE = NUMERO.ID_REVUE " .
                    "AND NUMERO.ID_NUMPUBLIE=DIS_REVUE.ID_REVUE " .
                    "AND NUMERO.TITRE_ABREGE!=''
                    AND REVUE_COURANTE=''
                    AND REVUE.STATUT=1 AND NUMERO.STATUT=?
                    AND (DERNIERE_EDITION='' OR DERNIERE_EDITION IS NULL)
                    AND REVUE.URL_REWRITING!=''  AND NUMERO.URL_REWRITING!=''
                    AND TYPEPUB=?"
                    . $where;

            // Construction des paramètres
            $reqParams      = array();
            $reqParams[]    = $statut;
            $reqParams[]    = $typePub;
            if($discPos != null) {$reqParams[] = $discPos;$reqParams[] = $discPos;}
            if($editeur != null) {$reqParams[] = $editeur;}
            if($collection != null) {$reqParams[] = $collection;}

            // Execution
            $count = $this->executerRequete($sql, $reqParams)->fetch();

            // Redis
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $count[0]);
            }
            return $count[0];
        }
    }

    /**
     * Retourne la liste des publications [OUVRAGES ou ENCYCLOPEDIES], peut être filtrée relativement à une discipline et/ou une lettre du titre
     * @return ARRAY ;
     */
    public function getOuvragesByTitle($typePub, $statut, $LET, $discPos = null, $editeur = null, $includeNoDis = false, $collection = null, $from = null, $to = null) {
        $redisKey = 'getOuvragesByTitle/' . $typePub . '/' . $statut . '/' . $discPos . '/' . $LET . '/' . $editeur . '/' . $includeNoDis . '/' . $collection . '/' . $from . '/' . $to;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            if ($discPos != null) {
                $sql = "SELECT DISTINCT REVUE.ID_REVUE, REVUE.TYPEPUB, REVUE.REVUE_PRECEDENTE, "
                        . " GROUP_CONCAT(distinct concat(AUTEUR.`PRENOM`, ':', AUTEUR.`NOM`) order by ORDRE SEPARATOR ',&nbsp;' ) AS NOM, "
                        . " GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR) ORDER BY ORDRE SEPARATOR ' , ' ) AS NOM_AUTEURS, "
                        . " NUMERO.ID_NUMPUBLIE, REVUE.TITRE_ABREGE,NUMERO.TITRE, NUMERO.TITRE AS NUMERO_TITRE , NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE, "
                        . " ANNEE,NUMERO,VOLUME, REVUE.ISSN, REVUE.ISSN_NUM, REVUE.PERIODICITE, NUMERO.URL_REWRITING, NUMERO.ISBN, NUMERO.EAN, "
                        . " EDITEUR.ID_EDITEUR as EDITEUR_ID_EDITEUR, EDITEUR.NOM_EDITEUR AS EDITEUR_NOM_EDITEUR,EDITEUR.VILLE as EDITEUR_VILLE, EDITEUR.PAYS as EDITEUR_PAYS "
                        . " FROM REVUE LEFT JOIN EDITEUR ON(REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR),"
                        . ($includeNoDis ? "" : " DIS_REVUE, ")
                        . "NUMERO LEFT JOIN AUTEUR_ART ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` ) "
                        . " LEFT JOIN AUTEUR ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR` AND AUTEUR_ART.`ID_ARTICLE` = '') "
                        . " WHERE REVUE.STATUT=1 "
                        . ($includeNoDis ? "" : " AND DIS_REVUE.ID_REVUE=NUMERO.ID_NUMPUBLIE     ")
                        . " AND (DIS_REVUE.POS_DISC=?   OR DIS_REVUE.POS_DISC IN (SELECT POS_DISC FROM DISCIPLINE WHERE  PARENT=?)) "
                        . " AND NUMERO.STATUT=? AND TYPEPUB=?"
                        . " AND (DERNIERE_EDITION='' OR DERNIERE_EDITION IS NULL) "
                        . " AND NUMERO.ID_REVUE=REVUE.ID_REVUE  AND REVUE.URL_REWRITING!=''  AND NUMERO.URL_REWRITING!='' ";

                // Définition de l'éditeur, si il existe
                //. ($editeur != null ? (" AND REVUE.ID_EDITEUR = ?") : "")
                if($editeur != null) {
                    if(($editeur == "1") || ($editeur == "0")) {$sql .= " and EDITEUR.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
                    else {$sql .= " and REVUE.ID_EDITEUR = ?";}
                }
                // Définition de la collection, si elle existe
                //$sql .= ($collection != null ? (" AND REVUE.ID_REVUE = ?") : "")
                if($collection != null) {
                    $sql .= " AND REVUE.ID_REVUE = ?";
                }
                // Définition de la xxx, si elle existe
                if ($LET != 'ALL') {
                    $sql.="AND NUMERO.TRISHOW like ? ";
                }
                // Order & Group By
                $sql .= "GROUP BY NUMERO.ID_NUMPUBLIE ORDER BY NUMERO.TRISHOW ";
                // LIMITE
                if($from !== null) {
                    $sql .= " LIMIT $from, $to";
                }

                // Construction des paramètres
                $reqParams      = array();
                $reqParams[]    = $discPos;
                $reqParams[]    = $discPos;
                $reqParams[]    = $statut;
                $reqParams[]    = $typePub;
                if($editeur != null) {$reqParams[] = $editeur;}
                if($collection != null) {$reqParams[] = $collection;}
                if($LET != 'ALL') {$reqParams[] = ($LET.'%');}

                // Execution
                $lastNums = $this->executerRequete($sql, $reqParams);

                //$lastNums = $this->executerRequete($sql, array($discPos, $discPos, $statut, $typePub));
                /*if (($LET != 'ALL')) {
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($discPos, $discPos, $statut, $editeur, $typePub, ($LET.'%')));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($discPos, $discPos, $statut, $typePub, ($LET.'%')));
                    }
                }else{
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($discPos, $discPos, $statut, $editeur, $typePub));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($discPos, $discPos, $statut, $typePub));
                    }
                }*/
            } else {
                $sql = "SELECT DISTINCT  REVUE.ID_REVUE, REVUE.TYPEPUB, "
                        . " GROUP_CONCAT(distinct concat(AUTEUR.`PRENOM`, ':', AUTEUR.`NOM`) order by ORDRE SEPARATOR ',&nbsp;' ) AS NOM,"
                        . " GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR) ORDER BY ORDRE SEPARATOR ' , ' ) AS NOM_AUTEURS, "
                        . " NUMERO.ID_NUMPUBLIE, REVUE.TITRE_ABREGE,NUMERO.TITRE, NUMERO.TITRE AS NUMERO_TITRE , NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE, "
                        . " ANNEE, NUMERO, VOLUME, REVUE.ISSN, REVUE.ISSN_NUM, REVUE.PERIODICITE, "
                        . " NUMERO.URL_REWRITING, NUMERO.ISBN, NUMERO.EAN, "
                        . " EDITEUR.ID_EDITEUR as EDITEUR_ID_EDITEUR, EDITEUR.NOM_EDITEUR AS EDITEUR_NOM_EDITEUR,EDITEUR.VILLE as EDITEUR_VILLE, EDITEUR.PAYS as EDITEUR_PAYS "
                        . " FROM REVUE  LEFT JOIN EDITEUR  ON(REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR), "
                        . ($includeNoDis ? "" : " DIS_REVUE, ")
                        . "NUMERO LEFT JOIN AUTEUR_ART ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` AND AUTEUR_ART.`ID_ARTICLE` = '' ) "
                        . " LEFT JOIN AUTEUR  ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`) "
                        . " WHERE REVUE.STATUT=1 "
                        . ($includeNoDis ? "" : " AND DIS_REVUE.ID_REVUE=NUMERO.ID_NUMPUBLIE     ")
                        . " AND NUMERO.STATUT=1  AND NUMERO.ID_REVUE=REVUE.ID_REVUE AND REVUE.URL_REWRITING!='' "
                        . " AND NUMERO.URL_REWRITING!='' "
                        . " AND (DERNIERE_EDITION='' OR DERNIERE_EDITION IS NULL) AND TYPEPUB=? ";
                // Définition de la xxx, si elle existe
                if ($LET != 'ALL') {
                    $sql.= "  AND NUMERO.TRISHOW like ? ";
                }
                // Définition de l'éditeur, si il existe
                if($editeur != null) {
                    if(($editeur == "1") || ($editeur == "0")) {$sql .= " and EDITEUR.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
                    else {$sql .= " and REVUE.ID_EDITEUR = ?";}
                }
                // Définition de la collection, si elle existe
                if($collection != null) {
                    $sql .= " AND REVUE.ID_REVUE = ?";
                }
                // Order & Group By
                $sql .= "GROUP BY NUMERO.ID_NUMPUBLIE ORDER BY NUMERO.TRISHOW ";

                // LIMITE
                if($from !== null) {
                    $sql .= " LIMIT $from, $to";
                }

                // Construction des paramètres
                $reqParams      = array();
                $reqParams[]    = $typePub;
                if($LET != 'ALL') {$reqParams[] = ($LET.'%');}
                if($editeur != null) {$reqParams[] = $editeur;}
                if($collection != null) {$reqParams[] = $collection;}

                // Execution
                $lastNums = $this->executerRequete($sql, $reqParams);

                //$lastNums = $this->executerRequete($sql, array($typePub));
                /*if (($LET != 'ALL')) {
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($typePub, ($LET.'%'), $editeur));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($typePub, ($LET.'%')));
                    }
                }else{
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($typePub, $editeur));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($typePub));
                    }
                }*/
            }
            $fetched = $lastNums->fetchAll();
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    /**
     * Retourne la liste des publications [OUVRAGES ou ENCYCLOPEDIES], peut être filtrée relativement à une discipline et/ou une lettre du titre
     * @return ARRAY ;
     */
    public function getOuvragesByAuthor($typePub, $statut, $LET, $discPos = null) {
        $redisKey = 'getOuvragesByAuthor/' . $typePub . '/' . $statut . '/' . $discPos . '/' . $LET;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            if ($discPos != null) {
                $sql = "SELECT DISTINCT REVUE.ID_REVUE, GROUP_CONCAT(distinct concat(AUTEUR.`PRENOM`, ':', AUTEUR.`NOM` , ':', AUTEUR.`ID_AUTEUR`) order by ORDRE SEPARATOR ',&nbsp;' ) AS NOM_AUTEURS, "
                        . " NUMERO.ID_NUMPUBLIE, REVUE.TITRE_ABREGE,NUMERO.TITRE AS NUMERO_TITRE , NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE, "
                        . " ANNEE,NUMERO,VOLUME, NUMERO.URL_REWRITING, NUMERO.ISBN, NOM_EDITEUR, NUMERO.EAN "
                        . " FROM REVUE LEFT JOIN EDITEUR ON(REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR),"
                        . " DIS_REVUE, NUMERO LEFT JOIN AUTEUR_ART ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` ) "
                        . " LEFT JOIN AUTEUR ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`) "
                        . " WHERE REVUE.STATUT=1 AND DIS_REVUE.ID_REVUE=NUMERO.ID_NUMPUBLIE     "
                        . " AND (DIS_REVUE.POS_DISC=?   OR DIS_REVUE.POS_DISC IN (SELECT POS_DISC FROM DISCIPLINE WHERE  PARENT=?)) "
                        . " AND NUMERO.STATUT=?"
                        . " AND ID_ARTICLE='' AND NUMERO.ID_REVUE=REVUE.ID_REVUE  AND REVUE.URL_REWRITING!=''  AND NUMERO.URL_REWRITING!='' "
                        . " AND TYPEPUB=? "
                        . " AND (DERNIERE_EDITION='' OR DERNIERE_EDITION IS NULL) "
                        . " AND AUTEUR.NOM like ? GROUP BY NUMERO.ID_NUMPUBLIE ORDER BY AUTEUR.NOM ";

                $lastNums = $this->executerRequete($sql, array($discPos, $discPos, $statut, $typePub,($LET.'%')));
            } else {
                $sql = "SELECT DISTINCT  REVUE.ID_REVUE, GROUP_CONCAT(distinct concat(AUTEUR.`PRENOM`, ':', AUTEUR.`NOM` , ':', AUTEUR.`ID_AUTEUR`) order by ORDRE SEPARATOR ',&nbsp;' ) AS NOM_AUTEURS,"
                        . " NUMERO.ID_NUMPUBLIE, REVUE.TITRE_ABREGE,NUMERO.TITRE AS NUMERO_TITRE , NUMERO.TITRE_ABREGE AS NUMERO_TITRE_ABREGE, ANNEE,NUMERO,VOLUME,   "
                        . " NUMERO.URL_REWRITING, NUMERO.ISBN, NOM_EDITEUR, NUMERO.EAN "
                        . " FROM REVUE  LEFT JOIN EDITEUR  ON(REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR), "
                        . " NUMERO LEFT JOIN AUTEUR_ART ON(AUTEUR_ART.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` ) "
                        . " LEFT JOIN AUTEUR  ON(AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`) "
                        . " WHERE REVUE.STATUT=1   "
                        . " AND (DERNIERE_EDITION='' OR DERNIERE_EDITION IS NULL) "
                        . " AND NUMERO.STATUT=1  AND ID_ARTICLE='' AND NUMERO.ID_REVUE=REVUE.ID_REVUE AND REVUE.URL_REWRITING!='' "
                        . " AND NUMERO.URL_REWRITING!='' "
                        . " AND TYPEPUB=? "
                        . " AND AUTEUR.NOM like ? "
                        . " GROUP BY NUMERO.ID_NUMPUBLIE "
                        . " ORDER BY AUTEUR.NOM ";
                $lastNums = $this->executerRequete($sql, array($typePub,($LET.'%')));
            }
            $fetched = $lastNums->fetchAll();
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    /*
     *  Renvoie le nombre de revues, éventuellement filtré sur une discipline ou une lettre
     */
    public function countRevues($typePub, $statut, $discPos = null, $LET = null, $editeur = null, $includeNoDisc = false) {
        $redisKey = 'countRevues/' . $typePub . '/' . $statut . '/' . $discPos . '/' . $LET . '/' . $editeur . '/' . $includeNoDisc;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $where = "";
            if ($discPos != null) {
                $where = " AND DIS_REVUE.POS_DISC= ?";
            }
            /*if ($LET != null) {
                $where.=" AND TRISHOW like '$LET%' ";
            }*/
            if ($editeur != null) {
                $where.=" AND ID_EDITEUR = ? ";
            }
            $sql = "SELECT COUNT(DISTINCT REVUE.ID_REVUE)
                    FROM " . ($includeNoDisc ? "" : "DIS_REVUE, ") .
                    " REVUE " .
                    //" JOIN(SELECT ID_REVUE, ID_NUMPUBLIE, DATE_PARUTION, STATUT FROM NUMERO ) AS TBL2" .
                    //" ON (REVUE.ID_REVUE=TBL2.ID_REVUE AND TBL2.`DATE_PARUTION` = (SELECT MAX(DATE_PARUTION) FROM NUMERO N2 WHERE N2.STATUT = 1 AND N2.ID_REVUE = TBL2.ID_REVUE))".
                    "JOIN (SELECT  tt.ID_NUMPUBLIE, tt.TITRE, tt.ID_REVUE,tt.DATE_PARUTION, tt.ANNEE, tt.NUMERO FROM  NUMERO tt
                                INNER JOIN  (SELECT DISTINCT NUMERO.ID_REVUE,   MAX(NUMERO.`DATE_PARUTION`) AS MaxDateTime FROM NUMERO WHERE STATUT = 1 GROUP BY NUMERO.`ID_REVUE`) groupedtt
                                    ON tt.ID_REVUE = groupedtt.ID_REVUE AND tt.DATE_PARUTION = groupedtt.MaxDateTime
                            ) AS TBL2
                            ON (TBL2.ID_REVUE=REVUE.`ID_REVUE`)" .
                    "WHERE REVUE_COURANTE='' " .
                    ($includeNoDisc ? "" : "AND REVUE.ID_REVUE=DIS_REVUE.ID_REVUE ") .
                "AND TITRE_ABREGE!=''
                    AND REVUE.STATUT=?
                    AND TYPEPUB=?"
                    . $where;

            if($discPos != null) {
                if ($editeur != null) {
                    $count = $this->executerRequete($sql, array($statut, $typePub,$discPos,$editeur))->fetch();
                }else{
                    $count = $this->executerRequete($sql, array($statut, $typePub,$discPos))->fetch();
                }
            }else{
                if ($editeur != null) {
                    $count = $this->executerRequete($sql, array($statut, $typePub,$editeur))->fetch();
                }else{
                    $count = $this->executerRequete($sql, array($statut, $typePub))->fetch();
                }
            }
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $count[0]);
            }
            return $count[0];
        }
    }

    /*
     * Renvoie le nombre total de revues, éventuellement filtré sur une discipline ou une lettre
     * La méthode countRevues ne semble pas renvoyer le bon résultat, cette version simplifiée semble plus adéquate.
     * NB : countRevues() n'était appelée que dans le ControleurAccueil avec au maximum 3 paramètres.
     */
    public function countTotalRevues($typePub, $statut, $discPos = null, $editeur = null) {
        $redisKey = 'countTotalRevues/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            // Init
            $where = "";

            /// Définition de la discipline, si elle existe
            if($discPos != null) {$where .= " and POS_DISC = ?";}
            // Définition de l'éditeur, si il existe
            if($editeur != null) {
                if(($editeur == "1") || ($editeur == "0")) {$where .= " and EDITEUR.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
                else {$where .= " and REVUE.ID_EDITEUR = ?";}
            }

            // Requête
            $sql = "SELECT COUNT(DISTINCT REVUE.ID_REVUE)
                    FROM REVUE
                    INNER JOIN DIS_REVUE
                    ON DIS_REVUE.ID_REVUE = REVUE.ID_REVUE
                    INNER JOIN EDITEUR
                    ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
                    WHERE REVUE_COURANTE = '' AND STATUT = ? and TYPEPUB = ? ".$where;


            // Construction des paramètres
            $reqParams      = array();
            $reqParams[]    = $statut;
            $reqParams[]    = $typePub;
            if($discPos != null) {$reqParams[] = $discPos;}
            if($editeur != null) {$reqParams[] = $editeur;}

            // Execution
            $count = $this->executerRequete($sql, $reqParams)->fetch();

            // Redis
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $count[0]);
            }

            // Retour
            return $count[0];
        }
    }

    /**
     * Retourne la liste des publications [REVUES], peut être filtrée relativement à une discipline et/ou une lettre du titre voire un éditeur
     * @return PDOStatement ;
     */
    public function getRevuesByTitle($typePub, $statut, $LET, $discPos = null, $editeur = null, $includeNoDis = false, $from = null, $to = null) {
        $redisKey = 'getRevuesByTitle/' . $typePub . '/' . $statut . '/' . $discPos . '/' . $LET . '/' . $editeur . '/' . $includeNoDis . '/' . $from . '/' . $to;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            if ($discPos == null) {
                $sql = "SELECT DISTINCT REVUE.ID_REVUE, URL_REWRITING, REVUE.TITRE, REVUE.ID_EDITEUR, TBL1.NOM_EDITEUR,  TBL2.ID_NUMPUBLIE, REVUE.TRISHOW, "
                        . " REVUE.TYPEPUB, REVUE.ISSN, REVUE.ISSN_NUM, REVUE.PERIODICITE, REVUE.PERIODICITE_EN, REVUE.STITRE, REVUE.SAVOIR_PLUS, REVUE.SAVOIR_PLUS_EN, REVUE.REVUE_PRECEDENTE "
                        . " FROM "
                        . ($includeNoDis ? "" : "DIS_REVUE, ")
                        . " REVUE "
                        . " LEFT JOIN(SELECT  DISTINCT NOM_EDITEUR,ID_EDITEUR,TYPE FROM   EDITEUR  ) AS TBL1      "
                        . " ON (REVUE.`ID_EDITEUR`=TBL1.ID_EDITEUR) "
                        //. " JOIN(SELECT   ID_REVUE, ID_NUMPUBLIE, DATE_PARUTION, STATUT FROM NUMERO ) AS TBL2"
                        //. " ON (REVUE.ID_REVUE=TBL2.ID_REVUE AND TBL2.`DATE_PARUTION` = (SELECT MAX(DATE_PARUTION) FROM NUMERO N2 WHERE N2.STATUT = 1 AND N2.ID_REVUE = TBL2.ID_REVUE))"
                        . "JOIN (SELECT  tt.ID_NUMPUBLIE, tt.TITRE, tt.ID_REVUE,tt.DATE_PARUTION, tt.ANNEE, tt.NUMERO FROM  NUMERO tt
                                INNER JOIN  (SELECT DISTINCT NUMERO.ID_REVUE,   MAX(NUMERO.`DATE_PARUTION`) AS MaxDateTime FROM NUMERO WHERE STATUT = 1 GROUP BY NUMERO.`ID_REVUE`) groupedtt
                                    ON tt.ID_REVUE = groupedtt.ID_REVUE AND tt.DATE_PARUTION = groupedtt.MaxDateTime
                            ) AS TBL2
                            ON (TBL2.ID_REVUE=REVUE.`ID_REVUE`)"
                        . " WHERE TYPEPUB=? "
                        . ($includeNoDis ? "" : " AND REVUE.ID_REVUE=DIS_REVUE.ID_REVUE ")
                        . " AND REVUE.STATUT=? "
                        . " AND REVUE_COURANTE=''" ;
                // Définition de la xxx, si elle existe
                if (($LET != 'ALL') && ($LET != 'NOTHING')) {
                    $sql .= "AND TRISHOW like ?";
                }
                // Définition de l'éditeur, si il existe
                if ($editeur != null) {
                    //$sql.="AND REVUE.ID_EDITEUR = ?";
                    if(($editeur == "1") || ($editeur == "0")) {$sql .= " and TBL1.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
                    else {$sql .= " and REVUE.ID_EDITEUR = ?";} // ID de l'éditeur
                }
                // Group by & Order by
                $sql .= "GROUP BY REVUE.ID_REVUE ";
                $sql.="ORDER BY TRISHOW";

                // LIMITE
                if($from !== null) {
                    $sql .= " LIMIT $from, $to";
                }

                if (($LET != 'ALL') && ($LET != 'NOTHING')) {
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut, ($LET.'%'), $editeur));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut, ($LET.'%')));
                    }
                }else{
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut, $editeur));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut));
                    }
                }
            } else {
                $sql = "SELECT "
                        . " DISTINCT REVUE.ID_REVUE, REVUE.TRISHOW, "
                        . " URL_REWRITING,"
                        . " REVUE.TITRE,"
                        . " REVUE.ID_EDITEUR, REVUE.STITRE, REVUE.SAVOIR_PLUS, REVUE.SAVOIR_PLUS_EN, "
                        . " TBL1.NOM_EDITEUR,"
                        . " TBL2.ID_NUMPUBLIE,"
                        . " REVUE.TYPEPUB, REVUE.ISSN, REVUE.ISSN_NUM, REVUE.PERIODICITE, REVUE.PERIODICITE_EN, REVUE.REVUE_PRECEDENTE "
                        . " FROM "
                        . " DIS_REVUE,"
                        . " REVUE  LEFT JOIN(SELECT  DISTINCT NOM_EDITEUR,ID_EDITEUR FROM   EDITEUR  ) AS TBL1"
                        . " ON (REVUE.`ID_EDITEUR`=TBL1.ID_EDITEUR)"
                        //. " JOIN(SELECT   ID_REVUE, ID_NUMPUBLIE, DATE_PARUTION, STATUT FROM NUMERO ) AS TBL2"
                        //. " ON (REVUE.ID_REVUE=TBL2.ID_REVUE AND TBL2.`DATE_PARUTION` = (SELECT MAX(DATE_PARUTION) FROM NUMERO N2 WHERE N2.STATUT = 1 AND N2.ID_REVUE = TBL2.ID_REVUE))"
                        . "JOIN (SELECT  tt.ID_NUMPUBLIE, tt.TITRE, tt.ID_REVUE,tt.DATE_PARUTION, tt.ANNEE, tt.NUMERO FROM  NUMERO tt
                                INNER JOIN  (SELECT DISTINCT NUMERO.ID_REVUE,   MAX(NUMERO.`DATE_PARUTION`) AS MaxDateTime FROM NUMERO WHERE STATUT = 1 GROUP BY NUMERO.`ID_REVUE`) groupedtt
                                    ON tt.ID_REVUE = groupedtt.ID_REVUE AND tt.DATE_PARUTION = groupedtt.MaxDateTime
                            ) AS TBL2
                            ON (TBL2.ID_REVUE=REVUE.`ID_REVUE`)"
                        . " WHERE "
                        . " REVUE.ID_REVUE=DIS_REVUE.ID_REVUE"
                        . " AND TYPEPUB=?"
                        . " AND REVUE.STATUT=?"
                        . " AND POS_DISC=? "
                        . " AND REVUE_COURANTE=''" ;
                // Définition de la xxx, si elle existe
                if (($LET != 'ALL') && ($LET != 'NOTHING')) {
                    $sql .= "AND TRISHOW like ?";
                }
                // Définition de l'éditeur, si il existe
                if ($editeur != null) {
                    //$sql.="AND REVUE.ID_EDITEUR = ?";
                    if(($editeur == "1") || ($editeur == "0")) {$sql .= " and TBL1.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
                    else {$sql .= " and REVUE.ID_EDITEUR = ?";}
                }
                // Group by & Order by
                $sql .= "GROUP BY REVUE.ID_REVUE ";
                $sql.="ORDER BY TRISHOW";

                // LIMITE
                if($from !== null) {
                    $sql .= " LIMIT $from, $to";
                }
                //$lastNums = $this->executerRequete($sql, array($typePub, $statut, $discPos));
                if (($LET != 'ALL') && ($LET != 'NOTHING')) {
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut, $discPos, ($LET.'%'), $editeur));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut, $discPos, ($LET.'%')));
                    }
                }else{
                    if($editeur != null){
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut, $discPos, $editeur));
                    }else{
                        $lastNums = $this->executerRequete($sql, array($typePub, $statut, $discPos));
                    }
                }
            }
            $fetched = $lastNums->fetchAll(PDO::FETCH_ASSOC);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    /**
     * Retourne la liste des articles de Revues les plus consultés
     * @return ARRAY ;
     */
    public function getMostConsultated($typePub, $statut, $discPos = null, $limit = null) {
        $redisKey = 'getMostConsultated/' . $typePub . '/' . $statut . '/' . $discPos;
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $whereDisc = "";
            if($discPos != null) {$whereDisc = "AND POS_DISC=?";}

            $sql = " SELECT "
                    . "     AUTEUR,"
                    . "     CAIRN_ARTICLE_DISC.TITRE,"
                    . "     CAIRN_ARTICLE_DISC.ID_ARTICLE,"
                    . "     SOUSTITRE, "
                    . "     CAIRN_ARTICLE_DISC.ID_REVUE, "
                    . "     CAIRN_ARTICLE_DISC.ID_NUMPUBLIE, "
                    . "     ARTICLE.ID_ARTICLE, "
                    . "     ARTICLE.ID_ARTICLE_S, "
                    . "     ARTICLE.CONFIG_ARTICLE, "
                    . "     ARTICLE.PAGE_DEBUT, "
                    . "     NUMERO.ID_NUMPUBLIE, "
                    . "     NUMERO.ANNEE, "
                    . "     NUMERO.VOLUME, "
                    . "     NUMERO.NUMEROA, "
                    . "     NUMERO.NUMERO, "
                    . "     REVUE.URL_REWRITING, "
                    . "     REVUE.ID_REVUE "
                    . " FROM "
                    . "     CAIRN_ARTICLE_DISC,"
                    . "     REVUE,"
                    . "     NUMERO,"
                    . "     ARTICLE"
                    . "  WHERE"
                    . "     TYPEPUB=? "
                    . "     AND REVUE.STATUT=?"
                    . "     AND NUMERO.STATUT=?"
                    . $whereDisc
                    . "     AND REVUE.ID_REVUE=CAIRN_ARTICLE_DISC.ID_REVUE "
                    . "     AND NUMERO.ID_NUMPUBLIE=CAIRN_ARTICLE_DISC.ID_NUMPUBLIE "
                    . "     AND ARTICLE.ID_ARTICLE=CAIRN_ARTICLE_DISC.ID_ARTICLE "
                    . "     AND ARTICLE.STATUT='1'"
                    . "  ORDER BY ORDRE";

            // Ajout d'une limite
            if($limit != null) {$sql .= " LIMIT ".$limit;}

            // Avec ou Sans Discipline définie
            if($discPos != null) {$mostConsultated = $this->executerRequete($sql, array($typePub, $statut, $statut, $discPos));}
            else {$mostConsultated = $this->executerRequete($sql, array($typePub, $statut, $statut));}
            $fetched = $mostConsultated->fetchAll(PDO::FETCH_ASSOC);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    /**
     * Retourne la liste des articles d'une Revues les plus consultés
     * @return ARRAY ;
     */
    public function getMostConsultatedFromRevue($idRevue, $limit = 5) {
        $sql = "SELECT
                    CAR.ID_REVUE, CAR.ID_NUMPUBLIE, CAR.ID_ARTICLE,
                    R.URL_REWRITING,
                    N.TITRE as TITRE_NUMERO, N.ANNEE as NUMERO_ANNEE, N.NUMERO as NUMERO_NUMERO, N.VOLUME as NUMERO_VOLUME,
                    A.TITRE as TITRE_ARTICLE, A.SOUSTITRE as SOUSTITRE_ARTICLE, A.URL_REWRITING_EN as URL_EN, A.PAGE_DEBUT
                FROM
                    CAIRN_ARTICLE_REVUE as CAR
                INNER JOIN
                    REVUE as R
                    ON R.ID_REVUE = CAR.ID_REVUE
                INNER JOIN
                    NUMERO as N
                    ON N.ID_NUMPUBLIE = CAR.ID_NUMPUBLIE
                INNER JOIN
                    ARTICLE as A
                    ON A.ID_ARTICLE = CAR.ID_ARTICLE
                WHERE
                    CAR.ID_REVUE = ?
                ORDER BY
                    ORDRE ASC
                LIMIT ".$limit;
        $mostConsultated = $this->executerRequete($sql, array($idRevue));
        return $mostConsultated->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne la liste des articles de Revues les plus consultés
     * @return ARRAY ;
     */
    public function getOuvrageMostConsultated($typePub, $statut, $discPos = null, $limit = 10) {
        $redisKey = 'getOuvrageMostConsultated/' . $typePub . '/' . $statut . '/' . $discPos;
        // if ($this->redis != null && $this->redis->exists($redisKey)) {
            // return json_decode($this->redis->get($redisKey), true);
        // }
        $whereDisc = "";
        if($discPos != null) {$whereDisc = "AND CAIRN_OUVRAGE_DISC.POS_DISC = ?";}

        $sql = "SELECT
                    NUMERO.ID_NUMPUBLIE,
                    NUMERO.TITRE as NUMERO_TITRE,
                    NUMERO.SOUS_TITRE as NUMERO_SOUSTITRE,
                    NUMERO.ISBN as NUMERO_ISBN,
                    NUMERO.URL_REWRITING as NUMERO_URL_REWRITING,
                    REVUE.ID_REVUE,
                    REVUE.TITRE as REVUE_TITRE,
                    CAIRN_OUVRAGE_DISC.CONSULTATIONS AS NUMERO_NB_CONSULTATIONS,
                    CAIRN_OUVRAGE_DISC.ORDRE AS NUMERO_ORDRE,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(AUTEUR.PRENOM, ' ', AUTEUR.NOM)
                        ORDER BY AUTEUR_ART.ORDRE SEPARATOR ', '
                    ) AS NUMERO_AUTEUR
                FROM NUMERO
                LEFT JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                LEFT JOIN CAIRN_OUVRAGE_DISC ON CAIRN_OUVRAGE_DISC.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
                LEFT JOIN AUTEUR_ART ON
                    AUTEUR_ART.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE AND
                    AUTEUR_ART.ID_ARTICLE = ''
                LEFT JOIN AUTEUR ON AUTEUR.ID_AUTEUR = AUTEUR_ART.ID_AUTEUR
                WHERE
                    REVUE.TYPEPUB = ? AND
                    NUMERO.STATUT = ? ".$whereDisc."
                GROUP BY NUMERO.ID_NUMPUBLIE
                ORDER BY
                    CAIRN_OUVRAGE_DISC.". ($discPos === null ? 'CONSULTATIONS DESC' : 'ORDRE ASC');

        // Ajout d'une limite
        if($limit != null) {$sql .= " LIMIT ".$limit;}

        // Avec ou Sans Discipline définie
        if($discPos != null) {$mostConsultated = $this->executerRequete($sql, array($typePub, $statut, $discPos));}
        else {$mostConsultated = $this->executerRequete($sql, array($typePub, $statut));}
        $fetched = $mostConsultated->fetchAll(PDO::FETCH_ASSOC);
        if ($this->redis != null) {
            $this->redis->setex($redisKey, $fetched);
        }
        return $fetched;
    }

    public function getMinMaxAnneeRevuesById($idRevue) {
        $sql = "SELECT MIN(ANNEE) AS MIN, MAX(ANNEE) AS MAX FROM NUMERO WHERE ID_REVUE = ? AND ANNEE IS NOT NULL AND ANNEE != ''";
        $annees = $this->executerRequete($sql, array($idRevue));
        return $annees->fetch();
    }

    public function getMinMaxAnneeRevuesByIdOnCairn($idRevue) {
        $sql = "SELECT MIN(ANNEE) AS MIN, MAX(ANNEE) AS MAX FROM NUMERO WHERE ID_PORTAIL = '' AND ID_REVUE = ? AND ANNEE IS NOT NULL AND ANNEE != ''";
        $annees = $this->executerRequete($sql, array($idRevue));
        return $annees->fetch();
    }

    public function getAccessibleArticles() {
        $sql = "SELECT docid FROM ART_PARIS7
                UNION
                SELECT docid FROM ART_FREE";
        $arts = $this->executerRequete($sql);
        return $arts->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getReferences($idArticle, $type = null) {
        $sql = "SELECT *
                FROM CAIRN_REFERENCES
                WHERE ID_ARTICLE=?";
        if ($type != null) {
            $sql .= " AND TYPEF = '" . $type . "'";
        }
        $refs = $this->executerRequete($sql, array($idArticle));
        return $refs->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReferencedBy($idArticle, $typef = null, $typepub = null) {
        $sql = "SELECT *
                FROM CAIRN_REFERENCES
                WHERE ID_CAIRN_CIBLE=?
                AND TYPEF IN ('N','B','E','T')";
        /* if ($typef != null) {
          $sql .= " AND TYPEF = '" . $typef . "'";
          } */
        if ($typepub != null) {
            $sql .= " AND TYPEPUB " . $typepub;
        } else {
            $sql .= " AND TYPEPUB != 0";
        }
        $sql .= " ORDER BY ANNEE_SOURCE DESC, NUMERO_SOURCE DESC, NOREF ASC";
        $refs = $this->executerRequete($sql, array($idArticle));
        return $refs->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNumReferencedBy($idNumPublie, $typef = null, $typepub = null) {
        $sql = "SELECT *
                FROM CAIRN_REFERENCES
                WHERE ID_CAIRN_NUM=?
                AND TYPEF IN ('N','B','E','T')";
        /* if ($typef != null) {
          $sql .= " AND TYPEF = '" . $typef . "'";
          } */
        if ($typepub != null) {
            $sql .= " AND TYPEPUB " . $typepub;
        }
        $sql .= " ORDER BY ANNEE_SOURCE DESC, NUMERO_SOURCE DESC, NOREF ASC";
        $refs = $this->executerRequete($sql, array($idNumPublie));
        return $refs->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countReferencedBy($idArticle) {
        $sql = "SELECT TYPEPUB, COUNT(NOREF) AS CNT
                FROM CAIRN_REFERENCES
                WHERE ID_CAIRN_CIBLE=? AND TYPEPUB != 0 AND TYPEF IN ('N','B','E','T')
                GROUP BY TYPEPUB";
        $refs = $this->executerRequete($sql, array($idArticle));
        return $refs->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countNumReferencedBy($idNumPublie) {
        $sql = "SELECT TYPEPUB, COUNT(NOREF) AS CNT
                FROM CAIRN_REFERENCES
                WHERE ID_CAIRN_NUM=? AND TYPEPUB != 0 AND TYPEF IN ('N','B','E','T')
                GROUP BY TYPEPUB";
        $refs = $this->executerRequete($sql, array($idNumPublie));
        return $refs->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAlertes($idUser, $type = null) {
        $sql = "SELECT * FROM ALERTE WHERE ID_USER = ?";
        if ($type != null) {
            $sql .= " AND TYPE_ALERTE = '" . $type . "'";
        }
        $alertes = $this->executerRequete($sql, array($idUser));
        return $alertes->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRevuesByType($typePub, $filtre = false) {
        $sql = "SELECT TITRE, URL_REWRITING,ID_REVUE, NOM_EDITEUR, ACCES_EN_LIGNE FROM REVUE "
                . "LEFT JOIN EDITEUR ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR "
                . "WHERE TYPEPUB=? "
                . "AND STATUT=1 "
                . "AND REVUE_COURANTE='' " ;
        if ($filtre == true) {
            $sql .= " AND EXISTS (SELECT * FROM NUMERO
                            WHERE NUMERO.ID_REVUE = REVUE.ID_REVUE
                            AND NUMERO.STATUT = 1)";
        }

        $sql .= " ORDER BY TITRE ASC;";

        $titles = $this->executerRequete($sql, array($typePub));
        return $titles->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumesArticle($idArticle) {
        $sql = "SELECT * FROM RESUMES WHERE ID_ARTICLE = ?";
        $resumes = $this->executerRequete($sql, array($idArticle));
        return $resumes->fetchAll(PDO::FETCH_ASSOC);
    }

    public function loadRevuesAlertes($id_user) {
        $sql = "SELECT * FROM ALERTE "
                . "LEFT JOIN REVUE ON ALERTE.ID_ALERTE = REVUE.ID_REVUE "
                . "LEFT JOIN EDITEUR ON REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR "
                . "LEFT JOIN(SELECT  ID_REVUE, ID_NUMPUBLIE, DATE_PARUTION, STATUT FROM NUMERO ) AS TBL2 "
                . "ON (REVUE.ID_REVUE=TBL2.ID_REVUE AND TBL2.DATE_PARUTION = (SELECT MAX(DATE_PARUTION) FROM NUMERO N2 WHERE N2.STATUT = 1 AND N2.ID_REVUE = TBL2.ID_REVUE)) "
                . "WHERE ALERTE.ID_USER = '" . $id_user . "' AND REVUE.TYPEPUB = 1 ORDER BY REVUE.TRISHOW";

        $revuesAlertes = $this->executerRequete($sql);
        return $revuesAlertes;
    }

    public function loadCollectionAlertes($id_user) {
        $sql = "SELECT * FROM ALERTE "
                . "LEFT JOIN REVUE ON ALERTE.ID_ALERTE = REVUE.ID_REVUE "
                . "LEFT JOIN EDITEUR ON REVUE.ID_EDITEUR = EDITEUR.ID_EDITEUR "
                . "LEFT JOIN(SELECT  ID_REVUE, ID_NUMPUBLIE, DATE_PARUTION, STATUT FROM NUMERO ) AS TBL2 "
                . "  ON (REVUE.ID_REVUE=TBL2.ID_REVUE AND TBL2.DATE_PARUTION = (SELECT MAX(DATE_PARUTION) FROM NUMERO N2 WHERE N2.STATUT = 1 AND N2.ID_REVUE = TBL2.ID_REVUE )) "
                . "WHERE ALERTE.ID_USER = ? AND REVUE.TYPEPUB IN (3,6)";

        $collectionsAlertes = $this->executerRequete($sql, array($id_user));
        return $collectionsAlertes;
    }

    public function getListCollection() {
        $sql = "SELECT ID_REVUE, TITRE FROM REVUE WHERE TYPEPUB IN (3,6) AND STATUT = 1 ORDER BY TITRE ASC";
        $collections = $this->executerRequete($sql);
        return $collections->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListCollectionFromEditeur($idEditeur = null) {
        // Condition
        $where = "";

        if($idEditeur != null) {
            // Définition de l'éditeur
            if(($idEditeur == "1") || ($idEditeur == "0")) {$where = "EDITEUR.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
            else {$where = "REVUE.ID_EDITEUR = ?";}

            $sql = "SELECT
                        ID_REVUE, TITRE
                    FROM REVUE
                    INNER JOIN EDITEUR
                    ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
                    WHERE TYPEPUB IN (3,6) AND STATUT = 1 AND ".$where."
                    ORDER BY TITRE ASC";
            $collections = $this->executerRequete($sql, array($idEditeur));
        }
        else {
            $sql = "SELECT ID_REVUE, TITRE FROM REVUE WHERE TYPEPUB IN (3,6) AND STATUT = 1 ORDER BY TITRE ASC";
            $collections = $this->executerRequete($sql);
        }

        return $collections->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListCollectionByTypepub($typepub) {
        $sql = "SELECT ID_REVUE, TITRE FROM REVUE WHERE TYPEPUB = ? AND STATUT = 1 ORDER BY TITRE ASC";
        $collections = $this->executerRequete($sql, array($typepub));
        return $collections->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAboDetails($idAbo, $idRevue) {
        $sql = "SELECT ABONNEMENT.ID_ABON,
                       ABONNEMENT.PRIX,
                       REVUE.ID_REVUE,
                       REVUE.URL_REWRITING,
                       REVUE.TYPEPUB,
                       REVUE.TITRE,
                       ABONNEMENT.LIBELLE,
                       TBL2.ID_NUMPUBLIE,
                       ABONNEMENT.NEXTANNEE,
                       ABONNEMENT.NOMBRE
                FROM ABONNEMENT
                JOIN REVUE ON REVUE.ID_REVUE = ABONNEMENT.ID_REVUE
                JOIN(SELECT   ID_REVUE, ID_NUMPUBLIE, DATE_PARUTION, STATUT FROM NUMERO ) AS TBL2
                 ON (REVUE.ID_REVUE=TBL2.ID_REVUE AND TBL2.`DATE_PARUTION` = (SELECT MAX(DATE_PARUTION) FROM NUMERO N2 WHERE N2.STATUT = 1 AND N2.ID_REVUE = TBL2.ID_REVUE))
                WHERE ABONNEMENT.ID_ABON = ?
                AND ABONNEMENT.ID_REVUE = ?";
        $details = $this->executerRequete($sql, array($idAbo, $idRevue));
        return $details->fetch(PDO::FETCH_ASSOC);
    }

    public function getAchatDetailForNumero($idNumPublie) {
        $sql = "SELECT NUMERO.ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE, NUMERO.ANNEE AS NUMERO_ANNEE,
                    NUMERO.NUMERO AS NUMERO_NUMERO, NUMERO.VOLUME AS NUMERO_VOLUME,
                    NUMERO.TITRE AS NUMERO_TITRE, NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                    NUMERO.SOUS_TITRE AS NUMERO_SOUS_TITRE,
                    REVUE.TITRE AS REVUE_TITRE, REVUE.URL_REWRITING AS REVUE_URL_REWRITING, REVUE.TYPEPUB AS REVUE_TYPEPUB,
                    EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR, EDITEUR.ID_EDITEUR AS EDITEUR_ID_EDITEUR,
                    GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS NUMERO_AUTEUR,
                    NUMERO.ISBN
            FROM NUMERO
            JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
            LEFT JOIN EDITEUR ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
            LEFT JOIN  AUTEUR_ART
                ON (`AUTEUR_ART`.`ID_NUMPUBLIE`=  NUMERO.`ID_NUMPUBLIE` AND AUTEUR_ART.ID_ARTICLE = '')
            LEFT JOIN AUTEUR
                ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
            WHERE NUMERO.ID_NUMPUBLIE = ?";
        $details = $this->executerRequete($sql, array($idNumPublie));
        return $details->fetch(PDO::FETCH_ASSOC);
    }

    public function getAchatDetailForArticle($idArticle) {
        $sql = "SELECT
                ARTICLE.TITRE AS ARTICLE_TITRE,
                ARTICLE.SOUSTITRE AS ARTICLE_SOUSTITRE,
                ARTICLE.URL_REWRITING_EN as ARTICLE_URL_REWRITING_EN,
                ARTICLE.PAGE_DEBUT AS ARTICLE_PAGE_DEBUT,
                ARTICLE.ID_ARTICLE AS ARTICLE_ID_ARTICLE,
                ARTICLE.PRIX AS ARTICLE_PRIX,
                ARTICLE.CONFIG_ARTICLE AS ARTICLE_CONFIG_ARTICLE,
                ARTICLE.MOVINGWALL AS MOVINGWALL,
                NUMERO.ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                NUMERO.ANNEE AS NUMERO_ANNEE,
                NUMERO.NUMERO AS NUMERO_NUMERO,
                NUMERO.VOLUME AS NUMERO_VOLUME,
                NUMERO.EAN AS NUMERO_EAN,
                NUMERO.ISBN AS NUMERO_ISBN,
                NUMERO.TITRE AS NUMERO_TITRE,
                NUMERO.URL_REWRITING AS NUMERO_URL_REWRITING,
                NUMERO.MOVINGWALL AS NUMERO_MOVINGWALL,
                REVUE.TITRE AS REVUE_TITRE,
                REVUE.URL_REWRITING AS REVUE_URL_REWRITING,
                REVUE.TYPEPUB AS REVUE_TYPEPUB,
                REVUE.URL_REWRITING_EN AS REVUE_URL_REWRITING_EN,
                EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,
                EDITEUR.ID_EDITEUR AS EDITEUR_ID_EDITEUR,
                GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR
            FROM ARTICLE
            JOIN NUMERO ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
            JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
            LEFT JOIN EDITEUR ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
            LEFT JOIN AUTEUR_ART
                ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
            LEFT JOIN AUTEUR
                ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
            WHERE ARTICLE.ID_ARTICLE = ?";
        $details = $this->executerRequete($sql, array($idArticle));
        return $details->fetch(PDO::FETCH_ASSOC);
    }

    public function getNumPublieByIdRevue($idRevue, $limit = 1) {
        $sql = "SELECT NUMERO.ID_NUMPUBLIE,
                    NUMERO.NUMERO,
                    NUMERO.ANNEE,
                    EDITEUR.NOM_EDITEUR
                    FROM NUMERO
                    JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                    JOIN EDITEUR ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
                    WHERE NUMERO.ID_REVUE=?
                    AND NUMERO.STATUT = 1
                    ORDER BY NUMERO.DATE_PARUTION DESC LIMIT " . $limit;
        $pub = $this->executerRequete($sql, array($idRevue))->fetchAll(PDO::FETCH_ASSOC);
        return $pub;
    }

    public function getNumerosFrom($idRevue, $idNumPublie, $limit) {
        $sql = "SELECT * FROM NUMERO
                WHERE DATE_PARUTION >= (SELECT DATE_PARUTION FROM NUMERO N2
                                        WHERE N2.ID_REVUE = NUMERO.ID_REVUE
                                        AND N2.ID_NUMPUBLIE = ?)
                AND ID_REVUE = ?
                ORDER BY DATE_PARUTION
                LIMIT " . $limit;
        return $this->executerRequete($sql, array($idNumPublie, $idRevue))->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCairnParamInst($id_user, $typeParam) {
        $sql = "SELECT * FROM CAIRN_PARAM_INST WHERE ID_USER = ? AND TYPE = ?";
        $param = $this->executerRequete($sql, array($id_user, $typeParam));
        return $param->fetch(PDO::FETCH_ASSOC);
    }

    public function getCairnParamsInst($id_user, $typeParams) {
        $sql = "SELECT TYPE, GROUP_CONCAT(VALEUR)
                FROM CAIRN_PARAM_INST WHERE ID_USER = ? AND TYPE IN (" . $typeParams . ")
                GROUP BY TYPE";
        $param = $this->executerRequete($sql, array($id_user));
        return $param->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getLicenceStatus($id_user) {
        $sql = "SELECT count(ID_LICENCE) as NBRE_LICENCES_USER, (SELECT count(ID_LICENCE) FROM LICENCE) as NBRE_LICENCES_TOTAL
                FROM LICENCE_I as LI
                WHERE LI.ID_USER = ?";
        $licences = $this->executerRequete($sql, array($id_user));
        $result = $licences->fetchAll(PDO::FETCH_ASSOC);
        return $result[0];
    }

    public function getRevueAbos($idRevue) {
        $sql = "SELECT * FROM ABONNEMENT WHERE ID_REVUE = ? AND VISIBLE = 1";
        $abos = $this->executerRequete($sql, array($idRevue));
        return $abos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFraisByZone($field, $zone, $idRevue) {
        $sql = "SELECT " . $field . " FROM FRAIS_REVUE
                WHERE ID_ZONE = ? AND ID_REVUE = ?";
        $fp = $this->executerRequete($sql, array($zone, $idRevue));
        return $fp->fetch(PDO::FETCH_COLUMN);
    }

    public function getFraisZone($idRevue, $pays) {
        $sql = "SELECT FRAIS_REVUE.ID_ZONE
                FROM FRAIS_REVUE
                LEFT JOIN ".Configuration::get('fraisPays')." ON ".Configuration::get('fraisPays').".ID_ZONE = FRAIS_REVUE.ID_ZONE
                WHERE ID_REVUE = ? AND PAYS = ?";
        $fp = $this->executerRequete($sql, array($idRevue, $pays));
        return $fp->fetch(PDO::FETCH_COLUMN);
    }

    public function getFraisPort($field, $revues, $pays) {
        $sql = "SELECT SUM(" . $field . ") FROM ".Configuration::get('fraisPays')."
                JOIN FRAIS_REVUE ON FRAIS_REVUE.`ID_ZONE` = ".Configuration::get('fraisPays').".`ID_ZONE`
                WHERE PAYS = ?";
        $where = '';
        foreach ($revues as $revue) {
            $where .= ($where == '' ? '' : ' OR ') . 'ID_REVUE = ?';
        }
        if ($where != '') {
            $sql .= ' AND (' . $where . ')';
        }
        array_unshift($revues, $pays);
        $fp = $this->executerRequete($sql, $revues);
        return $fp->fetch(PDO::FETCH_COLUMN);
    }


    /**
    *  Retourne la licence de la revue/l'ouvrage pour un utilisateur donnée
    *   @param string $id_user L'identifiant unique de l'utilisateur
    *   @param string $id_revue L'identifiant unique de la revue/ouvrage
    *
    *   @return array La licence si elle existe
    */
    public function getUserLicenceOfRevue($id_user, $id_revue) {
        $sql = "SELECT
            LICENCE.ID_LICENCE,
            LICENCE.LIBELLE
        FROM LICENCE_REVUE
            JOIN LICENCE_I ON LICENCE_I.ID_LICENCE = LICENCE_REVUE.ID_LICENCE
            JOIN LICENCE ON LICENCE_REVUE.ID_LICENCE = LICENCE.ID_LICENCE
        WHERE
            LICENCE_I.ID_USER = ? AND LICENCE_REVUE.ID_REVUE = ?
        ";
        $licence = $this->executerRequete($sql, array($id_user, $id_revue));
        return $licence->fetch(PDO::FETCH_ASSOC);
    }


    public function searchLicence($idUser, $idRevue) {
        $sql = "SELECT * FROM LICENCE_REVUE
                JOIN LICENCE_I ON LICENCE_I.ID_LICENCE = LICENCE_REVUE.ID_LICENCE
                WHERE ID_USER = ? AND ID_REVUE = ?";
        $licence = $this->executerRequete($sql, array($idUser, $idRevue));
        return $licence->rowCount() > 0 ? true : false;
    }

    public function getLicences($filterName = null)
    {
        $sqlFilterName = '';
        if ($filterName) {
            $filterName .= ' -%';
            $sqlFilterName = 'WHERE LICENCE.LIBELLE LIKE ?';
        }
        $sql = "SELECT
            LICENCE.ID_LICENCE as id,
            LICENCE.LIBELLE as name
        FROM
            LICENCE
        $sqlFilterName
        ORDER BY
            LICENCE.LIBELLE
        ";
        $licences = $this->executerRequete($sql, array($filterName));
        return $licences->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette méthode va permettre d'obtenir
     * une liste de licence à partir de leur
     * identifiant.
     */
    public function getListLicences($listId)
    {
        $sql = "SELECT
            LICENCE.ID_LICENCE as id,
            LICENCE.LIBELLE as name
        FROM
            LICENCE
            WHERE LICENCE.ID_LICENCE in ('".implode("','", $listId)."')
        ORDER BY
            LICENCE.LIBELLE
        ";

        $licences = $this->executerRequete($sql);
        return $licences->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLicencesArticles($idUser) {
        $sql = "SELECT ID_ARTICLE FROM LICENCE_REVUE
                JOIN LICENCE_I ON LICENCE_I.ID_LICENCE = LICENCE_REVUE.ID_LICENCE
                JOIN REVUE ON REVUE.ID_REVUE = LICENCE_REVUE.ID_REVUE
                JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                JOIN ARTICLE ON ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
                WHERE ID_USER = ?
                AND REVUE.STATUT = 1 AND NUMERO.STATUT = 1 AND ARTICLE.STATUT = 1
                AND ARTICLE.PRIX > 0
                AND (NUMERO.MOVINGWALL = '0000-00-00' OR NUMERO.MOVINGWALL > NOW() OR ARTICLE.TOUJOURS_PAYANT = 1)
                AND REVUE.ACHAT_ARTICLE = 1
                ";
        $articles = $this->executerRequete($sql, array($idUser));
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getLicencesArticlesDocId($idUser, $timeStart = null) {
        $sql = "SELECT docsRS.id FROM LICENCE_REVUE
                    JOIN LICENCE_I ON LICENCE_I.ID_LICENCE = LICENCE_REVUE.ID_LICENCE
                    JOIN REVUE ON REVUE.ID_REVUE = LICENCE_REVUE.ID_REVUE
                    JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                    JOIN ARTICLE ON ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
                    JOIN docsRS ON docsRS.docfilename = ARTICLE.ID_ARTICLE
                    WHERE ID_USER = ?
                    AND REVUE.STATUT = 1 AND NUMERO.STATUT = 1 AND ARTICLE.STATUT = 1
                    AND ARTICLE.PRIX > 0
                    AND (NUMERO.MOVINGWALL = '0000-00-00' OR NUMERO.MOVINGWALL > NOW() OR ARTICLE.TOUJOURS_PAYANT = 1)
                    AND REVUE.ACHAT_ARTICLE = 1
                ";
        //echo $sql;
        $articles = $this->executerRequete($sql, array($idUser));
        if ($timeStart != null) {
            $time_inter = microtime(true);
            echo "executerRequete:" . ($time_inter - $timeStart);
        }
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getFreeArticlesDocsId($timeStart = null) {
        $sql = "SELECT docsRS.id FROM REVUE
                    JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                    JOIN ARTICLE ON ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
                    JOIN docsRS ON docsRS.docfilename = ARTICLE.ID_ARTICLE
                    WHERE REVUE.STATUT = 1 AND NUMERO.STATUT = 1 AND ARTICLE.STATUT = 1
                    AND (ARTICLE.PRIX = 0
                         OR (NUMERO.MOVINGWALL != '0000-00-00' AND NUMERO.MOVINGWALL <= NOW()
                            AND ARTICLE.TOUJOURS_PAYANT != 1)
                        )
                ";
        //echo $sql;
        $articles = $this->executerRequete($sql);
        if ($timeStart != null) {
            $time_inter = microtime(true);
            echo "executerRequete:" . ($time_inter - $timeStart);
        }
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getArticlesFromRevues($idsRevues) {
        $sql = "SELECT ID_ARTICLE FROM REVUE
                JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                JOIN ARTICLE ON ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
                WHERE REVUE.ID_REVUE IN (" . $idsRevues . ")
                AND REVUE.STATUT = 1 AND NUMERO.STATUT = 1 AND ARTICLE.STATUT = 1
                AND ARTICLE.PRIX > 0
                AND (NUMERO.MOVINGWALL = '0000-00-00' OR NUMERO.MOVINGWALL > NOW() OR ARTICLE.TOUJOURS_PAYANT = 1)
                AND REVUE.ACHAT_ARTICLE = 1
                ";
        $articles = $this->executerRequete($sql, array($idsRevues));
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getArticlesDocIdFromRevues($idsRevues) {
        $sql = "SELECT docsRS.id FROM REVUE
                    JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                    JOIN ARTICLE ON ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
                    JOIN docsRS ON docsRS.docfilename = ARTICLE.ID_ARTICLE
                    WHERE REVUE.ID_REVUE IN (" . $idsRevues . ")
                    AND REVUE.STATUT = 1 AND NUMERO.STATUT = 1 AND ARTICLE.STATUT = 1
                    AND ARTICLE.PRIX > 0
                    AND (NUMERO.MOVINGWALL = '0000-00-00' OR NUMERO.MOVINGWALL > NOW() OR ARTICLE.TOUJOURS_PAYANT = 1)
                    AND REVUE.ACHAT_ARTICLE = 1
                ";
        $articles = $this->executerRequete($sql, array($idsRevues));
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function isPaysEU($pays) {
        $sql = "SELECT PAYS, TAUX_TVA, TAUX_TVA_REDUIT FROM PAYS WHERE PAYS = ? AND IS_EU = 1";
        return $this->executerRequete($sql, array($pays))->fetch(PDO::FETCH_ASSOC);
    }

    public function getListePays() {
        $redisKey = 'getListePays/';
        if ($this->redis != null && $this->redis->exists($redisKey)) {
            return json_decode($this->redis->get($redisKey), true);
        } else {
            $sql = "SELECT PAYS FROM PAYS ORDER BY PAYS";
            $fetched = $this->executerRequete($sql)->fetchAll(PDO::FETCH_COLUMN);
            if ($this->redis != null) {
                $this->redis->setex($redisKey, $fetched);
            }
            return $fetched;
        }
    }

    public function getUsersCairn() {
        $sql = "SELECT ID_USER FROM USER_CAIRN";
        $uc = $this->executerRequete($sql);
        return $uc->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSsos() {
        $sql = "SELECT * FROM SSOCAS WHERE PAYS = 'France' OR PAYS IS NULL ORDER BY TITLE";
        $ssos = $this->executerRequete($sql);
        return $ssos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSsosInt() {
        $sql = "SELECT * FROM SSOCAS WHERE PAYS != 'France' ORDER BY PAYS, TITLE";
        $ssos = $this->executerRequete($sql);
        return $ssos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSsosPays() {
        $sql = "SELECT DISTINCT PAYS FROM SSOCAS WHERE PAYS != 'France' ORDER BY PAYS";
        $ssos = $this->executerRequete($sql);
        return $ssos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCurDisciplineEn($idRevue) {
        $sql = "SELECT URL_REWRITING_EN, DISCIPLINE_EN
                FROM DIS_REVUE
                JOIN DISCIPLINE ON DISCIPLINE.POS_DISC = DIS_REVUE.POS_DISC
                WHERE ID_REVUE = ?";
        $curdisc = $this->executerRequete($sql, array($idRevue));
        return $curdisc->fetch(PDO::FETCH_ASSOC);
    }

    public function getDisciplineForRevue($idRevue) {
        $sql = "SELECT DISCIPLINE.POS_DISC, PARENT
                FROM DIS_REVUE
                JOIN DISCIPLINE ON DISCIPLINE.POS_DISC = DIS_REVUE.POS_DISC
                WHERE ID_REVUE = ?";
        $curdisc = $this->executerRequete($sql, array($idRevue));
        return $curdisc->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkResumeInter($idArticle) {
        $sql = "SELECT 1 FROM RESUMES WHERE RESUME_EN != '' AND ID_ARTICLE = ?";
        $res = $this->executerRequete($sql, array($idArticle));
        return $res->fetch(PDO::FETCH_COLUMN);
    }

    public function getSsoByUrl($idP) {
        $sql = "SELECT * FROM SSOCAS WHERE URL_LOGIN LIKE ?";
        $res = $this->executerRequete($sql, array($idP . '%'));
        //$sql = "SELECT * FROM SSOCAS WHERE EntityDescriptorID = ?";
        //$res = $this->executerRequete($sql, array($idP));
        return $res->fetch(PDO::FETCH_ASSOC);
    }

    public function getSsoByEntityDescriptorId($idP) {
        $sql = "SELECT * FROM SSOCAS WHERE EntityDescriptorID = ?";
        $res = $this->executerRequete($sql, array($idP));
        return $res->fetch(PDO::FETCH_ASSOC);
    }

    public function getSsoById($idP) {
        $sql = "SELECT * FROM SSOCAS WHERE ID_SSO = ?";
        $res = $this->executerRequete($sql, array($idP));
        return $res->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * * compare an array of firstletters with the full alphabet
     * return full aphabet, each letter annotated with 0/1 if present in the given array
     *
     * @return ARRAY ;
     */
    private function &checkAlphabet(&$letters) {
        $allLetters = array();
        for ($x = 65; $x < 65 + 26; $x++) {
            if (isset($letters[chr($x)])) {
                $allLetters[] = array('LET' => chr($x), 'A' => 1);
            } else {
                $allLetters[] = array('LET' => chr($x), 'A' => 0);
            }
        }
        return $allLetters;
    }

    private function &checkAlphabet2(&$letters) {
        $allLetters = array();
        for ($x = 65; $x < 65 + 26; $x++) {
            if (isset($letters[chr($x)])) {
                $allLetters[] = array('LET' => chr($x), 'A' => 1);
            }
        }
        return $allLetters;
    }

    public function getModifiedNumsSince($lastDate, $modeInter = 0) {
        $sql = "SELECT NUMERO.STATUT AS NUMERO_STATUT,
                       NUMERO.ID_NUMPUBLIE AS NUMERO_ID_NUMPUBLIE,
                       NUMERO.DERNIERE_EDITION AS NUMERO_DERNIERE_EDITION,
                       REVUE.STATUT AS REVUE_STATUT,
                       REVUE.TYPEPUB as REVUE_TYPEPUB
                FROM NUMERO
                JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                WHERE NUMERO.DATEMAJ > ?";
        if($modeInter == 1){
            $sql .= " OR EXISTS (SELECT * FROM ARTICLE WHERE ARTICLE.`ID_NUMPUBLIE` = NUMERO.`ID_NUMPUBLIE` AND ARTICLE.MDATE > ?)";
            $numeros = $this->executerRequete($sql, array($lastDate,$lastDate));
        }else{
            $numeros = $this->executerRequete($sql, array($lastDate));
        }
        //$numeros = $this->executerRequete($sql, array($lastDate));
        return $numeros->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllArticlesForFields2() {
        $sql = "SELECT ARTICLE.*,
            GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR,
            PORTAIL.ID_PORTAIL as PORTAIL_ID_PORTAIL,
            PORTAIL.NOM_PORTAIL as PORTAIL_NOM_PORTAIL,
            NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
            NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
            NUMERO.DATE_PARUTION as NUMERO_DATE_PARUTION,
            NUMERO.TITRE_RECH as NUMERO_TITRE_RECH,
            NUMERO.VOLUME as NUMERO_VOLUME,
            NUMERO.NUMERO as NUMERO_NUMERO,
            NUMERO.TITRE as NUMERO_TITRE,
            NUMERO.ANNEE as NUMERO_ANNEE,
            NUMERO.MEMO as NUMERO_MEMO,
            NUMERO.DATE_MISEENLIGNE as NUMERO_DATE_MISEENLIGNE,
            NUMERO.TYPE_NUMPUBLIE as NUMERO_TYPE_NUMPUBLIE,
            REVUE.ACHAT_ARTICLE as REVUE_ACHAT_ARTICLE,
            REVUE.ID_REVUE as REVUE_ID_REVUE,
            REVUE.TITRE as REVUE_TITRE,
            REVUE.TYPEPUB as REVUE_TYPEPUB,
            EDITEUR.ID_EDITEUR as EDITEUR_ID_EDITEUR,
            EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,EDITEUR.VILLE as EDITEUR_VILLE,
            GROUP_CONCAT(DISTINCT IF(PARENT = 0, DISCIPLINE.POS_DISC, PARENT) SEPARATOR '|' ) as dr
        FROM ARTICLE
        JOIN NUMERO
            ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
        JOIN REVUE
            ON REVUE.ID_REVUE = NUMERO.ID_REVUE
        LEFT JOIN  AUTEUR_ART
            ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
        LEFT JOIN AUTEUR
            ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
        LEFT JOIN PORTAIL
            ON ( PORTAIL.ID_PORTAIL = ARTICLE.ID_PORTAIL)
        LEFT JOIN EDITEUR
            ON ( EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR)
        LEFT JOIN DIS_REVUE
            ON ( DIS_REVUE.ID_REVUE = REVUE.ID_REVUE)
        LEFT JOIN DISCIPLINE
            ON ( DISCIPLINE.POS_DISC = DIS_REVUE.POS_DISC)
        WHERE ARTICLE.STATUT = 1 AND NUMERO.STATUT = 1
        AND ARTICLE.ID_REVUE <> 'EDM'
        AND (NUMERO.DERNIERE_EDITION IS NULL || NUMERO.DERNIERE_EDITION = '')";

        if (Configuration::get('mode') == 'cairninter') {
            //$sql .= " AND ARTICLE.LANGUE_INTEGRALE = 'en' ";
        }

        $sql .= "GROUP BY ARTICLE.ID_ARTICLE
        ORDER BY NUMERO.ANNEE";

        $articles = $this->executerRequete($sql, null);
        return $articles->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArticlesForFields2($numPublie, $typepub) {
        // Indexation conditionnée
        $sqlDisJoint = "REVUE.ID_REVUE"; // Valeur par défaut (Magazine et Revue)
        if(($typepub == 6) || ($typepub == 3) || ($typepub == 5)) {$sqlDisJoint = "NUMERO.ID_NUMPUBLIE";} // Pour Encyclopédies, Ouvrages et Monographie

        // Exécution de la requête
        $sql = "SELECT ARTICLE.*,
            GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR,
            PORTAIL.ID_PORTAIL as PORTAIL_ID_PORTAIL,
            PORTAIL.NOM_PORTAIL as PORTAIL_NOM_PORTAIL,
            NUMERO.MOVINGWALL as NUMERO_MOVINGWALL,
            NUMERO.ID_NUMPUBLIE as NUMERO_ID_NUMPUBLIE,
            NUMERO.DATE_PARUTION as NUMERO_DATE_PARUTION,
            NUMERO.TITRE_RECH as NUMERO_TITRE_RECH,
            NUMERO.VOLUME as NUMERO_VOLUME,
            NUMERO.NUMERO as NUMERO_NUMERO,
            NUMERO.TITRE as NUMERO_TITRE,
            NUMERO.ANNEE as NUMERO_ANNEE,
            NUMERO.DATE_MISEENLIGNE as NUMERO_DATE_MISEENLIGNE,
            NUMERO.TYPE_NUMPUBLIE as NUMERO_TYPE_NUMPUBLIE,
            NUMERO.MEMO as NUMERO_MEMO,
            REVUE.ACHAT_ARTICLE as REVUE_ACHAT_ARTICLE,
            REVUE.ID_REVUE as REVUE_ID_REVUE,
            REVUE.TITRE as REVUE_TITRE,
            REVUE.TYPEPUB as REVUE_TYPEPUB,
            EDITEUR.ID_EDITEUR as EDITEUR_ID_EDITEUR,
            EDITEUR.NOM_EDITEUR as EDITEUR_NOM_EDITEUR,EDITEUR.VILLE as EDITEUR_VILLE,
            GROUP_CONCAT(DISTINCT IF(PARENT = 0, DISCIPLINE.POS_DISC, PARENT) SEPARATOR '|' ) as dr
        FROM ARTICLE
        JOIN NUMERO
            ON NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE
        JOIN REVUE
            ON REVUE.ID_REVUE = NUMERO.ID_REVUE
        LEFT JOIN  AUTEUR_ART
            ON (`AUTEUR_ART`.`ID_ARTICLE`=  ARTICLE.`ID_ARTICLE`)
        LEFT JOIN AUTEUR
            ON ( AUTEUR.`ID_AUTEUR`=AUTEUR_ART.`ID_AUTEUR`)
        LEFT JOIN PORTAIL
            ON ( PORTAIL.ID_PORTAIL = ARTICLE.ID_PORTAIL)
        LEFT JOIN EDITEUR
            ON ( EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR)
        LEFT JOIN DIS_REVUE
            ON ( DIS_REVUE.ID_REVUE = ".$sqlDisJoint.")
        LEFT JOIN DISCIPLINE
            ON ( DISCIPLINE.POS_DISC = DIS_REVUE.POS_DISC)
        WHERE ARTICLE.ID_NUMPUBLIE =?
        GROUP BY ARTICLE.ID_ARTICLE
        ORDER BY ARTICLE.TRISHOW";

        $articles = $this->executerRequete($sql, array($numPublie));
        return $articles->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAuteursForFields2($idArticle) {
        $sql = "SELECT trim(AUTEUR.NOM) as NOM,
                    trim(AUTEUR.PRENOM)as PRENOM,
                    trim(AUTEUR_ART.ATTRIBUT)as ATTRIBUT,
                    AUTEUR.ID_AUTEUR
                FROM `AUTEUR_ART`
                INNER JOIN `AUTEUR`
                    ON (`AUTEUR_ART`.`ID_AUTEUR` = `AUTEUR`.`ID_AUTEUR`)
                WHERE AUTEUR_ART.ID_ARTICLE= ?
               ORDER BY AUTEUR_ART.ORDRE LIMIT 3";
        $auteurs = $this->executerRequete($sql, array($idArticle));
        return $auteurs->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAuteursForReference($idArticle) {
        $sql = "SELECT CONCAT(PRENOM , ' ', NOM) as AUTEUR, T.ID_ARTICLE
                FROM AUTEUR as A
                INNER JOIN AUTEUR_ART as T
                ON A.ID_AUTEUR = T.ID_AUTEUR
                INNER JOIN CAIRN_REFERENCES as C
                ON C.ID_ARTICLE = T.ID_ARTICLE
                WHERE C.ID_CAIRN_CIBLE = ?
                ORDER BY T.ID_ARTICLE ASC, T.ORDRE ASC";
        $auteurs = $this->executerRequete($sql, array($idArticle));
        //return $auteurs->fetchAll(PDO::FETCH_ASSOC);

        // Création d'un tableau associatif suivant l'ID de l'article
        $res = array();
        while($row = $auteurs->fetch(PDO::FETCH_ASSOC))
            $res[$row["ID_ARTICLE"]][] = $row["AUTEUR"];
        return $res;
    }

    // Récupère la liste des auteurs/contributeurs d'un numéro/ouvrage ou d'un article/chapitre
    // On récupère les auteurs en éléments concaténé (:) utilisé un peu partout
    // $idReference     = ID ARTICLE ou ID NUMPUBLIE
    // $params          = array reprennant les paramètres disponibles
    //                      $type            = 'article' ou 'numero' ?
    //                      $notThisAuthor   = ID d'un auteur que l'on ne souhaite pas récupérer
    public function getAuteurFromReference($idReference, $params = null) {
        // Init
        $type          = null;
        $notThisAuthor = null;
        $noAttribute   = null;

        // Définition des paramètres
        if($params != null) {
            if($params["type"]) {$type = $params["type"];}
            if($params["notThisAuthor"]) {$notThisAuthor = $params["notThisAuthor"];}
            if($params["noAttribute"] && $params["noAttribute"] == 1) {$noAttribute = $params["noAttribute"];}
        }

        // Définition de la requête
        $whereClause = "";
        if($notThisAuthor != null) {$whereClause .= " AND A.ID_AUTEUR != '".$notThisAuthor."'";}
        if($noAttribute != null) {$whereClause .= " AND T.ATTRIBUT = ''";}

        // NUMERO
        if($type == "numero") {
            $sql = "SELECT GROUP_CONCAT(DISTINCT CONCAT(A.PRENOM, ':', A.NOM, ':', A.ID_AUTEUR, ':', REPLACE(T.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR
                    FROM AUTEUR as A
                    INNER JOIN AUTEUR_ART as T
                    ON A.ID_AUTEUR = T.ID_AUTEUR
                    WHERE T.ID_NUMPUBLIE = ? AND ID_ARTICLE = '' ".$whereClause."
                    GROUP BY T.ID_NUMPUBLIE
                    ORDER BY T.ORDRE ASC";
        }
        // ARTICLE
        if(($type == "article") || ($type == null)) {
            $sql = "SELECT GROUP_CONCAT(DISTINCT CONCAT(A.PRENOM, ':', A.NOM, ':', A.ID_AUTEUR, ':', REPLACE(T.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR
                    FROM AUTEUR as A
                    INNER JOIN AUTEUR_ART as T
                    ON A.ID_AUTEUR = T.ID_AUTEUR
                    WHERE T.ID_ARTICLE = ? ".$whereClause."
                    GROUP BY T.ID_ARTICLE
                    ORDER BY T.ORDRE ASC";
        }

        // Exécution de la requête
        $auteurs = $this->executerRequete($sql, array($idReference));

        return $auteurs->fetch(PDO::FETCH_ASSOC);
    }


    public function getLastArticleOfLastNums() {
        $sql = "SELECT
                    ARTICLE.ID_ARTICLE AS ARTICLE_ID_ARTICLE,
                    ARTICLE.TITRE AS ARTICLE_TITRE,
                    ARTICLE.URL_REWRITING_EN AS ARTICLE_URL_REWRITING_EN,
                    REVUE.TITRE AS REVUE_TITRE,
                    ARTICLE.DATE_MISEENLIGNE AS PUB_DATE_MISEENLIGNE
                FROM NUMERO
                LEFT JOIN ARTICLE ON (
                    ARTICLE.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE AND
                    LANGUE_INTEGRALE = 'en' AND
                    PAGE_FIN - PAGE_DEBUT >= 3
                ) LEFT JOIN REVUE ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                WHERE
                    NUMERO.STATUT = 1 AND
                    REVUE.STATUT = 1 AND
                    ARTICLE.LANGUE_INTEGRALE = 'en'
                    AND (PAGE_FIN - PAGE_DEBUT >= 3)
                GROUP BY NUMERO.ID_NUMPUBLIE
                ORDER BY PUB_DATE_MISEENLIGNE DESC LIMIT 8";
        $lastArts = $this->executerRequete($sql);
        return $lastArts->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserEditeur($idEditeur,$codeAbo){
        $sql = "SELECT * FROM USER_EDITEUR "
                . "WHERE ID_EDITEUR = ? "
                . "AND REF_EDITEUR = ? "
                . "AND ID_USER = CONCAT('###',?,'###',?,'###')";
        $userEditeur = $this->executerRequete($sql, array($idEditeur,$codeAbo,$idEditeur,$codeAbo));
        return $userEditeur->fetch(PDO::FETCH_ASSOC);
    }



    // Fonctions spécifique à KBART
    public function getAllRevuesForKbart($licence = null)
    {
        $columns = '';
        $where = '';
        $join = '';
        if ($licence !== null) {
            $join .= 'LEFT JOIN LICENCE_REVUE ON REVUE.ID_REVUE = LICENCE_REVUE.ID_REVUE';
            $where .= "AND LICENCE_REVUE.ID_LICENCE = ?";
        }
        $sql = "SELECT
                REVUE.ID_REVUE as id_revue,
                REVUE.TITRE as publication_title,
                REVUE.ISSN as print_identifier,
                REVUE.ISSN_NUM as online_identifier,
                REVUE.URL_REWRITING as title_url,
                REVUE.MOVINGWALL AS embargo,
                REVUE.ARRET AS is_stop,
                EDITEUR.NOM_EDITEUR as publisher_name,
                REVUE.MOVINGWALL as movingwall,
                REVUE.REVUE_PRECEDENTE as last_rev,
                REVUE.REVUE_COURANTE as current_rev
                $columns
            FROM
                REVUE
                LEFT JOIN EDITEUR ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
                $join
            WHERE
                REVUE.ID_REVUE > ' '
                AND REVUE.TYPEPUB = '1'
                AND REVUE.STATUT = '1'
                $where
            ORDER BY REVUE.TITRE ASC";
        $revues = $this->executerRequete($sql, [$licence]);
        return $revues->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette fonction va permettre de récupérer
     * les ouvrages et les encyclopédies de poches.
     * En tenant compte ou pas de l'identifiant de licence
     * reçu en paramètre.
     */
    public function getAllOuvragesPochesForKbart($licence = null, $typepub)
    {

        $sql = 'SELECT DISTINCT
                REVUE.ID_REVUE as id_revue,
                NUMERO.TITRE as publication_title,
                NUMERO.EAN as print_identifier,
                NUMERO.ISBN,
                NUMERO.ISBN_NUMERIQUE as online_identifier,
                NUMERO.URL_REWRITING as title_url,
                REVUE.MOVINGWALL AS embargo,
                REVUE.ARRET AS is_stop,
                EDITEUR.NOM_EDITEUR as publisher_name,
                REVUE.MOVINGWALL as movingwall,
                REVUE.REVUE_PRECEDENTE as last_rev,
                REVUE.REVUE_COURANTE as current_rev,
                DATE_FORMAT(NUMERO.DATE_PARUTION, "%Y-%m-%d") as date_monograph_published_print,
                DATE_FORMAT(NUMERO.DATE_MISEENLIGNE, "%Y-%m-%d") as date_monograph_published_online,
                NUMERO.ID_NUMPUBLIE,
                NUMERO.VOLUME as monograph_edition,
                REVUE.TITRE as nom_revue
                FROM REVUE
                LEFT JOIN EDITEUR ON EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR
                LEFT JOIN NUMERO ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                LEFT JOIN LICENCE_REVUE ON LICENCE_REVUE.ID_REVUE = NUMERO.ID_NUMPUBLIE
                WHERE REVUE.TYPEPUB = ?
                AND NUMERO.STATUT = 1
                AND NUMERO.DATE_MISEENLIGNE != "0000-00-00"
                AND NUMERO.DATE_MISEENLIGNE IS NOT NULL';

        if (!isset($licence)) {

            if ($typepub == 3) {
                $licence = 86; //Pour avoir le bouquet "Ouvrages - Général".
            } elseif ($typepub == 6) {
                $licence = 72; //Pour avoir le bouquet "Poches - Général".
            } else {
                die('Error no Data...');
            }

        }

        $sql .= ' AND LICENCE_REVUE.ID_LICENCE = ? ORDER BY REVUE.TITRE ASC';

        $revues = $this->executerRequete($sql, array($typepub, $licence));
        return $revues->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette méthode permet d'avoir
     * la liste des auteurs pour un ensemble de numéro.
     */
    public function getFirstAuthorForPublication(array $tabNumPubli) {

        $authors = array();
        $tabAuthors = array();

        if (isset($tabNumPubli) && !empty($tabNumPubli)) {
            $sql = "SELECT DISTINCT "
                    . "AUTEUR_ART.ID_NUMPUBLIE, "
                    . "TRIM(AUTEUR.NOM) as NOM_AUTEUR "
                    . "FROM AUTEUR_ART "
                    . "LEFT JOIN AUTEUR ON (AUTEUR.ID_AUTEUR = AUTEUR_ART.ID_AUTEUR) "
                    . "WHERE AUTEUR_ART.ID_NUMPUBLIE in ('" . implode("', '", $tabNumPubli) . "') "
                    . "AND ORDRE = 1 "
                    . "AND (ID_ARTICLE = '' OR ID_ARTICLE IS NULL) ";

            $authorQuery = $this->executerRequete($sql);
            $authors = $authorQuery->fetchAll(PDO::FETCH_ASSOC);

            foreach ($authors as $auteur) {
                $tabAuthors[$auteur['ID_NUMPUBLIE']] = $auteur['NOM_AUTEUR'];
            }
        }

        return $tabAuthors;

    }


    public function getLicenceForKbart($idLicence)
    {
        $sql = "SELECT
                LICENCE.LIBELLE as name,
                LICENCE.ID_LICENCE as id_licence
            FROM
                LICENCE
            WHERE
                LICENCE.ID_LICENCE = ?
        ";
        $licence = $this->executerRequete($sql, [$idLicence]);
        return $licence->fetch(PDO::FETCH_ASSOC);
    }

    private function getNumeroForKbart($idRevue, $isFirst, $isFree)
    {
        $currentDate = date('Y-m-d');
        $sql = "SELECT
                NUMERO.DATE_PARUTION AS date,
                NUMERO.ANNEE AS annee,
                NUMERO.NUMERO AS numero,
                NUMERO.NUMEROA AS numeroa,
                NUMERO.VOLUME AS volume
            FROM NUMERO
            WHERE
                NUMERO.ID_REVUE = ?
                AND NUMERO.STATUT = 1
                AND NUMERO.ID_PORTAIL = ''
                AND NUMERO.MOVINGWALL ".($isFree ? '<=' : '>')." ?
            ORDER BY NUMERO.DATE_PARUTION ".($isFirst ? 'ASC' : 'DESC')."
            LIMIT 1
        ";
        $numero = $this->executerRequete($sql, [$idRevue, $currentDate]);
        return $numero->fetch(PDO::FETCH_ASSOC);
    }

    public function getFirstFreeNumeroForKbart($idRevue)
    {
        return $this->getNumeroForKbart($idRevue, true, true);
    }
    public function getLastFreeNumeroForKbart($idRevue)
    {
        return $this->getNumeroForKbart($idRevue, false, true);
    }
    public function getFirstPayNumeroForKbart($idRevue)
    {
        return $this->getNumeroForKbart($idRevue, true, false);
    }
    public function getLastPayNumeroForKbart($idRevue)
    {
        return $this->getNumeroForKbart($idRevue, false, false);
    }
    public function getFirstNumeroForKbart($idRevue)
    {
        $currentDate = date('Y-m-d');
        $sql = "SELECT
                NUMERO.DATE_PARUTION AS date,
                NUMERO.ANNEE AS annee,
                NUMERO.NUMERO AS numero,
                NUMERO.NUMEROA AS numeroa,
                NUMERO.VOLUME AS volume
            FROM NUMERO
            WHERE
                NUMERO.ID_REVUE = ?
                AND NUMERO.STATUT = 1
                AND NUMERO.ID_PORTAIL = ''
            ORDER BY NUMERO.DATE_PARUTION ASC
            LIMIT 1
        ";
        $numero = $this->executerRequete($sql, array($idRevue));
        return $numero->fetch(PDO::FETCH_ASSOC);
    }
    public function getLastIdRevuePrec($idRevue)
    {
        $currentDate = date('Y-m-d');
        $sql = "SELECT
		REVUE_PRECEDENTE as last_rev
		FROM REVUE
		WHERE ID_REVUE = ?
        ";
        $numero = $this->executerRequete($sql, array($idRevue));
        return $numero->fetch(PDO::FETCH_ASSOC);
    }
    // Fin des fonctions utilisé par KBART



    public function getDisciplinesH($statut) {
        $sql = " SELECT POS_DISC, DISCIPLINE, PARENT FROM DISCIPLINE WHERE PARENT =0";
        $sql.= " AND URL_REWRITING!=''";
        $sql.= " AND DISCIPLINE.POS_DISC IN (SELECT DISTINCT POS_DISC FROM DIS_REVUE,REVUE WHERE REVUE_COURANTE='' ";
        $sql.= " AND DIS_REVUE.ID_REVUE=REVUE.ID_REVUE AND STATUT=? ) ORDER BY DISCIPLINE";
        $disciplines = $this->executerRequete($sql, array($statut));

        $discs = $disciplines->fetchAll(PDO::FETCH_ASSOC);
        return $discs;
    }

    public function getSsDisciplinesH($parent) {
        $sql = " SELECT POS_DISC, DISCIPLINE, PARENT FROM DISCIPLINE WHERE PARENT =? ORDER BY DISCIPLINE";
        $disciplines = $this->executerRequete($sql,array($parent));

        $discs = $disciplines->fetchAll(PDO::FETCH_ASSOC);
        return $discs;
    }

    public function getRevues($statut,$typepub){
        $sql = "SELECT DISTINCT TITRE FROM REVUE WHERE REVUE_COURANTE = '' AND STATUT = ? AND TYPEPUB = ?";
        $revues = $this->executerRequete($sql, array($statut,$typepub));

        return $revues->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getRevuesPortail($idportail){
        $sql = "SELECT DISTINCT(ID_REVUE)
                FROM `NUMERO`
                WHERE ID_PORTAIL = ?
                ORDER BY ID_REVUE ASC";
        $revues = $this->executerRequete($sql, array($idportail));

        return $revues->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getEditeursExp(){
        $sql = "SELECT NOM_EDITEUR FROM EDITEUR";
        $editeurs = $this->executerRequete($sql);

        return $editeurs->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAuteurs($typepub){
        $sql = "SELECT DISTINCT CONCAT(PRENOM , ' ', NOM) FROM AUTEUR"
                . " WHERE EXISTS "
                . " (SELECT * FROM AUTEUR_ART "
                . "     JOIN ARTICLE ON ARTICLE.ID_ARTICLE = AUTEUR_ART.ID_ARTICLE "
                . "     JOIN REVUE ON REVUE.`ID_REVUE` = ARTICLE.`ID_REVUE` "
                . "     WHERE AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR AND ARTICLE.STATUT = 1 AND REVUE.STATUT = 1 AND REVUE.TYPEPUB = ?)"
                . " OR EXISTS "
                . " (SELECT * FROM AUTEUR_ART "
                . "     JOIN NUMERO ON NUMERO.ID_NUMPUBLIE = AUTEUR_ART.ID_NUMPUBLIE
                        JOIN REVUE ON REVUE.`ID_REVUE` = NUMERO.`ID_REVUE`
                     WHERE AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR AND NUMERO.STATUT = 1 AND REVUE.STATUT = 1 AND REVUE.TYPEPUB = ?)";
        $auteurs = $this->executerRequete($sql, array($typepub,$typepub));

        return $auteurs->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllArticlesByType($typePub, $limitFrom, $limitNb){
        $sql = "SELECT CONCAT (NUMERO.TITRE, ' : ', ARTICLE.TITRE) AS TITRE,
		ARTICLE.ID_ARTICLE,
                ARTICLE.CONFIG_ARTICLE,
		NUMERO.ID_NUMPUBLIE,
		NUMERO.`DATE_PARUTION`,
		REVUE.ID_REVUE,
		EDITEUR.`NOM_EDITEUR`,
                EDITEUR.PAYS,
		GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.`PRENOM`,' ',AUTEUR.`NOM`) ORDER BY ORDRE) AS AUTEUR,
		REVUE.TITRE AS REVUE_TITRE,
		DISCIPLINE.`DISCIPLINE`,
                RESUMES.RESUME_FR
            FROM ARTICLE
            JOIN NUMERO ON NUMERO.`ID_NUMPUBLIE` = ARTICLE.`ID_NUMPUBLIE`
            JOIN REVUE ON REVUE.`ID_REVUE` = NUMERO.`ID_REVUE`
            JOIN EDITEUR ON EDITEUR.`ID_EDITEUR` = REVUE.`ID_EDITEUR`
            JOIN AUTEUR_ART ON AUTEUR_ART.`ID_ARTICLE` = ARTICLE.`ID_ARTICLE`
            JOIN AUTEUR ON AUTEUR.`ID_AUTEUR` = AUTEUR_ART.`ID_AUTEUR`
            JOIN DIS_REVUE ON DIS_REVUE.`ID_REVUE`= REVUE.`ID_REVUE`
            JOIN `DISCIPLINE` ON DISCIPLINE.`POS_DISC` = DIS_REVUE.`POS_DISC`
            JOIN RESUMES ON RESUMES.`ID_ARTICLE` = ARTICLE.`ID_ARTICLE`
            WHERE REVUE.TYPEPUB = ? AND REVUE.STATUT = 1 AND NUMERO.STATUT = 1 AND ARTICLE.STATUT = 1
            AND REVUE.`REVUE_COURANTE` = '' AND NUMERO.DERNIERE_EDITION = ''
            AND ARTICLE.CONFIG_ARTICLE LIKE '%,%,%,1%' AND RESUME_FR != ''
            GROUP BY ARTICLE.ID_ARTICLE
            ORDER BY DATE_MISEENLIGNE DESC
            LIMIT ".$limitFrom.",".$limitNb;
        $articles = $this->executerRequete($sql, array($typePub));

        return $articles->fetchAll(PDO::FETCH_ASSOC);

    }


    function getDatasForRedirectAutocomplete($category, $term) {
        $CONSTANTS = Service::get('Constants');
        $sql = null;
        /*
        TODO: Il y a le remplacement des espaces insécables par un simple espace
        directement dans la requête. Ce n'est pas bon.
        Il faut une colonne "empreinte/fingerprint" qui supprime tous les caractères
        n'étant pas ASCII et les espaces pour pouvoir faire des recherches rapide et simple.
        */
        switch ($category) {
            case $CONSTANTS::AUTOCOMPLETE_CATEGORY_OUVRAGE:
                $sql = "SELECT
                        NUMERO.ID_NUMPUBLIE AS id_numpublie,
                        NUMERO.URL_REWRITING AS url_rewriting,
                        NUMERO.ISBN AS isbn,
                        REVUE.TYPEPUB AS typepub
                    FROM
                        NUMERO
                        LEFT JOIN REVUE ON NUMERO.ID_REVUE = REVUE.ID_REVUE
                    WHERE
                        REPLACE(NUMERO.TITRE, '\xc2\xa0', ' ') = ?
                        AND TRIM(REVUE.REVUE_COURANTE) = ''
                ";
                break;
            case $CONSTANTS::AUTOCOMPLETE_CATEGORY_REVUE:
                $sql = "SELECT
                        REVUE.ID_REVUE AS id_numpublie,
                        REVUE.URL_REWRITING AS url_rewriting,
                        REVUE.TYPEPUB AS typepub
                    FROM
                        REVUE
                    WHERE
                        REPLACE(REVUE.TITRE, '\xc2\xa0', ' ') = ?
                ";
                break;
            case $CONSTANTS::AUTOCOMPLETE_CATEGORY_AUTEUR:
                $sql = "SELECT
                        AUTEUR.NOM AS nom,
                        AUTEUR.PRENOM AS prenom,
                        AUTEUR.ID_AUTEUR AS id_auteur
                    FROM AUTEUR
                    WHERE CONCAT(AUTEUR.PRENOM, ' ', AUTEUR.NOM) = ?
                ";
                break;
            default:
                break;
        }
        if ($sql === null) { return array(); }
        $result = $this->executerRequete($sql, [$term])->fetchAll(PDO::FETCH_ASSOC);
        return $result ? $result : array();
    }

    /**
     * Cette fonction permet d'obtenir
     * la ou les disciplines, pour
     * une revue, un ouvrage, une encyclopédies de poche, ou
     * un magazine
     */
    public function getDisciplinesOfRevue($id) {

        $tabDiscipline = array();
        $tabSousDiscipline = array();
        $tabGroupDiscipline = array();

        $sql = "SELECT DISCIPLINE.DISCIPLINE, PARENT
                FROM DIS_REVUE
                LEFT JOIN DISCIPLINE ON (DISCIPLINE.POS_DISC = DIS_REVUE.POS_DISC)
                WHERE DIS_REVUE.ID_REVUE = ?";

        $disciplines = $this->executerRequete($sql, array($id));

        $rows = $disciplines->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if ($row['PARENT'] == 0) {
                $tabDiscipline[] =  $row['DISCIPLINE'];
            } else {
                $tabSousDiscipline[] = $row['DISCIPLINE'];
                $tabGroupDiscipline[] = $row['PARENT'];
            }
        }

        //Discipline des sous-disciplines.
        if (!empty($tabGroupDiscipline)) {
            $tabDiscipline = array_merge($tabDiscipline, $this->getParentDisciplinesOfRevue($tabGroupDiscipline));
        }

        return array('DISCIPLINES' => $tabDiscipline, 'SOUS DISCIPLINE' => $tabSousDiscipline);
    }

    /**
     * Cette fonction permet
     * d'obtenir les disciplines des sous-disciplines.
     */
    public function getParentDisciplinesOfRevue($ids) {

        $listId = implode(',', $ids);

        $sql = "SELECT DISCIPLINE
                FROM DISCIPLINE
                WHERE POS_DISC in ($listId)";

        $disciplines = $this->executerRequete($sql);

        return $disciplines->fetchAll(PDO::FETCH_COLUMN);

    }


    /**
     * Permet d'obtenir la liste des revues avec la tva réduite pour la presse
     */
    public function getRevueWithPressTva() {
        $sql = "SELECT ID_REVUE, DATE_FIN_CPPAP FROM REVUE WHERE DATE_FIN_CPPAP > '0000-00-00'";
        $revues = $this->executerRequete($sql);
        return $revues->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette fonction permet d'obtenir
     * la ou les disciplines d'un editeur
     */
    public function getDisciplinesOfEditeur($idEditeur, $typepub) {

        // Condition
        $where = "";

        // Définition de l'éditeur
        if(($idEditeur == "1") || ($idEditeur == "0")) {$where = "E.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
        else {$where = "R.ID_EDITEUR = ?";}

        // Requête
        // Pour les ouvrages
        if(($typepub == 3) || ($typepub == 6)) {
            /*$sql = "SELECT DISC.POS_DISC, DISC.URL_REWRITING, DISC.DISCIPLINE, DISC.DISCIPLINE_EN
                    FROM DISCIPLINE as DISC
                    INNER JOIN DIS_REVUE as DR
                    ON DR.POS_DISC = DISC.POS_DISC
                    INNER JOIN NUMERO as N
                    ON N.ID_NUMPUBLIE = DR.ID_REVUE
                    INNER JOIN REVUE as R
                    ON R.ID_REVUE = N.ID_REVUE
                    INNER JOIN EDITEUR as E
                    ON E.ID_EDITEUR = R.ID_EDITEUR
                    WHERE ".$where." AND DISC.PARENT = '0' AND R.TYPEPUB = ?
                    GROUP BY DISC.POS_DISC";*/
            $sql = "SELECT DISCPARENT.POS_DISC, DISCPARENT.URL_REWRITING, DISCPARENT.DISCIPLINE, DISCPARENT.DISCIPLINE_EN
                    FROM DISCIPLINE as DISCCHILD
                    LEFT JOIN DISCIPLINE as DISCPARENT
                    ON DISCPARENT.POS_DISC = DISCCHILD.PARENT
                    INNER JOIN DIS_REVUE as DR
                    ON DR.POS_DISC = DISCCHILD.POS_DISC
                    INNER JOIN NUMERO as N
                    ON N.ID_NUMPUBLIE = DR.ID_REVUE
                    INNER JOIN REVUE as R
                    ON R.ID_REVUE = N.ID_REVUE
                    INNER JOIN EDITEUR as E
                    ON E.ID_EDITEUR = R.ID_EDITEUR
                    WHERE ".$where." AND DISCPARENT.PARENT = '0' AND R.TYPEPUB = ?
                    GROUP BY DISCPARENT.POS_DISC";
        }
        // Pour les REVUES et AUTRES
        else {
            $sql = "SELECT DISC.POS_DISC, DISC.URL_REWRITING, DISC.DISCIPLINE, DISC.DISCIPLINE_EN
                    FROM DISCIPLINE as DISC
                    INNER JOIN DIS_REVUE as DR
                    ON DR.POS_DISC = DISC.POS_DISC
                    INNER JOIN REVUE as R
                    ON R.ID_REVUE = DR.ID_REVUE
                    INNER JOIN EDITEUR as E
                    ON E.ID_EDITEUR = R.ID_EDITEUR
                    WHERE ".$where." AND DISC.PARENT = '0' AND R.TYPEPUB = ?
                    GROUP BY DISC.POS_DISC";
        }
        $disciplines = $this->executerRequete($sql, array($idEditeur, $typepub));
        return $disciplines->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette fonction permet d'obtenir
     * la ou les sous-disciplines d'un editeur selon la discipline sélectionnée
     */
    public function getSousDisciplinesOfEditeur($idEditeur, $typepub, $discpos) {

        // Condition
        $where = "";

        // Définition de l'éditeur
        if(($idEditeur == "1") || ($idEditeur == "0")) {$where = "E.TYPE = ?";} // 1 = Maison d'édition, 0 = Autre structure
        else {$where = "R.ID_EDITEUR = ?";}

        // Requête
        // Pour les ouvrages
        if(($typepub == 3) || ($typepub == 6)) {
            $sql = "SELECT DISC.POS_DISC, DISC.URL_REWRITING, DISC.DISCIPLINE, DISC.DISCIPLINE_EN
                    FROM DISCIPLINE as DISC
                    INNER JOIN DIS_REVUE as DR
                    ON DR.POS_DISC = DISC.POS_DISC
                    INNER JOIN NUMERO as N
                    ON N.ID_NUMPUBLIE = DR.ID_REVUE
                    INNER JOIN REVUE as R
                    ON R.ID_REVUE = N.ID_REVUE
                    INNER JOIN EDITEUR as E
                    ON E.ID_EDITEUR = R.ID_EDITEUR
                    WHERE ".$where." AND R.TYPEPUB = ? AND DISC.PARENT = ? 
                    GROUP BY DISC.POS_DISC";
        }
        $sousdisciplines = $this->executerRequete($sql, array($idEditeur, $typepub, $discpos));
        return $sousdisciplines->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette fonction permet de récupérer les informations
     * complémentaires pour les métatags de webTrends.
     * Dimitry (Cairn) le 19/11/2015.
     */
    public function getCairnParamsInstWebTrends($idInstitution) {
        $sql = "SELECT * FROM `CAIRN_PARAM_INST_WEBTRENDS` WHERE IDENTIFIANT = ? LIMIT 1";
        $infosInstitution = $this->executerRequete($sql, array($idInstitution));
        return $infosInstitution->fetch(PDO::FETCH_BOTH);
    }

    /**
     * Cette méthode va permettre la récupération
     * des tags manquants pour le téléchargement
     * du document pdf.
     * Dimitry (Cairn) le 27/01/2016.
     */
    public function getDataTagWebTrends($idArticle) {

        $data = array();

        $sql = "SELECT "
                . "REVUE.MOVINGWALL,"
                . "ARTICLE.PAGE_DEBUT,"
                . "ARTICLE.PAGE_FIN, "
                . "ARTICLE.CONFIG_ARTICLE, "
                . "ARTICLE.PRIX as ARTICLE_PRIX, "
                . "REVUE.ID_REVUE, "
                . "REVUE.TITRE as titre_revue, "
                . "REVUE.TYPEPUB, "
                . "REVUE.AFFILIATION, "
                . "REVUE.ID_EDITEUR, "
                . "REVUE.BENEFICIAIRE, "
                . "REVUE.DISCIPLINE, "
                . "EDITEUR.NOM_EDITEUR, "
                . "NUMERO.ID_NUMPUBLIE, "
                . "NUMERO.TITRE as titre_numero, "
                . "NUMERO.DATE_MISEENLIGNE, "
                . "NUMERO.ANNEE, "
                . "NUMERO.GRILLEPRIX AS NUMERO_GRILLEPRIX, "
                . "NUMERO.MOVINGWALL AS NUMERO_MOVINGWALL "
                . "FROM ARTICLE "
                . "INNER JOIN NUMERO ON (NUMERO.ID_NUMPUBLIE = ARTICLE.ID_NUMPUBLIE) "
                . "INNER JOIN REVUE ON (REVUE.ID_REVUE = NUMERO.ID_REVUE) "
                . "INNER JOIN EDITEUR ON (EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR) "
                ."WHERE ARTICLE.ID_ARTICLE = ?";

        $infosWebTrends = $this->executerRequete($sql, array($idArticle));
        $row = $infosWebTrends->fetch(PDO::FETCH_ASSOC);

        //Traitement au niveau des données.
        //comm_rev
        $data['comm_rev'] = ($row['MOVINGWALL'] == 0) ? 'gratuite' : 'payante';

        //doc_temps_de_lecture
        $data['doc_temps_de_lecture'] = '';
        if (is_numeric($row['PAGE_DEBUT']) && is_numeric($row['PAGE_FIN'])) {
            $data['doc_temps_de_lecture'] = (intval($row['PAGE_FIN']) - intval($row['PAGE_DEBUT']) + 1) * 45;
        }

        //doc_pdf_dispo
        $configArticle = explode(',', $row['CONFIG_ARTICLE']);
        $data['doc_pdf_dispo'] = ($configArticle[3] == 1) ? 1 : 0;

        //pn_type
        $mappingTypepub = [1 => 'Revues',2 => 'Magazines',3 => 'Ouvrages',4 => 'État du monde',5 => 'Monographie',6 => 'Que sais-je ? / Repères'];
        $data['pn_type'] = $mappingTypepub[$row['TYPEPUB']];

        //cleo
        $data['cleo'] = '';
        if (isset($row['AFFILIATION']) && (strpos($row['AFFILIATION'], 'Revues.org') !== false)) {
            $data['cleo'] = 'Affiliée au Cléo';
        }

        //pour le nombre de page.
        $data['doc_nb_pages'] = '';
        if (is_numeric($row['PAGE_DEBUT']) && is_numeric($row['PAGE_FIN'])) {
            $data['doc_nb_pages'] = (intval($row['PAGE_FIN']) - intval($row['PAGE_DEBUT']) + 1);
        }

        //Pour l'id_editeur
        $data['id_editeur'] = $row['ID_EDITEUR'];

        //Pour le nom de l'éditeur.
        $data['editeur'] = $row['NOM_EDITEUR'];

        //Pour l'année de mise en ligne.
        $data['annee_mise_en_ligne'] = isset($row['DATE_MISEENLIGNE']) ? (substr($row['DATE_MISEENLIGNE'], 0, 4) != '0000' ? substr($row['DATE_MISEENLIGNE'], 0, 7) : '') : '';

        //Pour l'année_tomaison
        $data['annee_tomaison'] = $row['ANNEE'];

        //Pour le pn_grid
        $data['pn_grid'] = $row['ID_REVUE'];

        //Pour le pn_gr
        $data['pn_gr'] = $row['titre_revue'];

        //Pour le pn_ntit
        $data['pn_ntit'] = $row['titre_numero'];

        //Pour art_p1
        $data['art_p1'] = $row['BENEFICIAIRE'];

        //Pour le pn_nid.
        $data['pn_nid'] = $row['ID_NUMPUBLIE'];

        //Pour les disciplines et sous-discipline.
        if (in_array($row['TYPEPUB'], array(3, 6))) {//Pour le cas d'un ouvrage ou d'une encyclopédie de poche.
            $dataDiscipline = $this->getDisciplinesOfRevue($row['ID_NUMPUBLIE']);
        } elseif ($row['TYPEPUB'] == 1) { //Pour les revues.
            $dataDiscipline = $this->getDisciplinesOfRevue($row['ID_REVUE']);
        }
        $data['discipline'] = isset($dataDiscipline['DISCIPLINES']) ? implode(';', $dataDiscipline['DISCIPLINES']) : '';
        $data['sub_discipline'] = isset($dataDiscipline['SOUS DISCIPLINE']) ? implode(';', $dataDiscipline['SOUS DISCIPLINE']) : '';

        //Accessoire.
        $data['type_publication'] = $row['TYPEPUB'];

        //Pour les consultations à prendre en compte, pour cette article.
        $data['consultation'] = 'non';
        switch ($row['TYPEPUB']) {
            case 1: //Revues
                $dateW = date('Y', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 2));
                if (($row['MOVINGWALL'] > 0) && ($row['ANNEE'] >= $dateW)) {
                    $data['consultation'] = 'oui';
                }
                break;
            case 3: //Ouvrages
                if (isset($row['NUMERO_GRILLEPRIX']) && $row['NUMERO_GRILLEPRIX'] !== '0') {
                    $data['consultation'] = 'oui';
                }
                break;
            case 6: //Encyclopédies poche
            case 2: //Magazines
                $data['consultation'] = 'oui';
                break;
        }

        //Pour la partie comm_art
        $namePage = Service::get('Webtrends')->getLettersToNamePage('PDF', $row);
        $mappingDescPages = ['landing_pdf-payant' => 'payant',
            'landing_pdf-post-movingwall' => 'post barrière mobile',
            'landing_pdf-gratuit' => 'gratuit',
            'landing_pdf-unknown' => 'unknown'];
        $data['comm_art'] = $mappingDescPages[$namePage];

        //Pour la partie discipline principale.
        $data['discipline_principale'] = $row['DISCIPLINE'];

        return $data;

    }

    /**
     * Cette méthode va permettre la récupération
     * des tags pour le numéro
     * Dimitry (Cairn) le 15/02/2016.
     */
    public function getDataTagNumeroWebTrends($idNumero) {

        $data = array();

        $sql = "SELECT "
                . "NUMERO.ID_NUMPUBLIE, "
                . "NUMERO.TITRE as titre_numero, "
                . "NUMERO.ANNEE, "
                . "NUMERO.GRILLEPRIX AS NUMERO_GRILLEPRIX, "
                . "REVUE.ID_REVUE, "
                . "REVUE.TITRE as titre_revue, "
                . "REVUE.TYPEPUB, "
                . "REVUE.MOVINGWALL, "
                . "REVUE.BENEFICIAIRE, "
                . "REVUE.ID_EDITEUR, "
                . "REVUE.DISCIPLINE, "
                . "REVUE.AFFILIATION, "
                . "EDITEUR.NOM_EDITEUR "
                . "FROM NUMERO "
                . "INNER JOIN REVUE ON (REVUE.ID_REVUE = NUMERO.ID_REVUE) "
                . "INNER JOIN EDITEUR ON (EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR) "
                . "WHERE NUMERO.ID_NUMPUBLIE = ?";

        $infosWebTrends = $this->executerRequete($sql, array($idNumero));
        $row = $infosWebTrends->fetch(PDO::FETCH_ASSOC);

        //Traitement au niveau des données.
        //comm_rev
        $data['comm_rev'] = ($row['MOVINGWALL'] == 0) ? 'gratuite' : 'payante';

//        //doc_temps_de_lecture
//        $data['doc_temps_de_lecture'] = '';
//        if (is_numeric($row['PAGE_DEBUT']) && is_numeric($row['PAGE_FIN'])) {
//            $data['doc_temps_de_lecture'] = (intval($row['PAGE_FIN']) - intval($row['PAGE_DEBUT']) + 1) * 45;
//        }
//
//        //doc_pdf_dispo
//        $configArticle = explode(',', $row['CONFIG_ARTICLE']);
//        $data['doc_pdf_dispo'] = ($configArticle[3] == 1) ? 1 : 0;

        //pn_type
        $mappingTypepub = [1 => 'Revues',2 => 'Magazines',3 => 'Ouvrages',4 => 'État du monde',5 => 'Monographie',6 => 'Que sais-je ? / Repères'];
        $data['pn_type'] = $mappingTypepub[$row['TYPEPUB']];

        //cleo
        $data['cleo'] = '';
        if (isset($row['AFFILIATION']) && (strpos($row['AFFILIATION'], 'Revues.org') !== false)) {
            $data['cleo'] = 'Affiliée au Cléo';
        }

//        //pour le nombre de page.
//        $data['doc_nb_pages'] = '';
//        if (is_numeric($row['PAGE_DEBUT']) && is_numeric($row['PAGE_FIN'])) {
//            $data['doc_nb_pages'] = (intval($row['PAGE_FIN']) - intval($row['PAGE_DEBUT']) + 1);
//        }
//
        //Pour l'id_editeur
        $data['id_editeur'] = $row['ID_EDITEUR'];

        //Pour le nom de l'éditeur.
        $data['editeur'] = $row['NOM_EDITEUR'];

        //Pour l'année de mise en ligne.
        $data['annee_mise_en_ligne'] = isset($row['DATE_MISEENLIGNE']) ? (substr($row['DATE_MISEENLIGNE'], 0, 4) != '0000' ? substr($row['DATE_MISEENLIGNE'], 0, 7) : '') : '';

        //Pour l'année_tomaison
        $data['annee_tomaison'] = $row['ANNEE'];

        //Pour le pn_grid
        $data['pn_grid'] = $row['ID_REVUE'];

        //Pour le pn_gr
        $data['pn_gr'] = $row['titre_revue'];

        //Pour le pn_ntit
        $data['pn_ntit'] = $row['titre_numero'];

        //Pour art_p1
        $data['art_p1'] = $row['BENEFICIAIRE'];

        //Pour le pn_nid.
        $data['pn_nid'] = $row['ID_NUMPUBLIE'];

        //Pour les disciplines et sous-discipline.
        if (in_array($row['TYPEPUB'], array(3, 6))) {//Pour le cas d'un ouvrage ou d'une encyclopédie de poche.
            $dataDiscipline = $this->getDisciplinesOfRevue($row['ID_NUMPUBLIE']);
        } elseif ($row['TYPEPUB'] == 1) { //Pour les revues.
            $dataDiscipline = $this->getDisciplinesOfRevue($row['ID_REVUE']);
        }
        $data['discipline'] = isset($dataDiscipline['DISCIPLINES']) ? implode(';', $dataDiscipline['DISCIPLINES']) : '';
        $data['sub_discipline'] = isset($dataDiscipline['SOUS DISCIPLINE']) ? implode(';', $dataDiscipline['SOUS DISCIPLINE']) : '';

        //Accessoire.
        $data['type_publication'] = $row['TYPEPUB'];

        //Pour les consultations à prendre en compte, pour cette article.
        $data['consultation'] = 'non';
        switch ($row['TYPEPUB']) {
            case 1: //Revues
                $dateW = date('Y', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 2));
                if (($row['MOVINGWALL'] > 0) && ($row['ANNEE'] >= $dateW)) {
                    $data['consultation'] = 'oui';
                }
                break;
            case 3: //Ouvrages
                if (isset($row['NUMERO_GRILLEPRIX']) && $row['NUMERO_GRILLEPRIX'] !== '0') {
                    $data['consultation'] = 'oui';
                }
                break;
            case 6: //Encyclopédies poche
            case 2: //Magazines
                $data['consultation'] = 'oui';
                break;
        }

//        //Pour la partie comm_art
//        $namePage = Service::get('Webtrends')->getLettersToNamePage('PDF', $row);
//        $mappingDescPages = ['landing_pdf-payant' => 'payant',
//            'landing_pdf-post-movingwall' => 'post barrière mobile',
//            'landing_pdf-gratuit' => 'gratuit',
//            'landing_pdf-unknown' => 'unknown'];
//        $data['comm_art'] = $mappingDescPages[$namePage];

        //Pour la partie discipline principale.
        $data['discipline_principale'] = $row['DISCIPLINE'];

        return $data;

    }

    /**
     * Cette méthode va permettre la récupération
     * des tags au niveau de la revue,
     * Dimitry (Cairn) le 15/02/2016.
     */
    public function getDataTagRevueWebTrends($idRevue, $idNumero) {

        $data = array();

        $sql = "SELECT "
                . "REVUE.ID_REVUE, "
                . "REVUE.TITRE as titre_revue, "
                . "REVUE.TYPEPUB, "
                . "REVUE.ID_EDITEUR, "
                . "REVUE.MOVINGWALL, "
                . "REVUE.BENEFICIAIRE, "
                . "REVUE.DISCIPLINE, "
                . "REVUE.AFFILIATION, "
                . "NUMERO.ID_NUMPUBLIE, "
                . "EDITEUR.NOM_EDITEUR "
                . "FROM REVUE "
                . "INNER JOIN EDITEUR ON (EDITEUR.ID_EDITEUR = REVUE.ID_EDITEUR) "
                . "INNER JOIN NUMERO ON (REVUE.ID_REVUE = NUMERO.ID_REVUE) "
                . "WHERE REVUE.ID_REVUE = ? "
                . "AND NUMERO.ID_NUMPUBLIE = ?";

        $infosWebTrends = $this->executerRequete($sql, array($idRevue, $idNumero));
        $row = $infosWebTrends->fetch(PDO::FETCH_ASSOC);

        //Traitement au niveau des données.
        //comm_rev
        $data['comm_rev'] = ($row['MOVINGWALL'] == 0) ? 'gratuite' : 'payante';

//        //doc_temps_de_lecture
//        $data['doc_temps_de_lecture'] = '';
//        if (is_numeric($row['PAGE_DEBUT']) && is_numeric($row['PAGE_FIN'])) {
//            $data['doc_temps_de_lecture'] = (intval($row['PAGE_FIN']) - intval($row['PAGE_DEBUT']) + 1) * 45;
//        }
//
//        //doc_pdf_dispo
//        $configArticle = explode(',', $row['CONFIG_ARTICLE']);
//        $data['doc_pdf_dispo'] = ($configArticle[3] == 1) ? 1 : 0;

        //pn_type
        $mappingTypepub = [1 => 'Revues',2 => 'Magazines',3 => 'Ouvrages',4 => 'État du monde',5 => 'Monographie',6 => 'Que sais-je ? / Repères'];
        $data['pn_type'] = $mappingTypepub[$row['TYPEPUB']];

        //cleo
        $data['cleo'] = '';
        if (isset($row['AFFILIATION']) && (strpos($row['AFFILIATION'], 'Revues.org') !== false)) {
            $data['cleo'] = 'Affiliée au Cléo';
        }

//        //pour le nombre de page.
//        $data['doc_nb_pages'] = '';
//        if (is_numeric($row['PAGE_DEBUT']) && is_numeric($row['PAGE_FIN'])) {
//            $data['doc_nb_pages'] = (intval($row['PAGE_FIN']) - intval($row['PAGE_DEBUT']) + 1);
//        }
//
        //Pour l'id_editeur
        $data['id_editeur'] = $row['ID_EDITEUR'];

        //Pour le nom de l'éditeur.
        $data['editeur'] = $row['NOM_EDITEUR'];

        //Pour l'année de mise en ligne.
//        $data['annee_mise_en_ligne'] = isset($row['DATE_MISEENLIGNE']) ? (substr($row['DATE_MISEENLIGNE'], 0, 4) != '0000' ? substr($row['DATE_MISEENLIGNE'], 0, 7) : '') : '';

        //Pour l'année_tomaison
//        $data['annee_tomaison'] = $row['ANNEE'];

        //Pour le pn_grid
        $data['pn_grid'] = $row['ID_REVUE'];

        //Pour le pn_gr
        $data['pn_gr'] = $row['titre_revue'];

        //Pour le pn_ntit
//        $data['pn_ntit'] = $row['titre_numero'];

        //Pour art_p1
        $data['art_p1'] = $row['BENEFICIAIRE'];

        //Pour le pn_nid.
//        $data['pn_nid'] = $row['ID_NUMPUBLIE'];

        //Pour les disciplines et sous-discipline.
        if (in_array($row['TYPEPUB'], array(3, 6))) {//Pour le cas d'un ouvrage ou d'une encyclopédie de poche.
            $dataDiscipline = $this->getDisciplinesOfRevue($row['ID_NUMPUBLIE']);
        } elseif ($row['TYPEPUB'] == 1) { //Pour les revues.
            $dataDiscipline = $this->getDisciplinesOfRevue($row['ID_REVUE']);
        }
        $data['discipline'] = isset($dataDiscipline['DISCIPLINES']) ? implode(';', $dataDiscipline['DISCIPLINES']) : '';
        $data['sub_discipline'] = isset($dataDiscipline['SOUS DISCIPLINE']) ? implode(';', $dataDiscipline['SOUS DISCIPLINE']) : '';

        //Accessoire.
//        $data['type_publication'] = $row['TYPEPUB'];

        //Pour les consultations à prendre en compte, pour cette article.
//        $data['consultation'] = 'non';
//        switch ($row['TYPEPUB']) {
//            case 1: //Revues
//                $dateW = date('Y', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 2));
//                if (($row['MOVINGWALL'] > 0) && ($row['ANNEE'] >= $dateW)) {
//                    $data['consultation'] = 'oui';
//                }
//                break;
//            case 3: //Ouvrages
//                if (isset($row['NUMERO_GRILLEPRIX']) && $row['NUMERO_GRILLEPRIX'] !== '0') {
//                    $data['consultation'] = 'oui';
//                }
//                break;
//            case 6: //Encyclopédies poche
//            case 2: //Magazines
//                $data['consultation'] = 'oui';
//                break;
//        }

//        //Pour la partie comm_art
//        $namePage = Service::get('Webtrends')->getLettersToNamePage('PDF', $row);
//        $mappingDescPages = ['landing_pdf-payant' => 'payant',
//            'landing_pdf-post-movingwall' => 'post barrière mobile',
//            'landing_pdf-gratuit' => 'gratuit',
//            'landing_pdf-unknown' => 'unknown'];
//        $data['comm_art'] = $mappingDescPages[$namePage];

        //Pour la partie discipline principale.
        $data['discipline_principale'] = $row['DISCIPLINE'];

        return $data;

    }

    // Récupération de la dernière news
    public function getActualite() {
        // Récupération de la dernière news
        $sql = "SELECT
                    DATE_ACTU, URL_REWRITING, TITRE, DESCRIPTION, IMAGE
                FROM
                    CAIRN_DOSSIER_ACTU
                WHERE
                    STATUT = 1 AND DATE_ACTU <= CURDATE() AND DATE_LIMITE_AFFICHAGE >= CURDATE()
                ORDER BY
                    DATE_ACTU DESC
                LIMIT 1";
        // Exécution
        $news = $this->executerRequete($sql, array());
        return $news->fetchAll(PDO::FETCH_ASSOC);
    }
}
