<?php
$this->titre = 'Publications de ' . $auteur["AUTEUR_PRENOM"] . " " . $auteur["AUTEUR_NOM"];

$typePub = ''; //Pas de tab sélectionné...
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="body-content">
    <h1 class="main-title">
        <span class="subinfos">Publications de</span>
        <span class="infos-subtitle"><strong><?php echo $auteur["AUTEUR_PRENOM"] . " " . $auteur["AUTEUR_NOM"]; ?></strong></span>
        <span class="subinfos">diffusées sur Cairn.info ou sur un portail partenaire</span>
    </h1>
    <div id="free_text" class="biblio">
        <div class="list_articles">
            <?php if ($ouvrages) { ?>
                <h2 class="section">Ouvrages</h2>
                <?php
                $arrayForList = $ouvrages;
                $arrayFieldsToDisplay = array('COLL_TITLE', 'AUTEURS_CONTRIBUTEURS', 'STATE_OUV');
                include (__DIR__ . '/../CommonBlocs/liste_1col.php');
            }
            ?>

            <?php if ($contribs) { ?>
                <h2 class="section">Contributions d'ouvrages</h2>
                <?php
                $currentPage = 'contrib';
                $arrayForList = $contribs;
                $arrayFieldsToDisplay = array('NUMERO_TITLE', 'AUTEURS_CONTRIBUTEURS', 'STATE');
                include (__DIR__ . '/../CommonBlocs/liste_1col.php');
            }
            ?>

            <?php if ($articlesRev) { ?>
                <h2 class="section">Articles de revues</h2>
                <?php
                $arrayForList = $articlesRev;
                $arrayFieldsToDisplay = array('REVUE_TITLE', 'AUTEURS_CONTRIBUTEURS', 'STATE');
                include (__DIR__ . '/../CommonBlocs/liste_1col.php');
            }
            ?>

            <?php if ($articlesMag) { ?>
                <h2 class="section">Articles de magazines</h2>
                <?php
                $arrayForList = $articlesMag;
                $arrayFieldsToDisplay = array('REVUE_TITLE', 'AUTEURS_CONTRIBUTEURS', 'STATE');
                include (__DIR__ . '/../CommonBlocs/liste_1col.php');
            }
            ?>
        </div>
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
