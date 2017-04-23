<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Configuration.php';
require_once 'include/JsonRpcPth.php';

abstract class ModelejsonRPC {

    private static $client = array();
    protected $current = 'search';

    private static function getConnection($current) {
        if (!isset(self::$client[$current])) {
            // Récupération des paramètres de configuration BD
            $host = Configuration::get($current . "Host");
            $port = Configuration::get($current . "Port");
            $URI = Configuration::get($current . "URI");
            // Création de la connexion

            self::$client[$current] = new JsonRpcPth("http://$host:$port/$URI");
        }
        return self::$client[$current];
    }

    protected function selectCurrent($current) {
        $this->current = $current;
    }

    protected function executerRequete($method, $query) {
        $resultat = self::getConnection($this->current)->$method($query);
        return $resultat;
    }

}
