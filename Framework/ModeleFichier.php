<?php

require_once 'Configuration.php';

/**
 * Classe abstraite Modèle.
 * Centralise les services d'accès à un fichier.
 *
 * @author benjamin
 */
abstract class ModeleFichier {

    /** Nom du fichier
      Statique donc partagé par toutes les instances des classes dérivées */
    protected static $file;

    /**
     * Lit un fichier
     *
     * @param string $fichier Nom du fichier
     * @param string $type Type de fichier
     * @return Contenu du fichier
     */
    protected function executerRequete($fichier, $type) {
        switch ($type) {
            case 'JSON':
                $resulat = json_decode(file_get_contents(self::getFile($fichier)));
                break;
            case 'XML':
                //$resultat = simplexml_load_file(self::getFile($fichier));
                $resultat = DOMDocument::load(self::getFile($fichier));
                break;
            default:
                $resultat = file_get_contents(self::getFile($fichier));
                break;
        }

        return $resultat;
    }

    /**
     * Renvoie un fichier en vérifiant son existence
     *
     * @return $file Fichier présent sur le serveur
     */
    private static function getFile($fichier) {
        if (self::$file === null) {
            self::$file = $fichier;
            if (!file_exists(self::$file)) {
                $prefixPath = Configuration::get("prefixPath");
                // Création de la connexion
                self::$file = $prefixPath . "/" . $fichier;
                if (!file_exists(self::$file)) {
                    //throw new Exception('File '.self::$file.' not found');
                    return FALSE;
                }
            }
        }
        return self::$file;
    }

}
