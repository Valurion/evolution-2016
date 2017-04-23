<?php
/**
 * CONTROLER - Utilisé pour le lancement des traitements batch.
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com
 * @author Benjamin HENNON
 */
require_once 'Framework/Controleur.php';

// loading the related Model
require_once 'Modele/Content.php';
require_once 'Modele/ContentCom.php';
require_once 'Modele/Manager.php';
require_once 'Modele/ManagerCom.php';
require_once 'Modele/ContentEvidensse.php';
require_once 'Modele/ManagerEvidensse.php';
require_once 'Modele/Filter.php';

class ControleurBatch extends Controleur {

    private $content;
    private $contentCom;
    private $manager;
    private $managerCom;

    private $MODE_DEBUG = 1;
    private $MODE_TEST = 2;
    private $MODE_PROD = 3;

    private $DEFAULT_TVA = 20;
    private $DEFAULT_REDUCED_TVA = 5.5;

    public function __construct() {
        $this->content = new Content();
        $this->contentCom = new ContentCom('dsn_com');
        $this->manager = new Manager();
        $this->managerCom = new ManagerCom('dsn_com');
    }

    public function index(){

    }

    public function reCalcCmd(){
        $fromDate = '20150315';
        if($this->requete->existeParametre('fromDate')){
            $fromDate = $this->requete->getParametre('fromDate');
        }
        $toDate = date('Ymd');
        if($this->requete->existeParametre('toDate')){
            $toDate = $this->requete->getParametre('toDate');
        }
        $cmds = $this->contentCom->getCommandeFromInterval($fromDate,$toDate);
        foreach($cmds as $cmd){
            $myTotalPrice = 0;
            $arrayArts = array();
            $panier = json_decode($cmd["ACHATS"]);
            foreach($panier as $item){
                $prefix = substr($item,0,1);
                switch($prefix){
                    case 'B':
                        $itemElems = explode('°',substr($item,1));
                        $arrayAbo = [
                          "ABO" => $this->content->getAboDetails($itemElems[0], $itemElems[1])
                        ];
                        $myTotalPrice += $arrayAbo['ABO']['PRIX'];
                        break;
                    case 'N':
                        $numeroPaper = $this->content->getNumpublieById(substr($item,1))[0];
                        // Oui, c'est du dirty-fix
                        if ($numeroPaper['NUMERO_PRIX'] == 0 || $numeroPaper['NUMERO_EPUISE'] == 1) {
                            break;
                        }
                        $myTotalPrice += $numeroPaper['NUMERO_PRIX'];
                        break;
                    case 'E':
                        $arrayNumRevElec = $this->content->getNumpublieById(substr($item,1))[0];
                        $myTotalPrice += $arrayNumRevElec['NUMERO_PRIX_ELEC'];
                        break;
                    case 'A':
                        $arrayArts[] = substr($item,1);
                        break;
                    case 'D':
                        //Bundle...
                        break;
                    case 'C':
                        $myTotalPrice += substr($item,1);
                        break;
                }
            }
            if(!empty($arrayArts)){
                $articles = $this->content->getBiblioArticles($arrayArts);
                foreach ($articles as $article) {
                    $myTotalPrice += $article['ARTICLE_PRIX'];
                }
            }
            if($cmd['PRIX']<$myTotalPrice){
                echo '<BR/>Cmd '.$cmd['ID_COMMANDE'].': '.$myTotalPrice .'><'.$cmd['PRIX']." >>> ".($cmd['PRIX']>=$myTotalPrice?"OK":"KO PRICES DON'T MATCH !!!");
            }
        }
    }

    public function deleteRedisKey(){
        if($this->requete->existeParametre('key')){
            require_once 'Modele/RedisClient.php';
            $redis = new RedisClient();
            $key = $this->requete->getParametre('key');
            $ret = $redis->delete($key);
            var_dump($ret);
        }
    }

