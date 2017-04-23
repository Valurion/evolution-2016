<div class="grid-g grid-3-head" id="page_header">

    <div class="grid-u-1-4">
        <img
            src="/<?= $vign_path ?>/<?= $revue['ID_REVUE'] ?>/<?= $revue['ID_NUMPUBLIE'] ?>_L204.jpg"
            alt="<?php echo $revue['TITRE']; ?>"
            class="big_coverbis">

        <!-- Snippet pour la période de test SHS (début 01/04/2014) -->
        <div id="test_shs" class="blue_button" style="display:none; text-align: center; background-color: rgb(75, 178, 172); margin-top: 0.6em; padding: 0.3em 0; border:0; width: 216px; cursor: text;">Revue en test</div>

    </div>

    <div class="grid-u-1-2 meta">
        <h1 class="title_big_blue title"><?= $revue['TITRE'] ?></h1>
        <h2 class="subtitle_medium_grey subtitle"><?= $revue['STITRE'] ?></h2>
        <ul class="others">
            <li><span class="yellow editor">&#201;diteur :</span> <a
                    href="./editeur.php?ID_EDITEUR=<?= $revue['ID_EDITEUR'] ?>" class="url">
                    <?= $revue['NOM_EDITEUR'] ?> </a></li>
            <?php if ($revue['AFFILIATION'] != "") : ?>
                <li class="wrapper_affiliation">
                    <?= $revue['AFFILIATION'] ?>
                </li>
                <li><p class="subtitle_medium_grey subtitle"></p>
                <?php endif; ?>
                <?php if ($revue['ISSN'] != '') : ?>
                <li><span class="yellow issn">ISSN : </span> <?= $revue['ISSN'] ?></li>
            <?php endif; ?>
            <?php if ($revue['ISSN_NUM'] != '') : ?>
                <li><span class="yellow issn">ISSN en ligne :</span> <?= $revue['ISSN_NUM'] ?></li>
            <?php endif; ?>
            <?php if ($revue['PERIODICITE'] != '') : ?>
                <li><span class="yellow period">P&#233;riodicit&#233; : </span>
                    <?= $revue['PERIODICITE'] ?></li>
            <?php endif; ?>
            <?php if ($revue['WEB'] != '') : ?>
                <li>
                    <a target="_blank" href="<?= $revue['WEB'] ?>">Site internet</a>
                </li>
            <?php endif; ?>
        </ul>
        <form name="rechrevue" action="./resultats_recherche.php"
              method="get" class="search_inside">
            <button type="submit">
                <img src="./static/images/icon/magnifying-glass-black.png">
            </button>
            <input type="text" placeholder="Chercher dans la collection"
                   name="searchTerm" /> <input type="hidden" name="ID_REVUE"
                   value="<?= isset($revue['REVUE_ID_REVUE'])?$revue['REVUE_ID_REVUE']:$revue['ID_REVUE'] ?>" />
        </form>
    </div>
    <div class="grid-u-1-4">
        <?php $numero = $numeros[0];
        include (__DIR__ . '/../../CommonBlocs/alertesEmail.php');?>
    </div>
</div>


<?php
$script = <<<'EOD'
    if (cairn.metadata['inst_id_user'] == 'biblio_shs') {
        $(function() {
            campaigns.shs_03_2014_stack.push(["REVUE_ID_REVUE", $("#test_shs")]);
            campaigns.shs_03_2014();
        })
    }
EOD;
$this->javascripts[] = str_replace(
    'REVUE_ID_REVUE',
    isset($revue['REVUE_ID_REVUE']) ? $revue['REVUE_ID_REVUE'] : $revue['ID_REVUE'],
    $script
);
?>
