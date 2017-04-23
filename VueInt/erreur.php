<div style="margin: auto; width: 960px; text-align: center; margin-top: 10em;">
    <div style="margin-bottom: 2em;">
        <img src="static/images/logo-cairn-int.png" />
    </div>
    <h1>Oops...</h1>
    <p>
        The site is currently undergoing maintenance.<br />
        We are doing our best and get it back online as soon as possible.<br />
        Thanks for your patience!
    </p>
    <p>
        L'équipe Cairn.info<br />
        <a href="https://twitter.com/cairnint">https://twitter.com/cairnint</a>
    </p>
    <?php if (Configuration::get('allow_backoffice', false)): ?>
        <p style="border: 1px dashed red; margin-top: 2em;">
            <?= $this->nettoyer($msgErreur) ?>
        </p>
    <?php else: ?>
        <script>
            console.log(<?= json_encode($this->nettoyer($msgErreur)) ?>);
        </script>
    <?php endif; ?>
</div>

