<?php
$this->titre = $auteur["AUTEUR_PRENOM"] . " " . $auteur["AUTEUR_NOM"]. "'s publications";

$typePub = ''; //Pas de tab sélectionné...
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="body-content">
    <h1 class="main-title">
        <span class="subinfos">Articles from</span>
        <span class="infos-subtitle"><strong><?php echo $auteur["AUTEUR_PRENOM"] . " " . $auteur["AUTEUR_NOM"]; ?></strong></span>
        <span class="subinfos">Published on Cairn International</span>
    </h1>
    <div id="free_text" class="biblio">
        <div class="list_articles">
            <?php if ($articlesRev) { ?>    
                <h2 class="section">Journal articles</h2>
                <?php
                $arrayForList = $articlesRev;
                $arrayFieldsToDisplay = array('REVUE_TITLE', 'STATE_INTER');
                include (__DIR__ . '/../CommonBlocs/liste_1col.php');
            }
            ?>

        </div>
    </div>
 
</div>
<?php
include (__DIR__ . "/../CommonBlocs/invisible.php");
?>