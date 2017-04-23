<?php
$this->titre = "Création de compte";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Mon compte</a>
</div>
<div id="body-content">
    <div id="free_text">
        <h1 class="main-title"></h1>
        <a target="_blank" href="http://aide.cairn.info/le-compte-personnel/">
            <span style="bottom: 1em;" class="question-mark">
            <span class="tooltip">En savoir plus sur Mon Cairn.info</span>
            </span>
        </a>
        <p class="h_center">
            Déjà enregistré ? <a class="yellow italic" href="connexion.php">Connectez-vous</a>
        </p>

        <hr class="w50">

        <h1 class="main-title">Créer un compte Cairn.info</h1>
        <p>
            Si vous ne disposez pas encore d'un compte Cairn.info, veuillez
            entrer les informations suivantes<br>
            <em>(les champs suivis d'un astérisque <span class="red">*</span>
                sont obligatoires)
            </em>
        </p>

        <form id="creer_compte" action="#" method="POST" name="creecompte">

            <h2><strong>Données de connexion</strong></h2><br>

            <div class="wrapper">
                <div class="blue_milk w45">
                    <label for="email">Adresse e-mail <span class="red">*</span></label>
                    <span class="flash "></span>
                    <input type="email" required="required" value="<?= $email ?>" id="email" name="email">
                </div>
            </div>
            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label for="cemail">Adresse e-mail <small>(confirmation)</small> <span class="red">*</span></label>
                    <span class="flash "></span>
                    <input type="email" required="required" value="<?= $cemail ?>" id="cemail" name="cemail">
                </div>
            </div>

            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="mdp" for="mdp">Mot de passe <small>(6 à 20 caractères et/ou chiffres)</small> <span class="red">*</span></label>
                    <input type="password" required="required" value="" id="mdp" name="mdp" class="mdp">
                </div>
            </div>
            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="cmdp" for="cmdp">Mot de passe <small>(confirmation)</small> <span class="red">*</span></label>
                    <input type="password" required="required" value="" id="cmdp" name="cmdp" class="cmdp">
                </div>
            </div>

            <h2><strong>Profil</strong></h2><br>

            <div class="wrapper">
                <div class="blue_milk w45">
                    <label for="prenom">Prénom <span class="red">*</span></label>
                    <span class="flash"></span> <input type="text" required="required" value="<?= $prenom ?>" id="prenom" name="prenom">
                </div>
            </div>
            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label for="nom">Nom <span class="red">*</span></label> <span class="flash"></span> <input type="text" required="required" value="<?= $nom ?>" id="nom" name="nom">
                </div>
            </div>

            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="" for="select">Activité<span class="red">*</span></label>
                    <select class="w100" style="margin-top: 5px;" id="activity" name="activity" required="required">
                        <option value="" disabled="disabled" selected="selected">Choisissez...</option>
                        <?php foreach ($userActivities as $activity): ?>
                            <option
                                value="<?= $activity["POS_PROF"] ?>"
                                <?php if (isset($posProf) && $posProf == $activity["POS_PROF"]): ?>
                                    selected="selected"
                                <?php endif; ?>
                            ><?= $activity["PROFESSION"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label for="pos_disc">Discipline de prédilection</label>
                    <select class="w100" style="margin-top: 5px;" id="pos_disc" name="pos_disc" placeholder="Choisissez...">
                        <option value="" disabled="disabled" selected="selected">Choisissez...</option>
                        <?php foreach ($disciplines as $discipline): ?>
                            <option
                                value="<?= $discipline["POS_DISC"] ?>"
                                <?php if (isset($posDisc) && $posDisc == $discipline["POS_DISC"]): ?>
                                    selected="selected"
                                <?php endif; ?>
                            ><?= $discipline["DISCIPLINE"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="" for="select">Pays <span class="red">*</span></label>
                    <select class="w100" style="margin-top: 5px;" id="pays" name="pays">
                        <?php
                            foreach($listePays as $key => $value) {
                                if($value == "------------") {echo "<option value=\"$value\" disabled=\"disabled\">".$value."</option>";}
                                else if($pays == $value) {echo "<option value=\"$value\" selected=\"selected\">".$value."</option>";}
                                else {echo "<option value=\"$value\">".$value."</option>";}
                            }
                        ?>
                    </select>
                </div>
            </div>

            <h2><strong>Activation d’accès à une revue</strong></h2><br>
            <div class="wrapper">
                Si vous avez reçu un code d’activation d'accès à une revue suite à un abonnement en tant que particulier, vous pourrez l'entrer à la page suivante après avoir créé votre compte.
            </div>

            <h2><strong>Conditions d'utilisation</strong></h2>
            <p>
                <label for="accept_conditions">
                    <input type="checkbox" required="required" id="accept_conditions" name="checkconditions">
                    J'accepte les <a class="" target="_blank" href="./conditions.php"><span style="text-decoration: underline;">conditions d'utilisation de Cairn.info</span></a> et 
                    la réception de cookies à des fins de statistiques <span class="red">*</span>
                </label>
            </p>
            <p>
                <label for="accept_partenaires">
                    <input type="checkbox" id="accept_partenaires" name="accept_partenaires">
                    J'accepte de recevoir par email des informations sur l'évolution des services de Cairn.info ainsi que sur 
                    l'activité éditoriale de ses partenaires.
                </label>
            </p>
            <?php
            if($from != ''){
                echo '<input type="hidden" value="'.$from.'" />';
            }
            ?>
            <input type="submit" value="Créer mon compte" class="button right">

        </form>
        <br>
    </div>
</div>
