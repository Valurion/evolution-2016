<?php
//error_reporting(1);

// Rapporte les erreurs d'exécution de script
// error_reporting(E_ALL);
ini_set('memory_limit', '-1');

umask(0);
/*
 * Pour que ce script fonctionne, il faut installer les bibliothèque fpdi et fpdf. Sous une Debian-like
 * (ubuntu par exemple), cela se fait via aptitude install libfpdi-php php-fpdf libfpdf-tpl-php Le script est
 * encore expérimental. Les chemins des bibliothèques ne devraient pas être en dur, en prévision du
 * déploiement sur centos, qui n'a pas les mêmes chemins que debian.
 * Un exemple de pdf qui fonctionne :
 * http://localhost:8080/index.php?controleur=Pages&PDF=1&ID_ARTICLE=AG_660_0129 Un exemple de pdf qui ne
 * fonctionne pas : http://kakemphaton:8080/index.php?controleur=Pages&PDF=1&ID_ARTICLE=JEPAM_112_0003 Cela
 * est dû au numéro de version du pdf. Les version pdf >= 1.5 ne sont pas pris en compte par fpdf. Il existe
 * d'autres bibliothèques php pour le traitement des pdfs. Mais soit elles sont lente, soit elles ne prennent
 * pas en compte l'utf-8, soit elles sont lourdingue à utiliser et cela reviendra au même que l'ancien script
 * Watermarking.php Toutefois, rien ne vous interdit d'expérimenter d'autres choses. L'ensemble tcpf+fpdi me
 * semblait le plus adéquat sur le moment. Pour outrepasser cette limitation, deux solutions : Payer pour une
 * licence relativement chère auprès de tcpdf Au moment de l'import, convertir en version pdf < 1.5 La seconde
 * solution a ma préférence, car elle permet au passage de réduire la taille du pdf et d'économiser de la
 * bande passante. Et elle est la plus interopérable. L'important est de tenir compte des performances, de la
 * lisibilité du script et que ce soit en php (sinon, j'aurais utilisé volontier autre chose). Ainsi que la
 * gestion de l'encodage.
 * Faire un ln -s ../../Vue/Pages/pdf.php /VueInt/Pages/
 */
/*
    Formatte les auteurs reçus de la BDD vers une chaine de caractère adapté à l'affichage pdf

    Pour les auteurs de l'article, $rawAuthors sera une string sous la forme
        `prenomAuteur1:nomAuteur1:idAuteur1:,prenomAuteur2:nomAuteur2:idAuteur2:`
    Pour les auteurs du numéro, c'est un tableau
*/
// var_dump($mode);
// var_dump(get_defined_vars());die();

function fullUpper($string)
{
    $convert_from = array ("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","à","á","â","ã","ä","å","æ","ç","è","é","ê","ë","ì","í","î","ï","ð","ñ","ò","ó","ô","õ","ö","ø","ù","ú","û","ü","ý","а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я","œ","æ" );
    $convert_to = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","À","Á","Â","Ã","Ä","Å","Æ","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ð","Ñ","Ò","Ó","Ô","Õ","Ö","Ø","Ù","Ú","Û","Ü","Ý","А","Б","В","Г","Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ъ","Ь","Э","Ю","Я" ,"Œ","Æ");

    return str_replace($convert_from, $convert_to, $string);
}

 // var_dump($currentArticle);die();

// On préfère tcpdf à fpdf, car ce dernier a vraiment du mal avec l'encodage en utf-8

require_once(Configuration::get('cairn_includes_path').'tcpdf/tcpdf.php');
require_once(Configuration::get('cairn_includes_path').'fpdi/fpdi.php');
$Watermarking = Service::get('WatermarkingPdf');

// Constante utilisé tout au long de ce script
$IS_REVUE = (($revue ['TYPEPUB'] == '1') || ($revue ['TYPEPUB'] == '2'));
$typeOuvrage = 'revue';
if ($numero ['REVUE_TYPEPUB'] == '3' || $numero ['REVUE_TYPEPUB'] == '6') {
    $typeOuvrage = ($numero ['NUMERO_TYPE_NUMPUBLIE'] == '0') ? 'monographie' : 'collectif';
}

