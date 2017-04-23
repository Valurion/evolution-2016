<?php

/**
 * Ce service prend en charge les vérifications liées aux possibilités d'achat sur une page
 *
 * @author benjamin
 */
class Ogone {

    private $staticOptions = [
        "BGCOLOR" => "#FFFFFF",
        "TXTCOLOR" => "#727255",
        "TBLBGCOLOR" => "#E1E1C2",
        "TBLTXTCOLOR" => "#727255",
        "BUTTONBGCOLOR" => "#727255",
        "BUTTONTXTCOLOR" => "#FFFFFF",
        "FONTTYPE" => "Trebuchet MS",
        "LOGO" => "CairnHL_r1_c1.jpg",
        "PM" => "CreditCard",
        "ERROR_ACHAT" => "1"
    ];

    public function getOgoneInputs($commandeTmp) {
        if(Configuration::get('ogoneMode') == 'old'){
            return $this->getOgoneInputsOldVersion($commandeTmp);
        }else{
            $urlSite = Configuration::get('urlSite');
            $shasig = Configuration::get('ogone_shasig');

            $returnArray = [
                "ORDERID" => $commandeTmp['ID_COMMANDE'],
                "AMOUNT" => (floatval($commandeTmp['PRIX']) + floatval($commandeTmp['FRAIS_PORT'])) * 100,
                "ACCEPTURL" => "http://" . $urlSite . "/landing_ogone.php?NO_COMMANDE=" . $commandeTmp['ID_COMMANDE'],
                "DECLINEURL" => "http://" . $urlSite . "/landing_ogone_erreur.php",
                "EXCEPTIONURL" => "http://" . $urlSite . "/landing_ogone_erreur.php",
                "CANCELURL" => "http://" . $urlSite . "/landing_ogone_erreur.php",
                "HOMEURL" => "http://" . $urlSite,
                "CURRENCY" => "EUR",
                "LANGUAGE" => "fr_FR",
                "PSPID" => Configuration::get('ogone_pspid'),
            ];
            ksort($returnArray);

            $shasign = "";
            foreach ($returnArray as $key => $value) {
                $shasign .= $key . "=" . $value . $shasig;
            }
            $shasign = strtoupper(sha1($shasign));
            $returnArray['SHASIGN'] = $shasign;

            return $returnArray;
        }
    }

    public function getOgoneInputsOldVersion($commandeTmp) {
        $urlSite = Configuration::get('urlSite');
        $shasig = Configuration::get('ogone_shasig');

        $returnArray = [
            "orderID" => $commandeTmp['ID_COMMANDE'],
            "amount" => (floatval($commandeTmp['PRIX']) + floatval($commandeTmp['FRAIS_PORT'])) * 100,
            "currency" => "EUR",
            "language" => "fr_FR",
            "accepturl" => "http://" . $urlSite . "/landing_ogone.php?NO_COMMANDE=" . $commandeTmp['ID_COMMANDE'],
            "declineurl" => "http://" . $urlSite . "/landing_ogone_erreur.php",
            "exceptionurl" => "http://" . $urlSite . "/landing_ogone_erreur.php",
            "cancelurl" => "http://" . $urlSite . "/landing_ogone_erreur.php",
            "homeurl" => "http://" . $urlSite,
            "CN" => $commandeTmp['FACT_NOM'],
            "EMAIL" => $commandeTmp['ID_USER'],
            "PSPID" => Configuration::get('ogone_pspid'),
            "COM" => "Achat de la commande ".$commandeTmp['ID_COMMANDE']
        ];
        $returnArray = array_merge($returnArray, $this->staticOptions);

        $amountTot = (floatval($commandeTmp['PRIX']) + floatval($commandeTmp['FRAIS_PORT'])) * 100;
        $shasign = $commandeTmp['ID_COMMANDE'].$amountTot.'EUR'.Configuration::get('ogone_pspid').Configuration::get('ogone_shasig');
        $shasign = sha1($shasign);
        $returnArray['SHASign'] = $shasign;

        return $returnArray;
    }

