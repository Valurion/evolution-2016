<?php
$this->titre = 'Mon panier';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="body-content">
<div id="free_text">
    <h1 class="main-title">Commande annulée</h1>
    <hr class="grey">

    <div class="Clearfix"></div>
    <div id="wrapper_breadcrumb_cart">
        <ol id="breadcrumb_cart">
            <li>Mon panier</li>
            <li>Coordonnées</li>
            <li>Methode de paiement</li>
            <li class="black_button">Paiement</li>
            <li>Accès</li>
        </ol>
    </div>

    <br />
    
    <b>Votre commande est annulée.</b>
    <p>
            Votre panier d'achats reste toutefois rempli pour une tentative
            ultérieure. <br>
            <a class="acceder" href="./mon_panier.php">Retour à votre panier</a>
            <br>Si vous rencontrez une difficulté particulière pour effectuer
            un réglement par carte de crédit, merci de <a href="./contact.php">
                    contacter notre support utilisateurs</a>.
    </p>
</div>
</div>
   
