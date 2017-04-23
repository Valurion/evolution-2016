<?php
$this->titre = 'Mon compte';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Mon compte</a>
</div>

<div id="cnt_1">

    <div id="body-content">
        <div id="free_text">
            <h1 class="main-title">Mon compte Cairn.info</h1>
            <p><em>Les champs suivis d'un astérisque <span class="red">*</span> sont obligatoires</em></p>

            <form id="accountNameForm" method="post" name="modifiercompte" action="mon_compte.php">

                <h2><strong>Données de connexion</strong></h2><br>
                <div class="wrapper">
                    <div class="blue_milk left w45">
                        <label class="prenom" for="prenom"> Adresse e-mail <span class="red">*</span></label>
                        <div>
                            <span><?php echo $authInfos["U"]["EMAIL"]; ?></span>
                            <small class="right"><a class="link-underline" href="modification-adresse-email.php">Changer</a></small>
                        </div>
                    </div>
                    <div class="blue_milk right w45">
                        <label class="prenom" for="nom"> Mot de passe <span class="red">*</span></label>
                        <div>
                            <span>&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</span>
                            <small class="right ml5"><a class="link-underline" href="mdp_oublie.php">Oublié ?</a></small>
                            <small class="right mr5"><a class="link-underline" href="modification-de-mot-de-passe.php">Changer</a></small>                            
                        </div>
                    </div>
                </div>

                <h2><strong>Profil</strong></h2><br>
                <div class="wrapper">
                    <div class="blue_milk w45">
                        <label class="prenom" for="prenom">
                            Prénom <span class="red">*</span>
                        </label>
                        <input type="text" required="required" value="<?= $authInfos["U"]["PRENOM"]?>" id="prenom" name="prenom" class="prenom">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <div class="blue_milk w45">
                        <label class="prenom" for="nom">Nom <span class="red">*</span></label>
                        <input type="text" required="required" value="<?= $authInfos["U"]["NOM"]?>" id="nom" name="nom" class="prenom">
                    </div>
                </div>

                <div class="wrapper mt1">
                    <div class="blue_milk w45">
                        <label for="activity">Activité <span class="red">*</span></label>
                        <select class="w100" style="margin-top: 5px;" id="activity" name="activity" required="required">
                            <option value="" disabled="disabled" selected="selected">Choisissez...</option>
                            <?php foreach ($userActivities as $activity): ?>
                                <option
                                    value="<?= $activity["POS_PROF"] ?>"
                                    <?php if (isset($authInfos["U"]["PROFESSION"]) && $authInfos["U"]["PROFESSION"] == $activity["POS_PROF"]): ?>
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
                        <select class="w100" style="margin-top: 5px;" id="pos_disc" name="pos_disc">
                            <option value="0">Choisissez…</option>
                            <?php
                            foreach($disciplines as $discipline){
                                echo '<option value="'.$discipline["POS_DISC"].'" '.($discipline["POS_DISC"]==$authInfos["U"]["POS_DISCU"]?'selected':'').'>'.$discipline["DISCIPLINE"].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="wrapper mt1">
                    <div class="blue_milk w45">
                        <label class="" for="select">Pays <span class="red">*</span></label>
                        <select class="w100" style="margin-top: 5px;" id="pays" name="pays" required="required">
                            <?php
                                foreach($listePays as $key => $value) {
                                    if($value == "------------") {echo "<option value=\"$value\" disabled=\"disabled\">".$value."</option>";}
                                    else if($value == "-nc-") {echo "<option value=\"\">".$value."</option>";}
                                    else if($authInfos["U"]["PAYS"] == $value) {echo "<option value=\"$value\" selected=\"selected\">".$value."</option>";}
                                    else {echo "<option value=\"$value\">".$value."</option>";}
                                }
                            ?>
                        </select>
                    </div>
                </div>
            

                <h2><strong>Activation d’accès à une revue</strong></h2><br>
                <div class="wrapper">
                    Si vous avez reçu un code d’activation pour l’accès à une revue suite à un abonnement en tant que particulier, <a class="link-underline" href="./code-abonnement-papier.php">cliquez ici</a>.
                </div>
            

          
                <h2><strong>Conditions d'utilisation</strong></h2><br>
                <input type="checkbox" id="checkshowall" name="checkshowall" <?= $authInfos["U"]["SHOWALL"]==1?'checked':''?>>
                <label for="checkshowall">
                    Je veux un accès complet à la base Cairn.info, quelles que soient les restrictions de l'institution à partir de laquelle je me connecte.
                </label>
                <br/>
                <input type="checkbox" id="checkpartenaires" name="checkpartenaires" <?= !empty($alerte)?"checked":"" ?>>
                <label for="checkpartenaires">J'accepte de recevoir par email des informations sur l'évolution des services de Cairn.info ainsi que sur l'activité éditoriale de ses partenaires.
                </label>
                

                <div class="wrapper mt1">                    
                    <small class=""><a class="link-underline" href="#" onclick="javascript:ajax.deleteUserModal()">Supprimer mon compte</a></small>
                    <input type="submit" value="Modifier mon compte" class="button right">
                </div>
            </form>
        </div>
    </div>
</div>


<div style="display: none;" class="window_modal" id="modal_confirm_delete_user">
    <div class="info_modal">
        <div id="delete_user_message">
            <h1>Attention !</h1>
            <p>Voulez-vous réellement supprimer votre compte Cairn.info ?</p>
            <p>Cette action est <strong>irréversible</strong> et <strong>vous perdrez</strong> votre historique de recherche, votre historique de consultations, votre bibliographie et les accès en ligne vers vos achats éventuels.</p>
            <p>Si vous voulez plutôt désactiver des alertes emails, <a class="link-underline" href="mes_alertes.php">cliquez ici</a>.</p>
            <div class="" style="margin-top: 30px;width: 100%;height: 40px;clear: both;">
                <a class="blue_button right bold"  href="javascript:void(0);" onclick="javascript:ajax.deleteUserModal('confirm')">Oui, supprimer mon compte</a>
                <a class="black_button right bold" style="margin-right: 10px;" href="javascript:void(0);" onclick="javascript:cairn.close_modal();">Non, annuler</a>
            </div>
        </div>
        <div style="display: none;" id="delete_user_in_progress"><p>Suppression de votre compte en cours...</p></div>
        <div style="display: none;" id="delete_user_error"><p>Erreur lors de la suppression de votre compte...</p></div>
        <div style="display: none;" id="delete_user_valide"><p>Suppression terminée. Redirection en cours...</p></div>
    </div>    
</div>