    public function genererFactures() {
        // On récupère la liste des revues concernées par la TVA presse réduite
        $revuesWithPressTVA = $this->content->getRevueWithPressTva();
        $listRevuesWithPressTVA = array();
        foreach ($revuesWithPressTVA as $revue) {
            $listRevuesWithPressTVA[$revue['ID_REVUE']] = $revue['DATE_FIN_CPPAP'];
        }
        $pressTVA = 2.1;

        //1- Trouver les factures à envoyer
        $commandes = $this->contentCom->getCommandesAFacturer();

        foreach($commandes as $commande){
            echo '<br\>Commande : '.$commande['NO_COMMANDE'].'<br/>';

            $idCommande = $commande['NO_COMMANDE'];

            $commande['DATE_ONLY'] = explode(' ', $commande['DATE'])[0];
            $commandeTmp = $commande;
            $emailUser = $commandeTmp['ID_USER'];

            $commandeDepuisFrance = ($commande['FACT_PAYS']) === 'France' || !$commande['FACT_PAYS'];
            $isCE = $this->content->isPaysEU($commandeTmp['FACT_PAYS']);
            $tauxTVA = 0;
            $tauxTVAreduit = 0;
            if($isCE){
                $tauxTVA = $isCE['TAUX_TVA']!=null?$isCE['TAUX_TVA']:$this->DEFAULT_TVA;
                $tauxTVAreduit = $isCE['TAUX_TVA_REDUIT']!=null?$isCE['TAUX_TVA_REDUIT']:$this->DEFAULT_REDUCED_TVA;
            }
            $achats = $this->contentCom->getAchatsCommande($commande['NO_COMMANDE']);

            $abos = $this->contentCom->getAbosCommande($commande['NO_COMMANDE']);
            $arrayCredits = $this->contentCom->getCreditsCommande('C'.$commande['NO_COMMANDE']);


            $arrayAbos = array();
            $arrayNums = array();
            $arrayNumsElec = array();
            $arrayArts = array();

            $prices = [
                "totalPrice" => 0,
                "totalPriceHT" => 0,
                "totalTVA20" => 0,
                "totalTVA5dot5" => 0,
                "totalTVA2dot1" => 0,
                "totalFP" => 0
            ];
            foreach($arrayCredits as &$credit){
                $tva = $tauxTVA;
                $tvaPriceName = 'totalTVA20';
                if ($commandeDepuisFrance) {
                    $tva = $tauxTVAreduit;
                    $tvaPriceName = 'totalTVA5dot5';
                }
                $credit['PRIX_TOTAL'] = $credit['PRIX_CMD'];
                $credit['PRIX_HT'] = round(floatval($credit['PRIX_TOTAL'] / (1 + ($tva/100))),2);
                $credit['PRIX_TVA'] = floatval($credit['PRIX_TOTAL'] - $credit['PRIX_HT']);
                $credit['TAUX_TVA'] = $tva;
                $credit['FP'] = 0;

                $prices['totalPrice'] += $credit['PRIX_TOTAL'];
                $prices['totalPriceHT'] += $credit['PRIX_HT'];
                $prices[$tvaPriceName] += $credit['PRIX_TVA'];
                $prices['totalFP'] += 0;

            }
            foreach($abos as $abo){
                $abon = $this->content->getAboDetails($abo['ID_ABON'], $abo['ID_REVUE']);
                $abon['INFOSUP'] = $abo['ANNEE_DEBUT']!=''?$abo['ANNEE_DEBUT']:$abo['ID_NUMPUBLIE'];
                $idZone = $abo['ID_ZONE'];
                $fp = $this->content->getFraisByZone('PORT_ABO',$idZone,$abo['ID_REVUE']);

                $tva = $tauxTVAreduit;
                $tvaPriceName = 'totalTVA5dot5';
                if ($isCE) {
                    $tva = 5.5;
                    $isRevueWithPressTVA = isset($listRevuesWithPressTVA[$abo['ID_REVUE']]);
                    if ($isRevueWithPressTVA) {
                        $isRevueWithPressTVA = $commandeTmp['DATE_ONLY'] <= $listRevuesWithPressTVA[$abo['ID_REVUE']];
                    }
                    if ($isRevueWithPressTVA) {
                        $tva = $pressTVA;
                        $tvaPriceName = 'totalTVA2dot1';
                    }
                }

                $abon['PRIX_TOTAL'] = floatval($fp)+floatval($abo['PRIX']);

                $abon['PRIX_HT'] = round(floatval($abon['PRIX_TOTAL'] / (1 + ($tva/100))),2);
                $abon['PRIX_TVA'] = floatval($abon['PRIX_TOTAL'] - $abon['PRIX_HT']);
                $abon['TAUX_TVA'] = $tva;

                $abon['FP'] = $fp;

                $prices['totalPrice'] += $abon['PRIX_TOTAL'];
                $prices['totalPriceHT'] += $abon['PRIX_HT'];
                $prices[$tvaPriceName] += $abon['PRIX_TVA'];
                $prices['totalFP'] += $abon['FP'];

                $arrayAbos[] = $abon;
            }
            foreach($achats as $achat){
                if($achat['ID_ARTICLE'] != '') {
                    //Modification de Dimitry (Cairn), le 26 octobre 2015.
                    //Partie bloquante de la génération des factures.
                    $art = array_map('strip_tags', $this->content->getArticleFromId($achat['ID_ARTICLE']));
                    $tva = $tauxTVA;
                    $tvaPriceName = 'totalTVA20';
                    if ($commandeDepuisFrance) {
                        if ($art['REVUE_TYPEPUB'] == 2) {
                            $tva = $pressTVA;
                            $tvaPriceName = 'totalTVA2dot1';
                        } else if ($art['REVUE_TYPEPUB'] == 1) {
                            $isRevueWithPressTVA = isset($listRevuesWithPressTVA[$art['REVUE_ID_REVUE']]);
                            if ($isRevueWithPressTVA) {
                                $isRevueWithPressTVA = $commandeTmp['DATE_ONLY'] <= $listRevuesWithPressTVA[$art['REVUE_ID_REVUE']];
                            }
                            if ($isRevueWithPressTVA) {
                                $tva = $pressTVA;
                                $tvaPriceName = 'totalTVA2dot1';
                            } else {
                                $tva = $tauxTVAreduit;
                                $tvaPriceName = 'totalTVA5dot5';
                            }
                        } else if (in_array($art['REVUE_TYPEPUB'], [3, 6])) {
                            $tva = $tauxTVAreduit;
                            $tvaPriceName = 'totalTVA5dot5';
                        }
                    }
                    $art['PRIX_TOTAL'] = $achat['PRIX'];
                    $art['PRIX_HT'] = round(floatval($art['PRIX_TOTAL'] / (1 + ($tva/100))),2);
                    $art['PRIX_TVA'] = floatval($art['PRIX_TOTAL'] - $art['PRIX_HT']);
                    $art['FP'] = $achat['FRAIS_PORT'];
                    $art['TAUX_TVA'] = $tva;

                    $prices['totalPrice'] += $art['PRIX_TOTAL'];
                    $prices['totalPriceHT'] += $art['PRIX_HT'];
                    $prices[$tvaPriceName] += $art['PRIX_TVA'];
                    $prices['totalFP'] += $art['FP'];

                    $arrayArts[] = $art;
                }else{
                    if($achat['TYPE'] == 'E'){
                        $num = $this->content->getNumpublieById($achat['ID_NUMPUBLIE'])[0];
                        $tva = $tauxTVA;
                        $tvaPriceName = 'totalTVA20';

                        if ($commandeDepuisFrance) {
                            if ($num['REVUE_TYPEPUB'] == 1) {
                                $isRevueWithPressTVA = isset($listRevuesWithPressTVA[$num['NUMERO_ID_REVUE']]);
                                if ($isRevueWithPressTVA) {
                                    $isRevueWithPressTVA = $commandeTmp['DATE_ONLY'] <= $listRevuesWithPressTVA[$num['NUMERO_ID_REVUE']];
                                }
                                if ($isRevueWithPressTVA) {
                                    $tva = $pressTVA;
                                    $tvaPriceName = 'totalTVA2dot1';
                                } else {
                                    $tva = $tauxTVAreduit;
                                    $tvaPriceName = 'totalTVA5dot5';
                                }
                            } elseif ($num['REVUE_TYPEPUB'] == 3) {
                                $tva = $tauxTVAreduit;
                                $tvaPriceName = 'totalTVA5dot5';
                            }
                        }

                        $num['PRIX_TOTAL'] = $achat['PRIX'];

                        $num['PRIX_HT'] = round(floatval($num['PRIX_TOTAL'] / (1 + ($tva/100))),2);
                        $num['PRIX_TVA'] = floatval($num['PRIX_TOTAL'] - $num['PRIX_HT']);
                        $num['TAUX_TVA'] = $tva;

                        $num['FP'] = $achat['FRAIS_PORT'];

                        $prices['totalPrice'] += $num['PRIX_TOTAL'];
                        $prices['totalPriceHT'] += $num['PRIX_HT'];
                        $prices[$tvaPriceName] += $num['PRIX_TVA'];
                        $prices['totalFP'] += $num['FP'];

                        $arrayNumsElec[] = $num;
                    }else{
                        $num = $this->content->getNumpublieById($achat['ID_NUMPUBLIE'])[0];

                        $tva = $tauxTVAreduit;
                        $tvaPriceName = 'totalTVA5dot5';
                        if ($isCE) {
                            $tva = 5.5;
                            $isRevueWithPressTVA = isset($listRevuesWithPressTVA[$num['NUMERO_ID_REVUE']]);
                            if ($isRevueWithPressTVA) {
                                $isRevueWithPressTVA = $commandeTmp['DATE_ONLY'] <= $listRevuesWithPressTVA[$num['NUMERO_ID_REVUE']];
                            }
                            if ($isRevueWithPressTVA) {
                                $tva = $pressTVA;
                                $tvaPriceName = 'totalTVA2dot1';
                            }
                        }

                        $num['PRIX_TOTAL'] = floatval($achat['FRAIS_PORT'])+floatval($achat['PRIX']);
                        $num['PRIX_HT'] = round(floatval($num['PRIX_TOTAL'] / (1 + ($tva/100))),2);
                        $num['PRIX_TVA'] = floatval($num['PRIX_TOTAL'] - $num['PRIX_HT']);
                        $num['TAUX_TVA'] = $tva;

                        $num['FP'] = $achat['FRAIS_PORT'];

                        $prices['totalPrice'] += $num['PRIX_TOTAL'];
                        $prices['totalPriceHT'] += $num['PRIX_HT'];
                        $prices[$tvaPriceName] += $num['PRIX_TVA'];
                        $prices['totalFP'] += $num['FP'];

                        $arrayNums[] = $num;
                    }
                }
            }
            $typesPanier = $this->getTypePanier($arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits);
            $typePanier = $typesPanier['typePanier'];

            //Envoyer le mail avec la facture attachée
            $emailsCC = $this->content->getUsersCairn();
            $toCc = array();
            foreach ($emailsCC as $emailCC){
               $toCc[] = $emailCC['ID_USER'];
            }

            switch($typePanier){
                case 'credit':
                    require_once (__DIR__."/../".Configuration::get('dirVue')."/User/Factures/facture".ucfirst($typePanier).".php");
                    $bdcContent = getFactureCredit($idCommande,$commandeTmp, $arrayCredits, $prices, $tauxTVA, $tauxTVAreduit);

                    $pdfContent = $this->saveFacture("F".$idCommande,$bdcContent);

                    Service::get('Mailer')->sendMailFromTpl(
                        '/'.Configuration::get('dirVue').'/User/Mails/mailFacture'.ucfirst($typePanier).'.xml',
                        array(
                            'isHtml' => 1,
                            'to' => $emailUser,
                            'NO_COMMANDE' => $idCommande,
                            'toBcc' => $toCc,
                            'ID_USER' => $emailUser,
                            'DATE_ACHAT' => date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y'),
                            'CREDIT_PRIX' => $arrayCredits[0],
                            'CREDIT_EXPIRATION_CREDIT' => '31-12-'.(intval(date('Y'))+1),
                            'URLSITE' => Configuration::get('urlSite')
                        ),
                        array(
                            "F".$idCommande.".html" => $bdcContent,
                            "F".$idCommande.".pdf" => $pdfContent
                        )
                    );


                    break;
                case 'numero':
                    require_once (__DIR__."/../".Configuration::get('dirVue')."/User/Factures/factureNumero.php");
                    $bdcContent = getFactureNumero($idCommande,$commandeTmp,$arrayNums, $prices, $tauxTVA, $tauxTVAreduit);

                    $pdfContent = $this->saveFacture("F".$idCommande,$bdcContent);

                    Service::get('Mailer')->sendMailFromTpl(
                        '/'.Configuration::get('dirVue').'/User/Mails/mailFacture'.ucfirst($typePanier).'.xml',
                        array(
                            'isHtml' => 1,
                            'to' => $emailUser,
                            'NO_COMMANDE' => $idCommande,
                            'toBcc' => $toCc,
                            'NUMERO_ANNEE' => $arrayNums[0]['NUMERO_ANNEE'],
                            'NUMERO_NUMERO' => $arrayNums[0]['NUMERO_NUMERO'],
                            'REVUE_TITRE' => strip_tags($arrayNums[0]['REVUE_TITRE']),
                            'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                            'ID_USER' => $emailUser,
                            'ID_REVUE' => $arrayNums[0]['NUMERO_ID_REVUE'],
                            'NUMERO_ID_NUMPUBLIE' => $arrayNums[0]['NUMERO_ID_NUMPUBLIE'],
                            'DATE_ACHAT' => date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y'),
                            'URLSITE' => Configuration::get('urlSite')
                        ),
                        array(
                            "F".$idCommande.".html" => $bdcContent,
                            "F".$idCommande.".pdf" => $pdfContent
                        )
                    );
                    break;
                case 'numeroElec':
                    require_once (__DIR__."/../".Configuration::get('dirVue')."/User/Factures/facture".ucfirst($typePanier).".php");
                    $bdcContent = getFactureNumeroElec($idCommande,$commandeTmp,$arrayNumsElec, $prices, $tauxTVA, $tauxTVAreduit);

                    $pdfContent = $this->saveFacture("F".$idCommande,$bdcContent);

                    Service::get('Mailer')->sendMailFromTpl(
                        '/'.Configuration::get('dirVue').'/User/Mails/mailFacture'.ucfirst($typePanier).'.xml',
                        array(
                            'isHtml' => 1,
                            'to' => $emailUser,
                            'NO_COMMANDE' => $idCommande,
                            'toBcc' => $toCc,
                            'NUMERO_ANNEE' => $arrayNumsElec[0]['NUMERO_ANNEE'],
                            'NUMERO_NUMERO' => $arrayNumsElec[0]['NUMERO_NUMERO'],
                            'REVUE_TITRE' => strip_tags($arrayNumsElec[0]['REVUE_TITRE']),
                            'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                            'ID_USER' => $emailUser, 'ID_REVUE' => $arrayNumsElec[0]['NUMERO_ID_REVUE'],
                            'NUMERO_ID_NUMPUBLIE' => $arrayNumsElec[0]['NUMERO_ID_NUMPUBLIE'],'DATE_ACHAT' => date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y'),
                            'URLSITE' => Configuration::get('urlSite')
                        ), array(
                            "F".$idCommande.".html" => $bdcContent,
                            "F".$idCommande.".pdf" => $pdfContent
                        )
                    );
                    break;
                case 'article':
                case 'articleMag':
                    require_once (__DIR__."/../".Configuration::get('dirVue')."/User/Factures/facture".ucfirst($typePanier).".php");
                    if($typePanier == 'article'){
                        $bdcContent = getFactureArticle($idCommande,$commandeTmp,$arrayArts, $prices, $tauxTVA, $tauxTVAreduit);
                    }else{
                        $bdcContent = getFactureArticleMag($idCommande,$commandeTmp,$arrayArts, $prices, $tauxTVA, $tauxTVAreduit);
                    }

                    $pdfContent = $this->saveFacture("F".$idCommande,$bdcContent);

                    Service::get('Mailer')->sendMailFromTpl(
                        '/'.Configuration::get('dirVue').'/User/Mails/mailFacture'.ucfirst($typePanier).'.xml',
                        array(
                            'isHtml' => 1,
                            'to' => $emailUser,
                            'NO_COMMANDE' => $idCommande,
                            'toBcc' => $toCc,
                            'REVUE_TITRE' => strip_tags($arrayArts[0]['REVUE_TITRE']),
                            'ID_USER' => $emailUser,
                            'DATE_ACHAT' => date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y'),
                            'ID_ARTICLE' => $arrayArts[0]['ARTICLE_ID_ARTICLE'],
                            'URLSITE' => Configuration::get('urlSite')
                        ),
                            array("F".$idCommande.".html" => $bdcContent,
                                  "F".$idCommande.".pdf" => $pdfContent));
                    break;
                case 'chapitre':
                    require_once (__DIR__."/../".Configuration::get('dirVue')."/User/Factures/factureChapitre.php");
                    $bdcContent = getFactureChapitre($idCommande,$commandeTmp,$arrayArts, $prices, $tauxTVA, $tauxTVAreduit);

                    $pdfContent = $this->saveFacture("F".$idCommande,$bdcContent);

                    Service::get('Mailer')->sendMailFromTpl(
                        '/'.Configuration::get('dirVue').'/User/Mails/mailFacture'.ucfirst($typePanier).'.xml',
                        array(
                            'isHtml' => 1,
                            'to' => $emailUser,
                            'NO_COMMANDE' => $idCommande,
                            'toBcc' => $toCc,
                            'NUMERO_TITRE' => strip_tags($arrayArts[0]['NUMERO_TITRE']),
                            'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                            'ID_USER' => $emailUser,
                            'DATE_ACHAT' => date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y'),
                            'ID_ARTICLE' => $arrayArts[0]['ARTICLE_ID_ARTICLE'],
                            'URLSITE' => Configuration::get('urlSite')
                        ),
                        array(
                            "F".$idCommande.".html" => $bdcContent,
                            "F".$idCommande.".pdf" => $pdfContent
                        )
                    );
                    break;
                case 'abo':
                    //Un seul abo dans la commande
                    $infoSup = $arrayAbos[0]['INFOSUP'];
                    if(strlen($infoSup) == 4 && $infoSup > 2010 && $infoSup < 2050){
                        $abonnementInfoSup = $infoSup;
                    }else{
                        $numFrom = $this->content->getNumpublieById($infoSup)[0];
                        $abonnementInfoSup = 'à partir du n°'.$numFrom['NUMERO_ANNEE'].'/'.$numFrom['NUMERO_NUMERO'].($numFrom['NUMERO_VOLUME']==''?'':(' ('.$numFrom['NUMERO_VOLUME'].')'));
                    }
                    require_once (__DIR__."/../".Configuration::get('dirVue')."/User/Factures/factureAbo.php");
                    $bdcContent = getFactureAbo($idCommande,$commandeTmp,$arrayAbos, $prices, $tauxTVA, $tauxTVAreduit);

                    $pdfContent = $this->saveFacture("F".$idCommande,$bdcContent);

                    Service::get('Mailer')->sendMailFromTpl(
                        '/'.Configuration::get('dirVue').'/User/Mails/mailFacture'.ucfirst($typePanier).'.xml',
                        array(
                            'isHtml' => 1,
                            'to' => $emailUser,
                            'NO_COMMANDE' => $idCommande,
                            'toBcc' => $toCc,
                            'REVUE_TITRE' => strip_tags($arrayAbos[0]['TITRE']),
                            'DATE_ACHAT' => date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y'),
                            'ID_USER' => $emailUser,
                            'ID_REVUE' => $arrayAbos[0]['ID_REVUE'],
                            'ABONNEMENT_LIBELLE' => strip_tags($arrayAbos[0]['LIBELLE']),
                            'ABONNEMENT_INFOSUP' => strip_tags($abonnementInfoSup),
                            'URLSITE' => Configuration::get('urlSite')
                        ),
                        array(
                            "F".$idCommande.".html" => $bdcContent,
                            "F".$idCommande.".pdf" => $pdfContent
                        )
                    );
                    break;
                case 'achats':
                    require_once (__DIR__."/../".Configuration::get('dirVue')."/User/Factures/factureAchats.php");


                    $bdcContent = getFactureAchats($idCommande, $commandeTmp, $arrayArts, $arrayNums, $arrayNumsElec, $arrayAbos, $arrayCredits, $prices, $tauxTVA, $tauxTVAreduit);

                    $pdfContent = $this->saveFacture("F".$idCommande,$bdcContent);

                    Service::get('Mailer')->sendMailFromTpl(
                        '/'.Configuration::get('dirVue').'/User/Mails/mailFacture'.ucfirst($typePanier).'.xml',
                        array(
                            'isHtml' => 1,
                            'to' => $emailUser,
                            'NO_COMMANDE' => $idCommande,
                            'toBcc' => $toCc,
                            'ID_USER' => $emailUser,
                            'DATE_ACHAT' => date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y'),
                            'URLSITE' => Configuration::get('urlSite')
                        ),
                        array(
                            "F".$idCommande.".html" => $bdcContent,
                            "F".$idCommande.".pdf" => $pdfContent
                        )
                    );
                    break;
            }
            //3- Garnir la DB avec le numéro de facture
            $this->managerCom->setNoFactureAchats($idCommande);
            $this->managerCom->setNoFactureAbos($idCommande);
            $this->managerCom->setNoFactureCredits('C'.$idCommande);
            $this->managerCom->setDateSendFact($idCommande);

        }
    }

