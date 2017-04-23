<?php

require_once 'Modele/ContentTools.php';


/**
 * @author Jeremy Liessi
 */
class BonADiffuser {
    
    private $contentTools;
    
    function __construct() {
        $this->contentTools = new ContentTools('dsn_tools');
    }
    /*
     *  verifie la validité d'un token
     **/
    function checkTokenForBAD($id_numpublie, $token, $numero_date_miseenligne) {
        $oneToken = $this->contentTools->searchTokenBad($id_numpublie, $token); 
        
        
        if (!empty($oneToken) || $oneToken['ID_NUMPUBLIE'] == $numero["NUMERO_ID_NUMPUBLIE"]) //verif token
        {
            $dateTimeNow = new DateTime(date("Y-m-j"));
            $noDate = false;
            /*if(isset($oneToken['DATE_TRANSFERT'])) 
            {
                $dateProg = new DateTime($oneToken['DATE_TRANSFERT']);
            }
            elseif(isset($numero_date_miseenligne))
            {
                $pasDeDate = true;
                $dateProg = new DateTime($numero_date_miseenligne);
            }
            else
            {
               $dateProg = new DateTime("3000-01-01");
            }*/
            if(!isset($oneToken['DATE_TRANSFERT']) || $oneToken['DATE_TRANSFERT'] == "0000-00-00")
            {
            	$noDate = true;
            }
            else 
            {
                $dateProg = new DateTime($oneToken['DATE_TRANSFERT']);
                $dateProg = new DateTime($dateProg->format('Y-m-d')); // on tronque les hh:mm:ss
            }
            
            if(($dateProg > $dateTimeNow) || $noDate ) //si la date programmée n'est pas passée ou si il n'y en a pas
            {
                return $oneToken; 
            }
            else 
            {
                return false;
            }
        }
    }
    
    
    function setInterfictionForBAD($id_numpublie, $token) {
         return $this->contentTools->setInterfictionForBAD($id_numpublie, $token);
    }
    
    function setDateTransfertForBAD($id_numpublie, $token, $dateTransfet)
    {
    	return $this->contentTools->setDateTransfertBAD($id_numpublie, $token, $dateTransfet);
    }
}
