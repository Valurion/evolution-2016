<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Configuration.php';

abstract class ModeleSoap {

    private static $client;
    private static $clientCnt;

    private static function getConnection($which) {
        if (self::$$which === null) {
            $connexionUrl = $which . "ConnexionURL";
            self::$$which = new SoapClient(Configuration::get($connexionUrl));
        }
        return self::$$which;
    }

    protected function executerRequete($which, $method, $query) {
        $resultat = self::getConnection($which)->$method($query);
        return $resultat;
    }

}
