<div class="grid-u-1-4 numero meta">
    <h1 class="title_big_blue revue_title"><?php echo (($typePub == "revue" || $typePub == "magazine") ? $revue["REVUE_TITRE"] : $numero["NUMERO_TITRE"]); ?></h1>
    <h3 class="text_medium reference">
        <?php echo $revue["NUMERO_ANNEE"] . ($revue["NUMERO_NUMERO"] != "" ? ("/" . $revue["NUMERO_NUMERO"]) : "") . " " . ($revue["NUMERO_VOLUME"] != '' ? '(' . $revue["NUMERO_VOLUME"] . ')' : ''); ?>
    </h3>
    <ul class="others">
        <?php if ($revue["NUMERO_NB_PAGE"] != '') { ?>
            <li>
                <span class="yellow nb_pages">Pages : </span><?php echo $revue["NUMERO_NB_PAGE"]; ?>
            </li>
        <?php } ?>
        <?php if ($revue["NUMERO_EAN"] != '') { ?>
            <li>
                <span class="yellow issn">ISBN : </span><?php echo $revue["NUMERO_EAN"]; ?>
            </li>
        <?php } ?>
        <?php if ($currentArticle["ARTICLE_DOI"] != '') { ?>
            <li>
                <span class="yellow">DOI : </span><?= $currentArticle["ARTICLE_DOI"] ?>
            </li>
        <?php } ?>
        <li>
            <span class="yellow ">Publisher : </span>
            <a href="./publisher.php?ID_EDITEUR=<?php echo $revue["REVUE_ID_EDITEUR"]; ?>"><?php echo $revue["EDITEUR_NOM_EDITEUR"]; ?></a>
        </li>
        <?php if (Configuration::get('allow_backoffice', false)): ?>
            <!-- Permet de retrouver les identifiants plus facilement sur le backoffice -->
            <li>
                <span class="yellow">Id Revue : </span>
                <?= $currentArticle['REVUE_ID_REVUE'] ?>
            </li>
            <li>
                <span class="yellow">Id Numpublie : </span>
                <?= $currentArticle['NUMERO_ID_NUMPUBLIE'] ?>
            </li>
            <li>
                <span class="yellow">Id Article : </span>
                <?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>
            </li>
            <li>
                (<a href="<?= Configuration::get('menu_conversion', '#').'?ID_NUMPUBLIE='.$currentArticle['NUMERO_ID_NUMPUBLIE'].'&ID_REVUE='.$currentArticle['REVUE_ID_REVUE'] ?>" class="bo-content" target="_blank">Menu conversion</a>)
            </li>
            <li>
                <!-- Lien vers revue -->
                (<a href="<?= Configuration::get('backoffice', '#') ?>?controleur=Revues&amp;action=index&amp;ID_REVUE=<?= $currentArticle['REVUE_ID_REVUE'] ?>" class="bo-content" target="_blank">Revue back-office</a>)
            </li>
            <li>
                <!-- Lien vers numéro -->
                (<a href="<?= Configuration::get('backoffice', '#') ?>?controleur=Revues&amp;action=numero&amp;ID_NUMPUBLIE=<?= $currentArticle['NUMERO_ID_NUMPUBLIE'] ?>" class="bo-content" target="_blank">Numéro back-office</a>)
            </li>
            <li>
                <!-- Lien vers article -->
                (<a href="<?= Configuration::get('backoffice', '#') ?>?controleur=Revues&amp;action=article&amp;ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" class="bo-content" target="_blank">Article back-office</a>)
            </li>
        <?php endif; ?>

    </ul>

    <ul class="list_links">
        <li>
            <a href="about_this_journal.php?ID_REVUE=<?php echo $revue["REVUE_ID_REVUE"]; ?>">Journal information
                <span class="icon-arrow-black-right icon right"></span>
            </a>
        </li>
        <li><a target="_blank" href="/list_articles_fulltext.php?ID_REVUE=<?php echo $revue["REVUE_ID_REVUE"]; ?>">English full text articles <span class="icon-arrow-black-right icon right"></span></a></li>
        <li><a target="_blank" href="<?php echo $revue["REVUE_WEB"]; ?>">Journal Website <span class="icon-arrow-black-right icon right"></span></a></li>
    </ul>

    <hr class="grey">

    <?php include (__DIR__ . '/../../CommonBlocs/alertesEmail.php');?>
</div>
