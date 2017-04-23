<?php

$delais = 60 * 60 * 4;   // 4 heures
header("Pragma: public");
header("Cache-Control: maxage=" . $delais);
header("Expires: " . gmdate('D, d M Y H:i:s', time() + $delais) . " GMT");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function author_permut($string) {
    $string = trim(str_replace('  ', ' ', $string));
    $string = str_replace('  ', ' ', $string);
    $string = str_replace('  ', ' ', $string);
    $string = str_replace(' ', ' AND ', $string);

    return $string;
}

function unaccent_compare($a, $b) {
    return strcmp(trim(strtolower(remove_accents($a['TITRE']))), trim(strtolower(remove_accents($b['TITRE']))));
}

function seems_utf8($str) {
    $length = strlen($str);
    for ($i = 0; $i < $length; $i++) {
        $c = ord($str[$i]);
        if ($c < 0x80)
            $n = 0;# 0bbbbbbb
        elseif (($c & 0xE0) == 0xC0)
            $n = 1;# 110bbbbb
        elseif (($c & 0xF0) == 0xE0)
            $n = 2;# 1110bbbb
        elseif (($c & 0xF8) == 0xF0)
            $n = 3;# 11110bbb
        elseif (($c & 0xFC) == 0xF8)
            $n = 4;# 111110bb
        elseif (($c & 0xFE) == 0xFC)
            $n = 5;# 1111110b
        else
            return false;# Does not match any model
        for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
            if (( ++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                return false;
        }
    }
    return true;
}

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function remove_accents($string) {
    if (!preg_match('/[\x80-\xff]/', $string))
        return $string;

    if (seems_utf8($string)) {
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
            // Euro Sign
            chr(226) . chr(130) . chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194) . chr(163) => '');

        $string = strtr($string, $chars);
    } else {
        // Assume ISO-8859-1 if not UTF-8
        $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
                . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                . chr(252) . chr(253) . chr(255);

        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }
    $string = str_replace("’", "'", $string);
    return $string;
}

function removeLig($string) {
    $chars = array('Œ' => 'OE', 'œ' => 'oe',
        'æ' => 'ae', 'Æ' => 'AE',
        'ĳ' => 'ij',
        'ﬀ' => 'ff',
        'ﬁ' => 'fi',
        'ﬂ' => 'fl',
        'ﬃ' => 'ffi',
        'ﬄ' => 'ffl',
        'ﬅ' => 'ft',
        'ﬆ' => 'st');
    $string = strtr($string, $chars);
    return $string;
}


// Cette fonction permet de trier les sous-éléments d'un tableau
// @Author : Julien CADET
// @Date   : Janvier 2017
function orderDataInArray($data, $sortRules = null) {

    // Parcours des données reçues (tableau)
    foreach($data as $key => $value) {

        // Création d'un tableau temporaire
        $tmp = (array) $data[$key];

        // Tri du tableau selon le paramètre donné
        if($sortRules[$key]) {
            if($sortRules[$key] == "asort") {arort($tmp);}      // Sur Valeur Croisant
            if($sortRules[$key] == "arsort") {arsort($tmp);}    // Sur Valeur Décroisant
            if($sortRules[$key] == "ksort") {krort($tmp);}      // Sur Clé Croisant
            if($sortRules[$key] == "krsort") {krsort($tmp);}    // Sur Clé Décroisant
        }
        // Sinon, tri par défaut sur la valeur
        else {
            arsort($tmp);
        }

        // Réassignation
        $data[$key] = $tmp;
    }

    // Renvoie du tableau/objet
    return $data;
}

// Cette fonction permet de trier les sous-éléments d'un tableau
// @Author : Julien CADET
// @Date   : Janvier 2017
function sliceDataInArray($data, $nbreByGroup = null) {

    // Parcours des données reçues (tableau)
    foreach($data as $key => $value) {

        // Création d'un tableau temporaire
        $tmp = (array) $data[$key];

        // Récupère uniquement la partie des données souhaitées (sinon, on garde tout)
        if($nbreByGroup[$key]) {
            array_splice($tmp, $nbreByGroup[$key]);
        }

        // Réassignation
        $data[$key] = $tmp;
    }

    // Renvoie du tableau/objet
    return $data;
}

// 1) Conserve les valeurs qui ont été retirées du tableau principale
// 2) Regroupe les valeurs en une valeur "Autres"
function autresFacettes($facettesAll, $facettesSliced) {

    // Init
    $tmp  = array();
    $data = array();


    // Parcours l'ensemble des données et ne conserve que
    // les données supprimées du tableau
    foreach($facettesAll as $key => $facettes) {

        // Récupération des différences
        $data[$key] = array_diff_assoc($facettesAll[$key], $facettesSliced[$key]);
    }

    // Regroupements des données par groupe
    foreach($data as $key => $facettes) {
        // Conteneur
        $ids    = array();
        $nbres  = 0;

        // Parcours des facettes
        foreach($facettes as $id => $value) {
            $ids[] = $id;
            $nbres += $value;
        }

        // Assignation
        if($nbres != 0) {
            if(Configuration::get('mode') == 'normal') {$label = "Autres";}
            if(Configuration::get('mode') == 'cairninter') {$label = "Others";}
            $tmp[$key][] = array("ID" => implode(",", $ids), "LABEL" => $label, "NBRE" => $nbres);
        }

    }

    // Réassignation
    $data = $tmp;

    // Renvoie du tableau/objet
    return $data;
}


require_once 'Framework/Controleur.php';
require_once 'Modele/Search.php';
require_once 'Modele/Content.php';
require_once 'Modele/ManagerStatMongo.php';
require_once 'Modele/RedisClient.php';
require_once 'Modele/Filter.php';
require_once 'Modele/Translator.php';
require_once 'Modele/AnalyseRequest.php';
require_once 'Framework/include/JsonRpcPth.php';

class ControleurRecherche extends Controleur {

    private $content;
    private $contentdb;
    private $managerStat;
    private $redisClient;
    private $redisClientF;
    private $filter;
    private $analyser;
    private $expandSearch = true;
    private $urlService; //pour la recherche depuis le nt110

    // instantiate the Model Class
    public function __construct() {
        $this->content = new Search();
        $this->contentdb = new Content();
        $this->managerStat = new ManagerStatMongo(Configuration::get('dsn_stat_mongo'));
        $this->redisClient = new RedisClient(Configuration::get('redis_db_search'));
        $this->redisClientF = new RedisClient(Configuration::get('redis_db_user'));
        $this->filter = new Filter();
        $this->analyser = new AnalyseRequest();
        $this->urlService = Configuration::get('middleware_json_rpc', null);  // pour la recherche en utilisant le middleware
    }