if('normal'==$mode){
    $to = "à";
    $reproduction = "La reproduction ou représentation de cet article, notamment par photocopie, n'est autorisée que dans les limites des conditions générales d'utilisation du site ou, le cas échéant, des conditions générales de la licence souscrite par votre établissement. Toute autre reproduction ou représentation, en tout ou partie, sous quelque forme et de quelque manière que ce soit, est interdite sauf accord préalable et écrit de l'éditeur, en dehors des cas prévus par la législation en vigueur en France. Il est précisé que son stockage dans une base de données est également interdit." ;
    $rights = 'Tous droits réservés pour tous pays.';
    $distribution = "Distribution électronique Cairn.info pour";
    $translation = "";
    $citation = "Pour citer cet article :";
    $available = "Article disponible en ligne à l'adresse :";
    $image = "logo-cairn.png";
    $LOGO_CAIRN = [ 'path' => __DIR__ . "/../../static/images/".$image,'width' => 48,'height' => 16 ];
    $moreAvailable = '';
}else{
    $to = "-";
    $reproduction =  "Reproducing this article (including by photocopying) is only authorized in accordance with the general terms and conditions of use for the website, or with the general terms and conditions of the license held by your institution, where applicable. Any other reproduction, in full or in part, or storage in a database, in any form and by any means whatsoever is strictly prohibited without the prior written consent of the publisher, except where permitted under French law.";
    $rights = 'All rights reserved for all countries.';
    $distribution = "Electronic distribution by Cairn on behalf of";
    $translation = "This document is a translation of:";
    $citation = "How to cite this article :";
    $available = "Available online at :";
    $moreAvailable = 'The English version of this issue is published thanks to the support of the CNRS';
    $image = "logo-cairn-int.png";
    $LOGO_CAIRN = [ 'path' => __DIR__ . "/../../static/images/".$image,'width' => 46,'height' => 16 ];
}
$translationOf = true;
if($currentArticle['ARTICLE_LANGUE']=='en' && $currentArticle['ARTICLE_LANGUE_INTEGRALE']=='en'){
   $translationOf = false;
}
$PORTRAIT = 'P';
$LANDSCAPE = 'L';



$PAGE_DIMENSION = [
    'width' => 160,
    'height' => 240
];
$MARGIN = [
    'width' => 10,
    'height' => 10
];
class CairnPdf extends FPDI
{
    // Fonctions vide pour que tcpdf n'affiche pas de lignes de header/footer
    // Normalement, il y a une fonction dédiée, mais elle bug...
    public function Header() {}
    public function Footer() {}
}

$pdf = new CairnPdf();

$pathpdf = implode('/', [
    Configuration::get('prefixPath'),
    $currentArticle ['REVUE_ID_REVUE'],
    $currentArticle ['NUMERO_ID_NUMPUBLIE'],
    $currentArticle ['ARTICLE_ID_ARTICLE'],
    $currentArticle ['ARTICLE_ID_ARTICLE']
]);

// Ajout d'une nouvelle page blanche
$pdf->addPage($PORTRAIT, [ $PAGE_DIMENSION ['width'],$PAGE_DIMENSION ['height'] ]);

// Insertion du logo cairn
$pdf->Image($LOGO_CAIRN ['path'], // Chemin du logo
$PAGE_DIMENSION ['width'] - $LOGO_CAIRN ['width'] - $MARGIN ['width'], // Position en x de l'image
$MARGIN ['height'], // Position en y de l'image
$LOGO_CAIRN ['width']); // Largeur de l'image

$articleHref = '';
if ($IS_REVUE) {
    if('normal'==$mode){
    $articleHref = (($revue ['TYPEPUB'] == '1') ? 'revue-' : 'magazine-') . $revue ['REVUE_URL_REWRITING'] . '-' . $numero ['NUMERO_ANNEE'] . '-' . $numero ['NUMERO_NUMERO'];
    }else{
        $articleHref = (($revue ['TYPEPUB'] == '1') ? 'article-' : 'magazine-') . $currentArticle ['ARTICLE_ID_ARTICLE'].'--' . $currentArticle ['ARTICLE_URL_REWRITING_EN'];
    }
} else {
    $articleHref = $numero ['NUMERO_URL_REWRITING'] . '--' . $numero ['NUMERO_ISBN'];
}
if('normal'==$mode){
    //http://www.cairn-int.info/
$articleHref = [ 'http://www.cairn.info/',$articleHref,'-page-' . $currentArticle ['ARTICLE_PAGE_DEBUT'],'.htm' ];
}else{
    $articleHref = ['http://www.cairn-int.info/',$articleHref,'.htm' ];
}
$articleHref = implode($articleHref);

