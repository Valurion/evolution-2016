/*
 * Table de stockage des utilisateurs anonymes
 * (auparavant ces informations étaient en session)
 */
CREATE TABLE `USER_GUEST` (
  `ID_USER` varchar(50) NOT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `HISTO_JSON` text,
  `CDATE` datetime DEFAULT NULL,
  PRIMARY KEY (`ID_USER`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
 * Table de stockage des utilisateurs connectés par login manuel (autant particuliers qu'institutions)
 * Sert notamment à la gestion des accès concurrents
 */
CREATE TABLE `USER_LOG` (
  `ID_USER` varchar(50) NOT NULL,
  `DATE_DEBUT` datetime NOT NULL,
  `DATE_FIN` datetime DEFAULT NULL,
  PRIMARY KEY (`ID_USER`,`DATE_DEBUT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
 * Table de stockage des utilisateurs connectés par IP
 */
CREATE TABLE `USER_LOG_IP` (
  `ID_USER` varchar(50) NOT NULL,
  `IP_USER` varchar(15) DEFAULT NULL,
  `CDATE` datetime NOT NULL,
  PRIMARY KEY (`ID_USER`,`CDATE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
 * Historique de l'utilisateur sous forme de string JSON.
 * Utilisé en remplacement de l'ancien champ "HISTORIQUE", peu lisible et exploitable
 * Ce champ est initialisé via la conversion du champ HISTORIQUE, au premier login de l'utilisateur dans le nouveau site.
 */
ALTER TABLE `USER`   
  ADD COLUMN `HISTO_JSON` TEXT NULL AFTER `FACT_NOM`;

/*
 * Ce champ sert à stocker le token de ré-initialisation du mot de passe (envoyé dans l'e-mail d'oubli de mot de passe)
 */
ALTER TABLE `USER`   
  ADD COLUMN `MOT_PASSE_TMP` TEXT NULL AFTER `MOT_PASSE`;

/*
 * Permet de donner accès à la petite interface d'administration
 */
ALTER TABLE `USER`   
  ADD COLUMN `ADMIN` tinyint(1) NOT NULL DEFAULT '0';

/*
 * Table de stockage des commandes temporaires (jusqu'au paiement)
 */
CREATE TABLE `COMMANDE_TMP`(  
  `ID_COMMANDE` VARCHAR(50) NOT NULL,
  `ID_USER` VARCHAR(50),
  `DATE` DATETIME,
  `ACHATS` TEXT,
  `FACT_NOM` VARCHAR(50),
  `FACT_ADR` VARCHAR(80),
  `FACT_CP` VARCHAR(10),
  `FACT_VILLE` VARCHAR(30),
  `FACT_PAYS` VARCHAR(20),
  `PRIX` FLOAT,
  `FRAIS_PORT` FLOAT,
  `MODE_PAIEMENT` ENUM('ogone','credit','cheque_vir'),
  PRIMARY KEY (`ID_COMMANDE`)
);

/*
 * Champs de liaison entre les commandes temporaires et les définitives.
 */
ALTER TABLE `COMMANDE_TMP`   
  ADD COLUMN `COMMANDE_NO_COMMANDE` VARCHAR(50) NULL AFTER `MODE_PAIEMENT`,
  ADD COLUMN `CREDIT_NO_COMMANDE` VARCHAR(50) NULL AFTER `COMMANDE_NO_COMMANDE`;

/*
 * Champ utilisé pour connaître le type d'achat (papier ou numérique).
 * Utile notamment pour connaître le taux de TVA à appliquer.
 */
ALTER TABLE `ACHAT`   
  ADD COLUMN `TYPE` VARCHAR(1) DEFAULT 'P'  NOT NULL AFTER `PRIX`;

/*
 * Champs destinés à connaître l'adresse de livraison
 */
ALTER TABLE `COMMANDE_TMP`   
  ADD COLUMN `NOM` VARCHAR(30) NULL AFTER `FACT_PAYS`,
  ADD COLUMN `PRENOM` VARCHAR(20) NULL AFTER `NOM`,
  ADD COLUMN `ADRESSE` VARCHAR(80) NULL AFTER `PRENOM`,
  ADD COLUMN `CP` VARCHAR(10) NULL AFTER `ADRESSE`,
  ADD COLUMN `VILLE` VARCHAR(30) NULL AFTER `CP`,
  ADD COLUMN `PAYS` VARCHAR(20) NULL AFTER `VILLE`;

ALTER TABLE `USER_LOG_IP`   
  CHANGE `ID_USER` `ID_USER_IP` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NOT NULL,
  ADD COLUMN `ID_USER` VARCHAR(50) NOT NULL AFTER `ID_USER_IP`;

ALTER TABLE `USER_LOG_IP`   
  ADD COLUMN `DATE_FIN` DATETIME NULL AFTER `CDATE`;

/*
 * Contient le montant commandé, sachant que le champ PRIX contient le montant commandé + le solde du crédit précédent
 */
ALTER TABLE `CREDIT_ARTICLE`   
  ADD COLUMN `PRIX_CMD` VARCHAR(10) NULL AFTER `PRIX`;

/*
 * Les "TOUCH_DATE" permettent de connaître la dernière activité d'un utilisateur connecté
 * Utilisé pour gérer un timeout d'inactivité => déconnexion
 */
ALTER TABLE `USER_LOG_IP`   
  ADD COLUMN `TOUCH_DATE` DATETIME NULL AFTER `DATE_FIN`;

ALTER TABLE `USER_LOG`   
  ADD COLUMN `TOUCH_DATE` DATETIME NULL AFTER `DATE_FIN`;

ALTER TABLE `USER_LOG`   
  ADD COLUMN `ID_USER_LOG` VARCHAR(50) NOT NULL FIRST, 
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`ID_USER`, `DATE_DEBUT`, `ID_USER_LOG`);

/*
 * Enrichissement de la table des commandes pour connaître toutes les informations liées.
 * Utile notamment pour :
 * - disposer de toutes les informations de manière centralisée (plus besoin d'aller rechercher les infos dans différentes tables)
 * - garder de manière pérenne les informations (notamment les adresses qui peuvent évoluer dans la table USER
 */
ALTER TABLE `COMMANDE_LOG` 
	ADD COLUMN `DATE` DATETIME NULL AFTER `ID_MODEPAIEMENT`, 
	ADD COLUMN `ID_USER` VARCHAR(50) NULL AFTER `DATE`, 
	ADD COLUMN `FACT_NOM` VARCHAR(50) NULL AFTER `ID_USER`, 
	ADD COLUMN `FACT_ADR` VARCHAR(80) NULL AFTER `FACT_NOM`, 
	ADD COLUMN `FACT_CP` VARCHAR(10) NULL AFTER `FACT_ADR`, 
	ADD COLUMN `FACT_VILLE` VARCHAR(30) NULL AFTER `FACT_CP`, 
	ADD COLUMN `FACT_PAYS` VARCHAR(20) NULL AFTER `FACT_VILLE`, 
	ADD COLUMN `NOM` VARCHAR(30) NULL AFTER `FACT_PAYS`, 
	ADD COLUMN `PRENOM` VARCHAR(20) NULL AFTER `NOM`, 
	ADD COLUMN `ADRESSE` VARCHAR(80) NULL AFTER `PRENOM`, 
	ADD COLUMN `CP` VARCHAR(10) NULL AFTER `ADRESSE`, 
	ADD COLUMN `VILLE` VARCHAR(30) NULL AFTER `CP`, 
	ADD COLUMN `PAYS` VARCHAR(20) NULL AFTER `VILLE`; 

/*
 * Comme la gestion des accès concurrents se fait par éjection du connecté par l'arrivant, 
 * ce flag sert à informer "l'éjecté" de la raison de sa déconnexion.
 */
ALTER TABLE `USER_LOG`   
  ADD COLUMN `ALERT_EJECT` INT(1) DEFAULT 0  NULL AFTER `TOUCH_DATE`;

/*
 * Allongement des champs PAYS (jusque la limités à 20 caractères
 */
ALTER TABLE `USER`   
  CHANGE `PAYS` `PAYS` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci DEFAULT ''  NOT NULL,
  CHANGE `FACT_PAYS` `FACT_PAYS` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci DEFAULT ''  NOT NULL;

ALTER TABLE `COMMANDE_LOG`   
  CHANGE `FACT_PAYS` `FACT_PAYS` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NULL,
  CHANGE `PAYS` `PAYS` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `COMMANDE_TMP`   
  CHANGE `FACT_PAYS` `FACT_PAYS` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NULL,
  CHANGE `PAYS` `PAYS` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NULL;