    /**
     * Construction de la requête booleenne pour le formulaire de recherche avancée
     * Basiquement, pour chaque src on crée une requête de type "src contains (value)" ou "xfilter(word src::'value')"
     */
    private function advancedFormRequestBuild($srcParams, $opeParams, $iParams) {
        // Initialisation du morceau de filtre (fpart = filter part)
        $fPart = "";

        // Tableau de correspondance des index / filtres
        // KEY = Element du formulaire via SRC, Value = index/filtre pour le middleware
        $arrayIndex    = array("R" => "Resume", "B" => "Biblio", "Tr" => "rev0", "T" => "titre", "To" => "titrech", "Disc" => "dr", "Year" => "dp", "A" => "auteur", "Editeur" => "ed", "TypePub" => "tp", "ISBN" => "ISBN", "ISSN" => "ISSN", "DOI" => "DOI");

        // Tableau de correspondance des opérateurs
        // KEY = Element du formulaire via OPERATEUR, Value = operateur pour le middleware
        $arrayOperator  = array("" => "", "AND" => " and ", "OR" => " or ", "BUT" => " and not ", "NEAR" => " near ");
        $operator = $arrayOperator[$opeParams];

        // Construction en fonction des cas
        // Texte Intégral
        if($srcParams == "Tx") {
            // Récupération des valeurs et création de la requête
            if($this->requete->existeParametre("word".$iParams)) {
                $value      = $this->requete->getParametre("word".$iParams);

                // Expression exacte
                if($this->requete->existeParametre("exact".$iParams)) {
                    $fPart = "(\"".$value."\")".$operator;
                }
                // Expression détaillée
                else {
                    $fPart = "(".$value.")".$operator;
                }
            }
        }
        // Champs de texte
        else if (($srcParams == "R") || ($srcParams == "B") || ($srcParams == "Tr") || ($srcParams == "T") || ($srcParams == "To") || ($srcParams == "A") || ($srcParams == "ISBN") || ($srcParams == "ISSN") || ($srcParams == "DOI")) {
            // Récupération des valeurs et création de la requête
            if($this->requete->existeParametre("word".$iParams)) {
                $value = $this->requete->getParametre("word".$iParams);
                $index = $arrayIndex[$srcParams];
                $fPart = "(".$index." contains (".$value."))".$operator;
            }
        }
        // Champ de date
        else if($srcParams == "Year") {
            // Récupération des valeurs et création de la requête
            if( ($this->requete->existeParametre("from".$iParams)) && ($this->requete->existeParametre("to".$iParams)) ) {
                $value1 = $this->requete->getParametre("from".$iParams);
                $value2 = $this->requete->getParametre("to".$iParams);
                $index = $arrayIndex[$srcParams];
                $fPart = "(xfilter (word '".$index."::".$value1."~~".$value2."'))".$operator;
            }
        }
        // XFilter (Discipline, Editeur, Type de publication)
        else {
            // Récupération des valeurs et création de la requête
            if($this->requete->existeParametre("word".$iParams)) {
                $value = $this->requete->getParametre("word".$iParams);
                $index = $arrayIndex[$srcParams];
                $fPart = "(xfilter (word '".$index."::".$value."'))".$operator;
            }
        }

        // Retour
        return $fPart;
    }

    private function makeBooleanFacette(&$facettes, $name, $operator) {
        $f_bool = array();
        foreach ($facettes as $facette) {
            $f_bool[] = "(xfilter (word \"$name::$facette\"))";
        }
        return "(" . implode(")$operator(", $f_bool) . ")";
    }

