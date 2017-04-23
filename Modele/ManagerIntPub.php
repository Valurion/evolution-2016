<?php

require_once 'Framework/Modele.php';

/**
 * Vérifie l'existance de traductions en ANGLAIS directement dans la base de données CairnINT_PUB
 * @author @cairn.info - www.cairn.info
 * @author Julien CADET
 */
class ManagerIntPub extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    // Vérification de l'existance d'une revue traduite et renvoi son ID
    public function checkIfRevueOnCairnInt($idRevue) {

        // Prépération de la requête
        // Récupération de l'ID de la revue sur CAIRN-INT selon la valeur de référence ($idRevue = ID de la revue sur CAIRN).
        $sql = "SELECT REVUE.ID_REVUE, REVUE.URL_REWRITING_EN
                FROM REVUE
                WHERE REVUE.ID_REVUE_S = ?
                LIMIT 1";
        $revueInt = $this->executerRequete($sql, array($idRevue));
        return $revueInt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérification de l'existance d'un numéro traduit et renvoi son ID
    public function checkIfNumeroOnCairnInt($idNumero) {

        // Prépération de la requête
        // Récupération de l'ID du numéro sur CAIRN-INT selon la valeur de référence ($idNumero = ID du numéro sur CAIRN).
        $sql = "SELECT NUMERO.ID_REVUE, NUMERO.ID_NUMPUBLIE
                FROM NUMERO
                WHERE NUMERO.ID_NUMPUBLIE_S = ?
                LIMIT 1";
        $numeroInt = $this->executerRequete($sql, array($idNumero));
        return $numeroInt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérification de l'existance d'un article traduit et renvoi son ID
    public function checkIfArticleOnCairnInt($idArticle) {

        // Prépération de la requête
        // Récupération de l'ID de l'article sur CAIRN-INT selon la valeur de référence ($idArticle = ID de l'article sur CAIRN).
        $sql = "SELECT ARTICLE.ID_REVUE, ARTICLE.ID_NUMPUBLIE, ARTICLE.ID_ARTICLE, ARTICLE.URL_REWRITING_EN
                FROM ARTICLE
                WHERE ARTICLE.ID_ARTICLE_S = ? AND ARTICLE.LANGUE_INTEGRALE = 'en'
                LIMIT 1";
        $articleInt = $this->executerRequete($sql, array($idArticle));
        return $articleInt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérification de l'existence d'un résumé traduit et renvoi 1 (oui) ou 0 (non)
    public function checkIfResumeOnCairnInt($idResume) {

        // Prépération de la requête
        // Récupération de l'ID de l'article sur CAIRN-INT selon la valeur de référence ($idResume = ID de l'article sur CAIRN).
        /*$sql = "SELECT ARTICLE.ID_REVUE, ARTICLE.ID_NUMPUBLIE, ARTICLE.ID_ARTICLE
                FROM ARTICLE
                LEFT JOIN RESUMES
                ON RESUMES.ID_ARTICLE = ARTICLE.ID_ARTICLE
                WHERE ARTICLE.ID_ARTICLE_S = ? AND RESUMES.RESUME_EN != ''
                LIMIT 1";*/
        $sql = "SELECT count(*) as count, ARTICLE.ID_ARTICLE, ARTICLE.URL_REWRITING_EN
                FROM ARTICLE
                LEFT JOIN RESUMES
                ON RESUMES.ID_ARTICLE = ARTICLE.ID_ARTICLE
                WHERE ARTICLE.ID_ARTICLE_S = ? AND RESUMES.RESUME_EN != ''
                LIMIT 1";
        $resumeInt = $this->executerRequete($sql, array($idResume));
        return $resumeInt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupération des méta-data d'un article
    public function getMetadataArticleOnCairnInt($idArticle) {

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
                WHERE ARTICLE.ID_ARTICLE_S = ?
                GROUP BY ARTICLE.ID_ARTICLE
                LIMIT 1
            ";
        $articleCairn = $this->executerRequete($sql, array($idArticle));
        return $articleCairn->fetch(PDO::FETCH_ASSOC);
    }

    // Récupération de la liste des articles traduits
    public function getListArticleOnCairnInt($idNumero) {

        // Prépération de la requête
        // Récupération de la liste des articles sur CAIRN-INT selon la valeur de référence ($idNumero = ID de la revue)
        $sql = "SELECT ARTICLE.ID_ARTICLE_S, ARTICLE.ID_ARTICLE, ARTICLE.URL_REWRITING_EN
                FROM ARTICLE
                WHERE ARTICLE.ID_NUMPUBLIE = ? AND ARTICLE.LANGUE_INTEGRALE = 'en'";
        $articleInt = $this->executerRequete($sql, array($idNumero));
        //return $articleInt->fetchAll(PDO::FETCH_KEY_PAIR);
        return $articleInt->fetchAll(PDO::FETCH_GROUP);
    }

    // Récupération de la liste des contributions à une revue d'un auteur
    // L'ID de l'auteur n'étant pas identique sur Cairn et Cairn-Int, on utilise une paire de valeur (NOM et PRENOM)
    public function getListOuvragesAuteurOnCairnInt($nomAuteur, $prenomAuteur) {

        // Prépération de la requête
        // Récupération de la liste des revues, articles sur CAIRN-INT selon la valeur de référence ($idAuteur = ID de l'auteur)
        $sql = "SELECT N.ID_NUMPUBLIE_S, N.ID_NUMPUBLIE, N.ANNEE, N.NUMERO, R.URL_REWRITING_EN
                FROM AUTEUR_ART as AA
                INNER JOIN AUTEUR as AUTEUR
                ON AUTEUR.ID_AUTEUR = AA.ID_AUTEUR
                INNER JOIN NUMERO as N
                ON N.ID_NUMPUBLIE = AA.ID_NUMPUBLIE
                INNER JOIN REVUE as R
                ON R.ID_REVUE = N.ID_REVUE
                WHERE AUTEUR.NOM = ? AND AUTEUR.PRENOM = ? AND N.ID_NUMPUBLIE_S != ''";
        $articleInt = $this->executerRequete($sql, array($nomAuteur, $prenomAuteur));
        return $articleInt->fetchAll(PDO::FETCH_GROUP);
    }

    public function getListArticlesAuteurOnCairnInt($nomAuteur, $prenomAuteur) {

        // Prépération de la requête
        // Récupération de la liste des revues, articles sur CAIRN-INT selon la valeur de référence ($idAuteur = ID de l'auteur)
        $sql = "SELECT A.ID_ARTICLE_S, A.ID_ARTICLE, A.URL_REWRITING_EN
                FROM AUTEUR_ART as AA
                INNER JOIN AUTEUR as AUTEUR
                ON AUTEUR.ID_AUTEUR = AA.ID_AUTEUR
                INNER JOIN ARTICLE as A
                ON A.ID_ARTICLE = AA.ID_ARTICLE
                WHERE AUTEUR.NOM = ? AND AUTEUR.PRENOM = ? AND A.ID_ARTICLE_S != ''";
        $articleInt = $this->executerRequete($sql, array($nomAuteur, $prenomAuteur));
        return $articleInt->fetchAll(PDO::FETCH_GROUP);
    }




}
