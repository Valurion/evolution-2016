<?php 
	// Configuration de connexion à/aux base(s) de données
	define("DBMS", "mysql");
	define("DBHOST", "localhost");
	define("DBUSER", "");
	define("DBPASS", "");
	
	define("DBNAME", "cairn3_pub");
	define("DBINTNAME", "cairnint_pub");

	// Définition des chemins
	define("PATHCAIRN", "http://www.cairn.info/");										// Adresse du site
	define("PATHSTATIC", "http://www.cairn.info/static/mails/");						// Adresse de l'emplacement des éléments (images, css, ...)
	define("PATHVIGNETTE", "http://www.cairn.info/vign_rev/");							// Adresse d'accès aux vignettes
	
	define("PATHCAIRNINT", "http://www.cairn-int.info/");								// Adresse du site
	define("PATHSTATICINT", PATHSTATIC);												// Adresse de l'emplacement des éléments (images, css, ...)
	define("PATHVIGNETTEINT", "http://www.cairn-int.info/static/includes/vign_rev/");	// Adresse d'accès aux vignettes

?>