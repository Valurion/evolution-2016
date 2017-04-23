<?php

require_once 'Controleur.php';
require_once 'Requete.php';
require_once 'Vue.php';

/*
 * Classe de routage des requêtes entrantes.
 *
 * Inspirée du framework PHP de Nathan Davison
 * (https://github.com/ndavison/Nathan-MVC)
 *
 * @version 1.0
 * @author Baptiste Pesquet
 */

class Routeur {

    /**
     * Méthode principale appelée par le contrôleur frontal
     * Examine la requête et exécute l'action appropriée
     */
    public function routerRequete() {
        try {

            // Fusion des paramètres GET et POST de la requête
            // Permet de gérer uniformément ces deux types de requête HTTP
            $requete = new Requete(array_merge($_GET, $_POST, $_COOKIE, $_SERVER, isset($_SESSION) ? $_SESSION : array()));
            Configuration::setRequete($requete);

            if (file_exists(__DIR__ . "/../sync.log")) {
                throw new Exception("La synchronisation des données est en cours. Merci de votre compréhension", -1, null);
            }

            $controleur = $this->creerControleur($requete);
            //var_dump($requete);
            /*
             * BH:
             * A ce stade, on peut déterminer si on va à l'action principale
             * ou si il y a dans la requête un paramètre qui exige un détour
             *
             * Pour l'instant, je contrôle "en dur", mais il serait préférable
             * d'avoir cela en config.
             * (on peut imaginer l'association
             *  'nom_du_parametre"=>("critere de détour","action si critère non rempli")
             * )
             */
            if ($requete->existeParametre('USER') && !$requete->existeParametre('SHELL') && !$controleur->isUserConnected()) {
                $requeteDerivee = new Requete(array_merge($_COOKIE, $_SERVER, array("controleur" => "user", "action" => "connectBefore",
                            "connectFrom" => "routeur", "fromString" => ($requete->existeParametre('REQUEST_URI') ? $requete->getParametre('REQUEST_URI') : ''), "USER" => $requete->getParametre('USER'))
                ));
                if ($controleur instanceof ControleurUser) {
                    $controleurDerive = $controleur;
                    $controleurDerive->setRequete($requeteDerivee);
                } else {
                    $controleurDerive = $this->creerControleur($requeteDerivee);
                }
                $actionDerivee = $this->creerAction($requeteDerivee);
                $controleurDerive->executerAction($actionDerivee);
            } else {
                $action = $this->creerAction($requete);
                $controleur->executerAction($action);
            }
        } catch (Exception $e) {
            $this->gererErreur($e);
        }
    }

    /**
     * Instancie le contrôleur approprié en fonction de la requête reçue
     *
     * @param Requete $requete Requête reçue
     * @return Instance d'un contrôleur
     * @throws Exception Si la création du contrôleur échoue
     */
    private function creerControleur(Requete $requete) {
        // Grâce à la redirection, toutes les URL entrantes sont du type :
        // index.php?controleur=XXX&action=YYY&id=ZZZ

        $controleur = "Accueil";  // Contrôleur par défaut
        if ($requete->existeParametre('controleur')) {
            $controleur = $requete->getParametre('controleur');
            // Première lettre en majuscules
            $controleur = ucfirst($controleur);
        }
        // Création du nom du fichier du contrôleur
        // La convention de nommage des fichiers controleurs est : Controleur/Controleur<$controleur>.php
        $classeControleur = "Controleur" . $controleur;
        $fichierControleur = "Controleur/" . $classeControleur . ".php";
        if (file_exists($fichierControleur)) {
            // Instanciation du contrôleur adapté à la requête
            require($fichierControleur);
            $controleur = new $classeControleur();
            $controleur->setRequete($requete);
            return $controleur;
        } else {
            throw new Exception("Fichier '$fichierControleur' introuvable");
        }
    }

    /**
     * Détermine l'action à exécuter en fonction de la requête reçue
     *
     * @param Requete $requete Requête reçue
     * @return string Action à exécuter
     */
    private function creerAction(Requete $requete) {
        $action = "index";  // Action par défaut
        if ($requete->existeParametre('action')) {
            $action = $requete->getParametre('action');
        }
        return $action;
    }

    /**
     * Gère une erreur d'exécution (exception)
     *
     * @param Exception $exception Exception qui s'est produite
     */
    private function gererErreur(Exception $exception) {
        $vue = new Vue('erreur');
        $vue->generer(array('msgErreur' => $exception->getMessage()), 'gabaritErreur.php');
    }

}
