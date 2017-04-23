<?php

// Contrôleur batch : pré-traite les argv puis instancie un routeur pour traiter la requête entrante

foreach ($argv as $arg) {
    $parts = explode('=', $arg);
    if (count($parts) > 1) {
        $_GET[$parts[0]] = $parts[1];
    }
}

if (isset($_GET['semaphore'])) {
    file_put_contents($_GET['semaphore'], "running");
}

require 'Framework/Routeur.php';

$routeur = new Routeur();
$routeur->routerRequete();

if (isset($_GET['semaphore']) && is_file($_GET['semaphore'])) {
    unlink($_GET['semaphore']);
}
