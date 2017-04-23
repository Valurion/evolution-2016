<?php

/**
 * Classe de gestion des services applicatifs
 * 
 * @version 1.0
 * @author Benjamin
 */
class Service {

    /** Tableau des services déjà instanciés */
    private static $services = array();

    /**
     * Renvoie une instance d'un service
     * 
     * @param string $nom Nom du service
     * @param array $args Argument à donner au constructeur du service
     * @return instance du service
     */
    public static function get($nom, $args = null) {
        $serviceName = ucfirst($nom);
        if (array_key_exists($serviceName, self::$services)) {
            $service = self::$services[$serviceName];
        } else {
            require_once 'Service/' . $serviceName . '.php';
            $service = new $serviceName($args != null ? implode(',', $args) : null);
            self::$services[$serviceName] = $service;
        }
        return $service;
    }

}
