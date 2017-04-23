<?php

/**
 * CONTROLER - Control the navigation
 * @version 0.1
 * @author ©Cairn - www.cairn.com - Dimitry BERTÉ
 * @author Dimitry BERTÉ
 */
require_once 'Framework/Controleur.php';

/**
 * Controleur permettant de gérer les erreurs.
 */
class ControleurError extends Controleur {

    public function index() {}

    /**
     * Méthode permettant d'afficher la page d'erreur.
     */
    public function error() {

        $uri = 'http://' . Configuration::get('urlSite') . $_SERVER["REQUEST_URI"];

        $header = Service::get('Webtrends')->webtrendsHeaders('page-error', $this->authInfos);

        //Pour le tag WT.cg_n
        $header[2] = array('tagname' => 'meta',
            'attributes' => array(0 => array('name' => 'name', 'value' => 'WT.cg_s'),
                1 => array('name' => 'content', 'value' => "Page [" . $uri . "] n’existe pas")));

        $this->genererVue(null, 'erreur.php', "gabaritErreur.php", $header);
    }

    /**
     * Méthode permettant d'afficher la page d'erreur,
     * pour les erreurs de type 'no id'.
     */
    public function errorNoID() {
        $header = Service::get('Webtrends')->webtrendsHeaders('page-error', $this->authInfos);

        //Pour le tag WT.cg_n
        $header[2] = array('tagname' => 'meta',
            'attributes' => array(0 => array('name' => 'name', 'value' => 'WT.cg_s'),
                1 => array('name' => 'content', 'value' => "Paramètre 'ID_ARTICLE' absent de la requête")));

        $this->genererVue(null, 'erreur.php', "gabaritErreur.php", $header);
    }

    /**
     * Méthode permettant d'afficher la page d'erreur,
     * pour les erreurs au niveau de l'id.
     */
    public function errorID() {
        $header = Service::get('Webtrends')->webtrendsHeaders('page-error', $this->authInfos);

        //Pour le tag WT.cg_n
        $header[2] = array('tagname' => 'meta',
            'attributes' => array(0 => array('name' => 'name', 'value' => 'WT.cg_s'),
                1 => array('name' => 'content', 'value' => "Paramètre 'ID_ARTICLE' erroné dans la requête")));

        $this->genererVue(null, 'erreur.php', "gabaritErreur.php", $header);
    }
}
