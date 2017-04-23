<?php

/**
 * CONTROLER - À Propos
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Miguel Ferreira
 * @author Miguel Ferreira
 */
require_once 'Framework/Controleur.php';
require_once 'Modele/Content.php';

class ControleurApropos extends Controleur {

    // empty constructor
    public function __construct() {
        $this->content = new Content();
    }

    // call the templated view
    public function index() {
        $this->genererVue(null, null, null, Service::get('Webtrends')->webtrendsHeaders('corporate-*-a-propos', $this->authInfos));
    }

    public function servicesEditeurs() {
        $this->genererVue(null, null, null, Service::get('Webtrends')->webtrendsHeaders('corporate-*-services-editeur', $this->authInfos));
    }

    /**
     * Modification de la méthode le 07/01/2016.
     * par Dimitry (Cairn).
     *
     */
    public function servicesInstitutions() {
        $licences = $this->content->getLicences('revues');

        //Liste des id, pour les ouvrages et les poches.
        $idOuvrages = array('90', '87', '88', '89', '115', '116', '118', '117', '122', '123', '121',
            '120', '138', '55', '66', '80', '101', '124', '51', '152', '160');
        $idPoches = array('59', '54', '60', '53', '62', '63', '64', '65', '130', '52', '73');


        $licencesOuvrages = $this->content->getListLicences($idOuvrages);
        $licencesPoches = $this->content->getListLicences($idPoches);

        $this->genererVue([
            'licences' => $licences,
            'licencesOuvrages' => $licencesOuvrages,
            'licencesPoches' => $licencesPoches,
        ], null, null, Service::get('Webtrends')->webtrendsHeaders('corporate-*-services-institutions', $this->authInfos));
    }

    public function servicesParticuliers() {
        $this->genererVue(null, null, null, Service::get('Webtrends')->webtrendsHeaders('corporate-*-services-particuliers', $this->authInfos));
    }

    public function conditions() {
        $this->genererVue(null, null, null, Service::get('Webtrends')->webtrendsHeaders('corporate-*-conditions', $this->authInfos));
    }

    public function conditionsVente() {
        $this->genererVue(null, null, null, Service::get('Webtrends')->webtrendsHeaders('corporate-*-conditions-vente', $this->authInfos));
    }

    public function viePrivee() {
        $this->genererVue(null, null, null, Service::get('Webtrends')->webtrendsHeaders('corporate-*-vie-privee', $this->authInfos));
    }

    public function help() {
        $this->genererVue(null);
    }

}
