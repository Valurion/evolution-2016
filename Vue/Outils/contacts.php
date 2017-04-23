<?php
    include(__DIR__ . '../../../public_libs/captcha/shared.php');
    include(__DIR__ . '../../../public_libs/captcha/captcha_code.php');

    $captcha = new CaptchaCode();
    $code = str_encrypt($captcha->generateCode(5));
?>

<?php $this->titre = "Contacts"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="success_envoye" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Votre message a bien été envoyé</h2>
        <p>Nous nous efforcerons d'y répondre dans les plus brefs délais.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
        </div>
    </div>
</div>

<div id="error_envoye" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problème rencontré avant envoi du message</h2>
        <p>Une erreur est survenue lors de l'envoi du message, s'il vous plaît réessayer plus tard</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
        </div>
    </div>
</div>

<div id="error_empty_field" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problème rencontré avant envoi du message</h2>
        <p>Les champs suivis d'une astérisque sont obligatoires.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
        </div>
    </div>
</div>

<div id="error_invalid_email" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problème rencontrée avant envoi du message</h2>
        <p>L'adresse <span id="addr_email"></span> ne correspond pas à la syntaxe d'une adresse email.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
        </div>
    </div>
</div>

<div id="error_invalid_captcha" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problème rencontrée avant envoi du message</h2>
        <p>Le code captcha ne correspond pas à l'image.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
        </div>
    </div>
</div>


<!-- EOF MODALS -->


<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">Contacts</h1>

        <h2>Pour une aide à l'utilisation du site</h2>
        <p>
            Vous cherchez de l'aide pour l'utilisation d'une fonction particulière sur Cairn.info&#160;?
            Elle se trouve peut-être dans nos pages d'aide.
            <a href="http://aide.cairn.info" class="acceder">Consulter l'aide en ligne</a>
        </p>

        <?php if(isset($authInfos['I']['BRANDING']) && strpos($authInfos['I']['BRANDING'],'Contact')){?>
        <h2>Pour une assistance au sein de votre institution</h2>
        <?php
            echo $authInfos['I']['BRANDING'];
        } ?>

        <h2>Pour contacter Cairn.info par email</h2>
        <p>Utilisez le formulaire ci-dessous pour toute demande concernant nos services, votre message sera dirigé vers l'interlocuteur approprié.</p>

        <style>
            #form_contact .articleBody > div + div { margin-top : 1.5em;}
            #form_contact label { display : block;}
        </style>

        <form action="javascript:ajax.checkContactForm();" method="post" id="form_contact">
            <div class="articleBody">
                <div>
                    <div class="blue_milk w45">
                        <label class="prenom" for="prenom">Prénom <span class="red">*</span></label>
                        <input id="prenom" class="prenom" type="text" value="<?php echo $authInfos["U"]["PRENOM"]; ?>" required="required">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="prenom" for="nom">Nom <span class="red">*</span></label>
                        <input id="nom" class="prenom" type="text" value="<?php echo $authInfos["U"]["NOM"]; ?>" required="required">
                    </div>
                </div>
                <div class="blue_milk w97">
                    <label for="email">Adresse email <span class="red">*</span></label>
                    <input id="email" class="prenom" type="text" value="<?php echo $authInfos["U"]["EMAIL"]; ?>" required="required">
                </div>

                <div class="blue_milk w97">
                    <label for="institution">Institution ou société</label>
                    <input id="institution" class="prenom" type="text" value="<?php echo $authInfos["I"]["NOM"]; ?>" required="required">
                </div>
                
                
                <div class="blue_milk w97">
                    <label for="service">Quel service souhaitez-vous contactez chez Cairn.info&#160;? <span class="red">*</span></label>
                    <select class="email" id="service" required="required">
                        <option value="">Choisissez…</option>
                        <option value="<?php echo $serviceClients; ?>">Service clients (ex. : suivi de commande en ligne)</option>
                        <option value="<?php echo $supportTechnique; ?>">Support technique (ex. : aide à l'utilisation du site)</option>
                        <option value="<?php echo $administrateur; ?>">Administrateur web (ex. : questions sur le contenu du site)</option>
                        <option value="<?php echo $serviceCommercial; ?>">Service commercial (ex. : bouquets à destination des institutions)</option>
                        <option value="<?php echo $serviceAdministratif; ?>">Services administratifs (ex. : facturation et comptabilité)</option>
                    </select>
                </div>
                <div class="blue_milk w97">
                    <label for="message">Entrez votre message <span class="red">*</span></label>
                    <textarea class="custom_textarea_bm" rows=20 class="email" id="message" required="required"></textarea>
                </div>

                <div class="blue_milk w97">
                    <label for="telephone">Numéro de téléphone <small>(si vous souhaitez que l'on vous appelle en journée suite à votre demande)</small></label>
                    <input id="telephone" class="prenom" type="text" value="">
                </div>

                <div style="overflow:hidden;">
                    <div class="blue_milk w45 left">
                        <label for="formug">Recopier le code ci-contre<span class="red">*</span></label>
                        <input type="text" size="5" maxlength="7" class="inputform" id="formug" required="required">

                    </div>
                    <div class="blue_milk w45 right">
                        <input id="captchaCode" type="hidden" value="<?php echo $code; ?>">
                        <img style="text-align:center;" src="./public_libs/captcha/captcha_images.php?width=120&height=40&code=<?php echo $code?>">
                    </div>
                </div>
            </div>
            <div style="margin-top: 20px;"><input class="button right" type="submit" value="Envoyer mon message"></div>
        </form>
        <br />
        <h2>Pour contacter Cairn.info par courrier, fax ou téléphone</h2>
        <p>
            <strong>Cairn.info Belgique</strong>
        </p>
        <p>
            58/60, rue des Champs,<br />
            B-4020 LI&Egrave;GE<br />
            Tél. (32/0) 4 340 38 38.<br />
            TVA : BE 0873 856 568
        </p>
        <p>
            <strong>Cairn.info France</strong>
        </p>
        <p>
            26, Rue Édouard-Lockroy,<br />
            75011 PARIS<br />
            Tél. (33/0) 1 55 28 83 00. Fax (33/0) 1 55 28 35 33.<br />
            TVA : FR 12 48 77 04 942
        </p>
    </div>
</div>
