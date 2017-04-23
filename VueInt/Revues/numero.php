<?php $this->titre = "Journal " . $revue['REVUE_TITRE'] . " " . $revue["NUMERO_ANNEE"] . "/" . $revue["NUMERO_NUMERO"];
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
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
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./disc-<?= $curDiscipline?>.htm"><?= $filterDiscipline?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./journal-<?php echo $revue["REVUE_URL_REWRITING"] ?>.htm">Journal</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./journal-<?php echo $revue["REVUE_URL_REWRITING"] ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">Issue</a>
</div>

<div id="body-content">
    <div id="page_numero">
        <div class="grid-g grid-3-head" id="page_header">
            <div class="grid-u-1-4">
                <a href="./journal-<?php echo $revue["REVUE_URL_REWRITING"] ?>.htm"><img src="/<?= $vign_path ?>/<?= $revue['REVUE_ID_REVUE'] ?>/<?= $revue['NUMERO_ID_NUMPUBLIE'] ?>_H310.jpg" alt="couverture de <?= $revue['NUMERO_ID_NUMPUBLIE'] ?>" class="big_coverbis"></a>

                <?php if($accessElecOk && $numero['NUMERO_EPUB'] == 1){?>
                <br/>
                <table border="0" cellpadding="0" cellspacing="0" style="text-align: left; cursor: pointer; border: none; width: 214px;" id="epub"><tr>
                    <td style="padding : 5px;" width="45"><img height="45" src="./static/images/epub.png"/></td>
                    <td style="padding : 5px;" ><span style="font: bold 14px Alegreya;"><a href="./load_epub.php?ID_NUMPUBLIE=<?= $revue['NUMERO_ID_NUMPUBLIE'] ?>">Download EPUB version of this issue</a></span></td>
                </tr></table>
                <?php } ?>
            </div>

            <div class="grid-u-1-2 meta">
                <div class="descriptionPage minHeight">
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

                        <?php if ($revue['NUMERO_NB_PAGE'] != '') { ?>
                            <li>
                                <span class="yellow nb_pages">Pages : </span><?= $revue['NUMERO_NB_PAGE'] ?>
                            </li>
                        <?php } if ($revue['NUMERO_EAN'] != '') { ?>
                            <li>
                                <span class="yellow issn">ISBN : </span><?= $revue['NUMERO_EAN'] ?>
                            </li>
                        <?php } if ($revue['EDITEUR_NOM_EDITEUR'] != '') { ?>
                            <li>
                                <span class="yellow ">Publisher : </span><a href="./publisher.php?ID_EDITEUR=<?php echo $revue['REVUE_ID_EDITEUR']; ?>"><?= $revue['EDITEUR_NOM_EDITEUR'] ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="w100">
                    <?php if($efta > 0){ ?>
                        <div class="frenchVersion">
                            <a href="./list_articles_fulltext.php?ID_REVUE=<?= $revue['REVUE_ID_REVUE'] ?>">English Full-text Articles</a>
                        </div>
                    <?php } ?>
                    <div class="frenchVersion inline-block">
                        <a href="./journal-<?= $revue['URL_REWRITING_EN']?>.htm">List of issues</a>
                    </div>
                </div>
            </div>
            <div class="grid-u-1-4">
                <?php include (__DIR__ . '/../CommonBlocs/alertesEmail.php');?>
            </div>
        </div>

        <?php if ($revue['NUMERO_MEMO'] != ""): ?>
            <hr class="grey">
            <div class="memo-numpublie">
                <h1 id="memo" class="main-title">Presentation</h1>
                <p><?= $revue['NUMERO_MEMO'] ?></p>
            </div>
        <?php endif; ?>
        <hr class="grey"/>
        <?php
             include(__DIR__ . '/Blocs/navPage.php');
        ?>
        <h1 class="main-title" id="summary">
            Table of Contents
            <?php
                require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                checkBiblio($numero['NUMERO_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], null, $authInfos);
            ?>
        </h1>

        <div class="list_articles">

            <?php
            $done = -1;
            foreach ($articles as $article):
                $done++;
                if (($article['ARTICLE_STATUT'] == 0) && (!Configuration::get('allow_backoffice', false))) continue;
                ?>
                <?php if ($article['ARTICLE_SECT_SOM'] != '' && $article['ARTICLE_SECT_SOM'] != $articles[$done - 1]['ARTICLE_SECT_SOM']) : ?>
                    <h2 class="sect_som"><?= $article['ARTICLE_SECT_SOM'] ?></h2>
                <?php endif; ?>
                <?php if ($article['ARTICLE_SECT_SSOM'] != '' && $article['ARTICLE_SECT_SSOM'] != $articles[$done - 1]['ARTICLE_SECT_SSOM']) : ?>
                    <h3 class="sect_ssom"><?= $article['ARTICLE_SECT_SSOM'] ?></h3>
                <?php endif; ?>

                <div class="article"  style="padding:0">
                    <div class="pages_article">
                        <p style="position:relative;top:24px;" class="btn gray">
                            <b>Page <?= $article['ARTICLE_PAGE_DEBUT'] ?> to <?= $article['ARTICLE_PAGE_FIN'] ?></b>
                        </p>
                    </div>

                    <div class="metadata_article">
                        <p class="yellow-author2">
                            <?php
                            if($article['ARTICLE_AUTEUR'] != ''){
                                $theAuthors = explode(",", $article['ARTICLE_AUTEUR']);
                                $numItems = count($theAuthors);
                                $i = 0;
                                foreach ($theAuthors as $theAuthor):
                                    $theauthorParam = explode(':', $theAuthor);
                                    $theAutheurPrenom = $theauthorParam[0];
                                    $theAutheurNom = $theauthorParam[1];
                                    $theAutheurId = $theauthorParam[2];
                                    $theAutheurAttribut = $theauthorParam[3];
                                    if (++$i != 1) echo'<span>, </span>';
                                    if ($theAutheurAttribut != '') echo '<i class="yellow">'.$theAutheurAttribut.'</i>';
                                    echo '<a class="yellow" href="./publications-of-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'. $theAutheurPrenom . ' ' . $theAutheurNom .'</a></span>';
                                endforeach;
                            }else{
                                echo '&nbsp;';
                            }
                            ?>
                        </p>
                        <p class="mt1">
                            <?php if ($article['ARTICLE_SURTITRE']): ?>
                                <div><span class="surtitle"><?= $article['ARTICLE_SURTITRE'] ?></span></div>
                            <?php endif; ?>
                            <?php if (($article['ARTICLE_STATUT'] == 0) && (Configuration::get('allow_backoffice', false))): ?>
                                <h2 class="title red"><i>(désactivé)</i> <?= $article['ARTICLE_TITRE'] ?></h2>
                            <?php else: ?>
                                <?= $article['ARTICLE_TITRE'] ?>
                            <?php endif; ?>
                            <span class="subtitle"><?= $article['ARTICLE_SOUSTITRE'] ?></span>
                        </p>
                    </div>

                    <div class="state_article">
                        <?php
                        $abstract = $article['LISTE_CONFIG_ARTICLE'][0];
                        if($abstract == ''){
                            echo '<span class="button-grey2 w49 left">Abstract</span>';
                        }else{
                            echo '<a href="./abstract-'.$article['ARTICLE_ID_ARTICLE'].'--'.$article['ARTICLE_URL_REWRITING_EN'].'.htm" class="button-blue2 w49 left">Abstract</a>';
                        }
                        
                        // Liens vers l'article en FRANCAIS
                        $frArticleArray = $numero["IDS_ARTICLE_CAIRN"];
                        // L'article est disponible en FR (il est présent dans le tableau regroupant TOUS les articles de ce numero)
                        if(in_array($article['ARTICLE_ID_ARTICLE_S'], $frArticleArray)) {
                            echo '<a href="'.Service::get('ParseDatas')->getCrossDomainUrl().'/article.php?ID_ARTICLE='.$article['ARTICLE_ID_ARTICLE_S'].'" class="button-blue2 w49 right">French</a>';
                        }
                        else {
                            echo '<span class="button-grey2 w49 right">French</span>';
                        } 
                        //$french = $article['LISTE_CONFIG_ARTICLE'][1];
                        //if($french == ''){
                        //    echo '<span class="button-grey2 w49 right">French</span>';
                        //}else{
                        //    echo '<a href="http://www.cairn.info/article.php?ID_ARTICLE='.$article['ARTICLE_ID_ARTICLE_S'].'" class="button-blue2 w49 right">French</a>';
                        //}

                        echo '<br>';
                        $english = trim($article['LISTE_CONFIG_ARTICLE'][2], '/');
                        if($english == ''){
                            echo '<span class="button-grey2 w100">English
                                        <span data-article-title="'.strip_tags($article['ARTICLE_TITRE']).'" data-suscribe-on-translation="'.$article['ARTICLE_ID_ARTICLE'].'" class="question-mark">
                                            <span class="tooltip">Why is this article not available in English?</span>
                                        </span>
                                    </span>';
                        }else{
                            echo '<a href="./'.$english.'" class="button-blue2 w100">';

                            if(strpos($english,'my_cart.php') !== FALSE){
                                echo 'English <span class="cart-icon">'.$article['ARTICLE_PRIX'].' € </span>';
                            }else{
                                if($article['ARTICLE_PRIX'] == 0
                                    || ($article['NUMERO_MOVINGWALL'] != '0000-00-00'
                                            && $article['NUMERO_MOVINGWALL'] <= date('Ymd')
                                            && $article['ARTICLE_TOUJOURS_PAYANT'] == 0)){
                                    echo 'English : Free';
                                }else{
                                    echo 'English';
                                }
                            }
                            echo '</a>';
                        }?>

                        <?php checkBiblio($revue['REVUE_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], $article['ARTICLE_ID_ARTICLE'],$authInfos); ?>
                    </div>
                </div>

            <?php endforeach; ?>

            <!-- Export Zotero/EndNote/Refworks -->
            <p>
                <a href="<?= Configuration::get('zotero') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Download as RefMan/RIS (<b>ZOTERO</b>)</a><br />
                <a href="<?= Configuration::get('endnote') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Download as ENW (<b>ENDNOTE</b>)</a><br />
                <a href="http://www.refworks.com/express/ExpressImport.asp?vendor=Cairn&amp;filter=Refworks%20Tagged%20Format&amp;encoding=65001&amp;url=<?= Configuration::get('refworks') ?>?t=uniq&ID_ARTICLE=<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>">Export to <b>REFWORKS</b></a>
            </p>
        </div>
    </div>

</div>
</div>
<div id="modal_confirm-why-not-article" class="window_modal">
    <div class="basic_modal">
        <h1>Message sent</h1>
        <p>
            Your email address has been saved.<br>
            We will notify you when this article becomes available in English.
        </p>
        <br>
        <br>
        <button onclick="cairn.close_modal();" class="button-blue">Close</button>
    </div>
</div>

<?php
    /*
        La condition suivante ne concerne que les revues qui sont supportés par le CNRS
        Pour éviter de boucler deux fois, on récupère ici l'éventualité qu'un numéro soit affilié au CNRS (selon le TYPE_NUMPUBLIE).
        Il n'y a pas, à l'heure actuelle, de possibilité pour savoir si une revue est concernée par le partenariat avec le CNRS.
        Pour l'affichage de la mention, voir en fin de fichier

        /!\ Le code qui suit est relativement fragile. Si on passe la liste des numéros sur deux pages (comme par exemple sur l'ancien cairn-int), ça ne marchera plus.
        J'avoue ne pas avoir le temps de réfléchir à une solution plus pérenne pour le moment, désolé.
    */
    if (($numero['NUMERO_TYPE_NUMPUBLIE'] === '5')) {
        $affilToCNRS = true;
    }
    /* Ce qui suite ne concerne que les numéros de revues affiliés au CNRS */
    if ($affilToCNRS === true) {
        $this->javascripts[] = <<<'EOD'
            $(function()  {
                $('#logos_footer').addClass('logo-plural').removeClass('logo-single');;
            });
EOD;
    }
?>


<?php include (__DIR__ . "/../CommonBlocs/invisible.php"); ?>
