<?php

if($gabarit == 'gabarit.php'){
$this->titre = 'My cart';
include (__DIR__ . '/../CommonBlocs/tabs.php');
echo '<div id="body-content">';
}

?>
<div id="free_text">
    <h1 class="main-title">Order Confirmation</h1>
    <hr class="grey">

    <div class="Clearfix"></div>
    <div id="wrapper_breadcrumb_cart">
        <ol id="breadcrumb_cart">
            <li>My cart</li>
            <li>Billing Address</li>
            <li>Payment method</li>
            <li>Payment</li>
            <li class="black_button">Get Access</li>
        </ol>
    </div>

    <p>Your order has been registered.<br/>
    You will receive in the next few minutes an e-mail confirmation.</p>
    <?php switch($typePanier){
        case 'article':
            echo '<a href="'.$linkPanier.'" class="acceder link_custom_generic">Go to the article</a>';
            break;        
        default :
            echo '<a href="./my_purchases.php" class="acceder link_custom_generic">Access all your purchases</a>';
            break;            
    }
    ?> 
    <br/>
    <a href="./my_cart.php" class="acceder link_custom_generic">Back to cart</a> 
    <br/>
    <a href="/" class="acceder link_custom_generic">Back to Homepage</a>

</div>

<?php

if($gabarit == 'gabarit.php'){
echo '</div>';
}

