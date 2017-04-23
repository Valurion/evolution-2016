<?php
$this->titre = $numero["NUMERO_TITRE"];
$theAuthors = explode(",", $currentArticle['ARTICLE_AUTEUR']);
$theAuthorsText = "";
foreach ($theAuthors as $theAuthor):
    $theauthorParam = explode(':', $theAuthor);
    $theAutheurPrenom = $theauthorParam[0];
    $theAutheurNom = $theauthorParam[1];
    $theAuthorsText .= ($theAuthorsText == "" ? "" : ", ") . $theAutheurPrenom . " " . $theAutheurNom;
    ?>
<?php endforeach; ?>
<body>
    <!-- Pour des raisons de lisibilité, le header propre à cairn n'est pas affiché. Ainsi, on se rapproche du design de zen.php -->
    <div id="logo" style="margin-top:1em; margin-bottom:1em; text-align:center;">
        <a href="/"><img src="./static/images/logo-cairn.png" srcset="./static/images/logo-cairn@2x.png 2x, ./static/images/logo-cairn@3x.png 3x, ./static/images/logo-cairn@4x.png 4x" alt="CAIRN.INFO : Chercher, repérer, avancer."></a>
    </div>
    <div id="body-content">


        <div id="pdfviewer">
            <div class="wrapper_menu grid-g grid-3-head">
                <div class="grid-u-1-4 return_to_numero"><a href="<?= $numero["NUMERO_URL_REWRITING"] ?>--<?= $numero["NUMERO_ISBN"] ?>.htm"><span class="icon">&#10140;</span>Retour au sommaire</a></div>
                <form class="menu grid-u-3-4" id="menu" style="display:none;">
                    <label for="adjust-zoom">Zoom</label>
                    <input type="range" min="0" max="10" step="1" id="adjust-zoom">
                    <button type="button" id="adjust-width">&#8596;</button>
                    <button type="button" id="adjust-height">&#8597;</button>
                    <button type="button" id="rotate-left">&#8634;</button>
                    <button type="button" id="rotate-right">&#8635;</button>
                    <button type="button" id="goto-first-page">&#x25c4;</button>
                    <button type="button" id="goto-prev-page">&#x25c4;</button>
                    <span id="current-page">Curr page</span>
                    <button type="button" id="goto-next-page">&#x25bA;</button>
                    <button type="button" id="goto-last-page">&#x25bA;</button>
                    <button type="button" id="show-grid">&#9638;</button>
                    <button type="button" id="show-one-page">&#9647;</button>
                    <button type="button" id="show-two-pages">&#9707;</button>
                    <button type="button" id="print">&#9113;</button>
                    <button type="button" id="show-fullscreen">Fullscreen</button>
                </form>
            </div>
            <div class="wrapper_viewer grid-g grid-3-head">
                <div class="nav grid-u-1-4">
                    <div class="meta">
                        <div class="title numero"><?= $numero["NUMERO_TITRE"] ?></div>

                        <ul class="authors numero">
                            <?php
                            $theAuthors = explode(",", $currentArticle['ARTICLE_AUTEUR']);
                            $theAuthorsText = "";
                            foreach ($theAuthors as $theAuthor):
                                $theauthorParam = explode(':', $theAuthor);
                                $theAutheurPrenom = $theauthorParam[0];
                                $theAutheurNom = $theauthorParam[1];
                                $theAuthorsText .= ($theAuthorsText == "" ? "" : ", ") . $theAutheurPrenom . " " . $theAutheurNom;
                                ?>
                                <li class="author"><?php echo $theAutheurPrenom; ?> <?php echo $theAutheurNom; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="pages article yellow">
                            <?php
                            echo ($currentArticle["ARTICLE_PAGE_DEBUT"] > 0 ? "Page" . ($currentArticle["ARTICLE_PAGE_FIN"] > 0 ? 's ' : ' ') . $currentArticle["ARTICLE_PAGE_DEBUT"] : '')
                            . ($currentArticle["ARTICLE_PAGE_FIN"] > 0 ? (' à ' . $currentArticle["ARTICLE_PAGE_FIN"]) : '');
                            ?>
                        </div>
                        <div class="title article"><?= $currentArticle["ARTICLE_TITRE"] ?></div>

                        <form class="search_inside search_feuilleter" method="post" action="javascript:searchText();" name="rechercheForm" id="rechercheForm">
                            <button type="submit">
                                <img src="./static/images/icon/magnifying-glass-black.png">
                            </button>
                            <input type="text" onblur="searchText();" placeholder="Rechercher" id="larech" name="larech" class="inputText"/>
                        </form>
                    </div>
                    <div class="summary">
                        <h2>
                            <span id="trigger_summary_numero" class="left">Sommaire</span>

                        </h2>
                        <div id="summary_numero" style="height: 285px;">
                            <?php
                            foreach ($articles as $article) {
                                echo '<a href="feuilleter.php?ID_ARTICLE=' . $article["ARTICLE_ID_ARTICLE"] . '">' . $article["ARTICLE_TITRE"] . '</a>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div id="swf_wrapper" class="viewer grid-u-3-4" style="width:699px">
                    <div id="altContent">
                        <h1>&#160;<br />&#160;<br />Feuilleteur Cairn.info</h1>
                        <p>Pour consulter cet ouvrage sur Cairn.info, il vous faut disposer de l'application Adobe Flash Player.</p>

                        <p><a href="http://www.adobe.com/go/getflashplayer">Téléchargez Adobe Flash Player.</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include (__DIR__ . "/../CommonBlocs/invisible.php"); ?>
</body>



<script type="text/javascript" src="flash/js/swfobject.js"></script>

<?php
    $userEmail = isset($authInfos['U']['EMAIL']) ? $authInfos['U']['EMAIL'] : 'un visiteur';
    $flashVars = json_encode([
        'xmlPath' => './load_xml.php?ID_ARTICLE='.$currentArticle["ARTICLE_ID_ARTICLE"].'&page.xml',
        'urlPage' => '',
        'pdfPath' => './load_pdf.php?ID_ARTICLE='.$currentArticle["ARTICLE_ID_ARTICLE"].'&page.xml',
        'startpage' => $currentArticle["ARTICLE_PAGE_DEBUT"],
        'print' => $pageType == 'FEUILA' ? 'all' : 'current',
        'watermark' => "$userEmail avec l'adresse IP " . $_SERVER['REMOTE_ADDR'] . " sur www.cairn.info",
        'copyPastRefBefore' => "« ",
        'removeStaticText' => "0",
        'copyPastRef' => ' » ('
            .strip_tags($theAuthorsText)
            .', '
            .strip_tags($currentArticle["ARTICLE_TITRE"])
            .', '
            .$revue["EDITEUR_NOM_EDITEUR"]
            .' « '
            .strip_tags($revue['TYPEPUB'] == 1 ? $revue["REVUE_TITRE"] : $revue['NUMERO_TITRE'])
            .' », '
            .$numero["NUMERO_ANNEE"]
            .', p. '
            .$currentArticle["ARTICLE_PAGE_DEBUT"]
            .')'
    ]);
    $htmlDatasToJson = json_encode($htmlDatas ? $htmlDatas : '');
?>

<?php
$this->javascripts[] = "flashvars = $flashVars;";
$this->javascripts[] = "htmlDatas = $htmlDatasToJson;";
$this->javascripts[] = <<<'EOD'
    $(window).load(function() {
        "use strict";

        var params = {
            menu: 'true',
            scale: 'noscale',
            bgcolor: '#FFFFFF',
            wmode: 'transparent',
            allowfullscreen: 'true',
            allowscriptaccess: 'sameDomain'
        };

        var attributes = {
            id: 'swfViewer'
        };

        swfobject.embedSWF(
            './static/AbstraktPDFViewer.swf',
            'altContent',
            '100%',
            '100%',
            '9.0.0',
            false,
            flashvars,
            params,
            attributes
        );
        var viewer = $('#swfViewer')[0];

        var _pre_v = $('#adjust-zoom').val();
        var _curr_v = _pre_v;
        $('#adjust-zoom').on("input", function() {
            _curr_v = this.value;
            if (_pre_v < _curr_v)
                viewer.zoomPlus();
            if (_pre_v > _curr_v)
                viewer.zoomMinus();
            _pre_v = _curr_v;
        });
        // Ajuste le viewer au maximum de largeur, par rapport à la largeur qui lui est alloué
        $('#adjust-width').click(function() {
            viewer.adjustZoomOnWidth()
        });
        // Ajuste le viewer au maximum de hauteur, par rapport à la hauteur qui lui est alloué
        $('#adjust-height').click(function() {
            viewer.adjustZoomOnPage();
        });
        // Tourne la page affiché dans le viewer avec une rotation anti-horaire de 90°
        $('#rotate-left').click(function() {
            viewer.rotationLeft();
        });
        // Tourne la page affiché dans le viewer avec une rotation horaire de 90°
        $('#rotate-right').click(function() {
            viewer.rotationRight();
        });


        $('#goto-first-page').click(function() {
            viewer.firstPage();
        });
        $('#goto-prev-page').click(function() {
            viewer.prevPage();
        });
        $('#goto-next-page').click(function() {
            viewer.nextPage();
        });
        $('#goto-last-page').click(function() {
            viewer.lastPage();
        });


        $('#show-grid').click(function() {
            viewer.dispositionGrid();
        });
        $('#show-one-page').click(function() {
            viewer.dispositionSimple();
        });
        $('#show-two-pages').click(function() {
            viewer.dispositionDouble();
        });


        $('#print').click(function() {
            viewer.externalPrint();
        });


        // Initialisation des variables
        var $summary_numero = $('#summary_numero');
        var $summary_article = $('#summary_article');
        var $trigger_numero = $('#trigger_summary_numero');
        var $trigger_article = $('#trigger_summary_article');

        var win_h = $(window).height();
        var $swf_wrapper = $('#swf_wrapper');
        var $summaries = $('#summary_numero, #summary_article');

        // Calcul la hauteur maximal à afficher pour le viewer pdf et la section sommaire.
        // Aucun élement ne doit dépasser la fenêtre utilisateur, ceci afin d'éviter un double scroll (viewer + fenetre)
        var swf_h = Math.floor(win_h - $swf_wrapper.offset().top)
        $swf_wrapper.find('#swfViewer').height(swf_h);
        $swf_wrapper.height(swf_h);
        $summaries.height(Math.floor(win_h - $summaries.offset().top));

        // Met en place le système d'onglet clickable pour passer du sommaire numéro au sommaire article
        $trigger_article.click(function() {
            $this = $(this);
            if (!$this.hasClass('unactive'))
                return false;
            $summary_numero.hide();
            $summary_article.show();
            $this.removeClass('unactive');
            $trigger_numero.addClass('unactive');
        });

        $trigger_numero.click(function() {
            $this = $(this);
            if (!$this.hasClass('unactive'))
                return false;
            $summary_article.hide();
            $summary_numero.show();
            $this.removeClass('unactive');
            $trigger_article.addClass('unactive');
        });

    });
EOD;

$this->javascripts[] = <<<'EOD'
    // Fonctions appeles dans le viewer (HTML -> SWF)
    function changePage(n) {
        document.getElementById('swfViewer').changePage(n);
    }

    function searchText() {
        var texte = document.getElementById('larech').value;
        document.getElementById('swfViewer').searchText(texte);
    }
    function loadComplete() {}

    function highlight() {
        var texte = htmlDatas;

        if(texte != '' && document.getElementById('swfViewer')){
            document.getElementById('swfViewer').highlight(texte);
        }
    }
EOD;
?>
