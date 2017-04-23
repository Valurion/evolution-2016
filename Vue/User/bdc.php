<?php 
function getBdc($idCommande,$commandeTmp,$authInfos, $arrayAbos, $arrayNums, $arrayNumsElec, $arrayArts, $arrayCredits){
    $bdcContent = '<html><head>
            <title>CAIRN.INFO &mdash; Bon de commande</title>
            <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
            <style type="text/css">
                p {font-family: Georgia; font-size: 12px;}
                h1 {font-family: Georgia; font-size: 18pt; }
                td.gauche {font-family: Georgia; font-size: 14px; font-variant: small-caps}
                td.droite {font-family: Georgia; font-size: 14px; font-weight: bold}
                td.texte {font-family: Georgia; font-size: 14px; }
                td.entete1 {font-family: Georgia; font-size: 14px; font-weight: bold}
                td.entete2 {font-family: Georgia; font-size: 14px; font-weight: bold}
                td.entete3 {font-family: Georgia; font-size: 14px; text-align: right; font-weight: bold; padding-right: 10px;}
                td.texte3 {text-align: right; padding-right: 10px;
                </style>
            </head>


            <body><table border="0" align="left" width="700" cellspacing="5" cellpadding="5">
                    <tbody><tr><td class="blanc"><img src="http://www.cairn.info/img/CAIRN.INFO.png"></td></tr>
                        <tr height="35px">
                            <td valign="top"> <h2 align="center">BON DE COMMANDE<br><small>n° '. $idCommande .'</small></h2>
                                <p align="center">à imprimer et à retourner, accompagné de votre règlement, à :<br><br><strong>CAIRN.INFO, 26, Rue Édouard-Lockroy, 75011 PARIS, France</strong></p>
                                <p> </p>
                                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                    <tbody><tr><td colspan="2"><hr></td></tr>
                                        <tr height="25px"><td class="texte" colspan="2"><u>Mes coordonnées</u></td></tr>
                        <tr height="25px">
                            <td class="gauche">Identifiant</td>
                            <td class="droite">'. $authInfos['U']['ID_USER'] .'</td>
                        </tr>
                        <tr height="25px">
                            <td class="gauche">Nom</td>
                            <td class="droite">'. $commandeTmp['PRENOM'] .' '. $commandeTmp['NOM'] .'</td>
                        </tr>
                        <tr height="25px">
                            <td class="gauche">Adresse email</td>
                            <td class="droite">'. $authInfos['U']['EMAIL'] .'</td>
                        </tr>
                        <tr height="25px">
                            <td class="gauche">Adresse postale</td>
                            <td class="droite">'. $commandeTmp['ADRESSE'] .'</td>
                        </tr>
                        <tr height="25px">
                            <td class="gauche">CP - Ville - Pays</td>
                            <td class="droite">'. $commandeTmp['CP'] .' &ndash; '. $commandeTmp['VILLE'] .' - '. $commandeTmp['PAYS'] .'</td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr></tbody></table>

                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tbody><tr height="25px">
                            <td class="entete1">Designation</td>
                            <td class="entete2">ID</td>
                            <td class="entete3">Prix TTC</td></tr>';
                        foreach($arrayCredits as $credit){
                            $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Crédit d\'articles (valable pour tous les contenus diffusés sur Cairn.info)</td>
                                    <td class="texte3">'. $credit.' €</td>
                                </tr>';                    
                         }    
                        foreach($arrayArts as $art){
                            if($art['REVUE_TYPEPUB'] == 1){
                               $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Article : « '. $art['ARTICLE_TITRE'] .' »<br>Revue : <em>'. $art['REVUE_TITRE'] .'</em>, n° '. $art['NUMERO_ANNEE'] .'/'. $art['NUMERO_NUMERO'] .'</td>
                                    <td class="texte3">'. $art['ARTICLE_PRIX'] .' €</td>
                                </tr>';
                            }else if($art['REVUE_TYPEPUB'] == 2){
                                $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Article : « '. $art['ARTICLE_TITRE'] .' »<br>Magazine : <em>'. $art['REVUE_TITRE'] .'</em>, n° '. $art['NUMERO_ANNEE'] .'/'. $art['NUMERO_NUMERO'] .'</td>
                                    <td class="texte3">'. $art['ARTICLE_PRIX'] .' €</td>
                                </tr>';
                            }else{ 
                                $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Contribution : « '. $art['ARTICLE_TITRE'] .' »<br>dans : <em>'. $art['NUMERO_TITRE'] .'</em></td>
                                    <td class="texte3">'. $art['ARTICLE_PRIX'] .' €</td>
                                </tr>';
                                /*<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Chapitre : « [TITRE_ART] »<br>dans : <em>[TITRE_NUM]</em> (Collection "Que sais-je ?")</td>
                                    <td class="texte3">[PRIX] €</td>
                                </tr>*/
                            }
                        }
                        foreach($arrayNumsElec as $num){
                            if($num['REVUE_TYPEPUB'] == 1 || $num['REVUE_TYPEPUB'] == 2){
                                $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">'. ($num['REVUE_TYPEPUB']==1?'Revue':'Magazine').' : <em>'. $num['REVUE_TITRE'] .'</em><br>Numéro '. $num['NUMERO_ANNEE'] .'/'. $num['NUMERO_NUMERO'] .'<br>Titre : '. $num['NUMERO_TITRE'] .'<br>(Achat électronique seulement)</td>
                                    <td class="texte3">'. $num['NUMERO_PRIX'] .' €</td>
                                </tr>';                       
                            }else{ 
                                $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Ouvrage : <em>'. $num['NUMERO_TITRE'] .'</em><br>(Coll. "'. $num['REVUE_TITRE'] .'")<br>(Achat électronique seulement)</td>
                                    <td class="texte3">'. $num['NUMERO_PRIX'] .' €</td>
                                </tr>';                        
                            }
                        } 
                        foreach($arrayNums as $num){
                            if($num['REVUE_TYPEPUB'] == 1 || $num['REVUE_TYPEPUB'] == 2){
                                $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">'. ($num['REVUE_TYPEPUB']==1?'Revue':'Magazine').' : <em>'. $num['REVUE_TITRE'] .'</em><br>Numéro '. $num['NUMERO_ANNEE'] .'/'. $num['NUMERO_NUMERO'] .'<br>Titre : '. $num['NUMERO_TITRE'] .'<br>+ frais de port</td>
                                    <td class="texte3">'. $num['NUMERO_PRIX'] .' €</td>
                                </tr>';                        
                            }else{
                                $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Ouvrage : <em>'. $num['NUMERO_TITRE'] .'</em><br>(Coll. "'. $num['REVUE_TITRE'] .'")<br>+ frais de port</td>
                                    <td class="texte3">'. $num['NUMERO_PRIX'] .' €</td>
                                </tr>';                        
                            }
                        } 
                        foreach($arrayAbos as $abo){
                            $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr>
                                    <td width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte1"> </td>
                                    <td width="55%" valign="top" style="font-family: \'Trebuchet MS\'; font-size:10pt;" class="texte2">Revue : <em>'. $abo['TITRE'].'</em><br> Formule : '. $abo['LIBELLE'].'<br>'. $abo['INFOSUP'].'<br>+ frais de port</td>
                                    <td class="texte3">'. $abo['PRIX'].' €</td>
                                </tr>';                    
                         }
                        $bdcContent.='<tr><td colspan="3"><hr></td></tr><tr><td colspan="3"><hr></td></tr>
                        <tr>
                            <td class="texte1"><b>MONTANT TOTAL </b></td>
                            <td class="texte2"> </td>
                            <td class="texte3"><b>'. (floatval($commandeTmp['PRIX'])+  floatval($commandeTmp['FRAIS_PORT'])).' €</b><br/>dont frais de port: '. $commandeTmp['FRAIS_PORT'].' €</td>
                        </tr>
                        <tr><td colspan="3"><hr></td></tr>
                    </tbody></table>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tbody><tr><td class="texte" colspan="2"><u><br>Je joins au présent courrier un chèque de '. (floatval($commandeTmp['PRIX'])+  floatval($commandeTmp['FRAIS_PORT'])).' € à l’ordre de « CAIRN S.A. », ou une attestation de virement.</u><br>(Nos coordonnées bancaires : <a title="Fichier PDF. Nouvelle fenêtre" target="_blank" href=" http://www.cairn.info/docs/RIB.pdf ">http://www.cairn.info/docs/RIB.pdf</a><a>).</a></td></tr>
            <tr valign="bottom" height="90px">
                <td align="center"><b>Date</b> </td>
                <td align="center"><b>Signature</b> </td>
            </tr>';
            if ($commandeTmp['FACT_ADR'] != $commandeTmp['ADRESSE']) { 
                $bdcContent.='<tr><td colspan="2"><br><hr></td></tr>
                <tr height="35px"><td class="texte" colspan="2"><b>ADRESSE DE FACTURATION</b> (si différente de l’adresse de livraison).</td></tr>
                <tr height="35px">
                    <td class="gauche">Société ou institution</td>
                    <td class="droite">'. $commandeTmp['FACT_NOM'] .'</td>
                </tr>
                <tr height="35px">
                    <td class="gauche">Adresse postale</td>
                    <td class="droite">'. $commandeTmp['FACT_ADR'] .'</td>
                </tr>
                <tr height="35px">
                    <td class="gauche">CP - Ville - Pays</td>
                    <td class="droite">'. $commandeTmp['FACT_CP'] .' &ndash; '. $commandeTmp['FACT_VILLE'] .' - '. $commandeTmp['FACT_PAYS'] .'</td>
                </tr>';
            }
            $bdcContent.='<tr><td colspan="2"><hr></td></tr>
        </tbody></table>
    </td></tr>
    </tbody></table>
    </body></html>';
        
    return $bdcContent;
}

