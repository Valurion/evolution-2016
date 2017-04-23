<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <!--base href="<?= $racineWeb . '/cairn' ?>" -->

        <?php
            // Inclusion des feuilles de style css
            require_once('CommonBlocs/headerCss.php');
        ?>
        <title><?= strip_tags($titre) ?> - Cairn International</title>
    </head>
<body>

    <?= $contenu ?>

    <!-- JS starts here -->
    <?php
        // Inclusion des scripts js
        require_once('CommonBlocs/footerJavascript.php');
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
