<?php
/**
 * Dedicated View [Coupled with the default method of the controler]
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = $revue['TITRE']; ?>

<?php
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>



<?php if ($revue['STATUT'] == 0): ?>
    <div class="danger backoffice article-desactivate">
        Ce magazine est actuellement désactivé.<br />
        Sur http://cairn.info, ce magazine <strong>n’apparaîtra pas</strong>. Il apparaît <strong>uniquement</strong> sur <?= Configuration::get('urlSite') ?>.
    </div>
<?php endif; ?>


<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./magazines.php">Magazines</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./magazine-<?php echo $revue["URL_REWRITING"]; ?>.htm"><?php echo $revue["TITRE"]; ?></a>
</div>

<div id="body-content">
    <div id="page_revue">
        <?php require_once 'Vue/Revues/Blocs/indexMagazine.php'; ?>

        <hr class="grey">

        <div class="magazines">
            <div class="nav_to_year">
                <?php
                    // Activation / Désactivation des boutons
                    $classPrev = "";
                    $classNext = "";

                    // Limite des magazines atteintes 
                    if($numero["NUMERO_ANNEE"] == $refAnnees["first"]+1) {$classPrev = "inactive";} 
                    if($numero["NUMERO_ANNEE"] == $refAnnees["last"]) {$classNext = "inactive";}
                ?>
                <a class="blue_button left <?php echo $classPrev; ?>" href="./magazine.php?ID_REVUE=<?php echo $revue["ID_REVUE"]; ?>&amp;ANNEE=<?php echo $refAnnees["current"] > ($refAnnees["first"] + 1) ? $refAnnees["current"] - 1 : $refAnnees["current"]; ?>#page_revue"> <span class="icon-arrow-white-left icon"></span> Année précédente </a>
                <a class="blue_button right <?php echo $classNext; ?>" href="./magazine.php?ID_REVUE=<?php echo $revue["ID_REVUE"]; ?>&amp;ANNEE=<?php echo $refAnnees["current"] < $refAnnees["last"] ? ($refAnnees["current"] == $refAnnees["first"] ? ($refAnnees["current"] + 2) : ($refAnnees["current"] + 1)) : $refAnnees["current"]; ?>#page_revue"> Année suivante <span class="icon-arrow-white-right icon"></span> </a>
            </div>

            <h2 class="magazine_year"><hr class="before"/><?php echo $numero["NUMERO_ANNEE"]; ?><hr class="after"/></h2>
            <div class="list_magazines">

                <?php
                    $count = 0;
                    $countAnnee = 0;
                    $bclAnnee = '';
                    
                    foreach ($numeros as $numero) {

                        if ($countAnnee % 4 == 0) { echo ($countAnnee > 0 ? '</div>' : '') . '<div class="grid-g grid-4 last_numeros-1">'; }
                        $count++;
                        $countAnnee++;
                ?>
                        <div class="grid-u-1-4 numero greybox_hover">
                            <a href="./magazine-<?php echo $revue["URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">
                                <img class="big_cover" src="/<?= $vign_path ?>/<?php echo $revue["ID_REVUE"]; ?>/<?php echo $numero["NUMERO_ID_NUMPUBLIE"]; ?>_L204.jpg" alt="Consulter <?php echo $revue["TITRE"]; ?> <?php echo $numero["NUMERO_ANNEE"]; ?>/<?php echo $numero["NUMERO_NUMERO"]; ?>">
                            </a>
                            <div class="subtitle_little_grey reference"><?php echo $numero["NUMERO_VOLUME"]; ?> <?php echo $numero["NUMERO_NUMERO"]; ?>/<?php echo $numero["NUMERO_ANNEE"]; ?></div>
                            <h2 class="title_medium_blue revue_title"><a href="magazine-<?php echo $revue["URL_REWRITING"]; ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm"><?php echo $numero["NUMERO_TITRE_ABREGE"]; ?></a></h2>
                        </div>
                <?php } ?>
            </div>

            <hr />
            <div class="nav_to_year nav_to_year_bottom">
                <?php
                    // Activation / Désactivation des boutons
                    $classPrev = "";
                    $classNext = "";

                    // Limite des magazines atteintes 
                    if($numero["NUMERO_ANNEE"] == $refAnnees["first"]+1) {$classPrev = "inactive";} 
                    if($numero["NUMERO_ANNEE"] == $refAnnees["last"]) {$classNext = "inactive";}
                ?>
                <a class="blue_button left <?php echo $classPrev; ?>" href="./magazine.php?ID_REVUE=<?php echo $revue["ID_REVUE"]; ?>&amp;ANNEE=<?php echo $refAnnees["current"] > ($refAnnees["first"] + 1) ? $refAnnees["current"] - 1 : $refAnnees["current"]; ?>#page_revue"> <span class="icon-arrow-white-left icon"></span> Année précédente </a>
                <a class="blue_button right <?php echo $classNext; ?>" href="./magazine.php?ID_REVUE=<?php echo $revue["ID_REVUE"]; ?>&amp;ANNEE=<?php echo $refAnnees["current"] < $refAnnees["last"] ? ($refAnnees["current"] == $refAnnees["first"] ? ($refAnnees["current"] + 2) : ($refAnnees["current"] + 1)) : $refAnnees["current"]; ?>#page_revue"> Année suivante <span class="icon-arrow-white-right icon"></span> </a>
            </div>
        </div>
    </div>
</div>
