<?php

require_once "Framework/Modele.php";

/**
 * Description of ManagerEvidensse
 *
 * @author ben
 */
class ManagerEvidensse extends Modele {

    private $docsRsTable = null;

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
        $this->docsRsTable = Configuration::get('docsRsTable');
    }

    public function setLastCrawlingDate($idxId) {
        $sql = "UPDATE indexes SET lastused = NOW() WHERE auto_id = ?";
        return $this->executerRequete($sql, array($idxId));
    }

    public function updateUpdfldForArticlesIfNecessary($updfld, $listArticlesIds) {
        $sql = "UPDATE " . $this->docsRsTable . " SET UPDFLD = ? "
                . "WHERE docfilename IN (" . $listArticlesIds . ") "
                . "AND UPDFLD NOT IN (?,?)";
        return $this->executerRequete($sql, array($updfld, $updfld, ($updfld * 10)));
    }

    public function updateUpdfldForArticle($updfld, $idArticle, $fields2, $idxid) {
        $sql = "UPDATE " . $this->docsRsTable . " SET UPDFLD = ?, fields2 = ?, idxid = ? WHERE docfilename = ?";
        return $this->executerRequete($sql, array($updfld, $fields2, $idxid, $idArticle));
    }

    public function insertDocRS($idxId, $docname, $article, $fields2, $modeInter = 0 ) {
        $sql = "INSERT INTO " . $this->docsRsTable . " (docname,idxid,fields2,dtcreated,dtmodified,docfilename,UPDFLD) "
                . "VALUES (?,?,?,?,?,?,1)";
        /* PYTHAGORIA le 11 avril 2017 : pour les 2 sites, DATE_MISEENLIGNE fait foi, avec priorité sur celle de l'article, si elle est définie...
         * if($modeInter == 1){
            return $this->executerRequete($sql, array($docname, $idxId, $fields2, $article['DATESTATUT'], $article['DATESTATUT'], $article['ID_ARTICLE']));
        }else{
            return $this->executerRequete($sql, array($docname, $idxId, $fields2, $article['NUMERO_DATE_MISEENLIGNE'], $article['NUMERO_DATE_MISEENLIGNE'], $article['ID_ARTICLE']));
        }*/
        $dateEligible = "0000-00-00";
        if(isset($article["DATE_MISEENLIGNE"]) && $article["DATE_MISEENLIGNE"] != "" && $article["DATE_MISEENLIGNE"] != "0000-00-00"){
            $dateEligible = $article["DATE_MISEENLIGNE"];
        }else{
            $dateEligible = $article["NUMERO_DATE_MISEENLIGNE"];
        }
        return $this->executerRequete($sql, array($docname, $idxId, $fields2, $dateEligible, $dateEligible, $article['ID_ARTICLE']));
    }

    public function prepareDocsRS() {
        $sql = "DELETE FROM " . $this->docsRsTable . " WHERE UPDFLD = 2";
        $this->executerRequete($sql);
        $sql = "UPDATE " . $this->docsRsTable . " SET UPDFLD = (UPDFLD*10) WHERE UPDFLD < 10";
        $this->executerRequete($sql);
    }
    
    /*public function addEvidensseInfos($file,$work){
        $sql = "TRUNCATE TABLE `tmp-".$work."`";
        $this->executerRequete($sql);
        $sql = "LOAD DATA LOCAL INFILE '".$file."' INTO TABLE `tmp-".$work."` FIELDS ESCAPED BY '\\\\' TERMINATED BY '\\t' LINES TERMINATED BY '\\n' (`docid`, `id`, `proche`)";
        $this->executerRequete($sql);
        $sql = "UPDATE " . $this->docsRsTable . " INNER JOIN `tmp-".$work."`
            ON (" . $this->docsRsTable . ".`docfilename` = `tmp-".$work."`.`id`)
            SET " . $this->docsRsTable . ".`id` = `tmp-".$work."`.`docid` , " . $this->docsRsTable . ".`proche`=`tmp-".$work."`.`proche`";
        $this->executerRequete($sql);
    }*/
    
    public function updateDocsRsInfos($docId, $docFilename, $proche){
        $sql = "UPDATE " . $this->docsRsTable . " SET oldId = id, id = ?, proche = ? WHERE docfilename = ?";
        $this->executerRequete($sql,array($docId,$proche,$docFilename));
    }
    
    public function switchToIdx($docname,$idxid){
        $sql = "UPDATE " . $this->docsRsTable . " SET idxid = ?, UPDFLD = 1 WHERE docname = ?";
        $this->executerRequete($sql,array($idxid,$docname));
    }

}