$pdf->setFont('dejavuserif', 'I', 9, '', false);
$pdf->setFont('dejavuserif', null, 9, '', false);

$html = '';
$html .= "<br />&#160;<br />";
// Surtitre de l'article, si besoin
if ($currentArticle ['ARTICLE_SURTITRE']) {
    $html .= '<h2 style="font-size: 1em; line-height: 0.8; font-weight: normal;">' . $currentArticle ['ARTICLE_SURTITRE'] . '</h2>';
}
// Titre de l'article

$html .= '<div><span style="font-size: 1.2em;">' . strtoupper(fullUpper($currentArticle ['ARTICLE_TITRE'])) . "</span>";
// Sous-titre de l'article, si besoin
if ($currentArticle ['ARTICLE_SOUSTITRE']) {
    $html .= '<br/><span>' . $currentArticle ['ARTICLE_SOUSTITRE'] . '</span>';
}
$html .= '<br/><span style="color: olive;">' . $articleAuthors . '</span>';

// Auteurs de l'article

if ($typeOuvrage == 'collectif') {
    $parseAutNumeroColl = $numero ['NUMERO_AUTEUR'] [0] ['AUTEUR_PRENOM'] . ' ' . $numero ['NUMERO_AUTEUR'] [0] ['AUTEUR_NOM'];
    if (count($numero ['NUMERO_AUTEUR']) > 1) {
        $parseAutNumeroColl .= ' <i>et al.</i>';
        $parseAutNumeroColl = "<i>in</i> " . $parseAutNumeroColl;
    }
}
// Auteurs de l'ouvrage
if (!$IS_REVUE) {
    $html .= '<br/><br/>';
    if ($parseAutNumeroColl) {
        $html .= '<span style="color:#a5a524;">'.$parseAutNumeroColl.", ";
    }
    $html .= "<i>".$numero['NUMERO_TITRE'].'</i></span>';
}
$html .= '</div>';
// Les margins/paddings marchent pas...
// Les br successifs non plus....
$html .= '';

// Éditeur de la revue et nom de la revue ou nom de l'ouvrage
$html .= '<div>';
$html .= '<span style="line-height: 1px;">';
$html .= $currentArticle ['EDITEUR_NOM_EDITEUR'];
$html .= '</span>';
$html .= ' | ';
// Nom de la revue ou nom de l'ouvrage
$html .= '<span style="color: #4bb2ac; ">';
$html .= "«&#160;" . $revue ['REVUE_TITRE'] . "&#160;» ";

$html .= '</span>';
$html .= "</div>";

// Numérotation du numéro ou de l'ouvrage
$html .= '<div>';
$html .= '<span style="line-height: 1px;">';
if ($IS_REVUE) {
    $html .= $numero ['NUMERO_ANNEE'] . '/' . $numero ['NUMERO_NUMERO'] . " ";
    $html .= $numero ['NUMERO_VOLUME'];
} else {
    $html .= $numero ['NUMERO_ANNEE'];
}
$html .= " | pages " . $currentArticle ['ARTICLE_PAGE_DEBUT'] ." $to " . $currentArticle ['ARTICLE_PAGE_FIN'];
$html .= '</span>';
$html .= '<br />&#160;<br />';

// Issn de la revue
if ($IS_REVUE && $revue ['ISSN']) {
    $html .= '<p style="line-height: 0;">ISSN ' . $revue ['ISSN'] . "</p>";
}
if ($numero ['NUMERO_ISBN']) {
    $html .= '<p style="line-height: 0;">ISBN ' . $numero ['NUMERO_ISBN'] . "</p>";
}

// Url de l'article sur cairn
$html .= '<br />';
 //This document is a translation of:
