<?php
$this->titre = "Connexion";
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div id="free_text">
    <h1 class="main-title">Accès Mon Cairn.info</h1>

    <?php
        // Message d'accès restreint
        if(isset($restricted)) {
            echo "<p class=\"alert alert-warning\">La page ou fonctionnalité à laquelle vous souhaitez avoir accès est réservée aux utilisateurs de Mon Cairn.info.</p>";
        }

        // Définition de l'action a effectuer en fonction du connectFrom 
        // "alert" fait référence à une action JS alertPopup(), "biblio.php" fait référence à a page
        if(in_array($connectFrom, ['alerte', 'biblio.php'])) {
            // Nettoyage et définition de la méthode JS a appeler
            $connectFromParts = explode(".", $connectFrom);            
            $methode          = ucfirst($connectFromParts[0]);
            // Définition de l'action
            $action = "ajax.connexion".$methode."()";
        }
        // Action par défaut
        else {
            /* Valeur BRUT : javascript:goToPaiementAfterIdentification(&quot;javascript:ajax.connexion<?= !$connectFrom ? ucfirst($connectFrom) : '' ?>(<?= $connectFrom == 'routeur' ? "&apos;".$fromString."&apos;" : '' ?>)&quot;) */
            $action  = "ajax.connexion";
            $action .= !$connectFrom ? ucfirst($connectFrom) : '';
            $action .= "(";
            $action .= $connectFrom == 'routeur' ? "&apos;".$fromString."&apos;" : '';
            $action .= ")";            
        }
    ?>

    <div class="wrapper">

        <!-- Identification de l'utilisateur -->
        <div class="w40 left">
            <h2><strong><small class="">Vous avez déjà un compte ?</small><br /><span class="title_little_blue">Identifiez-vous !</span></strong></h2>
            
            <form class="mt1" id="connectBlocForm" method="post" action="javascript:goToPaiementAfterIdentification(&quot;javascript:<?=$action;?>&quot;)">          
                <div class="blue_milk w100">
                    <label for="email_connexion">Adresse e-mail <span class="red">*</span></label>
                    <input type="email" value="<?= isset($email)?$email:'' ?>" id="email_connexion" name="email_connexion" class="prenom" required="required">
                </div>

                <div class="blue_milk mt1 w100">
                    <label for="password_connexion">Mot de passe <span class="red">*</span></label>
                    <input type="password" id="password_connexion" value="" class="prenom" name="password_connexion" required="required" <?= isset($email)?'autofocus':'' ?>>
                </div>

                <div class="mt1 w100" style="width: 410px;">
                    <label><input type="checkbox" id="remember" name="remember" value="1" checked="checked" /> Rester connecté</label>
                    <input class="button right" type="submit" value="Me connecter" id="valider" name="valider">
                </div>

                <div class="mt2 w100 clearfix" style="width: 410px;">
                    <a href="mdp_oublie.php" class="link-underline">Mot de passe oublié ?</a>
                </div>
                <?php 
                    // Hidden Fields Prix Total
                    if(isset($totalPrice)){
                        echo '<input type="hidden" id="totalPrice" value="'.$totalPrice.'"/>';
                    }
                    // Hidden Fields From Params
                    foreach($params as $kparam => $vparam) {
                        echo "<input type=\"hidden\" id=\"$kparam\" name=\"$kparam\" value=\"$vparam\" />";
                    }
                ?>
            </form>
        </div>

        <?php
            // Initialisation
            $connectFromTag = "";

            // Définition du tag
            if($connectFrom != "") {
                // Comportement spécifique
                $arrayTags = array("mes_recherches.php" => "recherches", "mon_historique.php" => "historique", "biblio.php" => "ajoutBiblio");
                if(array_key_exists($connectFrom, $arrayTags)) {
                    $connectFromTag = "?from=".$arrayTags[$connectFrom];
                }
                // Comportement par défaut
                else {
                    $connectFromTag .= "?from=".$connectFrom;
                } 
            }
        ?>
        <!-- Inscription sur cairn.info -->
        <div class="w45 right">
            <h2><strong><small class="">Vous n'avez pas encore de compte ?</small><br /><span class="title_little_blue">Inscrivez-vous gratuitement !</span></strong></h2>

            <p>
                Votre compte Mon Cairn.info vous permettra également de :
            </p>
            <p>
                - sauvegarder votre panier d'un appareil à un autre ;<br />
                - accéder à vos historiques de recherches et de consultations ;<br />
                - ou encore commander un crédit d'articles.
            </p>
            <p>
                <a style="margin-top: 14px;" class="button right" href="./creer_compte.php<?php echo $connectFromTag; ?><?= isset($email)?'&user='.$email:'' ?>">Créer mon compte gratuitement</a>
            </p>
        </div>
    </div>
</div>

<?php if($modal_activate_acces && $modal_activate_acces == "oui") { ?>
    <!-- ACCESS -->
    <div style="display: none;" class="window_modal" id="modal_activate_acces">
        <div class="info_modal"><a class="close_modal" href="javascript:void(0);" onclick="cairn.close_modal();"></a>        
            <h2>Activation de votre accès</h2>
            <p>
                <b>Attention !</b><br />
                Pour activer votre accès suite à votre abonnement papier, vous devez d'abord vous identifier à votre compte Mon Cairn.info ou en créer un gratuitement. Ce n'est qu'à l'étape suivante que vous pourrez entrer le code fourni par la revue ou son éditeur.
            </p>
            <p class="clearfix mt2">
                <span class="right"><a class="blue_button" href="javascript:void(0);" onclick="cairn.close_modal();">Continuer</a></span>
            </p>
        </div>
    </div>
    <?php $this->javascripts[] = '<script type="text/javascript">cairn.show_modal(\'#modal_activate_acces\');</script>'; ?>        
<?php } ?>


<?php if($connectFrom && $connectFrom == "alerte") { ?>
    <!-- ALERTES E-MAIL -->
    <div style="display: none;" class="window_modal" id="modal_alerte">
        <div class="info_modal"><a class="close_modal" href="javascript:void(0);" onclick="cairn.close_modal();"></a>        
            <h2>Alertes e-mail</h2>
            <p>
                <b>Attention !</b><br />
                Pour activer votre alerte email et recevoir le sommaire des nouveaux numéros vous devez d'abord vous identifier à votre compte Mon Cairn.info ou en créer un gratuitement.
            </p>
            <p class="clearfix mt2">
                <span class="right"><a class="blue_button" href="javascript:void(0);" onclick="cairn.close_modal();">Continuer</a></span>
            </p>
        </div>
    </div>
    <?php $this->javascripts[] = '<script type="text/javascript">cairn.show_modal(\'#modal_alerte\');</script>'; ?>        
<?php } ?>