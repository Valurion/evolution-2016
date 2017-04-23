<?php
    $this->titre = "Revue ".$revue['REVUE_TITRE']." ".$revue["NUMERO_ANNEE"]."/".$revue["NUMERO_NUMERO"];
    $typePub = 'revue';
    include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<?php
	// Définition de l'URL sur PREPROD / BON A TIRER
	$url_token = "";
	// Concervation du TOKEN dans l'URL
	if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
	{
	    $url_token = "?token=" . $_GET['token'];
	}
?>

<?php if ($numero['NUMERO_STATUT'] == 0 || $revue['STATUT'] == 0): ?>
    <div class="danger backoffice article-desactivate">
        <?php if ($revue['STATUT'] == 0): ?>
            Cette revue est actuellement désactivé.<br />
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
    <!-- <a href="[DISCIPLINE_HREF]">Discipline ([DISCIPLINE_DISCIPLINE])</a>
    <span class="icon-breadcrump-arrow icon"></span> -->
    <a class="inactive" href="./revue-<?php echo $revue["REVUE_URL_REWRITING"] ?>.htm">Revue</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./revue-<?php echo $revue["REVUE_URL_REWRITING"] ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">Num&#233;ro</a>
</div>

<div id="body-content">
    <div id="page_numero">
        <div class="grid-g grid-3-head" id="page_header">
            <div class="grid-u-1-4">
                <a href="revue-<?php echo $revue["REVUE_URL_REWRITING"] ?>.htm"><img src="/<?= $vign_path ?>/<?= $revue['REVUE_ID_REVUE'] ?>/<?= $revue['NUMERO_ID_NUMPUBLIE'] ?>_L204.jpg" alt="couverture de <?= $revue['NUMERO_ID_NUMPUBLIE'] ?>" class="big_coverbis"></a>

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
                <h3 class="text_medium reference">
                    <?= $numero['NUMERO_ANNEE'] ?><!--
                    <?php if ($numero['NUMERO_NUMERO']): ?>
                        -->/<?= $numero['NUMERO_NUMERO'] ?><!--
                        <?php if ($numero['NUMERO_NUMEROA']): ?>
                            -->-<?= $numero['NUMERO_NUMEROA'] ?><!--
                        <?php endif; ?>
                    <?php endif; ?>-->
                    (<?= $numero['NUMERO_VOLUME'] ?>)
                </h3>
                <h3 class="text_medium title"><b><?= $revue['NUMERO_TITRE'] ?></b></h3>

                <h4 class="text_medium title subtitle"><?= $revue['NUMERO_SOUS_TITRE'] ?></h4>


                <!-- Affichage auteurs du numéros -->
                <div class="authors">
                    <?php
                        // La liste des auteurs est générée différemment si la recherche se fait
                        // via ID (string) ou via une URL REWRITED (array)
                        // Détection du type de valeur
                        $typeAuthorsContainer = gettype($numero['NUMERO_AUTEUR']);

                        // Gestion du string
                        if($typeAuthorsContainer == "string") {
                            $authors = explode(',', $numero['NUMERO_AUTEUR']);
                        }
                        // Gestion du tableau
                        if($typeAuthorsContainer == "array") {
                            $authors = $numero['NUMERO_AUTEUR'];
                        }

                        $lengthAuthors = count($authors) - 1;

                        // Parcours du tableau
                        foreach ($authors as $index => $author):

                            // Découpage de la chaine de caractère
                            if($typeAuthorsContainer == "string") {
                                $splitAuthor = explode(':', $author);
                                $authorNom = trim($splitAuthor[1]);
                                $authorPrenom= trim($splitAuthor[0]);
                                $authorId = trim($splitAuthor[2]);
                                $authorAttribut = trim($splitAuthor[3]);
                            }
                            // Découpage du tableau
                            if($typeAuthorsContainer == "array") {
                                $authorNom = trim($author['AUTEUR_NOM']);
                                $authorPrenom = trim($author['AUTEUR_PRENOM']);
                                $authorId = trim($author['AUTEUR_ID_AUTEUR']);
                                $authorAttribut = trim($author['AUTEUR_ATTRIBUT']);
                            }
                        ?>
                        <?php if ($authorAttribut): ?>
                            <span class="yellow2">
                                <?= $authorAttribut ?>
                            </span>
                        <?php endif; ?>
                        <a class="author" href="publications-de-<?= $authorNom ?>-<?= $authorPrenom ?>--<?= $authorId ?>.htm">
                            <?= $authorPrenom ?>
                            <?=$authorNom ?><?php if ($index < $lengthAuthors): ?><span class="comma">,</span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <!-- /Affichage auteurs du numéros -->


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

                    <?php if (($revue['NUMERO_NB_PAGE'] != '') && ($numero['NUMERO_PREPUB'] != '1')) { ?>
                        <li>
                            <span class="yellow nb_pages">Pages : </span><?= $revue['NUMERO_NB_PAGE'] ?>
                        </li>
                    <?php } if ($revue['EDITEUR_NOM_EDITEUR'] != '') { ?>
                        <li>
                            <span class="yellow ">&#201;diteur : </span><a href="./editeur.php?ID_EDITEUR=<?php echo $revue['REVUE_ID_EDITEUR']; ?>"><?= $revue['EDITEUR_NOM_EDITEUR'] ?></a>
                        </li>
                    <?php } if ($revue['REVUE_AFFILIATION'] != '') { ?>
                        <li class="wrapper_affiliation"><?= $revue['REVUE_AFFILIATION'] ?></li>
                    <?php } if ($revue['NUMERO_EAN'] != '') { ?>
                        <li>
                            <span class="yellow issn">ISBN : </span><?= $revue['NUMERO_EAN'] ?>
                        </li>
                    <?php } if ($numero['NUMERO_ISBN_NUMERIQUE'] != '') { ?>
                        <li>
                            <span class="yellow issn">ISBN version en ligne : </span><?= $numero['NUMERO_ISBN_NUMERIQUE'] ?>
                        </li>
                    <?php } if ($revue['ISSN'] != '') { ?>
                        <li>
                            <span class="yellow issn">ISSN : </span><?= $revue['ISSN'] ?>
                        </li>
                    <?php } if ($revue['REVUE_WEB'] != '') { ?>
                        <li>
                            <a target="_blank" href="<?= $revue['REVUE_WEB'] ?>" id="site-internet">Site internet</a>
                        </li>
                    <?php } ?>
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
                        <?php if ($revue['NUMERO_MEMO'] != ""): ?><li><a href="revue-<?php echo $revue["REVUE_URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm<?php echo $url_token; ?>#memo">Présentation <span class="icon-arrow-black-right icon right"></span></a></li><?php endif; ?>
                        <?php if ($numero['NUMERO_PREPUB'] != '1'): ?>
                            <li><a href="revue-<?php echo $revue["REVUE_URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm<?php echo $url_token; ?>#summary">Sommaire <span class="icon-arrow-black-right icon right"></span></a></li>
                            <li><a href="revue-<?php echo $revue["REVUE_URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm<?php echo $url_token; ?>#about">Fiche technique <span class="icon-arrow-black-right icon right"></span></a></li>
                        <?php endif; ?>
                        <?php if($revue["ACCES_EN_LIGNE"] == 1) { ?>
                        <li><a href="code-abonnement-papier.php?ID_REVUE=<?php echo $revue['REVUE_ID_REVUE']; ?>">Accès abonnés <span class="icon-arrow-black-right icon right"></span></a></li>
                        <?php } ?>
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

                <?php if ($numero["ID_NUMERO_INT"] != null) { ?>
                    <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/numero.php?ID_REVUE=<?php echo $numero["ID_REVUE_INT"]; ?>&amp;ID_NUMPUBLIE=<?php echo $numero["ID_NUMERO_INT"]; ?>" class="cairn-int_link"><span class="label">Browse this issue in English</span></a>
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
            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
        ?>
        <?php if ($numero['NUMERO_PREPUB'] != '1'): ?>
            <h1 class="main-title" id="summary">
                Sommaire
                <?php
                if($oneTokenBAD === false) {
                    checkBiblio($numero['NUMERO_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], null, $authInfos);
                }
                ?>
            </h1>
        <?php endif; ?>

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
                                        $_authors[] = $author;
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
                        <?php if($numero['NUMERO_PREPUB'] != 1 && trim($article['ARTICLE_PAGE_DEBUT']) != '' && trim($article['ARTICLE_PAGE_FIN']) != ''){ ?>
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
                            <?php if(isset($configArticle['BEFORE'])) {echo "<span style=\"margin-right: 5px;\">".$configArticle['BEFORE']."</span>" ;}?>
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
                                <?php if(isset($configArticle['TARGET'])) {echo "target=\"".$configArticle['TARGET']."\"";}?>
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
                                <?php if(isset($configArticle['TARGET'])) {echo "target=\"".$configArticle['TARGET']."\"";}?>
                            ><?= $configArticle['LIB'] ?></a>
                            <?php endif ?>
                        <?php endforeach; ?>

                        <?php
                            // Version EN
                            // L'ID de l'article est présent dans le tableau, on affiche un lien directement vers la version de cairn-int
                            if(array_key_exists($article['ARTICLE_ID_ARTICLE'], $numero["ARTICLES_INT"])) {

                                // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                                $array              = $numero["ARTICLES_INT"][$article['ARTICLE_ID_ARTICLE']][0];
                                $id_article_int     = $array["ID_ARTICLE"];
                                $url_article_int    = $array["URL_REWRITING_EN"];

                                echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                            }
                        ?>

                        <?php
                            // BADN
                            // Affichage du prix
                            if($oneTokenBAD != false) {
                                // Récupération du prix
                                $prix = $article["ARTICLE_PRIX"];

                                // Formatage du prix
                                if($prix == 0) {$prix = "Gratuit";}
                                else {$prix = $prix."€";}

                                // Affichage
                                echo "<span style=\"margin-right: 20px;padding: 4px 7px;float: right;text-align: center;font-size: 16px;line-height: 16px;font-weight: bold;background: #4bb2ac;color: #FFF;-webkit-border-radius: 8px;border-radius: 8px;\">".$prix."</span>";
                            }
                            // Outils
                            else {
                                checkBiblio($revue['REVUE_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], $article['ARTICLE_ID_ARTICLE'],$authInfos);
                            }
                        ?>
                        </div>
                </div>
                    <!-- COinS -->
                        <?php
                            $coins = http_build_query(array(
                                'ctx_ver' => 'Z39.88-2004',
                                'ctx_id' => $article['ARTICLE_ID_ARTICLE'],
                                'ctx_tim' => date('c', time()),
                                'rft_val_fmt' => 'info:ofi/fmt:kev:mtx:journal',
                                'rft.genre' => 'article',
                                'rft_id' => "http://cairn.info/".$article['LISTE_CONFIG_ARTICLE'][0]['HREF'],
                                'rfr_id' => "info:sid/cairn.info:".$article['ARTICLE_ID_ARTICLE'],
                                'rft.atitle' => $this->sanatizeXmlToText($article['ARTICLE_TITRE']),
                                'rft.btitle' => $this->sanatizeXmlToText($revue['REVUE_TITRE']),
                                'rft.jtitle' => $this->sanatizeXmlToText($numero['NUMERO_TITRE']),
                                'rft.title' => $this->sanatizeXmlToText($numero['NUMERO_TITRE']),
                                'rft.language' => $article['ARTICLE_LANGUE'],
                                'rft.date' => $numero['NUMERO_ANNEE'],
                                'rft.volume' => $numero['NUMERO_VOLUME'],
                                'rft.issue' => $numero['NUMERO_NUMERO'],
                                'rft.spage' => $article['ARTICLE_PAGE_DEBUT'],
                                'rft.epage' => $article['ARTICLE_PAGE_FIN'],
                                'rft.pages' => $article['ARTICLE_PAGE_DEBUT'].'-'.$article['ARTICLE_PAGE_FIN'],
                                'rft.issn' => $revue['ISSN'],
                                'rft.eissn' => $revue['ISSN_NUM'],
                                'rft.isbn' => $numero['NUMERO_ISBN'],
                                'rft.publisher' => $this->sanatizeXmlToText($revue['EDITEUR_NOM_EDITEUR'])
                            ));
                            foreach ($_authors as $_author) {
                                $coins .= '&';
                                $coins .= http_build_query(array(
                                    // 'rft.au' => $_author['PRENOM'].', '.$_author['NOM'],
                                    'rft.aulast' => $this->sanatizeXmlToText($_author['NOM']),
                                    'rft.aufirst' => $this->sanatizeXmlToText($_author['PRENOM']),
                                ));
                            }
                            $coins = str_replace('&', '&amp;', $coins);
                        ?>
                        <!-- <span class="Z3988" title="<?= $coins ?>"></span> -->
                        <!--<?= '<div>'.urldecode($coins).'</div>' ?>-->
                    <!-- /COinS -->
            <?php endforeach; ?>

        </div>

        <?php if ($numero['NUMERO_PREPUB'] != '1'): ?>
            <div class="about-numpublie">
                <h2 id="about">Fiche technique</h2>
                <p>
                    <em><?= $revue['REVUE_TITRE'] ?></em>
                    <?= $numero['NUMERO_ANNEE'] ?>/<?= $numero['NUMERO_NUMERO'] ?><!--
                    <?php if ($numero['NUMERO_NUMEROA']): ?>
                        -->-<?= $numero['NUMERO_NUMEROA']; ?><!--
                    <?php endif; ?>-->
                    (<?= $numero['NUMERO_VOLUME'] ?>).
                    <?= $numero['NUMERO_NB_PAGE'] ?>&#160;pages.
                    <?php if ($revue['ISSN']): ?>
                        <br />
                        ISSN&#160;: <?= $revue['ISSN'] ?>
                    <?php endif; ?>
                    <?php if ($revue['REVUE_ISSN_NUM']): ?>
                        <br />
                        ISSN en ligne&#160;: <?= $revue['REVUE_ISSN_NUM'] ?>
                    <?php endif; ?>
                    <?php if ($revue['NUMERO_EAN']): ?>
                        <br />
                        ISBN&#160;: <?= $revue['NUMERO_EAN'] ?>
                    <?php endif; ?>
                    <br />
                    Lien&#160;: &lt;<u>http://www.cairn.info/revue-<?= $revue['REVUE_URL_REWRITING'] ?>-<?= $numero['NUMERO_ANNEE'] ?>-<?= $numero['NUMERO_NUMERO'] ?>.htm</u>&gt;
                </p>
            </div>
        <?php endif; ?>

        <!-- Export Zotero/EndNote/Refworks -->
        <p>
            <a href="<?= Configuration::get('zotero') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Télécharger au format RefMan/RIS (<b>ZOTERO</b>)</a><br />
            <a href="<?= Configuration::get('endnote') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Télécharger au format ENW (<b>ENDNOTE</b>)</a><br />
            <a href="http://www.refworks.com/express/ExpressImport.asp?vendor=Cairn&amp;filter=Refworks%20Tagged%20Format&amp;encoding=65001&amp;url=<?= Configuration::get('refworks') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Exporter vers <b>REFWORKS</b></a>
        </p>


    </div>

</div>

<?php
    /* Ce qui suite ne concerne que les numéros de revues affiliés au CNRS */
    $revuesCNRS = explode(',', Configuration::get('revuesCNRS', ''));
    if (in_array($revue['REVUE_ID_REVUE'], $revuesCNRS)) {
        $this->javascripts[] = <<<'EOD'
            $(function()  {
                $('#logos_footer').addClass('logo-plural').removeClass('logo-single');;
            });
EOD;
    }
?>


<?php include (__DIR__ . "/../CommonBlocs/invisible.php"); ?>