    public function index() {

        // Initialisation des valeurs de recherche
        $searchTerm         = "";
        $searchTermAccess   = "all";
        $searchTermPlus     = array();      // Pour ne pas créer des variables en continu, ce tableau permet de stocker des données et de les passers dans la vue (ex.: ID_REVUE, ...)
        $boolean            = array();
        $advanced           = array();

        $periode            = "ALL";        // /!\ Utilité à définir
        $sortMode           = "PERT";       // Valeur par défaut (ou "")
        $startAt            = 0;

        $indexes            = array(Configuration::get("indexPath"));
        $fw                 = Configuration::get('fwRech');
        $pack               = Configuration::get('packing');
        $proxyWindowWidth   = 8; // La taille de la fenêtre pour le paramètre de proximité (à utiliser lorsque la recherche est en mode double_Z2_Z3) valeur par défaut : 8

        // Configuration des facettes
        $cairnFacettes      = explode(',', Configuration::get('cairnFacettes'));    // array("tp", "dr", 'id_r', "dp");
        $cairnLabels        = explode(',', Configuration::get('cairnLabels'));      // array('tp' => 'Types', 'dp' => 'Dates de parution', 'dr' => 'Disciplines', 'id_r' => "Revues/collect.");
        $facette2labels     = array_combine($cairnFacettes, $cairnLabels);          // Nécessaire pour le Middleware
        $labels2facettes    = array_flip($facette2labels);                          // Nécessaire pour le Middleware

        $facettesSelected   = array();

        // Définition de l'extansion
        // Chaque string représente un type d'expansion à appliquer aux termes de la requête
        // La valeur par défaut est consignée dans le fichier de configuration sous la forme d'une liste de valeurs séparées par un « ; »
        // => Config[expansion=family;lemma]
        // ex.: family : expansion aux termes proches (vivre,vie,vécu),
        //      Lemma permet de lemmatiser les termes de la reqûete
        $expander = array("family");
        if(Configuration::get('expansion') !== false){
            $expander = explode(',',Configuration::get('expansion'));
        }

        // Paramètre (?) utilisé dans l'élaboration du tableau des facettes
        if ($this->requete->existeParametre('refinedr')) { $is_refinedr = 1; } else { $is_refinedr = 0; }

        // Paramètres complémentaire
        if ($this->requete->existeParametre('type_search')) {
            if ($this->requete->getParametre('type_search') == 'english') {
                $boolean[] = '((xfilter (word "efta::2")))';
                $facetteshidden['efta'] = '0,1,2';
                $facettesJson['efta'] = '2';
                Service::get('CairnHisto')->addToHisto('searchMode', 'english', $this->authInfos);
            } else {
                Service::get('CairnHisto')->addToHisto('searchMode', 'all', $this->authInfos);
            }
        }



        // Définition du mode de tri (order by)
        if($this->requete->existeParametre("orderby")) {
            $sortMode = $this->requete->getParametre("orderby");
        }

        // Définition du point de départ
        if ($this->requete->existeParametre("START")) {
            $startAt = (int) $this->requete->getParametre("START");
        }

        // Recherche précise : NUMERO ou REVUE
        if ($this->requete->existeParametre("ID_NUMPUBLIE")) {
            $id_numpub = $this->requete->getParametre("ID_NUMPUBLIE");
            $boolean[] = "(xfilter (word \"np::$id_numpub\"))";
            $advanced['ID_NUMPUBLIE'] = $id_numpub;
            $searchTermPlus["ID_NUMPUBLIE"] = $id_numpub;
        }
        if ($this->requete->existeParametre("ID_REVUE")) {
            $id_numpub = $this->requete->getParametre("ID_REVUE");
            $boolean[] = "(xfilter (word \"id_r::$id_numpub\"))";
            $advanced['ID_REVUE'] = $id_numpub;
            $searchTermPlus["ID_REVUE"] = $id_numpub;
        }

        // Profilage institution
        if (isset($this->authInfos['I']['PARAM_INST']['S'])) {
            $notdrs = explode(',', $this->authInfos['I']['PARAM_INST']['S']);
            $allDisc = $this->contentdb->getDisciplines(null, 1);
            $boolDisc = "";
            foreach($allDisc as $disc){
                if(!in_array($disc['POS_DISC'], $notdrs)){
                    $boolDisc .= ($boolDisc==""?"":" OR ").'(xfilter (word \"dr::'.$disc['POS_DISC'].'\"))';
                }
            }
            if($boolDisc != ''){
                $boolean[] = "(".$boolDisc.")";
            }
            /*foreach ($notdrs as $notdr) {
                $boolean[] = "(xfilter (notword \"dr::$notdr\"))";
            }*/
        }
        if (isset($this->authInfos['I']['PARAM_INST']['Y'])) {
            $nottps = explode(',', $this->authInfos['I']['PARAM_INST']['Y']);
            foreach ($nottps as $nottp) {
                $boolean[] = "(xfilter (notword \"tp::$nottp\"))";
            }
        }

        // Type de recherche (all ou access)
        if ($this->requete->existeParametre("searchIn")) {
            $searchTermAccess = $this->requete->getParametre("searchIn");

            // Stockage de la valeur pour la durée de la session
            setcookie('UsersearchIn', $searchTermAccess);

        }
        if ($this->requete->existeParametre("searchTermAccess")) {
            $searchTermAccess = $this->requete->getParametre("searchTermAccess");
        }

        /**
         * Récupération des valeurs du filtre / facettes
         * Pour chaque élément du filtre, on ajoute une condition
         *
         * On peut déduire qu'un filtre a été appliqué sur les éléments de recherche grâce au paramètre "filter"
         */
        if($this->requete->existeParametre("filter")) {
            // Parcours des différents types de facettes ("tp", "dr", 'id_r', "dp")
            foreach ($cairnFacettes as $facette) {

                // Récupération des données de la facette si elle existe et si elle a une valeur
                // Si la valeur est ALL, il n'y a donc pas de restriction
                if(($this->requete->existeParametre($facette)) && ($this->requete->getParametre($facette) != "") && ($this->requete->getParametre($facette) != "ALL")) {
                    // Récupération des valeurs
                    $valeurs = $this->requete->getParametre($facette);
                    $valeurs = explode(",", $valeurs);

                    // Définition de la condition
                    $boolean[] = $this->makeBooleanFacette($valeurs, $facette, 'OR');
                    $facettesSelected[$facette] = $valeurs;
                }
            }
        }

        // Concaténation des éléments de la recherche boolean
        $booleanCondition = implode(" AND ", $boolean);

        /**
         * Afin d'afficher une entrée dans l'historique lors de l'utilisation du formulaire de recherche avancée,
         * on crée un terme de recherche $advancedSearchTerm (un peu similaire à TRAD anciennement) avec les
         * différents éléments du formulaire
         */
        $advancedSearchTerm             = "";
        $advancedSearchTermOperator     = array("" => "", "AND" => "ET", "OR" => "OU", "BUT" => "SAUF", "NEAR" => "PRES DE"); // Tableau de correspondance des opérateurs (pas de moteur de recherche avancé sur cairn-int)
        $advancedSearchTermDisciplines  = array("70" => "Arts", "2" => "Droit", "1" => "Economie,  Gestion", "30" => "Géographie", "3" => "Histoire", "9" => "Info. - Com.", "4" => "Intérêt général", "5" => "Lettres et linguistique", "139" => "Médecine", "6" => "Philosophie", "7" => "Psychologie", "141" => "Santé publique", "8" => "Sciences de l’éducation", "10" => "Sciences politiques", "11" => "Sociologie et société", "12" => "Sport et société");  // Tableau des disciplines
        $advancedSearchTermTypepub      = array("1" => "Revue", "2" => "Magazine", "3" => "Ouvrages", "6" => "Que sais-je / Repères");  // Tableau des types

        /**
         * Décembre 2016 : Modification du formulaire de recherche avancée
         * Développement par Julien CADET
         * Dans le cas d'une recherche avancée, il faut récupérer l'ensemble des paramètres reçu et
         * les traduires en une requête interpretable par le middleware de Pythagoria
         *
         * On peut déduire qu'il existe une recherche avancée grâce au paramètre "submitAdvForm" au
         * lieu du larech_ et/ou du etou_ et si le paramètre nparams (qui compte le nombre d'élément)
         * existe et si il contient au moins 1 élément
         */
        $SrcFacetteCorespondance    = array("TypePub" => "tp", "Disc" => "dr", "Tr" => "id_r", "Year" => "dp"); // Tableau de correspondance des valeurs
        if($this->requete->existeParametre("submitAdvForm") && ($this->requete->existeParametre("nparams") && $this->requete->getParametre("nparams") != 0) ) {

            // Initialisation des valeurs
            $fnc_rech = ""; // Représente la requête envoyée au middleware (ex.: "(Maison) and (xfilter (notword "id_r::EDM")) and (xfilter (word "dp::2001~~2016" ))")
            $nParams  = $this->requete->getParametre("nparams"); // Nombre de paramètres complet (src + valeur + operateur) passé. Facilite la gestion des valeurs.

            // Parcours des données reçues
            for($iParams = 1; $iParams <= $nParams; $iParams++) {

                // Initialisation des valeurs
                $srcParams = "";
                $opeParams = "";

                // Récupération des valeurs
                if($this->requete->existeParametre("src".$iParams)) {$srcParams = $this->requete->getParametre("src".$iParams);}
                if($this->requete->existeParametre("operator".$iParams)) {$opeParams = $this->requete->getParametre("operator".$iParams);}

                // Création du morceau de requête en fonction de la source et de l'opérateur
                $reqParams = $this->advancedFormRequestBuild($srcParams, $opeParams, $iParams);

                // Sélection des facettes adéquates (si la facette existe pour le type d'élément)
                if (array_key_exists($srcParams, $SrcFacetteCorespondance)) {

                    // Src & Valeur
                    $facette = $SrcFacetteCorespondance[$srcParams];

                    // Valeurs différentes d'un range d'année
                    if($facette != "dp") {
                        // Récupération de la valeur
                        $valeur = $this->requete->getParametre("word".$iParams);

                        // Assignation de la valeur dans les facettes sélectionnées
                        $facettesSelected[$facette][] = $valeur;
                    }
                    // Range d'année
                    else {
                        // Récupération de la valeur
                        $startValue = $this->requete->getParametre("from".$iParams);
                        $endValue   = $this->requete->getParametre("to".$iParams);

                        // Avant 2007, on redéfini l'année de départ et on ajoute la recherche AVANT 2007
                        if($startValue < 2007) { $startValue = 2007; $facettesSelected[$facette][] = "~~2006"; }
                        // Si la recherche complète est AVANT 2007, on ajoute que la recherche AVANT 2007
                        if($endValue < 2007) { $startValue = $endValue = 0; $facettesSelected[$facette][] = "~~2006"; }

                        // Boucle
                        for($iY = $startValue; $iY <= $endValue; $iY++) {
                            $facettesSelected[$facette][] = $iY;
                        }
                    }
                }
                // Concaténation
                $fnc_rech .= $reqParams;

                // Création d'un terme de recherche pour insérer dans l'historique
                // Récupération de la valeur de recherche
                if($srcParams == "Year") {
                    $valueParams = $this->requete->getParametre("from".$iParams)."-".$this->requete->getParametre("to".$iParams);
                } else {
                    // On récupère l'ID de la discipline et on affiche la valeur textuelle...
                    if($srcParams == "Disc") {
                        $valueParams = $advancedSearchTermDisciplines[$this->requete->getParametre("word".$iParams)];
                    }
                    // ...On récupère l'ID du type de publication et on affiche la valeur textuelle
                    else if($srcParams == "TypePub") {
                        $valueParams = $advancedSearchTermTypepub[$this->requete->getParametre("word".$iParams)];
                    }
                    // ...On récupère le nom de l'éditeur
                    else if($srcParams == "Editeur") {
                        $editeur[0] = $this->contentdb->getEditeurById($this->requete->getParametre("word".$iParams));
                        $valueParams = $editeur[0]["EDITEUR_NOM_EDITEUR"];
                    }
                    // ...sinon on affiche simplement la valeur
                    else {
                        $valueParams = $this->requete->getParametre("word".$iParams);
                    }
                }
                // Concaténation du terme (valeur + opérateur traduit)
                $advancedSearchTerm .= $valueParams." ".$advancedSearchTermOperator[$opeParams]." ";
            }
            //echo $fnc_rech;

            // Nettoyage (si nécessaire de la requête)
            $fnc_rech = rtrim($fnc_rech, " and ");
        }

        //var_dump($facettesSelected);

        // Récupération de la valeur de recherche (normal search form)
        if ($this->requete->existeParametre("searchTerm")) {
            $searchTerm = $this->requete->getParametre("searchTerm");
            $searchTerm = str_replace('’', "'", $searchTerm);

            Service::get("CairnHisto")->addToHisto('recherches', $searchTerm, $this->authInfos);
        }
        // Ajout du terme de recherche généré par le moteur de recherche avancée dans l'historique
        else {
            Service::get("CairnHisto")->addToHisto('recherches', $advancedSearchTerm, $this->authInfos);
        }

        // Définition de la condition booléene
        // Si il existe des conditions supplémentaires ($booleanCondition), comme une recherche
        // par ID, une limitation par l'institution, on les ajoutes aux paramètres de recherche
        if ((isset($fnc_rech) && $fnc_rech != '')) {
            if (!($booleanCondition == ''))
                $booleanCondition = "($booleanCondition) AND ($fnc_rech)";
            else
                $booleanCondition = $fnc_rech;
        }



        // Définition du mode de recherche
        // Mode boolean
        if ($searchTerm == "") {
            $searchMode = "boolean";
        }
        // Mode triple
        else {
            $searchMode = "triple";
            if(Configuration::get('modeRech') != null){
                $searchMode = Configuration::get('modeRech');
            }

            // Nettoyage du terme de recherche
            $str = trim($searchTerm);

            // Le terme de recherche est entre guillemets ...
            if (substr($str, 0, 1) == '"' && substr($str, strlen($str) - 1) == '"') {
                $this->expandSearch = false;
                // Suppression de la ponctuation, parenthèse ou étoile présente dans le terme de recherche
                if (ctype_punct(substr($str, strlen($str) - 2)) && substr($str, strlen($str) - 2) != ')' && substr($str, strlen($str) - 2) != '*') {
                    $searchTerm = substr($str, 0, strlen($str) - 2) . '"';
                }
            }
            // Le terme de recherche n'est pas entre guillemets ...
            else {
                // ... mais contient un double guillemets
                if(strpos($str,'"') !== FALSE){
                    $this->expandSearch = false;
                }
                // Suppression de la ponctuation, parenthèse ou étoile présente dans le terme de recherche
                if (ctype_punct(substr($str, strlen($str) - 1)) && substr($str, strlen($str) - 1) != ')' && substr($str, strlen($str) - 1) != '*' ) {
                    $searchTerm = substr($str, 0, strlen($str) - 1);
                }
            }

            // Re-définition de l'extansion (peut-être peut-il est déplacé ci-dessus ?)
            if ($this->expandSearch == false) {
                $expander = array();
            }

            // Nettoyage du terme de recherche
            $searchTerm = str_replace(' et ', ' et~ ', $searchTerm);
            $searchTerm = str_replace(' ou ', ' ou~ ', $searchTerm);
            $searchTerm = str_replace(' SAUF ', ' ET PAS ', $searchTerm);
            $searchTerm = removeLig($searchTerm);
        }


        // Si on a une config spécifique, on surcharge...
        $evidensse = null;
        if ($this->requete->existeParametre('evidensse')) {
            $evidensse = $this->requete->getParametre('evidensse');
            foreach ($evidensse as $key => $value) {
                if ($key == 'expander') {
                    if (is_array($value)) {
                        $$key = $value;
                    } else {
                        $$key = explode(',', $value);
                    }
                } else {
                    $$key = $value;
                }
            }
        }

        // On regarde si on a besoin d'un searchFilter (si il est dispo ou si on doit le générer
        $applyFilter = '';
        //Le choix de la recherche se fait dans le G ou le U
        /*if((isset($this->authInfos['U']) && isset($this->authInfos['U']['HISTO_JSON']->searchModeInfo) && $this->authInfos['U']['HISTO_JSON']->searchModeInfo[0] == 'access')
            || (!isset($this->authInfos['U']) && isset($this->authInfos['G']) && isset($this->authInfos['G']['HISTO_JSON']->searchModeInfo) && $this->authInfos['G']['HISTO_JSON']->searchModeInfo[0] == 'access'))
        {*/
        if(($this->requete->existeParametre("searchIn") && $this->requete->getParametre("searchIn") == 'access') || $this->requete->existeParametre("searchTermAccess") && ($this->requete->getParametre("searchTermAccess") == 'access')){
            //Mais on n'applique un filtre que si on est connecté institution
            if (isset($this->authInfos['I'])){
                if(!$this->redisClientF->exists($this->authInfos['I']['ID_USER'] . "AccessFilter")) {
                    Service::get("Authentification")->genFilter($this->authInfos['I']['ID_USER']);
                }
                //$applyFilter = json_decode($this->redisClientF->get($this->authInfos['I']['ID_USER']."AccessFilter"));
                $applyFilter = "/data/www/sites/EvidensseWork/filters".Configuration::get("runningProcess")."/".$this->authInfos["I"]["ID_USER"].".flt";
            }/*else {
                //Service::get('CairnHisto')->addToHisto('searchModeInfo', 'all', $this->authInfos);
                $applyFilter = Configuration::get('filterPath').'/cairnFreeArticles.flt';
            }*/
            //echo 'Filtre utilisé:'.$applyFilter.'<br/>';
        }


        // Prise en compte du terme de recherche (form search normal)
        if ($searchTerm <> '') {
            $request2analyse = strtolower($searchTerm);
            $request2analyse = str_replace('  ', ' ', $request2analyse);
            $request2analyse = trim($request2analyse);
            $request2analyse = trim($request2analyse);
            $request2analyse = trim($request2analyse);
            $reqArray = explode(" ", $request2analyse);
            if (sizeof($reqArray) < 5)
                $resultC4 = $this->analyser->doAnalyze($request2analyse); {

            }

            $booleanConditionL = "";
            $resultC4 = $resultC4 ? $resultC4 : array();
            foreach ($resultC4 as $C4) {
                //$booleanConditionL.= " orword \"C4::$C4\"  ";
				if($C4 != ''){
	                $booleanConditionL.= " andany(C4 contains($C4))  ";
				}
                //echo " <p>$C4</p>";
            }
            if ($booleanConditionL <> "") {
                if ($booleanCondition <> "")
                    $booleanCondition.="  $booleanConditionL";
                else {
                    $booleanCondition = "xfilter (word 'xlastword')   $booleanConditionL";
                }
            }
            $booleanCondition = trim($booleanCondition);
           // echo " <p>$booleanCondition</p>";
        }

        // Exécution de la recherche via le MiddleWare
        if (isset($advanced['ID_NUMPUBLIE']))
            $searchT = array('searchMode' => $searchMode, 'sort' => $sortMode, 'pack' => 0, 'fieldWeights' => $fw, 'request' => $searchTerm, 'applyFilter' => $applyFilter, 'method' => 'search', 'facettes' => $cairnFacettes, 'wantDetails' => 0, 'maxFiles' => 20, 'startAt' => $startAt, 'spell' => "fr", 'expander' => $expander, "index" => $indexes, "booleanCondition" => $booleanCondition);
        else{
            $searchT = array('searchMode' => $searchMode, 'autoStopLimit' => 350000, 'sort' => $sortMode, 'pack' => $pack, 'fieldWeights' => $fw, 'request' => $searchTerm, 'applyFilter' => $applyFilter, 'method' => 'search', 'facettes' => $cairnFacettes, 'wantDetails' => 0, 'maxFiles' => 20, 'startAt' => $startAt, 'spell' => "fr", 'expander' => $expander, "index" => $indexes, "booleanCondition" => $booleanCondition);
        }

        if($proxyWindowWidth != null)
        {
            $searchT['proxyWindowWidth'] = 8;
        }

        // Le nombre de mots qui seront affichés pour siter le contexte de la recherche
        $searchT['amountOfContext'] = Configuration::get('amountOfContext', 15);
        if (Configuration::get('allow_backoffice', false)) {
            $searchT['amountOfContext'] = $this->requete->getParametre("context", $searchT['amountOfContext']);
        }
        // Le nombre de lettres qui seront affichés pour le mémo d'un numéro
        $contextForNumeroMemo = Configuration::get('amountOfContextForNumeroMemo', 200);
        if (Configuration::get('allow_backoffice', false)) {
            $contextForNumeroMemo = $this->requete->getParametre("context-memo", $contextForNumeroMemo);
        }

        // Prise en compte du tri par date de parution
        if($sortMode == 'byField'){
            $searchT["sort"] = 'byField';
            $searchT["sortField"] = 'fromMap';
            $searchT["sortAscending"] = 0;
        }

        // we check the cached mem
        //
        /* $redis = new Redis();
          $redis->connect(Configuration::get('redis_server'), 6379); */
        if ($this->redisClient->exists($searchT)) {
            $timeStart = microtime(true);
            $result = json_decode($this->redisClient->get($searchT));
            $timeEnd = microtime(true);
            // var_dump($result);
            $searchT['redis'] = 1;
        }
        else {
            $timeStart = microtime(true);

            if(!empty($this->urlService)) {
                $clientS = new JsonRpcPth($this->urlService);
                $result = $clientS->doSearch($searchT);
            }
            else {
                $result = $this->content->doSearch($searchT);
            }

            $timeEnd = microtime(true);
            $this->redisClient->setex($searchT, $result);
            $searchT['redis'] = 0;
        }
        $searchT['execTime'] = $timeEnd - $timeStart;
        $searchT['totalFiles'] = $result->Stats->TotalFiles;
        $searchT['totalUnpacked'] = ((int) $result->Stats->TotalFiles + (int) $result->Stats->rejected);
        $this->managerStat->insertRecherche($searchTerm, $this->authInfos, $searchT);
        // -- Resultat de recherche disponible sous $result -- \\


        // Lors d'une recherche, les facettes sont définies en fonction du résultat.
        // Lors de l'ajout d'un filtre, les facettes ne sont plus identiques puisque le résultat change !
        // Il faut donc concerver les facettes initiales, celles qui correspondent à la recherche de départ.
        // Ce sont ces facettes qui seront affichées dans le formulaire de FILTRE.
        // Aucun filtre n'a été ajouté, il s'agit des valeurs initiales
        if (!$this->requete->existeParametre('filter')) {

            // Définition des facettes (BRUT) en provenance du résultat de recherche du Middleware
            // Les facettes sont divisées en 4 groupes (défini également en config via $cairnFacettes) : tp => Types, dr => Disciplines, id_r => Revues/collect., dp => Dates de parution
            // Chaque groupe est composé d'un ID et d'une valeur numérique représentant le NOMBRE DE RESULTAT pour cet élément
            // ex.: Dans les TYPES (tp), les REVUES, dont l'ID est égal à 1, ont 32144 résultats correspondant à la recherche
            // Note : sur Cairn-int, il semblerait qu'un groupe de facette "efta" existe également.
            $facettes = $result->Facettes;

            // Pré-traitement des données
            // Par facilité, on transforme l'objet en tableau
            $facettes = (array) $facettes;
            // On trie les sous-tableaux dans l'ordre souhaité
            $facettes = orderDataInArray($facettes, array("dp" => "krsort", "efta" => "krsort"));
            // On n'affiche qu'un certain nombre d'élément "parlant" dans chaque sous-tableau,
            // les autres sont conservé pour afficher un élément "Autres"
            $facettesToKeep = sliceDataInArray($facettes, array("dr" => 15, "id_r" => 10));
            $facettesAutres = autresFacettes($facettes, $facettesToKeep);

            // Réassignation des valeurs
            $facettes       = $facettesToKeep;

            // Récupération des ressources : Disciplines et Typepub
            // Ces ressources permettrons d'assigner un label aux données BRUT récupérées
            // Pour les Collections, il faut définir une liste aupréalable afin d'alléger la charge
            $collectionList    = "'".implode("','", array_keys($facettes["id_r"]))."'"; // La méthode getCollectionNamesFromListId() a besoin d'une liste d'ID entourée de simple guillemet (')
            $srcDisciplines    = $this->contentdb->getAllDisciplinesLabels();
            $srcTypepub        = $this->contentdb->getAllTypePubLabels();
            $srcCollections    = $this->contentdb->getCollectionNamesFromListId($collectionList);

            //var_dump($facettes["dr"]);
            //echo "<br />";
            //var_dump($srcDisciplines);

            // Construction du tableau d'affichage des facettes
            // On souhaite en plus de l'ID correspondant et du nombre de résultat :
            // Afficher le label/libellé (ex. pour une discipline : Sociologie et Société)
            $facettesToDisplay = array();
            $dpAv2000Total     = 0;

            // Parcours du tableau selon le type de facette (tp,dr,id_r,dp)
            // Formatage des données pour une exploitation simplifiée
            foreach ($facettes as $key => $values) {

                // Définition de la source des labels/libellés
                if($key == "tp") {$sourceLabel = $srcTypepub;}
                // Disciplines
                if($key == "dr") {$sourceLabel = $srcDisciplines;}
                // Revues / Collection
                if($key == "id_r") {$sourceLabel = $srcCollections;}
                // Date de publication
                if($key == "dp") {$sourceLabel = null;}
                // EFTA
                if($key == "efta") {$sourceLabel = array("0"=>"French full-text", "1"=>"French full-text with English abstract", "2"=>"English full-text");}

                // Parcours des valeurs
                foreach ($values as $id => $value) {
                    // Vérification des données
                    if(($id != "") && ($id != "-")) {
                        // Comportement par défaut
                        if($key != "dp") {
                            // Enregistrement des valeurs
                            $facettesToDisplay[$key][] = array("ID" => $id, "LABEL" => htmlentities(addslashes($sourceLabel[$id])), "NBRE" => $value);
                        }
                        // Gestion des dates de parutions
                        else {
                            // Valeurs au dessus de l'année 2007
                            if($id >= 2007) {
                                $facettesToDisplay[$key][] = array("ID" => $id, "LABEL" => $id, "NBRE" => $value);
                            }
                            // Collecte des données (on ne fait qu'une seule entrée pour les données précédent 2000)
                            else {
                                $dpAv2000Total += $value;
                            }
                        }
                    }
                }
                // Si il existe des données avant 2000, on les ajoutes aux tableaux des facettes
                if($dpAv2000Total > 0) {
                    if(Configuration::get('mode') == 'normal') {$label = "Avant 2007";}
                    if(Configuration::get('mode') == 'cairninter') {$label = "Before 2007";}
                    $facettesToDisplay[$key][] = array("ID" => "~~2006", "LABEL" => $label, "NBRE" => $dpAv2000Total);
                }

                // Ajout des valeurs 'Autres'
                if($facettesAutres[$key]) {
                    $facettesToDisplay[$key][] = $facettesAutres[$key][0];
                }
            }
        }
        // On récupère les facettes initiales grâce aux valeurs reçues en serialize
        else {
            // A cause de certain caractères présents dans les labels, le compteur du serialize n'est plus correcte, le preg-replace permet de contourner le problème)
            // @http://stackoverflow.com/a/9970580
            $facettesToDisplay = unserialize(preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $this->requete->getParametre('facettesToDisplay')));
        }

