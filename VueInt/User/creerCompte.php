<?php 
$this->titre = "Sign up";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Sign up</a>
</div>
<div id="body-content">
    <div id="free_text">
        <h1 class="main-title"></h1>
        
        <p class="h_center">
            Already registered? <a class="yellow italic" href="connexion.php">Login here</a>
        </p>

        <hr class="w50">
        
        <h1 class="main-title">Create your Account now</h1>
        <p>
            If you do not already have a Cairn.info account, please enter the following information
            <br>
            <em>(the fields marked with an asterisk <span class="red">*</span> are required)</em>
        </p>

        <form id="creer_compte" action="#" method="POST" name="creecompte">

            <h2><strong>Connection details</strong></h2><br>

            <div class="wrapper">
                <div class="blue_milk w45">
                    <label for="email">E-mail address <span class="red">*</span></label>
                    <span class="flash "></span>
                    <input type="email" required="required" value="<?= $email ?>" id="email" name="email">
                </div>
            </div>
            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label for="cemail">E-mail address <small>(confirmation)</small> <span class="red">*</span></label>
                    <span class="flash "></span>
                    <input type="email" required="required" value="<?= $cemail ?>" id="cemail" name="cemail">
                </div>
            </div>

            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="mdp" for="mdp">Password <small>(6 to 20 characters and/or numbers)</small> <span class="red">*</span></label>
                    <input type="password" required="required" value="" id="mdp" name="mdp" class="mdp">
                </div>
            </div>
            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="cmdp" for="cmdp">Password <small>(confirmation)</small> <span class="red">*</span></label>
                    <input type="password" required="required" value="" id="cmdp" name="cmdp" class="cmdp">
                </div>
            </div>


            <h2><strong>Profile</strong></h2><br>

            <div class="wrapper">
                <div class="blue_milk w45">
                    <label for="prenom">First name <span class="red">*</span></label>
                    <span class="flash"></span> <input type="text" required="required" value="<?= $prenom ?>" id="prenom" name="prenom">
                </div>
            </div>
            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label for="nom">Last name <span class="red">*</span></label> <span class="flash"></span> <input type="text" required="required" value="<?= $nom ?>" id="nom" name="nom">
                </div>
            </div>

            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="" for="select">Activity<span class="red">*</span></label>
                    <select class="w100" style="margin-top: 5px;" id="activity" name="activity" required="required">
                        <option value="" disabled="disabled" selected="selected">Choose...</option>
                        <?php foreach ($userActivities as $activity): ?>
                            <option
                                value="<?= $activity["POS_PROF"] ?>"
                                <?php if (isset($posProf) && $posProf == $activity["POS_PROF"]): ?>
                                    selected="selected"
                                <?php endif; ?>
                            ><?= $activity["PROFESSION_EN"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label for="pos_disc">Subject of interest</label>
                    <select class="w100" style="margin-top: 5px;" id="pos_disc" name="pos_disc" placeholder="Choisissez...">
                        <option value="" disabled="disabled" selected="selected">Choose...</option>
                        <?php foreach ($disciplines as $discipline): ?>
                            <option
                                value="<?= $discipline["POS_DISC"] ?>"
                                <?php if (isset($posDisc) && $posDisc == $discipline["POS_DISC"]): ?>
                                    selected="selected"
                                <?php endif; ?>
                            ><?= $discipline["DISCIPLINE_EN"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="wrapper mt1">
                <div class="blue_milk w45">
                    <label class="" for="select">Country <span class="red">*</span></label>
                    <input type="text" required="required" value="<?= $pays ?>" id="pays" name="pays">
                </div>
            </div>

            <!--<h2><strong>Promotional code</strong></h2><br>
            <div class="wrapper">
                Si vous avez reçu un code d’activation d'accès à une revue suite à un abonnement en tant que particulier, vous pourrez l'entrer à la page suivante après avoir créé votre compte.
            </div>-->

            <h2><strong>Terms of use</strong></h2><br>
            <p>
                <label for="accept_conditions">
                    <input type="checkbox" required="required" id="accept_conditions" name="checkconditions">
                    I accept the <a class="" target="_blank" href="./conditions.php"><span style="text-decoration: underline;">terms of use of Cairn.info</span></a> and 
                    the use of cookies for statistical analysis <span class="red">*</span>
                </label>
            </p>
            <p>
                <label for="accept_partenaires">
                    <input type="checkbox" id="accept_partenaires" name="checkpartenaires">
                    I agree to receive email information on the evolution of services Cairn.info and on the editorial activity of its partners.
                </label>
            </p>
            <?php
            if($from != ''){
                echo '<input type="hidden" value="'.$from.'" />';
            }
            ?>
            <input type="submit" value="Create my account" class="button right">

        </form>
        <br>
    </div>
</div>