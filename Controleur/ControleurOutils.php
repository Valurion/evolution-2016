<?php

/**
 * CONTROLER - Outils
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Miguel Ferreira
 * @author Miguel Ferreira
 */
require_once 'Framework/Controleur.php';

require_once 'Modele/Content.php';

class ControleurOutils extends Controleur {

    private $content;

// empty constructor
    public function __construct() {
        $this->content = new Content();
    }

    // call the templated view
    public function index() {
        $this->genererVue(null);
    }

    public function contacts() {
        $serviceClients = Configuration::get("serviceClients");
        $supportTechnique = Configuration::get("supportTechnique");
        $administrateur = Configuration::get("administrateur");
        $serviceCommercial = Configuration::get("serviceCommercial");
        $serviceAdministratif = Configuration::get("serviceAdministratif");

        $headers = Service::get('Webtrends')->webtrendsHeaders('corporate-*-contact', $this->authInfos);

        $this->genererVue(array('serviceClients' => $serviceClients , 'supportTechnique' => $supportTechnique , 'administrateur' => $administrateur , 'serviceCommercial' => $serviceCommercial , 'serviceAdministratif' => $serviceAdministratif), null, null, $headers);
    }

    public function contactsConfirm() {
        $headers = Service::get('Webtrends')->webtrendsHeaders('corporate-*-contact', $this->authInfos);
        $headers[3]["attributes"][1]["value"] = "contact (formulaire envoyé)";
        $this->genererVue(null, 'contact-confirm.php', null, $headers);
    }

    public function fluxRss() {
        $revues = $this->content->getRevuesByType(1);
        $headers = Service::get('Webtrends')->webtrendsHeaders('flux-rss', $this->authInfos);
        $this->genererVue(array('revues'=>$revues, 'rss_path'=>Configuration::get('rss_path')), null, null, $headers);
    }

    public function sendContactMail() {
        $prenom = $this->requete->getParametre('prenom');
        $nom = $this->requete->getParametre('nom');
        $email = $this->requete->getParametre('email');
        $institution = $this->requete->getParametre('institution');        
        $service = $this->requete->getParametre('service');
        $message = $this->requete->getParametre('message');
        $telephone = $this->requete->getParametre('telephone');
        $copie = $this->requete->getParametre('copie');

        // Formatage des données
        // Pour coller au mieux avec le template d'e-mail on ajoute des informations.
        if($institution != "") {
            $institutionForMail = "de ".$institution; 
            $institutionForCopy = " lors de votre connexion via ".$institution;
        }
        // Ajout du numéro de téléphone
        if($telephone != "") {
            $telephoneForMail = ", ".$telephone;
        }

        if($copie == 'on')
        {
            try
            {
                Service::get('Mailer')->sendMailFromTpl('/Vue/Outils/mailTemplateContactsCairn.xml', array('to' => $service , 'from' => $email, 'text' => $message , 'prenom' => $prenom , 'nom' => $nom , 'institution' => $institutionForMail, 'telephone' => $telephoneForMail, 'date' => date('d/m/Y') ));
                Service::get('Mailer')->sendMailFromTpl('/Vue/Outils/mailTemplateContactsCopy.xml', array('to' => $email , 'from' => $service, 'institution' => $institutionForCopy, 'text' => $message , 'date' => date('d/m/Y') ));
            }
            catch(Exception $e)
            {
                echo '1';
            }
        }
        else
        {
            try
            {
                Service::get('Mailer')->sendMailFromTpl('/Vue/Outils/mailTemplateContactsCairn.xml', array('to' => $service , 'from' => $email, 'text' => $message , 'prenom' => $prenom , 'nom' => $nom , 'date' => date('d/m/Y') ));
            }
            catch(Exception $e)
            {
                echo '1';
            }
        }
    }

    public function sendFeedbackMail() {
        // Bon, on lutte contre le spam comme on peut...
        if (!$this->requete->existeParametre('f_i-am-not-a-robot')) {
            echo 'Hello robot !';
            return;
        }
        $iAmNotARobot = $this->requete->getParametre('f_i-am-not-a-robot');
        if ($iAmNotARobot !== 'i-am-a-castor-sauvage') {
            echo 'Hello robot !';
            return;
        }
        // Les paramètres à fournir au template de mail
        $templateParams = [
            'from' => $this->requete->getParametre('f_email'),
            'to' => Configuration::get('feedback'),
            'date' => date('d/m/Y H:i:s'),
            'category' => $this->requete->getParametre('f_category'),
            'text' => $this->requete->getParametre('f_message'),
            'from_site' => $this->requete->getParametre('f_from'),
            'from_url' => $this->requete->getParametre('f_url'),
            'from_inst' => (isset($this->authInfos['I']['ID_USER'])) ? $this->authInfos['I']['ID_USER'] : '',
            'from_user' => (isset($this->authInfos['U']['ID_USER'])) ? $this->authInfos['U']['ID_USER'] : '',
            'IS_USED_FROM' => true,
        ];
        // On échappe les éventuelles balises html pour des raisons de sécurités
        foreach ($templateParams as $key => $value) {
            $templateParams[$key] = strip_tags($value);
        }
        // On envoi le mail
        Service::get('Mailer')->sendMailFromTpl('/Vue/Outils/mailTemplateFeedbackCairn.xml', $templateParams);
    }
}
