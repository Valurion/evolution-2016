<?php

require_once "Framework/Modele.php";
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ManagerStat
 *
 * @author ben
 */
class ManagerStat extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    public function insertArticle($type, $idArticle, $idNumPublie, $idRevue, $authInfos, $user_agent = null) {
        $sql = "INSERT INTO
            `STAT_LOG`
            (DATE, IP, SESSION_ID, INST, USER, TYPE, ID_REVUE, ID_NUMPUBLIE, ID_ARTICLE, `HTTP_USER_AGENT`)
            VALUES (NOW(),?,?,?,?,?,?,?,?,?)
        ";
        $this->executerRequete($sql, array(
            $authInfos['IP'],
            $authInfos['TOKEN'],
            (isset($authInfos['I']) ? $authInfos['I']['ID_USER'] : ''),
            (isset($authInfos['U']) ? $authInfos['U']['ID_USER'] : ''),
            $type,
            $idRevue,
            $idNumPublie,
            $idArticle,
            $user_agent
        ));
        return $this->getLastInsertId();
    }


    public function insertArticleCrossValidation($idLog) {
        $sql = 'INSERT INTO STAT_LOG_R (ID_STAT_LOG) VALUES (?)';
        return $this->executerRequete($sql, [$idLog]);
    }

    public function insertRecherche($searchTerm, $authInfos, $searchT = null) {
        $sql = "INSERT INTO `RECH_LOG` (DATE, IP, SESSION_ID, INST, USER, SEARCH_TERM, SEARCHT) "
                . "VALUES (NOW(),?,?,?,?,?,?)";
        return $this->executerRequete($sql, array($authInfos['IP'], $authInfos['TOKEN'],
                    (isset($authInfos['I']) ? $authInfos['I']['ID_USER'] : ''),
                    (isset($authInfos['U']) ? $authInfos['U']['ID_USER'] : ''),
                    $searchTerm,
                    ($searchT == null ? '' : json_encode($searchT))));
    }

}
