<?php 
$this->titre = 'Mes demandes';
if($gabarit != 'gabaritAjax.php'){
    include (__DIR__ . '/../CommonBlocs/tabs.php');

?>
<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="./mes_demandes.php">Mes demandes</a>
</div>
<?php
}
?>
<div class="biblio mon-panier" id="body-content">
    <div class="list_articles">
       <h1 class="main-title">Mes demandes</h1>
                
        <?php if(!empty($artOuv)){ ?>
        <br>
        <h2 class="section">
            <span>Contributions d’ouvrages</span>
        </h2>
        <?php             
            $arrayForList = $artOuv;
            $currentPage = 'contrib';
            $arrayFieldsToDisplay = array('ID', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET_INST');
            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
        
        } ?>

        <?php if(!empty($artRev)){ ?>
        <br>
        <h2 class="section">
            <span>Articles de revues</span>
        </h2>
        <?php 
            $arrayForList = $artRev;
            $currentPage = 'contrib';
            $arrayFieldsToDisplay = array('ID', 'REVUE_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET_INST');
            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
        
        } ?>
    
        <?php if(!empty($artMag)){ ?>
        <br>
        <h2 class="section">
            <span>Articles de magazines</span>
        </h2>
        <?php 
            $arrayForList = $artMag;
            $currentPage = 'contrib';
            $arrayFieldsToDisplay = array('ID', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BASKET_INST');
            include (__DIR__ . '/../CommonBlocs/liste_1col.php');
        
        } ?>
    
    <div class="checkout-bottom-section">
        <?php if(!empty($artOuv) || !empty($artRev) || !empty($artMag)){ ?>
            <a class="continuer checkout-button" href="javascript:ajax.demandeBiblio()">Envoyer cette liste au bibliothécaire</a>
        <?php 
            if($returnLink != null){
                echo '<a class="payer checkin-button" href="./'.$returnLink.'">Retour à la page</a>';
            }
        } ?>               
    </div>

    <br>
</div>
