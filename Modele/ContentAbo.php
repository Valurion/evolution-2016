<?php

require_once 'Framework/Modele.php';

/**
 * Provide access services to Cairn Abonnes DB 
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com
 * @author Benjamin Hennon
 * @todo : standardise return types : PDOStatements => Arrays et adresse ; Factorization
 * @todo : documentation
 */
class ContentAbo extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    public function searchIpAbo($ip) {
        $sql = "SELECT ABONNE.ID, ABONNE.NOM, ABONNE.B_USER,
                IP_ABONNE.NETWORK_ADDRESS, IP_ABONNE.SUBNET_MASK,
                ( ~INET_ATON(SUBNET_MASK) & 0xffffffff | INET_ATON(NETWORK_ADDRESS)) AS NUM_BROADCAST,
                INET_NTOA( ~INET_ATON(SUBNET_MASK) & 0xffffffff | INET_ATON(NETWORK_ADDRESS)) AS IP_BROADCAST
                FROM ABONNE
                JOIN IP_ABONNE ON IP_ABONNE.ID = ABONNE.`ID`
                WHERE IGNORE_USER = 0 AND NBRE_IP > 0
                HAVING INET_ATON(?) BETWEEN INET_ATON(NETWORK_ADDRESS) AND NUM_BROADCAST";
        $ipAbo = $this->executerRequete($sql, array($ip));
        return $ipAbo->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchIpAbo2($ip) {
        $sql = "SELECT ABONNE.ID, ABONNE.NOM, ABONNE.B_USER,
                IP_ABONNE.NETWORK_ADDRESS, IP_ABONNE.SUBNET_MASK,
                ( (INET_ATON(?) & INET_ATON(SUBNET_MASK) ) = INET_ATON(NETWORK_ADDRESS) ) AS CHECK_IP
                FROM ABONNE
                JOIN IP_ABONNE ON IP_ABONNE.ID = ABONNE.`ID`
                WHERE IGNORE_USER = 0 AND NBRE_IP > 0
                HAVING CHECK_IP = 1";
        $ipAbo = $this->executerRequete($sql, array($ip));
        return $ipAbo->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserInfos($userId) {
        $sql = "SELECT * FROM `ABONNE` WHERE ID = ?";
        $user = $this->executerRequete($sql, array($userId));
        return $user->fetch(PDO::FETCH_ASSOC);
    }

}