        //var_dump($facettesSelected);

        // Définition des métas données des résultats
        $listId = array();
        $listNums = array();
        $listId2 = array();
        $listPortals = array();
        $portalInfo = '';
        foreach ($result->Items as $res) {
            $listNums[] = "'" . $res->userFields->np . "'";
            $listId2[] = "'" . trim($res->userFields->id) . "'";
            if ($res->userFields->pk0 == '1') {
                $listId[] = "'" . $res->userFields->np . "'";
            }
            if (isset($res->userFields->idp) && !($res->userFields->idp == '')) {
                $listPortals[] = "'" . trim($res->userFields->id) . "'";
            }
        }

        if (sizeof($listId2) > 0) {

            if (sizeof($listPortals) > 0) {
                $portalInfo = $this->contentdb->getPortalInfoFromArticleId(implode(',', $listPortals));
            }
            $modeBoutons = Configuration::get('modeBoutons');
            if ($modeBoutons == 'cairninter') {
                $articlesButtons = Service::get('ContentArticle')->readButtonsForInterFromSearch(implode(',', $listId2), $this->authInfos);
            } else {
                $articlesButtons = Service::get('ControleAchat')->whichButtonsForArticles($this->authInfos, implode(',', $listId2));
            }
        }

        // Termes associés (non-utilisé)
        $concepts = array();
        $concepts = $result->Concepts;

