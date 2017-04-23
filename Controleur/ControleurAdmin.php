<?php

/**
 * CONTROLER - Control the navigation for consultation pages
 * @author ©Pythagoria - www.pythagoria.com
 * @author benjamin
 */
require_once 'Framework/Controleur.php';

require_once 'Modele/ContentCom.php';
require_once 'Modele/ManagerCom.php';
require_once 'Modele/ManagerAbo.php';
require_once 'Modele/Content.php';
require_once 'Modele/Manager.php';

class ControleurAdmin extends Controleur {

    //S'occupe des select sur la base commerciale
    private $contentCom;
    //S'occupe des insert/update sur la base commerciale
    private $managerCom;
    private $managerAbo;
    //S'occupe des select sur la base pub
    private $content;
    //S'occupe des insert/update sur la base pub
    private $manager;

    public function __construct() {
        $this->content = new Content();
        $this->manager = new Manager();
        $this->contentCom = new ContentCom('dsn_com');
        $this->managerCom = new ManagerCom('dsn_com');
        $this->managerAbo = new ManagerAbo('dsn_abo');
    }

    public function index() {
        $this->genererVue(null);
    }
    
    public function administration(){        
        $this->genererVue(null, 'administration.php','gabaritAdmin.php');
    }
    public function statistiquesConsultation(){      
        if( ($this->requete->existeParametre("begin_date")) || ($this->requete->existeParametre("end_date")) ) {
            
            $this->requete->existeParametre("begin_date") ? $begin_date = $this->requete->getParametre("begin_date") : $begin_date = '0000-00-00';
            $this->requete->existeParametre("end_date") ? $end_date = $this->requete->getParametre("end_date") : $end_date = (string)date('Y-m-d H:i:s');
            
            $userSessionCounter = $this->contentCom->getUsersCountSessions($begin_date,$end_date);
            $guestSessionCounter = $this->contentCom->getGuestCountSessions($begin_date,$end_date);
            $institutionSessionCounter = $this->contentCom->getInstitutionCountSessions($begin_date,$end_date);
            
            $maxSessionCounter = (int)$userSessionCounter[0] + (int)$guestSessionCounter[0] + (int)$institutionSessionCounter[0];
                        
            $dataBoardUser = $this->contentCom->getDataBoardUserInterval($begin_date,$end_date);
            $dataBoardGuest = $this->contentCom->getDataBoardGuest($begin_date,$end_date);
        }
        else
        {
            $userSessionCounter = $this->contentCom->getUsersCountSessions();
            $guestSessionCounter = $this->contentCom->getGuestCountSessions();
            $institutionSessionCounter = $this->contentCom->getInstitutionCountSessions();
            $maxSessionCounter = (int)$userSessionCounter[0] + (int)$guestSessionCounter[0] + (int)$institutionSessionCounter[0];            
            
            $dataBoardUser = $this->contentCom->getDataBoardUserNow();
            $dataBoardGuest = $this->contentCom->getDataBoardGuest();
        }
        
         
        $this->genererVue(array(
            'maxSessionCounter'=>$maxSessionCounter,
            'userSessionCounter'=>$userSessionCounter,
            'guestSessionCounter'=>$guestSessionCounter,
            'institutionSessionCounter'=>$institutionSessionCounter,
            'dataBoardUser'=>$dataBoardUser,
            'dataBoardGuest' => $dataBoardGuest
        ),'statistiques_consultation.php','gabaritAdmin.php');
    }
    
    
    
    public function gestionUtilisateurs(){
        
        if( $this->requete->existeParametre("id_user") ){
            
            $idUser = $this->requete->getParametre('id_user');            
            $userSessionCounter = $this->contentCom->getCountSessionByUser($idUser);
            $userMaxSessionCounter = $this->contentCom->getMaxSessionByUser($idUser);
            $instMaxSessionCounter = $this->contentCom->getMaxSessionIpByUser($idUser);
            
            $networkAddresses = $this->contentCom->getNetworkAddressesByUser($idUser);
            $idAbonnes = $this->contentCom->getIdAbonnesByUser($idUser);
            
            $cairnParams = $this->contentCom->getCairnParamsByUser($idUser);
            $disciplines = $this->contentCom->getDiscipline();
            $typePubs = $this->contentCom->getTypePub();
            
            $getTypeUser = $this->contentCom->getTypeUser($idUser);
            
            $this->genererVue(array(
                'userSessionCounter'=>$userSessionCounter,
                'userMaxSessionCounter'=>$userMaxSessionCounter,
                'instMaxSessionCounter'=>$instMaxSessionCounter,
                'networkAddresses'=>$networkAddresses,
                'idAbonnes'=>$idAbonnes,
                'cairnParams'=>$cairnParams,
                'disciplines'=>$disciplines,
                'typePubs'=>$typePubs,
                'getTypeUser'=>$getTypeUser
            ), 'gestion_utilisateurs.php','gabaritAdmin.php');
        }
        else
        {
            $this->genererVue(null, 'gestion_utilisateurs.php','gabaritAdmin.php');
        }        
    }
    
    
    
    public function removeNetworkAddress() {
        if( $this->requete->existeParametre("id") ){            
            $this->contentCom->removeNetworkAddress($this->requete->getParametre('id'));
        }
    }
    
    public function editNetworkAddress() {
        if( $this->requete->existeParametre("edit_id") ){            
            
            $line = $this->requete->getParametre('edit_id');
            $address = $this->requete->getParametre('address');
            $mask = $this->requete->getParametre('mask');
            
            $this->contentCom->editNetworkAddress($address,$mask,$line);
        }
    }
    
    public function addNetworkAddress() {
        if( $this->requete->existeParametre("idAbonnes") ){            
            
            $line = $this->requete->getParametre('idAbonnes');
            $address = $this->requete->getParametre('address');
            $mask = $this->requete->getParametre('mask');
            
            $this->contentCom->addNetworkAddress($address,$mask,$line);
        }
    }
    
    public function addCairnParams() {
        if( $this->requete->existeParametre("param") ){            
            
            $param = $this->requete->getParametre('param');
            $value = $this->requete->getParametre('value');
            $user = $this->requete->getParametre('user');
            
            $this->contentCom->addCairnParams($param,$value,$user);
        }
    }
    
    public function removeCairnParam() {
        if( $this->requete->existeParametre("type") ){ 
            $type = $this->requete->getParametre('type');
            $value = $this->requete->getParametre('value');
            $user = $this->requete->getParametre('user');
            
            $this->contentCom->removeCairnParam($type,$value,$user);
        }
    }
    
    public function changeMaxSessions(){
        $idUser = $this->requete->getParametre('user');
        $newMax = $this->requete->getParametre('newMax');
        $this->managerAbo->updateMaxSessions($idUser,$newMax);
    }
    
    public function changeMaxSessionsIP(){
        $idUser = $this->requete->getParametre('user');
        $newMax = $this->requete->getParametre('newMax');
        $this->managerAbo->updateMaxSessionsIP($idUser,$newMax);
    }
}
