<?php

require_once 'Framework/Modele.php';

/**
 * Vérifie l'existance de traductions en FRANCAIS directement dans la base de données Cairn3_PUB
 * @author @cairn.info - www.cairn.info
 * @author Julien CADET
 */
class ManagerCairn3Pub extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    public function getListArticleOnCairn3($idNumero) {
        // Prépération de la requête
        // Récupération de l'ID de l'article sur CAIRN3 selon la valeur de référence ($idArticle = ID de l'article sur CAIRN INT).
        $sql = "SELECT ARTICLE.ID_ARTICLE, ARTICLE.ID_ARTICLE
                FROM ARTICLE
                WHERE ARTICLE.ID_NUMPUBLIE = ?";
        $numeroCairn = $this->executerRequete($sql, array($idNumero));
        return $numeroCairn->fetchAll(PDO::FETCH_KEY_PAIR); // On récupère un tableau dont la valeur est également l'ID (ex.: [AFCO_248_0061] => "AFCO_248_0061")
        //return $numeroInt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérification de l'existance d'un article traduit et renvoi son ID
    public function checkIfArticleOnCairn3($idArticle) {

        // Prépération de la requête
        // Récupération de l'ID de l'article sur CAIRN3 selon la valeur de référence ($idArticle = ID de l'article sur CAIRN INT).
        $sql = "SELECT ARTICLE.ID_REVUE, ARTICLE.ID_NUMPUBLIE, ARTICLE.ID_ARTICLE
                FROM ARTICLE
                WHERE ARTICLE.ID_ARTICLE = ? AND ARTICLE.LANGUE = 'fr'
                LIMIT 1";
        $articleCairn = $this->executerRequete($sql, array($idArticle));
        return $articleCairn->fetch(PDO::FETCH_ASSOC);
    }

    // Récupération des méta-data d'un article
    public function getMetadataArticleOnCairn3($idArticle) {

        // Préparation de la requête
        // Récupération des données de l'article sur CAIRN3
        $sql = "SELECT
                    ARTICLE.ID_REVUE,
                    ARTICLE.ID_NUMPUBLIE,
                    ARTICLE.ID_ARTICLE,
                    ARTICLE.TITRE,
                    ARTICLE.SOUSTITRE,
                    ARTICLE.PAGE_DEBUT,
                    ARTICLE.PAGE_FIN,
                    ARTICLE.DOI,
                    GROUP_CONCAT(CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', AUTEUR_ART.ATTRIBUT)) AS AUTEUR
                FROM ARTICLE
                    LEFT JOIN AUTEUR_ART ON ARTICLE.ID_ARTICLE = AUTEUR_ART.ID_ARTICLE
                    LEFT JOIN AUTEUR ON AUTEUR_ART.ID_AUTEUR = AUTEUR.ID_AUTEUR
                WHERE ARTICLE.ID_ARTICLE = ?
                GROUP BY ARTICLE.ID_ARTICLE
                LIMIT 1
        ";
        $articleCairn = $this->executerRequete($sql, array($idArticle));
        return $articleCairn->fetch(PDO::FETCH_ASSOC);
    }

    // Récupération des méta-data d'un numero
    public function getMetadataNumeroOnCairn3($idNumero) {

        // Préparation de la requête
        // Récupération des données du numero sur CAIRN3
        $sql = "SELECT NUMERO.NUMERO, NUMERO.ANNEE, NUMERO.VOLUME, REVUE.TITRE
                FROM NUMERO
                INNER JOIN REVUE
                ON REVUE.ID_REVUE = NUMERO.ID_REVUE
                WHERE NUMERO.ID_NUMPUBLIE = ? limit 1";
        $numeroCairn = $this->executerRequete($sql, array($idNumero));
        return $numeroCairn->fetch(PDO::FETCH_ASSOC);
    }

    // Récupération des méta-data d'une revue
    public function getMetadataRevueOnCairn3($idRevue) {

        // Préparation de la requête
        // Récupération des données du numero sur CAIRN3
        $sql = "SELECT REVUE.TITRE, REVUE.URL_REWRITING
                FROM REVUE
                WHERE REVUE.ID_REVUE = ? limit 1";
        $numeroCairn = $this->executerRequete($sql, array($idRevue));
        return $numeroCairn->fetch(PDO::FETCH_ASSOC);
    }
}
