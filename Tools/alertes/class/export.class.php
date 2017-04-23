<?php

	// Cette class permet de récupérer une série d'élément nécessaire à la création du template
	class export {

		// Attributs
		public $pdo;

		// Constructeur
		public function __construct() {

			// Connexion à la base de données
			$this->pdoConnect();
		}

		// Méthodes
		// Connexion à la base de données
		public function pdoConnect() {
			try {
	            $this->pdo = new PDO(DBMS . ":host=" . DBHOST . "; dbname=" . DBNAME, DBUSER, DBPASS, array (PDO::ATTR_PERSISTENT => true ));
	            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	            $state = $this->pdo->prepare('SET NAMES UTF8');
	            $state->execute();
	        } 
	        catch (Exception $e) {
	            echo "<script>
	                    alert(\"Erreur de connexion\");             
	                  </script>";

	            exit();
	        }
		}

		// Récupération des données du numéro
		public function getNumeroById($idNumpublie) {			
			// Construction de la requête
			$sql = "SELECT 
						R.ID_REVUE as REVUE_ID_REVUE, R.TITRE as REVUE_TITRE, R.TYPEPUB, R.URL_REWRITING as REVUE_URL, 
						N.TITRE as NUMERO_TITRE, N.VOLUME as NUMERO_VOLUME, N.NUMERO as NUMERO_NUMERO, N.ANNEE as NUMERO_ANNEE, N.NB_PAGE as NUMERO_NBPAGE, N.PRIX as NUMERO_PRIX, N.PRIX_ELEC as NUMERO_EPRIX, N.ISBN as NUMERO_ISBN, N.URL_REWRITING as NUMERO_URL,
						E.NOM_EDITEUR
					FROM 
						NUMERO as N
					LEFT JOIN
						REVUE as R
						ON R.ID_REVUE = N.ID_REVUE
					LEFT JOIN 
						EDITEUR as E
						ON E.ID_EDITEUR = R.ID_EDITEUR
					WHERE
						N.ID_NUMPUBLIE = :idNumpublie";

			// Préparation et exécution de la requête
			$query = $this->pdo->prepare($sql);
			$query->bindValue(':idNumpublie', $idNumpublie, PDO::PARAM_STR);
			$query->execute();

			// Résultat
			return $query->fetchAll(PDO::FETCH_ASSOC);
		}

		// Récupération des articles d'un numéro
		public function getArticleByNumero($idNumpublie) {
			// Construction de la requête
			$sql = "SELECT 
						A.ID_ARTICLE, A.PAGE_DEBUT, A.PAGE_FIN, A.SECT_SOM, A.TITRE,
						GROUP_CONCAT(DISTINCT CONCAT(AUTEUR.PRENOM, ':', AUTEUR.NOM, ':', AUTEUR.ID_AUTEUR, ':', REPLACE(AUTEUR_ART.ATTRIBUT, ',', '&#44;')) ORDER BY ORDRE SEPARATOR ' , ' ) AS ARTICLE_AUTEUR
					FROM 
						ARTICLE as A
					LEFT JOIN 
						AUTEUR_ART
			            ON AUTEUR_ART.ID_ARTICLE = A.ID_ARTICLE
			        LEFT JOIN 
			        	AUTEUR
			            ON AUTEUR.ID_AUTEUR = AUTEUR_ART.ID_AUTEUR
					WHERE
						A.ID_NUMPUBLIE = :idNumpublie AND A.STATUT = 1
					GROUP BY 
						A.ID_ARTICLE
					ORDER BY 
						A.TRISHOW ASC";

			// Préparation et exécution de la requête
			$query = $this->pdo->prepare($sql);
			$query->bindValue(':idNumpublie', $idNumpublie, PDO::PARAM_STR);
			$query->execute();

			// Résultat
			return $query->fetchAll(PDO::FETCH_ASSOC);
		}

	}


?>