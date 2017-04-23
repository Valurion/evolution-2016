<div style="margin: auto; width: 960px; text-align: center; margin-top: 10em;">
    <div style="margin-bottom: 2em;">
        <img src="static/images/logo-cairn.png" />
    </div>
    <h1>Hem...</h1>
    <p>
        Le site est pour le moment en maintenance.<br />
        Nous faisons le maximum pour remettre Cairn.info en ligne dès que possible.<br />
        Merci pour votre patience !
    </p>
    <p>
        L'équipe Cairn.info<br />
        <a href="https://twitter.com/cairninfo">https://twitter.com/cairninfo</a>
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

