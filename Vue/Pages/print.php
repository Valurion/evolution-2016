<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $currentArticle["ARTICLE_TITRE"]; ?></title>
        <?= $this->getHeaders('html') ?>

        <?php
            // Inclusion des feuilles de style css
            require_once('Vue/CommonBlocs/headerCss.php');
        ?>
        <link href="static/css/article.css" rel="stylesheet" type="text/css">

    </head>
    <body id="page-print-article" class="page-article">

        <div id="copyright">
            <img alt="cairn logo" src="static/images/logo-cairn.png">
            <p><span class="numero_pages"><?php echo $currentArticle["ARTICLE_PAGE_DEBUT"] . "-" . $currentArticle["ARTICLE_PAGE_FIN"]; ?></span>
                Distribution électronique Cairn pour <?= $currentArticle['EDITEUR_NOM_EDITEUR'] ?> © <?= $currentArticle['EDITEUR_NOM_EDITEUR'] ?>. Tous droits réservés pour tous pays. Il est interdit, sauf accord préalable et écrit de l’éditeur, de reproduire (notamment par photocopie) partiellement ou totalement le présent article, de le stocker dans une banque de données ou de le communiquer au public sous quelque forme et de quelque manière que ce soit.
            </p>
        </div>

        <div class="full" id="textehtml">

            <?php echo isset($htmlDatas['METAS'])?$htmlDatas["METAS"]:''; ?>
            <?php echo isset($htmlDatas['CONTENUS'])?$htmlDatas["CONTENUS"]:''; ?>

        </div>
        <div id="wmk_container" style="writing-mode:tb-rl; white-space: nowrap;-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg);left:720px;color:#666;position:relative;Bottom:0px;width:90px;" id="lignesssss">
            <span id="wmk" style="padding:0 200px 0 0">Document téléchargé depuis www.cairn.info -  -   <?php echo $ipClient; ?> - <?php
                date_default_timezone_set('Europe/Paris');
                echo date('d/m/Y H\hi');
                ?>. &copy; <?php echo $revue["EDITEUR_NOM_EDITEUR"]; ?></span>
        </div>
        <?php
            if (Configuration::get('webtrends_datasource', null)) {
                include(__DIR__ . '/CommonBlocs/webtrends.php');
            }
        ?>

<?php

// Watermarking dynamique sur base de la longueur du contenu
$this->javascripts[] = <<<'EOD'
    var contentHeight = document.getElementById('page-print-article').offsetHeight;
    var watermarkLength = document.getElementById('wmk').offsetWidth;

    var nb = Math.floor(contentHeight / watermarkLength);

    var wmk_elem = document.getElementById('wmk');
    var wmk_container = document.getElementById('wmk_container');
    for (i = 1; i < nb; i++) {
        wmk_container.appendChild(wmk_elem.cloneNode());
    }
EOD;

?>

    <!-- JS starts here -->
    <?php
        // Inclusion des scripts js
        require_once(__DIR__ . '/../CommonBlocs/footerJavascript.php');
    ?>

    <?php
        if (Configuration::get('webtrends_datasource', null)) {
            include(__DIR__ . '/../CommonBlocs/webtrends.php');
        }
    ?>

    <?php if (Configuration::get('allow_backoffice', false)): ?>
        <script>
            <?php if (isset($_SERVER['REDIRECT_QUERY_STRING'])): ?>
                // Uniquement à destination des développeurs, pour faciliter la recherche de controlleur et actions
                console.debug("URL-DEBUG :: <?= $_SERVER['REDIRECT_QUERY_STRING'] ?>");
            <?php endif; ?>
            window.DEBUG = true;
        </script>
    <?php endif; ?>

    <?= $this->getJavascripts(); ?>
    <!-- JS ends here -->
    </body>
</html>
