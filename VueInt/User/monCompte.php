<?php 
$this->titre = 'My account';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">My account</a>
</div>

<div id="cnt_1">

    <div id="body-content">
        <div id="free_text">
            <h1 class="main-title">My Cairn.info account</h1>
            <p><em>The fields marked with an asterisk <span class="red">*</span> are required</em></p>

            <form id="accountNameForm" method="post" name="modifiercompte" action="my_account.php">

                <h2><strong>Connection details</strong></h2><br>
                <div class="wrapper">
                    <div class="blue_milk left w45">
                        <label class="prenom" for="prenom"> E-mail address <span class="red">*</span></label>
                        <div>
                            <span><?php echo $authInfos["U"]["EMAIL"]; ?></span>
                            <small class="right"><a class="link-underline" href="modification-adresse-email.php">Change</a></small>
                        </div>
                    </div>
                    <div class="blue_milk right w45">
                        <label class="prenom" for="nom"> Password <span class="red">*</span></label>
                        <div>
                            <span>&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</span>
                            <small class="right ml5"><a class="link-underline" href="mdp_oublie.php">Forgot your password?</a></small>
                            <small class="right mr5"><a class="link-underline" href="change-password.php">Change</a></small>                            
                        </div>
                    </div>
                </div>

                <h2><strong>Profile</strong></h2><br>

                <div class="wrapper">
                    <div class="blue_milk w45">
                        <label for="prenom">First Name <span class="red">*</span></label>
                        <span class="flash"></span> <input type="text" required="required" value="<?= $authInfos["U"]["PRENOM"]?>" id="prenom" name="prenom">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <div class="blue_milk w45">
                        <label for="nom">Last Name <span class="red">*</span></label> <span class="flash"></span> <input type="text" required="required" value="<?= $authInfos["U"]["NOM"]?>" id="nom" name="nom">
                    </div>
                </div>

                <div class="wrapper mt1">
                    <div class="blue_milk w45">
                        <label class="" for="select">Activity<span class="red">*</span></label>
                        <select class="w100" style="margin-top: 5px;" id="activity" name="activity" required="required">
                            <option value="" disabled="disabled" selected="selected">Choose...</option>
                            <?php 
                                $userActivities = array(0 => "", "undergraduate", "graduate", "postgraduate", "teacher and/or researcher", "archivist/librarian", "employee of the public service", "employee in the voluntary sector", "employee in the private sector", "profession", "unemployed", "retired", "other");
                                foreach ($userActivities as $key => $value) { ?>
                                <option
                                    value="<?= $key ?>"
                                    <?php if ($authInfos["U"]["PROFESSION"] == $key): ?>
                                        selected="selected"
                                    <?php endif; ?>
                                ><?= $value ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="wrapper mt1">
                    <div class="blue_milk w45">
                        <label for="pos_disc">Subject of interest</label>
                        <select class="w100" style="margin-top: 5px;" id="pos_disc" name="pos_disc" placeholder="Choisissez...">
                            <option value="" disabled="disabled" selected="selected">Choose...</option>
                            <?php
                            foreach($disciplines as $discipline){
                                echo '<option value="'.$discipline["POS_DISC"].'" '.($discipline["POS_DISC"]==$authInfos["U"]["POS_DISCU"]?'selected':'').'>'.$discipline["DISCIPLINE_EN"].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="wrapper mt1">
                    <div class="blue_milk w45">
                        <label class="" for="select">Country <span class="red">*</span></label>
                        <input type="text" required="required" value="<?= $authInfos["U"]["PAYS"] ?>" id="pays" name="pays">
                    </div>
                </div>

                <!--<h2><strong>Promotional code</strong></h2><br>
                <div class="wrapper">
                    Si vous avez reçu un code d’activation pour l’accès à une revue suite à un abonnement en tant que particulier, <a class="link-underline" href="./code-abonnement-papier.php">cliquez ici</a>.
                </div>-->

                <h2><strong>Terms of use</strong></h2><br>
                <input type="checkbox" id="checkshowall" name="checkshowall" <?= $authInfos["U"]["SHOWALL"]==1?'checked':''?>>
                <label for="checkshowall">
                    I want full access to the base Cairn.info whatever restrictions the institution from which I connect.
                </label>
                <br/>
                <input type="checkbox" id="checkpartenaires" name="checkpartenaires" <?= !empty($alerte)?"checked":"" ?>> 
                <label for="checkpartenaires">I agree to receive email information on the evolution of service Cairn.info and on the editorial activity of its partners.
                </label>

                <div class="wrapper mt1">
                    <small class=""><a class="link-underline" href="#" onclick="javascript:ajax.deleteUserModal()">Delete my account</a></small>
                    <input type="submit" value="Change my account" class="button right">               
                </div>
            </form>
        </div>
    </div>
</div>

<div style="display: none;" class="window_modal" id="modal_confirm_delete_user">
    <div class="info_modal" id="delete_user_message">
        <div id="delete_user_message">
            <h1>Are you sure?</h1>
            <p>Do you really want to delete your Cairn.info account?</p>
            <p>This action can not be undone and you will lose all your search history, your traffic history, your saved articles and access to the content you may have purchased.</p>
            <p>If you would rather unsubscribe from email alerts, <a class="link-underline" href="mes_alertes.php">click here</a>.</p>
            <div class="" style="margin-top: 30px;width: 100%;height: 40px;clear: both;">
                <a class="blue_button right bold"  href="javascript:void(0);" onclick="javascript:ajax.deleteUserModal('confirm')">Yes, delete my account</a>
                <a class="black_button right bold" style="margin-right: 10px;" href="javascript:void(0);" onclick="javascript:cairn.close_modal();">No, cancel</a>
            </div>
        </div>
        <div style="display: none;" id="delete_user_in_progress"><p>Deleting your account...</p></div>
        <div style="display: none;" id="delete_user_error"><p>Error when trying to delete the account</p></div>
        <div style="display: none;" id="delete_user_valide"><p>Account deleted. Redirecting...</p></div>
    </div>
</div>