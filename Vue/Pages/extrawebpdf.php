<?php

/*
 * Il est nécessaire d'avoir un lien symbolique "cairnv2" à la racine de notre projet.
 * Il doit pointer vers les includes CAIRN (ce qui nous permet de ne pas les trimballer dans nos sources et le git.
 * 
 * TODO : achever dans le répertoire include_V2 le remplacement des chemins relatifs statiques avec des __DIR__ qui nous donneront plus de flexibilité
 */ 
$NOM_FILE = $currentArticle['ARTICLE_EXTRAWEB_NOM_FICHIER'];
$file = $prefix_path.$currentArticle['ARTICLE_ID_REVUE'].'/'.$currentArticle['ARTICLE_ID_NUMPUBLIE'].'/'.$currentArticle['ARTICLE_ID_ARTICLE'].'/'.$NOM_FILE;
 
header("Content-disposition: attachment; filename=$NOM_FILE");
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($file));
header("Pragma: no-cache"); 
header("Expires: 0");

readfile($file); // Envoie le fichier*/

 
?>


