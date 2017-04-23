<?php
$this->titre = 'Publications from ' . $editeur["EDITEUR_NOM_EDITEUR"];

$typePub = ''; //Pas de tab sélectionné...
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="body-content">
    <h1 class="main-title">
        <span class="subinfos">Publications from</span>
        <?php if (trim($editeur['EDITEUR_WEBSITE']) !== ''): ?>
            <a class="infos-subtitle" href="<?= $editeur['EDITEUR_WEBSITE'] ?>">
                <strong><?php echo $editeur["EDITEUR_NOM_EDITEUR"]; ?></strong>
            </a>
        <?php else: ?>
            <span class="infos-subtitle">
                <strong><?php echo $editeur["EDITEUR_NOM_EDITEUR"]; ?></strong>
            </span>
        <?php endif ?>
        <span class="subinfos">proposed on Cairn International Edition</span>
    </h1>

    <div class="boxHome borderTop lescollections">
        <?php if ($countRev > 0) { ?>
            <!--h1 class="main-title">Journals (<?php echo $countRev; ?>)</h1-->
            <?php
            $arrayForList = $revues;
            $arrayFieldsToDisplay = array('PERIODICITE_EN', 'ISSN', 'ISSN_NUM');
            include (__DIR__ . '/../CommonBlocs/liste_2col.php');
        }
        ?>
    </div>
</div>

