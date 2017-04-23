<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
$this->titre = "À propos de " . $revue['TITRE'];
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./revue-<?php echo $revue["URL_REWRITING"]; ?>.htm">Revue</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./en-savoir-plus-sur-la-revue-<?php echo $revue["URL_REWRITING"]; ?>.htm">À propos de <?php echo $revue["TITRE"]; ?></a>
</div>

<div id="body-content">
    <div id="page_revue">
        <?php
        $modeIndex = 'apropos';
        require_once 'Vue/Revues/Blocs/indexRevue.php';
        ?>
        <hr class="grey">
        <div id="about_revue">
            <?php echo $revue["SAVOIR_PLUS2"]; ?>
        </div>
    </div>
</div>

<?php
    /* Ce qui suite ne concerne que les numéros de revues affiliés au CNRS */
    $revuesCNRS = explode(',', Configuration::get('revuesCNRS', ''));
    if (in_array($revue['ID_REVUE'], $revuesCNRS)) {
        $this->javascripts[] = <<<'EOD'
            $(function()  {
                $('#logos_footer').addClass('logo-plural').removeClass('logo-single');;
            });
EOD;
    }
?>