    public function envoiConfirmation(){

    }

    public function alerteExpirationCredit(){

    }

    private function getTypePanier($arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits){
        $poidPanier = 0;
        $typePanier = '';
        $linkPanier = '';
        if(!empty($arrayArts)){
            $chap = 0;
            $art = 0;
            $artMag = 0;
            foreach ($arrayArts as $article){
                if($article['REVUE_TYPEPUB'] == 1){
                    $art++;
                }else if($article['REVUE_TYPEPUB'] == 2){
                    $artMag++;
                }else{
                    $chap++;
                }
            }
            if($art > 0){
                $poidPanier += $art;
                $typePanier = 'article';
                $linkPanier = './article.php?ID_ARTICLE='.$arrayArts[0]['ARTICLE_ID_ARTICLE'];
            }
            if($artMag > 0){
                $poidPanier += $artMag;
                $typePanier = 'articleMag';
                $linkPanier = './article.php?ID_ARTICLE='.$arrayArts[0]['ARTICLE_ID_ARTICLE'];
            }
            if($chap > 0){
                $poidPanier += $chap;
                $typePanier = 'chapitre';
                $linkPanier = './article.php?ID_ARTICLE='.$arrayArts[0]['ARTICLE_ID_ARTICLE'];
            }
        }
        if(!empty($arrayNums)){
            $poidPanier += count($arrayNums);
            $typePanier = 'numero';
            $linkPanier = './revue-'.$arrayNums[0]['REVUE_URL_REWRITING']
                        .'-'.$arrayNums[0]['NUMERO_ANNEE']
                        .'-'.$arrayNums[0]['NUMERO_NUMERO'].'.htm';
        }
        if(!empty($arrayNumsElec)){
            $poidPanier += count($arrayNumsElec);
            $typePanier = 'numeroElec';
            $linkPanier = './revue-'.$arrayNumsElec[0]['REVUE_URL_REWRITING']
                        .'-'.$arrayNumsElec[0]['NUMERO_ANNEE']
                        .'-'.$arrayNumsElec[0]['NUMERO_NUMERO'].'.htm';
        }
        if(!empty($arrayAbos)){
            $poidPanier += count($arrayAbos);
            $typePanier = 'abo';
            $linkPanier = './revue-'.$arrayAbos[0]['URL_REWRITING'].'.htm';
        }
        if(!empty($arrayCredits)){
            $poidPanier += count($arrayCredits);
            $typePanier = 'credit';
            $linkPanier = './mon_credit.php';
        }
        if($poidPanier > 1){
            $typePanier = 'achats';
            $linkPanier = './mes_achats.php';
        }
        return array("typePanier"=>$typePanier, 'linkPanier'=>$linkPanier);
    }

