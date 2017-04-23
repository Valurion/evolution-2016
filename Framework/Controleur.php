<?php

require_once 'Requete.php';
require_once 'Vue.php';
require_once 'Service.php';

/**
 * Classe abstraite Controleur
 * Fournit des services communs aux classes Controleur dérivées
 *
 * @version 1.0
 * @author Baptiste Pesquet
 */
abstract class Controleur {

    /** Action à réaliser */
    private $action;

    /** Requête entrante */
    protected $requete;

    /** Infos d'authentification */
    protected $authInfos = null;

    /**
     * Définit la requête entrante
     *
     * @param Requete $requete Requete entrante
     */
    public function setRequete(Requete $requete) {
        $this->requete = $requete;
        //Dans la requête on reçoit également les infos du cookie de connexion => on fait la lecture ici pour en bénéficier dans les autres controleurs
        $this->authInfos = Service::get('Authentification')
                ->setToken($requete->existeParametre("cairn_token") ? $requete->getParametre("cairn_token") : null)
                ->setGuestToken($requete->existeParametre("cairn_guest") ? $requete->getParametre("cairn_guest") : null,$requete->getParametre("REQUEST_URI",null))
                ->readToken($requete);
    }

    /**
     * Exécute l'action à réaliser.
     * Appelle la méthode portant le même nom que l'action sur l'objet Controleur courant
     *
     * @throws Exception Si l'action n'existe pas dans la classe Controleur courante
     */
    public function executerAction($action) {
        if (method_exists($this, $action)) {
            $this->action = $action;
            $this->{$this->action}();
        } else {
            $classeControleur = get_class($this);
            throw new Exception("Action '$action' non définie dans la classe $classeControleur");
        }
    }

    /**
     * Méthode abstraite correspondant à l'action par défaut
     * Oblige les classes dérivées à implémenter cette action par défaut
     */
    public abstract function index();

    /**
     * Génère la vue associée au contrôleur courant
     *
     * @param array $donneesVue Données nécessaires pour la génération de la vue
     * @param array $headers Les métadonnées de la vue. Par exemple en html, tout ce qui est dans /html/head
     */
    protected function genererVue($donneesVue = array(), $my_view = null, $gabarit = null, $headers = null) {
        // Détermination du nom du fichier vue à partir du nom du contrôleur actuel
        $classeControleur = get_class($this);
        $controleur = str_replace("Controleur", "", $classeControleur);

        // Instanciation et génération de la vueF
        if ($my_view == null) {
            $vue = new Vue($this->action, $controleur, null, $headers);
        } else {
            $vue = new Vue($this->action, $controleur, $my_view, $headers);
        }
        if ($this->authInfos) {
            $donneesVue["authInfos"] = $this->authInfos;
            $donneesVue["modeAchat"] = Service::get('ControleAchat')->getModeAchat($this->authInfos);
        }
        $donneesVue['tabsMode'] = Configuration::get('tabsMode');
        $donneesVue['breadcrumbMode'] = Configuration::get('breadcrumbMode');
        $donneesVue['vign_url'] = Configuration::get('vign_url');
        $donneesVue['vign_path'] = Configuration::get('vign_path');
        $vue->generer($donneesVue, $gabarit);
    }

    public function isUserConnected() {
        return isset($this->authInfos['U']);
    }

}
