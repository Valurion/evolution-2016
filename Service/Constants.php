<?php

class Constants {
    // Les types de publications disponible sur cairn
    CONST TYPEPUB_REVUE = 1;
    CONST TYPEPUB_MAGAZINE = 2;
    CONST TYPEPUB_OUVRAGE = 3;
    CONST TYPEPUB_EDM = 4;
    CONST TYPEPUB_MONOGRAPHIE = 5;
    CONST TYPEPUB_ENCYCLOPEDIE = 6;

    // Les types de données sur cairn
    CONST IS_REVUE = 'REVUE/COLLECTION';
    CONST IS_NUMERO = 'NUMERO/OUVRAGE';
    CONST IS_ARTICLE = 'ARTICLE/CHAPITRE';
    CONST IS_AUTEUR = 'AUTEUR';


    // Les constantes utilisés par l'autocomplete, pour les categories à rechercher
    const AUTOCOMPLETE_CATEGORY_AUTEUR = 'A';
    const AUTOCOMPLETE_CATEGORY_REVUE = 'R';
    const AUTOCOMPLETE_CATEGORY_OUVRAGE = 'O';
    const AUTOCOMPLETE_CATEGORY_EXPRESSION = 'E';
}
