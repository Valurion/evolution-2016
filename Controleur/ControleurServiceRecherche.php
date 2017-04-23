<?php

/*
 * Pythagoria
 * Pierre-Yves Thomas
 * pierreyves.thomas@pythagoria.com
 */

// load Controleur BaseClass from FrameWork
require_once 'Framework/Controleur.php';

// load Redis Client Model for caching
require_once 'Modele/RedisClient.php';


// laod jsonRpcPth class 
require_once 'Framework/include/JsonRPCServer.php';

// load search model 
require_once 'Modele/Search.php';

class JsonRpcService {

    private $content;
    private $redisClient;

    public function __construct() {
        header("Content-Type:text/javascript");
        $this->redisClient = new RedisClient(Configuration::get('redis_db_search'));
        $this->content = new Search();
    }

    public function doSearch($params) {
        // $params is a ~ json object received [from request]
        // we will overwrite the index value with the Config one!!
        // 
        $params['index'] = array(Configuration::get("indexPath"));

        // check some required parameters, if not present in the request => get the default ones from the Config
        if (!isset($params['searchMode'])) {
            if ($params['request'] == "") {
                $searchMode = "boolean";
            } else {
                $searchMode = "triple";
                if (Configuration::get('modeRech') != null) {
                    $searchMode = Configuration::get('modeRech');
                }
            }
            $params['searchMode'] = $searchMode;
        }

        $params['fieldWeights'] = Configuration::get('fwRech');

        if (!isset($params['expander'])) {
            $expander = array("family");
            if (Configuration::get('expansion') !== false) {
                $expander = explode(',', Configuration::get('expansion'));
            }
            $params['expander'] = $expander;
        }


        if (!isset($params['proxyWindowWidth']))
        {
        
        if (Configuration::get('proxyWindowWidth') != null) {
                    $params['proxyWindowWidth'] = Configuration::get('proxyWindowWidth');
                }  else {
                    
                $params['proxyWindowWidth'] = 8;}
        }
        
        
        if ($this->redisClient->exists($params)) {
            $result = json_decode($this->redisClient->get($params));
            // var_dump($result);
        } else {
            $result = $this->content->doSearch($params);
            $this->redisClient->setex($params, $result);
        }                

        return $result;
    }

    public function doGetDoc($params) {
	// a securiser si utilisé
	return 1;
        //return ($this->content->doGetDoc($params));
    }

    public function doGetHilightPdf($params) {
	// a securiser si utilisé
	return 1;
        //return ($this->content->doGetHilightPdf($params));
    }

}

class soapService {

    private $content;
    private $redisClient;

    public function __construct() {
        $this->redisClient = new RedisClient(Configuration::get('redis_db_search'));
        $this->content = new Search();
    }

    function searchFacet($request, $param) {
        doSearch($request, $param);
    }

    function doSearch($request, $param) {
        $params = array();
        $params['request'] = $request;
        $params['index'] = array(Configuration::get("indexPath"));

        $xml = simplexml_load_string($param);

        // db => on laisse tomber, on prend dans la config
        // booleanCondition :
        if ($xml->fnc != null) {
            $booleanCondition = (STRING) $xml->fnc;
        } else {
            $booleanCondition = "";
        }
        $params['booleanCondition'] = $booleanCondition;

        if ($xml->AutoStopLimit != null) {
            $autoStopLimit = trim((STRING) $xml->AutoStopLimit);
            $params['autoStopLimit'] = (INT) $autoStopLimit;
        }

        if ($xml->MAX_FIND != null) {
            $maxFiles = trim((STRING) $xml->MAX_FIND[0]['value']);
            $params['maxFiles'] = (INT) $maxFiles;
        }
        if ($xml->START != null) {
            $startAt = trim((STRING) $xml->START[0]['value']);
            $params['startAt'] = (INT) $startAt;
        }
        
         

        
        if ($xml->maxContextBlocks != null) {
            $maxContextBlocks = trim((STRING) $xml->maxContextBlocks);
            $params['maxContextBlocks'] = (INT) $maxContextBlocks;
        }


        if ($xml->amountOfContext != null) {
            $amountOfContext = trim((STRING) $xml->amountOfContext);
            $params['amountOfContext'] = (INT) $amountOfContext;
        }

        if ($xml->FIELDWEIGHTS != null) {
            $fieldWeights = trim((STRING) $xml->FIELDWEIGHTS);
            $params['fieldWeights'] = $fieldWeights;
        } else {
            $params['fieldWeights'] = Configuration::get('fwRech');
        }


        if ($xml->noConcepts != null) {
            $noConcepts = trim((STRING) $xml->noConcepts);
            $params['noConcepts'] = (INT) $noConcepts;
        }

        if ($xml->noFacettes != null) {
            $noFacettes = trim((STRING) $xml->noFacettes);
            $params['noFacettes'] = (INT) $noFacettes;
        }

        if ($xml->pack != null) {
            $pack = trim((STRING) $xml->pack);
            $params['pack'] = (INT) $pack;
        }





        // check some required parameters, if not present in the request => get the default ones from the Config

        if ($params['request'] == "") {
            $searchMode = "boolean";
        } else {
            $searchMode = "triple";
            if (Configuration::get('modeRech') != null) {
                $searchMode = Configuration::get('modeRech');
            }
        }
        $params['searchMode'] = $searchMode;

        $params['fieldWeights'] = Configuration::get('fwRech');


        if (!isset($params['expander'])) {
            $expander = array("family");
            if (Configuration::get('expansion') !== false) {
                $expander = explode(',', Configuration::get('expansion'));
            }
            $params['expander'] = $expander;
        }

        $params['proxyWindowWidth'] = 8;
        //file_put_contents("/tmp/toto", "done");

        /*
          $param = '<param><list_db><db>cairn</db></list_db><byWordExact/>
          <MAX_FIND value="100"/>
          <maxContextBlocks>1</maxContextBlocks>
          <amountOfContext>20</amountOfContext>
          <AutoStopLimit>100</AutoStopLimit>
          <FIELDWEIGHTS value="tr:3000,Sectsom:2000,Titre:1500,Motscles:1500,Auteur:3000,Inter1:700,Inter2:500,Inter3:300,Note:100"/>
          <fnc>' . $fnc . '</fnc></param>'; */
        $result = ($this->content->doSearch($params));

        return $result;
    }

}

class ControleurServiceRecherche extends Controleur {

    // instantiate the Model Class


    public function index() {
        if(AuthentificationWS::IsAValidToken($_GET['token'])) {
            $JsonRpcService = new JsonRpcService();
            jsonRPCServer::handle($JsonRpcService);
        }
        else {
            echo "Invalid token.";
        }
    }

    public function soap() {
        if(AuthentificationWS::IsAValidToken($_GET['token'])) {
            $options = array('uri' => 'http://127.0.0.1/');
            //create a new SOAP server
            $server = new SoapServer(NULL, $options);
            //attach the API class to the SOAP Server
            $server->setClass('soapService');
            //start the SOAP requests handler
            $server->handle();
        }
        else {
            echo "Invalid token.";
        }
    }

}

class AuthentificationWS {
    //TODO : a placer en DB si le nombre d'utilisateur augmente.

    public static $tokens = array("cairn" => "RtaQryhNmEZnX9ZHi1g2",
    "archimede" => "HYZsw1ZYp3d2sCVw1DSB");

    public static function IsAValidToken($token) {
        return in_array($token, self::$tokens);
    }
}
