<?php $this->titre = "Désinscription confirmée"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <a class="inactive" href="<?php echo 'http://' . Configuration::get('urlSite') ?>">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="desinscription_alerte.php">D&eacute;sinscription alerte email</a>
</div>

<div id="body-content">
    <div id="free_text">
        <?php if (isset($revue) && !empty($revue)) : ?>
        <h1 class="main-title">
            D&eacute;sinscription alerte de parution
        </h1>
        <p>
            Votre demande a bien &eacute;t&eacute; prise en compte.<br />
            D&eacute;sormais, vous ne recevrez plus les alertes de parution de la revue <span class="italic"><?= $revue['REVUE_TITRE'] ?></span>.<br />
            Cliquez <a href="<?php echo 'http://' . Configuration::get('urlSite') ?>/mes_alertes.php" class="link-underline">ici</a>, si vous souhaitez ajouter ou supprimer d'autres alertes de parution.
        </p>
        <?php else : ?>
        <h1 class="main-title">
            Erreur
        </h1>
        <p>
            Votre demande n'a pas &eacute;t&eacute; prise en compte.<br />
            Il semblerait que le lien de d&eacute;sinscription ne soit pas correct.<br />
            Cliquez <a href="<?php echo 'http://' . Configuration::get('urlSite') ?>/mes_alertes.php" class="link-underline">ici</a> vous souhaitez supprimer un abonnement à une newsletter.
        </p>
        <?php endif ?>
    </div>
</div>
 
