<?php
//var_dump($results[1]);

$rss_content = "";

foreach ($results as $result)
{

	$typePubTitle = $typeDocument[$pack][$offset];
	$typePub = $result->userFields->tp;
	$typeNumPublie = $result->userFields->tnp;
	$ARTICLE_ID_ARTICLE = $result->userFields->id;
	$ARTICLE_ID_REVUE = $result->userFields->id_r;
	$NUMERO_ID_REVUE = $ARTICLE_ID_REVUE;
	$ARTICLE_PRIX = $result->userFields->px;

	$ARTICLE_ID_NUMPUBLIE = $result->userFields->np;
	$NUMERO_ID_NUMPUBLIE = $ARTICLE_ID_NUMPUBLIE;
	$ARTICLE_TITRE = $result->userFields->tr;
	$NUMERO_TITRE = $result->userFields->titnum;
	$NUMERO_SOUS_TITRE = $metaNumero[$NUMERO_ID_NUMPUBLIE]['SOUS_TITRE'];
	$ARTICLE_HREF = "revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd . ".htm";
	
	$ARTICLE_TITRE = $result->userFields->tr;
	$NUMERO_TITRE = $result->userFields->titnum;
	$NUMERO_SOUS_TITRE = $metaNumero[$NUMERO_ID_NUMPUBLIE]['SOUS_TITRE'];
        $REVUE_TITRE = $result->userFields->rev0;
	$NOM_EDITEUR = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NOM_EDITEUR'];
	//var_dump($ARTICLE_ID_ARTICLE);
	//var_dump($NUMERO_ID_NUMPUBLIE);
	//var_dump($NOM_EDITEUR); echo "<br>";
        $REVUE_ID = $result->userFields->id_r;
        $authors = explode('|', $result->userFields->auth0);
        $NUMERO_ANNEE = $result->userFields->an;
        $NUMERO_NUMERO = $result->userFields->NUM0;
        $NUMERO_VOLUME = $result->userFields->vol;
        $ARTICLE_PAGE = $result->userFields->pgd;

	$DATEM = $result->item->dateM; //dernière modif du document au moment de l'indexation
	$SYNOPSIS = $result->item->Synopsis;

	$authors = explode('|', $result->userFields->auth0);

	$ARTICLE_HREF = '';
	$NUMERO_HREF = '';
	$REVUE_HREF = "";
	$detail = ""; //champs titre rss	

	switch ($typePub) {
	    case "1":
		$title = $ARTICLE_TITRE ;
		$detail = $REVUE_TITRE . " " . $NUMERO_ANNEE . "/" . $NUMERO_NUMERO . " (". $NUMERO_VOLUME .")";

	        $ARTICLE_HREF = "article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
	        $NUMERO_HREF = "revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
	        $REVUE_HREF;
	        $REVUE_HREF = "revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
	        break;
	    case "2":
		$title = $ARTICLE_TITRE;
		$detail = $NUMERO_TITRE . " - " ;
		if ($NUMERO_SOUS_TITRE != '') $detail .= " " . $NUMERO_SOUS_TITRE;
		$detail .= " " . $NOM_EDITEUR . ", " . $NUMERO_ANNEE;
		
	        $ARTICLE_HREF = "magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
	        $ARTICLE_HREF = "article.php?ID_ARTICLE=".urlencode($ARTICLE_ID_ARTICLE).$getDocUrlParameters;
	        $NUMERO_HREF = "magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING']. "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
	        $REVUE_HREF;
	        $REVUE_HREF = "magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
	        break;
	    case "3": //numrevue
		$title = $ARTICLE_TITRE;
		$detail = $NUMERO_TITRE . " - " ;
		if ($NUMERO_SOUS_TITRE != '') $detail .= " " . $NUMERO_SOUS_TITRE;
		$detail .= " " . $NOM_EDITEUR . ", " . $NUMERO_ANNEE;

	        $ARTICLE_HREF = "" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
	        $ARTICLE_HREF = "article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
	        $NUMERO_HREF = "" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . ".htm";
	        $REVUE_HREF = "collection-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
	        break;

	    case "6": //encyclopedie de poche
		$title = $ARTICLE_TITRE;
		$detail = $NUMERO_TITRE . " - " ;
		if ($NUMERO_SOUS_TITRE != '') $detail .= " " . $NUMERO_SOUS_TITRE;
		$detail .= " " . $NOM_EDITEUR . ", " . $NUMERO_ANNEE;

	        $ARTICLE_HREF = "" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
	        $ARTICLE_HREF = "article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
	        $NUMERO_HREF = "" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . ".htm";
	        $REVUE_HREF = "collection-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
	        break;
	}

$BLOC_AUTEUR = '';
            $BLOC_AUTEUR_PACK = '';
            if (sizeof($authors) > 2) {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR = $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . " et al.";
            } else if (sizeof($authors) == 2) {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR =  $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . " et ";
                $authors2 = explode('#', $authors[1]);
                $BLOC_AUTEUR .= $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1];
            } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                $authors2 = explode('#', $authors[0]);
                $BLOC_AUTEUR =  $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1];
            }

            $BLOC_AUTEUR = trim($BLOC_AUTEUR);

            if($BLOC_AUTEUR != ''){
                $BLOC_AUTEUR_PACK = $BLOC_AUTEUR;
            }else{
                $numeroAuteurs = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NUMERO_AUTEUR'];
                $authors = explode('|',$numeroAuteurs);
                if (sizeof($authors) > 2) {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = $authors2[0] . ' ' . $authors2[1] . "et al.";
                } else if (sizeof($authors) == 2) {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK = $authors2[0] . ' ' . $authors2[1] . "et ";
                    $authors2 = explode(':', $authors[1]);
                    $BLOC_AUTEUR_PACK .=  $authors2[0] . ' ' . $authors2[1];
                } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                    $authors2 = explode(':', $authors[0]);
                    $BLOC_AUTEUR_PACK =  $authors2[0] . ' ' . $authors2[1];
                }
                $BLOC_AUTEUR_PACK = trim($BLOC_AUTEUR_PACK);
            }

//var_dump($ARTICLE_HREF);echo"<br>";var_dump($NUMERO_HREF);echo"<br>";var_dump($REVUE_HREF);echo"<br>";echo"<br>-----<br>";
//var_dump($ARTICLE_TITRE);echo"<br>";var_dump($NUMERO_TITRE);echo"<br>";var_dump($NUMERO_SOUS_TITRE);echo"<br>";var_dump($REVUE_TITRE);echo"<br>---<br>";


	$rss_content .= "<item>";
	$rss_content .= "<title><![CDATA[" . strip_tags($title) . "]]></title>";
	$rss_content .= "<link>http://www.cairn.info/" . $ARTICLE_HREF . "</link>";
	$rss_content .= "<description><![CDATA[" . $BLOC_AUTEUR . "<br><br> " . strip_tags($detail) .  "]]></description>"; //$typePub .
	$rss_content .= "</item>";
 

}

/*echo '<?xml version="1.0" encoding="UTF-8" ?>'; */
echo '<rss version="2.0">';
echo '<channel><title>Cairn.info - Recherche RSS</title>';
echo '<link>http://www.cairn.info</link>';
echo '<language>fr</language>';
echo $rss_content;
echo '</channel></rss>';

?>
