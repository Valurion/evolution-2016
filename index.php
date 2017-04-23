<?php

session_start();

if (!isset($token)) $token = null;  // Dirty-fix, parce que j'en ai marre d'avoir des milliards de warnings dans les logs
setcookie('ID_REVUE', $token, time() - 3600, '/', '', 0);
unset($_COOKIE["ID_REVUE"]);

// Contrôleur frontal : instancie un routeur pour traiter la requête entrante

require 'Framework/Routeur.php';

$routeur = new Routeur();
$routeur->routerRequete();
