<?php
$this->titre = "My purchases";
include (__DIR__ . '/../CommonBlocs/tabs.php');

function getAuteurs($auteur_string){
    $theAuthors = explode(",", $auteur_string);
    $str = "";
    foreach ($theAuthors as $theAuthor){
        $theauthorParam = explode(':', $theAuthor);
        $theAutheurPrenom = $theauthorParam[0];
        $theAutheurNom = $theauthorParam[1];
        $theAutheurId = $theauthorParam[2];
        $str .= ($str != '' ?', ':'');
        $str .= '<span class="author"><a class="yellow" href="publications-of-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'.$theAutheurPrenom.' '.$theAutheurNom.'</a></span>';
        $i++;
    }
    return $str;
}
?>
<div class="biblio" id="body-content">
    <div class="list_articles">
        <div class="wrapper mt1 mb1" style="text-align: center; position: relative;">
            <h1 class="main-title" style="margin: 0; padding: 0; display: inline-block;">My purchases</h1>
            <a href="mes_factures.php" class="search_button" style="position: absolute; right:0;">
                My bills
                <span class="unicon unicon-round-arrow-black-right right ml6">➜</span>
            </a>
        </div>
        <?php
        if(!empty($artRev)){ ?>
        <h2 class="section"><span>Journal articles</span></h2>
        <?php foreach($artRev as $art){?>
        <div class="article greybox_hover">
            <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $art['ID_REVUE'] ?>/<?= $art['ID_NUMPUBLIE']?>_L62.jpg">
            <div class="meta">
                <div class="title">
                    <a href="article-<?= $art['details']['ARTICLE_ID_ARTICLE'] . '--' . $art['details']["ARTICLE_URL_REWRITING_EN"]?>.htm"><strong><?= $art['details']['ARTICLE_TITRE']?></strong></a><br>
                    <?= $art['details']['ARTICLE_SOUSTITRE']?>
                </div>
                <div class="authors"><?= getAuteurs($art['details']['ARTICLE_AUTEUR']) ?></div>
                <div class="revue_title">
                    in <a class="title_little_blue" href="revue-<?= $art['details']['REVUE_URL_REWRITING_EN']?>.htm"><span class="title_little_blue"><?= $art['details']['REVUE_TITRE']?></span></a> <strong><?= $art['details']['NUMERO_ANNEE']?>/<?= $art['details']['NUMERO_NUMERO']?>
                        <?= $art['details']['NUMERO_VOLUME']!=''?('('.$art['details']['NUMERO_VOLUME'].')'):''?></strong>
                </div>
                <div class="date"><i>Purchased the <?= date_format(new DateTime($art['DATE']), 'd/m/Y')?>.</i></div>
                <div class="state">
                    <?php foreach ($art['details']['LISTE_CONFIG_ARTICLE'] as $configArticle): ?>
                        <a
                            <?php if ($configArticle['LIB'] === 'English'): ?>
                                href="article.php?ID_ARTICLE=<?= $art['details']['ARTICLE_ID_ARTICLE'] ?>"
                            <?php else: ?>
                                href="<?= $configArticle['HREF'] ?>"
                            <?php endif; ?>
                            class="<?= ((!isset($configArticle['CLASS']) || $configArticle['CLASS']=='')?'button':$configArticle['CLASS']) ?>"
                            <?php if (strpos($configArticle['HREF'], 'load_pdf') !== false || (strpos($configArticle['HREF'], 'revues.org') !== false)): ?>
                                <?php echo strpos($configArticle['HREF'], 'load_pdf') !== false ? 'data-webtrends="goToPdfArticle"' : 'data-webtrends="goToRevues.org"' ?>
                                data-id_article="<?= $art['details']['ARTICLE_ID_ARTICLE'] ?>"
                                data-titre=<?=
                                    Service::get('ParseDatas')->cleanAttributeString($art['details']['ARTICLE_TITRE'])
                                ?>
                                data-authors=<?=
                                    Service::get('ParseDatas')->cleanAttributeString(
                                        Service::get('ParseDatas')->stringifyRawAuthors(
                                            $art['details']['ARTICLE_AUTEUR'],
                                            0,
                                            ';',
                                            null,
                                            null,
                                            true,
                                            ',',
                                            ':'
                                        )
                                    )
                                ?>
                            <?php endif; ?>
                        ><?= $configArticle['LIB'] ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php }
        }?>
    </div>
</div>



