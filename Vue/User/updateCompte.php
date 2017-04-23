<?php ?>
<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">Modification d'email ou de mot de passe</h1>
        <form method="POST" id="modifiercompte" name="modifiercompte" action="modif_email.php">
            <fieldset>
                <legend>Mes informations actuelles</legend>
                <div class="wrapper">
                    <div class="blue_milk left w45">
                        <label class="email" for="email">Email <span class="red">*</span></label>
                        <input type="email" required="required" value="" id="email" name="email" class="email">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="mdp" for="mdp">Mot de passe <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp" name="mdp" class="mdp">
                    </div>
                </div>
            </fieldset>

            <fieldset class="mt2">
                <legend>Mes nouvelles informations</legend>
                <div class="wrapper">
                    <div class="blue_milk left w45">
                        <label class="email2" for="email2">Nouvel email <span class="red">*</span></label>
                        <input type="email2" required="required" value="" id="email2" name="email2" class="email2">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <div class="blue_milk left w45">
                        <label class="mdp2" for="mdp2">Nouveau mot de passe <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp2" name="mdp2" class="mdp2">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="mdp3" for="mdp3">Confirmation de ce mot de passe <span class="red">*</span></label>
                        <input type="password" required="required" value="" id="mdp3" name="mdp3" class="mdp3">
                    </div>
                </div>
            </fieldset>

            <div class="wrapper mt1">
                <button class="blue_button right bold">Modifier mon compte</button>
            </div>

        </form>
    </div>
</div>

