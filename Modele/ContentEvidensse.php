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
class ContentEvidensse extends Modele {

    private $docsRsTable = null;

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
        $this->docsRsTable = Configuration::get('docsRsTable');
    }

    public function getLastCrawlingDate($idxId) {
        $sql = "SELECT lastused FROM indexes WHERE auto_id = ?";
        $lastUsed = $this->executerRequete($sql, array($idxId));
        return $lastUsed->fetch(PDO::FETCH_COLUMN);
    }

    public function getArticleFromDocsRS($idArticle, $idxId) {
        $sql = "SELECT * FROM " . $this->docsRsTable . " WHERE docfilename = ? AND UPDFLD NOT IN (2,20)";
        $docsRS = $this->executerRequete($sql, array($idArticle));
        return $docsRS->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getArticles500FromDocsRS() {
        $sql = "SELECT * FROM " . $this->docsRsTable . " WHERE idxid = 500 AND UPDFLD NOT IN (2,20)";
        $docsRS = $this->executerRequete($sql);
        return $docsRS->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDocsId($docfilenames) {
        echo '<br/>getDocsId:' . microtime();
        if (count($docfilenames) > 1000) {
            $chunks = array_chunk($docfilenames, 1000);
            $sql = "SELECT id FROM " . $this->docsRsTable . " WHERE ";
            $cnt = 0;
            foreach ($chunks as $chunk) {
                $cnt++;
                if ($cnt == 1) {
                    $sql .= "docfilename in ('" . implode("','", $chunk) . "')";
                } else {
                    $sql .= " or docfilename in ('" . implode("','", $chunk) . "')";
                }
            }
        } else {
            $sql = "SELECT id FROM " . $this->docsRsTable . " WHERE docfilename in ('" . implode("','", $docfilenames) . "')";
        }
        echo $sql;
        $docsRS = $this->executerRequete($sql);
        echo '==>' . microtime();
        return $docsRS->fetchAll(PDO::FETCH_COLUMN);
    }

}
