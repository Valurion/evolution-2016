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
class ContentStat extends Modele {

    function __construct($dsn_name) {
        $this->selectDatabase($dsn_name);
    }

    public function checkSortieZen($authInfos, $idArticle) {
        //deprecié
        return 0;
    }

}
