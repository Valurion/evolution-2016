<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
$this->titre = "About " . $revue['TITRE'];
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="breadcrump">
    <a class="inactive" href="./">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="disc-<?= $curDiscipline?>.htm"><?= $filterDiscipline?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./journal-<?php echo $revue["URL_REWRITING_EN"]; ?>.htm">Journal</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./about-the-journal-<?php echo $revue["URL_REWRITING_EN"]; ?>.htm">About <?php echo $revue["TITRE"]; ?></a>
</div>

<div id="body-content">
    <div id="page_revue">
        <?php
        $modeIndex = 'apropos';
        require_once __DIR__.'/Blocs/indexRevue.php';
        ?>
        <hr class="grey">
        <section id="about_revue" class="desc Clearfix desc-about-journal">
            <div><h3>Publisher</h3></div>
            <article>
                    <p><strong><?php echo $revue["NOM_EDITEUR"]; ?></strong></p>
            </article>
        </section>
        <div id="about_revue">
            <?php echo $revue["SAVOIR_PLUS2_EN"]; ?>
        </div>
    </div>
</div>

<?php
    /*
        La condition suivante ne concerne que les revues qui sont supportés par le CNRS
        Pour éviter de boucler deux fois, on récupère ici l'éventualité qu'un numéro soit affilié au CNRS (selon le TYPE_NUMPUBLIE).
        Il n'y a pas, à l'heure actuelle, de possibilité pour savoir si une revue est concernée par le partenariat avec le CNRS.
        Pour l'affichage de la mention, voir en fin de fichier

        /!\ Le code qui suit est relativement fragile. Si on passe la liste des numéros sur deux pages (comme par exemple sur l'ancien cairn-int), ça ne marchera plus.
        J'avoue ne pas avoir le temps de réfléchir à une solution plus pérenne pour le moment, désolé.

        le 21/04/2017 :
        Sur cette page nous n'avons pas les données des numéros, une méthode a été ajoutée pour pouvoir récupérer le TYPE_NUMPUBLIE de la revue. 
        C'est donc exceptionnellement sur la variable $revue qu'on récupère l'information 
    */
    if (($revue['NUMERO_TYPE_NUMPUBLIE'] === '5')) {
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