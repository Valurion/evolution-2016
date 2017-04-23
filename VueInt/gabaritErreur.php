<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <title>Maintenance | <?= Configuration::get('siteName', 'Cairn International') ?></title>
        <meta name="viewport" content="width=1024">
        <link rel="icon" type="image/png" href="favicon.ico" />
        <!-- cairn-build :: [test] -->

        <?php
            // Inclusion des feuilles de style css
            require_once('CommonBlocs/headerCss.php');
        ?>

        <?= $this->getHeaders('html', "\n        ") ?>

    </head>
    <body>

        <div id="contenu">
            <?= $contenu ?>
        </div> <!-- #contenu -->
        <!-- #global -->
    <!-- JS starts here -->
    <?php
        // Inclusion des scripts js
        require_once('CommonBlocs/footerJavascript.php');
    ?>

    <?php
        if (Configuration::get('webtrends_datasource', null)) {
            include(__DIR__ . '/../Vue/CommonBlocs/webtrends.php');
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
