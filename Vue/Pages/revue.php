<?php
    $this->titre = $currentArticle["ARTICLE_TITRE"];

    require_once('Blocs/headerArticle.php');

    $this->javascripts[] = '<script type="text/javascript" src="./static/js/article.js"></script>';
    $this->javascripts[] = '<script type="text/javascript" src="./static/js/jquery.expander.min.js"></script>';

    include (__DIR__ . '/../CommonBlocs/tabs.php');

    // On prépare les libellés, urls, ... etc
    $typeRev_suffixe = "";
    $typeNum_suffixe = "";
    $revue_url = "";
    $numero_url = "";
    if ($typePub == "revue" || $typePub == "magazine") {
        $article_libelle = "article";
        $article_det = "cet";
        $revue_url = $typePub . '-' . $revue["REVUE_URL_REWRITING"];
        $numero_url = $typePub . '-' . $revue["REVUE_URL_REWRITING"] . '-' . $revue["NUMERO_ANNEE"] . '-' . $revue["NUMERO_NUMERO"];
    } else {
        $article_libelle = "chapitre";
        $article_det = "ce";
        if ($typePub == "encyclopédie") {
            $typeRev_suffixe = " de poche";
        }
        $revue_url = $numero["NUMERO_URL_REWRITING"] . '--' . $numero["NUMERO_ISBN"];
        $numero_url = $numero["NUMERO_URL_REWRITING"] . '--' . $numero["NUMERO_ISBN"];

        if ($numero["NUMERO_TYPE_NUMPUBLIE"] == 1) {
            $typeNum_suffixe = " collectif";
        }
    }
    $typePub_url = null;
    switch ($typePub) {
        case 'revue':
            $typePub_url = 'Accueil_Revues.php';
            break;
        case 'encyclopédie':
            $typePub_url = 'que-sais-je-et-reperes.php';
            break;
        default:
            $typePub_url = $typePub.'s.php';
            break;
    }

    // Définition de l'URL sur PREPROD / BON A TIRER
    $url_token = "";
	// Concervation du TOKEN dans l'URL
	if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
	{
	   	//$numero_url .= "?token=" . $_GET['token'];
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
        <?php if ($currentArticle['ARTICLE_STATUT'] == 0): ?>
            Cet article est actuellement désactivé.<br />
        <?php endif; ?>
        Sur http://cairn.info, ce numéro <strong>n’apparaîtra pas</strong>. Il apparaît <strong>uniquement</strong> sur <?= Configuration::get('urlSite') ?>.
    </div>
<?php endif; ?>


<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./<?php echo $typePub_url ?>"><?php echo ucfirst($typePub); ?>s<?php echo ($typeRev_suffixe != '' ? $typeRev_suffixe : ''); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./<?php echo $revue_url; ?>.htm"><?php echo ucfirst($typePub=="encyclopédie"?"ouvrage":$typePub); ?><?php echo ($typeNum_suffixe != '' ? $typeNum_suffixe : ''); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <?php if ($typePub == "revue" || $typePub == "magazine") { ?>
        <a class="inactive" href="./<?php echo $numero_url; ?>.htm<?php echo $url_token; ?>">Num&#233;ro</a>
        <span class="icon-breadcrump-arrow icon"></span>
    <?php } ?>
    <a href="#"><?php echo ucfirst($article_libelle); ?></a>
</div>

<div id="body-content">
    <div id="page_article" class="lang-<?= $currentArticle['ARTICLE_LANGUE'] ?>">
        <input type="hidden" id="hits" value="<?= $hits ?>"/>
        <div id="page_header" class="grid-g grid-3-head">
            <div class="grid-u-1-4">
                <a href="./<?php echo $numero_url; ?>.htm<?php echo $url_token; ?>">
                    <img
                        class="big_coverbis"
                        id="numero-cover"
                        alt="<?php echo $revue["REVUE_TITRE"]; ?> <?php echo $revue["NUMERO_ANNEE"]; ?>/<?php echo $revue["NUMERO_NUMERO"]; ?>"
                        src="/<?= $vign_path ?>/<?php echo $revue["REVUE_ID_REVUE"]; ?>/<?= $revue['NUMERO_ID_NUMPUBLIE'] ?>_L204.jpg"
                        >
                </a>
            </div>

            <div class="grid-u-1-2 meta">
                <!-- DEBUT DES METADONNEES DE L'ARTICLE -->
                <?php
                if(isset($htmlDatas['METAS'])){
                //On commence par faire le pré-traitement sur les auteurs...
                $metasHtml = $htmlDatas["METAS"];
                //1 - Remplacement de [ARTICLE_SAME_AUTHOR_URL]
                if($currentArticle['ARTICLE_AUTEUR'] != ''){
                    $theAuthors = explode(",", $currentArticle['ARTICLE_AUTEUR']);
                    foreach ($theAuthors as $theAuthor) {
                        $theauthorParam = explode(':', $theAuthor);
                        $theAutheurPrenom = $theauthorParam[0];
                        $theAutheurNom = $theauthorParam[1];
                        $theAutheurId = $theauthorParam[2];
                        $theAutheurAttribut = $theauthorParam[3];
                        if (preg_match($patternIgnoreLinkOnAuthorContribution, $theAutheurAttribut)) {
                            // On zappe les liens des auteurs dont les contributions doivent être ignorés
                            $replaceStr = '#';
                            $replaceStr .= '" data-with-author-link="no"';
                        } else {
                            $replaceStr = 'publications-de-' . $theAutheurNom . '-' . $theAutheurPrenom . '--' . $theAutheurId . '.htm';
                            $replaceStr .= '" data-with-author-link="yes"';
                        }

                        $metasHtml = preg_replace('[\[ARTICLE_SAME_AUTHOR_URL\]]', $replaceStr, $metasHtml, 1);
                    }
                }
                echo $metasHtml;
                }?>
                <!-- FIN DES METADONNEES DE L'ARTICLE -->

                <!-- Récupération de la section et de la sous-section -->
                <?php
                    // Définition des variables
                    $article_sections       = "";
                    $article_section        = $currentArticle["ARTICLE_SECT_SOM"];
                    $article_sous_section   = $currentArticle["ARTICLE_SECT_SSOM"];

                    // Affichage
                    //if($article_section != "") {$article_sections .= "<span class=\"yellow\"><b>Section&nbsp;: </b></span> $article_section";}
                    //if($article_sous_section != "") {$article_sections .= "- <span class=\"yellow\"><b>Sous-Section&nbsp;: </b></span> $article_sous_section";}
                    //if($article_sections != "") {echo "<div>$article_sections</div>";}
                ?>
            </div>
            <div class="grid-u-1-4">
                <!-- Raccourcis -->
                <div id="raccourcis" class="contrast-box">
                    <h1>Raccourcis</h1>
                    <ul id="article_shortcuts">
                        <li>
                            <a href="#anchor_abstract" id="link-abstract" style="display: none;">
                                Résumé
                                <span class="icon-arrow-black-right icon right"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#anchor_plan" id="link-plan-of-article" style="display: none;">
                                Plan de l'article
                                <span class="icon-arrow-black-right icon right"></span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" id="link-cite-this-article" onclick="cairn.show_modal('#modal_citation');">
                                Citer <?php echo $article_det . " " . $article_libelle; ?>
                                <span class="icon-arrow-black-right icon right"></span>
                            </a>
                        </li>
                        <li>
                            <?php
                                // Définition du label du sommaire
                                if ($typePub == "revue" || $typePub == "magazine") {$label_sommaire = "Sommaire du numéro";}
                                else {$label_sommaire = "Sommaire de l'ouvrage";}
                            ?>
                            <a href="./<?php echo $numero_url; ?>.htm<?php echo $url_token; ?>">
                                <?php echo $label_sommaire; ?> <span class="icon-arrow-black-right icon right"></span>
                            </a>
                        </li>
                        <?php if($currentArticle["REVUE_ACCES_EN_LIGNE"] == 1) { ?>
                        <li><a href="code-abonnement-papier.php?ID_REVUE=<?php echo $revue['REVUE_ID_REVUE']; ?>">Accès abonnés <span class="icon-arrow-black-right icon right"></span></a></li>
                        <?php } ?>

                        <?php if (Configuration::get('allow_backoffice', false)): ?>
                            <li>
                                <a class="bo-content" href="<?= Configuration::get('tires_a_part', '#') ?>?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>">
                                    Tirés à part
                                </a>
                            </li>
                        <?php endif ?>
                    </ul>
                </div>

                <?php if (count($countReferencedBy) > 0) { ?>
                    <!-- Cité par -->
                    <div id="citerpar" class="contrast-box">
                        <h1>Cité par...</h1>
                        <ul id="article-cited-by">
                            <?php
                            foreach ($countReferencedBy as $refBy) {
                                // Définition du type du réferer
                                if($refBy["TYPEPUB"] == 1) {$refByLabel = "Articles de revues"; $refByType = "R";}
                                else if($refBy["TYPEPUB"] == 2) {$refByLabel = "Articles de magazines"; $refByType = "M";}
                                else {$refByLabel = "Ouvrages"; $refByType = "O";}

                                // Affichage
                                echo '<li><a class="cited-by" href="./cite-par.php?ID_ARTICLE=' . $currentArticle["ARTICLE_ID_ARTICLE"] . '&amp;T='.$refByType.'">' . $refByLabel . ' [' . $refBy["CNT"] . '] <span class="icon-arrow-black-right icon right"></span></a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php if($currentArticle['ARTICLE_SUJET_PROCHE'] == 1){ ?>
                    <!-- Voir aussi -->
                    <div id="voiraussi" class="contrast-box">
                        <h1>Voir aussi</h1>
                        <ul id="see_also_links">
                            <li>
                                <a id="see-also" href="./sur-un-sujet-proche.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>">
                                    Sur un sujet proche
                                    <span class="icon-arrow-black-right icon right"></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php }


                if($currentArticle['ARTICLE_EXTRAWEB_TITRE'] != '' && $currentArticle['ARTICLE_EXTRAWEB_NOM_FICHIER'] != ''){?>
                    <!-- Documents -->
                    <div id="documents-associes" class="contrast-box">
                        <h1>Documents associés</h1>
                        <ul id="see_also_links">
                            <?php
                                $output_array = "";
                                preg_match("/^http/", $currentArticle["ARTICLE_EXTRAWEB_NOM_FICHIER"], $output_array);
                                if(empty($output_array)) { ?>
                                    <li><a href="loadextraweb.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>"><?= $currentArticle['ARTICLE_EXTRAWEB_TITRE'] ?><span class="icon-arrow-black-right icon right"></span></a></li>
                                <?php } else {?>
                                    <li><a href="<?php echo $currentArticle['ARTICLE_EXTRAWEB_NOM_FICHIER']; ?>"><?= $currentArticle['ARTICLE_EXTRAWEB_TITRE'] ?><span class="icon-arrow-black-right icon right"></span></a></li>
                            <?php }?>
                        </ul>
                    </div>
                <?php }?>

                <?php if ($numero["ID_ARTICLE_INT"] != null && $currentArticle["ARTICLE_LANGUE"] != "en") { ?>
                    <!-- Affichage du lien vers la traduction SI la langue originale de l'article n'est pas EN et si une traduction existe bien -->                    
                    <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/article-<?=$numero["ID_ARTICLE_INT"]?>--<?php echo $numero["URL_REWRITING_INT"]; ?>.htm" class="cairn-int_link"><span class="label">Read this article in English</span></a>
                <?php } ?>

                <!-- Suggestions d'achats pour les institutions -->
                <?php if ($hasAlreadySubmitSuggestionForInstitution === false): ?>
                    <div id="suggest-ouvrage-institution" class="contrast-box">
                        <h1>Suggérer cet ouvrage à votre bibliothécaire</h1>
                        <p>
                            Votre suggestion sera transmise aux personnes en charge des acquisitions au sein de votre institution.
                        </p>
                        <button class="button" onclick="ajax.postSuggestionOuvragePourInstitution('<?= $numero['NUMERO_ID_NUMPUBLIE'] ?>')">Suggérer</button>
                    </div>
                    <div id="suggest-ouvrage-institution-modal" class="window_modal" style="display:none;">
                        <div class="info_modal">
                            <p>Merci ! Votre demande a bien été prise en compte.</p>
                            <div class="buttons">
                                <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <div class="grid-g grid-4-article">
            <?php
            include(__DIR__ . '/Blocs/numeroMeta.php');
            ?>
            <div class="grid-u-3-4">
                <div id="article_content">
                    <hr class="grey">
                    <?php
                    include(__DIR__ . '/Blocs/navPage.php');
                    ?>

                    <ul id="usermenu-tools" style="margin-top: 0px;">
                        <?php
                            // Bouton Ajout à la Bibliographie
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($revue['REVUE_ID_REVUE'], $numero['NUMERO_ID_NUMPUBLIE'], $currentArticle['ARTICLE_ID_ARTICLE'],$authInfos,'usermenu');


                            // Bouton Télécharger au format PDF (si la config de l'article le permet)
                            $configs_articles = explode(',',$currentArticle['ARTICLE_CONFIG_ARTICLE']);
                        ?>
                        <?php if($configs_articles[3] == 1) { ?>
                            <li>
                                <a class="icon icon-usermenu-tools icon-usermenu-tools-pdf"
                                   target="_blank" href="load_pdf.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>"
                                   data-webtrends="goToPdfArticle" data-id_article="<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>"
                                   data-titre=<?= Service::get('ParseDatas')->cleanAttributeString($currentArticle['ARTICLE_TITRE']) ?>
                                   data-authors=<?= Service::get('ParseDatas')->cleanAttributeString(Service::get('ParseDatas')->stringifyRawAuthors($currentArticle['ARTICLE_AUTEUR'], 0, null, null, null, true, ',', ':')) ?>
                                >
                                <label><span>Télécharger au format PDF</span></label>
                                </a>
                            </li>
                            <?php
                                // Afficher / Cacher le surlignage (uniquement suite à une recherche)
                                if($_GET["hits"]) { ?>
                                <li>
                                    <a class="icon icon-usermenu-tools icon-usermenu-tools-highlight-inactive highlight-inactive" href="javascript:void(0);" onclick="$(this).hide();$('a.highlight-active').show();$('span.highlight').addClass('noHighlight');"><label><span>Désactiver le surlignage</span></label></a>
                                    <a style="display: none;" class="icon icon-usermenu-tools icon-usermenu-tools-highlight-active highlight-active" href="javascript:void(0);" onclick="$(this).hide();$('a.highlight-inactive').show();$('span.highlight').removeClass('noHighlight');"><label><span>Activer le surlignage</span></label></a>
                                </li>
                            <?php } ?>
                        <?php } ?>

                        <?php if($configs_articles[2] == 1) { ?>
                            <!-- // Bouton Feuilleter (si la config de l'article le permet) -->
                            <li>
                                <a class="icon icon-usermenu-tools icon-usermenu-tools-summary"
                                   target="_blank"
                                   href="feuilleter.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>"
                                >
                                <label><span>Voir en mode feuilletage</span></label>
                                </a>
                            </li>
                        <?php } ?>
                            <!-- // Bouton d'impression -->
                            <li>
                                <a class="icon icon-usermenu-tools icon-usermenu-tools-print"
                                   target="_blank"
                                   href="./article_p.php?ID_ARTICLE=<?php echo $currentArticle["ARTICLE_ID_ARTICLE"]; ?>"
                                >
                                <label><span>Version imprimable</span></label>
                                </a>
                            </li>
                        <?php
                            // Bouton d'accès au XML
                            if (Configuration::get('allow_backoffice', false)) { ?>
                                <?php
                                    /*
                                        Lien vers le menu conversion sur le back-office, permettant diverses actions sur les numéros/articles
                                    */
                                    $_link_edit_xml = Configuration::get('edit_xml', '#');
                                    if ($_link_edit_xml !== '#') {
                                        $_link_edit_xml .= '#id_article='.$currentArticle['ARTICLE_ID_ARTICLE'];
                                    }
                                ?>
                                <li>
                                    <a class="icon icon-usermenu-tools icon-usermenu-tools-xml"
                                       data-tooltip=""
                                       target="_blank"
                                       href="<?= $_link_edit_xml ?>"
                                    >
                                    <label><span>Édition xml</span></label>
                                    </a>
                                </li>
                        <?php } ?>
                    </ul>

                    <div class="col600" id="textehtml">
                        <!-- DEBUT DU CONTENU DE L'ARTICLE -->
                        <?php echo isset($htmlDatas["CONTENUS"])?$htmlDatas["CONTENUS"]:'';
                        if($typePub == "magazine" && ($revue['SOAP'] == null || $revue['SOAP'] == '')){
                            echo '<br><p class="copymag">&copy; '.$revue['EDITEUR_NOM_EDITEUR'].', '.$numero['NUMERO_ANNEE'].'</p>';
                        }

                        ?>
                        <!-- FIN DU CONTENU DE L'ARTICLE -->


                        <?php
                            $linktoInt = !empty($currentArticle["ARTICLE_ID_ARTICLE_S"]) ? $currentArticle["ARTICLE_ID_ARTICLE_S"] : $currentArticle["ARTICLE_URL_REWRITING_EN"];
                        ?>

                        <?php if ($currentArticle["ARTICLE_LANGUE"] != "en" && $numero["HAS_RESUME_INT"] != 0) { ?>
                            <p class="center" id="link_abstract_en">
                                <?php /*<a href="http://cairn-int.info/resume.php?ID_ARTICLE=<?=$numero["ID_ARTICLE_INT"]?>" class="link_custom_en" style="color:black;">
                                    <span class="icon icon-round-arrow-right black mr6"></span>
                                    English abstract on Cairn International Edition
                                </a> */ ?>
                                <!-- Lien URL REWRITING -->
                                <?php
                                    // Création du lien vers l'abstract
                                    // Récupération des données de l'abstract
                                    if($numero["HAS_RESUME_ID"] != null) {
                                        $abstract_link = "abstract-".$numero["HAS_RESUME_ID"]."--".$numero["HAS_RESUME_URL"].".htm";
                                        echo "<a href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/".$abstract_link."\" class=\"link_custom_en\" style=\"color:black;\">
                                                <span class=\"icon icon-round-arrow-right black mr6\"></span>
                                                English abstract on Cairn International Edition
                                              </a>";
                                    }
                                ?>

                            </p>
                            <style>
                                #resume_en,.abstract.nb1.en > h3 { display : none; }
                            </style>
                        <?php } ?>

                    </div>
                    <hr style="margin-top:3em;" class="grey">
                    <?php include(__DIR__ . '/Blocs/navPage.php'); ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include(__DIR__ . '/Blocs/citation.php');
include (__DIR__ . "/../CommonBlocs/invisible.php");

// #85748 - Quand un utilisateur arrive sur une page de résumé ou texte intégral à partir de crossref.org ou scopus.com,
// et que l'article se trouve sur cairn.info et sur cairn-int.info, on affiche un lightbox avec un choix de destination
if($displayArticleOnCairnOrCairnInt) {include (__DIR__ . "/choix_langues.php");}
?>


<?php
$this->javascripts[] = <<<'EOD'
    $(function() {
        $("#link_abstract_en").appendTo($('#from_xml_bottom .abstract'));
    });

    if($("#from_xml_bottom section#plan-of-article").length) {$("a#link-plan-of-article").show();}
EOD;
?>

<?php
/*$this->javascripts[] = <<<'EOD'
    $(document).ready(function() {
      // override default options (also overrides global overrides)
      $('div#textehtml').expander({
        slicePoint:       50000,  // default is 100
        expandPrefix:     '', // default is '... '
        expandText:       'Afficher la suite de l\'article', // default is 'read more'
        userCollapseText: 'Réduire l\'article',  // default is 'read less'
        moreClass: 'expander-read-more',
        lessClass: 'expander-read-less',
      });

    });
EOD;*/
?>

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


