<?php

require_once 'Framework/Modele.php';

/**
 * Provide update services to Cairn Commercial DB 
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com
 * @author Benjamin Hennon
 * @todo : documentation
 */
class ManagerAbo extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    public function createAbonne($email, $nom, $mdp) {
        $sql = "INSERT INTO `ABONNE` (ID, NOM, B_USER, B_PSW, NBRE_AB, NBRE_IP) 
                SELECT MAX(ID)+1,?,?,?,1,1 FROM ABONNE";
        return $this->executerRequete($sql, array($nom, $email, $mdp));
    }

    public function updateAbonne($oldemail, $email, $mdp) {
        $sql = "UPDATE `ABONNE` SET B_USER = ?, B_PSW = ?
                WHERE B_USER = ?";
        return $this->executerRequete($sql, array($email, $mdp, $oldemail));
    }

    public function updateAbonnePassword($mdp, $email) {
        $sql = "UPDATE `ABONNE` SET B_PSW = ?
                WHERE B_USER = ?";
        return $this->executerRequete($sql, array($mdp, $email));
    }

    public function updateMaxSessions($idUser, $newMax) {
        $sql = "UPDATE ABONNE SET NBRE_AB = ? WHERE B_USER = ?";
        return $this->executerRequete($sql, array($newMax, $idUser));
    }

    public function updateMaxSessionsIP($idUser, $newMax) {
        $sql = "UPDATE ABONNE SET NBRE_IP = ? WHERE B_USER = ?";
        return $this->executerRequete($sql, array($newMax, $idUser));
    }

}
