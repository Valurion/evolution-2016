<?php

if($gabarit == 'gabarit.php'){
$this->titre = 'Mon panier';
include (__DIR__ . '/../CommonBlocs/tabs.php');
echo '<div id="body-content">';
}

?>
<div id="free_text">
    <h1 class="main-title">Confirmation d'achat</h1>
    <hr class="grey">

    <div class="Clearfix"></div>
    <div id="wrapper_breadcrumb_cart">
        <ol id="breadcrumb_cart">
            <li>Mon panier</li>
            <li>Coordonnées</li>
            <li>Methode de paiement</li>
            <li>Paiement</li>
            <li class="black_button">Accès</li>
        </ol>
    </div>

    <p>Votre commande a bien été enregistrée.<br/>
        Vous allez recevoir dans les prochaines minutes un e-mail de
        confirmation.</p>
    <?php switch($typePanier){
        case 'article':
        case 'articleMag':
            echo '<a href="'.$linkPanier.'" class="acceder link_custom_generic">Accéder à l\'article</a>';
            break;
        case 'numero':
            echo '<a href="'.$linkPanier.'" class="acceder link_custom_generic">Accéder au numéro</a>';
            break;
        case 'chapitre':
            echo '<a href="'.$linkPanier.'" class="acceder link_custom_generic">Accéder au chapitre</a>';
            break;
        case 'abo':
            echo '<a href="'.$linkPanier.'" class="acceder link_custom_generic">Accéder à la revue</a>';
            break;
        case 'credit':
            echo '<a href="./mon_credit.php" class="acceder link_custom_generic">Accéder à votre crédit d\'articles</a>';
            break;
        default :
            echo '<a href="./mes_achats.php" class="acceder link_custom_generic">Accéder à tous vos achats</a>';
            break;            
    }
    ?> 
    <br/>
    <a href="./mon_panier.php" class="acceder link_custom_generic">Retour à votre panier</a> 
    <br/>
    <a href="/" class="acceder link_custom_generic">Retour à l'accueil</a>

</div>

<?php

if($gabarit == 'gabarit.php'){
echo '</div>';
}

