<?php
	// Include
	include_once("../includes/config.php");
	include_once("../includes/modeles.php");
	include_once("./class/export.class.php");

	// Définition des paramètres
	$pathCairn			= PATHCAIRN;		// Adresse du site
	$pathStatic 		= PATHSTATIC;		// Adresse de l'emplacement des éléments (images, css, ...)
	$pathVignette		= PATHVIGNETTE;		// Adresse d'accès aux vignettes
	$html 				= "";
	$idNumpublie 		= $_GET["id_numpublie"];

	// Récupération du template
	$html_tpl_base 		= file_get_contents('tpl/tpl-base.html');
	$html_tpl_section   = file_get_contents('tpl/tpl-section-sommaire.html');
	$html_tpl_article 	= file_get_contents('tpl/tpl-article.html');

	// Assignation
	$html 				= $html_tpl_base;		// Contient le code HTML de base (header, body, ...)
	$html_section 		= $html_tpl_section;	// Contient le code HTML d'une section du sommaire
	$html_article 		= $html_tpl_article;	// Contient le code HTML d'un article (auteurs, titre, bouton)

	$html_liste 		= ""; 					// Contient la liste des articles formaté en HTML
	$last_section		= "";					// Valeur temporaire conservant la précédente section du sommaire

	// Exécution
	$export 			= new export();
	$numero 			= $export->getNumeroById($idNumpublie);
	$articles 			= $export->getArticleByNumero($idNumpublie);
	$typepub 			= $numero[0]["TYPEPUB"];

	// REMPLACEMENT DES VALEURS
	// Paramétrage de l'URL du Numéro
	// Revue
	if($typepub == 1) {
		$numero_url = $pathCairn."revue-".$numero[0]["REVUE_URL"]."-".$numero[0]["NUMERO_ANNEE"]."-".$numero[0]["NUMERO_NUMERO"].".htm";		
	}
	// Ouvrage et Encyclopédie
	if(($typepub == 3) || ($typepub == 6)) {
		$numero_url = $pathCairn.$numero[0]["NUMERO_URL"]."--".$numero[0]["NUMERO_ISBN"].".htm";
	}
	// Magazine
	if($typepub == 2) {
		$numero_url = $pathCairn."magazine-".$numero[0]["REVUE_URL"]."-".$numero[0]["NUMERO_ANNEE"]."-".$numero[0]["NUMERO_NUMERO"].".htm";
	}

	// Paramétrage de l'URL de la vignette
	$vignette_url = $pathVignette.$numero[0]["REVUE_ID_REVUE"]."/".$idNumpublie."_L204.jpg";

	// GENERAL
	$html = str_replace("###PATHCAIRN###", $pathCairn, $html);
	$html = str_replace("###PATHSTATIC###", $pathStatic, $html);
	$html = str_replace("###NUMERO_URL###", $numero_url, $html);
	$html = str_replace("###VIGNETTE###", $vignette_url, $html);
	$html = str_replace("###TITRE_NUMERO_URL###", urlencode($numero[0]["NUMERO_TITRE"].", en ligne sur"), $html);
	if(($typepub == 1) || ($typepub == 2)) {$html = str_replace("###REFERENCE###", "###NUMERO_VOLUME###, ###NUMERO_ANNEE### / ###NUMERO_NUMERO###", $html);} else {$html = str_replace("###REFERENCE###", "", $html);}
	
	// BASE
	foreach ($numero[0] as $key => $value) {
		$html = str_replace("###".$key."###", $value, $html);
	}

	// LISTE DES ARTICLES
	foreach ($articles as $article) {
		
		// Création d'un élément temporaire contenant le HTML d'un article
		$tmp = $html_article;

		// LISTE DES VALEURS DE L'ARTICLE
		foreach($article as $key => $value) {

			// Ajout de la section sommaire si nécessaire
			if($key == "SECT_SOM") {
				// La section sommaire est différence de la dernière section enregistrée, on ajoute la section sommaire
				if(($value != $last_section) && ($value != "")) {
					// Modification des valeurs de la section
					$section = str_replace("###".$key."###", $value, $html_section);	

					// Ajout de la section au début de l'élément temporaire
					$tmp = $section.$tmp;

					// Enregistrement de la section
					$last_section = $value;
				}
			}

			// Gestion des auteurs
			if($key == "ARTICLE_AUTEUR") {
				// Explosion de la chaine en tableau
				$tabAuteurs 	= array();
				$auteurs 		= explode(",", $value);

				// Parcours de chaque auteur
				$i = 0;
				foreach($auteurs as $auteur) {
					// On affiche maximum 3 auteurs
					if($i < 3) {
						// Explosion des données de l'auteur
						$auteur = explode(":", $auteur);
						// Auteur sans attribut
						if($auteur[3] == "") {
							$tabAuteurs[] = trim($auteur[0])." ".trim($auteur[1]);
						}
						else {
							$tabAuteurs[] = trim($auteur[3])." ".trim($auteur[0])." ".trim($auteur[1]);	
						}
					}
					$i++;
				}
				// Il y a plus que 3 auteurs
				if($i >= 3) {$tabAuteurs[] = "et al.";}
				
				// Définition de la valeur
				$value = implode(", ", $tabAuteurs);
			}

			// Ajout de l'élément
			$tmp = str_replace("###".$key."###", $value, $tmp);
			// Paramétrage de l'URL
			$article_url = "http://www.cairn.info/article.php?ID_ARTICLE=".$article["ID_ARTICLE"];
			$tmp = str_replace("###ARTICLE_URL###", $article_url, $tmp);
		}

		// Ajout de l'élément dans la liste
		$html_liste .= $tmp;
	}
	// Assignation de la liste
	$html = str_replace("###LISTE_ARTICLE###", $html_liste, $html);

	// Rendu HTML
	echo $html;
?>