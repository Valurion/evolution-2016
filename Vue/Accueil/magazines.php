<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "Magazines";
$typePub = 'magazine';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="body-content">
    <!-- NUMEROS RECENTS -->
    <div id="last_numeros">
        <h1 class="main-title"><?php echo count($revues) . ' magazines'; ?></h1>

        <div class="grid-g grid-5 last_numeros-1">
            <?php 
                // Liste des revues
                $i = 1;
                foreach ($revues as $revue) { ?>

                    <div class="grid-u-1-5 numero">
                        <a href="./magazine-<?= ($revue['URL_REWRITING']) . '.htm' ?>">
                            <img src="/<?= $vign_path ?>/<?= ($revue['ID_REVUE']) . '/' . ($revue['ID_NUMPUBLIE']) . '_L204.jpg' ?>" class="big_cover">
                        </a>
                        <h2 style="margin-top: 10px;" class="title_big_blue revue_title"><a href="./magazine-<?= ($revue['URL_REWRITING']) . '-' . $revue['ANNEE'] . '-' . $revue['NUMERO'] . '.htm' ?>"><?= ($revue['REVUE_TITRE']) ?></a></h2>
                        <div class="subtitle_little_grey reference"></div>
                    </div>

                <?php                     
                    if($i % 5 == 0) {
                        echo "</div><div class=\"grid-g grid-5 last_numeros-1 mt2\">";
                    }
                    $i++;
            } ?> 
        </div>
    </div>
</div>

<!-- Qu'est-ce que Cairn ? -->
<?php include_once(__DIR__.'/../CommonBlocs/questcequecairn.php'); ?>
