/*
 * Modifications relatives à la gestion des (co-)citations sur les numéros
 * (seulement sur les articles auparavant).
 */
ALTER TABLE `NUMERO`   
  ADD COLUMN `DOI` VARCHAR(40) NULL AFTER `STATUT`;

ALTER TABLE `CAIRN_REFERENCES`   
  CHANGE `ID_CAIRN_CIBLE` `ID_CAIRN_CIBLE` VARCHAR(30) DEFAULT ''  NULL,
  ADD COLUMN `ID_CAIRN_NUM` VARCHAR(30) DEFAULT ''  NULL AFTER `ID_CAIRN_CIBLE`;

/* 
 * Nouveau champ destiné au prix à appliquer à un achat de numéro au format électronique uniquement
 */
ALTER TABLE `NUMERO`
  ADD COLUMN `PRIX_ELEC` FLOAT DEFAULT 0 NOT NULL AFTER `PRIX`;

/*
 * Stockage du crédit d'article comme mode de paiement à part entière
 */
INSERT INTO MODE_PAIEMENT (ID_MODEPAIEMENT, LIBELLE) 
  VALUES (6,'Crédit d''articles');

/* 
 * Nouveau champ destiné à la vente de numéro sous forme d'epub
 */
ALTER TABLE `NUMERO`   
  ADD COLUMN `EPUB` INT(1) DEFAULT 0  NULL AFTER `DATEMAJ`;

/* 
 * Nouveaux index, amélioration des performances
 */
ALTER TABLE `AUTEUR_ART` 
	ADD KEY `ID_AUTEUR`(`ID_AUTEUR`,`ID_ARTICLE`,`ID_NUMPUBLIE`) , 
	ADD KEY `ID_NUMPUBLIE`(`ID_NUMPUBLIE`,`ID_AUTEUR`) ;

ALTER TABLE `DISCIPLINE` 
	ADD KEY `PARENT`(`PARENT`,`URL_REWRITING`(10),`DISCIPLINE`) ;

ALTER TABLE `NUMERO` 
	ADD KEY `DATE_PARUTION`(`DATE_PARUTION`) , 
	ADD KEY `ID_REVUE`(`ID_REVUE`,`DATE_PARUTION`) ;

/*
 * Vues vers les tables EvidenSSE, notamment pour les sujets proches
 */
CREATE VIEW `docsRS` 
    AS
(SELECT * FROM evidensse.`docsRS`);

CREATE VIEW `meta` 
    AS
(SELECT * FROM evidensse.`meta`);

/* Modification concerant la nouvelle règlementation TVA : 
 * appliquer la TVA en fonction du pays de facturation
 *
 * On regroupe tous les pays dans une table, avec un statut "isEU" et les taux à appliquer
 * Cela permet également de ne plus avoir les pays en dur dans l'appli...
 */
CREATE TABLE `PAYS`(
  `FUPDATE` INT(1),
  `PAYS` VARCHAR(100) NOT NULL,
  `IS_EU` INT(1) DEFAULT 0,
  `TAUX_TVA` FLOAT DEFAULT 0,
  `TAUX_TVA_REDUIT` FLOAT DEFAULT 0,
  PRIMARY KEY (`PAYS`)
);


/*
 * Ajout du pays de manière à différencier les idP FR (renater) et Internationaux (EduGain)
 */
ALTER TABLE `SSOCAS`   
  ADD COLUMN `PAYS` VARCHAR(50) NULL AFTER `TICKET_VALIDATION`;

/*******************/
/* DONNEES DE TEST */
/*******************/
/*UPDATE NUMERO SET PRIX_ELEC = '12', EPUB = 1 WHERE ID_NUMPUBLIE = 'AFCO_248';
UPDATE NUMERO SET DOI = '10.3917/arss.136' WHERE ID_NUMPUBLIE = 'ARSS_136';
UPDATE CAIRN_REFERENCES SET ID_CAIRN_NUM = 'ARSS_136'  WHERE ID_CAIRN_CIBLE = 'ARSS_136_0005';*/ 

/****************/
/* DONNEES PAYS */
/****************/

