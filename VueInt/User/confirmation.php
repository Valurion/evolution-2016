<?php 
$this->titre = "Confirmation of account creation";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Confirmation</a>
</div>
<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">Confirmation of account creation</h1>
        <p>
            <b>Welcome!</b>
        </p>
        <p>
            Your Cairn.info account is now active. You will receive a confirmation email shortly.
        </p> 
        <?php switch($from){
            case "demandeBiblio":
                echo "<p>
                        <a class=\"button right\" href=\"javascript:ajax.demandeBiblio()\">Continue to my list of articles <span class=\"icon-arrow-black-right icon right\"></span></a>
                      </p>";
                break;
            case "panierAchat":
                echo "<p>
                        <a class=\"button right\" href=\"./mon_panier.php\">Continue my purchase<span class=\"icon-arrow-black-right icon right\"></span></a>
                      </p>";
                break;
            default:
                echo "<p>
                        <a class=\"link-underline\" href=\"./\">Back to homepage</a>
                      </p>";
        }?>
        <p>&nbsp;</p>
    </div>
</div>
