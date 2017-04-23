<?php

/**
 * CONTROLER - Control the navigation for consultation pages
 * @author ©Pythagoria - www.pythagoria.com
 * @author benjamin
 */
require_once 'Framework/Controleur.php';

require_once 'Modele/Content.php';
require_once 'Modele/ContentCom.php';
require_once 'Modele/ContentStat.php';
require_once 'Modele/ManagerStatMongo.php';
require_once 'Modele/HtmlFile.php';
require_once 'Modele/DefaultFile.php';
require_once 'Modele/Search.php';
require_once 'Modele/SoapSNI.php';
require_once 'Modele/ManagerComMongo.php';

class ControleurPages extends Controleur
{
    private $content;
    private $managerStat;
    private $contentStat;
    private $managerCom;
    private $html;
    private $file;
    private $soapSNI;

    public $mappingTypePage = [
        'PR' => [
            'category' => 'article',
            'subcategory' => 'print-abstract',
            'format' => 'html',
        ],
        'PA' => [
            'category' => 'article',
            'subcategory' => 'print-fulltext',
            'format' => 'html',
        ],
        'R' => [
            'category' => 'article',
            'subcategory' => 'abstract',
            'format' => 'html',
        ],
        'A' => [
            'category' => 'article',
            'subcategory' => 'fulltext',
            'format' => 'html',
        ],
        'Z' => [
            'category' => 'article',
            'subcategory' => 'zen-fulltext',
            'format' => 'html',
        ],
        'PDF' => [
            'category' => 'article',
            'subcategory' => 'fulltext',
            'format' => 'pdf',
        ],
        'SWF' => [
            'category' => 'article',
            'subcategory' => 'fulltext',
            'format' => 'swf',
        ],
        'FEUIL' => [
            'category' => 'article',
            'subcategory' => 'fulltext',
            'format' => 'swf',
        ],
    ];

    // Les liens des auteurs dans le html des articles n'est pas insérés en dur, mais
    // est inséré à la volée.
    // Suivant certaines contributions d'auteurs, on n'insère pas ces liens.
    // Ce tableau doit contenir des regexps, qui seront concaténés en une seule grosse regexps.
    public $ignoreLinkOnAuthorContribution = [
        '\s*traduit\s*par\s*',
        '\s*avec\s*l.+assistance\s+d.*',
    ];
    private $urlService; // pour la recherche en utilisant le middleware

    // instantiate the Model Classes
    public function __construct()
    {
        $this->content = new Content();
        $this->managerStat = new ManagerStatMongo(Configuration::get('dsn_stat_mongo'));
        $this->contentStat = new ContentStat('dsn_stat');
        $this->managerCom = new ManagerComMongo(Configuration::get('dsn_com_mongo'));
        $this->contentCom = new ContentCom('dsn_com');
        $this->html = new HtmlFile();
        $this->file = new DefaultFile();
        $this->soapSNI = new SoapSNI();
        $this->urlService = Configuration::get('middleware_json_rpc', null);
    }
    public function index()
    {
        // Dev spécifique Culturethèque
        if ($this->requete->existeParametre('casid') && $this->requete->existeParametre('ticket')) {
            $casid = $this->requete->getParametre('casid');
            $ticket = $this->requete->getParametre('ticket');
            $idArticle = $this->requete->getParametre('ID_ARTICLE');

            $urlValid = 'http://www.culturetheque.com/EXPLOITATION/Default/validate.aspx?service=' . urlencode("http://" . Configuration::get('urlSite') . "/article.php?ID_ARTICLE=" . $idArticle . "&casid=" . $casid) . "&ticket=" . $ticket;
            // echo $urlValid;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlValid);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

            $response = (curl_exec($ch));
            curl_close($ch);

            if ($response == 'no|') {
                // echo 'No access';
                // return;
            } else {
                // echo 'access ok, login ctheque';

                $userId = 'org_culturetheque';
                if (Service::get('Authentification')->getToken() != null) {
                    $token = Service::get('Authentification')->updateToken($userId, $type);
                } else {
                    $token = Service::get('Authentification')->createToken($userId);
                }
                $this->authInfos = Service::get('Authentification')->readToken($this->requete, 0);

                if (isset($this->authInfos ['I']) && $this->authInfos ['I'] ['ID_USER'] == $userId) {
                    setcookie('cairn_token', $token, strtotime('+' . Configuration::get('userInstSessionDuration') . ' ' . strtolower(Configuration::get('userInstSessionUnit')) . 's'));

                    //$managerCom->insertUserLog(Service::get('Authentification')->getUserLogId('userILog'), $userId, Configuration::get('userInstSessionDuration'), Configuration::get('userInstSessionUnit'));
                    $this->managerCom->insertInstLog($this->authInfos["G"]['TOKEN'],Configuration::get('userInstSessionDuration'), Configuration::get('userInstSessionUnit'));
                    $this->managerComMongo->incrementInstitutionCounter($this->authInfos["G"]['TOKEN'], $userId);
                }
            }
        }

        // Accès par une URL "propre"
        if ($this->requete->existeParametre("REVUE")) {
            $this->selectRevue(
                $this->requete->existeParametre("ID_REVUE") ? $this->requete->getParametre("ID_REVUE") : NULL,
                $this->requete->existeParametre("REVUE") ? $this->requete->getParametre("REVUE") : NULL,
                $this->requete->existeParametre("ANNEE") ? $this->requete->getParametre("ANNEE") : NULL,
                $this->requete->existeParametre("NUMERO") ? $this->requete->getParametre("NUMERO") : NULL,
                $this->requete->existeParametre("ISBN") ? $this->requete->getParametre("ISBN") : NULL,
                $this->requete->existeParametre("PAGE") ? "A" : ($this->requete->existeParametre("P") ? "R" : NULL),
                $this->requete->existeParametre("PAGE") ? $this->requete->getParametre("PAGE") : (
                    $this->requete->existeParametre("P") ? $this->requete->getParametre("P"): NULL
                ),
                $this->requete->existeParametre("TYPEPUB") ? $this->requete->getParametre("TYPEPUB"): NULL
            );
        }

        // Accès par une URL "xxxx.php"
        if ($this->requete->existeParametre("RES")) {//Visualisation article : abstract ou résumé.
            $this->loadTypePage("R");
        }
        if ($this->requete->existeParametre("ZEN")) { //Visualisation en mode Zen.
            $this->loadTypePage("Z");
        }
        if ($this->requete->existeParametre("A")) {
            $this->loadTypePage("A");
        }
        if ($this->requete->existeParametre("PA")) {
            $this->loadTypePage("PA");
        }
        if ($this->requete->existeParametre("PR")) {
            $this->loadTypePage("PR");
        }
        if ($this->requete->existeParametre("PDF")) {
            $this->loadTypePage("PDF");
        }
        if ($this->requete->existeParametre("XML")) {
            $this->loadXml();
        }
        if ($this->requete->existeParametre("EPUB")) {
            $this->loadEpub();
        }
        if ($this->requete->existeParametre("SWF")) {
            $this->loadSwf();
        }
        if ($this->requete->existeParametre("IMG")) {
            $this->loadImg();
        }
        if ($this->requete->existeParametre("FEUIL")) {
            $this->loadTypePage("FEUIL", $this->requete->getParametre("FEUIL"));
        }
        if ($this->requete->existeParametre("EXTRAWEB")) {
            $this->loadTypePage("EXTRAWEB");
        }
    }


