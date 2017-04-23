<?php

require_once 'Configuration.php';

/**
 * Classe abstraite Modèle.
 * Centralise les services d'accès à une base de données.
 * Utilise l'API PDO de PHP
 *
 * @version 1.0
 * @author Baptiste Pesquet
 */
abstract class Modele {

    /** Objet PDO d'accès à la BD
      Statique donc partagé par toutes les instances des classes dérivées */
    private static $bdd = array();
    protected $currentDsn = 'dsn'; // par défaut la DB est la db "pub"

    /**
     * Exécute une requête SQL
     *
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return PDOStatement Résultats de la requête
     */

    protected function executerRequete($sql, $params = null) {
        if ($params == null) {
            $resultat = self::getBdd($this->currentDsn)->query($sql);   // exécution directe
        } else {
            $resultat = self::getBdd($this->currentDsn)->prepare($sql); // requête préparée
            $resultat->execute($params);
        }
        return $resultat;
    }

    protected function selectDatabase($dsn) {
        $this->currentDsn = $dsn;
    }


    protected function getLastInsertId() {
        return self::getBdd($this->currentDsn)->lastInsertId();
    }

    /**
     * Renvoie un objet de connexion à la BDD en initialisant la connexion au besoin
     *
     * @return PDO Objet PDO de connexion à la BDD
     */
    private static function getBdd($currentDsn) {
        if (!isset(self::$bdd[$currentDsn]) || self::$bdd[$currentDsn] === null) {
            // Récupération des paramètres de configuration BD
            $dsn = Configuration::get($currentDsn);
            $login = Configuration::get("login");
            $mdp = Configuration::get("mdp");
            // Création de la connexion
            self::$bdd[$currentDsn] = new PDO($dsn, $login, $mdp, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        return self::$bdd[$currentDsn];
    }

    protected function closeDatabase() {
        self::$bdd[$this->currentDsn] = null;
    }

}
