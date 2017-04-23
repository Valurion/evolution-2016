<?php $this->titre = "Collection " . $revue['TITRE']; ?>
<?php
$typePub = ($revue['TYPEPUB'] == 3 ? 'ouvrage' : 'encyclopedie');
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./ouvrages.php"><?php echo $revue['TYPEPUB'] == 3 ? 'Ouvrages' : 'Encyclopedies de poche'; ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./collections.php">Collections</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./collection.php?ID_REVUE=<?= $revue["ID_REVUE"] ?>">Collection</a>
</div>

<div id="body-content">
    <div id="page_revue">
        <?php require_once 'Vue/Revues/Blocs/indexColl.php'; ?>

        <br/><br/>

        <?php
        $arrayForList = $numeros;
        $arrayFieldsToDisplay = array('SOUS_TITRE', 'NOM_AUTEUR-ANNEE');
        $classForList = 'list_numeros';
        $prefix = 'NUMERO_';
        include (__DIR__ . '/../CommonBlocs/liste_2col.php');
        ?>
        <br/>
        <?php
        $nbPerPage = 20;
        $nbAround = 3;
        $urlPager = 'collection.php?ID_REVUE=' . $revue["ID_REVUE"];
        include (__DIR__ . '/../CommonBlocs/pager.php');
        ?>
    </div>
</div>
