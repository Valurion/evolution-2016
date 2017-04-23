<?php

require_once 'Framework/Modele.php';

/**
 * Provide access services to Cairn tools
 * @version 0.1

 */
class ContentTools extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    /**
     * @author Jeremy Liessi
     * 
     * @param string $id_numpublie
     * @param string $token
     * 
     * Retourne les informations du token s'il existe un et qu'il est bien lié à l'id_numpulie fourni.
     */
    public function searchTokenBad($id_numpublie, $token) {
        $sql = "SELECT * 
                FROM BON_A_DIFFUSER
                WHERE TOKEN = ? AND ID_NUMPUBLIE = ?
                AND INTERDICTION = '0'";
        $oneToken = $this->executerRequete($sql, array($token, $id_numpublie));
        return $oneToken->fetch(PDO::FETCH_ASSOC);
    }
    
    public function setInterfictionForBAD($id_numpublie, $token) {
        $sql = "UPDATE BON_A_DIFFUSER
                SET INTERDICTION = 1, DATE_MAJ = NOW()
                WHERE TOKEN = ? AND ID_NUMPUBLIE = ?";
        $oneToken = $this->executerRequete($sql, array($token, $id_numpublie));
        return 0;
    }
    
    public function setDateTransfertBAD($id_numpublie, $token, $date) {
    	$sql = "UPDATE BON_A_DIFFUSER
                SET DATE_TRANSFERT = ?, DATE_MAJ = NOW()
                WHERE TOKEN = ? AND ID_NUMPUBLIE = ?";
    	$oneToken = $this->executerRequete($sql, array($date, $token, $id_numpublie));
    	return 0;
    }

}
