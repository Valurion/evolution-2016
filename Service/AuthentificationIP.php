<?php

require_once 'Modele/ContentAbo.php';
//require_once 'Modele/ManagerCom.php';

class AuthentificationIP {

    private $contentAbo = null;

    function __construct() {
        $this->contentAbo = new ContentAbo('dsn_abo');
    }

    public function loginByIP($requete) {
        //$ipClient = '194.57.83.122';
        $ipClient = $this->getIpClient($requete);
        //var_dump($requete);
        /*if($ipClient == null || strpos($ipClient,'157.164.') !== FALSE){
            $managerCom = new ManagerCom('dsn_com');
            $managerCom->insertIpReceived($ipClient, print_r($requete,true));
        }*/
        if (!$this->ipValid($ipClient)) {
            return NULL;
        } else {
            $ipAbo = $this->contentAbo->searchIpAbo2($ipClient);
            if ($ipAbo) {
                $ipAbo['IP_USER'] = $ipClient;
            }
            return $ipAbo;
            /* $ipAbos = $this->contentAbo->searchIpAbo($ipClient);
              $ipAboFound = null;
              foreach($ipAbos as $ipAbo){
              //Deuxième validation, côté PHP.
              //Elle va permettre d'exclure les records renvoyés à cause d'un mauvais SUBNET (qui aura entraîné un mauvais calcul de broadcast)
              if($this->ipInRange($ipClient, $ipAbo['NETWORK_ADDRESS'], $ipAbo['SUBNET_MASK'])){
              $ipAboFound = $ipAbo;
              break;
              }
              }
              return $ipAboFound; */
        }
    }

    /* plus utilisé, fait côté mysql
      private function ipInRange($ipClient, $networkAddress, $subnetMask) {
      if ($subnetMask == '255.255.255.255') {
      return ($ipClient == $networkAddress);
      } else {
      $ipClient = ip2long($ipClient);
      $networkAddress = ip2long($networkAddress);
      $subnetMask = ip2long($subnetMask);
      return (($ipClient & $subnetMask) == $networkAddress);
      }
      } */

    private function ipValid($ip) {
        return (0 == preg_match('/^\d{1,3}(\.\d{1,3}){3}$/', $ip)) ? false : true;
    }

    public function getIpClient($requete) {
        /*if ($requete->existeParametre('X-Forwarded-For')) {
            return $requete->getParametre('X-Forwarded-For');
        } else if ($requete->existeParametre('HTTP_X_FORWARDED_FOR')) {
            $iplist = explode(',', $requete->getParametre('HTTP_X_FORWARDED_FOR'));
            return $iplist[0];
        } else if ($requete->existeParametre('HTTP_X_FORWARDED')) {
            return $requete->getParametre('HTTP_X_FORWARDED');
        } else if ($requete->existeParametre('HTTP_X_CLUSTER_CLIENT_IP')) {
            return $requete->getParametre('HTTP_X_CLUSTER_CLIENT_IP');
        } else if ($requete->existeParametre('HTTP_FORWARDED_FOR')) {
            return $requete->getParametre('HTTP_FORWARDED_FOR');    
        } else if ($requete->existeParametre('HTTP_FORWARDED')) {
             return $requete->getParametre('HTTP_FORWARDED');
        } else*/
        if ($requete->existeParametre('REMOTE_ADDR')) {
            return $requete->getParametre('REMOTE_ADDR');
        } else {
            return null;
        }
    }
    
}
