<div class="grid-g grid-3-head" id="page_header">

    <div class="grid-u-1-4">
        <img
            src="/<?= $vign_path ?>/<?= $revue['ID_REVUE'] ?>/<?= $revue['ID_NUMPUBLIE'] ?>_L204.jpg"
            alt="<?php echo $revue['TITRE']; ?>"
            class="big_cover">
    </div>

    <div class="grid-u-1-2 meta">
        <h1 class="title_big_blue title"><?= $revue['TITRE'] ?></h1>
        <ul class="others">
            <?php if (Configuration::get('allow_backoffice', false)): ?>
                <span class="yellow id-revue">Id Revue : </span>
                <?= $revue['ID_REVUE'] ?>
                (<a href="<?= Configuration::get('backoffice', '#') ?>?controleur=Revues&amp;action=index&amp;ID_REVUE=<?= $revue['ID_REVUE'] ?>" class="bo-content" target="_blank">back-office</a>)
            <?php endif; ?>
            <?php if ($revue['AFFILIATION'] != "") : ?>
                <li class="wrapper_affiliation">
                    <?= $revue['AFFILIATION'] ?>
                </li>
                <li><p class="subtitle_medium_grey subtitle"></p>
                <?php endif; ?>
                <?php if ($revue['PERIODICITE'] != '') { ?>
                <li><span class="yellow period">P&#233;riodicit&#233; : </span>
                    <?= $revue['PERIODICITE'] ?></li>
            <?php } if ($revue['ISSN'] != '') { ?>
                <li><span class="yellow issn">ISSN : </span> <?= $revue['ISSN'] ?></li>
            <?php } if ($revue['NOM_EDITEUR'] != '') { ?>
                <li><span class="yellow editor">&#201;diteur :</span> <a
                        href="./editeur.php?ID_EDITEUR=<?= $revue['ID_EDITEUR'] ?>" class="url">
                        <?= $revue['NOM_EDITEUR'] ?> </a></li>
            <?php } if ($revue['WEB'] != '') : ?>
                <li>
                    <a target="_blank" href="<?= $revue['WEB'] ?>">Site internet</a>
                </li>
            <?php endif; ?>
        </ul>
        <?php
            if(isset($typesAchat['DISPLAY_BLOC_ACHAT'])){
                include __DIR__."/../../CommonBlocs/addToBasket.php";
            }
        ?>
    </div>
    <div class="grid-u-1-4">
        <?php
        $numero = $numeros[0];
        include (__DIR__ . '/../../CommonBlocs/alertesEmail.php');?>
    </div>
</div>
<?php
if(isset($typesAchat['DISPLAY_BLOC_ACHAT'])){
    include __DIR__."/../../CommonBlocs/blocAddToBasket.php";
}
?>