    private function saveFacture($nom, $contenu){
        //Version HTML
        $fileName = Configuration::get('facturePath').'htm/'.$nom.'.htm';

        file_put_contents($fileName, $contenu);

        //Version PDF
        require_once(Configuration::get('cairn_includes_path').'/html2pdf/html2pdf.class.php');

        try
        {
            //Pour contourner le fait que les serveurs de prod ne peuvent faire de l'openUrl
            $contenu = str_replace('http://www.cairn.info','.',$contenu);

            $html2pdf = new HTML2PDF('P', 'A4', 'fr');
            $html2pdf->setDefaultFont('Times');
            $html2pdf->writeHTML($contenu);
            $html2pdf->Output(Configuration::get('facturePath').'pdf/'.$nom.'.pdf','F');

            return $html2pdf->Output($nom.'.pdf', 'S');
        }
        catch(HTML2PDF_exception $e) {
            echo $e;
            exit;
        }


    }

    public function firstDbCrawler(){
        ini_set('memory_limit', '8000M');

        $mode='auto';
        if($this->requete->existeParametre('mode')){
            $mode = $this->requete->getParametre('mode');
        }
        $managerEvidensse = new ManagerEvidensse('dsn_evidensse');

        $nbInsert = 0;

        $articles = $this->content->getAllArticlesForFields2();
        foreach($articles as $article){
            $fields2 = $this->generateFields2($article,$this->content);
            $fileInfos = $this->getFileName($article,$this->content);

            if($mode != 'test'){
                $managerEvidensse->insertDocRS($fileInfos[0],$fileInfos[1],$article,$fields2,1);
            }
            $nbInsert++;
            $this->displayMsg($this->MODE_TEST, 'INSERT (article '.$article['ID_ARTICLE'].' [idxid='.$fileInfos[0].'/file='.$fileInfos[1].'] )');
            $this->displayMsg($this->MODE_DEBUG, $fields2);
        }
        $this->displayMsg($this->MODE_PROD, 'Rapport complet:<br/>'
            . '- Articles ajout&eacute;s : '.$nbInsert.'<br/>'
           );
    }

