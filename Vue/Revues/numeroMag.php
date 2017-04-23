<?php
$this->titre = $revue['REVUE_TITRE'] . " " . $revue["NUMERO_ANNEE"] . "/" . $revue["NUMERO_NUMERO"] . ", " . $revue['NUMERO_TITRE'];
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<?php if ($numero['NUMERO_STATUT'] == 0 || $revue['STATUT'] == 0): ?>
    <div class="danger backoffice article-desactivate">
        <?php if ($revue['STATUT'] == 0): ?>
            Ce magazine est actuellement désactivé.<br />
        <?php endif; ?>
        <?php if ($numero['NUMERO_STATUT'] == 0): ?>
            Ce numéro est actuellement désactivé.<br />
        <?php endif; ?>
        Sur http://cairn.info, ce numéro <strong>n’apparaîtra pas</strong>. Il apparaît <strong>uniquement</strong> sur <?= Configuration::get('urlSite') ?>.
    </div>
<?php endif; ?>

<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./magazines.php">Magazines</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./magazine-<?php echo $revue["REVUE_URL_REWRITING"] ?>.htm">Magazine</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./magazine-<?php echo $revue["REVUE_URL_REWRITING"] ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">Numéro</a>
</div>

<div id="body-content">
    <div id="page_numero">
        <div class="grid-g grid-3-head" id="page_header">
            <div class="grid-u-1-4">
                <a href="magazine-<?php echo $revue["REVUE_URL_REWRITING"] ?>.htm"><img src="/<?= $vign_path ?>/<?= $revue['REVUE_ID_REVUE'] ?>/<?= $revue['NUMERO_ID_NUMPUBLIE'] ?>_L204.jpg" alt="couverture de [REVUE_ID_NUMPUBLIE]" class="big_coverbis"></a>

                <?php if($accessElecOk && $numero['NUMERO_EPUB'] == 1){?>
                <br/>
                <table border="0" cellpadding="0" cellspacing="0" style="text-align: left; cursor: pointer; border: none; width: 214px;" id="epub"><tr>
                    <td style="padding : 5px;" width="45"><img height="45" src="./static/images/epub.png"/></td>
                    <td style="padding : 5px;" ><span style="font: bold 14px Alegreya;"><a href="./load_epub.php?ID_NUMPUBLIE=<?= $revue['NUMERO_ID_NUMPUBLIE'] ?>">Télécharger la version EPUB du numero</a></span></td>
                </tr></table>
                <?php } ?>


            </div>

            <div class="grid-u-1-2 meta">
                <h1 class="title_big_blue revue_title"><?= $revue['REVUE_TITRE'] ?></h1>
                <h3 class="text_medium reference"><?= $revue['NUMERO_VOLUME'] ?>, <?= $revue['NUMERO_ANNEE'] ?><?php echo ($numero['NUMERO_NUMERO'] != '') ? ('/' . $numero['NUMERO_NUMERO']) : '' ?>
                </h3>
                <h3 class="text_medium title"><b><?= $revue['NUMERO_TITRE'] ?></b></h3>

                <h4 class="text_medium title subtitle"><?= $revue['NUMERO_SOUS_TITRE'] ?></h4>

                <ul class="others">


                    <?php if (Configuration::get('allow_backoffice', false)): ?>
                        <!-- Lien vers le back-office de la revue -->
                        <li>
                            <span class="yellow id-revue">Id Revue : </span>
                            <?= $revue['REVUE_ID_REVUE'] ?>
                            (<a href="<?= Configuration::get('backoffice', '#') ?>?controleur=Revues&amp;action=index&amp;ID_REVUE=<?= $revue['REVUE_ID_REVUE'] ?>" class="bo-content" target="_blank">back-office</a>)
                        </li>
                        <!-- Lien vers le back-office du numéro -->
                        <li>
                            <span class="yellow id-revue">Id Numpublie : </span>
                            <?= $numero['NUMERO_ID_NUMPUBLIE'] ?>
                            (<a href="<?= Configuration::get('backoffice', '#') ?>?controleur=Revues&amp;action=numero&amp;ID_NUMPUBLIE=<?= $numero['NUMERO_ID_NUMPUBLIE'] ?>" class="bo-content" target="_blank">back-office</a>,
                            <!-- Lien vers le menu conversion -->
                            <a href="<?= Configuration::get('menu_conversion', '#').'?ID_NUMPUBLIE='.$revue['NUMERO_ID_NUMPUBLIE'].'&ID_REVUE='.$revue['REVUE_ID_REVUE'] ?>" class="bo-content" target="_blank">menu conversion</a>)
                        </li>
                    <?php endif; ?>


                    <li>
                        <span class="yellow nb_pages">Pages : </span><?= $revue['NUMERO_NB_PAGE'] ?>
                    </li>
                    <li>
                        <span class="yellow ">&#201;diteur : </span><a href="./editeur.php?ID_EDITEUR=<?php echo $revue['REVUE_ID_EDITEUR']; ?>"><?= $revue['EDITEUR_NOM_EDITEUR'] ?></a>
                    </li>
                    <?php if ($revue["REVUE_AFFILIATION"] != '') { ?>
                        <li class="wrapper_affiliation">
                            <span class="yellow ">Affiliation : </span><?= $revue['REVUE_AFFILIATION'] ?>
                        </li>
                    <?php } ?>
                    <li>
                        <a target="_blank" href="<?= $revue['REVUE_WEB'] ?>" id="site-internet">Site internet</a>
                    </li>
                </ul>

                <?php
                if(isset($typesAchat['DISPLAY_BLOC_ACHAT'])){
                    include __DIR__."/../CommonBlocs/addToBasket.php";
                }
                ?>
            </div>
            <div class="grid-u-1-4">
                <?php if($oneTokenBAD != false) {
                    // Validation BADN
                    include (__DIR__ . '/../CommonBlocs/blocMiseEnLigneBAD.php');
                } ?> 

                <!-- Raccourcis -->
                <div id="raccourcis" class="contrast-box">
                    <h1>Raccourcis</h1>
                    <ul id="shortcuts_links">
                        <?php if ($revue['NUMERO_MEMO'] != ""): ?><li><a href="magazine-<?php echo $revue["REVUE_URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm#memo">Présentation <span class="unicon unicon-round-arrow-black-right right">&#10140;</span></a></li><?php endif; ?>
                        <li><a href="magazine-<?php echo $revue["REVUE_URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm#summary">Sommaire <span class="unicon unicon-round-arrow-black-right right">&#10140;</span></a></li>
                        <li><a href="magazine-<?php echo $revue["REVUE_URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm#about">Fiche technique <span class="unicon unicon-round-arrow-black-right right">&#10140;</span></a></li>
                    </ul>                    
                </div>

                <?php if($oneTokenBAD === false) {
                    // Alertes e-mail
                    include (__DIR__ . '/../CommonBlocs/alertesEmail.php');
                } ?>

                <!-- Cité par -->
                <?php if (count($countReferencedBy) > 0) { ?>
                <div id="citerpar" class="contrast-box">
                    <h1>Cité par...</h1>
                    <ul>
                        <?php
                        $countRefByRevue = 0;
                        $countRefByOuvrage = 0;
                        foreach ($countReferencedBy as $refBy) {
                            if ($refBy['TYPEPUB'] == 1) {
                                $countRefByRevue += $refBy['CNT'];
                            } else {
                                $countRefByOuvrage += $refBy['CNT'];
                            }
                        }
                        ?>
                        <?php if ($countRefByRevue > 0): ?>
                            <li>
                                <a href="./cite-par.php?ID_NUMPUBLIE=<?= $numero['NUMERO_ID_NUMPUBLIE'] ?>#cite-par-revue">
                                    <?= $countRefByRevue ?> articles de revues
                                    <span class="icon-arrow-black-right icon right"></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($countRefByOuvrage > 0): ?>
                            <li>
                                <a href="./cite-par.php?ID_NUMPUBLIE=<?= $numero['NUMERO_ID_NUMPUBLIE'] ?>#cite-par-ouvrage">
                                    <?= $countRefByOuvrage ?> ouvrages
                                    <span class="icon-arrow-black-right icon right"></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php } ?>
                <!-- /Cité par -->

                <?php if ($numero["NUMERO_ID_NUMPUBLIE_S"] != '') { ?>
                    <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/numero.php?ID_REVUE=<?php echo $revue["REVUE_ID_REVUE_S"]; ?>&amp;ID_NUMPUBLIE=<?php echo $numero["NUMERO_ID_NUMPUBLIE_S"]; ?>" class="cairn-int_link"><span class="label">Browse this issue in English</span></a>
                <?php } ?>
            </div>
        </div>

        <?php
        if(isset($typesAchat['DISPLAY_BLOC_ACHAT'])){
            include __DIR__."/../CommonBlocs/blocAddToBasket.php";
        }
        ?>

        <?php if ($revue['NUMERO_MEMO'] != ""): ?>
            <hr class="grey">
            <div class="memo-numpublie">
                <h1 id="memo" class="main-title">Présentation</h1>
                <p><?= $revue['NUMERO_MEMO'] ?></p>
            </div>
        <?php endif; ?>

        <hr class="grey" />
        
        <?php
            include(__DIR__ . '/Blocs/navPage.php');
        ?>

        <h1 class="main-title" id="summary">
            Sommaire
        </h1>

        <div class="list_articles">

            <?php
            $done = -1;
            foreach ($articles as $article):
                $done++;
                if (($article['ARTICLE_STATUT'] == 0) && (!Configuration::get('allow_backoffice', false))) continue;
                ?>
                <?php if ($done == 0 || $article['ARTICLE_SECT_SOM'] != $articles[$done - 1]['ARTICLE_SECT_SOM']) : ?>
                    <h2 class="sect_som"><?= $article['ARTICLE_SECT_SOM'] ?></h2>
                <?php endif; ?>
                <?php if ($done == 0 || $article['ARTICLE_SECT_SSOM'] != $articles[$done - 1]['ARTICLE_SECT_SSOM']) : ?>
                    <h3 class="sect_ssom"><?= $article['ARTICLE_SECT_SSOM'] ?></h3>
                <?php endif; ?>

                <div class="article greybox_hover" id="<?= $article['ARTICLE_ID_ARTICLE'] ?>">
                    <div class="authors">
                        <span class="author">
                            <?php if (!!$article['ARTICLE_AUTEUR']): ?>
                                <?php
                                    $_authors = array();  // Utilisé pour le taggage coins, pour des raisons de performances
                                    $authors = explode(',', $article['ARTICLE_AUTEUR']);
                                    $lengthAuthors = count($authors);
                                ?>

                                <?php foreach ($authors as $index => $author): ?>
                                    <?php
                                        $author = explode(':', $author);
                                        $author = array(
                                            'PRENOM' => $author[0],
                                            'NOM' => $author[1],
                                            'UID' => $author[2],
                                            'ATTRIBUT' => $author[3],
                                        );
                                    ?>
                                    <?php if ($author['ATTRIBUT']) echo '<i>'.$author['ATTRIBUT'].'</i>'; ?>
                                    <a class="yellow" href="./publications-de-<?= $author['NOM'] ?>-<?= $author['PRENOM'] ?>--<?= $author['UID'] ?>.htm">
                                        <?= $author['PRENOM'] ?> <?= $author['NOM'] ?><!--
                                     --></a><!--
                                    --><?php if (($index + 1) < $lengthAuthors) echo ','; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <?php endif ?>
                    </div>
                    <span class="surtitle"><?= $article['ARTICLE_SURTITRE'] ?></span>
                    <div class="wrapper_title">
                        <?php if((trim($article['ARTICLE_PAGE_DEBUT']) != '') && (trim($article['ARTICLE_PAGE_FIN']) != '') && ($article["ARTICLE_MODEPAGE"] == 1)) { ?>
                        <span class="nb_pages">Page <?= $article['ARTICLE_PAGE_DEBUT'] ?> &#224; <?= $article['ARTICLE_PAGE_FIN'] ?></span>
                        <?php } ?>
                        <?php if (($article['ARTICLE_STATUT'] == 0) && (Configuration::get('allow_backoffice', false))): ?>
                            <h2 class="title red"><i>(désactivé)</i> <?= $article['ARTICLE_TITRE'] ?></h2>
                        <?php else: ?>
                            <h2 class="title"><?= $article['ARTICLE_TITRE'] ?></h2>
                        <?php endif; ?>
                    </div>
                    <span class="subtitle"><?= $article['ARTICLE_SOUSTITRE'] ?></span>

                    <span class="mention_title"><?= $article['ARTICLE_MENTION_SOMMAIRE'] ?></span>

                    <div class="state">
                        <?php foreach ($article['LISTE_CONFIG_ARTICLE'] as $configArticle): ?>
                            <?php if(strpos($configArticle['CLASS'], 'wrapper_buttons_add-to-cart') === false) : ?>
                            <a
                                href="<?= $configArticle['HREF'] ?>"
                                class="<?= ((!isset($configArticle['CLASS']) || $configArticle['CLASS']=='')?'button':$configArticle['CLASS']) ?>"
                                <?php if (strpos($configArticle['HREF'], 'load_pdf') !== false || (strpos($configArticle['HREF'], 'revues.org') !== false)): ?>
                                    <?php echo strpos($configArticle['HREF'], 'load_pdf') !== false ? 'data-webtrends="goToPdfArticle"' : 'data-webtrends="goToRevues.org"' ?>
                                    data-id_article="<?= $article['ARTICLE_ID_ARTICLE'] ?>"
                                    data-titre=<?=
                                        Service::get('ParseDatas')->cleanAttributeString($article['ARTICLE_TITRE'])
                                    ?>
                                    data-authors=<?=
                                        Service::get('ParseDatas')->cleanAttributeString(
                                            Service::get('ParseDatas')->stringifyRawAuthors(
                                                $article['ARTICLE_AUTEUR'],
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
                            <?php elseif (!isset($numero['NUMERO_MOV_WALL_PPV']) || (date('Y-m-d') >= $numero['NUMERO_MOV_WALL_PPV'])) : ?>
                            <a
                                href="<?= $configArticle['HREF'] ?>"
                                class="<?= ((!isset($configArticle['CLASS']) || $configArticle['CLASS']=='')?'button':$configArticle['CLASS']) ?>"
                                <?php if (strpos($configArticle['HREF'], 'load_pdf') !== false || (strpos($configArticle['HREF'], 'revues.org') !== false)  || (strpos($configArticle['HREF'], 'mon_panier') !== false))  : ?>
                                    <?php
                                        if (strpos($configArticle['HREF'], 'load_pdf') !== false) {
                                            echo 'data-webtrends="goToPdfArticle"';
                                        } elseif (strpos($configArticle['HREF'], 'revues.org') !== false) {
                                            echo 'data-webtrends="goToRevues.org"';
                                        }  elseif (strpos($configArticle['HREF'], 'mon_panier') !== false) {
                                            echo 'data-webtrends="goToMonPanier" ';
                                            echo 'data-prix_article="' . number_format($article['ARTICLE_PRIX'], 2, '.', '') . '" ';
                                        }
                                    ?>
                                    data-id_article="<?= $article['ARTICLE_ID_ARTICLE'] ?>"
                                    data-titre=<?=
                                        Service::get('ParseDatas')->cleanAttributeString($article['ARTICLE_TITRE'])
                                    ?>
                                    data-authors=<?=
                                        Service::get('ParseDatas')->cleanAttributeString(
                                            Service::get('ParseDatas')->stringifyRawAuthors(
                                                $article['ARTICLE_AUTEUR'],
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
                            <?php endif ?>
                        <?php endforeach; ?>
                        <?php
                        require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                        checkBiblio($revue['REVUE_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], $article['ARTICLE_ID_ARTICLE'],$authInfos);
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <div class="about-numpublie">
            <h2 id="about">Fiche technique</h2>
            <p>
                <?php
                echo '<em>' . $revue["REVUE_TITRE"] . '</em> ' . $numero['NUMERO_ANNEE'] . '/' . $numero['NUMERO_NUMERO'];
                echo $numero['NUMERO_NUMEROA'] != '' ? ('-' . $numero['NUMERO_NUMEROA']) : '';
                echo ' (' . $numero['NUMERO_VOLUME'] . '). ' . $numero['NUMERO_NB_PAGE'] . '&#160;pages.
                    '.($revue['ISSN'] == ''?'':('<br />ISSN : ' . $revue['ISSN'] . '.')).'
                    '.($revue["REVUE_ISSN_NUM"]==''&& $revue["NUMERO_EAN"]==''?'':'<br />').($revue["REVUE_ISSN_NUM"] == ''?'':('ISSN en ligne : ' . $revue['REVUE_ISSN_NUM'].'. ')) . ($revue["NUMERO_EAN"] == ''?'':('ISBN&#160;: ' . $numero['NUMERO_EAN'].'.')).'
                    '.($numero['NUMERO_DOI']!=null?'<br/>DOI : '.$numero['NUMERO_DOI']:'').'
                    <br />Lien : &lt;<u>http://www.cairn.info/magazine-' . $revue["REVUE_URL_REWRITING"] . '-' . $numero["NUMERO_ANNEE"] . '-' . $numero["NUMERO_NUMERO"] . '.htm</u>&gt;.';
                ?>
            </p>
        </div>

        <!-- Export Zotero/EndNote/Refworks -->
        <p>
            <a href="<?= Configuration::get('zotero') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Télécharger au format RefMan/RIS (<b>ZOTERO</b>)</a><br />
            <a href="<?= Configuration::get('endnote') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Télécharger au format ENW (<b>ENDNOTE</b>)</a><br />
            <a href="http://www.refworks.com/express/ExpressImport.asp?vendor=Cairn&amp;filter=Refworks%20Tagged%20Format&amp;encoding=65001&amp;url=<?= Configuration::get('refworks') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Exporter vers <b>REFWORKS</b></a>
        </p>
    </div>
</div>

<?php include (__DIR__ . "/../CommonBlocs/invisible.php"); ?>
