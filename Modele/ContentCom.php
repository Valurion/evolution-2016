<?php

require_once 'Framework/Modele.php';

/**
 * Provide access services to Cairn Commercial DB
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com
 * @author Benjamin Hennon
 * @todo : standardise return types : PDOStatements => Arrays et adresse ; Factorization
 * @todo : documentation
 */
class ContentCom extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);

        if (!defined("NOWDATE"))
            define("NOWDATE", date('Y-m-d H:i:s'));
        if (!defined("NOWDATEGUEST"))
            define("NOWDATEGUEST", date('Y-m-d H:i:s', strtotime("- " . (string) Configuration::get('guestSessionDuration') . ' ' . strtolower((string) Configuration::get('guestSessionUnit')) . 's')));
        if (!defined("NOWDATEUSER"))
            define("NOWDATEUSER", date('Y-m-d H:i:s', strtotime("- " . (string) Configuration::get('userSessionDuration') . ' ' . strtolower((string) Configuration::get('userSessionUnit')) . 's')));
        if (!defined("NOWDATEUSERIP"))
            define("NOWDATEUSERIP", date('Y-m-d H:i:s', strtotime("- " . (string) Configuration::get('userIPSessionDuration') . ' ' . strtolower((string) Configuration::get('userIPSessionUnit')) . 's')));
        if (!defined("NOWDATEUSERINST"))
            define("NOWDATEUSERINST", date('Y-m-d H:i:s', strtotime("- " . (string) Configuration::get('userInstSessionDuration') . ' ' . strtolower((string) Configuration::get('userInstSessionUnit')) . 's')));
    }

    public function getUserInfos($userId) {
        $sql = "SELECT * FROM `USER` WHERE ID_USER = ?";
        $user = $this->executerRequete($sql, array($userId));
        return $user->fetch(PDO::FETCH_ASSOC);
    }

    public function getGuestInfos($userId) {
        $sql = "SELECT * FROM USER_GUEST WHERE ID_USER = ?";
        $guest = $this->executerRequete($sql, array($userId));
        return $guest->fetch(PDO::FETCH_ASSOC);
    }

    public function validateLogin($email, $password) {
        $sql = "SELECT * FROM `USER` WHERE ID_USER = ? ";
        $login = $this->executerRequete($sql, array($email/*, $password, $password*/));
        if ($login->rowCount() > 0) {
            $row = $login->fetch(PDO::FETCH_ASSOC);
            if($row["TYPE"] == 'U' && Service::get("CairnToken")->compareMp($row["MOT_PASSE_CR"], $password)){
                return $row;
            }else if($row["TYPE"] == 'I' && Service::get("CairnToken")->compareMp($row["PWD_LOG_CR"], $password)){
                return $row;
            }else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function validateSession($email, $interval, $unit) {
        $sql = "SELECT 1 FROM `USER`
                LEFT JOIN  (SELECT ID_USER, COUNT(*) AS CNT_LOG FROM USER_LOG
                            WHERE USER_LOG.`ID_USER` = ?
                            AND (DATE_FIN IS NULL OR DATE_FIN > NOW())
                            AND (TOUCH_DATE IS NOT NULL AND TOUCH_DATE > NOW() - INTERVAL " . $interval . " " . $unit . "))
                    AS CNT_SESSION_ACTIVE
                    ON CNT_SESSION_ACTIVE.ID_USER = USER.`ID_USER`
                LEFT JOIN " . Configuration::get('db_abo') . ".ABONNE AB
                    ON AB.B_USER = USER.ID_USER
                WHERE USER.ID_USER = ?
                AND IFNULL(CNT_SESSION_ACTIVE.CNT_LOG,0) < IFNULL(`NBRE_AB`,1)";
        $session = $this->executerRequete($sql, array($email, $email));
        return $session->fetch(PDO::FETCH_COLUMN);
    }

    public function validateEmail($email) {
        $sql = "SELECT EMAIL FROM `USER` WHERE ID_USER = ?";
        $login = $this->executerRequete($sql, array($email));
        if ($login->rowCount() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getSumCreditAchat($email) {
        $sql = "SELECT SUM(PRIX) FROM CREDIT_ARTICLE WHERE STATUT = 1 AND ID_USER = ?";
        $sumCredit = $this->executerRequete($sql, array($email));
        return $sumCredit->fetch(PDO::FETCH_COLUMN);
    }

    public function getSumAchatCredit($email) {
        $sql = "SELECT SUM(PRIX) FROM ACHAT "
                . "WHERE NO_COMMANDE IN (SELECT NO_COMMANDE FROM CREDIT_ARTICLE WHERE ID_USER = ? AND STATUT = 1) "
                . "AND STATUT = 1 AND ID_USER = ?";
        $sumAchatCredit = $this->executerRequete($sql, array($email, $email));
        return $sumAchatCredit->fetch(PDO::FETCH_COLUMN);
    }

    public function getSumAbosCredit($email) {
        $sql = "SELECT SUM(PRIX) FROM ACHAT_ABONNEMENT "
                . "WHERE NO_COMMANDE IN (SELECT NO_COMMANDE FROM CREDIT_ARTICLE WHERE ID_USER = ? AND STATUT = 1) "
                . "AND STATUT = 1 AND ID_USER = ?";
        $sumAchatCredit = $this->executerRequete($sql, array($email, $email));
        return $sumAchatCredit->fetch(PDO::FETCH_COLUMN);
    }

    public function getAchatsAbonnements($email) {
        $sql = "SELECT * FROM ACHAT_ABONNEMENT WHERE STATUT = 1 AND ID_USER = ? "
                . "ORDER BY DATE_ACHAT DESC, ID_REVUE ASC";
        $achatsAbos = $this->executerRequete($sql, array($email));
        return $achatsAbos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAchats($email) {
        $sql = "SELECT * FROM ACHAT WHERE STATUT = 1 AND ID_USER = ? AND SITE = ? "
                . "ORDER BY DATE DESC, ID_NUMPUBLIE ASC, ID_ARTICLE ASC";
        $achatsAbos = $this->executerRequete($sql, array($email,
            (Configuration::get("mode")=="normal"?0:1)));
        return $achatsAbos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAchatsCredit($email) {
        $sql = "SELECT * FROM ACHAT "
                . "WHERE NO_COMMANDE IN (SELECT NO_COMMANDE FROM CREDIT_ARTICLE WHERE ID_USER = ? AND STATUT = 1) "
                . "AND STATUT = 1 AND ID_USER = ? "
                . "ORDER BY DATE DESC, ID_NUMPUBLIE ASC, ID_ARTICLE ASC";
        $achatsAbos = $this->executerRequete($sql, array($email, $email));
        return $achatsAbos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAbosCredit($email) {
        $sql = "SELECT * FROM ACHAT_ABONNEMENT "
                . "WHERE NO_COMMANDE IN (SELECT NO_COMMANDE FROM CREDIT_ARTICLE WHERE ID_USER = ? AND STATUT = 1) "
                . "AND STATUT = 1 AND ID_USER = ? "
                . "ORDER BY DATE_ACHAT DESC, ID_REVUE ASC";
        $achatsAbos = $this->executerRequete($sql, array($email, $email));
        return $achatsAbos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTokenByUser($idUser) {
        $sql = "SELECT MOT_PASSE_TMP FROM USER WHERE ID_USER = ? LIMIT 1";
        return $this->executerRequete($sql, array($idUser))->fetch();
    }

    public function getCommandeTmp($tmpCmdId) {
        $sql = "SELECT * FROM COMMANDE_TMP WHERE ID_COMMANDE = ?";
        return $this->executerRequete($sql, array($tmpCmdId))->fetch(PDO::FETCH_ASSOC);
    }

    public function getCommande($cmdId) {
        $sql = "SELECT * FROM COMMANDE_LOG WHERE NO_COMMANDE = ?";
        return $this->executerRequete($sql, array($cmdId))->fetch(PDO::FETCH_ASSOC);
    }

    public function getNowDate() {
        return strtotime("now");
    }

    /* counters */

    public function getUsersCountSessions($beginDateUSER = NOWDATEUSER, $endDate = NOWDATE) {
        $sql = "SELECT COUNT(*) FROM USER_LOG WHERE DATE_DEBUT >= ? AND DATE_FIN <= ?;";
        $result = $this->executerRequete($sql, array($beginDateUSER, $endDate));
        return $result->fetch();
    }

    public function getGuestCountSessions($beginDateGUEST = NOWDATEGUEST, $endDate = NOWDATE) {
        $sql = "SELECT COUNT(*) FROM USER_GUEST WHERE CDATE >= ? AND CDATE <= ?;";
        $result = $this->executerRequete($sql, array($beginDateGUEST, $endDate));
        return $result->fetch();
    }

    public function getInstitutionCountSessions($beginDateINST = NOWDATEINST, $endDate = NOWDATE) {
        $sql = "SELECT COUNT(*) FROM USER_LOG_IP WHERE CDATE >= ? AND DATE_FIN <= ?;";
        $result = $this->executerRequete($sql, array($beginDateINST, $endDate));
        return $result->fetch();
    }

    /* eof counters */

    public function getDataBoardUserNow($beginDateUSER = NOWDATEUSER, $beginDateIP = NOWDATEUSERIP, $beginDateINST = NOWDATEUSERINST, $endDate = NOWDATE) {
        $sql = "
        SELECT
            USER.ID_USER,
            IFNULL(" . Configuration::get('db_abo') . ".`ABONNE`.`NBRE_AB`,0) AS MAX_AB,
            IFNULL(" . Configuration::get('db_abo') . ".`ABONNE`.`NBRE_IP`,0) AS MAX_IP,
            IFNULL(LOG_COUNTS.CT,0) AS USER_AB,
            (IFNULL(LOG_COUNTS_IP.CT_IP,0) + IFNULL(LOG_COUNTS2.CT2,0)) AS USER_IP
        FROM
            USER
        LEFT JOIN
            " . Configuration::get('db_abo') . ".`ABONNE`
        ON
            USER.ID_USER  =  " . Configuration::get('db_abo') . ".`ABONNE`.`B_USER`
        LEFT JOIN
        (
            SELECT
                ID_USER,
                COUNT(*) AS CT
            FROM
                USER_LOG
            WHERE
                USER_LOG.DATE_DEBUT >= '" . $beginDateUSER . "' AND (USER_LOG.DATE_FIN <= '" . $endDate . "' OR  USER_LOG.DATE_FIN IS NULL)
            GROUP BY
                ID_USER
        )
        AS
            LOG_COUNTS
        ON
            LOG_COUNTS.ID_USER = USER.ID_USER AND USER.TYPE = 'U'
        LEFT JOIN
        (
            SELECT
                ID_USER,
                COUNT(*) AS CT2
            FROM
                USER_LOG
            WHERE
                USER_LOG.DATE_DEBUT >= '" . $beginDateINST . "' AND (USER_LOG.DATE_FIN <= '" . $endDate . "' OR  USER_LOG.DATE_FIN IS NULL)
            GROUP BY
                ID_USER
        )
        AS
            LOG_COUNTS2
        ON
            LOG_COUNTS2.ID_USER = USER.ID_USER AND USER.TYPE = 'I'
        LEFT JOIN
        (
            SELECT
                ID_USER,
                COUNT(*) AS CT_IP
            FROM
                USER_LOG_IP
            WHERE
                USER_LOG_IP.CDATE >= '" . $beginDateIP . "' AND ( USER_LOG_IP.DATE_FIN <= '" . $endDate . "' OR  USER_LOG_IP.DATE_FIN IS NULL)
            GROUP BY
                ID_USER
        )
        AS
            LOG_COUNTS_IP
        ON
            LOG_COUNTS_IP.ID_USER = " . Configuration::get('db_abo') . ".`ABONNE`.`B_USER`
        WHERE
            USER.ID_USER IN ( SELECT ID_USER FROM USER_LOG WHERE USER_LOG.DATE_DEBUT >= '" . $beginDateUSER . "' AND ( USER_LOG.DATE_FIN <= '" . $endDate . "' OR USER_LOG.DATE_FIN IS NULL ) )
        OR
            USER.ID_USER IN ( SELECT ID_USER FROM USER_LOG_IP WHERE USER_LOG_IP.CDATE >= '" . $beginDateINST . "' AND ( USER_LOG_IP.DATE_FIN <= '" . $endDate . "' OR USER_LOG_IP.DATE_FIN IS NULL) );";

        $result = $this->executerRequete($sql);
        return $result->fetchAll();
    }

    public function getDataBoardUserInterval($beginDate, $endDate = NOWDATE) {
        $sql = "
        SELECT
            USER.ID_USER,
            IFNULL(" . Configuration::get('db_abo') . ".`ABONNE`.`NBRE_AB`,0) AS MAX_AB,
            IFNULL(" . Configuration::get('db_abo') . ".`ABONNE`.`NBRE_IP`,0) AS MAX_IP,
            IFNULL(LOG_COUNTS.CT,0) AS USER_AB,
            (IFNULL(LOG_COUNTS_IP.CT_IP,0) + IFNULL(LOG_COUNTS2.CT2,0)) AS USER_IP
        FROM
            USER
        LEFT JOIN
            " . Configuration::get('db_abo') . ".`ABONNE`
        ON
            USER.ID_USER  =  " . Configuration::get('db_abo') . ".`ABONNE`.`B_USER`
        LEFT JOIN
        (
            SELECT
                ID_USER,
                COUNT(*) AS CT
            FROM
                USER_LOG
            WHERE
                USER_LOG.DATE_DEBUT >= '" . $beginDate . "' AND (USER_LOG.DATE_FIN <= '" . $endDate . "' OR  USER_LOG.DATE_FIN IS NULL)
            GROUP BY
                ID_USER
        )
        AS
            LOG_COUNTS
        ON
            LOG_COUNTS.ID_USER = USER.ID_USER AND USER.TYPE = 'U'
        LEFT JOIN
        (
            SELECT
                ID_USER,
                COUNT(*) AS CT2
            FROM
                USER_LOG
            WHERE
                USER_LOG.DATE_DEBUT >= '" . $beginDate . "' AND (USER_LOG.DATE_FIN <= '" . $endDate . "' OR  USER_LOG.DATE_FIN IS NULL)
            GROUP BY
                ID_USER
        )
        AS
            LOG_COUNTS2
        ON
            LOG_COUNTS2.ID_USER = USER.ID_USER AND USER.TYPE = 'I'
        LEFT JOIN
        (
            SELECT
                ID_USER,
                COUNT(*) AS CT_IP
            FROM
                USER_LOG_IP
            WHERE
                USER_LOG_IP.CDATE >= '" . $beginDate . "' AND ( USER_LOG_IP.DATE_FIN <= '" . $endDate . "' OR  USER_LOG_IP.DATE_FIN IS NULL)
            GROUP BY
                ID_USER
        )
        AS
            LOG_COUNTS_IP
        ON
            LOG_COUNTS_IP.ID_USER = " . Configuration::get('db_abo') . ".`ABONNE`.`B_USER`
        WHERE
            USER.ID_USER IN ( SELECT ID_USER FROM USER_LOG WHERE USER_LOG.DATE_DEBUT >= '" . $beginDate . "' AND ( USER_LOG.DATE_FIN <= '" . $endDate . "' ) )
        OR
            USER.ID_USER IN ( SELECT ID_USER FROM USER_LOG_IP WHERE USER_LOG_IP.CDATE >= '" . $beginDate . "' AND ( USER_LOG_IP.DATE_FIN <= '" . $endDate . "' ) );";

        $result = $this->executerRequete($sql);
        return $result->fetchAll();
    }

    public function getDataBoardGuest($beginDate = NOWDATEGUEST, $endDate = NOWDATE) {
        $sql = "SELECT * FROM USER_GUEST WHERE CDATE >= ? AND CDATE <= ? ORDER BY CDATE DESC;";
        $result = $this->executerRequete($sql, array($beginDate, $endDate));
        return $result->fetchAll();
    }

    public function getCountSessionByUser($idUser) {
        $sql = "SELECT COUNT(*) FROM USER_LOG WHERE ID_USER = ?;";
        $result = $this->executerRequete($sql, array($idUser));
        return $result->fetch();
    }

    public function getMaxSessionByUser($idUser) {
        $sql = "
        SELECT
            IFNULL(" . Configuration::get('db_abo') . ".`ABONNE`.`NBRE_AB`,0) AS MAX_AB
        FROM
            USER
        LEFT JOIN
            " . Configuration::get('db_abo') . ".`ABONNE`
        ON
            USER.ID_USER  =  " . Configuration::get('db_abo') . ".`ABONNE`.`B_USER`
        WHERE
            USER.ID_USER = ?;
        ";
        $result = $this->executerRequete($sql, array($idUser));
        return $result->fetch();
    }

    public function getMaxSessionIpByUser($idUser) {
        $sql = "
        SELECT
            IFNULL(" . Configuration::get('db_abo') . ".`ABONNE`.`NBRE_IP`,0) AS MAX_IP
        FROM
            USER
        LEFT JOIN
            " . Configuration::get('db_abo') . ".`ABONNE`
        ON
            USER.ID_USER  =  " . Configuration::get('db_abo') . ".`ABONNE`.`B_USER`
        WHERE
            USER.ID_USER = ?;
        ";
        $result = $this->executerRequete($sql, array($idUser));
        return $result->fetch();
    }

    public function getIdAbonnesByUser($user) {
        $sql = "SELECT ID FROM " . Configuration::get('db_abo') . ".`ABONNE` WHERE B_USER = ?";
        $result = $this->executerRequete($sql, array($user));
        return $result->fetch();
    }

    public function getNetworkAddressesByUser($idUser) {
        $sql = "SELECT ID,ID_IP,NETWORK_ADDRESS,SUBNET_MASK FROM " . Configuration::get('db_abo') . ".`IP_ABONNE` WHERE ID = (SELECT ID FROM " . Configuration::get('db_abo') . ".ABONNE WHERE B_USER = ?)";
        $result = $this->executerRequete($sql, array($idUser));
        return $result->fetchAll();
    }

    public function removeNetworkAddress($id) {
        $sql = "DELETE FROM " . Configuration::get('db_abo') . ".`IP_ABONNE` WHERE ID_IP = ?";
        $result = $this->executerRequete($sql, array($id));
    }

    public function editNetworkAddress($address, $mask, $id) {
        $sql = "UPDATE " . Configuration::get('db_abo') . ".`IP_ABONNE` SET NETWORK_ADDRESS = ?,SUBNET_MASK = ? WHERE ID_IP = ?";
        $result = $this->executerRequete($sql, array($address, $mask, $id));
    }

    public function addNetworkAddress($address, $mask, $id) {
        $sql = "INSERT INTO " . Configuration::get('db_abo') . ".`IP_ABONNE` (NETWORK_ADDRESS,SUBNET_MASK,ID) VALUES (?,?,?);";
        $result = $this->executerRequete($sql, array($address, $mask, $id));
    }

    public function getCairnParamsByUser($idUser) {
        $sql = "SELECT * FROM " . Configuration::get('db_pub') . ".`CAIRN_PARAM_INST` WHERE ID_USER = ?";
        $result = $this->executerRequete($sql, array($idUser));
        return $result->fetchAll();
    }

    public function getDiscipline() {
        $sql = "SELECT * FROM " . Configuration::get('db_pub') . ".`DISCIPLINE`;";
        $result = $this->executerRequete($sql);
        return $result->fetchAll();
    }

    public function getTypePub() {
        $sql = "SELECT * FROM " . Configuration::get('db_pub') . ".`TYPEPUB` WHERE TYPEPUB IN (1,2,3,6);";
        $result = $this->executerRequete($sql);
        return $result->fetchAll();
    }

    public function addCairnParams($param, $value, $user) {
        $sql = "INSERT INTO " . Configuration::get('db_pub') . ".`CAIRN_PARAM_INST` (TYPE,VALEUR,ID_USER) VALUES (?,?,?);";
        $result = $this->executerRequete($sql, array($param, $value, $user));
    }

    public function removeCairnParam($type, $value, $user) {
        $sql = "DELETE FROM " . Configuration::get('db_pub') . ".`CAIRN_PARAM_INST` WHERE TYPE = ? AND VALEUR = ? AND ID_USER = ?;";
        $result = $this->executerRequete($sql, array($type, $value, $user));
    }

    public function getTypeUser($idUser) {
        $sql = "SELECT TYPE FROM `USER` WHERE ID_USER = ?;";
        $result = $this->executerRequete($sql, array($idUser));
        return $result->fetch();
    }

    /*
     * Cette méthode renvoie le premier crédit trouvé qui a un solde suffisant pour payer la commande en cours
     *
     * Attention: désormais ([04-11-2014 ->) il n'y aura plus qu'un crédit en cours pour un utilisateur donné.
     * Toute autre situation résulte d'une correction/transition non effectuée par l'équipe Cairn.
     *
     * @param $idUser Identifiant de l'utilisateur
     * @param $cmdPrix Prix de la commande en cours
     *
     * @return record complet de la table CREDIT_ARTICLE
     *
     * @DEPRECATED
     */

    public function getFirstCreditDispo($idUser, $cmdPrix) {
        $sql = "SELECT * FROM CREDIT_ARTICLE
            WHERE STATUT = 1 AND EXPIRATION_CREDIT > NOW()
            AND ID_USER = ?
            AND PRIX >= (? + (
                            SELECT IF(SUM(PRIX) IS NULL,0,SUM(PRIX) )  FROM ACHAT
                            WHERE ACHAT.ID_USER = CREDIT_ARTICLE.ID_USER
                            AND ACHAT.NO_COMMANDE = CREDIT_ARTICLE.NO_COMMANDE
                            AND ACHAT.STATUT = 1
                            )
                          + (
                            SELECT IF(SUM(PRIX) IS NULL,0,SUM(PRIX) )  FROM ACHAT_ABONNEMENT
                            WHERE ACHAT_ABONNEMENT.ID_USER = CREDIT_ARTICLE.ID_USER
                            AND ACHAT_ABONNEMENT.NO_COMMANDE = CREDIT_ARTICLE.NO_COMMANDE
                            AND ACHAT_ABONNEMENT.STATUT = 1
                            )
                        )
            ORDER BY EXPIRATION_CREDIT DESC, DATE_CREDIT DESC LIMIT 1";
        return $this->executerRequete($sql, array($idUser, $cmdPrix))->fetch(PDO::FETCH_ASSOC);
    }

    /*
     * Cette méthode renvoie le premier crédit en cours et son solde
     *
     * Attention: désormais ([04-11-2014 ->) il n'y aura plus qu'un crédit en cours pour un utilisateur donné.
     * Toute autre situation résulte d'une correction/transition non effectuée par l'équipe Cairn.
     *
     * @param $idUser Identifiant de l'utilisateur
     *
     * @return record complet de la table CREDIT_ARTICLE + le solde sur base des achats
     */

    public function getCreditDispo($idUser) {
        $sql = "SELECT CREDIT_ARTICLE.*,
                        (PRIX - (
                            SELECT IF(SUM(PRIX+FRAIS_PORT) IS NULL,0,SUM(PRIX+FRAIS_PORT) )  FROM ACHAT
                            WHERE ACHAT.ID_USER = CREDIT_ARTICLE.ID_USER
                            AND ACHAT.NO_COMMANDE = CREDIT_ARTICLE.NO_COMMANDE
                            AND ACHAT.STATUT = 1
                            )
                          - (
                            SELECT IF(SUM(PRIX+PORT_ABO) IS NULL,0,SUM(PRIX+PORT_ABO) )  FROM ACHAT_ABONNEMENT
                            LEFT JOIN " . Configuration::get('db_pub') . ".FRAIS_REVUE ON " . Configuration::get('db_pub') . ".FRAIS_REVUE.ID_REVUE = ACHAT_ABONNEMENT.`ID_REVUE` AND " . Configuration::get('db_pub') . ".FRAIS_REVUE.ID_ZONE = ACHAT_ABONNEMENT.`ID_ZONE`
                            WHERE ACHAT_ABONNEMENT.ID_USER = CREDIT_ARTICLE.ID_USER
                            AND ACHAT_ABONNEMENT.NO_COMMANDE = CREDIT_ARTICLE.NO_COMMANDE
                            AND ACHAT_ABONNEMENT.STATUT = 1
                            )
                        ) AS SOLDE
            FROM CREDIT_ARTICLE
            WHERE STATUT = 1 AND EXPIRATION_CREDIT > NOW()
            AND ID_USER = ?
            ORDER BY EXPIRATION_CREDIT DESC, DATE_CREDIT DESC LIMIT 1";
        return $this->executerRequete($sql, array($idUser))->fetch(PDO::FETCH_ASSOC);
    }

    /*
     * Cette méthode renvoie la somme des soldes restants sur les crédits disponibles
     *
     * Attention: désormais ([04-11-2014 ->) cette méthode n'est plus appelée QUE lors de l'achat, pour transférer les montants en cours sur le dernier acheté.
     * Cette méthode DOIT être suivie d'un appel à une méthode de bornage/expiration afin d'éviter qu'un solde crédit soit ajouté à plusieurs achats.
     *
     * @param $idUser Identifiant de l'utilisateur
     * @param $idCredit Identifiant d'un crédit précis
     *
     * @return record complet de la table CREDIT_ARTICLE
     */

    public function getSoldeCreditDispo($idUser, $idCredit = null) {
        $sql = "SELECT SUM(PRIX - (
                            SELECT IF(SUM(PRIX) IS NULL,0,SUM(PRIX) )  FROM ACHAT
                            WHERE ACHAT.ID_USER = CREDIT_ARTICLE.ID_USER
                            AND ACHAT.NO_COMMANDE = CREDIT_ARTICLE.NO_COMMANDE
                            AND ACHAT.STATUT = 1
                            )
                          - (
                            SELECT IF(SUM(PRIX) IS NULL,0,SUM(PRIX) )  FROM ACHAT_ABONNEMENT
                            WHERE ACHAT_ABONNEMENT.ID_USER = CREDIT_ARTICLE.ID_USER
                            AND ACHAT_ABONNEMENT.NO_COMMANDE = CREDIT_ARTICLE.NO_COMMANDE
                            AND ACHAT_ABONNEMENT.STATUT = 1
                            )
                        )
            FROM CREDIT_ARTICLE
            WHERE STATUT = 1 AND EXPIRATION_CREDIT > NOW()
            AND ID_USER = ?";
        if ($idCredit != null) {
            $sql .= " AND CREDIT_ARTICLE.NO_COMMANDE = '" . $idCredit . "'";
        }
        //$sql .= "ORDER BY EXPIRATION_CREDIT DESC";
        return $this->executerRequete($sql, array($idUser))->fetch(PDO::FETCH_COLUMN);
    }

    public function searchAchatArticle($idUser, $idArticle, $idNumPublie) {
        $sql = "SELECT * FROM ACHAT WHERE ID_NUMPUBLIE = ? AND ID_USER = ? AND ID_ARTICLE = ? AND STATUT =1";
        $achat = $this->executerRequete($sql, array($idNumPublie, $idUser, $idArticle));
        return $achat->rowCount() > 0 ? true : false;
    }

    public function searchAchatNumero($idUser, $idNumPublie, $idRevue, $formatAchat = null) {
        if ($formatAchat != null) {
            $sql = "SELECT * FROM ACHAT WHERE ID_NUMPUBLIE = ? AND ID_REVUE = ? AND ID_USER = ? AND ID_ARTICLE = '' AND STATUT =1 AND TYPE = ?";
            $achat = $this->executerRequete($sql, array($idNumPublie, $idRevue, $idUser, $formatAchat));
        } else {
            $sql = "SELECT * FROM ACHAT WHERE ID_NUMPUBLIE = ? AND ID_REVUE = ? AND ID_USER = ? AND ID_ARTICLE = '' AND STATUT =1";
            $achat = $this->executerRequete($sql, array($idNumPublie, $idRevue, $idUser));
        }
        return $achat->rowCount() > 0 ? true : false;
    }

    public function getAchatsNumeros($idUser) {
        $sql = "SELECT DISTINCT ID_NUMPUBLIE FROM ACHAT WHERE ID_USER = ? AND ID_ARTICLE = '' AND STATUT =1";
        $numeros = $this->executerRequete($sql, array($idUser));
        return $numeros->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAchatsArticles($idUser) {
        $sql = "SELECT DISTINCT ID_ARTICLE FROM ACHAT WHERE ID_USER = ? AND STATUT =1";
        $articles = $this->executerRequete($sql, array($idUser));
        return $articles->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAboRevue($idUser, $idRevue) {
        $sql = "SELECT * FROM ACHAT_ABONNEMENT WHERE ID_USER = ? AND ID_REVUE = ? AND STATUT = 1";
        $abos = $this->executerRequete($sql, array($idUser, $idRevue));
        return $abos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommandesAFacturer() {
        /* $sql = "SELECT * FROM COMMANDE_LOG WHERE DATE_SENDFACT = ''
          AND NO_COMMANDE LIKE '20%'
          AND (ID_MODEPAIEMENT = 1
          OR
          (ID_MODEPAIEMENT = 2
          AND NOT EXISTS (SELECT * FROM ACHAT
          WHERE ACHAT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
          AND STATUT = 5)
          ))"; */
        $sql = "SELECT * FROM COMMANDE_LOG WHERE (DATE_SENDFACT = '' OR DATE_SENDFACT = '0000-00-00')
                AND NO_COMMANDE LIKE '20%' AND SITE = ".(Configuration::get('mode')=="normal"?0:1)."
                AND (
                    EXISTS (SELECT * FROM ACHAT
                        WHERE ACHAT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                        AND STATUT = 1)
                    OR EXISTS (SELECT * FROM ACHAT_ABONNEMENT
                        WHERE ACHAT_ABONNEMENT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                        AND STATUT = 1)
                    OR EXISTS (SELECT * FROM CREDIT_ARTICLE
                        WHERE CREDIT_ARTICLE.NO_COMMANDE = CONCAT('C',COMMANDE_LOG.NO_COMMANDE)
                        AND STATUT = 1)
                    )
                AND NOT EXISTS (SELECT * FROM ACHAT
                                WHERE ACHAT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                                AND STATUT = 5)
                AND NOT EXISTS (SELECT * FROM ACHAT_ABONNEMENT
                                WHERE ACHAT_ABONNEMENT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                                AND STATUT = 5)
                AND NOT EXISTS (SELECT * FROM CREDIT_ARTICLE
                                WHERE CREDIT_ARTICLE.NO_COMMANDE = CONCAT('C',COMMANDE_LOG.NO_COMMANDE)
                                AND STATUT = 5)
                ";
        $afacturer = $this->executerRequete($sql);
        return $afacturer->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAchatsCommande($idCommande) {
        $sql = "SELECT * FROM ACHAT WHERE NO_COMMANDE = ?";
        $achats = $this->executerRequete($sql, array($idCommande));
        return $achats->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAbosCommande($idCommande) {
        $sql = "SELECT * FROM ACHAT_ABONNEMENT WHERE NO_COMMANDE = ?";
        $abos = $this->executerRequete($sql, array($idCommande));
        return $abos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCreditsCommande($idCommande) {
        $sql = "SELECT * FROM CREDIT_ARTICLE WHERE NO_COMMANDE = ?";
        $credits = $this->executerRequete($sql, array($idCommande));
        return $credits->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommandesByUser($idUser) {
        $sql = "SELECT * FROM COMMANDE_LOG
            WHERE ID_USER = ? AND SITE = ".(Configuration::get('mode')=="normal"?0:1)."
                AND (
                    EXISTS (SELECT * FROM ACHAT
                        WHERE ACHAT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                        AND STATUT = 1)
                    OR EXISTS (SELECT * FROM ACHAT_ABONNEMENT
                        WHERE ACHAT_ABONNEMENT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                        AND STATUT = 1)
                    OR EXISTS (SELECT * FROM CREDIT_ARTICLE
                        WHERE CREDIT_ARTICLE.NO_COMMANDE = CONCAT('C',COMMANDE_LOG.NO_COMMANDE)
                        AND STATUT = 1)
                    )
                AND NOT EXISTS (SELECT * FROM ACHAT
                                WHERE ACHAT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                                AND STATUT = 5)
                AND NOT EXISTS (SELECT * FROM ACHAT_ABONNEMENT
                                WHERE ACHAT_ABONNEMENT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
                                AND STATUT = 5)
                AND NOT EXISTS (SELECT * FROM CREDIT_ARTICLE
                                WHERE CREDIT_ARTICLE.NO_COMMANDE = CONCAT('C',COMMANDE_LOG.NO_COMMANDE)
                                AND STATUT = 5)
                 ORDER BY DATE DESC";
        $cmds = $this->executerRequete($sql, array($idUser));
        return $cmds->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkSession($idUserLog, $interval, $unit) {
        $sql = "SELECT DATE_FIN FROM USER_LOG
                JOIN USER ON USER.ID_USER = USER_LOG.ID_USER
                WHERE ID_USER_LOG = ?
                AND (DATE_FIN IS NULL OR DATE_FIN > NOW())
                AND (USER.TYPE = 'I' OR TOUCH_DATE > NOW() - INTERVAL " . $interval . " " . $unit . ")";
        $session = $this->executerRequete($sql, array($idUserLog));
        return $session->fetch(PDO::FETCH_ASSOC);
    }

    public function isEjectMode($idUserLog) {
        $sql = "SELECT ALERT_EJECT FROM USER_LOG WHERE ID_USER_LOG = ?";
        $session = $this->executerRequete($sql, array($idUserLog));
        return $session->fetch(PDO::FETCH_COLUMN);
    }

    public function checkSessionIP($idUserLog, $interval, $unit) {
        $sql = "SELECT DATE_FIN FROM USER_LOG_IP
                WHERE ID_USER_IP = ?
                AND (DATE_FIN IS NULL OR DATE_FIN > NOW())
                AND TOUCH_DATE > NOW() - INTERVAL " . $interval . " " . $unit;
        $session = $this->executerRequete($sql, array($idUserLog));
        return $session->fetch(PDO::FETCH_ASSOC);
    }

    public function existShibUser($persistentId) {
        $sql = "SELECT * FROM USER_SHIB
                WHERE SHIB_TARGETEDID = ?";
        $shib = $this->executerRequete($sql, array($persistentId));
        return $shib->fetch(PDO::FETCH_ASSOC);
    }

    public function getNextShibUser() {
        $sql = "SELECT SUBSTR(ID_USER,6)+1
                FROM USER
                WHERE ID_USER LIKE 'SHI_0%'
                ORDER BY ID_USER DESC
                LIMIT 1;";
        $res = $this->executerRequete($sql);
        return $res->fetch(PDO::FETCH_COLUMN);
    }

    public function getCommandeFromInterval($fromDate, $toDate){
        $sql = "SELECT * FROM COMMANDE_TMP WHERE DATE >= ".$fromDate." AND DATE <= ".$toDate." AND MODE_PAIEMENT IS NOT NULL";
        $res = $this->executerRequete($sql);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette fonction permet de savoir
     * si un utilisateur a au moins effectué un
     * achat en PPV.
     * Dimitry, le 22/12/2015.
     */
    public function isAchatsPPV($idUser) {
        $sql = "SELECT COUNT(*) FROM ACHAT WHERE ID_USER = ? AND STATUT = '1' AND PRIX > 0 AND SITE = ".(Configuration::get('mode')=="normal"?0:1);
        return (bool) $this->executerRequete($sql, array($idUser))->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Cette fonction récupère la liste des institutions
     * Pythagoria, mai 2016.
     */
    public function getInstitutions() {
        $sql = "SELECT * FROM USER WHERE TYPE = 'I'";
        return $this->executerRequete($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getParticuliers() {
        $sql = "SELECT ID_USER, MOT_PASSE, MOT_PASSE_CR FROM USER WHERE TYPE = 'U'";
        return $this->executerRequete($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * On récupère la liste des achats prévus pour être imprimés directement par l'éditeur.
     */
    public function getAchatsWithExernalPrintOnDemand($editeurs) {
        if (count($editeurs) === 0) return;
        $sql = '
            SELECT
                ACHAT.DATE AS DATETIME,
                COMMANDE_LOG.NO_COMMANDE AS NO_COMMANDE,
                USER.PRENOM AS PRENOM,
                USER.NOM AS NOM,
                IF(TRIM(COMMANDE_LOG.FACT_ADR) != "", COMMANDE_LOG.FACT_ADR, COMMANDE_LOG.ADRESSE) AS ADRESSE,
                IF(TRIM(COMMANDE_LOG.FACT_CP) != "", COMMANDE_LOG.FACT_CP, COMMANDE_LOG.CP) AS CODE_POSTAL,
                IF(TRIM(COMMANDE_LOG.FACT_VILLE) != "", COMMANDE_LOG.FACT_VILLE, COMMANDE_LOG.VILLE) AS VILLE,
                IF(TRIM(COMMANDE_LOG.FACT_PAYS) != "", COMMANDE_LOG.FACT_PAYS, COMMANDE_LOG.PAYS) AS PAYS,
                IF(COMMANDE_LOG.OK_EDITEUR = 1, USER.EMAIL, NULL) AS EMAIL,
                REVUE.TITRE AS TITRE,
                NUMERO.VOLUME AS VOLUME,
                NUMERO.ANNEE AS ANNEE,
                NUMERO.NUMERO AS NUMERO,
                -- REVUE.ID_EDITEUR AS ID_EDITEUR,
                REVUE.ID_REVUE AS ID_REVUE,
                NUMERO.ID_NUMPUBLIE AS ID_NUMPUBLIE,
                NUMERO.EAN AS EAN,
                ACHAT.PRIX AS PRIX_TTC,
                ACHAT.ID_USER AS ID_USER
            FROM ACHAT
            LEFT JOIN COMMANDE_LOG ON ACHAT.NO_COMMANDE = COMMANDE_LOG.NO_COMMANDE
            LEFT JOIN USER ON ACHAT.ID_USER = USER.ID_USER
            LEFT JOIN cairn3_pub.REVUE ON ACHAT.ID_REVUE = REVUE.ID_REVUE
            LEFT JOIN cairn3_pub.NUMERO ON ACHAT.ID_NUMPUBLIE = NUMERO.ID_NUMPUBLIE
            WHERE
                ACHAT.EXPEDIE_LE IS NULL AND
                ACHAT.STATUT = 1 AND
                ACHAT.TYPE = "P" AND
                TRIM(ACHAT.ID_ARTICLE) = "" AND
                REVUE.TYPEPUB = 1 AND
                REVUE.ID_EDITEUR IN (' . str_repeat('?,', count($editeurs) - 1) . '?)
            ORDER BY DATETIME ASC
        ';
        return $this->executerRequete(
            $sql,
            $editeurs
        )->fetchAll(PDO::FETCH_ASSOC);
    }


    // On vérifie si un numéro a déjà été soumis à l'institution par un utilisateur donné
    public function hasAlreadySubmitSuggestionForInstitution($idInstitution, $idSession, $idUser, $idNumpublie) {
        $sql = 'SELECT EXISTS(
            SELECT 1 FROM SUGGESTION_OUVRAGE WHERE ID_INSTITUTION = ? AND ID_NUMPUBLIE = ? ';
        $params = [$idInstitution, $idNumpublie];
        // Condition permettant de prendre en compte les vieilles suggestions, même si le token a changé
        $params []= $idSession;
        if ($idUser !== null) {
            $sql .= 'AND (ID_SESSION = ? OR ID_USER = ?)';
            $params []= $idUser;
        } else {
            $sql .= 'AND ID_SESSION = ?';
        }
        $sql .= ')';
        return (bool) $this->executerRequete($sql, $params)->fetch()[0];
    }
}
