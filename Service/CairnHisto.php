<?php

require_once 'CairnToken.php';
require_once 'Modele/ManagerCom.php';
require_once 'Modele/ManagerComMongo.php';
require_once 'Modele/ContentComMongo.php';
require_once 'Modele/Content.php';
require_once 'Framework/Modele.php';
require_once 'Framework/Configuration.php';

/**
 * Ce service prend en charge les opérations liées à l'historique Cairn
 *  - décomposition des valeurs et remplissage dans authInfos
 *  - mise à jour, recomposition et stockage des valeurs si changements via l'interface
 *  - conversion de l'ancien mode en JSON
 *  - à terme, on envisage de transférer l'histo Guest vers le User lors de la connexion
 *
 * @author benjamin
 */
class CairnHisto {

    private $managerCom;
    private $managerComMongo;
    private $content;
    private $contentComMongo;
    
    private $currentSite;

    function __construct() {
        $this->managerCom = new ManagerCom('dsn_com');
        $this->managerComMongo = new ManagerComMongo(Configuration::get('dsn_com_mongo'));
        $this->content = new Content();
        $this->contentComMongo = new ContentComMongo(Configuration::get('dsn_com_mongo'));
        
        $this->currentSite = Configuration::get("mode");
    }

    /*
     * Cette fonction effectue la lecture de l'historique json, sur base de ce qui est dans les cookies.
     * Comme toujours, la priorité est donnée à la connexion U par rapport au G
     * Si il se trouve que l'historique est toujours sous l'ancien format, on en profite pour le convertir.
     * 
     * UPDATE - SEPTEMBRE 2016
     * Suite à la fusion des bases de données COM de Cairn et CairnInt:
     * - on dispose de deux champs en DB : HISTO_JSON et HISTO_JSON_INT
     * - pour éviter de modifier l'ensemble de l'application avec des "if(<cairnInt>)...else..." encombrants,
     *   on fait la sélection du champ opportun à la lecture de l'historique. Dès lors, le reste de l'application
     *   peut continuer à se "servir" dans le champ $authInfos["U"]["HISTO_JSON"] indifférement du site sur lequel on se troue.
     * 
     */

    public function readHisto(&$authInfos) {
        if (isset($authInfos['U'])) {
            if($this->currentSite == 'normal'){
                if ($authInfos['U']['HISTO_JSON'] != NULL) {
                    $array_from_json = json_decode($authInfos["U"]["HISTO_JSON"]);
                } else {
                    $histo_json = $this->convertHistorique($authInfos["U"]["HISTORIQUE"]);
                    //On stocke la conversion JSON en DB, de manière à ne plus devoir la faire à l'avenir...
                    //TODO : Envisager de vider l'ancien champ, quand on sera sur de notre coup...
                    $this->managerCom->updateHistoJson($authInfos['U']['ID_USER'], $histo_json);
                    $array_from_json = json_decode($histo_json);
                }
            }else{
                if ($authInfos['U']['HISTO_JSON_INT'] != NULL) {
                    $array_from_json = json_decode($authInfos["U"]["HISTO_JSON_INT"]);
                }else{
                    $array_from_json = new stdClass();                    
                }
            }
            // Dans tous les cas, on garnit "HISTO_JSON", peu importe le site sur lequel on se trouve
            $authInfos["U"]["HISTO_JSON"] = $array_from_json;
        } else {
            if($this->currentSite == 'normal'){
                if ($authInfos["G"]["HISTO_JSON"] != NULL) {
                    $array_from_json = json_decode($authInfos["G"]["HISTO_JSON"]);
                }
            }else{
                if ($authInfos["G"]["HISTO_JSON_INT"] != NULL) {
                    $array_from_json = json_decode($authInfos["G"]["HISTO_JSON_INT"]);
                }
            }
            $authInfos["G"]["HISTO_JSON"] = $array_from_json;
        }
        $this->loadArticlesDisplayDatas($authInfos);
    }

    private function convertHistorique($string) {
        $return = null;
        if ($string == '') {
            return "";
        } else {
            $matches = array();
            preg_match_all('/\[\*\#ARTICLE\$@\](?P<articles>.*)\[\/\*\#ARTICLE\$\@\]'
                    . '\[\*\#RECHERCHE\$@\](?P<recherches>.*)\[\/\*\#RECHERCHE\$\@\]'
                    . '\[\*\#BIBLIO\$@\](?P<biblio>.*)\[\/\*\#BIBLIO\$\@\]'
                    . '\[\*\#PANIER\$@\](?P<panier>.*)\[\/\*\#PANIER\$\@\]'
                    . '\[\*\#PANIER_INST\$@\](?P<panierInst>.*)\[\/\*\#PANIER_INST\$\@\]'
                    . '/', $string, $matches);

            //On traite spécifiquement les recherches, pour séparer les infos "terme" et "date"
            $recherches = array();
            if ($matches['recherches'][0] != '') {
                foreach (explode(',', $matches['recherches'][0]) as $rech) {
                    $newrech = explode('/*-*/', $rech);
                    $recherches[] = $newrech;
                }
            }
            $articles = "";
            if ($matches['articles'][0] != '') {
                $articles = explode(',', $matches['articles'][0]);
            }
            $biblio = "";
            if ($matches['biblio'][0] != '') {
                $biblio = explode(',', $matches['biblio'][0]);
            }
            $panier = "";
            if ($matches['panier'][0] != '') {
                $panier = explode(',', $matches['panier'][0]);
            }
            $panierInst = "";
            if ($matches['panierInst'][0] != '') {
                $panierInst = explode(',', $matches['panierInst'][0]);
            }

            $return = array(
                'articles' => $articles,
                'recherches' => $recherches,
                'biblio' => $biblio,
                'panier' => $panier,
                'panierInst' => $panierInst
            );
            return json_encode($return);
        }
    }

