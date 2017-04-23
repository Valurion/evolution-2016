<?php
	// Include
	include_once("../includes/config.php");
	include_once("../includes/modeles.php");
	include_once("./class/export.class.php");

	// Définition des paramètres
	$pathCairn				= PATHCAIRN;		// Adresse du site
	$pathStatic 			= PATHSTATIC;		// Adresse de l'emplacement des éléments (images, css, ...)
	$pathVignette			= PATHVIGNETTE;		// Adresse d'accès aux vignettes
	$html 					= "";
	
	$from_nom 				= $_GET["from_nom"];
	$from_email				= $_GET["from_email"];
	$from_commentaire		= $_GET["from_commentaire"];
	$to_nom					= $_GET["to_nom"];
	$to_email 				= $_GET["to_email"];
	$to_user_exist			= $_GET["to_user_exist"];	// Le destinataire est-il déjà présent dans notre base de données (1 = Oui, 0 = Non)
	$bibliographie 			= $_GET["bibliographie"];	// Ex.: DEC_COUSI_2016_01_0001/DEC_COUSI_2016_01_0003/DEC_COUSI_2016_01_0005
	$articles 				= explode("/", $bibliographie);

	// Définition des valeurs

	// Récupération du template
	$html_tpl_base 			= file_get_contents('tpl/tpl-base.html');
	$html_tpl_biblio_from	= file_get_contents('tpl/tpl-biblio-from.html');
	$html_tpl_commentaire	= file_get_contents('tpl/tpl-commentaire.html');
	$html_tpl_article 		= file_get_contents('tpl/tpl-article.html');
	$html_tpl_numero 		= file_get_contents('tpl/tpl-numero.html');
	$html_tpl_disclaimer	= file_get_contents('tpl/tpl-disclaimer.html');
	$html_tpl_inscription	= file_get_contents('tpl/tpl-inscription.html');

	// Assignation
	$html 					= $html_tpl_base;		// Contient le code HTML de base (header, body, ...)
	$html_article 			= $html_tpl_article;	// Contient le code HTML d'un article (auteurs, titre, bouton)
	$html_numero 			= $html_tpl_numero;		// Contient le code HTML d'un numéro de revue ou d'ouvrage (auteurs, titre, bouton)

	$html_liste 			= ""; 					// Contient la liste des articles formaté en HTML

	// Exécution
	$export 				= new export();

	// REMPLACEMENT DES VALEURS	
	// Envoi à un autre destinataire, sans commentaire, on affiche un message de provenance du mail
	if($from_email != $to_email && $from_commentaire == "") {
		$html = str_replace("###BIBLIO_FROM###", $html_tpl_biblio_from, $html);
	} 
	// Envoie à un autre destinataire, avec un commentaire, on affiche le commentaire
	else if ($from_email != $to_email && $from_commentaire != "") {
		$html = str_replace("###BIBLIO_FROM###", $html_tpl_commentaire, $html);
	}	
	// Envoi à l'utilisateur
	else {
		$html = str_replace("###BIBLIO_FROM###", "", $html);	
	}

	// Envoi à une autre personne que celle propriétaire du compte
	if($from_email != $to_email) {
		// Destinataire inconnu
		// On affiche le disclaimer et le lien d'inscription
		if($to_user_exist == 0) {
			$html = str_replace("###DISCLAIMER###", $html_tpl_disclaimer, $html);
			$html = str_replace("###DISCLAIMER_REGISTRATION###", $html_tpl_inscription, $html);
		}
		// L'utilisateur a déjà un compte
		// On affiche uniquement le disclaimer sans le lien d'inscritpion
		else {
			$html = str_replace("###DISCLAIMER###", $html_tpl_disclaimer, $html);
			$html = str_replace("###DISCLAIMER_REGISTRATION###", "", $html);
		}
	}
	else {
		$html = str_replace("###DISCLAIMER###", "", $html);
	}

	// GENERAL
	$html = str_replace("###PATHCAIRN###", $pathCairn, $html);
	$html = str_replace("###PATHSTATIC###", $pathStatic, $html);
	$html = str_replace("###TITRE###", "Ma bibliographie", $html);
	$html = str_replace("###FROM_NOM###", $from_nom, $html);
	$html = str_replace("###FROM_COMMENTAIRE###", "<i>".$from_commentaire."</i>", $html);

	// Assignation de la liste
	foreach($articles as $id_article) {

		// Création d'un élément temporaire contenant le HTML d'un article
		$tmp = $html_article;

		// Récupération des données de l'article
		$article = $export->getArticle($id_article);	
		// Définition du typepub
		$typepub = $article[0]["TYPEPUB"];	

		// Il ne s'agit pas d'un article mais d'un numéro de revue/ouvrage
		if(empty($article)) {
			// Récupération des données du numéro
			$numero = $export->getNumero($id_article);
			// Il s'agit bien d'un numéro,
			if(!empty($numero)) {
				// On change aussi le template
				$tmp = $html_numero;
				// Définition du typepub
				$typepub = $numero[0]["TYPEPUB"];	
			}
		}

		// Gestion des références
		if(($typepub == 1) || ($typepub == 2)) {
			$tmp = str_replace("###REFERENCE###", "###NUMERO_VOLUME###, ###NUMERO_ANNEE### / ###NUMERO_NUMERO###", $tmp);
		} else {
			$tmp = str_replace("###REFERENCE###", "", $tmp);
		}
		
		// Article
		// Si l'article a bien été identifié
		if(!empty($article)) {
			// Assignation des valeurs à la volées
			foreach ($article[0] as $key => $value) {

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

				$tmp = str_replace("###".$key."###", $value, $tmp);

				// Paramétrage de l'URL
				$article_url = "http://www.cairn.info/article.php?ID_ARTICLE=".$id_article."&WT.mc_id=eBib";
				$tmp = str_replace("###ARTICLE_URL###", $article_url, $tmp);

				// Paramétrage de la vignette
				$article_vignette = $pathVignette.$article[0]["REVUE_ID_REVUE"]."/".$article[0]["NUMERO_ID_NUMERO"]."_L61.jpg";
				$tmp = str_replace("###VIGNETTE_ARTICLE###", $article_vignette, $tmp);			
			}	
		}
		// Numero (normalement, il ne devrait s'agir que d'ouvrage car les revues ne sont plus ajoutées à la bibliographie)
		// Si la revue/ouvrage a bien été identifié
		if(!empty($numero)) {
			// Assignation des valeurs à la volées
			foreach ($numero[0] as $key => $value) {

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

				$tmp = str_replace("###".$key."###", $value, $tmp);

				// Paramétrage de l'URL
				// Ouvrages
				if(($typepub == 3) || ($typepub == 5) || ($typepub == 6)) {
					$numero_url = "http://www.cairn.info/".$numero[0]["NUMERO_URL"]."--".$numero[0]["NUMERO_ISBN"].".htm?WT.mc_id=eBib";
					$tmp = str_replace("###NUMERO_URL###", $numero_url, $tmp);
				}
				// Revue
				if(($typepub == 1)) {
					$numero_url = "http://www.cairn.info/revue-".$numero[0]["REVUE_URL"]."-".$numero[0]["NUMERO_ANNEE"]."-".$numero[0]["NUMERO_NUMERO"].".htm?WT.mc_id=eBib";
					$tmp = str_replace("###NUMERO_URL###", $numero_url, $tmp);
				}
				// Magazine
				if(($typepub == 2)) {
					$numero_url = "http://www.cairn.info/magazine-".$numero[0]["REVUE_URL"]."-".$numero[0]["NUMERO_ANNEE"]."-".$numero[0]["NUMERO_NUMERO"].".htm?WT.mc_id=eBib";
					$tmp = str_replace("###NUMERO_URL###", $numero_url, $tmp);
				}

				// Paramétrage de la vignette
				$article_vignette = $pathVignette.$numero[0]["REVUE_ID_REVUE"]."/".$numero[0]["NUMERO_ID_NUMERO"]."_L61.jpg";
				$tmp = str_replace("###VIGNETTE_NUMERO###", $article_vignette, $tmp);			
			}	
		}	

		// L'identification n'a pas eu lieu
		if(empty($article) && empty($numero)) {
			$tmp = "";
		}

		// Assignation
		$html_liste .= $tmp;
	}

	// Assignation des valeurs
	$html = str_replace("###LISTE_ARTICLE###", $html_liste, $html);

	// Rendu HTML
	echo $html;

	// CONFIGURATION DU MAIL
	$to 		= $to_email;
	$subject 	= "Ma bibliographie sur Cairn.info";
	$message 	= $html;

	$headers    = "From: ".$from_nom." <".$from_email.">\r\n";
	$headers   .= "Reply-to: ".$from_nom." <".$from_email.">\r\n";
	$headers   .= "Mime-Version: 1.0\r\n";
	$headers   .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
	$headers   .= "Content-Transfer-Encoding: 8bit"; // ici on précise qu'il y a des caractères accentués	

	// Envoi
	mail($to, $subject, $message, $headers);
?>