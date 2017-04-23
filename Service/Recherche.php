<?php
	/*
	 * @date 16/03/2017
	 * @author Julien CADET
	 * Ce service de recherche permet, sans passer par un controleur de récupérer des données uniquement accessibles via le moteur de recherche de Pythagoria.
	 * Il s'agit généralement de parties de controleur copiées et adaptées
	 */

	require_once 'Modele/Search.php';
	require_once 'Modele/Content.php';
	require_once 'Framework/include/JsonRpcPth.php';

	class recherche {

		private $content;
    	private $contentdb;
    	private $urlService; //pour la recherche depuis le nt110

		function __construct() {
	        $this->content = new Search();
        	$this->contentdb = new Content();
        	$this->urlService = Configuration::get('middleware_json_rpc', null);
	    }

	    /* 
	     * Il s'agit du code du controleur "controleurRecherche > sujetProche()"
	     * Cette méthode permet de récupérer la liste des sujets proches.
	     * Remarque : les données sont déjà formatées
	     * @referer : #69436
	     */

		public function getSujetProche($ID_ARTICLE, $limit = 5) {
			$indexes = array(Configuration::get("indexPath"));


	        //$searchTerm = remove_accents($searchTerm);
	        $boolean = array();
	        $expander = array("family");


	        //$booleanCondition = "(xfilter (word \"id::$ID_ARTICLE\"))";
	        $booleanCondition = "(id contains ($ID_ARTICLE))";
	        $searchT = array('pack' => 0, 'request' => 'xlastword', 'applyFilter' => '', 'method' => 'search', 'noFacettes' => '1', 'wantDetails' => 0, 'maxFiles' => $limit, 'autoStopLimit' => $limit, 'startAt' => 0, 'spell' => "", "index" => $indexes, "booleanCondition" => $booleanCondition);


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

	        // Limitation manuelle des résultats (les valeurs de maxFiles et AutoStopLimit semblent ignorés...)
	        $i 		= 0;
	        $max 	= $limit;
	        foreach ($result->Items as $res) {
	        	if($i < $max) {
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

		            $i++;
	            }
	            else {
	            	break;
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

	        // Return 
	        return array('titre' => $titre, 'metaNumero' => $metaNumero, 'Ouvrages' => $ouvrages, 'Revues' => $revues, 'Magazines' => $magazines, 'label2facette' => $labels2facettes, 'stats' => $result->Stats, 'hiddenFacettes' => $facetteshidden, 'disciplines' => $disciplines, 'typepub' => $typepub, 'searchTerm' => $searchTerm, 'typeDocument' => $typeDocument, 'accessibleArticles' => $accessible_arts, 'limit' => $startAt, 'articlesButtons' => $articlesButtons, 'portalInfo' => $portalInfo, 'typePubCurrent' => $typePubCurrent);
		}
	}
?>