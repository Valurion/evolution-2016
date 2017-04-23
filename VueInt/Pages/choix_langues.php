<?php
$this->titre = $currentArticle["ARTICLE_TITRE"];
?>

<div id="body-content">
    <div class="grid-g grid-2">
        <div class="grid-u-1-2">
            <div style="padding-right: 2em;">
                <h2>English</h2>
                <p>This article is also available in English on Cairn International Edition</p>
            </div>
            <div class="article" style="padding-right: 2em;">

                    <a href="article.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" style="float: left;" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>>
                        <img src="/<?= $vign_path ?>/<?= $currentArticle['ARTICLE_ID_REVUE'] ?>/<?= $currentArticle['ARTICLE_ID_NUMPUBLIE'] ?>_L62.jpg" class="small_cover" alt="couverture de <?= $currentArticle['ARTICLE_ID_NUMPUBLIE'] ?>">
                    </a>
                    <div class="meta" style="padding-left: 90px;">
                        <h3 class="title">
                            <a href="article.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>>
                                <?= $currentArticle['ARTICLE_TITRE'] ?>
                            </a>
                        </h3>
                        <h4 class="subtitle">
                            <?= $currentArticle['ARTICLE_SOUSTITRE'] ?>
                        </h4>
                        <div class="authors yellow"><!--
                            --><?php foreach (explode(',', $currentArticle['ARTICLE_AUTEUR']) as $index => $auteur): ?><!--
                                <?php $auteur = explode(':', $auteur); ?>
                                --><?php if ($index > 0): ?>, <?php endif; ?><?= $auteur[3] ?> <?= $auteur[0] ?> <?= $auteur[1] ?><!--
                            --><?php endforeach; ?><!--
                        --></div>
                    </div>
            </div>
        </div>
        <div class="grid-u-1-2">
            <div style="padding-bottom: 21px;">
                <h2>Français</h2>
                <p>Cet article est disponible en français sur Cairn.info</p>
            </div>
            <div class="article">

                    <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/article.php?ID_ARTICLE=<?= $numero['META_ARTICLE_CAIRN']['ID_ARTICLE'] ?>" style="float: left;" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>>
                        <img src="/<?= $vign_path ?>/<?= $numero['META_ARTICLE_CAIRN']['ID_REVUE'] ?>/<?= $numero['META_ARTICLE_CAIRN']['ID_NUMPUBLIE'] ?>_L61.jpg" class="small_cover" alt="couverture de <?= $numero['META_ARTICLE_CAIRN']['ID_NUMPUBLIE'] ?>">
                    </a>
                    <div class="meta" style="padding-left: 90px;">
                        <h3 class="title">
                            <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/article.php?ID_ARTICLE=<?= $numero['META_ARTICLE_CAIRN']['ID_ARTICLE'] ?>" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>>
                                <?= $numero['META_ARTICLE_CAIRN']['TITRE'] ?>
                            </a>
                        </h3>
                        <h4 class="subtitle">
                            <?= $numero['META_ARTICLE_CAIRN']['SOUSTITRE'] ?>
                        </h4>
                        <div class="authors yellow"><!--
                            --><?php foreach (explode(',', $numero['META_ARTICLE_CAIRN']['AUTEUR']) as $index => $auteur): ?><!--
                                <?php $auteur = explode(':', $auteur); ?>
                                --><?php if ($index > 0): ?>, <?php endif; ?><?= $auteur[3] ?> <?= $auteur[0] ?> <?= $auteur[1] ?><!--
                            --><?php endforeach; ?><!--
                        --></div>
                    </div>
            </div>
        </div>
    </div>
</div>
