<?php
$this->titre = 'Publications de ' . $editeur["EDITEUR_NOM_EDITEUR"];

$typePub = ''; //Pas de tab sélectionné...
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="body-content">
    <h1 class="main-title">
        <span class="subinfos">Publications de</span>
        <?php if (trim($editeur['EDITEUR_WEBSITE']) !== ''): ?>
            <a class="infos-subtitle" href="<?= $editeur['EDITEUR_WEBSITE'] ?>" id="link-editeur">
                <strong><?php echo $editeur["EDITEUR_NOM_EDITEUR"]; ?></strong>
            </a>
        <?php else: ?>
            <span class="infos-subtitle">
                <strong><?php echo $editeur["EDITEUR_NOM_EDITEUR"]; ?></strong>
            </span>
        <?php endif ?>
        <span class="subinfos">diffusées sur Cairn.info</span>
    </h1>

    <div class="boxHome borderTop lescollections">
        <?php if ($countRev > 0) { ?>
            <h1 class="main-title">Revues (<?php echo $countRev; ?>)</h1>
            <?php
            $arrayForList = $revues;
            $arrayFieldsToDisplay = array('PERIODICITE', 'LIMITES');
            include (__DIR__ . '/../CommonBlocs/liste_2col.php');
        }
        ?>

        <?php if ($countColls > 0) { ?>
            <h1 class="main-title">Collections (<?php echo $countColls; ?>)</h1>
            <?php
            $currentPage = 'editeur';
            $arrayForList = $colls;
            $arrayFieldsToDisplay = array();
            include (__DIR__ . '/../CommonBlocs/liste_2col.php');
        }
        ?>

        <?php if ($countEncycs > 0) { ?>
            <h1 class="main-title">Encyclopedies de poche (<?php echo $countEncycs; ?>)</h1>
            <?php
            $arrayForList = $encycs;
            $arrayFieldsToDisplay = array('ISSN', 'ISSN_NUM');
            include (__DIR__ . '/../CommonBlocs/liste_2col.php');
        }
        ?>

        <?php if ($countMags > 0) { ?>
            <h1 class="main-title">Magazines (<?php echo $countMags; ?>)</h1>
            <?php
            $arrayForList = $mags;
            $arrayFieldsToDisplay = array('PERIODICITE', 'ISSN');
            include (__DIR__ . '/../CommonBlocs/liste_2col.php');
        }
        ?>
    </div>
</div>
<?php
include (__DIR__ . "/../CommonBlocs/invisible.php");
?>

<?php
$this->javascripts[] = <<<'EOD'
    $(campaigns.shs_03_2014);
EOD;
?>