    public function checkPostSaleIP($requete) {
        //return true;
        
        $ipClient = "";
        if($requete->existeParametre('X-Forwarded-For')){
            $ipClient = $requete->getParametre('X-Forwarded-For');
        }else if($requete->existeParametre('HTTP_X_FORWARDED_FOR')){
            $ipClient = $requete->getParametre('HTTP_X_FORWARDED_FOR');
        }else {
            $ipClient = $requete->getParametre('REMOTE_ADDR');
        }
        ereg("^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$", $ipClient, $ip_parts);

        $ogoneIps = explode(',', Configuration::get('ogone_knownIps'));

        foreach($ogoneIps as $ogoneIp){
            ereg("^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)\/([0-9]+)$", $ogoneIp, $ogone_parts);
            $mask = $ogone_parts[5];
            $nbIps = pow(2,32-$mask)-1;   // nombre d'IP possibles
            if ($ip_parts[4] >= $ogone_parts[4] && $ip_parts[4] <= $ogone_parts[4]+$nbIps &&
                $ip_parts[1] == $ogone_parts[1] && $ip_parts[2] == $ogone_parts[2] && $ip_parts[3] == $ogone_parts[3]) {

              return true;
            }
        }

        return false; 
    }

    public function checkShaOut($requete) {
        if(Configuration::get('ogoneMode') != 'old'){
            $ogoneparams = [
                "ORDERID" => $requete->getParametre('orderID'),
                "CURRENCY" => $requete->getParametre('currency'),
                "AMOUNT" => $requete->getParametre('amount'),
                "PM" => $requete->getParametre('PM'),
                "ACCEPTANCE" => $requete->getParametre('ACCEPTANCE'),
                "STATUS" => $requete->getParametre('STATUS'),
                "CARDNO" => $requete->getParametre('CARDNO'),
                "ED" => $requete->getParametre('ED'),
                "CN" => $requete->getParametre('CN'),
                "TRXDATE" => $requete->getParametre('TRXDATE'),
                "PAYID" => $requete->getParametre('PAYID'),
                "NCERROR" => $requete->getParametre('NCERROR'),
                "BRAND" => $requete->getParametre('BRAND'),
                "IP" => $requete->getParametre('IP')
            ];

            $ogoneShaOut = $requete->getParametre('SHASIGN');

            $shastring = "";
            ksort($ogoneparams);
            foreach ($ogoneparams as $key => $value) {
                $shastring .= strtoupper($key) . "=" . $value . Configuration::get('ogone_shasig');
            }
            $ourShasign = strtoupper(sha1($shastring));

            return ($ogoneShaOut == $ourShasign ? true : false);
        }else{
            return true;
                        
            $ogoneparams = [
                "ORDERID" => $requete->getParametre('orderID'),
                "CURRENCY" => $requete->getParametre('currency'),
                "AMOUNT" => $requete->getParametre('amount'),
                "PM" => $requete->getParametre('PM'),
                "ACCEPTANCE" => $requete->getParametre('ACCEPTANCE'),
                "STATUS" => $requete->getParametre('STATUS'),
                "CARDNO" => $requete->getParametre('CARDNO'),                
                "PAYID" => $requete->getParametre('PAYID'),
                "NCERROR" => $requete->getParametre('NCERROR'),
                "BRAND" => $requete->getParametre('BRAND')                
            ];

            $ogoneShaOut = $requete->getParametre('SHASIGN');

            $shastring = "";
            foreach ($ogoneparams as $key => $value) {
                $shastring .= $value;
            }
            $shastring .= Configuration::get('ogone_shasig');
            
            $ourShasign = strtoupper(sha1($shastring));
            
            return ($ogoneShaOut == $ourShasign ? true : false);            
        }
    }

}
