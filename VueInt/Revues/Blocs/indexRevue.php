<?php
?>
<div class="grid-g grid-3-head" id="page_header">
    <div class="grid-u-1-4">
        <img
            src="/<?= $vign_path ?>/<?= $revue['ID_REVUE'] ?>/<?= $numeros[0]['NUMERO_ID_NUMPUBLIE'] ?>_H310.jpg"
            alt="<?php echo $revue['REVUE_TITRE'] . " " . $revue['NUMERO_ANNEE'] . '/' . $revue['NUMERO_NUMERO'] - $revue['NUMERO_NUMEROA']; ?>"
            class="big_coverbis">
    </div>
    <div class="grid-u-1-2 meta">
        <div class="descriptionPage minHeight">
            <h1 class="title_big_blue title"><?= $revue['TITRE'] ?></h1>
            <?php echo $revue['STITRE'] != '' ?('<h2>'.$revue['STITRE'].'</h2>'):""; ?>
            <p><?= $revue['SAVOIR_PLUS_EN'] ?></p>
            <ul class="others">
                <?php if($modeIndex != 'apropos'){?>
                    <li><span class="yellow editor">Publisher :</span> <a
                        href="./publisher.php?ID_EDITEUR=<?= $revue['ID_EDITEUR'] ?>" class="url">
                        <?= $revue['NOM_EDITEUR'] ?> </a></li>
                <?php } ?>
                <?php if(!empty($revue['WEB'])) { ?>
                    <li><a href="<?= $revue['WEB'] ?>">Website</a></li>
                <?php } ?>
            </ul>
        </div>
        <div class="w100">
            <div class="frenchVersion">
            <?php if($modeIndex == 'apropos'){?>
                <a href="./journal-<?= $revue['URL_REWRITING_EN']?>.htm">List of issues</a>
            <?php }else{ ?>
                <a href="./about_this_journal.php?ID_REVUE=<?= $revue['ID_REVUE'] ?>">Journal Information</a>
            <?php } ?>
            </div>
            <div class="frenchVersion inline-block right">
            <?php if($modeIndex == 'fulltext'){?>
                <a href="./journal-<?= $revue['URL_REWRITING_EN']?>.htm">List of issues</a>
            <?php }else if($modeIndex == 'apropos'){?>
                <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/revue-<?= $revue['URL_REWRITING_CAIRN']?>.htm">Switch to French Edition</a>
            <?php }else if($efta > 0){ ?>
                <a href="./list_articles_fulltext.php?ID_REVUE=<?= $revue['ID_REVUE'] ?>">English Full-text Articles</a>
            <?php } ?>
            </div>
            <?php if($mostConsultated){?>
                <div class="frenchVersion">
                <a href="#MostViewedArticles">Most Viewed Articles</a>
                </div>
            <?php } ?>
            
        </div>
    </div>
    <div class="grid-u-1-4">
        <?php
        $numero = $numeros[0];
        include (__DIR__ . '/../../CommonBlocs/alertesEmail.php');?>
    </div>
</div>

