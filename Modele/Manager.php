<?php

require_once 'Framework/Modele.php';

/**
 * Provide update services to Cairn Pub DB
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com
 * @author Benjamin Hennon
 * @todo : documentation
 */
class Manager extends Modele {

    public function updateUserAlertes($oldemail, $email) {
        $sql = "UPDATE ALERTE SET ID_USER = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($email, $oldemail));
    }

    public function insertAlertePartenaire($userId) {
        $sql = "INSERT INTO `ALERTE` (ID_USER, ID_ALERTE, TYPE_ALERTE, DATE) VALUES (?,'PARTENAIRE','P',NOW())";
        return $this->executerRequete($sql, array($userId));
    }

    public function removeAlertePartenaire($userId) {
        $sql = "DELETE FROM `ALERTE` WHERE ID_USER = ? AND TYPE_ALERTE = 'P'";
        return $this->executerRequete($sql, array($userId));
    }

    public function existsAlert($idUser, $idAlerte) {
        $sql = "SELECT COUNT(*) FROM ALERTE WHERE ID_USER = ? AND ID_ALERTE = ?";
        $countAlerts = $this->executerRequete($sql, [$idUser, $idAlerte])->fetch()[0];
        return $countAlerts > 0;
    }

    public function addAlerts($id_user, $id_alerte, $type) {
        $sql = "INSERT INTO ALERTE (ID_USER,ID_ALERTE,TYPE_ALERTE,FUPDATE,DATE) VALUES (?,?,?,0,NOW())";
        $this->executerRequete($sql, array($id_user, $id_alerte, $type));
    }

    public function deleteAlerts($id_user, $id_alerte) {
        $sql = "DELETE FROM ALERTE WHERE ID_USER = ? AND ID_ALERTE = ?";
        $this->executerRequete($sql, array($id_user, $id_alerte));
    }

    public function updateUserEditeur($idEditeur, $codeAbo, $idUser){
        $sql = "UPDATE USER_EDITEUR SET ID_USER = ? WHERE ID_EDITEUR = ? AND REF_EDITEUR = ?";
        $this->executerRequete($sql,array($idUser,$idEditeur,$codeAbo));
    }

}
