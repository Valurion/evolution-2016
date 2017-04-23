<?php
$this->titre = $currentArticle["ARTICLE_TITRE"];

    require_once('Vue/Pages/Blocs/headerArticle.php');

    $this->javascripts[] = '<script type="text/javascript" src="./static/js/article.js"></script>';

include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./disc-<?= $curDiscipline?>.htm"><?= $filterDiscipline?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./journal-<?php echo $revue["REVUE_URL_REWRITING"] ?>.htm">Journal</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./journal-<?php echo $revue["REVUE_URL_REWRITING"] ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">Issue</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Abstract</a>
</div>

<div id="body-content">
    <div id="page_article">
        <div id="page_header" class="grid-g grid-3-head">
            <div class="grid-u-1-4">
                <a href="./journal-<?php echo $revue["REVUE_URL_REWRITING"] ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">
                    <img class="big_coverbis" alt="<?php echo $revue["REVUE_TITRE"]; ?> <?php echo $revue["NUMERO_ANNEE"]; ?>/<?php echo $revue["NUMERO_NUMERO"]; ?>" src="/<?= $vign_path ?>/<?php echo $revue["REVUE_ID_REVUE"]; ?>/<?= $revue['NUMERO_ID_NUMPUBLIE'] ?>_H310.jpg">
                </a>
            </div>

            <div class="grid-u-1-2 meta">
                <!-- DEBUT DES METADONNEES DE L'ARTICLE -->
                <?php
                //On commence par faire le pré-traitement sur les auteurs...
                $metasHtml = $htmlDatas["METAS"];
                //1 - Remplacement de [ARTICLE_SAME_AUTHOR_URL]
                $theAuthors = explode(",", $currentArticle['ARTICLE_AUTEUR']);
                foreach ($theAuthors as $theAuthor) {
                    $theauthorParam = explode(':', $theAuthor);
                    $theAutheurPrenom = $theauthorParam[0];
                    $theAutheurNom = $theauthorParam[1];
                    $theAutheurId = $theauthorParam[2];
                    $replaceStr = 'publications-de-' . $theAutheurNom . '-' . $theAutheurPrenom . '--' . $theAutheurId . '.htm';

                    $metasHtml = preg_replace('[\[ARTICLE_SAME_AUTHOR_URL\]]', $replaceStr, $metasHtml, 1);
                }

                echo $metasHtml;
                ?>

                <!-- FIN DES METADONNEES DE L'ARTICLE -->
            </div>
            <div class="grid-u-1-4">
                <!-- Raccourcis -->
                <div id="raccourcis" class="contrast-box">
                    <h1>Shortcuts</h1>
                    <ul id="article_shortcuts">
                        <li style="display:none" id="__article_shortcuts_template">
                            <a href="{link}">{title} <span class="icon-arrow-black-right icon right"></span></a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" id="link-cite-this-article" onclick="cairn.show_modal('#modal_citation');">
                                Citation export <span class="icon-arrow-black-right icon right"></span>
                            </a>
                        </li>
                        <li>
                            <a href="./journal-<?php echo $revue["URL_REWRITING_EN"] ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">
                                Journal issue <span class="icon-arrow-black-right icon right"></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <?php
                    $french = $currentArticle['LISTE_CONFIG_ARTICLE'][1];
                    if($numero["ID_ARTICLE_CAIRN"] != "") { ?>
                        <div class="frenchVersion">
                            <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/article.php?ID_ARTICLE=<?= $numero['ID_ARTICLE_CAIRN']?>" id="article-french-version">Full text in French</a>
                        </div>
                <?php } ?>

                <?php
                    $english = $currentArticle['LISTE_CONFIG_ARTICLE'][2];
                    if ($english != '' && strpos($english,'my_cart.php') !== FALSE){ ?>
                        <div class="frenchVersion">
                            <a href="./my_cart.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE']?>"><span class="add-to-cart-icon"></span><span class="add-to-cart-text-container"><span class="value-currency"><?= $currentArticle['ARTICLE_PRIX']?>&nbsp;€</span>Add to cart</span></a>
                        </div>
                <?php } ?>
            </div>
        </div>

        <div class="grid-g grid-4-article">
            <?php
            include(__DIR__ . '/Blocs/numeroMeta.php');
            ?>

            <div class="grid-u-3-4">
                <div id="article_content" class="content">
                    <hr class="grey">
                    <?php
                    include(__DIR__ . '/Blocs/navPage.php');
                    ?>
                    <ul id="usermenu-tools" style="margin-top: 0px;">
                        <?php
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($revue['REVUE_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], $currentArticle['ARTICLE_ID_ARTICLE'],$authInfos,'usermenu');
                        ?>
                        <?php
                            /* Contrairement à cairn.info qui utilise le champ CONFIG_ARTICLE de la table article, sur 
                             * cairn-int, on utilise un service afin de déterminer quelles sont les boutons disponibles,
                             * comme sur la page numéro listant les articles. Ce service défini si il existe une version 
                             * Française, un résumé et un article en Anglais, c'est ce dernier élément qui nous intéresse.
                             * D'une manière générale, si un article existe alors il y a une version PDF.
                             * 
                             * Les autres boutons sont générés de manière traditionelles, via le champ CONFIG_ARTICLE */

                            // Définition des boutons (si la config de l'article le permet) /!\ hors HTML et PDF
                            $configs_articles = explode(',',$currentArticle['ARTICLE_CONFIG_ARTICLE']);
                        ?>

                        <?php if ($hasAccess) { ?>
                        <?php
                            // Version HTML et PDF
                            $english = trim($currentArticle['LISTE_CONFIG_ARTICLE'][2], '/');
                            
                            // Il existe bien un article
                            if($english != "") { ?>
                                <!-- Version PDF -->
                                <li>
                                    <a class="icon icon-usermenu-tools icon-usermenu-tools-pdf" 
                                       target="_blank" href="load_pdf.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>" 
                                       data-webtrends="goToPdfArticle" data-id_article="<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" 
                                       data-titre=<?= Service::get('ParseDatas')->cleanAttributeString($currentArticle['ARTICLE_TITRE']) ?> 
                                       data-authors=<?= Service::get('ParseDatas')->cleanAttributeString(Service::get('ParseDatas')->stringifyRawAuthors($currentArticle['ARTICLE_AUTEUR'], 0, null, null, null, true, ',', ':')) ?>
                                    >
                                    <label><span>PDF Version</span></label>
                                    </a>
                                </li>
                                <!-- Version HTML -->
                                <li>
                                    <a class="icon icon-usermenu-tools icon-usermenu-tools-html"
                                       href="./article-<?= $currentArticle['ARTICLE_ID_ARTICLE']?>--<?= $currentArticle['ARTICLE_URL_REWRITING_EN']?>.htm"
                                    >
                                    <label><span>HTML Version</span></label>
                                    </a>
                                </li>
                        <?php } ?>

                        <?php if($configs_articles[2] == 1) { ?>
                            <!-- Feuilleteur -->
                            <li>
                                <a class="icon icon-usermenu-tools icon-usermenu-tools-summary" 
                                   target="_blank" 
                                   href="feuilleter.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>"
                                >
                                <label><span>Voir en mode feuilletage</span></label>
                                </a>
                            </li>
                        <?php } ?>
                        <?php } ?>
                        
                            <!-- Impression -->
                            <li>
                                <a class="icon icon-usermenu-tools icon-usermenu-tools-print" 
                                   target="_blank" 
                                   href="./article_p.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>"
                                >
                                <label><span>Printer-friendly</span></label>
                                </a>
                            </li>
                    </ul>
                    <div class="col600">
                        <!-- DEBUT DU CONTENU DE L'ARTICLE -->
                        <?php echo $htmlDatas["CONTENUS"]; ?>
                        <!-- FIN DU CONTENU DE L'ARTICLE -->

                        <style>
                            #resume_en { display : block; }
                        </style>
                        <br/>
                        <div class="add-to-cart">
                            <div style="display : table; text-align : center;">
                                <?php
                                $english = $currentArticle['LISTE_CONFIG_ARTICLE'][2];
                                if ($english != '') {
                                    if(strpos($english,'my_cart.php') !== FALSE){
                                    ?>
                                        <div style="display : table-cell;" class="frenchVersionCart w10">
                                            <a href="./my_cart.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE']?>">
                                            <span class="add-to-cart-text-container">
                                                <span class="value-currency"><?= $currentArticle['ARTICLE_PRIX']?>&nbsp;€</span>
                                                Add to cart
                                            </span>
                                            </a>
                                        </div>
                                    <?php }else{?>
                                        <div style="display : table-cell;" class="frenchVersion w10">
                                            <a href="./article-<?= $currentArticle['ARTICLE_ID_ARTICLE']?>--<?= $currentArticle['ARTICLE_URL_REWRITING_EN']?>.htm">Full text in English</a>
                                        </div>
                                    <?php }
                                }
                                $french = $currentArticle['LISTE_CONFIG_ARTICLE'][1];
                                //if($french != ''){
                                if($numero["ID_ARTICLE_CAIRN"] != "") { 
                                ?>
                                    <div style="display : table-cell;" class="frenchVersion w10">
                                        <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/article.php?ID_ARTICLE=<?= $numero["ID_ARTICLE_CAIRN"]?>">Full text in French</a>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>


                    <?php
                        /* Ce qui suite ne concerne que les articles de revues affiliés au CNRS */
                        if ($numero['NUMERO_TYPE_NUMPUBLIE'] === '5'):
                    ?>
                        <div class="section" style="clear:both;">
                            <b>The English version of this issue is published thanks to the support of the CNRS</b>
                        </div>
                        <hr style="margin-top:1em;" class="grey">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
    // Définition des métas données (from CAIRN3 ou INT si EN)
    $metaArticle = $numero["META_ARTICLE_CAIRN"];
    $metaNumero  = $numero["META_NUMERO_CAIRN"];
    include(__DIR__ . '/Blocs/citation.php');
?>

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