insert into PAYS (PAYS) values ('Afrique du Sud');
insert into PAYS (PAYS) values ('Albanie');
insert into PAYS (PAYS) values ('Algérie');
insert into PAYS (PAYS) values ('Allemagne');
insert into PAYS (PAYS) values ('Andorre');
insert into PAYS (PAYS) values ('Angleterre');
insert into PAYS (PAYS) values ('Angola');
insert into PAYS (PAYS) values ('Anguilla');
insert into PAYS (PAYS) values ('Antartique');
insert into PAYS (PAYS) values ('Antigua et Barbuda');
insert into PAYS (PAYS) values ('Antilles néerlandaises');
insert into PAYS (PAYS) values ('Arabie Saoudite');
insert into PAYS (PAYS) values ('Argentine');
insert into PAYS (PAYS) values ('Arménie');
insert into PAYS (PAYS) values ('Aruba');
insert into PAYS (PAYS) values ('Australie');
insert into PAYS (PAYS) values ('Autriche');
insert into PAYS (PAYS) values ('Azerbaïdjan');
insert into PAYS (PAYS) values ('Bahamas');
insert into PAYS (PAYS) values ('Bahreïn');
insert into PAYS (PAYS) values ('Bangladesh');
insert into PAYS (PAYS) values ('Barbades');
insert into PAYS (PAYS) values ('Belgique');
insert into PAYS (PAYS) values ('Belize');
insert into PAYS (PAYS) values ('Bénin');
insert into PAYS (PAYS) values ('Bermudes');
insert into PAYS (PAYS) values ('Bhoutan');
insert into PAYS (PAYS) values ('Biélorussie');
insert into PAYS (PAYS) values ('Bolivie');
insert into PAYS (PAYS) values ('Bosnie-Herzégovine');
insert into PAYS (PAYS) values ('Botswana');
insert into PAYS (PAYS) values ('Brésil');
insert into PAYS (PAYS) values ('Brunei Darussalam');
insert into PAYS (PAYS) values ('Bulgarie');
insert into PAYS (PAYS) values ('Burkina Faso');
insert into PAYS (PAYS) values ('Burundi');
insert into PAYS (PAYS) values ('Cambodge');
insert into PAYS (PAYS) values ('Cameroun');
insert into PAYS (PAYS) values ('Canada');
insert into PAYS (PAYS) values ('Cap Vert');
insert into PAYS (PAYS) values ('Chili');
insert into PAYS (PAYS) values ('Chine');
insert into PAYS (PAYS) values ('Chypre');
insert into PAYS (PAYS) values ('Colombie');
insert into PAYS (PAYS) values ('Comores');
insert into PAYS (PAYS) values ('Congo');
insert into PAYS (PAYS) values ('Congo, République Démocratique du');
insert into PAYS (PAYS) values ('Corée du sud');
insert into PAYS (PAYS) values ('Corée, République de');
insert into PAYS (PAYS) values ('Corée (sud)');
insert into PAYS (PAYS) values ('Corse');
insert into PAYS (PAYS) values ('Costa Rica');
insert into PAYS (PAYS) values ('Côte d''Ivoire');
insert into PAYS (PAYS) values ('Croatie');
insert into PAYS (PAYS) values ('Danemark');
insert into PAYS (PAYS) values ('Djibouti');
insert into PAYS (PAYS) values ('Dominique');
insert into PAYS (PAYS) values ('Écosse');
insert into PAYS (PAYS) values ('Égypte');
insert into PAYS (PAYS) values ('Émirats arabes unis');
insert into PAYS (PAYS) values ('Équateur');
insert into PAYS (PAYS) values ('Éritrée');
insert into PAYS (PAYS) values ('Espagne');
insert into PAYS (PAYS) values ('Estonie');
insert into PAYS (PAYS) values ('États-Unis');
insert into PAYS (PAYS) values ('Éthiopie');
insert into PAYS (PAYS) values ('Fédération de Russie');
insert into PAYS (PAYS) values ('Fidji');
insert into PAYS (PAYS) values ('Finlande');
insert into PAYS (PAYS) values ('France');
insert into PAYS (PAYS) values ('Gabon');
insert into PAYS (PAYS) values ('Gambie');
insert into PAYS (PAYS) values ('Géorgie');
insert into PAYS (PAYS) values ('Géorgie du sud et Îles Sandwich du sud');
insert into PAYS (PAYS) values ('Ghana');
insert into PAYS (PAYS) values ('Gibraltar');
insert into PAYS (PAYS) values ('Grande Bretagne');
insert into PAYS (PAYS) values ('Grèce');
insert into PAYS (PAYS) values ('Groënland');
insert into PAYS (PAYS) values ('Guadeloupe');
insert into PAYS (PAYS) values ('Guam');
insert into PAYS (PAYS) values ('Guatemala');
insert into PAYS (PAYS) values ('Guinée');
insert into PAYS (PAYS) values ('Guinée-Bissau');
insert into PAYS (PAYS) values ('Guinée équatoriale');
insert into PAYS (PAYS) values ('Guyane');
insert into PAYS (PAYS) values ('Guyane française');
insert into PAYS (PAYS) values ('Haïti');
insert into PAYS (PAYS) values ('Honduras');
insert into PAYS (PAYS) values ('Hong-Kong');
insert into PAYS (PAYS) values ('Hongrie');
insert into PAYS (PAYS) values ('Ile Christmas');
insert into PAYS (PAYS) values ('Ile de la Réunion');
insert into PAYS (PAYS) values ('Ile Maurice');
insert into PAYS (PAYS) values ('Ile Norfolk');
insert into PAYS (PAYS) values ('Îles Bouvet');
insert into PAYS (PAYS) values ('Îles Caïmans');
insert into PAYS (PAYS) values ('Îles Cocos-Keeling');
insert into PAYS (PAYS) values ('Îles Cook');
insert into PAYS (PAYS) values ('Îles Féroé');
insert into PAYS (PAYS) values ('Îles Heard et Mc Donald');
insert into PAYS (PAYS) values ('Îles Malouines');
insert into PAYS (PAYS) values ('Îles Mariannes du nord');
insert into PAYS (PAYS) values ('Îles Marshall');
insert into PAYS (PAYS) values ('Îles Salomon');
insert into PAYS (PAYS) values ('Îles Svalbard et Jan Mayen');
insert into PAYS (PAYS) values ('Îles Turks et Caicos');
insert into PAYS (PAYS) values ('Îles Vierges américaines');
insert into PAYS (PAYS) values ('Îles Vierges britanniques');
insert into PAYS (PAYS) values ('Inde');
insert into PAYS (PAYS) values ('Indonésie');
insert into PAYS (PAYS) values ('Irlande');
insert into PAYS (PAYS) values ('Irlande du nord');
insert into PAYS (PAYS) values ('Islande');
insert into PAYS (PAYS) values ('Israël');
insert into PAYS (PAYS) values ('Italie');
insert into PAYS (PAYS) values ('Jamaïque');
insert into PAYS (PAYS) values ('Japon');
insert into PAYS (PAYS) values ('Jordanie');
insert into PAYS (PAYS) values ('Kazakhstan');
insert into PAYS (PAYS) values ('Kenya');
insert into PAYS (PAYS) values ('Kirghizistan');
insert into PAYS (PAYS) values ('Kiribati');
insert into PAYS (PAYS) values ('Koweït');
insert into PAYS (PAYS) values ('La Grenade');
insert into PAYS (PAYS) values ('Les Açores');
insert into PAYS (PAYS) values ('Les Îles Canaries');
insert into PAYS (PAYS) values ('Lesotho');
insert into PAYS (PAYS) values ('Lettonie');
insert into PAYS (PAYS) values ('Liban');
insert into PAYS (PAYS) values ('Liberia');
insert into PAYS (PAYS) values ('Libye');
insert into PAYS (PAYS) values ('Lichtenstein');
insert into PAYS (PAYS) values ('Lituanie');
insert into PAYS (PAYS) values ('Luxembourg');
insert into PAYS (PAYS) values ('Macao');
insert into PAYS (PAYS) values ('Macédoine');
insert into PAYS (PAYS) values ('Madagascar');
insert into PAYS (PAYS) values ('Malaisie');
insert into PAYS (PAYS) values ('Malawi');
insert into PAYS (PAYS) values ('Maldives');
insert into PAYS (PAYS) values ('Mali');
insert into PAYS (PAYS) values ('Malte');
insert into PAYS (PAYS) values ('Maroc');
insert into PAYS (PAYS) values ('Martinique');
insert into PAYS (PAYS) values ('Mauritanie');
insert into PAYS (PAYS) values ('Mayotte');
insert into PAYS (PAYS) values ('Mexique');
insert into PAYS (PAYS) values ('Micronésie, états Fédérés de');
insert into PAYS (PAYS) values ('Moldavie, République de');
insert into PAYS (PAYS) values ('Monaco');
insert into PAYS (PAYS) values ('Mongolie');
insert into PAYS (PAYS) values ('Montserrat');
insert into PAYS (PAYS) values ('Mozambique');
insert into PAYS (PAYS) values ('Myanmar');
insert into PAYS (PAYS) values ('Namibie');
insert into PAYS (PAYS) values ('Nauru');
insert into PAYS (PAYS) values ('Népal');
insert into PAYS (PAYS) values ('Nicaragua');
insert into PAYS (PAYS) values ('Niger');
insert into PAYS (PAYS) values ('Nigéria');
insert into PAYS (PAYS) values ('Niue');
insert into PAYS (PAYS) values ('Norvège');
insert into PAYS (PAYS) values ('Nouvelle Calédonie');
insert into PAYS (PAYS) values ('Nouvelle Zélande');
insert into PAYS (PAYS) values ('Oman');
insert into PAYS (PAYS) values ('Ouganda');
insert into PAYS (PAYS) values ('Ouzbékistan');
insert into PAYS (PAYS) values ('Pakistan');
insert into PAYS (PAYS) values ('Palau');
insert into PAYS (PAYS) values ('Panama');
insert into PAYS (PAYS) values ('Papouasie-Nouvelle-Guinée');
insert into PAYS (PAYS) values ('Paraguay');
insert into PAYS (PAYS) values ('Pays-Bas');
insert into PAYS (PAYS) values ('Pays de Galles');
insert into PAYS (PAYS) values ('Pérou');
insert into PAYS (PAYS) values ('Philippines');
insert into PAYS (PAYS) values ('Pitcairn');
insert into PAYS (PAYS) values ('Pologne');
insert into PAYS (PAYS) values ('Polynésie Française');
insert into PAYS (PAYS) values ('Portugal');
insert into PAYS (PAYS) values ('Puerto Rico');
insert into PAYS (PAYS) values ('Qatar');
insert into PAYS (PAYS) values ('République Centrafricaine');
insert into PAYS (PAYS) values ('République démocratique populaire du Laos');
insert into PAYS (PAYS) values ('République Dominicaine');
insert into PAYS (PAYS) values ('République Tchèque');
insert into PAYS (PAYS) values ('Roumanie');
insert into PAYS (PAYS) values ('Royaume Uni');
insert into PAYS (PAYS) values ('Russie');
insert into PAYS (PAYS) values ('Rwanda');
insert into PAYS (PAYS) values ('Sahara occidental');
insert into PAYS (PAYS) values ('Sainte-Lucie');
insert into PAYS (PAYS) values ('Saint Marin');
insert into PAYS (PAYS) values ('Salvador');
insert into PAYS (PAYS) values ('Samoa Américaines');
insert into PAYS (PAYS) values ('Samoa (Indépendante)');
insert into PAYS (PAYS) values ('Sâo Tome et Prìncipe');
insert into PAYS (PAYS) values ('Sénégal');
insert into PAYS (PAYS) values ('Serbie-et-Monténégro');
insert into PAYS (PAYS) values ('Seychelles');
insert into PAYS (PAYS) values ('Sierra Leone');
insert into PAYS (PAYS) values ('Singapour');
insert into PAYS (PAYS) values ('Slovaquie');
insert into PAYS (PAYS) values ('Slovénie');
insert into PAYS (PAYS) values ('Somalie');
insert into PAYS (PAYS) values ('Sri Lanka');
insert into PAYS (PAYS) values ('Ste Hélène');
insert into PAYS (PAYS) values ('St Kitts et Nevis');
insert into PAYS (PAYS) values ('St. Pierre et Miquelon');
insert into PAYS (PAYS) values ('St Vincent et les Grenadines');
insert into PAYS (PAYS) values ('Suède');
insert into PAYS (PAYS) values ('Suisse');
insert into PAYS (PAYS) values ('Surinam');
insert into PAYS (PAYS) values ('Swaziland');
insert into PAYS (PAYS) values ('Tadjikistan');
insert into PAYS (PAYS) values ('Taïwan');
insert into PAYS (PAYS) values ('Tanzanie');
insert into PAYS (PAYS) values ('Tchad');
insert into PAYS (PAYS) values ('Territoire indien britannique');
insert into PAYS (PAYS) values ('Territoires français du sud');
insert into PAYS (PAYS) values ('Thaïlande');
insert into PAYS (PAYS) values ('Timor Est');
insert into PAYS (PAYS) values ('Togo');
insert into PAYS (PAYS) values ('Tokelau');
insert into PAYS (PAYS) values ('Tonga');
insert into PAYS (PAYS) values ('Trinité');
insert into PAYS (PAYS) values ('Trinité-et-Tobago');
insert into PAYS (PAYS) values ('Tunisie');
insert into PAYS (PAYS) values ('Turkménistan');
insert into PAYS (PAYS) values ('Turquie');
insert into PAYS (PAYS) values ('Tuvalu');
insert into PAYS (PAYS) values ('Ukraine');
insert into PAYS (PAYS) values ('Uruguay');
insert into PAYS (PAYS) values ('USA');
insert into PAYS (PAYS) values ('Vanuatu');
insert into PAYS (PAYS) values ('Vatican');
insert into PAYS (PAYS) values ('Venezuela');
insert into PAYS (PAYS) values ('Vietnam');
insert into PAYS (PAYS) values ('Wallis-et-Futuna');
insert into PAYS (PAYS) values ('Yémen');
insert into PAYS (PAYS) values ('Zambie');
insert into PAYS (PAYS) values ('Zimbabwe');

