<?php $this->titre = "Erreur"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php');?>

<div id="breadcrump">
    <a class="inactive" href="<?php echo 'http://' . Configuration::get('urlSite') ?>">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Erreur</a>
</div>

<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">
            Erreur...
        </h1>
        <p style="margin-bottom: 15em;">
            Le contenu auquel vous voulez accéder n’est pas disponible à l'adresse <?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?><br />
            Peut-être devriez-vous lancer une
            <a href="<?php echo 'http://' . Configuration::get('urlSite') . '/recherche_avancee.php' ?>" class="link-underline">
                recherche
            </a>
            ou passer par notre
            <a href="<?php echo 'http://' . Configuration::get('urlSite') ?>" class="link-underline">
                page d’accueil
            </a>
            afin de continuer votre visite ?
        </p>
    </div>
</div>
