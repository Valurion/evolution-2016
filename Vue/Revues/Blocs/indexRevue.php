<div class="grid-g grid-3-head" id="page_header">

    <div class="grid-u-1-4">
        <img
            src="/<?= $vign_path ?>/<?= $revue['ID_REVUE'] ?>/<?= $numeros[0]['NUMERO_ID_NUMPUBLIE'] ?>_L204.jpg"
            alt="<?php echo $revue['TITRE'] . " " . (isset($numero)?($numero['NUMERO_ANNEE'] . '/' . $numero['NUMERO_NUMERO'] - $numero['NUMERO_NUMEROA']):""); ?>"
            class="big_coverbis">


    </div>

    <div class="grid-u-1-2 meta">
        <h1 class="title_big_blue title">
            <?= $revue['TITRE'] ?>
        </h1>
        <h2 class="subtitle_medium_grey subtitle"><?= $revue['STITRE'] ?></h2>
        <ul class="others">
            <?php if (Configuration::get('allow_backoffice', false)): ?>
                <span class="yellow id-revue">Id Revue : </span>
                <?= $revue['ID_REVUE'] ?>
                (<a href="<?= Configuration::get('backoffice', '#') ?>?controleur=Revues&amp;action=index&amp;ID_REVUE=<?= $revue['ID_REVUE'] ?>" class="bo-content" target="_blank">back-office</a>)
            <?php endif; ?>
            <li>
                <span class="yellow editor">Éditeur :</span>
                <a href="./editeur.php?ID_EDITEUR=<?= $revue['ID_EDITEUR'] ?>" class="url">
                    <?= $revue['NOM_EDITEUR'] ?>
                </a>
            </li>
            <li>
                <span class="yellow editor">Sur Cairn.info :</span>
                Années <?php echo $revue['LIMITES']['MIN']." à ".$revue['LIMITES']['MAX']; ?>
            </li>
            <?php if ($revue['AFFILIATION'] != ""): ?>
                <li class="wrapper_affiliation">
                    <?= $revue['AFFILIATION'] ?>
                </li>
            <?php endif; ?>
            <?php if ($revue['PERIODICITE'] != ''): ?>
                <li>
                    <span class="yellow period">Périodicité : </span>
                    <?= $revue['PERIODICITE'] ?>
                </li>
            <?php endif ?>
            <?php if ($revue['ISSN']): ?>
                <li>
                    <span class="yellow issn">ISSN : </span>
                    <?= $revue['ISSN'] ?>
                </li>
            <?php endif; ?>
            <?php if ($revue['ISSN_NUM'] != ''): ?>
                <li>
                    <span class="yellow issn">ISSN en ligne :</span>
                    <?= $revue['ISSN_NUM'] ?>
                </li>
            <?php endif; ?>            
            <?php if ($revue['WEB'] != ''): ?>
                <li>
                    <!--span class="yellow website">Site internet : </span>-->
                    <a target="_blank" href="<?= $revue['WEB'] ?>">Site de la revue</a>
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
            foreach ($numeros as $oneNumero) {
                if ($oneNumero['NUMERO_NB_ARTICLES'] != '0') {
                    $numero = $oneNumero;
                    break;
                }
            }
        ?>
        
        <!-- Raccourcis -->
        <div id="raccourcis" class="contrast-box">
            <h1>Raccourcis</h1>
            <ul>
                <?php if (!isset($modeIndex) || $modeIndex != 'apropos') { ?><li><a href="en-savoir-plus-sur-la-revue-<?= $revue['URL_REWRITING'] ?>.htm">À propos de cette revue <span class="icon-arrow-black-right icon right"></span></a></li><?php } ?>
                <li><a href="revue-<?= $revue['URL_REWRITING'] ?>.htm#liste">Liste des numéros <span class="icon-arrow-black-right icon right"></span></a></li>
                <?php if($mostConsultated) { ?><li><a href="#articlesLesPlusConsultes">Articles les plus consultés <span class="icon-arrow-black-right icon right"></span></a></li><?php } ?>
                <?php if($revue["ACCES_EN_LIGNE"] == 1) {?><li><a href="code-abonnement-papier.php?ID_REVUE=<?php echo $revue['ID_REVUE']; ?>">Accès abonnés <span class="icon-arrow-black-right icon right"></span></a></li><?php } ?>
            </ul>
        </div>

        <?php
            // Bloc des alertes e-mails
            include (__DIR__ . '/../../CommonBlocs/alertesEmail.php');
        ?>

        <?php if ($revue["ID_REVUE_INT"] != '') { ?>
            <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/journal-<?php echo $revue["URL_REWRITING_INT"]; ?>.htm" class="cairn-int_link"><span class="label">Browse this journal in English</span></a>
        <?php } ?>
    </div>
</div>
<?php
if(isset($typesAchat['DISPLAY_BLOC_ACHAT'])){
    include __DIR__."/../../CommonBlocs/blocAddToBasket.php";
}
?>