        // Version normale
        // Récupération des articles EN
        if(Configuration::get('mode') == 'normal') {
            require_once 'Modele/ManagerIntPub.php';
            $managerIntPub              = new ManagerIntPub('dsn_int_pub');

            // Parsing
            foreach ($result->Items as $resInt) {

                // Définition de l'ID de l'article
                $id_article = $resInt->userFields->id;
                $articleInt = $managerIntPub->checkIfArticleOnCairnInt($id_article);

                // Ajout des données
                if($articleInt) {
                    $resInt->userFields->cairnArticleInt = $articleInt;
                } else {
                    $resInt->userFields->cairnArticleInt = null;
                }
            }
        }

        if (sizeof($listNums) > 0) {
            $listNum = implode(',', $listNums);
            $metaNumero = $this->contentdb->getMetNumForRecherche($listNum);
        } else {
            $metaNumero = array();
        }

        // Nettoyage des termes de recherches AVANT affichage (normal search form)
        $searchTerm = str_replace(' ET PAS ', ' SAUF ', $searchTerm);
        $searchTerm = str_replace('~', '', $searchTerm);

        // Définition des types de documents
        $typeMicro = array(1 => "Article de Revue", 2 => "Article de Magazine", 3 => "Chapitre d'ouvrage", 4 => "L'état du Monde", 5 => "Contribution d'ouvrage", 6 => "Chapitre d'encyclopédie de poche");
        $typeMacro = array(1 => "Numéro de Revue", 2 => "Numéro de Magazine", 3 => "Ouvrage", 4 => "L'état du Monde", 5 => "Ouvrage collectif", 6 => "Encyclopédie de poche");
        $typeDocument = array(0 => $typeMicro, 1 => $typeMacro);