    public function dbCrawler500(){
        ini_set('memory_limit', '8000M');

        $contentEvidensse = new ContentEvidensse('dsn_evidensse');
        $managerEvidensse = new ManagerEvidensse('dsn_evidensse');

        $managerEvidensse->prepareDocsRS();

        $articles500 = $contentEvidensse->getArticles500FromDocsRS();

        foreach($articles500 as $article500){
            if(file_exists($article500['docname'])){
                echo "Switching ".$article500['docname'];
                $managerEvidensse->switchToIdx($article500['docname'],'220');
            }else{
                echo "Still missing ".$article500['docname'];
            }
        }
    }

    public function dbCrawler(){
        ini_set('memory_limit', '8000M');

        $contentIdxer = new Content('dsn_pub_forIdxer');

        $mode = 'test';
        //$mode='auto';
        if($this->requete->existeParametre('mode')){
            $mode = $this->requete->getParametre('mode');
        }
        $contentEvidensse = new ContentEvidensse('dsn_evidensse');
        $managerEvidensse = new ManagerEvidensse('dsn_evidensse');

        if($mode != 'test'){
            $managerEvidensse->prepareDocsRS();
        }

        $nbInsert = 0;
        $nbDelete = 0;
        $nbReplace = 0;
        $nbUpdateMeta = 0;
        $nbUpdateFull = 0;
        $nbNoChanges = 0;

        //1- On récupère la dernière DATE de lancement
        $lastDate = $contentEvidensse->getLastCrawlingDate(Configuration::get('idxId'));
        $this->displayMsg($this->MODE_PROD, 'Lancement pr&eacute;c&eacute;dent:'.$lastDate);
        //2- On update cette date
        if($mode != 'test')
            $managerEvidensse->setLastCrawlingDate(Configuration::get('idxId'));

        //3- On récupère tous les NUMEROS modifiés depuis lors
        $numeros = $contentIdxer->getModifiedNumsSince($lastDate,(Configuration::get('mode') == 'cairninter' ? 1 : 0));

        /*
         * 4- On parcourt tous les ARTICLES, et on regarde les changements apportés:
         *  - Nouveau ? => 1
         *  - Désactivé ? => 2
         *  - Modifié sur le FS ? => 3
         *  - Modifié métas ? => 4
         *  - Sinon, rien...
         */
        foreach($numeros as $numero){
            $this->displayMsg($this->MODE_TEST, '#######################################<br/>Num&eacute;ro: '.$numero['NUMERO_ID_NUMPUBLIE']);
            if($numero['NUMERO_STATUT'] == 0 || $numero['REVUE_STATUT'] == 0
                    || ( $numero['NUMERO_DERNIERE_EDITION'] != "" && $numero['NUMERO_DERNIERE_EDITION'] != null)){
                $listArticlesIds = $contentIdxer->getAllArticlesIdFromNumero(("'".$numero['NUMERO_ID_NUMPUBLIE']."'"));
                if(!empty($listArticlesIds)){
                    $this->displayMsg($this->MODE_TEST, 'UPDATE 2 / articles :');
                    $this->displayMsg($this->MODE_TEST, null, $listArticlesIds);
                    if($mode != 'test')
                        $managerEvidensse->updateUpdfldForArticlesIfNecessary(2,("'".implode("','",$listArticlesIds)."'"));
                    $nbDelete += count($listArticlesIds);
                }
            }else{
                $articles = $contentIdxer->getArticlesForFields2($numero['NUMERO_ID_NUMPUBLIE'], $numero['REVUE_TYPEPUB']);
                foreach($articles as $article){
                    $this->displayMsg($this->MODE_TEST, '---------------------------------------------');
                    if($article['STATUT'] == 0){
                        if($mode != 'test')
                            $managerEvidensse->updateUpdfldForArticlesIfNecessary(2,("'".$article['ID_ARTICLE']."'"));
                        $nbDelete++;
                        $this->displayMsg($this->MODE_TEST, 'UPDATE 2 (article '.$article['ID_ARTICLE'].')');
                    }else {
                        $artDocRS = $contentEvidensse->getArticleFromDocsRS($article['ID_ARTICLE'],Configuration::get('idxId'));
                        $this->displayMsg($this->MODE_DEBUG, null, $artDocRS);
                        $fields2 = $this->generateFields2($article,$contentIdxer);
                        $fileInfos = $this->getFileName($article,$contentIdxer);
                        if(!$artDocRS){
                            if($mode != 'test')
                                $managerEvidensse->insertDocRS($fileInfos[0],$fileInfos[1],$article,$fields2,(Configuration::get('mode') == 'cairninter' ? 1 : 0));
                            $nbInsert++;
                            $this->displayMsg($this->MODE_TEST, 'INSERT (article '.$article['ID_ARTICLE'].')');
                            $this->displayMsg($this->MODE_DEBUG, $fields2);
                        }else{
                            //On regarde si le fichier est différent (autre nom)
                            if($fileInfos[1] != $artDocRS['docname']){
                                //On met en delete l'ancien
                                if($mode != 'test')
                                    $managerEvidensse->updateUpdfldForArticlesIfNecessary(2,("'".$article['ID_ARTICLE']."'"));
                                $this->displayMsg($this->MODE_TEST, 'UPDATE 2 REMPLACEMENT FICHIER (article '.$article['ID_ARTICLE'].')');
                                //On insère le nouveau
                                if($mode != 'test')
                                    $managerEvidensse->insertDocRS($fileInfos[0],$fileInfos[1],$article,$fields2,(Configuration::get('mode') == 'cairninter' ? 1 : 0));
                                $this->displayMsg($this->MODE_TEST, 'INSERT NOUVEAU FICHIER(article '.$article['ID_ARTICLE'].')');
                                $this->displayMsg($this->MODE_DEBUG, $fields2);

                                $nbReplace++;
                            }else{
                                //On regarde si le fichier a été modifié
                                $fileMod = $this->checkFileModified($article,$lastDate);
                                if($fileMod || $mode == 'bypass'){
                                    if($mode != 'test')
                                        $managerEvidensse->updateUpdfldForArticle(3,$article['ID_ARTICLE'],$fields2,$fileInfos[0]);
                                    $nbUpdateFull++;
                                    $this->displayMsg($this->MODE_TEST, 'UPDATE 3 (article '.$article['ID_ARTICLE'].')');
                                    $this->displayMsg($this->MODE_DEBUG, $fields2);
                                }else{
                                    //On vérifie si le fields2 a été modifié.
                                    //if($fields2 != str_replace("'","\'",$artDocRS['fields2'])){
                                    if($fields2 != (trim($artDocRS['fields2'])) || ($article['MDATE'] != '' && $article['MDATE'] >= $lastDate)){
                                        if($mode != 'test')
                                            $managerEvidensse->updateUpdfldForArticle(4,$article['ID_ARTICLE'],$fields2,$fileInfos[0]);
                                        $nbUpdateMeta++;
                                        $this->displayMsg($this->MODE_TEST, 'UPDATE 4 (article '.$article['ID_ARTICLE'].')');
                                        $this->displayMsg($this->MODE_DEBUG, $fields2);
                                    }else{
                                        //On ne fait rien
                                        $nbNoChanges++;
                                        $this->displayMsg($this->MODE_TEST, 'RIEN (article '.$article['ID_ARTICLE'].')');
                                        $this->displayMsg($this->MODE_DEBUG, $fields2);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->displayMsg($this->MODE_PROD, 'Rapport complet:<br/>'
            . '- Articles ajout&eacute;s : '.$nbInsert.'<br/>'
            . '- Articles supprim&eacute;s : '.$nbDelete.'<br/>'
            . '- Articles remplac&eacute;s : '.$nbReplace.'<br/>'
            . '- Articles m&agrave;j / mod fichier : '.$nbUpdateFull.'<br/>'
            . '- Articles m&agrave;j / m&eacute;tadonn&eacute;es : '.$nbUpdateMeta.'<br/>'
            . '- Articles non chang&eacute;s : '.$nbNoChanges
            . '<br/>'
            . '- Num&eacute;ros concern&eacute;s : '.count($numeros));
    }

    private function displayMsg($modeDebug,$message,$varToDump = null){
        $crawlerMode = Configuration::get('crawlerMode');
        if($this->$crawlerMode <= $modeDebug){
            if($message != null){
                echo '<br/>'.$message;
            }else{
                echo '<br/>';
                var_dump($varToDump);
            }
        }
    }

    private function checkFileModified($article, $lastCrawl){
        $fDir = Configuration::get("prefixPath") . $article["REVUE_ID_REVUE"] . '/' . $article["NUMERO_ID_NUMPUBLIE"] . '/' . $article["ID_ARTICLE"] . '/';
        if(file_exists($fDir) && is_dir($fDir)){
            $directory = opendir($fDir);
            while( ($file = readdir($directory)) !== false){
                $lastMod = date('Y-m-d H:i:s',filemtime($fDir.$file));
                if(is_file($fDir.$file) && $lastMod > $lastCrawl){
                    return true;
                }
            }
        }
        return false;
    }

    private function generateFields2($row,$contentIdxer){
        $ID_ARTICLE = $this->trimp($row['ID_ARTICLE']);
        $ID_REVUE = $this->trimp($row['ID_REVUE']);
        $NP = $this->trimp($row['ID_NUMPUBLIE']);
        $cfga = $this->trimp($row['CONFIG_ARTICLE']);
        $cfgaArr = explode(',',$cfga);

        $fieldauth = "id\t" . $ID_ARTICLE . "\tid_r\t$ID_REVUE\t**cfg0\t$cfga\ttr\t" . $this->trimp($row['TITRE']) . "\t";

        if($row['SECT_SOM'] != ''){
            $fieldauth .= "Sectsom\t".$this->trimp($row['SECT_SOM'])."\t";
        }

        $id_portail = $row['PORTAIL_ID_PORTAIL'];
        if ($id_portail <> '')
            $fieldauth.="**idp\t$id_portail\t";

        //RECH1,RECH2,RECH3,RECH4,RECH5,RECH6,
        for ($i = 1; $i <= 6; $i++) {
            $tirRech = $row['RECH' . $i];
            if ($tirRech != ''){
                $fieldauth.="RECH$i\t$tirRech\t";
            }
        }

        $fieldauth .= "**pgd\t" . $this->trimp($row['PAGE_DEBUT']) . "\t**pgf\t" . $this->trimp($row['PAGE_FIN']) . "\t**px\t" . $this->trimp($row['PRIX']) . "\t";

        $resAuteurs = $contentIdxer->getAuteursForFields2($row['ID_ARTICLE']);
        $doneauth = 0;
        $authors = array();
        $authorsPDF = array();
        foreach ($resAuteurs as $row2) {
            $doneauth++;
            //$fieldauth.= "**auth0\t".trimp($row2["ATTRIBUT"]."#".$row2["NOM"]. '#'. $row2["PRENOM"].'#'.$row2['ID_AUTEUR'])."\t";
            $authors[] = $this->trimp($row2["ATTRIBUT"] . "#" . $row2["NOM"] . '#' . $row2["PRENOM"] . '#' . $row2['ID_AUTEUR']);
            $authorsPDF[] = $row2["NOM"] . ' ' . $row2["PRENOM"];
        }

        $fieldauth.= "**auth0\t" . $this->trimp(implode('|', $authors)) . "\t";

        $fieldauth.= "dr\t" . $this->trimp($row["dr"]) . "\t";

        $fieldauth.= "rev0\t" . $this->trimp($row["REVUE_TITRE"]) . "\ttp\t" . $this->trimp($row["REVUE_TYPEPUB"]) . "\ted\t" . $this->trimp($row["EDITEUR_ID_EDITEUR"]) . "\t";
        $tp = $this->trimp($row["REVUE_TYPEPUB"]);

        //$datepub = $row['NUMERO_DATE_PARUTION'];
        // dp =>annee et plus date_parution.annee $fieldauth.= "**mvw0\t".trimp($row2["MOVINGWALL"])."\ttnp\t".trimp($row2["TYPE_NUMPUBLIE"])."\tdp\t".trimp(substr($row2['DATE_PARUTION'],0,4))."\ttitnum\t" . trimp($row2['TITRE'])."\t";;
        $fieldauth.= "**mvw0\t" . (($row["MOVINGWALL"]!= "" && $row["MOVINGWALL"]!="0000-00-00")?$this->trimp($row["MOVINGWALL"]):$this->trimp($row["NUMERO_MOVINGWALL"])) . "\ttnp\t" . $this->trimp($row["NUMERO_TYPE_NUMPUBLIE"])
                . "\tdp\t" . $this->trimp($row['NUMERO_ANNEE']) . "\ttitnum\t" . $this->trimp($row['NUMERO_TITRE']) . "\t";
        $tnp = (int) $this->trimp($row["NUMERO_TYPE_NUMPUBLIE"]);
        $fieldauth.= "**NUM0\t" . $this->trimp($row["NUMERO_NUMERO"]) . "\t**vol\t" . $this->trimp($row["NUMERO_VOLUME"]) . "\t**an\t"
                . $this->trimp($row["NUMERO_ANNEE"]) . "\ttitrech\t" . $this->trimp($row["NUMERO_TITRE_RECH"]) . "\t**tnp\t" . $this->trimp($tnp) . "\t";

        if (!(($tp == 1) && ($tnp == 0))) {
            $fields2 = "**pk0\t1"."\tMEMO\t".$this->trimp($row["NUMERO_MEMO"]);
        } else {
            $fields2 = "**pk0\t0";
        }

        $fields2 .= "\tnp\t$NP\t" . $fieldauth;

        if(Configuration::get('mode') == 'cairninter'){
            $efta = 0;
            if($row['LANGUE_INTEGRALE'] == 'en'){
                $efta = 2; //full-text
            }else{
                $resumeEN = $contentIdxer->checkResumeInter($row['ID_ARTICLE']);
                if($resumeEN == 1){
                    $efta = 1;
                }else{
                    $fields2.= "auteur\t" . $this->trimp(implode('|', $authorsPDF)) . "\t";
                    $fields2.= "Motscles\t" . $this->trimp($row['MOT_CLE']) . "\t";
                }
            }
            $fields2.= "efta\t" . $this->trimp($efta) . "\t";
        }else{
            if ($cfgaArr[1] == 0 && $cfgaArr[3] == 1) {
                $fields2.= "auteur\t" . $this->trimp(implode('|', $authorsPDF)) . "\t";
                $fields2.= "Motscles\t" . $this->trimp($row['MOT_CLE']) . "\t";
            }
        }

        $fields2 = (trim($fields2));

        return $fields2;
    }

    private function getFileName($article,$contentIdxer){
        if(Configuration::get('mode') == 'cairninter'){
            if($article['LANGUE_INTEGRALE'] == 'en'){
                $docname = Configuration::get('filePrefixTypeA')."_" . $article['ID_ARTICLE'] . ".htm";
                if (!file_exists(Configuration::get('prefixPath').$article['REVUE_ID_REVUE']."/".$article['NUMERO_ID_NUMPUBLIE']."/".$article['ID_ARTICLE']."/".$docname)){
                    $docname = "N_" . $article['ID_ARTICLE'] . ".htm";
                }
                $PATH = Configuration::get('prefixPath').$article['REVUE_ID_REVUE']."/".$article['NUMERO_ID_NUMPUBLIE']."/".$article['ID_ARTICLE']."/".$docname;
                if (!file_exists($PATH)) {
                    $idxid = 500;
                }else{
                    $idxid = Configuration::get('idxId');
                }
            }else{
                $resumeEN = $contentIdxer->checkResumeInter($article['ID_ARTICLE']);
                if($resumeEN == 1){
                    $docname = Configuration::get('filePrefixTypeR')."_" . $article['ID_ARTICLE'] . ".htm";
                    $PATH = Configuration::get('prefixPath').$article['REVUE_ID_REVUE']."/".$article['NUMERO_ID_NUMPUBLIE']."/".$article['ID_ARTICLE']."/".$docname;
                    if (!file_exists($PATH)) {
                        $idxid = 500;
                    }else{
                        $idxid = Configuration::get('idxId');
                    }
                }else{
                    $PATH = $article['ID_ARTICLE'];
                    //$idxid = 300; //c'est normal qu'on n'aie pas le fichier
                    $idxid = Configuration::get('idxId');
                }
            }
        }else{
            $cfgaArr = explode(',', $article['CONFIG_ARTICLE']);
            $ishtml = $cfgaArr[1];

            if ($ishtml) {
                $docname = Configuration::get('filePrefixTypeA')."_" . $article['ID_ARTICLE'] . ".htm";
                if (!file_exists(Configuration::get('prefixPath').$article['REVUE_ID_REVUE']."/".$article['NUMERO_ID_NUMPUBLIE']."/".$article['ID_ARTICLE']."/".$docname)){
                    $docname = "N_" . $article['ID_ARTICLE'] . ".htm";
                }
            } else if ($cfgaArr[3] == 1) {
                $docname = $article['ID_ARTICLE'] . ".PDF";
                if (!file_exists(Configuration::get('prefixPath').$article['REVUE_ID_REVUE']."/".$article['NUMERO_ID_NUMPUBLIE']."/".$article['ID_ARTICLE']."/".$docname)) {
                    //echo "Missing /rdata/cairn2014/data/pub/$ID_REVUE/$ID_NUMPUBLIE/$ID_ARTICLE/$docname";
                    $docname = $article['ID_ARTICLE'] . ".pdf";
                    //echo "\tfound $docname ?" . file_exists("/rdata/cairn2014/data/pub/$ID_REVUE/$ID_NUMPUBLIE/$ID_ARTICLE/$docname");
                }
            } else {
                $docname = Configuration::get('filePrefixTypeA')."_" . $article['ID_ARTICLE'] . ".htm";
            }

            $PATH = Configuration::get('prefixPath').$article['REVUE_ID_REVUE']."/".$article['NUMERO_ID_NUMPUBLIE']."/".$article['ID_ARTICLE']."/".$docname;
            if (!file_exists($PATH)) {
                $idxid = 500;
            }else{
                $idxid = Configuration::get('idxId');
            }
        }

        if($idxid == 500){
            Service::get('Mailer')->sendMailFromParams('Crawler: file not found', $PATH, 'benjamin.hennon@pythagoria.com', 'benjamin.hennon@pythagoria.com', 'Cairn Crawler');
        }

        return array($idxid,$PATH);
    }

    private function trimp($string) {
        $string = trim($string);
        $string = str_replace("\n", ' ', $string);
        $string = str_replace("\t", ' ', $string);
        $string = str_replace("\r", ' ', $string);
        $string = str_replace("  ", ' ', $string);
        $string = str_replace("  ", ' ', $string);
        $string = str_replace("  ", ' ', $string);
        $string = str_replace(chr(10), ' ', $string);
        $string = str_replace(chr(13), ' ', $string);
        $string = str_replace(chr(11), ' ', $string);
        $string = str_replace("   ", ' ', $string);
        $string = str_replace("   ", ' ', $string);

        if ($string == '')
            $string = ' - ';
        return $string;
    }
    
    public function generateurFreeArticlesFilter($batch = 0){
        $time_start = microtime(true);

        if($batch == 1){
            $filterPath = Configuration::get('filterPathBatch');
            $indexPath = Configuration::get('indexPathBatch');
        }else{
            $filterPath = Configuration::get('filterPath');
            $indexPath = Configuration::get('indexPath');
        }
        
        //Dans la liste des bouquets
        $freeArticles = $this->content->getFreeArticlesDocsId($time_start);
        $time_inter = microtime(true);
        echo "getFreeArticles:".($time_inter - $time_start);

        $applyFilter = $filterPath.'/cairnFreeArticles.flt';
        $request = array(
            "index" => $indexPath,
            "filterPath" => $applyFilter,
            "docsId" => array_map('intval',$freeArticles)
        );
        //var_dump($request);
        $filter = new Filter();
        $filter->genFilterByDocID($request);
        $time_inter2 = microtime(true);
        echo "filter ok:".($time_inter2 - $time_inter);
    }

    public function getCurrent(){
        echo Configuration::get('runningProcess');
    }

    public function addEvidensseInfos(){
        $file = $this->requete->getParametre('evidensseFile');
        $managerEvidensse = new ManagerEvidensse('dsn_evidensse');

        $row = 0;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {

                $managerEvidensse->updateDocsRsInfos($data[0],$data[1],$data[2]);
                $row++;
            }
            fclose($handle);
        }
        return $row;

    }


    /**
     * Les fonctions dédiées à l'envoi d'un mail quotidien aux éditeurs
     * qui proposent l'impression à la demande par eux-même.
     * Voir #94535 pour plus d'informations.
     */
    public function generatePrintOnDemandEmails() {
        // Récupération des données essentielles
        $ParseDatasService = Service::get('ParseDatas');
        $now = date('Y-m-d H:i:s');

        // On récupère la liste des revues concernées par la TVA presse réduite
        $revuesWithPressTVA = $this->content->getRevueWithPressTva();
        $listRevuesWithPressTVA = array();
        foreach ($revuesWithPressTVA as $revue) {
            $listRevuesWithPressTVA[$revue['ID_REVUE']] = $revue['DATE_FIN_CPPAP'];
        }
        $pressTVA = 2.1;

        $editeurs = explode(',', Configuration::get('batch_print_on_demande_id_editeurs'));
        $achats = $this->contentCom->getAchatsWithExernalPrintOnDemand($editeurs);

        // Aucun achats ne correspondant aux critères, on stop
        if (!count($achats)) die();

        // On parse les données
        $csv = [];
        $dates = [
            'most-early' => null,
            'most-recent' => null,
        ];
        $achatsToUpdate = [];
        foreach ($achats as $achat) {
            // On marque l'achat comme étant à mettre à jour
            // On enregistre en réalité la clé primaire pour la table ACHAT
            // ainsi que la date d'expédition
            array_push($achatsToUpdate, [
                $achat['ID_REVUE'],
                $achat['ID_NUMPUBLIE'],
                '', // ID_ARTICLE, qui est nécéssaire pour la clé primaire
                $achat['ID_USER'],
                $achat['NO_COMMANDE'],
                $achat['DATETIME'],
                $now, // La date d'expédition
            ]);
            unset($achat['ID_USER']);
            // On conserve la date la plus ancienne
            if ($dates['most-early'] === null) {
                $dates['most-early'] = $achat['DATETIME'];
            } elseif ($achat['DATETIME'] < $dates['most-early']) {
                $dates['most-early'] = $achat['DATETIME'];
            }
            // On conserve la date la plus récente
            if ($dates['most-recent'] === null) {
                $dates['most-recent'] = $achat['DATETIME'];
            } elseif ($achat['DATETIME'] > $dates['most-recent']) {
                $dates['most-recent'] = $achat['DATETIME'];
            }
            // On calcule la TVA
            $commandeDepuisFrance = ($achat['PAYS'] === 'France') || !$achat['PAYS'];
            $tvaCE = $this->content->isPaysEU($achat['PAYS']);
            $tva = 0;
            if ($tvaCE) {
                $tva = 5.5;
                $isRevueWithPressTVA = isset($listRevuesWithPressTVA[$achat['ID_REVUE']]);
                if ($isRevueWithPressTVA) {
                    $isRevueWithPressTVA = $achat['DATETIME'] <= $listRevuesWithPressTVA[$achat['ID_REVUE']];
                }
                if ($isRevueWithPressTVA) {
                    $tva = $pressTVA;
                }
            }
            // On calcule les prix suivants la TVA
            $achat['PRIX_HT'] = round(floatval($achat['PRIX_TTC'] / (1 + ($tva/100))),2);
            $achat['TAUX_TVA'] = $tva;
            array_push($csv, $achat);
        }

        // Transformation en csv réel
        $csv = $ParseDatasService::arrayToCSV($csv);
        $csv = implode("\n", $csv);
        // On affiche le csv formatté en html
        echo $ParseDatasService::formatCSVToHTML($csv);

        // Pour envoyer le mail, il faut l'avoir explicitement dit dans l'url
        if ($this->requete->getParametre('send-mail', false) !== '1') die();
        echo '<h2>Envoi du mail <small>(du '.$dates['most-early'].' au '.$dates['most-recent'].')</small></h2>';

        // On envoi le mail à destination des éditeurs
        $date = explode(' ', $dates['most-early'])[0];
        $subject = 'Commande d\'exemplaires de revues PUF sur Cairn.info - ' . $date;
        $body = "Bonjour\n\nCi-joint le détail des ".count($achats). " achats d'exemplaires papier de revues des Presses Universitaires de France effectués sur Cairn.info depuis le " . $date . ".";
        $from = Configuration::get('batch_print_on_demand_email_from');
        $fromName = '';
        $to = Configuration::get('batch_print_on_demand_email_to');
        $attachs = ['achats_'.$date.'_cairn-info.csv' => $csv];
        Service::get('Mailer')->sendMailFromParams($subject, $body, $to, $from, $fromName, $attachs);

        // Pour modifier la colonne EXPEDIE_LE, il faut l'avoir précisement explicité dans l'url
        if ($this->requete->getParametre('set-expedition-date', false) !== '1') die();
        echo "<h2>Mise à jour de EXPEDIE_LE</h2>";

        foreach ($achatsToUpdate as $achat) {
            var_dump($achat);
            call_user_func_array([$this->managerCom, "updateDateExpeditionAchatPapier"], $achat);
        }
    }
    
    public function generateAccessFilterForInst(){
        //0- On regénère le filtre des gratuits
        $this->generateurFreeArticlesFilter(1);
        
        //1- On sélectionne les institutions
        $insts = $this->contentCom->getInstitutions();
        
        //2- On vide le cache redis des filtres (version batch)
        exec("redis-cli -n ".Configuration::get("redis_db_user_batch")." flushdb");
                
        //2- Pour chacune, on fait un call au service de génération de filtre
        foreach($insts as $inst){
            echo "\n Generating filter for ".$inst['ID_USER'];
            Service::get("Authentification")->genFilter($inst["ID_USER"],null,1,1);
        }
    }
    
    public function doCryptPasswords(){
        ini_set('memory_limit','2048M');
        set_time_limit(1200);
        
        $insts = $this->contentCom->getInstitutions();
        foreach($insts as $inst){
            if($inst["PWD_LOG"] != "" && $inst["PWD_LOG_CR"] == ""){
                $crypted = Service::get("CairnToken")->cryptMp($inst["PWD_LOG"]);
                $this->managerCom->saveCryptedInstMP($inst["ID_USER"],$crypted);
            } 
        }
        $particuliers = $this->contentCom->getParticuliers();
        foreach($particuliers as $particulier){
            if($particulier["MOT_PASSE"] != "" && $particulier["MOT_PASSE_CR"] == ""){
                $crypted = Service::get("CairnToken")->cryptMp($particulier["MOT_PASSE"]);
                $this->managerCom->saveCryptedPartMP($particulier["ID_USER"],$crypted);
            } 
        }
    }       
}

