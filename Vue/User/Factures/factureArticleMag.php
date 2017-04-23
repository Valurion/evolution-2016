<?php

function getFactureArticleMag($idCommande, $commandeTmp, $arrayArt, $prices, $tauxTVA, $tauxTVAreduit){

    $str = '<html>
            <head>
            <title>Facture Cairn.info n° '.$idCommande.'</title>
            <META http-equiv=Content-Type content="text/html; charset=UTF-8">
            <style>
            table.tabfac {margin-top: 20pt; border-left: 1px solid black; width: 600pt; }
            table.tabbas {border: 1px solid black;}
            td {border-top: 1px solid black; border-right: 1px solid black;}
            td.videf {border-top: 1px solid black; border-left: 0px; border-right: 1px solid black;}
            td.desc {border-top: 1px solid black; border-right: 0px;}
            td.d {border-top: 1px solid black; border-right: 1px solid black; border-left: 1px solid black;}
            td.ds {border-top: 0px solid black; border-right: 0px solid black; border-left: 0px solid black;}
            td.blanc {border-top: 0px; border-right: 0px;}
            td.comp {border-top: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black;}
            td.descomp {border-top: 1px solid black; border-bottom: 1px solid black; border-right: 0px solid black;}
            td.compd {border-top: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; border-left: 1px solid black;}
            </style>
            <body>
            <table width="600pt" border="0" style="width:600pt; margin-top: 20pt;">
            <tr><td class="blanc"><img src="http://www.cairn.info/img/CAIRN.INFO.png"></td></tr>
            <tr><td class="blanc" style="font-family: \'Trebuchet MS\'; font-size: 8pt;" width="60%">Cairn S.A<br>26, Rue Édouard-Lockroy, <br>75011 Paris -  France<br>Tél. +33 1 55 28 83 00 /  Fax +33 1 55 28 35 33<br><a href="mailto:cairn@cairn.info">cairn@cairn.info</a></td>
            <td class="blanc" style="font-family: \'Trebuchet MS\'; font-size: 10pt;" width="40%">
            <br>'.$commandeTmp['FACT_NOM'].'<br>'.$commandeTmp['FACT_ADR'].'<br>'.$commandeTmp['FACT_CP'].' '.$commandeTmp['FACT_VILLE'].'<br>'.$commandeTmp['FACT_PAYS'].'</td></tr></table><br><br>
            <table cellpadding=3 cellspacing=0 width="600pt" class="tabfac">
            <tr>
            <td rowspan="3" colspan="2" style="width:180px; font-family: \'Trebuchet MS\'; font-size:10pt;">ID de client : '.$commandeTmp['ID_USER'].'</td>
            <td align="center" colspan="3" width="60%" style="font-family:\'Trebuchet MS\'; font-size:\'15pt\';"><b>FACTURE</b></td>
            </tr>
            <tr>
            <td align="center" width="30%" style="font-family:\'Trebuchet MS\'; font-size:10pt; align:\'center\';">Date</td>
            <td align="center" colspan="2" width="30%" style="font-family: \'Trebuchet MS\'; font-size:10pt; align:\'center\';">Numéro de facture</td>
            </tr>
            <tr>
            <td align=center width="30%" style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y').'</td>
            <td align=center colspan=2 width="30%" style="font-family: \'Trebuchet MS\'; font-size:10pt;">F'.$idCommande.'</td>
            </tr>
            <tr>
            <td class="desc" style="width:90px; font-family: \'Trebuchet MS\'; font-size:10pt;">Description</td>
            <td colspan=2 style="width:330px; font-family: \'Trebuchet MS\'; font-size:10pt;">Article acheté en ligne</td>
            <td align=center width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;">Taux de TVA</td>
            <td align=center width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;">Prix</td>
            </tr>';

    foreach($arrayArt as $art){
        $str .= '<tr>
            <td class="descomp" width="15%" style="font-family: \'Trebuchet MS\'; font-size:10pt;"> </td>
            <td class="comp" valign=top colspan=2 style="width: 330px; font-family: \'Trebuchet MS\'; font-size:10pt;"><br>Article : <em>'.$art['ARTICLE_TITRE'].'</em><br><br> Magazine : '.$art['REVUE_TITRE'].', n° '.$art['NUMERO_ANNEE'].'/'.$art['NUMERO_NUMERO'].'<br><br><br><br><br><br><br><br><br><br>ID article : '.$art['ARTICLE_ID_ARTICLE'].' - Commande n°'.$idCommande.'</td>
            <td align=center valign=top style="font-family: \'Trebuchet MS\'; font-size:10pt;"><br>'.$art['TAUX_TVA'].' %</td>
            <td valign=top align=right style="font-family: \'Trebuchet MS\'; font-size:10pt;"><br>'.$art['PRIX_HT'].' €</td>
            </tr>';
    }

    $str .= '<tr>
            <td class="ds" colspan=3></td>
            <td class="d" style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.($tauxTVA>0?'Total HT':'Prix hors<br/>frais de port').'</td>
            <td align=right style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.$prices['totalPriceHT'].' €</td>
            </tr>
            <tr>
            <td class="ds" colspan=3></td>
            <td class="d" style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.($tauxTVA>0?'TVA':'frais de port').'</td>
            <td align=right style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.($tauxTVA>0?(floatval($prices['totalTVA2dot1']+$prices['totalTVA5dot5']+$prices['totalTVA20'])):$prices['totalFP']).' €</td>
            </tr>
            <tr>
            <td class="ds" colspan=3></td>
            <td class="compd" style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.($tauxTVA>0?'Total TTC':'Total hors<br/>taxe').'</td>
            <td class="comp" align=right style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.$prices['totalPrice'].' €</td>
            </tr>
            </table>
            <table cellpadding=3 cellspacing=0 style="width:320px;">
            <tr>
            <td class="d" style="width:80px; font-family: \'Trebuchet MS\'; font-size:10pt;">Base</td>
            <td align=center style="width:80px; font-family: \'Trebuchet MS\'; font-size:10pt;">0 %</td>';
    if ($prices['totalTVA2dot1'] > 0) {
        $str .= '<td align=center style="width:80px; font-family: \'Trebuchet MS\'; font-size:10pt;">2.1 %</td>';
    }
    if ($prices['totalTVA5dot5'] > 0) {
        $str .= '<td align=center style="width:80px; font-family: \'Trebuchet MS\'; font-size:10pt;">'.$tauxTVAreduit.' %</td>';
    }
    if ($prices['totalTVA20'] > 0) {
        $str .= '<td align=center style="width:80px; font-family: \'Trebuchet MS\'; font-size:10pt;">'.$tauxTVA.' %</td>';
    }
    $str .= '</tr>
            <tr>
            <td class="compd" style="font-family: \'Trebuchet MS\'; font-size:10pt;">TVA</td>
            <td class="comp" align=center style="font-family: \'Trebuchet MS\'; font-size:10pt;">0.00 €</td>';
    if ($prices['totalTVA2dot1'] > 0) {
        $str .= '<td class="comp" align=center style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.$prices['totalTVA2dot1'].' €</td>';
    }
    if ($prices['totalTVA5dot5'] > 0) {
        $str .= '<td class="comp" align=center style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.$prices['totalTVA5dot5'].' €</td>';
    }
    if ($prices['totalTVA20'] > 0) {
        $str .= '<td class="comp" align=center style="font-family: \'Trebuchet MS\'; font-size:10pt;">'.$prices['totalTVA20'].' €</td>';
    }

    $str .= '
            </tr>
            </table>
            <p style="font-family: \'Trebuchet MS\'; font-size:10pt;">Certifié sincère et véritable à la somme de '.$prices['totalPrice'].' €.<br><br>Réglée par carte de crédit le '.date_format(new DateTime($commandeTmp['DATE']), 'd-m-Y').'.</p>
            <p align=center style="font-family: \'Trebuchet MS\'; font-size:9pt;"><b>Cairn S.A - RCS Paris 487 704 942</b></p>
            </body>
            </html>';

    return $str;
}