    public function statLogCrossValidation() {
        // Chaque fois qu'une page est consultée, on insère une ligne dans cairn3_stat.STAT_LOG
        // Pour faciliter le filtre sur les robots, on insère également une ligne dans cairn3_stat.STAT_LOG_R
        // après une requête effectué depuis un code javascript sur les pages articles
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die();
        }
        if (!$this->requete->existeParametre('id-cross-log')) {
            die();
        }
        $this->managerStat->insertArticleCrossValidation($this->requete->getParametre('id-cross-log'));
        die();
    }


    private function loadTypePage($pageType, $pageVal = null)
    {
        $article = $this->content->getArticleFromId($this->requete->getParametre("ID_ARTICLE"));
        $revue = $this->content->getRevuesById($article ["ARTICLE_ID_REVUE"]);
        $revues = $this->content->getRevuesByUrl($revue [0] ["URL_REWRITING"], $article ["ARTICLE_ID_NUMPUBLIE"], $revue [0] ["TYPEPUB"]);
        $articles = $this->content->getArticlesFromlNumero($article ["ARTICLE_ID_NUMPUBLIE"]);
        $numero = $this->content->getNumpublieById($article ["ARTICLE_ID_NUMPUBLIE"]);
        $countReferencedBy = $this->content->countReferencedBy($article ["ARTICLE_ID_ARTICLE"]);

        $this->revue($revues [0], $numero [0], $articles, $article, $pageType . $pageVal, $revue [0] ["TYPEPUB"], $countReferencedBy);
    }
    private function selectRevue($idRevue, $revueFilter, $anneeNumero, $numeroNumero, $numeroIsbn, $pageType, $pagePage, $typepub)
    {
        if (!isset($idRevue)) {
            $idRevue = $this->content->getIdRevueFromUrl($revueFilter, $typepub);
            if ($idRevue == '') {
                $idRevue = $this->content->getIdRevueFromNumeroUrl($revueFilter, $numeroIsbn);
            }
        }
        $revue = $this->content->getRevuesById($idRevue);
        $typePublication = $revue [0] ["TYPEPUB"];
        if (isset($numeroIsbn)) { // Ouvrage ou encyclopédie
            $numero = $this->content->getNumpublieFromIsbnAndUrlRewriting($numeroIsbn, $revueFilter);
        } else { // Revue ou magazine
            $numero = $this->content->getNumpublie($idRevue, $anneeNumero, $numeroNumero);
        }
        $idNumPublie = $numero [0] ["NUMERO_ID_NUMPUBLIE"];
        $numero[0]['NUMERO_AUTEUR'] = $this->content->getAuteursNum($numero[0]["NUMERO_ID_NUMPUBLIE"]); // Récupération des auteurs & contributeurs
        $revues = $this->content->getRevuesByUrl($revueFilter, $idNumPublie, $typePublication);

        $articles = $this->content->getArticlesFromlNumero($idNumPublie);
        $currentArticle     = $this->content->getArticleFromUrl($idNumPublie, $pagePage);
        $countReferencedBy  = $this->content->countReferencedBy($currentArticle ["ARTICLE_ID_ARTICLE"]);

        $this->revue($revues [0], $numero [0], $articles, $currentArticle, $pageType, $typePublication, $countReferencedBy);
    }

    /*
     * Cette fonction sert à l'affichage du détail d'une revue: - de la version HTML d'un article (le
     * paramètre "page" donne le page_debut) - des premières lignes d'un article (le paramètre "P" donne le
     * page_debut) - du résumé d'un article (paramètre "RES") - du contenu en mode zen (paramètre "ZEN") =>
     * vue différente @param $revue l'array de la REVUE (au sens large, tel que renvoyé par
     * content->getRevuesByUrl)
     */
    private function revue($revue, $numero, $articles, $currentArticle, $pageType, $typePublication, $countReferencedBy)
    {
        // En production, les articles désactivés NE DOIVENT PAS apparaitre
        // En interne, on affiche l'article, mais on prévient les convertisseurs que l'article est désactivé,
        // via la vue
        if ((!isset($revue)) || (!$revue) || ($revue ['STATUT'] == 0)) {
            if (!Configuration::get('allow_backoffice', false)) {
                header('Location: http://' . Configuration::get('urlSite', 'www.cairn.info'));
                die();
            }
        }
        if ((!isset($numero)) || (!$numero) || ($numero ['NUMERO_STATUT'] == 0)) {
            if (!Configuration::get('allow_backoffice', false)) {
                header('Location: http://' . Configuration::get('urlSite', 'www.cairn.info'));
                die();
            }
        }
        if (($currentArticle ['ARTICLE_STATUT'] == '0') || (!isset($currentArticle)) || (!$currentArticle)) {
            if (!Configuration::get('allow_backoffice', false)) {
                header('Location: http://' . Configuration::get('urlSite', 'www.cairn.info'));
                die();
            } else if (!isset($currentArticle) || !$currentArticle) {
                echo "Cet article n'existe pas";
                die();
            }
        }
        if (Configuration::get('modeBoutons') == 'cairninter') {
            // On vérifie pour cairn-international que LANGUE_INTEGRALE soit à `en`. Sinon, cela signifie qu'il n'y a pas de contenu en texte intégrale
            $hasFullTextContent = isset($currentArticle['ARTICLE_LANGUE_INTEGRALE']);
            $hasFullTextContent = $hasFullTextContent && ($currentArticle['ARTICLE_LANGUE_INTEGRALE'] === 'en');
            if (in_array($pageType, ['A', 'Z', 'PA', 'PDF']) && (!$hasFullTextContent)) {
                if (Configuration::get('allow_backoffice', false)) {
                    echo "<div class=\"alert alert-warning\" style=\"margin-bottom: 0;\">
                            Cet article existe, mais la langue intégrale n'est pas à `en`.<br />
                            En production, ce lien redirige vers le résumé html de l'article
                          </div>";
                    //die();
                } else {
                    // On affiche la page résumé
                    $pageType = 'R';
                }
            }

            // Pour qu'un résumé ait un contenu, il doit être dans la table RESUME
            $hasAbstractContent = $this->content->checkResumeInter($currentArticle['ARTICLE_ID_ARTICLE']);
            if ((!$hasFullTextContent) && (!$hasAbstractContent)) {
                if (!Configuration::get('allow_backoffice', false)) {
                    //header('Location: http://' . Configuration::get('urlSite', 'www.cairn.info'));
                    //die();
                } else {
                    echo "<div class=\"alert alert-warning\" style=\"margin-bottom: 0;\">
                            Cet article existe, mais n'a ni résumé, ni texte intégral
                          </div>";
                    //die();
                }
            }
        }

        // On fait tout de suite l'ajout à l'historique
        Service::get('CairnHisto')->addToHisto('articles', $currentArticle, $this->authInfos);

        // On vérifie tout de suite l'accès à l'article
        $hasAccess = Service::get('ControleAchat')->hasAccessToArticle($this->authInfos, $currentArticle, $numero, $revue, 0);
        $hasAccessToNumero = Service::get('ControleAchat')->hasAccessToNumero($this->authInfos, $numero, $revue);
        if (!$hasAccess) $hasAccess = $hasAccessToNumero;

        $hasntUnitSale = Service::get('ControleAchat')->checkArticleSolo($this->authInfos, $currentArticle);
        if(empty($hasntUnitSale)) {$currentArticle["HASNT_UNIT_SALE"] = "NO_UNIT_SALE";}

        //Preprod only - vérification du token acces BàD
        if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token'])) {
            $hasAccess = Service::get("BonADiffuser")->checkTokenForBAD($numero['NUMERO_ID_NUMPUBLIE'],$_GET['token'], $numero['NUMERO_DATE_MISEENLIGNE'] );
        }

        // Si on tente d'accéder à un pdf alors qu'il n'est que disponible en feuilletage, on le redirige vers le feuilletage
        if (($pageType == 'PDF') && (substr($currentArticle['ARTICLE_CONFIG_ARTICLE'], 4, 1)) == 1) {
            $pageType = 'FEUILA';
        }
        // Si on tente d'accéder à un pdf alors qu'il est désactivé, on redirige vers la page de résumé
        if ($pageType == 'PDF' && substr($currentArticle['ARTICLE_CONFIG_ARTICLE'], 6, 1) == 0) {
            $pageType = 'A';
        }
        // Si on tente d'accéder à une page payante, on redirige vers le résumé
        if (($pageType == 'A' || $pageType == 'Z' || $pageType == 'PDF') && !$hasAccess) {
            $pageType = 'R';
        } else if ($hasAccess && ($pageType == 'A' || $pageType == 'Z') && substr($currentArticle ['ARTICLE_CONFIG_ARTICLE'], 2, 1) == 0) {
            if (substr($currentArticle ['ARTICLE_CONFIG_ARTICLE'], 6, 1) == 1) {
                $pageType = 'PDF';
            } else if (substr($currentArticle ['ARTICLE_CONFIG_ARTICLE'], 4, 1) == 1) {
                $pageType = 'FEUILA';
            } else if (Configuration::get('mode') != 'cairninter' && strlen($currentArticle ['ARTICLE_CONFIG_ARTICLE']) > 10 && substr($currentArticle ['ARTICLE_CONFIG_ARTICLE'], 10, 1) == 1) {
                // Redirection immédiate vers portail...
                $pageType = 'PORTAIL';
            } else {
                $pageType = 'R';
            }
        }
        if (($pageType == 'PA') && !$hasAccess) {
            $pageType = 'PR';
        }
        if (!isset($currentArticle['CONFIG_ARTICLE'])) $currentArticle['CONFIG_ARTICLE'] = null;
        if (($pageType == 'FEUILA') && !$hasAccess) {
            $first = substr($currentArticle['CONFIG_ARTICLE'], 0, 1);
            switch ($first) {
                case '2' :
                    $pageType = 'FEUILC'; // current et pas all
                    break;
                default :
                    $pageType = 'R';
            }
        }

        // Voir la méthode $this->statLogCrossValidation
        $idLog = null;
        //Ajout d'une condition pour les logs.
        //Ne pas ajouter de log, pour la landing page.
        if (($pageType == 'PDF') && ($this->requete->existeParametre('act') == true || (isset($this->authInfos['I']) && $this->authInfos['I']['ID_USER'] == 'google_scholar')  || preg_match('/^http(s)?:\/\/'.str_replace("/", "\/", Configuration::get('urlSite')).'/i', $_SERVER['HTTP_REFERER']) == true)) {
            $idLog = $this->insertStatLog($pageType, $currentArticle);
        } elseif (($pageType != 'PDF')) {
            $idLog = $this->insertStatLog($pageType, $currentArticle);
        }

        // On va chercher le contenu dans le fichier
        if ($pageType != 'PORTAIL' && $pageType != 'PDF' && $pageType != 'FEUILA' && $pageType != 'FEUILC')
            $contentPrefix = Configuration::get('filePrefixType' . $pageType);


        $htmlDatas = array();
        $hits = null;
        if ($pageType == 'FEUILA') {
            $hits_local = '';
            if ($this->requete->existeParametre('DocId')) {
                // On appelle le moteur pour récupérer le contenu PDF indexé, avec les hits
                $searchT = "DocId=" . $this->requete->getParametre('DocId') . "&Index=" . Configuration::get('indexPdfPath');
                if ($this->requete->existeParametre('hits')) {
                    $arr_hits = explode(' ', $this->requete->getParametre('hits'));
                    foreach ($arr_hits as $cur_hit) {
                        if (!trim($cur_hit) == "0")
                            $hits_local .= dechex($cur_hit) . '+';
                    }
                    $searchT .= "&hits=" . $hits_local . "&hitsCount=" . $item->item->hitsCount;
                }
                $client = new Search();
                $htmlDatas = $client->doGetHilightPdf($searchT);
            } else if ($this->requete->existeParametre('searchTerm')) {
                // On fait un premier appel au moteur pour récupérer le docid dans l'index PDF
                // Ensuite on fera la même chose...
                $idArticle = $this->requete->getParametre('ID_ARTICLE');
                $searchTerm = $this->requete->getParametre('searchTerm');

                $searchT = array ('request' => $searchTerm,'method' => 'search','startAt' => 0,'expander' => array ("family" ),"index" => array (Configuration::get('indexPdfPath') ),"booleanCondition" => '(xfilter (word \"id::' . $idArticle . '\"))' );
                $client = new Search();
                $return = $client->doSearch($searchT);
                if (!empty($return->Items)) {
                    $item = $return->Items [0];
                    $searchT = "DocId=" . $item->item->docId . "&Index=" . Configuration::get('indexPdfPath');
                    $arr_hits = explode(' ', $item->item->hits);
                    foreach ($arr_hits as $cur_hit) {
                        if (!trim($cur_hit) == "0")
                            $hits_local .= dechex($cur_hit) . '+';
                    }
                    $searchT .= "&hits=" . $hits_local . "&hitsCount=" . $item->item->hitsCount;
                    $htmlDatas = $client->doGetHilightPdf($searchT);
                }
            }
        } else if ($pageType != 'PDF' && $pageType != 'FEUILA' && $pageType != 'FEUILC' && $pageType != 'PORTAIL') {
            if ($revue ['SOAP'] != null && $revue ['SOAP'] != '' && $pageType != 'R') {
                $htmlDatas = $this->soapSNI->doGetDoc($revue, $currentArticle, $this->authInfos);
            } else if ($pageType == 'A' && $this->requete->existeParametre('DocId')) {
                // On appelle le moteur pour récupérer le contenu indexé, avec les hits
                $searchT = "DocId=" . $this->requete->getParametre('DocId') . "&Index=" . Configuration::get('indexPath');
                if ($this->requete->existeParametre('hits')) {
                    $arr_hits = explode(' ', $this->requete->getParametre('hits'));
                    $hits_local = '';
                    foreach ($arr_hits as $cur_hit) {
                        if (!trim($cur_hit) == "0")
                            $hits_local .= dechex($cur_hit) . '+';
                    }
                    $searchT .= "&hits=" . $hits_local;
                    sort($arr_hits);
                    $hits = implode(',', $arr_hits);
                    $hits = substr($hits, 1);
                }
                // echo $searchT;
                if (!empty($this->urlService)) {
                    $client = new JsonRpcPth($this->urlService);
                    $htmlDatas = $client->doGetDoc($searchT);
                    // TODO: le middleware retourne actuellement un objet. La même méthode sur Search retourne un tableau
                    // Le top serait d'avoir deux arrays dans les deux cas, sans quoi le programme plante dans la suite
                    // du programme
                    $htmlDatas = ['METAS' => $htmlDatas->METAS, 'CONTENUS' => $htmlDatas->CONTENUS];
                } else {
                    $client = new Search();
                    $htmlDatas = $client->doGetDoc($searchT);
                }
                // Vérification des images
                $this->applyIMG($htmlDatas, $currentArticle, $numero);
                // Application des DOI aux contenus?
                $this->insertReferencesIntoContent($htmlDatas, $currentArticle ["ARTICLE_ID_ARTICLE"]);
            } else if ($this->html->loadFile($revue ["REVUE_ID_REVUE"] . '/' . $revue ["NUMERO_ID_NUMPUBLIE"] . '/' . $currentArticle ["ARTICLE_ID_ARTICLE"] . '/' . $contentPrefix . '_' . $currentArticle ["ARTICLE_ID_ARTICLE"] . '.htm', 'HTML')) {
                $htmlDatas = $this->html->getContent();
                // Vérification des images
                $this->applyIMG($htmlDatas, $currentArticle, $numero);
                // Application des DOI aux contenus?
                $this->insertReferencesIntoContent($htmlDatas, $currentArticle ["ARTICLE_ID_ARTICLE"]);
            }
        }

        if ($typePublication == 1) {
            $typePub = 'revue';
        } else if ($typePublication == 2) {
            $typePub = 'magazine';
        } else if ($typePublication == 3) {
            $typePub = 'ouvrage';
        } else if ($typePublication == 6) {
            $typePub = 'encyclopédie';
        }

        $curDisc = $this->content->getCurDisciplineEn($revue ["REVUE_ID_REVUE"]);
        $curDiscipline = $curDisc ['URL_REWRITING_EN'];
        $filterDiscipline = $curDisc ['DISCIPLINE_EN'];

        $currentLicence = null;
        /*
         * TODO: Utilisé pour les métadonnées webtrend. Mais Jean-Baptiste s'inquiète pour les performances et
         * l'utilité est réduite pour les statistiques. À voir plus tard
         */
        // if (isset($this->authInfos['I'])) {
        // $uid = in_array($revue['TYPEPUB'], [3, 5, 6]) ? $numero['NUMERO_ID_NUMPUBLIE'] :
        // $revue['REVUE_ID_REVUE'];
        // $currentLicence = $this->content->getUserLicenceOfRevue($this->authInfos['I']['ID_USER'], $uid);
        // }

        /*
         * TODO :: (serge.kilimoff@cairn.info) Je pensais que les meta dans le header ne concernait que
         * webtrends. En réalité, il risque d'y en avoir beaucoups plus que ce que je pensais. Il faudrait
         * faire un truc un poil plus générique que le Service webtrends
         */
        // Parce que la pile de code est trop importante, on merge certaines informations ici
        if (isset($revue['MOVINGWALL'])) {
            $currentArticle['REVUE_MOVINGWALL'] = $revue['MOVINGWALL'];
        }
        if (isset($numero['NUMERO_ANNEE']) && (!isset($currentArticle['NUMERO_ANNEE']))) {
            $currentArticle['NUMERO_ANNEE'] = $numero['NUMERO_ANNEE'];
        }
        if (isset($revue['REVUE_TITRE']) && (!isset($currentArticle['REVUE_TITRE']))) {
            $currentArticle['REVUE_TITRE'] = $revue['REVUE_TITRE'];
        }
        if (isset($numero['NUMERO_TITRE']) && (!isset($currentArticle['NUMERO_TITRE']))) {
            $currentArticle['NUMERO_TITRE'] = $numero['NUMERO_TITRE'];
        }
        if (isset($revue['REVUE_ID_EDITEUR']) && (!isset($currentArticle['EDITEUR_ID_EDITEUR']))) {
            $currentArticle['EDITEUR_ID_EDITEUR'] = $revue['REVUE_ID_EDITEUR'];
        }
        if (isset($revue['REVUE_AFFILIATION'])) {
            $currentArticle['REVUE_AFFILIATION'] = $revue['REVUE_AFFILIATION'];
        }
        //Pour webtrends : Année de mise en ligne
        if (isset($revue['NUMERO_DATE_MISEENLIGNE']) && !empty($revue['NUMERO_DATE_MISEENLIGNE'])) {
            $currentArticle['NUMERO_DATE_MISEENLIGNE'] = $revue['NUMERO_DATE_MISEENLIGNE'];
        }
        //Pour webtrends : Résumé anglais disponible
        if ($pageType == 'A') {
            if ($this->content->checkResumeInter($currentArticle['ARTICLE_ID_ARTICLE'])) {//Texte intégral version HTML
                $currentArticle['RESUME_ANGLAIS_DISPONIBLE'] = 'oui';
            } else {
                $currentArticle['RESUME_ANGLAIS_DISPONIBLE'] = 'non';
            }
        }
        //Pour webtrends : Prise en compte (Uniquement pour le cas des ouvrages).
        if (isset($numero['NUMERO_GRILLEPRIX']) && !empty($numero['NUMERO_GRILLEPRIX'])) {
            $currentArticle['NUMERO_GRILLEPRIX'] = $numero['NUMERO_GRILLEPRIX'];
        }
        //Pour webtrends : Nom de l'éditeur bénéficiaire.
        if (isset($revue['BENEFICIAIRE']) && !empty($revue['BENEFICIAIRE'])) {
            $currentArticle['NOM_EDITEUR_BENEFICIAIRE'] = $revue['BENEFICIAIRE'];
        }
        //Pour webtrends : Discipline principale de la revue.
        if (isset($revue['DISCIPLINE']) && !empty($revue['DISCIPLINE'])) {
            $currentArticle['REVUE_DISCIPLINE'] = $revue['DISCIPLINE'];
        }



        $patternIgnoreLinkOnAuthorContribution = '/';
        foreach ($this->ignoreLinkOnAuthorContribution as $index => $pattern) {
            $patternIgnoreLinkOnAuthorContribution .= '(' . $pattern . ')';
            if ($index < count($this->ignoreLinkOnAuthorContribution) - 1) {
                $patternIgnoreLinkOnAuthorContribution .= '|';
            }
        }
        $patternIgnoreLinkOnAuthorContribution .= '/i';

        // Récupération de la traduction (si elle existe)
        // Pour Résumé (R) et Article (A)
        // Ajout des données récupérées dans le tableau $numero
        // Le tableau est exploité dans Revues/revue.php (Article) et dans Revues/resume.php (Resumé)
        $onCairn = null;
        $onCairnInt = null;
        if($pageType == "R" ||$pageType == "A") {

            // Version normale
            if(Configuration::get('mode') == 'normal') {
                require_once 'Modele/ManagerIntPub.php';
                $managerIntPub              = new ManagerIntPub('dsn_int_pub');
                $idResumeCairn              = $currentArticle["ARTICLE_ID_ARTICLE"];
                $idsInt                     = $managerIntPub->checkIfArticleOnCairnInt($idResumeCairn);
                $hasResume                  = $managerIntPub->checkIfResumeOnCairnInt($idResumeCairn);
                $articleMetaData            = $managerIntPub->getMetadataArticleOnCairnInt($idResumeCairn);
                //$hasResume                = $hasResume["count"];  //

                // Insertion des données dans le résultat final
                $numero["ID_REVUE_INT"]     = $idsInt["ID_REVUE"]; // Ajout de l'ID de la revue INT dans les données du numéro
                $numero["ID_NUMERO_INT"]    = $idsInt["ID_NUMPUBLIE"]; // Ajout de l'ID du numéro INT dans les données du numéro
                $numero["ID_ARTICLE_INT"]   = $idsInt["ID_ARTICLE"]; // Ajout de l'ID de l'article INT dans les données du numéro
                $numero["URL_REWRITING_INT"]= $idsInt["URL_REWRITING_EN"]; // Ajout de la valeur de l'URL REWRITING

                // Valeur pour l'accès au résumé
                $numero["HAS_RESUME_INT"]   = $hasResume["count"]; // Ajoute le statut du résumé sur INT (0 = Résumé EN vide, 1 = Résumé EN non-vide)
                $numero["HAS_RESUME_ID"]    = $hasResume["ID_ARTICLE"];
                $numero["HAS_RESUME_URL"]   = $hasResume["URL_REWRITING_EN"];
                $numero["META_ARTICLE_INT"] = $articleMetaData; // Ajout des meta-données de l'article CAIRN3 dans les données du numéro
                $onCairn = true;
                $onCairnInt = !!$numero["ID_REVUE_INT"];
                $cairnLang = "fr";
            }
            // Version INT
            if(Configuration::get('mode') == 'cairninter') {
                require_once 'Modele/ManagerCairn3Pub.php';
                $managerCairn3Pub           = new ManagerCairn3Pub('dsn_cairn3_pub');
                $idResumeCairn              = $currentArticle["ARTICLE_ID_ARTICLE_S"]; // ex.: $idNumPublie = RHS_652_0193;
                $idsCairn                   = $managerCairn3Pub->checkIfArticleOnCairn3($idResumeCairn); // Liste des IDs des articles du numéro
                $articleMetaData            = $managerCairn3Pub->getMetadataArticleOnCairn3($idResumeCairn); // Récupération des données de l'article
                $numeroMetaData             = $managerCairn3Pub->getMetadataNumeroOnCairn3($articleMetaData["ID_NUMPUBLIE"]); // Récupération des données du numero

                // Insertion des données dans le résultat final
                $numero["ID_REVUE_CAIRN"]   = $idsCairn["ID_REVUE"]; // Ajout de l'ID de la revue CAIRN3 dans les données du numéro
                $numero["ID_NUMERO_CAIRN"]  = $idsCairn["ID_NUMPUBLIE"]; // Ajout de l'ID du numéro CAIRN3 dans les données du numéro
                $numero["ID_ARTICLE_CAIRN"] = $idsCairn["ID_ARTICLE"]; // Ajout de l'ID de l'article CAIRN3 dans les données du numéro
                $numero["META_ARTICLE_CAIRN"]= $articleMetaData; // Ajout des meta-données de l'article CAIRN3 dans les données du numéro
                $numero["META_NUMERO_CAIRN"]= $numeroMetaData; // Ajout des meta-données du numéro CAIRN3 dans les données du numéro
                $onCairnInt = !!$numero["ID_ARTICLE_CAIRN"];
                $onCairn = true;
                $cairnLang = "en";

                // Récupération des boutons disponibles (ABSTRACT, FRENCH et FULL)
                $typesAchat = Service::get('ControleAchat')->checkAchats($this->authInfos,$revue,$numero);

                // Le service s'attend à recevoir un tableau avec plusieurs articles
                $arrayArticle[] = $currentArticle;
                Service::get('ContentArticle')
                    ->setTypesAchat($typesAchat)
                    ->readButtonsForInter($arrayArticle, $this->authInfos,($accessOk==true?1:0));
            }
        }
        $displayArticleOnCairnOrCairnInt = false;
        // Quand on arrive de certains sites, on doit proposer la langue de l'article
        if($pageType == "R" ||$pageType == "A") {
            // Par sécurité, on n'accepte pas la surcharge du Referer dans les paramètres GET en dehors du mode debug
            if (Configuration::get('allow_backoffice', false)) {
                $httpReferer = $this->requete->getParametre('HTTP_REFERER', '');
            } else {
                $httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            }
            $displayArticleOnCairnOrCairnInt = $onCairn;
            $displayArticleOnCairnOrCairnInt &= $onCairnInt;
            // Pour le moment, on check les referers listés en dur.
            // Si besoin, utiliser des regexps et/ou passer par le fichier de conf
            $displayArticleOnCairnOrCairnInt &= (
                (strpos($httpReferer, 'crossref.org') !== false) ||
                (strpos($httpReferer, 'scopus.com') !== false)
            );
        }

        //Pour la partie webtrends.
        $webtrendsService = Service::get('Webtrends');
        //Recherche des disciplines.
        if (in_array($currentArticle['REVUE_TYPEPUB'], array(3, 6))) {//Pour le cas d'un ouvrage ou d'une encyclopédie de poche.
            $dataDiscipline = $this->content->getDisciplinesOfRevue($currentArticle['NUMERO_ID_NUMPUBLIE']);
        } elseif ($currentArticle['REVUE_TYPEPUB'] == 1) { //Pour les revues.
            $dataDiscipline = $this->content->getDisciplinesOfRevue($currentArticle['REVUE_ID_REVUE']);
        } else {
            $dataDiscipline = array();
        }
        $namePage = $webtrendsService->getLettersToNamePage($displayArticleOnCairnOrCairnInt ? 'C' : $pageType, $currentArticle);
        $webtrendsTags = array();
        $webtrendsTags = array_merge(
            $webtrendsTags,
            $webtrendsService->getTagsForAllPages($namePage, $this->authInfos),
            $webtrendsService->getTagsForArticlePage($currentArticle, $pageType, $currentLicence, $htmlDatas, $dataDiscipline)
        );
        //Pour les consultations à prendre en compte.
        $webtrendsTags['consultation-a-prendre-en-compte'] = 'non';
        if (isset($this->authInfos['I'])) {
            $webtrendsTags['consultation-a-prendre-en-compte'] = $webtrendsService->getTagConsultation($this->authInfos['I'], $currentArticle);
        }
        $headers = $webtrendsService->webtrendsTagsToHeadersTags($webtrendsTags);


        // Ajout des métadonnées pour Google Scholar, qui parse le contenu de cairn pour le diffuser sur leur
        // portail
        $googleScholarTags = [
            'citation_title" lang="'.$cairnLang.'' => $webtrendsTags['article-titre'], // Le tag $googleScholarTags n'est pas censé recevoir un attribut supplémentaire, il faut donc "bidouiller" un peu avec les guillemets
            'citation_language' => Configuration::get('modeBoutons') !== 'cairninter' ? $currentArticle['ARTICLE_LANGUE'] : $currentArticle['ARTICLE_LANGUE_INTEGRALE'],
            'citation_year' => $numero['NUMERO_ANNEE'],
            'citation_issue' => $numero['NUMERO_NUMERO'],
            'citation_firstpage' => $currentArticle['ARTICLE_PAGE_DEBUT'],
            'citation_lastpage' => $currentArticle['ARTICLE_PAGE_FIN'],
            'citation_issn' => $revue['ISSN'],
            'citation_doi' => null,
            'citation_publisher' => $webtrendsTags['editeur-nom'],
            'citation_journal_abbrev' => null,
            'citation_online_date' => $currentArticle['NUMERO_DATE_MISEENLIGNE'],
            // Il faut le titre abgrégé de la revue
            'citation_fulltext_html_url' => 'http://' . trim(Configuration::get('urlSite'), '/') . '/article.php?ID_ARTICLE=' . $currentArticle ['ARTICLE_ID_ARTICLE'],
            'citation_abstract_html_url' => 'http://' . trim(Configuration::get('urlSite'), '/') . '/resume.php?ID_ARTICLE=' . $currentArticle ['ARTICLE_ID_ARTICLE']
        ];

        // Ajout du résumé
        $currentArticle["RESUMES"] = $this->content->getResumesArticle($currentArticle ["ARTICLE_ID_ARTICLE"]);
        if($currentArticle["RESUMES"]) {
            // Tableau des langues
            $langArray = array("fr", "en", "de", "it", "es", "pt");

            // Boucle
            foreach($langArray as $lang) {
                // Le tag $googleScholarTags n'est pas censé recevoir un attribut supplémentaire, il faut donc "bidouiller" un peu avec les guillemets
                if($currentArticle["RESUMES"][0]["RESUME_".strtoupper($lang)]) {
                    // Récupération du résumé
                    $titre  = "";
                    $resume = strip_tags($currentArticle["RESUMES"][0]["RESUME_".strtoupper($lang)], "<h3><p><b><i><sup><sub><titre>"); // On conserve un certain nombre de tag (dont le titre pour pouvoir l'isoler)

                    // Isolation du titre (si existe)
                    preg_match('|<titre>(.*)</titre>|', $resume, $titreArray[$lang]);

                    // Récupération des titres (sauf FR)
                    if($titreArray[$lang][0] != "" && $lang != $cairnLang) {
                        $resume = str_replace($titreArray[$lang][0], "", $resume);   // Suppression du titre dans le résumé
                        $titre  = strip_tags($titreArray[$lang][0]);                 // Définition du titre seul
                    }

                    // Affichage du titre et du résumé
                    if($titre != "" && $lang != $cairnLang) {$googleScholarTags["citation_title\" lang=\"".$lang] = htmlentities($titre);}
                    $googleScholarTags["citation_abstract\" lang=\"".$lang] = htmlentities($resume);
                }
            }
        }

        // On fait apparaitre le titre du bouquin pour les ouvrages collectifs
        // Et pour les autres types de contenus, on fait apparaitre le titre de la collection
        if ($currentArticle['REVUE_TYPEPUB'] == 3 && $numero['NUMERO_TYPE_NUMPUBLIE'] == 1) {
            $googleScholarTags['citation_inbook_title'] = $webtrendsTags['numero-titre'];
        } else {
            $googleScholarTags['citation_journal_title'] = $webtrendsTags['revue-titre'];
        }
        // On fait apparaitre l'isbn papier pour les ouvrages ET les encyclopédies
        if (in_array($currentArticle['REVUE_TYPEPUB'], [3, 6])) {
            $googleScholarTags['citation_isbn'] = $numero['NUMERO_ISBN'];
        }


        // Les volumes des numéros contiennent des données parasites à supprimer (il faut juste le volume
        // brut)
        $googleScholarTags['citation_volume'] = Service::get('ParseDatas')->cleanString($numero['NUMERO_VOLUME']);
        // On réutilise les traitement effectués dans le taggage webtrends
        if (isset($webtrendsTags['article-auteurs'])) {
            $googleScholarTags ['citation_author'] = explode(';', $webtrendsTags['article-auteurs']);
        }
        if (isset($webtrendsTags ['article-mot_cle'])) {
            $googleScholarTags ['article-mot_cle'] = $webtrendsTags ['article-mot_cle'];
        }
        // Spécifique aux revues, les ouvrages ont un feuilletage
        if ($typePub === 'revue') {
            $googleScholarTags ['citation_pdf_url'] = 'http://' . trim(Configuration::get('urlSite'), '/') . '/load_pdf.php?ID_ARTICLE=' . $currentArticle ['ARTICLE_ID_ARTICLE'];
        }
        // On précise si l'article est disponible en accès libre
        // Pour cela, on réutilise les informations récupérés pour webtrends
        // Comme les valeurs de subcategory peuvent changer dans le futur, on préfère envoyer une référence
        if (($pageType === 'A') && in_array($webtrendsTags['article-type_commercialisation'], [
            $webtrendsService->mappingDescPages['article-post-movingwall']['article-type_commercialisation'],
            $webtrendsService->mappingDescPages['article-gratuit']['article-type_commercialisation'],
        ])) {
            $googleScholarTags['citation_fulltext_world_readable'] = ' ';
        }

        // On transforme les tags en forme normalisés pour le $headers
        array_push($headers, [
            'tagname' => '!COMMENT',
            'content' => 'GS metadata starts here'
        ]);
        foreach ($googleScholarTags as $key => $value) {
            if (!$value) {
                continue;
            }
            // Le foreach est un dirty-fix pour la demande #87605
            // Cela permet d'avoir un tag qui apparait plusieurs fois
            foreach (is_array($value) ? $value : [$value] as $subValue) {
                $subValue = trim($subValue);
                array_push($headers, array ('tagname' => 'meta','attributes' => [ array ('name' => 'name','value' => $key ),array ('name' => 'content','value' => $subValue ) ] ));
            }
        }

        if((isset($this->authInfos['I']) && $this->authInfos['I']['ID_USER'] == 'google_scholar')) { //métadonnées présente uniquement pour google_scholar
            $refBiblios = $this->getRefBiblioFromHTML($numero['NUMERO_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], $currentArticle ['ARTICLE_ID_ARTICLE']);
            foreach($refBiblios as $refBiblio) {
                array_push($headers, array ('tagname' => 'meta','attributes' => [ array ('name' => 'name','value' => 'citation_reference' ),array ('name' => 'content','value' => $refBiblio ) ] ));
            }
        }

        array_push($headers, [
            'tagname' => '!COMMENT',
            'content' => 'GS metadata ends here'
        ]);

        // On insère les métadonnées dans le document pour les exploiter avec Javascript
        if (isset($this->mappingTypePage[$pageType])) {
            $cairnMetadatas = [
                'page' => $this->mappingTypePage[$pageType],
                'revue' => [
                    'id_revue' => $revue['REVUE_ID_REVUE'],
                    'titre' => $revue['REVUE_TITRE'],
                ],
                'numero' => [
                    'id_numpublie' => $numero['NUMERO_ID_NUMPUBLIE'],
                    'titre' => $numero['NUMERO_TITRE'],
                ],
                'article' => [
                    'id_article' => $currentArticle['ARTICLE_ID_ARTICLE'],
                    'titre' => $currentArticle['ARTICLE_TITRE'],
                    'com' => [
                        'type' => $webtrendsTags['article-type_commercialisation'],
                    ]
                ]
            ];
            array_push($headers, [
                'tagname' => 'script',
                'content' => "window.cairnPageMetadatas = " . json_encode($cairnMetadatas),
            ]);
        }

        // Voir la méthode $this->statLogCrossValidation
        if (isset($idLog)) {
            if (!!$idLog) {
                array_push($headers, [
                    'tagname' => 'meta',
                    'attributes' => [
                        ['name' => 'name', 'value' => 'id-cross-log'],
                        ['name' => 'content', 'value' => $idLog,],
                    ],
                ]);
            }
        }
        // Le nombre de caractère en texte intégral.
        $typesAchat['countCharactersFullText'] = null;

        // On vérifie qu'il n'y a pas eu de suggestion pour l'institution sur cet ouvrage (#98362 - uniquement pour les ouvrages)
        $hasAlreadySubmitSuggestionForInstitution = null;
        if (isset($this->authInfos['I']['ID_USER']) && ($typePublication == 3)) {
            if ($hasAccessToNumero) {
                $hasAlreadySubmitSuggestionForInstitution = true;
            } else {
                $idInstitution = $this->authInfos['I']['ID_USER'];
                $idSession = $this->authInfos['G']['TOKEN'];
                $idUser = isset($this->authInfos['U']['ID_USER']) ? $this->authInfos['U']['ID_USER'] : null;
                $hasAlreadySubmitSuggestionForInstitution = $this->contentCom->hasAlreadySubmitSuggestionForInstitution(
                    $idInstitution,
                    $idSession,
                    $idUser,
                    $numero['NUMERO_ID_NUMPUBLIE']
                );
            }
        }

        switch ($pageType) {
            case 'Z' :
                $auteurs = null;
                if (($typePublication == 3 || $typePublication == 6) && $numero ['NUMERO_TYPE_NUMPUBLIE'] != 1) {
                    $auteurs = $this->content->getAuteursNum($numero ['NUMERO_ID_NUMPUBLIE']);
                }
                $this->genererVue(array ('revue' => $revue,'numero' => $numero,'currentArticle' => $currentArticle,'htmlDatas' => $htmlDatas,'typePub' => $typePub,'auteurs' => $auteurs,'currentLicence' => $currentLicence,'pageType' => $pageType ), 'zen.php', 'gabaritZen.php', $headers);
                break;
            case 'R' :
                $numero ['NUMERO_AUTEUR'] = $this->content->getAuteursNum($numero ['NUMERO_ID_NUMPUBLIE']);
                $modeBoutons = Configuration::get('modeBoutons');
                $typesAchat = null;
                if ($modeBoutons == 'cairninter') {
                    Service::get('ContentArticle')->readButtonsForInter($articles, $this->authInfos, ($hasAccess == true ? 1 : 0), $currentArticle);
                } else {
                    if (!$hasAccess) {
                        $typesAchat = Service::get('ControleAchat')->checkAchats($this->authInfos, $revue, $numero, $currentArticle);
                        if ($typePub === 'magazine') {
                            // On récupère le nombre de caractère en texte intégral (en supprimant les balises)
                            // On les affiche dans le bloc d'achat d'un article
                            $path = $revue["REVUE_ID_REVUE"]
                                .'/'.$revue["NUMERO_ID_NUMPUBLIE"]
                                .'/'.$currentArticle["ARTICLE_ID_ARTICLE"]
                                .'/'.Configuration::get('filePrefixTypeA')
                                .'_'.$currentArticle["ARTICLE_ID_ARTICLE"]
                                .'.htm';
                            if ($this->html->loadFile($path, 'HTML')) {
                                $htmlFullTextData = $this->html->getContent();
                                $typesAchat['countCharactersFullText'] = mb_strlen(strip_tags($htmlFullTextData['CONTENUS'])) + 3000;
                            }
                        }
                    }
                }
                $template = 'resume.php';
                $this->genererVue(array ('revue' => $revue,'numero' => $numero,'articles' => $articles,'currentArticle' => $currentArticle,'htmlDatas' => $htmlDatas,'typePub' => $typePub,'typesAchat' => $typesAchat,'hasAccess' => $hasAccess,'hasAccessToNumero' => $hasAccessToNumero,'curDiscipline' => $curDiscipline,'filterDiscipline' => $filterDiscipline,'currentLicence' => $currentLicence,'pageType' => $pageType, 'patternIgnoreLinkOnAuthorContribution' => $patternIgnoreLinkOnAuthorContribution, 'displayArticleOnCairnOrCairnInt' => $displayArticleOnCairnOrCairnInt, 'hasAlreadySubmitSuggestionForInstitution' => $hasAlreadySubmitSuggestionForInstitution), 'resume.php', null, $headers);
                break;
            case 'PR' :
            case 'PA' :
                $ipClient = Service::get('AuthentificationIP')->getIpClient($this->requete);
                $this->genererVue(array ('revue' => $revue,'numero' => $numero,'articles' => $articles,'currentArticle' => $currentArticle,'htmlDatas' => $htmlDatas,'pageType' => $pageType,'ipClient' => $ipClient,'currentLicence' => $currentLicence,'pageType' => $pageType ), 'print.php', 'none', $headers);
                break;
            case 'PDF' :
                $mode = Configuration::get('mode');
                $numero ['NUMERO_AUTEUR'] = $this->content->getAuteursNum($numero ['NUMERO_ID_NUMPUBLIE']);
                $articleAuthors = $this->parseRawAuthors($currentArticle ['ARTICLE_AUTEUR'], $mode);

                $numeroAuthors = $this->parseRawAuthors($numero ['NUMERO_AUTEUR'], $mode);
                $articleAuthorsCitation = $this->parseRawAuthorsCitation($currentArticle ['ARTICLE_AUTEUR'], $mode);
                $numeroAuthorsCitation = $this->parseRawAuthorsCitation($numero ['NUMERO_AUTEUR'], $mode);
                $citation = $this->parseCitation($currentArticle, $numero, $revue, $articleAuthorsCitation, $numeroAuthorsCitation, $articleAuthors, $mode);
                $parseTranslation = $this->parseTranslation($currentArticle, $numero, $revue, $articleAuthorsCitation, $numeroAuthorsCitation, $articleAuthors, $mode);

                //Accès liste revues cleo à partir du fichier de config
                $isCleo = $this->isCleoByConfig($currentArticle['REVUE_ID_REVUE'],$mode);
                $sujetProche = Service::get('Recherche')->getSujetProche($currentArticle["ARTICLE_ID_ARTICLE"]);

                if ($this->requete->existeParametre('act') == true || (isset($this->authInfos['I']) && $this->authInfos['I']['ID_USER'] == 'google_scholar')  || preg_match('/^http(s)?:\/\/'.str_replace("/", "\/", Configuration::get('urlSite')).'/i', $_SERVER['HTTP_REFERER']) == true) {
                    $this->genererVue(array ('parsetranslation' => $parseTranslation,'parsecitation' => $citation,'articleAuthors' => $articleAuthors,'numeroAuthors' => $numeroAuthors,'articleAuthorsCitation' => $articleAuthorsCitation,'numeroAuthorsCitation' => $numeroAuthorsCitation,'revue' => $revue,'numero' => $numero,'articles' => $articles,'currentArticle' => $currentArticle,'pageType' => $pageType,'prefix_path' => Configuration::get('prefixPath'),'cairn_includes_path' => Configuration::get('cairn_includes_path'),'mode' => $mode,'currentLicence' => $currentLicence,'pageType' => $pageType,'iscleo'=>$isCleo ), 'pdf.php', 'none', $headers);
                } else {
                    $this->genererVue([
                        'revue' => $revue,
                        'numero' => $numero,
                        'articles' => $articles,
                        'currentArticle' => $currentArticle,
                        'typePub' => $typePub,
                        'curDiscipline' => $curDiscipline,
                        'filterDiscipline' => $filterDiscipline,
                        'sujetProche' => $sujetProche
                    ], 'landing_pdf.php', 'gabarit.php', $headers);
                    break;
                }

                break;
            case 'EXTRAWEB' :
                $this->genererVue(array ('revue' => $revue,'numero' => $numero,'articles' => $articles,'currentArticle' => $currentArticle,'pageType' => $pageType,'prefix_path' => Configuration::get('prefixPath'),'cairn_includes_path' => Configuration::get('cairn_includes_path'),'currentLicence' => $currentLicence,'pageType' => $pageType ), 'extrawebpdf.php', 'none', $headers);
                break;
            case 'FEUILA' :
            case 'FEUILC' :
                $this->genererVue(array ('revue' => $revue,'numero' => $numero,'articles' => $articles,'currentArticle' => $currentArticle,'pageType' => $pageType,'htmlDatas' => $htmlDatas,'hits' => $hits_local,'currentLicence' => $currentLicence,'pageType' => $pageType ), 'feuilleter.php', 'gabaritZen.php', $headers);
                break;
            case 'PORTAIL' :
                $this->genererVue(array ('currentArticle' => $currentArticle,'currentLicence' => $currentLicence,'pageType' => $pageType ), 'portail.php', 'gabaritAjax.php');
                break;
            default :
                $modeBoutons = Configuration::get('modeBoutons');
                if ($modeBoutons == 'cairninter') {
                    Service::get('ContentArticle')->setTypesAchat($typesAchat)->readButtonsForInter($articles, $this->authInfos, ($hasAccess == true ? 1 : 0), $currentArticle);
                }
                $this->genererVue(array ('revue' => $revue,'numero' => $numero,'articles' => $articles,'currentArticle' => $currentArticle,'htmlDatas' => $htmlDatas,'typePub' => $typePub,'countReferencedBy' => $countReferencedBy,'hits' => $hits,'curDiscipline' => $curDiscipline,'filterDiscipline' => $filterDiscipline,'currentLicence' => $currentLicence,'pageType' => $pageType,  'patternIgnoreLinkOnAuthorContribution' => $patternIgnoreLinkOnAuthorContribution, 'displayArticleOnCairnOrCairnInt' => $displayArticleOnCairnOrCairnInt, 'hasAccess' => $hasAccess, 'hasAccessToNumero' => $hasAccessToNumero, 'hasAlreadySubmitSuggestionForInstitution' => $hasAlreadySubmitSuggestionForInstitution), 'revue.php', null, $headers);
        }
    }
    private function parseCitation($currentArticle, $numero, $revue, $articleAuthorsCitation, $numeroAuthorsCitation, $articleAuthors, $mode)
    {
        if ('normal' == $mode) {
            $join = "et";
        } else {

            $join = "and";
        }
        $typeOuvrage = 'revue';
        if ($numero ['REVUE_TYPEPUB'] == '3' || $numero ['REVUE_TYPEPUB'] == '6') {
            $typeOuvrage = ($numero ['NUMERO_TYPE_NUMPUBLIE'] == '0') ? 'monographie' : 'collectif';
        }
        $tabPoint = array ('?','.','!' );
        $point = " ";
        $currentArticle ['ARTICLE_TITRE'] = trim($currentArticle ['ARTICLE_TITRE']);
        $currentArticle ['ARTICLE_SOUSTITRE'] = trim($currentArticle ['ARTICLE_SOUSTITRE']);
        $currentArticle ['ARTICLE_MOTS_CLES_PT'] = trim($currentArticle ['ARTICLE_MOTS_CLES_PT']);
        /**
         * Vérifier si le titre ne se ne termine pas par une ponctuation
         */
        if (!in_array(substr($currentArticle ['ARTICLE_TITRE'], -1), $tabPoint)) {
            if (!empty($currentArticle ['ARTICLE_SOUSTITRE'])) {
                $point = ". ";
            }
        }
        $parseAutNumeroColl = $numero ['NUMERO_AUTEUR'] [0] ['AUTEUR_PRENOM'] . ' ' . $numero ['NUMERO_AUTEUR'] [0] ['AUTEUR_NOM'];
        // if ($numero['NUMERO_AUTEUR'][0])
        if (count($numero ['NUMERO_AUTEUR']) > 1) {
            $parseAutNumeroColl .= " <i> et al.</i>";
        }
        /**
         * Mettre des quillemets que si y'en a pas a la fin du titre
         */
        $leftb = '«&#160;';
        $rightb = '&#160;»';
        $html = '';
        if ($articleAuthorsCitation) {
            $html .= $articleAuthorsCitation.', ';
        }
        if ('cairninter' == $mode && $this->is_translated($currentArticle)) {

            if (substr(html_entity_decode($currentArticle ['ARTICLE_MOTS_CLES_PT']), -1) == utf8_decode(html_entity_decode("»"))) {
                $leftb = '';
                $rightb = '';
            }
            $html .= $leftb . $currentArticle ['ARTICLE_MOTS_CLES_PT'] . $rightb . ", ";
        } else {
            if (substr(html_entity_decode($currentArticle ['ARTICLE_TITRE']), -1) == utf8_decode(html_entity_decode("»"))) {
                $leftb = '';
                $rightb = '';
            }
            $html .= $leftb . $currentArticle ['ARTICLE_TITRE'] . $point . $currentArticle ['ARTICLE_SOUSTITRE'] . $rightb . ", ";
        }

        if ($typeOuvrage == 'revue') {
            $html .= "<i>" . $revue ['REVUE_TITRE'] . "</i> " . $numero ['NUMERO_ANNEE'] . '/' . $numero ['NUMERO_NUMERO'] . ' (' . $numero ['NUMERO_VOLUME'] . ')';
        } else {
            $in = '';
            if ($typeOuvrage == 'collectif') {
                $in = '<i> in </i>' . $parseAutNumeroColl . ", ";
            }
            $html .= $in . '<i>' . $numero ['NUMERO_TITRE'] . '</i>, ' . $currentArticle ['EDITEUR_NOM_EDITEUR'] . ' «&#160;' . $revue ['REVUE_TITRE'] . '&#160;», ' . $numero ['NUMERO_ANNEE'] . ' (' . $numero ['NUMERO_VOLUME'] . ')';
        }
        $html .= ', p.&#160;' . $currentArticle ['ARTICLE_PAGE_DEBUT'] . '-' . $currentArticle ['ARTICLE_PAGE_FIN'];
        $html .= '.';
        if (!!trim($currentArticle ['ARTICLE_DOI']) && !is_null($currentArticle ['ARTICLE_DOI'])) {
            $html .= '<br />DOI ' . $currentArticle ['ARTICLE_DOI'];
        }

        return $html;
    }
    private function isCleoByConfig($idrevue,$mode){
        $string = Configuration::get('revuescleo');
        $revuesPortail = split(',', $string);
        if ('normal' == $mode) {
            if(in_array($idrevue, $revuesPortail)){
                return true;
            }

        } else {
            //cairninter : pour l'instant cela ne concerne que cairn et pas cairn inter, toutes les revues sont en interne
            return false;
        }
        return false;
    }

    private function isCleo($idrevue,$mode){
        $revuesPortail =  $this->content->getRevuesPortail('cleo');
        $revuesPortail[] = 'CLIO1';
        if ('normal' == $mode) {
            if(in_array($idrevue, $revuesPortail)){
                return true;
            }

        } else {
            //cairninter : pour l'instant cela ne concerne que cairn et pas cairn inter, toutes les revues sont en interne
            return false;
        }
        return false;
    }
    private function parseTranslation($currentArticle, $numero, $revue, $articleAuthorsCitation, $numeroAuthorsCitation, $articleAuthors, $mode)
    {
        if ('normal' == $mode) {
            $join = "et";
        } else {

            $join = "and";
        }
        $typeOuvrage = 'revue';
        if ($numero ['REVUE_TYPEPUB'] == '3' || $numero ['REVUE_TYPEPUB'] == '6') {
            $typeOuvrage = ($numero ['NUMERO_TYPE_NUMPUBLIE'] == '0') ? 'monographie' : 'collectif';
        }
        $tabPoint = array ('?','.','!' );
        $point = " ";
        $currentArticle ['ARTICLE_TITRE'] = trim($currentArticle ['ARTICLE_TITRE']);
        $currentArticle ['ARTICLE_SOUSTITRE'] = trim($currentArticle ['ARTICLE_SOUSTITRE']);
        /**
         * Vérifier si le titre ne se ne termine pas par une ponctuation
         */
        if (!in_array(substr($currentArticle ['ARTICLE_TITRE'], -1), $tabPoint)) {
            if (!empty($currentArticle ['ARTICLE_SOUSTITRE'])) {
                $point = ". ";
            }
        }
        $parseAutNumeroColl = $numero ['NUMERO_AUTEUR'] [0] ['AUTEUR_PRENOM'] . ' ' . $numero ['NUMERO_AUTEUR'] [0] ['AUTEUR_NOM'];
        if (count($numero ['NUMERO_AUTEUR']) > 1) {
            $parseAutNumeroColl .= " <i> et al.</i>";
        }
        $leftb = '«&#160;';
        $rightb = '&#160;»';

        if ('cairninter' == $mode && $this->is_translated($currentArticle)) {
            if (substr(html_entity_decode($currentArticle ['ARTICLE_MOTS_CLES_PT']), -1) == utf8_decode(html_entity_decode("»"))) {
                $leftb = '';
                $rightb = '';
            }
            $articleAuthors = $this->parseTranslators($currentArticle ['ARTICLE_AUTEUR']);

            $j = '';
            if (!empty($articleAuthors)) {
                $j = ', ';
            }
            $autcitation = $articleAuthorsCitation;
            // on affiche : sil ya deux auteurs on les affiche, un auteur et un traducteur, on affiche s'il ya
            // en tout plus de 2 auteurs (auteurs et/ ou auteur traducteur) on met et al

            if (preg_match('/et al/i', $autcitation)) {
                $autcitation = $autcitation;
            } else {
                if (!empty($articleAuthors)) {
                    $arr = explode(",", $articleAuthors);
                    if (count($arr) == 1) {
                        $autcitation .= " and " . $arr [0];
                    } else {
                        $autcitation .= " <i>et al.</i>";
                    }
                }

            }
            $html = $autcitation . ", " . $leftb . $currentArticle ['ARTICLE_MOTS_CLES_PT'] . $rightb . ", ";
            // $html = $articleAuthorsCitation.$j.$articleAuthors . ", " . $leftb . $currentArticle
        // ['ARTICLE_MOTS_CLES_PT'] . $rightb . ", ";
            // $html = $articleAuthors . ", " . $leftb . $currentArticle ['ARTICLE_MOTS_CLES_PT'] . $rightb .
        // ", ";
        } else {
            if (substr(html_entity_decode($currentArticle ['ARTICLE_TITRE']), -1) == utf8_decode(html_entity_decode("»"))) {

                $leftb = '';
                $rightb = '';
            }

            $html = $articleAuthors . ", " . $leftb . $currentArticle ['ARTICLE_TITRE'] . $point . $currentArticle ['ARTICLE_SOUSTITRE'] . $rightb . ", ";
        }

        if ($typeOuvrage == 'revue') {
            $html .= "<i>" . $revue ['REVUE_TITRE'] . "</i> " . $numero ['NUMERO_ANNEE'] . '/' . $numero ['NUMERO_NUMERO'] . ' (' . $numero ['NUMERO_VOLUME'] . ')';
        } else {
            $in = '';
            if ($typeOuvrage == 'collectif') {
                $in = '<i> in </i>' . $parseAutNumeroColl . ", ";
            }
            $html .= $in . '<i>' . $numero ['NUMERO_TITRE'] . '</i>, ' . $currentArticle ['EDITEUR_NOM_EDITEUR'] . ' «&#160;' . $revue ['REVUE_TITRE'] . '&#160;», ' . $numero ['NUMERO_ANNEE'] . ' (' . $numero ['NUMERO_VOLUME'] . ')';
        }
        $html .= ', p.&#160;' . $currentArticle ['ARTICLE_PAGE_DEBUT'] . '-' . $currentArticle ['ARTICLE_PAGE_FIN'];
        $html .= '.';

        if (!!trim($currentArticle ['ARTICLE_DOI']) && !is_null($currentArticle ['ARTICLE_DOI'])) {
            $html .= '<br />DOI ' . $currentArticle ['ARTICLE_DOI'];
        }

        return $html;
    }
    private function remove_tag($string)
    {
        $pattern = '/<\/?\w+[^>]+>/i';
        return preg_replace($pattern, '', $string);
    }
    private function is_translated($currentArticle)
    {
        $currentArticle ['ARTICLE_TITRE_FR'] = $this->remove_tag($currentArticle ['ARTICLE_MOTS_CLES_PT']);

        // Si l'article est en français sur cairn.info et a été traduit pour cairn-int.info
        $is_translated = ($currentArticle ['ARTICLE_LANGUE_INTEGRALE'] == 'en' && ($currentArticle ['ARTICLE_LANGUE'] == 'fr' || $currentArticle ['ARTICLE_LANGUE'] == '') && $currentArticle ['ARTICLE_ID_ARTICLE_S'] && $currentArticle ['ARTICLE_TITRE_FR']);
        if ($is_translated) {
            return true;
        }
        return false;
    }
    /**
     * Liste des auteurs
     *
     * @param unknown $rawAuthors
     * @param unknown $mode
     * @return string Ambigous multitype:string >
     * @access : public
     * @version : 28 avr. 2015
     * @author : ibrahima
     */
    private function parseRawAuthors($rawAuthors, $mode)
    {
        if (empty($rawAuthors)) { return ''; }
        // $join = ($mode === 'normal') ? 'et' : 'and';
        $join = ', ';
        $authors = array();
        $rawAuthors = is_string($rawAuthors) ? explode(',', $rawAuthors) : $rawAuthors;

        foreach ($rawAuthors as $index => $author) {
            if (preg_match('/translated/i', $author)) {
                continue;
            }
            if (is_string($author)) {
                $author = explode(':', $author);
                $author = [
                    'AUTEUR_ATTRIBUT' => $author[3],
                    'AUTEUR_PRENOM' => $author[0],
                    'AUTEUR_NOM' => $author[1]
                ];
            }
            $authors[] = trim(implode(' ', [
                trim($index < 1 ? $author ['AUTEUR_ATTRIBUT'] : lcfirst($author['AUTEUR_ATTRIBUT'])),
                trim($author ['AUTEUR_PRENOM']),
                trim($author ['AUTEUR_NOM'])
            ]));
        }

        switch (count($authors)) {
            case 0 :
            case 1 :
                $authors = implode($authors);
                break;
            case 2 :
                $authors = implode($join, $authors);
                break;
            default :
                $authors = implode(', ', array_slice($authors, 0, -1)) . "$join" . end($authors);
        }
        $translators = $this->parseTranslators($rawAuthors);
        if (!empty($translators)) {
            $authors .= "<br/><i>" . $translators . "</i>";
        }
        return $authors;
    }
    /**
     * translators : type string
     *
     * @param unknown $rawAuthors
     * @return string Ambigous multitype:string >
     * @access : public
     * @version : 28 avr. 2015
     * @author : ibrahima
     */
    private function parseTranslators($rawAuthors)
    {
        if ('normal' == $mode) {
            $join = "et";
        } else {

            $join = "and";
        }
        $authors = array ();
        if (empty($rawAuthors))
            return '';
        $rawAuthors = is_string($rawAuthors) ? explode(',', $rawAuthors) : $rawAuthors;

        foreach ($rawAuthors as $author) {
            if (preg_match('/translated/i', $author)) {
                if (is_string($author)) {
                    $author = explode(':', $author);
                    $authors [] = $author [3] . ' ' . implode(' ', array_slice($author, 0, 2));
                } else {
                    $authors [] = $author ['AUTEUR_ATTRIBUT'] . ' ' . $author ['AUTEUR_PRENOM'] . '' . $author ['AUTEUR_NOM'];
                }
            }
        }

        switch (count($authors)) {
            case 0 :
            case 1 :
                $authors = implode($authors);
                break;
            case 2 :
                $authors = implode(" $join ", $authors);
                break;
            default :
                $authors = implode(', ', array_slice($authors, 0, -1)) . " $join " . end($authors);
        }
        return $authors;
    }
    /**
     * Liste des auteurs en citations
     *
     * @param unknown $rawAuthors
     * @param unknown $mode
     * @return string Ambigous multitype:string >
     * @access : public
     * @version : 28 avr. 2015
     * @author : ibrahima
     */
    private function parseRawAuthorsCitation($rawAuthors, $mode)
    {
        if ('normal' == $mode) {
            $join = ", ";
        } else {

            $join = ", ";
        }
        $authors = array ();
        if (empty($rawAuthors))
            return '';
        $rawAuthors = is_string($rawAuthors) ? explode(',', $rawAuthors) : $rawAuthors;

        foreach ($rawAuthors as $author) {
            if (preg_match('/translated/i', $author)) {
                continue;
            }
            if (is_string($author)) {
                $author = explode(':', $author);
                $authors [] = trim((isset($author[3]) ? trim($author[3]) . ' ' : '') . implode(' ', array_slice($author, 0, 2)));
            } else {
                $authors [] = $author ['AUTEUR_PRENOM'] . ' ' . $author ['AUTEUR_NOM'];
            }
        }

        switch (count($authors)) {
            case 0 :
            case 1 :
                $authors = implode($authors);
                break;
            case 2 :
                $authors = implode($join, $authors);
                break;
            default :
                $authors = $authors [0] . " <i>et al.</i>";
        }
        return $authors;
    }
    private function parseDocumentTranslation()
    {
        return 1;
    }
    private function loadXml()
    {
        $article = $this->content->getArticleFromId($this->requete->getParametre("ID_ARTICLE"));

        $hasAccess = Service::get('ControleAchat')->hasAccessToArticle($this->authInfos, $article);
        $onlyFirstPage = null;
        if (!$hasAccess) {
            $onlyFirstPage = 1;
        }
        $fileFullPath = Configuration::get('prefixPath') . '/' . $article ['NUMERO_ID_REVUE'] . "/" . $article ['NUMERO_ID_NUMPUBLIE'] . '/' . $article ['ARTICLE_ID_ARTICLE'];
        $this->genererVue(array ('fileFullPath' => $fileFullPath,'currentArticle' => $article,'onlyFirstPage' => $onlyFirstPage ), 'xml.php', 'none');
    }
    private function loadEpub()
    {
        $numero = $this->content->getNumpublieById($this->requete->getParametre("ID_NUMPUBLIE"))[0];

        $hasAccess = Service::get('ControleAchat')->hasAccessToNumero($this->authInfos, $numero, null, 0, 0, 'E');
        if ($hasAccess) {

            $fileFullPath = '/' . $numero ['NUMERO_ID_REVUE'] . "/" . $numero ['NUMERO_ID_NUMPUBLIE'] . '/' . $numero ['NUMERO_ID_NUMPUBLIE'] . '.epub';

            $epub = $this->file->loadFile($fileFullPath, 'EPUB');

            $this->genererVue(array ('datas' => $epub,'contentType' => 'application/epub+zip','fileName' => $numero ['NUMERO_ID_NUMPUBLIE'] . '.epub' ), 'file.php', 'none');
        }
    }
    private function loadSwf()
    {
        $article = $this->content->getArticleFromId($this->requete->getParametre("ID_ARTICLE"));
        $pageSwf = $this->requete->getParametre("PAGE");

        if ($this->file->loadFile($article ["ARTICLE_ID_REVUE"] . '/' . $article ["ARTICLE_ID_NUMPUBLIE"] . '/' . $article ["ARTICLE_ID_ARTICLE"] . '/' . $pageSwf, 'SWF')) {
            $datas = $this->file->getContent();
        }
        $this->genererVue(array ('datas' => $datas, 'contentType' => 'application/x-shockwave-flash' ), 'file.php', 'none');
    }
    private function loadImg()
    {
        $file = $this->requete->getParametre("FILE");
        $parts = explode('.', $file);
        $ext = $parts [count($parts) - 1];
        // echo $file."///".$ext;
        if ($this->file->loadFile($file, 'IMG')) {
            $datas = $this->file->getContent();
        }
        $this->genererVue(array ('contentType' => ('image/' . $ext),'datas' => $datas ), 'file.php', 'none');
    }
    private function applyDOI(&$htmlDatas, $id_article, $type = null)
    {
        // Type B = BIBLIO / N = NOTE / T = TEXTE
        $refs = $this->content->getReferences($id_article);
        foreach ($refs as $row) {
            $num = $row ['NOREF'];
            $type = $row ['TYPEF'];
            if ($type == 'B') {
                $FINDTXT = '<span class="reference" name="' . (strpos($num, 'rb') === FALSE ? ('rb' . $num) : $num) . '"><img src="./img/trefle_3.png" alt="" /></span>';
                // } else if($type == 'P' || $type == 'T') {
                // $FINDTXT = '<a class="no_para" href="#'.$num.'" ';
            } else {
                $FINDTXT = '<a id="' . (strpos($num, 'no') === FALSE ? 'no' : '') . $num . '" class';
            }
            if ($row ['ID_CAIRN_NUM'] == '' && isset($this->authInfos ['I']) && $this->authInfos ['I'] ['HISTORIQUE'] != '') {
                $URL = $this->authInfos ['I'] ['HISTORIQUE'];
                $URL = str_replace('[ID_ARTICLE]', $id_article, $URL);
                $URL = str_replace('[NOREF]', $num, $URL);
                $URL = str_replace('[TYPEREF]', $type, $URL);
                $URL = str_replace('[ID_INST]', $this->authInfos ['I'] ['ID_USER'], $URL);
                if ($type == 'B') {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<span class="reference" name="' . $num . '"><a href="' . $URL . '"  alt=" " ><img src="./img/' . $this->authInfos ['I'] ['ID_USER'] . '.png" alt="' . $this->authInfos ['I'] ['NOM'] . '"/></a></span>', $htmlDatas ["CONTENUS"]);
                } else {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<div class="refnote ref-enligne"><a href="' . $URL . '"><img src="./img/' . $this->authInfos ['I'] ['ID_USER'] . '.png" alt="' . $this->authInfos ['I'] ['NOM'] . '" /></a></div>' . $FINDTXT, $htmlDatas ["CONTENUS"]);
                }
            } else if ($row ['ID_CAIRN_NUM'] != '') {
                $URL = 'numero.php?ID_NUMPUBLIE=' . $row ['ID_CAIRN_NUM'];
                if ($type == 'B') {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<span class="reference" name="' . $num . '"><a href="' . $URL . '"  alt=" " ><img src="./img/enligneCairn.png" alt=""/></a></span>', $htmlDatas ["CONTENUS"]);
                } else {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<div class="refnote ref-enligne"><a href="' . $URL . '"><img src="./img/enligneCairn.png" alt="" /></a></div>' . $FINDTXT, $htmlDatas ["CONTENUS"]);
                }
            } else if ($row ['ID_CAIRN_CIBLE'] != '') {
                $URL = 'article.php?ID_ARTICLE=' . $row ['ID_CAIRN_CIBLE'];
                if ($type == 'B') {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<span class="reference" name="' . $num . '"><a href="' . $URL . '"  alt=" " ><img src="./img/enligneCairn.png" alt=""/></a></span>', $htmlDatas ["CONTENUS"]);
                } else {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<div class="refnote ref-enligne"><a href="' . $URL . '"><img src="./img/enligneCairn.png" alt="" /></a></div>' . $FINDTXT, $htmlDatas ["CONTENUS"]);
                }
            } elseif ($row ['DOI_CIBLE'] != '') {
                if ($type == 'B') {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<span class="reference" name="' . $num . '"><a href="' . $row ['DOI_CIBLE'] . '"  alt=" " ><img src="./img/enligne.png" alt=""/></a></span>', $htmlDatas ["CONTENUS"]);
                } else {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<div class="refnote ref-enligne"><a href="' . $row ['DOI_CIBLE'] . '"><img src="./img/enligne.png" alt="" /></a></div>' . $FINDTXT, $htmlDatas ["CONTENUS"]);
                }
            } elseif ($row ['URL_CIBLE'] != '') {
                if ($type == 'B') {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<span class="reference" name="' . $num . '"><a href="' . $row ['URL_CIBLE'] . '"  alt=" " target="_home"><img src="./img/enligne.png" alt=""/></a></span>', $htmlDatas ["CONTENUS"]);
                } else {
                    $htmlDatas ["CONTENUS"] = str_replace($FINDTXT, '<div class="refnote ref-enligne"><a href="' . $row ['URL_CIBLE'] . '" target="_home"><img src="./img/enligne.png" alt="" /></a></div>' . $FINDTXT, $htmlDatas ["CONTENUS"]);
                }
            }
        }
    }


    /*
    Insère les références bibliographiques dans le corps du texte.

    Selon la table CAIRN_REFERENCE, il y a 4 types de références :

        * T => référence sur les titres
        * N => référence sur les notes
        * P => référence sur les paragraphes
        * B => référence sur les bibliographies

    Ces références se distinguent par leur manière d'inclusion dans le html (en plus de la sémantique qui leur est propre).
    Une référence dans les notes ne s'insérerra pas de la même manière que dans un paragraphe.

    Une référence peut aussi pointer vers un contenu sur cairn qu'en dehors (suivant les partenariats ou autres).

    TODO
    ====
    La manière d'inclure les références selon leur type est un simple problème de style visuel. Cela pourrait être corriger de manière à avoir le même type
    d'inclusion. Je n'ai pas le temps de le faire actuellement, mais ça pourrait très bien être repris

    NOTES
    =====
    (serge.kilimoff@cairn.info) :: J'ai refactorisé le code de la fonction applyDOI car elle était peu maintenable en l'état, principalement à cause des copier-coller,
    sans compter le fait qu'il était fortement lié au texte brut html. Cela a cassé quand j'ai inversé les id et les classes des paragraphes (simple maintenance dans les xslts), ce qui est vraiment dommage et facile à prendre compte.
    Maintenant, on recherche la balise qui porte l'id de la référence, quel que soit son nom ou ses autres attributs.
    */
    private function insertReferencesIntoContent(&$htmlDatas, $id_article, $type = null)
    {
        $references = $this->content->getReferences($id_article);
        $stackRefs = array();
        foreach ($references as $ref) {
            // Un grand nombre de références sont en doublon dans la BDD. J'en ignore la raison.
            // Mais on court-circute tout ça en maintenant une pile des éléments déjà traités.
            if (in_array($ref['NOREF'], $stackRefs)) {
                continue;
            }
            array_push($stackRefs, $ref['NOREF']);
            $match = array();
            // On recherche la balise contenant l'id
            $regexp = '/<[^>]+(id|name)=([\'"])' . $ref['NOREF'] . '\2[^>]*>/';
            // Il y a une erreur de regexp, on saute par sécurité. mais ça n'arrivera bien sûr jamais :)
            if (preg_match($regexp, $htmlDatas['CONTENUS'], $match) == false) {
                continue;
            }

            // Suivant les références, on pointe sur cairn ou en dehors. L'icone ne sera pas la même.
            $onCairn = false;

            // On reconstruit l'url de la référence
            if ($ref['ID_CAIRN_NUM'] == '' && isset($this->authInfos ['I']) && $this->authInfos['I']['HISTORIQUE'] != '') {
                // C'est une url qui utilise open-url. Pour plus de renseignements, se reporter au code de openurl sur dedi.cairn.info
                $url = $this->authInfos['I']['HISTORIQUE'];
                $url = str_replace('[ID_ARTICLE]', $id_article, $url);
                $url = str_replace('[NOREF]', $ref['NOREF'], $url);
                $url = str_replace('[TYPEREF]', $ref['TYPEF'], $url);
                $url = str_replace('[ID_INST]', $this->authInfos ['I'] ['ID_USER'], $url);
                $onCairn = true;
            } elseif ($ref['ID_CAIRN_NUM'] != '') {
                $url = 'numero.php?ID_NUMPUBLIE=' . $ref['ID_CAIRN_NUM'];
                $onCairn = true;
            } elseif ($ref['ID_CAIRN_CIBLE'] != '') {
                $url = 'article.php?ID_ARTICLE=' . $ref['ID_CAIRN_CIBLE'];
                $onCairn = true;
            } elseif ($ref['DOI_CIBLE'] != '') {
                $url = $ref['DOI_CIBLE'];
            } elseif ($ref['URL_CIBLE'] != '') {
                $url = $ref['URL_CIBLE'];
            } else {
                continue;
            }

            $imgSrc = $onCairn ? 'enligneCairn' : 'enligne';

            // Ici, on fait le distinguo entre les manières d'insérer une référence au sein du html
            $refHtml = '';
            if ($ref['TYPEF'] === 'B') {
                $refHtml .= "<span class='reference'><a href='$url'><img src='./img/$imgSrc.png' /></a></span>";
                $refHtml .= $match[0];
            } else {
                $refHtml .= $ref['TYPEF'] !== 'N' ? $match[0] : '';
                $refHtml .= "<div class='refnote ref-enligne'><a href='$url'><img src='./img/$imgSrc.png' /></a></div>";
                $refHtml .= $ref['TYPEF'] === 'N' ? $match[0] : '';
            }
            // On insère la référence
            $htmlDatas['CONTENUS'] = str_replace($match[0], $refHtml, $htmlDatas['CONTENUS']);
        }
    }


    private function applyIMG(&$htmlDatas, $article, $numero)
    {
        foreach ($htmlDatas as $typeContent => &$htmlpart) {
            $htmlpart = str_replace('loadimg.php?FILE=', 'loadimg.php?FILE=' . $numero ['NUMERO_ID_REVUE'] . '/' . $numero ['NUMERO_ID_NUMPUBLIE'] . '/' . $article ['ARTICLE_ID_ARTICLE'] . '/', $htmlpart);
        }
    }
    private function insertStatLog($pageType, $currentArticle)
    {
        $typeLog = '';
        switch ($pageType) {
            case 'A' :
                $typeLog = 'A';
                $this->managerCom->incrementConsultationCounter($this->authInfos["G"]["TOKEN"]);
                break;
            case 'PDF' :
                $typeLog = 'P';
                $this->managerCom->incrementConsultationPdfCounter($this->authInfos["G"]["TOKEN"]);
                break;
            case 'FEUILA' :
                $typeLog = 'F';
                $this->managerCom->incrementConsultationCounter($this->authInfos["G"]["TOKEN"]);
                break;
            case 'FEUILC' :
                $typeLog = '1';
                break;
            case 'R' :
                $typeLog = 'R';
                break;
            case 'Z' :
                $typeLog = 'Z';
                break;
            case 'PA':
                $typeLog = '2';
                break;
            case 'PR':
                $typeLog = '3';
                break;
            case 'EXTRAWEB':
                $typeLog = '4';
                break;
            case 'PORTAIL':
                $typeLog = '5';
                break;
        }

        //On incrémente les logs sur les disciplines
        $discs = $this->content->getDisciplineForRevue($currentArticle['REVUE_ID_REVUE']);
        $alreadyDone = array();
        foreach($discs as $disc){
            $id = $disc["PARENT"]=="0"?$disc["POS_DISC"]:$disc["PARENT"];
            if(!in_array($id, $alreadyDone)){
                $this->managerCom->incrementDisciplineCounter($this->authInfos["G"]["TOKEN"], $id);
            }
        }

        return $this->managerStat->insertArticle(
            $typeLog,
            $currentArticle['ARTICLE_ID_ARTICLE'],
            $currentArticle['NUMERO_ID_NUMPUBLIE'],
            $currentArticle['REVUE_ID_REVUE'],
            $this->authInfos,
            $_SERVER['HTTP_USER_AGENT']
        );
    }

    private function getRefBiblioFromHTML($idRevue, $idNumpublie, $idArticle) {
        $matches = array();
        $htmlForRefBiblio = $this->html->loadFile($idRevue .'/'. $idNumpublie .'/'. $idArticle .'/PA_' . $idArticle . '.htm', 'HTML');


        if(!empty($htmlForRefBiblio)) {
            preg_match_all("/<div class=\"refbiblio\">(.*?)<\/div>/s", $htmlForRefBiblio, $matches);
            $matches = $matches[1];
            $matches = array_map("strip_tags", $matches);
            return $matches;
        }
        else { return false; }
    }
}
