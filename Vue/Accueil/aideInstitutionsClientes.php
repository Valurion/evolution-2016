<?php $this->titre = "Comment accéder à Cairn.info ?"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./aide-institutions-clientes.htm">Accéder à Cairn.info</a>
</div>


<div id="body-content">
    <div id="free_text" class="center">
        <a name="top"></a>
        <div id="flashContent" style="display:block; margin:0 auto;background-color: #F2F2E5; height: 500px; width: 770px;"></div>
    </div>
</div>


<script type="text/javascript" src="flash/js/swfobject.js"></script>
<?php $this->javascripts[] = <<<'EOD'
    $(function() {
        var flashvars = {};

        flashvars.path = 'flash/';
        flashvars.settings_file = 'flash/xml/ammap_settings.xml';
        flashvars.data_file = 'flash/xml/ammap_data.xml';
        flashvars.preloader_color = '#F2F2E5';

        var params = {};

        var attributes = {};
        attributes.id = 'ammap';

        swfobject.embedSWF('flash/ammap.swf', 'flashContent', '770', '500', '9.0.0', false, flashvars, params, attributes);
    });
EOD;
?>
