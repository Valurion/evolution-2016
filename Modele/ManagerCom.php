<?php

require_once 'Framework/Modele.php';

/**
 * Provide update services to Cairn Commercial DB
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com
 * @author Benjamin Hennon
 * @todo : documentation
 */
class ManagerCom extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    public function insertIpReceived($ip,$requete){
        $sql = "INSERT INTO USER_IP_RECEIVED (USER_IP, USER_REQUEST) values (?,?)";
        return $this->executerRequete($sql, array($ip,$requete));
    }

    public function createAccount($infos) {
        $sql = "INSERT INTO `USER` (ID_USER, NOM, PRENOM, EMAIL, PROFESSION, MOT_PASSE_CR, TYPE, POS_DISCU, PAYS, CDATE, SHOWALL) VALUES (?,?,?,?,?,?,?,?,?,NOW(),0)";
        return $this->executerRequete($sql, $infos);
    }

    public function updateAccount($infos) {
        $sql = "UPDATE `USER` SET NOM = ?, PRENOM = ?, POS_DISCU = ?, PROFESSION = ?, PAYS = ?, SHOWALL = ?, FUPDATE = 1
                WHERE ID_USER = ?";
        return $this->executerRequete($sql, $infos);
    }

    public function deleteAccount($idUser, $newIdUser) {

        // UPDATE EN CASCADE
        // Achats
        $sqlAchats = "UPDATE ACHAT set ID_USER = ? where ID_USER = ?";
        $this->executerRequete($sqlAchats, array($newIdUser, $idUser));
        // Achats Abonnements
        $sqlAbo = "UPDATE ACHAT_ABONNEMENT set ID_USER = ? where ID_USER = ?";
        $this->executerRequete($sqlAbo, array($newIdUser, $idUser));
        // Crédit Article
        $sqlCA = "UPDATE CREDIT_ARTICLE set ID_USER = ? where ID_USER = ?";
        $this->executerRequete($sqlCA, array($newIdUser, $idUser));
        // User
        $sqlUser = "UPDATE USER set ID_USER = ?, NOM = '', PRENOM = '', ADRESSE = '', EMAIL = '', CP = '', VILLE = '', PAYS = '' where ID_USER = ?";
        $this->executerRequete($sqlUser, array($newIdUser, $idUser));

        // INSERT
        $sqlUserDeleted = "INSERT INTO USER_DELETED VALUES (?, ?, UNIX_TIMESTAMP())";
        $this->executerRequete($sqlUserDeleted, array($newIdUser, $idUser));
    }

    public function insertUserLog($idUserLog, $userId, $interval = null, $unit = null) {
        if ($interval != null && $unit != null) {
            $sql = "INSERT INTO `USER_LOG` (ID_USER_LOG, ID_USER, DATE_DEBUT, DATE_FIN, TOUCH_DATE) VALUES (?,?, NOW(),NOW() + INTERVAL " . $interval . " " . $unit . ", NOW())";
        } else {
            $sql = "INSERT INTO `USER_LOG` (ID_USER_LOG, ID_USER, DATE_DEBUT, TOUCH_DATE) VALUES (?,?, NOW(), NOW())";
        }
        return $this->executerRequete($sql, array($idUserLog, $userId));
    }

    public function closeUserLog($userIdLog) {
        $sql = "UPDATE USER_LOG SET DATE_FIN = NOW() WHERE ID_USER_LOG = ? AND (DATE_FIN IS NULL OR DATE_FIN > NOW())";
        return $this->executerRequete($sql, array($userIdLog));
    }

    public function closeOtherUserLog($userId) {
        $sql = "UPDATE USER_LOG SET TOUCH_DATE = NOW(), DATE_FIN = NOW(), ALERT_EJECT = 1 WHERE ID_USER = ? AND (DATE_FIN IS NULL OR DATE_FIN > NOW())";
        return $this->executerRequete($sql, array($userId));
    }

    public function touchLog($userIdLog) {
        $sql = "UPDATE USER_LOG SET TOUCH_DATE = NOW() WHERE ID_USER_LOG = ? AND (DATE_FIN IS NULL OR DATE_FIN > NOW())";
        return $this->executerRequete($sql, array($userIdLog));
    }

    public function insertUserGuest($userId) {
        $sql = "INSERT INTO `USER_GUEST` (ID_USER, CDATE) VALUES (?, NOW())";
        return $this->executerRequete($sql, array($userId));
    }

    public function insertUserIP($userIpId, $userIP, $userId, $interval = null, $unit = null) {
        if ($interval != null && $unit != null) {
            $sql = "INSERT INTO `USER_LOG_IP` (ID_USER_IP, ID_USER, IP_USER, CDATE, DATE_FIN, TOUCH_DATE)
                    VALUES (?, ?, ?, NOW(),NOW() + INTERVAL " . $interval . " " . $unit . ", NOW())";
        } else {
            $sql = "INSERT INTO `USER_LOG_IP` (ID_USER_IP, ID_USER, IP_USER, CDATE, TOUCH_DATE) VALUES (?, ?, ?, NOW(),NOW())";
        }
        return $this->executerRequete($sql, array($userIpId, $userId, $userIP));
    }

    public function touchLogIp($userIpId) {
        $sql = "UPDATE USER_LOG_IP SET TOUCH_DATE = NOW() WHERE ID_USER_IP = ? AND DATE_FIN > NOW()";
        return $this->executerRequete($sql, array($userIpId));
    }

    public function closeUserLogIp($userLogIp) {
        $sql = "UPDATE USER_LOG_IP SET DATE_FIN = NOW() WHERE ID_USER_IP = ? AND DATE_FIN > NOW()";
        return $this->executerRequete($sql, array($userLogIp));
    }

    public function updateHistoJson($userId, $histoJson) {
        $sql = "UPDATE `USER` SET HISTO_JSON = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($histoJson, $userId));
    }

    public function updateHistoJsonInt($userId, $histoJson) {
        $sql = "UPDATE `USER` SET HISTO_JSON_INT = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($histoJson, $userId));
    }

    public function updateGuestHistoJson($userId, $histoJson) {
        $sql = "UPDATE `USER_GUEST` SET HISTO_JSON = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($histoJson, $userId));
    }

    public function updateUserAchats($oldemail, $email) {
        $sql = "UPDATE `ACHAT` SET ID_USER = ?, FUPDATE = 1 WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($email, $oldemail));
    }

    public function updateUserAchatsAbo($oldemail, $email) {
        $sql = "UPDATE `ACHAT_ABONNEMENT` SET ID_USER = ?, FUPDATE = 1 WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($email, $oldemail));
    }

    public function updateUserCreditArticle($oldemail, $email) {
        $sql = "UPDATE `CREDIT_ARTICLE` SET ID_USER = ?, FUPDATE = 1 WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($email, $oldemail));
    }

    public function updateUserLogs($oldemail, $email) {
        $sql = "UPDATE `USER_LOG` SET ID_USER = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($email, $oldemail));
    }

    public function updateUserEmail($oldemail, $email, $mdp) {
        $mdp = Service::get("CairnToken")->cryptMp($mdp);
        $sql = "UPDATE `USER` SET ID_USER = ?, EMAIL = ?, MOT_PASSE_CR = ?, FUPDATE = 1 WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($email, $email, $mdp, $oldemail));
    }

    public function updateUserPassword($mdp, $email) {
        $mdp = Service::get("CairnToken")->cryptMp($mdp);
        $sql = "UPDATE `USER` SET MOT_PASSE_CR = ? , MOT_PASSE_TMP = '', FUPDATE = 1 WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($mdp, $email));
    }

    public function addUserTmpPassword($token, $email) {
        $sql = "UPDATE `USER` SET MOT_PASSE_TMP = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($token, $email));
    }

    public function createCommandeTmp($id, $idUser, $panier, $prix, $session) {
        $sql = "INSERT INTO COMMANDE_TMP (ID_COMMANDE, ID_USER, DATE, ACHATS, PRIX, SITE, SESSION)
                VALUES (?,?,NOW(),?,?,?,?)";
        return $this->executerRequete($sql, array($id, $idUser, $panier, $prix,
            (Configuration::get("mode")=="normal"?0:1),$session));
    }

    public function updateCommandeTmpAchats($tmpCmdId, $panier, $totalPrice) {
        $sql = "UPDATE COMMANDE_TMP
                SET ACHATS = ?,
                    PRIX = ?
                WHERE ID_COMMANDE = ?";
        return $this->executerRequete($sql, array($panier, $totalPrice, $tmpCmdId));
    }

    public function updateCommandeTmp($tmpCmdId, $factNom, $factAdr, $factCp, $factVille, $factPays, $prenom, $nom, $adr, $cp, $ville, $pays, $fp, $okEditeur=null) {
        $sql = "UPDATE COMMANDE_TMP
                SET FACT_NOM = ?,
                    FACT_ADR = ?,
                    FACT_CP = ?,
                    FACT_VILLE = ?,
                    FACT_PAYS = ?,
                    PRENOM = ?,
                    NOM = ?,
                    ADRESSE = ?,
                    CP = ?,
                    VILLE = ?,
                    PAYS = ?,
                    FRAIS_PORT = ?,
                    OK_EDITEUR = ?
                WHERE ID_COMMANDE = ?";
        return $this->executerRequete($sql, array($factNom, $factAdr, $factCp, $factVille, $factPays,
                    $prenom, $nom, $adr, $cp, $ville, $pays, $fp, $okEditeur, $tmpCmdId));
    }

    public function updateUserFactInfos($idUser, $factNom, $factAdr, $factCp, $factVille, $factPays) {
        $sql = "UPDATE USER
                SET FACT_NOM = ?,
                    FACT_ADR = ?,
                    FACT_CP = ?,
                    FACT_VILLE = ?,
                    FACT_PAYS = ?,
                    FUPDATE = 1
                WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($factNom, $factAdr, $factCp, $factVille, $factPays, $idUser));
    }

    public function updateUserLivrInfos($idUser, $prenom, $nom, $adr, $cp, $ville, $pays) {
        $sql = "UPDATE USER
                SET PRENOM = ?,
                    NOM = ?,
                    ADRESSE = ?,
                    CP = ?,
                    VILLE = ?,
                    PAYS = ?,
                    FUPDATE = 1
                WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($prenom, $nom, $adr, $cp, $ville, $pays, $idUser));
    }

    public function validateCommandeTmp($idCommande, $idCredit, $typePaiement, $tmpCmdId) {
        $sql = "UPDATE COMMANDE_TMP SET COMMANDE_NO_COMMANDE = ?, CREDIT_NO_COMMANDE = ?, MODE_PAIEMENT = ? WHERE ID_COMMANDE = ?";
        $this->executerRequete($sql, array($idCommande, $idCredit, $typePaiement, $tmpCmdId));
    }

    public function insertCommandeLog($idCommande, $typePaiement, $idCommandeTmp, $payId = '', $status = '') {
        $sql = "INSERT INTO COMMANDE_LOG (NO_COMMANDE, FUPDATE, SENDMAIL, ID_MODEPAIEMENT, PAYID, STATUT_OGONE,
                    DATE, ID_USER, FACT_NOM, FACT_ADR, FACT_CP, FACT_VILLE, FACT_PAYS,
                    NOM, PRENOM, ADRESSE, CP, VILLE, PAYS, SITE)
                SELECT ?,1,1,?,?,?, DATE, ID_USER, FACT_NOM, FACT_ADR, FACT_CP, FACT_VILLE, FACT_PAYS,
                        NOM, PRENOM, ADRESSE, CP, VILLE, PAYS, SITE
                    FROM COMMANDE_TMP WHERE ID_COMMANDE = ?";
        $this->executerRequete($sql, array($idCommande, $typePaiement, $payId, $status, $idCommandeTmp));
    }

    /**
     * Cette méthode va permettre l'insertion
     * dans la table achat_abonnement.
     * Révisée le : 25/01/2016, par Dimitry (Cairn).
     */
    public function insertAchatAbo($cmdId, $abo, $idZone, $idUser, $statut = 1) {
        $sql = "INSERT INTO ACHAT_ABONNEMENT (ID_REVUE, ID_ABON, ID_ABON_REF, ID_USER, DATE_ACHAT,
                    ID_NUMPUBLIE, ANNEE_DEBUT, ID_ZONE, NEXTANNEE, NO_COMMANDE, STATUT, PRIX, FUPDATE)
                SELECT ?,?,MAX(ID_ABON_REF)+1,?,NOW(),?,?,?,?,?,?,?,1 FROM ACHAT_ABONNEMENT WHERE ID_REVUE = ? AND ID_ABON = ?";
        $infoSup = $abo['INFOSUP'];
        if (strlen($infoSup) == 4 && $infoSup > 2000 && $infoSup < 2050) {
            $anneeDebut = $infoSup;
            $idNumPublie = '';
        } else {
            $anneeDebut = '';
            $idNumPublie = $infoSup;
        }
        $this->executerRequete($sql, array($abo['ID_REVUE'], $abo['ID_ABON'], $idUser,
            $idNumPublie, $anneeDebut, ($idZone === false ? 0 : $idZone), $abo['NEXTANNEE'], $cmdId, $statut, $abo['PRIX'],
            $abo['ID_REVUE'], $abo['ID_ABON']));
    }

    /**
     * Cette méthode va permettre l'insertion
     * dans la table achat.
     * Révisée le : 25/01/2016, par Dimitry (Cairn).
     */
    public function insertAchatNum($cmdId, $num, $fp, $idUser, $elec = 0, $statut = 1, $site = 0) {
        $sql = "INSERT INTO ACHAT (ID_REVUE, ID_NUMPUBLIE, ID_ARTICLE, ID_USER, DATE,
                    FRAIS_PORT, NO_COMMANDE, STATUT, PRIX, TYPE,FUPDATE, SITE)
                VALUES (?,?,'',?,NOW(),?,?,?,?,?,1, ?)";

        $this->executerRequete($sql, array($num['NUMERO_ID_REVUE'], $num['NUMERO_ID_NUMPUBLIE'],
            $idUser, $fp, $cmdId, $statut,
            ($elec == 0 ? $num['NUMERO_PRIX'] : $num['NUMERO_PRIX_ELEC']),
            ($elec == 0 ? 'P' : 'E'),
            $site
        ));
    }

    /**
     * Cette méthode va permettre la mise à jour
     * d'une ligne d'achat.
     * Dimitry (Cairn), le 21/01/2016.
     */
    public function updateAchatNum($idCommande) {
        $sql = "UPDATE ACHAT SET STATUT = 1, FUPDATE = 1 WHERE NO_COMMANDE = ?";
        $this->executerRequete($sql, array($idCommande));
    }

    /**
     * Cette méthode va permettre la mise à jour
     * d'une ligne d'achat d'abonnement.
     * Dimitry (Cairn), le 21/01/2016.
     */
    public function updateAchatAbo($idCommande) {
        $sql = "UPDATE ACHAT_ABONNEMENT SET STATUT = 1, FUPDATE = 1 WHERE NO_COMMANDE = ?";
        $this->executerRequete($sql, array($idCommande));
    }

    /**
     * Cette méthode va mettre à jour la table achat
     * au niveau de l'achat d'un article.
     * Dimitry (Cairn), le 25/01/2016.
     */
    public function updateAchatArt($idCommande) {
        $sql = "UPDATE ACHAT SET STATUT = 1, FUPDATE = 1 WHERE NO_COMMANDE = ?";
        $this->executerRequete($sql, array($idCommande));
    }

    /**
     * Cette fonction va permettre de créer une
     * nouvelle ligne d'achat, pour un article acheté.
     */
    public function insertAchatArt($cmdId, $art, $fp, $idUser, $statut = 1, $site = 0) {
        $sql = "INSERT INTO ACHAT (ID_REVUE, ID_NUMPUBLIE, ID_ARTICLE, ID_USER, DATE,
                    FRAIS_PORT, NO_COMMANDE, STATUT, PRIX, TYPE, FUPDATE, SITE)
                VALUES (?,?,?,?,NOW(),?,?,?,?,'E',1,?)";

        $this->executerRequete($sql, array($art['ARTICLE_ID_REVUE'], $art['ARTICLE_ID_NUMPUBLIE'],
            $art['ARTICLE_ID_ARTICLE'], $idUser, $fp, $cmdId, $statut, $art['ARTICLE_PRIX'],
            $site));
    }

    public function termineCreditEnCours($idUser) {
        $sql = "UPDATE CREDIT_ARTICLE SET EXPIRATION_CREDIT = NOW()-INTERVAL 1 DAY, FUPDATE = 1
                WHERE ID_USER = ?
                AND EXPIRATION_CREDIT > NOW()
                AND STATUT = 1";
        $this->executerRequete($sql, array($idUser));
    }

    public function insertAchatCredit($cmdId, $montant, $montantCmd, $idUser, $statut) {
        $sql = "INSERT INTO CREDIT_ARTICLE (ID_USER, NO_COMMANDE, NO_FACTURE, PRIX, PRIX_CMD, DATE_CREDIT, EXPIRATION_CREDIT, STATUT, FUPDATE)"
                . "VALUES (?,?,'',?,?,NOW(),?,?,1)";

        $expiration = (intval(date('Y')) + 1) . '-12-31';
        $this->executerRequete($sql, array($idUser, $cmdId, $montant, $montantCmd, $expiration, $statut));
    }

    public function setNoFactureAchats($idCommande) {
        $sql = "UPDATE ACHAT SET NO_FACTURE = ?, FUPDATE = 1 WHERE NO_COMMANDE = ?";
        $this->executerRequete($sql, array(('F' . $idCommande), $idCommande));
    }

    public function setNoFactureAbos($idCommande) {
        $sql = "UPDATE ACHAT_ABONNEMENT SET NO_FACTURE = ?, FUPDATE = 1 WHERE NO_COMMANDE = ?";
        $this->executerRequete($sql, array(('F' . $idCommande), $idCommande));
    }

    public function setNoFactureCredits($idCommande) {
        $sql = "UPDATE CREDIT_ARTICLE SET NO_FACTURE = ?, FUPDATE = 1 WHERE NO_COMMANDE = ?";
        $this->executerRequete($sql, array(('F' . $idCommande), $idCommande));
    }

    public function setDateSendFact($idCommande) {
        $sql = "UPDATE COMMANDE_LOG SET DATE_SENDFACT = NOW(), FUPDATE = 1 WHERE NO_COMMANDE = ?";
        $this->executerRequete($sql, array($idCommande));
    }

    public function createUserShib($idP, $instId, $userId) {
        $sql = "INSERT INTO USER_SHIB (SHIB_TARGETEDID, ID_SSO, ID_USER) VALUES (?,?,?)";
        $this->executerRequete($sql, array($idP, $instId, $userId));
    }

    public function updateUserAbo($idEditeur, $codeAbo, $idUser){
        $sql = "UPDATE ACHAT_ABONNEMENT SET ID_USER = ?, STATUT = 1, PRIX = 0, DATE_ACHAT = NOW() WHERE ID_USER = CONCAT('###',?,'###',?,'###')";
        $this->executerRequete($sql, array($idUser, $idEditeur, $codeAbo));
    }

    public function saveCryptedInstMP($idUser,$mpCrypted) {
        $sql = "UPDATE `USER` SET PWD_LOG_CR = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($mpCrypted,$idUser));
    }

    public function saveCryptedPartMP($idUser,$mpCrypted) {
        $sql = "UPDATE `USER` SET MOT_PASSE_CR = ? WHERE ID_USER = ?";
        return $this->executerRequete($sql, array($mpCrypted,$idUser));
    }



    public function updateDateExpeditionAchatPapier($idRevue, $idNumpublie, $idArticle, $idUser, $noCommande, $date, $expedieLe) {
        $sql = 'UPDATE ACHAT SET FUPDATE = 1, EXPEDIE_LE = ? WHERE
            ID_REVUE = ? AND
            ID_NUMPUBLIE = ? AND
            ID_ARTICLE = ? AND
            ID_USER = ? AND
            NO_COMMANDE = ? AND
            DATE = ?
        ';
        $this->executerRequete($sql, [$expedieLe, $idRevue, $idNumpublie, $idArticle, $idUser, $noCommande, $date]);
    }


    // Quelqu'un a fait une suggestion d'achat d'ouvrage pour une institution
    public function insertSuggestionOuvragePourInstitution($idNumpublie, $idInstitution, $idSession, $idUser) {
        $sql = 'INSERT INTO SUGGESTION_OUVRAGE
            (ID_NUMPUBLIE, ID_INSTITUTION, ID_SESSION, ID_USER, SUGGESTION_LE)
            VALUES (?, ?, ?, ?, NOW())';
        $this->executerRequete($sql, [$idNumpublie, $idInstitution, $idSession, $idUser]);
    }


    // On met à jour l'id_user si un utilisateur se connecte APRÈS avoir suggérer un ouvrage
    public function updateSuggestionOuvragePourInstitutionApresLogin($idSession, $idUser) {
        $sql = 'UPDATE SUGGESTION_OUVRAGE SET ID_USER = :idUser WHERE ID_SESSION = :idSession AND ID_USER IS NULL';
        $this->executerRequete($sql, ['idSession' => $idSession, 'idUser' => $idUser]);
    }
}