if('cairninter'==$mode){
     //Si lang et lang_integrales différents de 'en'
    if($translationOf){
        $html .= '<span style="color: #AAAAAA;">'.$translation.'</span>';
        $html .= '<br /><span style="color: #AAAAAA;">--------------------------------------------------------------------------------------------------------------------</span>';
        $html .= '<br /><span>' . $parsetranslation . "</span>";
        $html .= '<br /><span style="color: #AAAAAA;">--------------------------------------------------------------------------------------------------------------------</span>';
        $html .= '<br /><span style="color: #AAAAAA;">'.$currentArticle['ARTICLE_MENTION_SOMMAIRE'].'</span>';
        $html .= '<br />&#160;<br />';
    }else{
        $html .= '<br />&#160;<br />';
    }

}
//Available online at : | Article disponible en ligne à l'adresse :
$html .= '<span style="color: #AAAAAA;">'.$available.'</span>';
$html .= '<br /><span style="color: #AAAAAA;">--------------------------------------------------------------------------------------------------------------------</span>';
$html .= '<br /><span><a style="color: #333333;text-decoration: none;" href="' . $articleHref . '">' . $articleHref . "</a></span>";
$html .= '<br /><span style="color: #AAAAAA;">--------------------------------------------------------------------------------------------------------------------</span>';
//Edition de CNRS pour cairn inter et seulement si NUMERO_TYPE_NUMPUBLIE=5
if(('cairninter'==$mode) && '5' == $numero['NUMERO_TYPE_NUMPUBLIE']){
    $html .= '<br />'.$moreAvailable.'';
    $html .= '<br /><span style="color: #AAAAAA;">--------------------------------------------------------------------------------------------------------------------</span>';
    $html .= '<br />&#160;<br />';
}

//How to cite this article : | Pour citer cet article :
$html .= '<br />';
$html .= '<br /><span style="color: #AAAAAA;">'.$citation.'</span>';
$html .= '<br /><span style="color: #AAAAAA;">--------------------------------------------------------------------------------------------------------------------</span>';
$html .= '<br /><span>';
// Citation en fonction du type(revue,ouv collectif,ouv monographie)
$html .= $parsecitation;
$html .= "</span> ";
$html .= '<br/><span style="color: #AAAAAA;">--------------------------------------------------------------------------------------------------------------------</span>';
$html .= '<br />';

// Copyright de l'article
$html .= '<div>';
$html .= '<br />&#160;<br />';
$html .= '<span style="font-size: 0.8em;">' . "$distribution " . $currentArticle ['EDITEUR_NOM_EDITEUR'] . ".<br />" . "© " . $currentArticle ['EDITEUR_NOM_EDITEUR'];
$html .= ". $rights" ;
$html .= "</span>";
// Mention légale
$html .= '<br />';
if('normal'==$mode){
    $html .= '<br />';
}

//bas de page(Reproducing this article  | La reproduction ou représentation )
$html .='<span style="font-size: 0.8em;">' .$reproduction.'</span>';
$html .= '</div>';
$pdf->writeHTMLCell($PAGE_DIMENSION ['width'] - ($MARGIN ['width'] * 2), // Largeur
0, // hauteur minimal
$MARGIN ['width'], // Position en x
$MARGIN ['height'] + $LOGO_CAIRN ['height'], // Position en y
'<div style="color: #222222;">' . $html . '</div>'); // Le code html à afficher
// echo $html;
// var_dump($currentArticle);
// die();

$pdf->Output($Watermarking->TEMPDIR.'Top'.$currentArticle['ARTICLE_ID_ARTICLE'].'.pdf', 'F');
chmod($Watermarking->TEMPDIR.'Top'.$currentArticle['ARTICLE_ID_ARTICLE'].".pdf", 0777) or die('Édition impossible');
$pdf->Close();

//var_dump($currentArticle,$iscleo);

$content = $Watermarking->makeWatermarking($currentArticle, $authInfos, $pathpdf,$mode);

if(true===$iscleo && 'normal'==$mode){
    if (is_file($pathpdf . ".PDF")) {
        $diri = $pathpdf . ".PDF";
    } else {
        $diri = $pathpdf . ".pdf";
    }
$content = file_get_contents($diri);
}
 // die('fin');
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="' . $currentArticle ["ARTICLE_ID_ARTICLE"] . '.pdf"');
// TODO: afficher la taille des fichiers pdf provoque une erreur chez les utilisateurs passant par certains proxys.
// header('Content-Length: ' . strlen($content));

echo $content;
