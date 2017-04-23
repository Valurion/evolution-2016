<?php
require_once 'Framework/Controleur.php';
require_once 'Modele/Search.php';
require_once 'Controleur/ControleurUser.php';
require_once 'Service/Ogone.php';
require_once 'Modele/Content.php';

class ControleurTest extends Controleur{
    
    public function index(){
    }
    
    public function testCtheque(){
        if($this->requete->existeParametre('casid') && $this->requete->existeParametre('ticket')){
            $casid = $this->requete->getParametre('casid');
            $ticket = $this->requete->getParametre('ticket');
            $idArticle = $this->requete->getParametre('ID_ARTICLE');
            
                     
            $urlValid = 'http://www.culturetheque.com/EXPLOITATION/Default/validate.aspx?service='.urlencode("http://".Configuration::get('urlSite')."/article.php?ID_ARTICLE=".$idArticle."&casid=".$casid."&ticket=".$ticket);
            //appel curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlValid);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

            $response = (curl_exec($ch));
            //$info=curl_getinfo($ch);
            curl_close($ch);

            //print_r($info);
            var_dump($response);
            if($response == 'no|'){
                echo 'No access';
                return;
            }else{
                echo 'access ok, login ctheque';
            } 
        }
    }
    
    public function getEmailCC(){
        $content = new Content('dsn');
        $emailsCC = $content->getUsersCairn();
       // var_dump($emailsCC);
        $toCc = array();
            foreach ($emailsCC as $emailCC){
               $toCc[] = $emailCC['ID_USER'];
            }
            var_dump($toCc);
            
            Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirmChequeVirCredit.xml',
                            array('to' => 'benjamin.hennon@pythagoria.com', 'NO_COMMANDE' => 'test123',
                                'ID_USER' => 'usertest123', 'toCc' => $toCc,
                                'URLSITE' => 'www.localcairn.lu'),
                            array("Bon-de-commande.html" => 'bdc')
                            );
    }
    
    public function testToken(){
        $toCrypt = array("ben.hennon@gmail.com","deuxième champ","troisième champ");
        echo "To crypt :";
        var_dump($toCrypt);
        $token = Service::get('CairnToken')->crypt($toCrypt,3600*12);
        echo '<br/>Token :';
        var_dump($token);
        echo "<br/>Decrypted :";
        $decrypted = Service::get('CairnToken')->decrypt($token);
        var_dump($decrypted);
    }
    public function getToken(){
        $toCrypt = array("ben.hennon@gmail.com");
        echo "To crypt :";
        var_dump($toCrypt);
        $token = Service::get('CairnToken')->crypt($toCrypt,3600*12);
        echo '<br/>Token :'.$token."<br/>urlencode(Token) :".urlencode($token);
    }
    public function readToken(){
        echo "Token :".$_GET['token']."<br/>Decrypted :";
        var_dump(Service::get('CairnToken')->decrypt($_GET['token']));
    }
    
    public function dateAndTime(){
        echo date('Y-m-d H:i:s');
        echo '<br/>';
        echo time();
    }

    public function testSearchPdf(){
        //$idArticle = $this->requete->getParametre('ID_ARTICLE');
        $idArticle = 'DEC_BUIRE_2008_01_0003';
        $indexes = array(Configuration::get("indexPdfPath"));
        $searchTerm = "L'important";
        //echo Configuration::get("indexPdfPath");
       /* $searchT = array('request' => $searchTerm, 
                        'method' => 'search', 
                        'index' => $indexes,
                        'booleanCondition' => ''
                        /*'booleanCondition' => '(xfilter (word \"id::'.$idArticle.'\"))');*/
        $searchT = array(
            'request' => $searchTerm, 
            'method' => 'search', 
            'startAt' => 0, 
            'expander' => array("family"),
            "index" => $indexes, 
            "booleanCondition" => '(xfilter (word \"id::'.$idArticle.'\"))');
        var_dump($searchT);   
        
        $client = new Search();
        //var_dump($searchT);
        $htmlDatas = $client->doSearch($searchT);
        var_dump($htmlDatas);
    }
    public function testGetPdfHilight(){
        //$docId = $this->requete->getParametre('ID_ARTICLE');
        
        $searchT="DocId=1"
                ."&Index=".Configuration::get('indexPdfPath')
                ."&hits=27 26";
        /*if($this->requete->existeParametre('hits')){
            $arr_hits = explode(' ', $this->requete->getParametre('hits'));
            foreach ($arr_hits as $cur_hit) {
                if (!trim($cur_hit) == "0")
                    $hits_local .= dechex($cur_hit) . '+';
            }
            $searchT .= "&hits=".$hits_local;
        }*/

        $client = new Search();
        //var_dump($searchT);
        $htmlDatas = $client->doGetHilightPdf($searchT);
        echo '<pre>';
        $htmlDatas = str_replace(Chr(13),'',$htmlDatas);
        $htmlDatas = str_replace(Chr(10),'',$htmlDatas);
        $htmlDatas = str_replace('<','&lt;',$htmlDatas);
        $htmlDatas = str_replace('>','&gt;',$htmlDatas);
        
        $htmlDatas = preg_replace('/units=(.*)\scolor=(.*)\smode=(.*)\sversion=([0-9]*)\&gt;/','units="$1" color="$2" mode="$3" version="$4"&gt;',$htmlDatas);
        $htmlDatas = preg_replace('/pg=(.*)\spos=(.*)\slen=([0-9]*)\&gt;/','pg="$1" pos="$2" len="$3"&gt;&lt;/loc&gt;',$htmlDatas);
        
        var_dump($htmlDatas);
        echo '</pre>';
    }
    
    public function testCredits(){
        $credits = [
            [
                "PRIX" => "50",
                "EXPIRATION_CREDIT" => "2014-11-01"
            ],
            [
                "PRIX" => "50",
                "EXPIRATION_CREDIT" => "2014-12-01"
            ],
            [
                "PRIX" => "50",
                "EXPIRATION_CREDIT" => "2014-12-15"
            ],
            [
                "PRIX" => "50",
                "EXPIRATION_CREDIT" => "2015-01-01"
            ],            
        ];
        $achats = [
            [
                "PRIX" => "20",
                "DATE" => "2014-10-02"
            ],
            [
                "PRIX" => "30",
                "DATE" => "2014-10-10"
            ],
            [
                "PRIX" => "50",
                "DATE" => "2014-11-02"
            ],
            [
                "PRIX" => "20",
                "DATE" => "2014-12-02"
            ],
        ];
        
        ControleurUser::rechercheCreditAchatDispo($credits,$achats);
        
    }
    
    public function testIP(){
        $ipClient = '212.35.124.164';
       // $ipClient = '127.0.0.1';
        ereg("^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$", $ipClient, $ip_parts);

        $ogoneIps = explode(',', Configuration::get('ogone_knownIps'));

        foreach($ogoneIps as $ogoneIp){
            ereg("^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)\/([0-9]+)$", $ogoneIp, $ogone_parts);
            $mask = $ogone_parts[5];
            $nbIps = pow(2,32-$mask)-1;   // nombre d'IP possibles
            if ($ip_parts[4] >= $ogone_parts[4] && $ip_parts[4] <= $ogone_parts[4]+$nbIps &&
                $ip_parts[1] == $ogone_parts[1] && $ip_parts[2] == $ogone_parts[2] && $ip_parts[3] == $ogone_parts[3]) {

              echo 'OK !';
              return;
            }
        }
        echo 'KO !';
    }
    
    public function testOgoneShaOutOld(){
        
        $ogoneparams = [
                "ORDERID" => "201503140740-8482138",
                "CURRENCY" => "EUR",
                "AMOUNT" => "2.5",
                "PM" => "CreditCard",
                "ACCEPTANCE" => "test123",
                "STATUS" => "5",
                "CARDNO" => "XXXXXXXXXXXX1111",
                //"ED" => "0317",
                //"CN" => "Benjamin Hennon",
                //"TRXDATE" => "03/14/15",
                "PAYID" => "40163014",
                "NCERROR" => "0",
                "BRAND" => "VISA",
                //"IP" => "81.240.106.79"
            ];

            $ogoneShaOut = "1778C840366C0E30318D2C9A2414E9BA9CFC41DA";

            $shastring = "";
            //ksort($ogoneparams);
            foreach ($ogoneparams as $key => $value) {
                //$shastring .= strtoupper($key) . "=" . $value . "sha out phrase de test";
                $shastring .= $value;
            }
            $shastring .= "sha out phrase de test";
            $ourShasign = strtoupper(sha1($shastring));
            echo $ogoneShaOut."<br/>".$ourShasign;
            echo ($ogoneShaOut == $ourShasign ? "true!" : "false!");
        /*$returnparams = [
            "orderID"   => "201503140740-8482138",
            "currency"  => "EUR",
            "amount"    => "2.5",
            "PM"        => "CreditCard",
            "ACCEPTANCE"=> "test123",
            "STATUS"    => "5",
            "CARDNO"    => "XXXXXXXXXXXX1111",
            "ED"        => "0317",
            "CN"        => "Benjamin Hennon",
            "TRXDATE"   => "03/14/15",
            "PAYID"     => "40163014",
            "NCERROR"   => "0",
            "BRAND"     => "VISA",
            "IPCTY"     => "BE",
            "CCCTY"     => "US",
            "ECI"       => "12",
            "CVCCheck"  => "NO",
            "AAVCheck"  => "NO",
            "VC"        => "NO",
            "IP"        => "81.240.106.79"
           // "SHASIGN"   => "1778C840366C0E30318D2C9A2414E9BA9CFC41DA"
        ];

        $ogoneShasign = '1778C840366C0E30318D2C9A2414E9BA9CFC41DA';
        
        $shastring = "";
        //ksort($returnparams);
        foreach ($returnparams as $key => $value){
            //$shastring .= strtoupper($key)."=".$value."sha out phrase de test";
            $shastring .= $key."=".$value."sha out phrase de test";
        }
        //$shastring = "201503140740-8482138"."250"."EUR"."Cairn"."sha out phrase de test";
        echo $shastring.'<br/>';
        $ourShasign = strtoupper(sha1($shastring));
        
        echo $ogoneShasign." >< ".$ourShasign." : ".($ogoneShasign==$ourShasign?"TRUE":"FALSE");
        */
        
        
    }
    
    public function testOgoneShaOut(){
        $returnparams= [
            "OrderID"=>"20141103-6219928",
            "Currency"=>'EUR',
            "Amount"=>'2.5',
            "PM"=>'CreditCard',
            "ACCEPTANCE"=>'test123',
            "STATUS"=>9,
            "CARDNO"=>'XXXXXXXXXXXX1111',
            "ED"=>'0215',
            "CN"=>'BH',
            "TRXDATE"=>'11/03/14',
            "PAYID"=>'36539141',
            "NCERROR"=>'0',
            "BRAND"=>'VISA',
            "IP"=>'88.207.200.30',
          //  'NO_COMMANDE'=>'20141103-6219928'
        ];
        
        $ogoneShasign = '04533499ADFB215A23383B8F20F729E89B06907C';
        
        $shastring = "";
        ksort($returnparams);
        foreach ($returnparams as $key => $value){
            $shastring .= strtoupper($key)."=".$value.Configuration::get('ogone_shasig');
        }
        echo $shastring.'<br/>';
        $ourShasign = strtoupper(sha1($shastring));
        
        echo $ogoneShasign." >< ".$ourShasign." : ".($ogoneShasign==$ourShasign?"TRUE":"FALSE");
        
        
    }
    
    public function testPthFilters(){
        require_once 'Modele/Filter.php';
        $applyFilter = Configuration::get('filterPath')."/".$this->authInfos['I']['ID_USER'].'.flt';
        $request = array(
            "index" => Configuration::get('indexPath'),
            "filterPath" => $applyFilter,
            "docsId" => array(1,2,3,4,5,6,7,8,9,10)
            //"docsId" => $this->redisClient->smembers($this->authInfos['I']['ID_USER'])
        );
        var_dump($request);
        $filter = new Filter();
        $ok = $filter->genFilter($request);
        var_dump($ok);
    }

    public function testPthTranslate(){
        require_once 'Modele/Translator.php';
        $searchTerm = $this->requete->getParametre('searchTerm');
        echo $searchTerm;
        $translator = new Translator();
        $result = $translator->translate($searchTerm);
        var_dump($result);
        $boolOperator=') W/5 (';
        $searchTermTranslated= urlencode (  '(' .  implode($boolOperator, $result)   .  ')'  ); 
        var_dump($searchTermTranslated);
        $newUrl='http://'.Configuration::get('crossDomainUrl').'/resultats_recherche.php?searchTerm='.$searchTermTranslated;
        
        header('Location: '.$newUrl);
    }
    
    public function testUpdateMongoWithDates(){
        require_once("Modele/ManagerComMongo.php");
        $manager = new ManagerComMongo(Configuration::get("dsn_com_mongo"));
        
        $manager->incrementDayCounter('2017022014876255711024');
        
    }
}