        // Métadonnées pour webtrends
        $webtrendsService = Service::get('Webtrends');
        $webtrendsTags = array_merge(
            $webtrendsService->getTagsForAllPages('resultats-recherche', $this->authInfos),
            $webtrendsService->getTagsForResearchPage($result->Stats->TotalFiles, $searchTerm)  // TODO: Il y a plusieurs nombres... Je ne sais pas lequel prendre
        );
        $headers = $webtrendsService->webtrendsTagsToHeadersTags($webtrendsTags);

        // Rendu
        $this->genererVue(array('contextForNumeroMemo'=>$contextForNumeroMemo, 'evidensse' => $evidensse, 'portalInfo' => $portalInfo, 'metaNumero' => $metaNumero, 'results' => $result->Items, 'stats' => $result->Stats, 'searchTerm' => $searchTerm, 'searchTermAccess' => $searchTermAccess, 'searchTermPlus' => $searchTermPlus, 'typeDocument' => $typeDocument, 'sortMode' => $sortMode, 'limit' => $startAt, 'articlesButtons' => $articlesButtons, 'facettesToDisplay' => $facettesToDisplay, 'facettesSelected' => $facettesSelected, 'searchT' => $searchT, 'booleanCondition' => $booleanCondition), 'resultatRecherche.php', null, $headers);
    }

    public function pertinent() {
        $indexes = array(Configuration::get("indexPath"));
        if ($this->requete->existeParametre("searchTerm"))
            $searchTerm = $this->requete->getParametre("searchTerm");
        else {
            $searchTerm = "";
        }


        //$searchTerm = remove_accents($searchTerm);
        $boolean = array();
        if ($this->requete->existeParametre("ID_NUMPUBLIE")) {
            $id_numpub = $this->requete->getParametre("ID_NUMPUBLIE");
            $boolean[] = "(xfilter (word \"np::$id_numpub\"))";
        }

        if ($this->requete->existeParametre("ID_ARTICLE")) {
            $id_art = $this->requete->getParametre("ID_ARTICLE");
            $boolean[] = "(xfilter (notword \"id::$id_art\"))";
        }

        if ($this->requete->existeParametre("booleanCondition")) {
            $boolean[] = $this->requete->getParametre("booleanCondition");
        }


        //$fnc_rech = "";
        //$TRA = "";
        //$advanced = array();
        //$this->advancedFormKeywordsAnalyse($TRA, $fnc_rech, $advanced);





        $expander = array("family");
        if(Configuration::get('expansion') !== false){
            $expander = explode(',',Configuration::get('expansion'));
        }
        $booleanCondition = implode(" AND ", $boolean);

        /*if ($TEXTE_SEARCH != '' || $fnc_rech != '') {
            $searchTerm = $TEXTE_SEARCH;

            if (!($booleanCondition == ''))
                $booleanCondition = "($booleanCondition) AND ($fnc_rech)";
            else
                $booleanCondition = $fnc_rech;
        }*/

        if ($searchTerm == "") {
            $searchMode = "boolean";
        } else {
            $searchMode = "triple";
            if (substr($searchTerm, 0, 1) == '"' && substr($searchTerm, strlen($searchTerm) - 1) == '"') {
                $expander = array();
            }
        }

        //echo "boolean:$booleanCondition" . "\n" ."searchMode:$searchMode \nsearchTerm=$searchTerm";
        //var_dump($_POST);

        $fw = Configuration::get('fwPert');

        //Si on a une config spécifique, on surcharge...
        $evidensse = null;
        if ($this->requete->existeParametre('evidensse')) {
            $evidensse = $this->requete->getParametre('evidensse');
            foreach ($evidensse as $key => $value) {
                if ($key == 'expander') {
                    if (is_array($value)) {
                        $$key = $value;
                    } else {
                        $$key = explode(',', $value);
                    }
                } else {
                    $$key = $value;
                }
            }
        }

        //On regarde si on a besoin d'un searchFilter (si il est dispo ou si on doit le générer
        $applyFilter = '';
        //Le choix de la recherche se fait dans le G ou le U
        /*if((isset($this->authInfos['U']) && isset($this->authInfos['U']['HISTO_JSON']->searchModeInfo) && $this->authInfos['U']['HISTO_JSON']->searchModeInfo[0] == 'access')
            || (!isset($this->authInfos['U']) && isset($this->authInfos['G']) && isset($this->authInfos['G']['HISTO_JSON']->searchModeInfo) && $this->authInfos['G']['HISTO_JSON']->searchModeInfo[0] == 'access'))
        {*/
        if($this->requete->existeParametre("searchIn") && $this->requete->getParametre("searchIn") == 'access'){
            //Mais on n'applique un filtre que si on est connecté institution
            if (isset($this->authInfos['I']) && $this->redisClientF->exists($this->authInfos['I']['ID_USER'] . "AccessFilter")) {
                //$applyFilter = json_decode($this->redisClientF->get($this->authInfos['I']['ID_USER']."AccessFilter"));
                $applyFilter = "/data/www/sites/EvidensseWork/filters".Configuration::get("runningProcess")."/".$this->authInfos["I"]["ID_USER"].".flt";
            }/*else {
                //Service::get('CairnHisto')->addToHisto('searchModeInfo', 'all', $this->authInfos);
                $applyFilter = Configuration::get('filterPath').'/cairnFreeArticles.flt';
            }*/
           // echo 'Filtre utilisé:'.$applyFilter;
        }

        $searchT = array('pack' => 0, 'fieldWeights' => $fw, 'request' => $searchTerm, 'applyFilter' => $applyFilter, 'method' => 'search', 'facettes' => '', 'wantDetails' => 0, 'maxFiles' => 3, 'startAt' => 0, 'spell' => "", 'expander' => $expander, "index" => $indexes, "booleanCondition" => $booleanCondition);


        // Le nombre de mots qui seront affichés pour siter le contexte de la recherche
        $searchT['amountOfContext'] = Configuration::get('amountOfContext', 15);
        if (Configuration::get('allow_backoffice', false)) {
            $searchT['amountOfContext'] = $this->requete->getParametre("context", $searchT['amountOfContext']);
        }

        if(!empty($this->urlService))
        {
            $clientS = new JsonRpcPth($this->urlService);
            $result = $clientS->doSearch($searchT);
        }
        else
        {
            $result = $this->content->doSearch($searchT);
        }

        $listPortals = array();
        $resultsToSort = array();
        $listId2 = array();
        foreach ($result->Items as $item) {
            $listId2[] = "'" . trim($item->userFields->id) . "'";
            $resultsToSort[(int) $item->userFields->pgd] = $item;
            if (!($item->userFields->idp == '')) {
                $listPortals[] = "'" . trim($item->userFields->id) . "'";
            }
        }


        if (sizeof($listPortals) > 0) {
            $portalInfo = $this->contentdb->getPortalInfoFromArticleId(implode(',', $listPortals));
        }

        $articlesButtons = array();
        if (sizeof($listId2) > 0) {
            $articlesButtons = Service::get('ControleAchat')->whichButtonsForArticles($this->authInfos, implode(',', $listId2));
        }

        //ksort($resultsToSort);



        $metaNumero = $this->contentdb->getMetNumForRecherche("'$id_numpub'");

        $typeMicro = array(1 => "Article de Revue", 2 => "Article de Magazine", 3 => "Chapitre d'ouvrage", 4 => "L'état du Monde", 5 => "Contribution d'ouvrage", 6 => "Chapitre d'encyclopédie de poche");
        $typeMacro = array(1 => "Numéro de Revue", 2 => "Numéro de Magazine", 3 => "Ouvrage", 4 => "L'état du Monde", 5 => "Ouvrage collectif", 6 => "Encyclopédie de poche");
        $typeDocument = array(0 => $typeMicro, 1 => $typeMacro);
        $this->genererVue(array('portalInfo' => $portalInfo, 'searchTerm' => $searchTerm, 'searchT' => $searchT, 'metaNumero' => $metaNumero, 'results' => $resultsToSort, 'stats' => $result->Stats, 'typepub' => $typepub, 'typeDocument' => $typeDocument, 'articlesButtons' => $articlesButtons), 'pertinent.php', 'gabaritAjax.php');

//        $this->genererVue(array('test' => $polo),'pertinent.php','gabaritAjax.php');
    }

    public function custom_sort($a, $b) {
        return strcoll($a['last_name'], $b['last_name']);
    }

    public function rechercheAvancee() {

        $revues = $this->contentdb->getRevuesByType(1, true);
        $mags = $this->contentdb->getRevuesByType(2, true);
        $revMags = array_merge($revues, $mags);

        usort($revMags, 'unaccent_compare');

        $collections = $this->contentdb->getRevuesByType(3, true);
        $collectionsEnc = $this->contentdb->getRevuesByType(6, true);
        $colls = array_merge($collections, $collectionsEnc);
        usort($colls, 'unaccent_compare');

        $editeurs = $this->contentdb->getEditeurs();

        $headers = Service::get('Webtrends')->webtrendsHeaders('recherche-avancee', $this->authInfos);

        $this->genererVue(array('revs' => $revues, 'mags' => $mags,
            'revMags' => $revMags, 'colls' => $colls, 'editeurs' => $editeurs), null, null, $headers);
    }

    public function getAjaxAdvancedForm() {
        $revues = $this->contentdb->getRevuesByType(1, true);
        $mags = $this->contentdb->getRevuesByType(2, true);
        $revMags = array_merge($revues, $mags);
        usort($revMags, 'unaccent_compare');

        $collections = $this->contentdb->getRevuesByType(3, true);
        $collectionsEnc = $this->contentdb->getRevuesByType(6, true);
        $colls = array_merge($collections, $collectionsEnc);
        usort($colls, 'unaccent_compare');

        $editeurs = $this->contentdb->getEditeurs();

        $this->genererVue(array('revs' => $revues, 'mags' => $mags,
            'revMags' => $revMags, 'colls' => $colls, 'editeurs' => $editeurs)
                , 'getAjaxAdvancedForm.php', 'gabaritAjax.php');
    }

    public function sujetProche() {
        // get id
        if ($this->requete->existeParametre("ID_ARTICLE")) {
            $ID_ARTICLE = $this->requete->getParametre("ID_ARTICLE");

            $indexes = array(Configuration::get("indexPath"));


            //$searchTerm = remove_accents($searchTerm);
            $boolean = array();
            if ($this->requete->existeParametre("ID_NUMPUBLIE")) {
                $id_numpub = $this->requete->getParametre("ID_NUMPUBLIE");
                $boolean[] = "(xfilter (word \"np::$id_numpub\"))";
            }

            $expander = array("family");


            //$booleanCondition = "(xfilter (word \"id::$ID_ARTICLE\"))";
            $booleanCondition = "(id contains ($ID_ARTICLE))";
            $searchT = array('pack' => 0, 'request' => 'xlastword', 'applyFilter' => '', 'method' => 'search', 'noFacettes' => '1', 'wantDetails' => 0, 'maxFiles' => 1, 'startAt' => 0, 'spell' => "", "index" => $indexes, "booleanCondition" => $booleanCondition);


            if(!empty($this->urlService))
            {
                $clientS = new JsonRpcPth($this->urlService);
                $result = $clientS->doSearch($searchT);
            }
            else
            {
                $result = $this->content->doSearch($searchT);
            }

            //$termesassocies = ($this->contentdb->getTermesassocies("'$ID_ARTICLE'"));


            $myConcepts = array();
            /*
              $C0a = array();
              foreach ($termesassocies as $key => $value) {
              //echo"<p>$value[0]</p>";
              $tt = explode(',', $value[0]);
              $done = 0;
              foreach ($tt as $t) {
              $done++;
              if ($done > 20)
              break;
              $ttt = explode(';', $t);
              //foreach($ttt as $keyt=>$valuet)
              //         echo "<p>".$ttt[0]."::". $ttt[1]."</p>";
              $valLocale = trim($ttt[1]);
              if ($valLocale <> "")
              $C0a[] = remove_accents($valLocale);
              }
              } */


            $C0 = ($result->Items[0]->userFields->C0);
            //echo "<hr/>";
            $C0a = explode('|', $C0);



            $C4 = ($result->Items[0]->userFields->C4);
            $np = ($result->Items[0]->userFields->np);

            $titre = $result->Items[0]->userFields->tr;
            $typePubCurrent = $result->Items[0]->userFields->tp;

            // Complément d'informations
            $idRevue        = $result->Items[0]->userFields->id_r;
            $idNumpublie    = $result->Items[0]->userFields->np;
            $idArticle      = $result->Items[0]->userFields->id;

            // Récupération des données de la revue
            $currentNumero  = $this->contentdb->getNumpublieById($idNumpublie)[0];
            $currentArticle = $this->contentdb->getArticleFromId($idArticle);
            //$auteursArticle = $this->contentdb->getAuteurFromReference($idArticle, array('type' => 'article'));
            //$currentNumero["AUTEURS_ARTICLE"] = $auteursArticle["ARTICLE_AUTEUR"];

            $C4 = str_replace("(", '', $C4);
            $C4 = str_replace(")", '', $C4);
            $C4a = explode('|', $C4);
            $bool = "";
            $bool2 = "";
            for ($x = 0; $x < sizeof($C0a); $x++) {
                $bool.=" andany(" . $C0a[$x] . ":" . (2 * 11 - 2 * $x) . ") ";
                if ($x > 9)
                    break;
            }

            for ($x = 0; $x < sizeof($C4a); $x++) {
                $bool2.=" andany(c4::" . $C4a[$x] . ":" . (10 - $x) . ") ";
                if ($x > 4)
                    break;
            }

            $bool3 = '';
            for ($x = 0; $x < sizeof($C4a); $x++) {
                if ($x == 0)
                    $bool3 = "(c4::" . trim($C4a[$x]) . ":" . (10 - $x) . ")";
                else {
                    $bool3.=" OR (c4::" . trim($C4a[$x]) . ":" . (10 - $x) . ")";
                }

                if ($x > 4)
                    break;
            }



            $booleanCondition = "(xfilter ($bool $bool2))  andany (np contains $np:100)";
            $booleanCondition = "(xfilter ($bool)) AND (($bool3))  andany (np contains $np:1000) AND NOT " . "(xfilter (word \"id::$ID_ARTICLE\"))";
            ;

            /*
             * Profilage institution
             */
            if (isset($this->authInfos['I']['PARAM_INST']['S'])) {
                $notdrs = explode(',', $this->authInfos['I']['PARAM_INST']['S']);
                foreach ($notdrs as $notdr) {
                    $booleanCondition .= " AND (xfilter (notword \"dr::$notdr\"))";
                }
            }
            if (isset($this->authInfos['I']['PARAM_INST']['Y'])) {
                $nottps = explode(',', $this->authInfos['I']['PARAM_INST']['Y']);
                foreach ($nottps as $nottp) {
                    $booleanCondition .= " AND (xfilter (notword \"tp::$nottp\"))";
                }
            }

            $searchT = array('pack' => 0, 'request' => 'xlastword', 'applyFilter' => '', 'method' => 'search', 'noFacettes' => '1', 'wantDetails' => 0, 'maxFiles' => 10, 'startAt' => 0, 'spell' => "", "index" => $indexes, "booleanCondition" => $booleanCondition, 'searchMode' => "boolean");

            if(!empty($this->urlService))
            {
                $clientS = new JsonRpcPth($this->urlService);
                $result = $clientS->doSearch($searchT);
            }
            else
            {
                $result = $this->content->doSearch($searchT);
            }

            $ouvrages = array();
            $revues = array();
            $magazines = array();
            $listId = array();
            $listId2 = array();
            $listNums = array();

            foreach ($result->Items as $res) {
                $listNums[] = "'" . $res->userFields->np . "'";
                $listId2[] = "'" . $res->userFields->id . "'";
                $typePub = $res->userFields->tp;
                if(Configuration::get('modeBoutons') == 'cairninter'){
                    $typePub = 1;
                }
                switch ($typePub) {
                    case "3":
                    case "5":
                    case "6":
                        $ouvrages[] = $res;
                        $listId[] = "'" . $res->userFields->np . "'";
                        break;
                    case "2":
                        $magazines[] = $res;
                        break;
                    case "1":
                        $revues[] = $res;
                        break;
                    case "4":
                        break;
                }

                if (isset($res->userFields->idp) && !($res->userFields->idp == '')) {
                    $listPortals[] = "'" . trim($res->userFields->id) . "'";
                }
            }
            if (sizeof($listNums) > 0) {
                $listNum = implode(',', $listNums);
                $metaNumero = $this->contentdb->getMetNumForRecherche($listNum);
            } else {
                $metaNumero = array();
            }

            if (isset($listPortals) && sizeof($listPortals) > 0) {
                $portalInfo = $this->contentdb->getPortalInfoFromArticleId(implode(',', $listPortals));
            } else {
                $portalInfo = array();
            }

            $articlesButtons = array();
            if (sizeof($listId2) > 0) {
                $modeBoutons = Configuration::get('modeBoutons');
                if ($modeBoutons == 'cairninter') {
                    $articlesButtons = Service::get('ContentArticle')->readButtonsForInterFromSearch(implode(',', $listId2), $this->authInfos);
                } else {
                    $articlesButtons = Service::get('ControleAchat')->whichButtonsForArticles($this->authInfos, implode(',', $listId2));
                }
            }

            $typeMicro = array(1 => "Article de Revue", 2 => "Article de Magazine", 3 => "Chapitre d'ouvrage", 4 => "L'état du Monde", 5 => "Contribution d'ouvrage", 6 => "Chapitre d'encyclopédie de poche");
            $typeMacro = array(1 => "Numéro de Revue", 2 => "Numéro de Magazine", 3 => "Ouvrage", 4 => "L'état du Monde", 5 => "Ouvrage collectif", 6 => "Encyclopédie de poche");
            $typeDocument = array(0 => $typeMicro, 1 => $typeMacro);

            // Metadonnées webtrends
            $headers = Service::get('Webtrends')->webtrendsHeaders('sur-un-sujet-proche', $this->authInfos);
            $this->genererVue(array('titre' => $titre, 'currentNumero' => $currentNumero, 'currentArticle' => $currentArticle, 'metaNumero' => $metaNumero, 'Ouvrages' => $ouvrages, 'Revues' => $revues, 'Magazines' => $magazines, 'label2facette' => $labels2facettes, 'stats' => $result->Stats, 'hiddenFacettes' => $facetteshidden, 'disciplines' => $disciplines, 'typepub' => $typepub, 'searchTerm' => $searchTerm, 'typeDocument' => $typeDocument, 'accessibleArticles' => $accessible_arts, 'limit' => $startAt, 'articlesButtons' => $articlesButtons, 'portalInfo' => $portalInfo, 'typePubCurrent' => $typePubCurrent), null, null, $headers);
        } else {
            // Affichage de la page d'erreur.
            header('Location: http://' . Configuration::get('urlSite', 'www.cairn.info') . '/error_no_id.php');
            die();
        }
    }

    public function redirectToFrench() {
        if ($this->requete->existeParametre('searchTerm')) {
            $searchTerm = $this->requete->getParametre('searchTerm');

            $translator = new Translator();
            $result = $translator->translate($searchTerm);

            $boolOperator = ') W/5 (';
            $searchTermTranslated = urlencode('(' . implode($boolOperator, $result) . ')');
            $newUrl = 'http://' . Configuration::get('crossDomainUrl') . '/resultats_recherche.php?searchTerm=' . $searchTermTranslated;

            header('Location: ' . $newUrl);
        }
    }

    /**
    * Redirige depuis les paramètres fournis dans l'url vers la bonne page.
    * Utilisé pour l'auto-complete.
    * Si le terme recherché n'est pas trouvé, ou si il y a plus d'un résultat, redirige vers la page de recherche
    **/
    public function redirectFromAutocomplete() {
        $CONSTANTS = Service::get('Constants');
        $ParseDatas = Service::get('ParseDatas');
        $term = $this->requete->existeParametre('term') ? $this->requete->getParametre('term') : null;
        $searchIn = $this->requete->existeParametre('searchIn') ? $this->requete->getParametre('searchIn') : null;
        $category = $this->requete->existeParametre('category') ? $this->requete->getParametre('category') : null;
        if ($category === null || $term === null) {
            http_response_code(422);
            return;
        }
        $term = trim($term);
        $datas = array();
        $type = null;
        switch ($category) {
            case $CONSTANTS::AUTOCOMPLETE_CATEGORY_OUVRAGE:
                $datas = $this->contentdb->getDatasForRedirectAutocomplete($category, $term);
                $type = $CONSTANTS::IS_NUMERO;
                break;
            case $CONSTANTS::AUTOCOMPLETE_CATEGORY_REVUE:
                $datas = $this->contentdb->getDatasForRedirectAutocomplete($category, $term);
                // #98818 - quand plusieurs revues lors de la redirection, en choisir une arbitrairement
                if(count($datas) > 1) {
                    // #70861 - On privilégie les Revues plutôt que les collections
                    // Parcours des données à la recherche d'une revue
                    foreach($datas as $data) {
                        // On retrouve bien une revue, on continue
                        if ($data['typepub'] == '1') {$datas = [$data];break;}
                    }
                }
                $type = $CONSTANTS::IS_REVUE;
                break;
            case $CONSTANTS::AUTOCOMPLETE_CATEGORY_AUTEUR:
                $term = trim($term, '.');  // Dirty-fix #68995  À voir avec pythagoria
                $datas = $this->contentdb->getDatasForRedirectAutocomplete($category, $term);
                $type = $CONSTANTS::IS_AUTEUR;
                break;
            default:
                break;
        }
        if ((count($datas) !== 1) || !$type) {
            $url = 'resultats_recherche.php?searchIn='.$searchIn.'&searchTerm="'
                .urlencode($term)
                .'"';
        } else {
            // Ajout à l'historique
            if ($term != "") {
                $searchTerm = $term;
                $searchTerm = str_replace('’', "'", $searchTerm);
                Service::get("CairnHisto")->addToHisto('recherches', $searchTerm, $this->authInfos);
            }

            $url = $ParseDatas->reconstructUrl($type, intval($datas[0]['typepub']), $datas[0]);
        }
        http_response_code(303);
        header('Location: ' . $url);
    }
}
