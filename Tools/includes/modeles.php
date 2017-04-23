<?php

// serge.kilimoff@cairn.info le 12/01/2017
// J'ai nettoyé un petit peu le code, parce que c'était n'importe quoi

function convertir($champ) {
    // J'ai nettoyé la fonction, qui introduisait plein d'erreurs d'encodages
    // J'ai laissé des trucs, mais je suis sûr à 99% qu'on peut faire autrement.
    $champ=str_replace("<titre>Résumé</titre>","",$champ);
    $champ=str_replace("<titre>Abstract</titre>","",$champ);
    $rech = '/(\<liensimple)([^\>]*)\>/';
    $remp = "";
    $champ = preg_replace($rech,$remp,$champ);
    $champ = str_replace("</liensimple>","",$champ);
    $rech = '/(\<image)([^\>]*)\>/';
    $remp = "";
    $champ = preg_replace($rech,$remp,$champ);
    $champ = str_replace('<titre>RESUME</titre>','',$champ);
    $champ = preg_replace("/(\<\!\-\-)(.+)(\-\-\>)/U","",$champ); // ??? C'est quoi ça ?
    $champ = trim($champ);
    return $champ;
}



function quenum($phrase) {
    // La fonction semble rechercher uniquement les chiffres.
    // J'ai un petit peu simplifié, mais ça reste bien compliqué pour pas grand chose
    $chnum = $phrase;
    $chnum = str_replace("n<sup>o</sup> ","",$chnum);
    $chnum = str_replace("n<sup>o</sup>","",$chnum);
    $chnum = str_replace("N<sup>o</sup> ","",$chnum);
    $chnum = str_replace("N<sup>o</sup>","",$chnum);
    $chnum = str_replace("n°","",$chnum);
    $chnum = str_replace("N°","",$chnum);
    $chnum = str_replace("n°","",$chnum);
    $chnum = str_replace("N°","",$chnum);
    $chnum = str_replace("No","",$chnum);    
    $chnum = str_replace("volume ","",$chnum);
    $chnum = str_replace("Volume ","",$chnum);
    $chnum = str_replace("vol. ","",$chnum);
    $chnum = str_replace("Tome ","",$chnum);
    $chnum = str_replace("Vol. ","",$chnum);
    $chnum = str_replace("tome ","",$chnum);
    $chnum = strtr($chnum, "abcdefghijklmnopqrstuwwxyz", "");
    $chnum = strtr($chnum,"ÄËÏÖÜäëïöüÂÉÊÎÔÛàâéèêîôùûœ ", "");
    $chnum = strtr($chnum, "ABCDE.FGHIJKLMNOPQRSTUVWXYZ <>","");
    $chnum = trim($chnum);
    return $chnum;
}



// Regroupe les fonctions de formatage et de traitement standard
function formatData($data) {
    $data = convertir($data);
    $data = strip_tags($data);
    $data = html_entity_decode($data, ENT_HTML5|ENT_QUOTES, 'UTF-8');
    return $data;
}

// Vérifie si le dernier caractère de la chaine est un point de ponctuation
function hasPonctuation($string) {
    // Tableau
    $array      = array(".", ";", "?", "!", ",");
    $lastChar   = substr($string, -1);

    if (in_array($lastChar, $array)) {return "1";}
    else {return "0";}
}
