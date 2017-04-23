<?php ?>

<div id="mainContent">
    <a name="top"></a>
    <div id="cnt_1">
        <div id="breadcrump">
            <a class="inactive" href="./">Accueil</a> 
            <span class="icon-breadcrump-arrow icon"></span>
            <a class="inactive" href="./mon_panier.php">Crédit Activation d'accès</a>
            <span class="icon-breadcrump-arrow icon"></span>
            <a href="#">Connexion</a>
        </div>
        <div id="body-content">
            <div id="free_text">
                <h1 class="main-title">S'identifier</h1>

                <p>Si vous disposez d'un compte Cairn.info, identifiez-vous&nbsp;:</p>
                <form id="indentificationFrom" method="post" action="connexion.php">
                    <input type="hidden" name="PAGE" value="CONNEXION"> <input type="hidden" name="OPERATE" value="CONNECT">



                    <div class="blue_milk w40">
                        <label for="email">Votre adresse email <span>*</span></label> <input type="email" value="" id="email" name="LOG" class="prenom" required="required">
                    </div>&nbsp;&nbsp;
                    <div class="blue_milk w40">
                        <label for="mdp">Votre mot de passe <span>*</span></label> <input type="password" id="mdp" value="" class="prenom" name="PWD">
                    </div>&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="submit" value=" Valider " id="valider" name="valider" class="button">
                    <br><br>
                    <a href="mdp_oublie.php" class="link_custom">Mot de passe oublié</a>							

                </form>
                <br>

                <p>Si vous ne disposez pas de compte Cairn.info, créez-en un&nbsp;: <a class="acceder link_custom_generic" href="./creer_compte.php">Créer mon compte</a></p>


            </div>
        </div>
    </div>
    
</div>