    public function addToHisto($typeInfo, $info, &$authInfos) {
        //On récupère l'info dans le bon user (U ou G)
        $typeUser = 'G';
        if (isset($authInfos['U'])) {
            $typeUser = 'U';
        }
        //On regarde si on a déjà quelque chose pour cette information
        $arrayToAdd = array();
        if (isset($authInfos[$typeUser]['HISTO_JSON']->$typeInfo) && is_array($authInfos[$typeUser]['HISTO_JSON']->$typeInfo)) {
            $arrayToAdd = $authInfos[$typeUser]['HISTO_JSON']->$typeInfo;
        }
        $infoToAdd = $this->preAddToHisto($typeInfo, $info, $arrayToAdd);
        if ($infoToAdd != "") {
            //On ajoute, en limitant à 20...
            array_unshift($arrayToAdd, $infoToAdd);
            if ($typeInfo == 'searchMode' || $typeInfo == 'searchModeInfo') {
                array_splice($arrayToAdd, 1);
            } else {
                array_splice($arrayToAdd, Configuration::get('limit_article'));
            }
            $authInfos[$typeUser]['HISTO_JSON']->$typeInfo = $arrayToAdd;
            //On sauvegarde dans la DB
            if ($typeUser == 'U') {
                if($this->currentSite == "normal"){
                    $this->managerCom->updateHistoJson($authInfos[$typeUser]['ID_USER'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }else{
                    $this->managerCom->updateHistoJsonInt($authInfos[$typeUser]['ID_USER'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }
            } else {
                if($this->currentSite == "normal"){
                    $this->managerComMongo->updateGuestHistoJson($authInfos['G']['TOKEN'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }else{
                    $this->managerComMongo->updateGuestHistoJsonInt($authInfos['G']['TOKEN'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }                
            }
            if ($typeInfo == 'articles') {
                //$this->loadArticlesDisplayDatas($authInfos);
                array_unshift($authInfos[$typeUser]['HISTO_JSON_ARTICLES'], array($info["ARTICLE_ID_ARTICLE"], $info["ARTICLE_TITRE"]));
            }
        }
    }

    public function removeFromHisto($typeInfo, $info, &$authInfos) {
        //On récupère l'info dans le bon user (U ou G)
        $typeUser = 'G';
        if (isset($authInfos['U'])) {
            $typeUser = 'U';
        }
        //On regarde si on a déjà quelque chose pour cette information
        $arrayForRemove = array();
        if (isset($authInfos[$typeUser]['HISTO_JSON']->$typeInfo)) {
            $arrayForRemove = $authInfos[$typeUser]['HISTO_JSON']->$typeInfo;
        }
        if (in_array($info, $arrayForRemove)) {
            //Si présent, on enlève...
            array_splice($arrayForRemove, array_search($info, $arrayForRemove), 1);
            $authInfos[$typeUser]['HISTO_JSON']->$typeInfo = $arrayForRemove;
            //On sauvegarde dans la DB
            if ($typeUser == 'U') {
                if($this->currentSite == "normal"){
                    $this->managerCom->updateHistoJson($authInfos[$typeUser]['ID_USER'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }else{
                    $this->managerCom->updateHistoJsonInt($authInfos[$typeUser]['ID_USER'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }
            } else {
                if($this->currentSite == "normal"){
                    $this->managerComMongo->updateGuestHistoJson($authInfos['G']['TOKEN'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }else{
                    $this->managerComMongo->updateGuestHistoJsonInt($authInfos['G']['TOKEN'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
                }
            }
        }
    }

    private function preAddToHisto($typeInfo, $info, $arrayToAdd) {
        $infoToAdd = "";
        switch ($typeInfo) {
            case 'recherches' :
                if (isset($arrayToAdd[0]) && $info == $arrayToAdd[0][0]) {
                    return;
                }
                $infoToAdd = array($info, date('d/m/Y H:i'));
                break;
            case 'articles' :
                if (isset($arrayToAdd[0]) && $info['ARTICLE_ID_ARTICLE'] == $arrayToAdd[0]) {
                    return;
                }
                $infoToAdd = $info['ARTICLE_ID_ARTICLE'];
                break;
            default :
                if (in_array($info, $arrayToAdd)) {
                    return;
                }
                $infoToAdd = $info;
        }
        return $infoToAdd;
    }

    /*
     * Cette fonction rend disponibles les informations nécessaires pour le display des 3 derniers articles consultés
     */

    private function loadArticlesDisplayDatas(&$authInfos) {
        if (isset($authInfos['U'])) {
            $this->getArticlesDisplayDatasFor($authInfos, 'U');
        } else {
            $this->getArticlesDisplayDatasFor($authInfos, 'G');
        }
    }

    private function getArticlesDisplayDatasFor(&$authInfos, $typeUser) {
        if (isset($authInfos[$typeUser]['HISTO_JSON']->articles)) {
            $arrayArticlesDisplay = array();
            $articles = $authInfos[$typeUser]['HISTO_JSON']->articles;
            for ($ind = 0; $ind < count($articles) && $ind < 10; $ind++) {
                $article = $articles[$ind];
                $art = $this->content->getArticleFromId($article);
                $arrayArticlesDisplay[] = array($art["ARTICLE_ID_ARTICLE"], $art["ARTICLE_TITRE"]);
            }
            $authInfos[$typeUser]['HISTO_JSON_ARTICLES'] = $arrayArticlesDisplay;
        } else {
            //Si pas encore présent, on l'initialise pour qu'il puisse recevoir son premier article
            $authInfos[$typeUser]['HISTO_JSON_ARTICLES'] = array();
        }
    }

    public function copieFromGuestToUser($typeInfo, $authInfos, $force = 0) {
        if (!isset($authInfos['U'])) {
            return;
        }
        $histoJson = "";
        if ($authInfos["G"]["TOKEN"] != NULL) {
            $guest = $this->contentComMongo->getGuestInfos($authInfos["G"]["TOKEN"]);
            $histoJson = $guest['HISTO_JSON'];
        } else if ($authInfos["G"]["HISTO_JSON"] != NULL) {
            $histoJson = $authInfos["G"]["HISTO_JSON"];
        }
        if ($histoJson != '') {
            //On commence par lire l'info du GUEST (pas lue par défaut en mode 'U')
            $array_from_json = json_decode($histoJson);
            $authInfos["G"]["HISTO_JSON"] = $array_from_json;

            if ($force == 0 && $authInfos['U']['HISTO_JSON']->$typeInfo != '' && count(array_diff($authInfos['U']['HISTO_JSON']->$typeInfo, $authInfos['G']['HISTO_JSON']->$typeInfo)) > 0) {
                return 'alert';
            } else {
                $authInfos['U']['HISTO_JSON']->$typeInfo = $authInfos['G']['HISTO_JSON']->$typeInfo;
                if($this->currentSite == "normal"){
                    $this->managerCom->updateHistoJson($authInfos['U']['ID_USER'], json_encode($authInfos['U']['HISTO_JSON']));
                }else{
                    $this->managerCom->updateHistoJsonInt($authInfos['U']['ID_USER'], json_encode($authInfos['U']['HISTO_JSON']));
                }                
            }
        }
    }

    public function mergeGuestAndUser($typeInfo, $authInfos) {
        if (!isset($authInfos['U'])) {
            return;
        }
        $histoJson = "";
        if ($authInfos["G"]["TOKEN"] != NULL) {
            $guest = $this->contentComMongo->getGuestInfos($authInfos["G"]["TOKEN"]);
            $histoJson = $guest['HISTO_JSON'];
        } else if ($authInfos["G"]["HISTO_JSON"] != NULL) {
            $histoJson = $authInfos["G"]["HISTO_JSON"];
        }
        if ($histoJson != '') {
            //On commence par lire l'info du GUEST (pas lue par défaut en mode 'U')
            $array_from_json = json_decode($histoJson);
            $authInfos["G"]["HISTO_JSON"] = $array_from_json;

            $newArray = array_merge($authInfos['G']['HISTO_JSON']->$typeInfo, array_diff($authInfos['U']['HISTO_JSON']->$typeInfo, $authInfos['G']['HISTO_JSON']->$typeInfo));
            $authInfos['U']['HISTO_JSON']->$typeInfo = $newArray;                
            if($this->currentSite == "normal"){
                $this->managerCom->updateHistoJson($authInfos['U']['ID_USER'], json_encode($authInfos['U']['HISTO_JSON']));
            }else{
                $this->managerCom->updateHistoJsonInt($authInfos['U']['ID_USER'], json_encode($authInfos['U']['HISTO_JSON']));
            } 
        }
    }

    public function clearHistorique($typeUser, $typeInfo, $authInfos) {
        $authInfos[$typeUser]['HISTO_JSON']->$typeInfo = array();
        //On sauvegarde dans la DB
        if ($typeUser == 'U') {
            if($this->currentSite == "normal"){
                $this->managerCom->updateHistoJson($authInfos[$typeUser]['ID_USER'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
            }else{
                $this->managerCom->updateHistoJsonInt($authInfos[$typeUser]['ID_USER'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
            } 
        } else {
            if($this->currentSite == "normal"){
                $this->managerComMongo->updateGuestHistoJson($authInfos['G']['TOKEN'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
            }else{
                $this->managerComMongo->updateGuestHistoJsonInt($authInfos['G']['TOKEN'], json_encode($authInfos[$typeUser]['HISTO_JSON']));
            }
        }
    }

}
