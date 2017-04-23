<?php
$this->titre = "Mes achats";
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
        $str .= '<span class="author"><a class="yellow" href="publications-de-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'.$theAutheurPrenom.' '.$theAutheurNom.'</a></span>';
    }
    return $str;
}
?>


<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="mes_achats">Mes achats</a>
</div>

<div class="biblio" id="body-content">
    <div class="list_articles">
        <div class="wrapper mt1 mb1" style="text-align: center; position: relative;">
            <h1 class="main-title" style="margin: 0; padding: 0; display: inline-block;">Mes achats</h1>
            <a href="mes_factures.php" class="search_button" style="position: absolute; right:0;">
                Mes factures
                <span class="unicon unicon-round-arrow-black-right right ml6">➜</span>
            </a>
        </div>



        <?php if (!empty($abos)): ?>
            <h2 class="section"><span>Abonnements</span></h2>
            <?php foreach ($abos as $abo): ?>
                <div class="article greybox_hover">
                    <a style="display: inline-block;" href="<?= $abo['details']['TYPEPUB']=='1'?'revue':($abo['details']['TYPEPUB']=='2'?'magazine':'collection')?>-<?= $abo['details']['URL_REWRITING']?>.htm">
                        <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $abo['ID_REVUE']?>/<?= $abo['details']['ID_NUMPUBLIE']?>_L61.jpg">
                    </a>
                    <div class="meta">
                        <div class="title_little_blue">
                            <a href="<?= $abo['details']['TYPEPUB']=='1'?'revue':($abo['details']['TYPEPUB']=='2'?'magazine':'collection')?>-<?= $abo['details']['URL_REWRITING']?>.htm"><strong><?= $abo['details']['TITRE']?></strong></a>
                        </div>
                        <div class="title"><?= $abo['details']['LIBELLE']?></div>
                        <div class="date"><i>Acheté le </i><?= date_format(new DateTime($abo['DATE_ACHAT']), 'd/m/Y')?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>



        <?php if (!empty($numRev)): ?>
            <h2 class="section"><span>Numéros de revues</span></h2>
            <?php foreach ($numRev as $num): ?>
                <div class="article greybox_hover">
                    <a style="display: inline-block;" href="revue-<?= $num['details']['REVUE_URL_REWRITING']?>-<?= $num['details']['NUMERO_ANNEE']?>-<?= $num['details']['NUMERO_NUMERO']?>.htm">
                        <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $num['ID_REVUE'] ?>/<?= $num['ID_NUMPUBLIE']?>_L61.jpg">
                    </a>
                    <div class="meta">
                        <div class="title">
                            <a href="revue-<?= $num['details']['REVUE_URL_REWRITING']?>-<?= $num['details']['NUMERO_ANNEE']?>-<?= $num['details']['NUMERO_NUMERO']?>.htm"><strong><?= $num['details']['NUMERO_TITRE'] ?></strong></a><br>
                            <i><?= $num['details']['NUMERO_SOUS_TITRE']?></i>
                        </div>
                        <div class="authors"></div>
                        <div class="revue_title">
                            <a class="title_little_blue" href="./revue.php?ID_REVUE=<?= $num['ID_REVUE']?>"><span class="title_little_blue"><?= $num['details']['REVUE_TITRE']?></span></a>
                            <strong><?= $num['details']['NUMERO_ANNEE']?>/<?= $num['details']['NUMERO_NUMERO']?></strong>
                        </div>
                        <div class="date"><i>Acheté le <?= date_format(new DateTime($num['DATE']), 'd/m/Y')?>.</i></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>



        <?php if (!empty($numOuv)): ?>
            <h2 class="section"><span>Ouvrages</span></h2>
            <?php foreach ($numOuv as $num): ?>
                <div class="article greybox_hover">
                    <a style="display: inline-block;" href="<?= $num['details']['NUMERO_URL_REWRITING']?>--<?= $num['details']['ISBN']?>.htm">
                        <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $num['ID_REVUE'] ?>/<?= $num['ID_NUMPUBLIE']?>_L61.jpg">
                    </a>
                    <div class="meta">
                        <div class="title">
                            <a href="<?= $num['details']['NUMERO_URL_REWRITING']?>--<?= $num['details']['ISBN']?>.htm">
                                <span class="title_little_blue"><?= $num['details']['NUMERO_TITRE'] ?></span>
                            </a>
                            <br>
                            <i><?= $num['details']['NUMERO_SOUS_TITRE']?></i>
                        </div>
                        <div class="authors"><?= getAuteurs($num['details']['NUMERO_AUTEUR']) ?></div>
                        <div class="revue_title">
                            <strong>
                                Coll.&nbsp;<a href="./collection.php?ID_REVUE=<?= $num['ID_REVUE']?>">&laquo;<?= $num['details']['REVUE_TITRE']?>&raquo;</a>,&nbsp;
                                <?= $num['details']['EDITEUR_NOM_EDITEUR']?>,&nbsp;
                                <?= $num['details']['NUMERO_ANNEE']?>
                            </strong>
                        </div>
                        <div class="date"><i>Acheté le <?= date_format(new DateTime($num['DATE']), 'd/m/Y')?>.</i></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>



        <?php if (!empty($artRev)): ?>
            <h2 class="section"><span>Articles de revues</span></h2>
            <?php foreach($artRev as $art): ?>
                <div class="article greybox_hover">
                    <a style="display: inline-block;" href="revue-<?= $art['details']['REVUE_URL_REWRITING']?>-<?= $art['details']['NUMERO_ANNEE']?>-<?= $art['details']['NUMERO_NUMERO']?>-page-<?= $art['details']['ARTICLE_PAGE_DEBUT']?>.htm">
                        <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $art['ID_REVUE'] ?>/<?= $art['ID_NUMPUBLIE']?>_L61.jpg">
                    </a>
                    <div class="meta">
                        <div class="title">
                            <a href="revue-<?= $art['details']['REVUE_URL_REWRITING']?>-<?= $art['details']['NUMERO_ANNEE']?>-<?= $art['details']['NUMERO_NUMERO']?>-page-<?= $art['details']['ARTICLE_PAGE_DEBUT']?>.htm"><strong><?= $art['details']['ARTICLE_TITRE']?></strong></a><br>
                            <?= $art['details']['ARTICLE_SOUSTITRE']?>
                        </div>
                        <div class="authors"><?= getAuteurs($art['details']['ARTICLE_AUTEUR']) ?></div>
                        <div class="revue_title">
                            Dans <a class="title_little_blue" href="revue-<?= $art['details']['REVUE_URL_REWRITING']?>.htm"><span class="title_little_blue"><?= $art['details']['REVUE_TITRE']?></span></a> <strong><?= $art['details']['NUMERO_ANNEE']?>/<?= $art['details']['NUMERO_NUMERO']?>
                                <?= $art['details']['NUMERO_VOLUME']!=''?('('.$art['details']['NUMERO_VOLUME'].')'):''?></strong>
                        </div>
                        <div class="date"><i>Acheté le <?= date_format(new DateTime($art['DATE']), 'd/m/Y')?>.</i></div>
                        <div class="state">
                            <?php foreach ($art['details']['LISTE_CONFIG_ARTICLE'] as $configArticle): ?>
                                <a
                                    href="<?= $configArticle['HREF'] ?>"
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
            <?php endforeach; ?>
        <?php endif; ?>



        <?php if (!empty($artMag)): ?>
            <h2 class="section"><span>Articles de magazines</span></h2>
            <?php foreach ($artMag as $art): ?>
                <div class="article greybox_hover">
                    <a style="display: inline-block;" href="magazine-<?= $art['details']['REVUE_URL_REWRITING']?>-<?= $art['details']['NUMERO_ANNEE']?>-<?= $art['details']['NUMERO_NUMERO']?>-page-<?= $art['details']['ARTICLE_PAGE_DEBUT']?>.htm">
                        <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $art['ID_REVUE'] ?>/<?= $art['ID_NUMPUBLIE']?>_L61.jpg">
                    </a>
                    <div class="meta">
                        <div class="title"><a href="magazine-<?= $art['details']['REVUE_URL_REWRITING']?>-<?= $art['details']['NUMERO_ANNEE']?>-<?= $art['details']['NUMERO_NUMERO']?>-page-<?= $art['details']['ARTICLE_PAGE_DEBUT']?>.htm"><strong><?= $art['details']['ARTICLE_TITRE']?>
                            <?= $art['details']['NUMERO_NUMERO']?>
                            <?= $art['details']['NUMERO_ANNEE']?></strong></a>
                        </div>
                        <div class="authors"><?= getAuteurs($art['details']['ARTICLE_AUTEUR']) ?></div>
                        <div class="date">Acheté le <?= date_format(new DateTime($art['DATE']), 'd/m/Y')?>.</div>
                        <div class="state">
                            <?php foreach ($art['details']['LISTE_CONFIG_ARTICLE'] as $configArticle): ?>
                                <a
                                    href="<?= $configArticle['HREF'] ?>"
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
            <?php endforeach; ?>
        <?php endif; ?>



        <?php if (!empty($artOuv)): ?>
            <h2 class="section"><span>Contributions d’ouvrages</span></h2>
            <?php foreach ($artOuv as $art): ?>
                <div class="article greybox_hover">
                    <a style="display: inline-block;" href="<?= $art['details']['NUMERO_URL_REWRITING'] ?>--<?= $art['details']['NUMERO_ISBN'] ?>-page-<?= $art['details']['ARTICLE_PAGE_DEBUT'] ?>.htm">
                        <img class="small_cover" alt="couverture" src="/<?= $vign_path ?>/<?= $art['ID_REVUE'] ?>/<?= $art['ID_NUMPUBLIE']?>_L61.jpg">
                    </a>
                    <div class="meta">
                        <div class="title">
                            <a href="<?= $art['details']['NUMERO_URL_REWRITING'] ?>--<?= $art['details']['NUMERO_ISBN'] ?>-page-<?= $art['details']['ARTICLE_PAGE_DEBUT'] ?>.htm"><strong><?= $art['details']['ARTICLE_TITRE']?></strong></a><br>
                            <?= $art['details']['ARTICLE_SOUSTITRE']?>
                        </div>
                        <div class="authors"><?= getAuteurs($art['details']['ARTICLE_AUTEUR']) ?></div>
                        <div class="revue_title">
                            Dans <span class="title_little_blue"><?= $art['details']['NUMERO_TITRE'] ?></span> <strong>(<?= $art['details']['EDITEUR_NOM_EDITEUR'] ?>, <?= $art['details']['NUMERO_ANNEE'] ?>)</strong>
                        </div>
                        <div class="date"><i>Acheté le <?= date_format(new DateTime($art['DATE']), 'd/m/Y')?>.</i></div>

                        <div class="state">
                            <?php foreach ($art['details']['LISTE_CONFIG_ARTICLE'] as $configArticle): ?>
                                <a
                                    href="<?= $configArticle['HREF'] ?>"
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>