update PAYS set IS_EU = 1 where PAYS IN (SELECT PAYS FROM PAYS_EU);

UPDATE PAYS SET TAUX_TVA = '19', TAUX_TVA_REDUIT = '7' WHERE PAYS = 'Allemagne';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Angleterre';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '10' WHERE PAYS = 'Autriche';
UPDATE PAYS SET TAUX_TVA = '21', TAUX_TVA_REDUIT = '6' WHERE PAYS = 'Belgique';
UPDATE PAYS SET TAUX_TVA = '19', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Chypre';
UPDATE PAYS SET TAUX_TVA = '25', TAUX_TVA_REDUIT = '0' WHERE PAYS = 'Danemark';
UPDATE PAYS SET TAUX_TVA = '21', TAUX_TVA_REDUIT = '10' WHERE PAYS = 'Espagne';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '9' WHERE PAYS = 'Estonie';
UPDATE PAYS SET TAUX_TVA = '24', TAUX_TVA_REDUIT = '10' WHERE PAYS = 'Finlande';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '5.5' WHERE PAYS = 'France';
UPDATE PAYS SET TAUX_TVA = '23', TAUX_TVA_REDUIT = '6.5' WHERE PAYS = 'Grèce';
UPDATE PAYS SET TAUX_TVA = '27', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Hongrie';
UPDATE PAYS SET TAUX_TVA = '23', TAUX_TVA_REDUIT = '9' WHERE PAYS = 'Irlande';
UPDATE PAYS SET TAUX_TVA = '22', TAUX_TVA_REDUIT = '10' WHERE PAYS = 'Italie';
UPDATE PAYS SET TAUX_TVA = '21', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Lituanie';
UPDATE PAYS SET TAUX_TVA = '15', TAUX_TVA_REDUIT = '6' WHERE PAYS = 'Luxembourg';
UPDATE PAYS SET TAUX_TVA = '18', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Malte';
UPDATE PAYS SET TAUX_TVA = '21', TAUX_TVA_REDUIT = '6' WHERE PAYS = 'Pays-Bas';
UPDATE PAYS SET TAUX_TVA = '23', TAUX_TVA_REDUIT = '8' WHERE PAYS = 'Pologne';
UPDATE PAYS SET TAUX_TVA = '23', TAUX_TVA_REDUIT = '6' WHERE PAYS = 'Portugal';
UPDATE PAYS SET TAUX_TVA = '21', TAUX_TVA_REDUIT = '15' WHERE PAYS = 'République Tchèque';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Royaume Uni';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '10' WHERE PAYS = 'Slovaquie';
UPDATE PAYS SET TAUX_TVA = '25', TAUX_TVA_REDUIT = '6' WHERE PAYS = 'Suède';
UPDATE PAYS SET TAUX_TVA = '4.5', TAUX_TVA_REDUIT = '4.5' WHERE PAYS = 'Andorre';
UPDATE PAYS SET TAUX_TVA = '8.5', TAUX_TVA_REDUIT = '2.1' WHERE PAYS = 'Guadeloupe';
UPDATE PAYS SET TAUX_TVA = '8.5', TAUX_TVA_REDUIT = '2.1' WHERE PAYS = 'Ile de la Réunion';
UPDATE PAYS SET TAUX_TVA = '0', TAUX_TVA_REDUIT = '0' WHERE PAYS = 'Les Îles Canaries';
UPDATE PAYS SET TAUX_TVA = '8.5', TAUX_TVA_REDUIT = '2.1' WHERE PAYS = 'Martinique';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '5.5' WHERE PAYS = 'Monaco';
UPDATE PAYS SET TAUX_TVA = '0', TAUX_TVA_REDUIT = '0' WHERE PAYS = 'Nouvelle Calédonie';
UPDATE PAYS SET TAUX_TVA = '20', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Pays de Galles';
UPDATE PAYS SET TAUX_TVA = '16', TAUX_TVA_REDUIT = '5' WHERE PAYS = 'Polynésie Française';