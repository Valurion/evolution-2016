<?php $this->titre = "Activation accès abonnés particuliers"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <div id="breadcrump_main">
        <a class="inactive" href="/">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
        <a href="code-abonnement-papier.php">Activation d'accès</a>
    </div>
</div>

<div id="body-content">
    <div id="free_text" class="biblio">
        <h1 class="main-title">Confirmation d'accès</h1>

        <p>Votre abonnement à la revue <?= $revue['TITRE'] ?> a bien été activé</p>
        <p><a href='/revue-<?= $revue['URL_REWRITING'] ?>.htm'>Accéder à la revue</a>&nbsp;&nbsp;<a href='/Accueil_Revues.php'>Retour à l'accueil</a></p>

    </div>
</div>
