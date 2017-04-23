<?php
$this->titre = "Connection";

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div id="free_text">
    <h1 class="main-title">Access to My Cairn.info</h1>

    <?php
        // Message d'accès restreint
        if(isset($restricted)) {
            echo "<p class=\"alert alert-warning\">The page or service you are tying to reach is restricted to users with a My Cairn.info account.</p>";
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
            <h2><strong><small class="">Do you already have an account?</small><br /><span class="title_little_blue">Sign in!</span></strong></h2>

            <form class="mt1" id="connectBlocForm" method="post" action="javascript:goToPaiementAfterIdentification(&quot;javascript:<?=$action;?>&quot;)">
            
                <div class="blue_milk w100">
                    <label for="email_connexion">Email address <span class="red">*</span></label>
                    <input type="email" value="<?= isset($email)?$email:'' ?>" id="email_connexion" name="email_connexion" class="prenom" required="required">
                </div>

                <div class="blue_milk mt1 w100">
                    <label for="password_connexion">Password <span class="red">*</span></label>
                    <input type="password" id="password_connexion" value="" class="prenom" name="password_connexion" required="required" <?= isset($email)?'autofocus':'' ?>>
                </div>

                <div class="mt1 w100" style="width: 410px;">
                    <label><input type="checkbox" id="remember" name="remember" value="1" checked="checked" /> Remember me</label>
                    <input class="button right" type="submit" value="Log in" id="valider" name="valider">
                </div>

                <div class="mt2 w100 clearfix" style="width: 410px;">
                    <a href="password_forgotten.php" class="link-underline">Password forgotten?</a>
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
            <h2><strong><small class="">Not registered yet?</small><br /><span class="title_little_blue">Sign up!</span></strong></h2>

            <p>
                A Cairn.info personal account will allow you to:
            </p>
            <p>
                - save your shopping cart across devices ;<br />
                - access your search and browse history ;<br />
                - or share your selection of articles.
            </p>
            <p>
                <a style="margin-top: 14px;" class="button right" href="./create_account.php<?php echo $connectFromTag; ?><?= isset($email)?'&user='.$email:'' ?>">Create my account</a>
            </p>
        </div>
    </div>
</div>