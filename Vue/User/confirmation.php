<?php 
$this->titre = "Confirmation de création de compte";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Confirmation</a>
</div>
<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">Confirmation de création de compte</h1>
        <p>
            <b>Bienvenue !</b>
        </p>
        <p>
            Votre compte Cairn.info est désormais actif. Vous allez recevoir un email de confirmation.
        </p>
        <?php switch($from){
            case "demandeBiblio":
                echo "<p>
                        <a class=\"button right\" href=\"javascript:ajax.demandeBiblio()\">Poursuivre ma demande <span class=\"icon-arrow-black-right icon right\"></span></a>
                      </p>";
                break;
            case "panierAchat":
                echo "<p>
                        <a class=\"button right\" href=\"./mon_panier.php\">Poursuivre mon achat <span class=\"icon-arrow-black-right icon right\"></span></a>
                      </p>";
                break;
            case "codeAboPapier":
                $liste = "";
                foreach ($revues as $revue) {
                    $liste .= "<option value=\"".$revue['ID_REVUE']."\">".$revue['TITRE']."</option>";
                }

                echo "<p>Vous pouvez désormais activer l'accès à votre revue :</p>";
                echo "<form id=\"ajoutalertes\" method=\"post\" name=\"ajoutalertes\" action='code-abonnement-papier.php'>
                        <div class=\"wrapper\">
                            <div class=\"blue_milk left w45\">
                                <label for=\"ID_REVUE\">Revue</label>
                                <select style=\"width: 100%;\" name=\"ID_REVUE\" id=\"ID_REVUE\">$liste</select>
                            </div>
                            <div class=\"blue_milk right w45\">
                                <label for=\"code_abonne\">Code d'abonné</label>
                                <input type=\"text\" name=\"code_abonne\" id=\"code_abonne\" value=\"\">
                            </div>
                        </div>
                        <div class=\"wrapper mt1\">
                            <a class=\"link-underline\" href=\"./\">Plus tard</a>
                            <input type='submit' class=\"button right\" value=\"Activer mon accès\">
                        </div>
                      </form>";
                break;
            default:
                echo "<p>
                        Si vous avez reçu un code d’activation pour l’accès à une revue suite à un abonnement en tant que particulier, <a class=\"acceder link-underline\" href=\"./code-abonnement-papier.php\">cliquez ici</a>.<br />
                        <a class=\"link-underline\" href=\"./\">Continuer vers la page d'accueil</a>
                      </p>";
        }?>
        <p>&nbsp;</p>
    </div>
</div>
