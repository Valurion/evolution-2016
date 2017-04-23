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
        <h2>Your message has been sent</h2>
        <p>We will try to respond as soon as possible.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Close</span>
        </div>
    </div>
</div>

<div id="error_envoye" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problem found before sending the message</h2>
        <p>An error has occurred when sending the message, please try again later.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Close</span>
        </div>
    </div>
</div>

<div id="error_empty_field" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problem found before sending the message</h2>
        <p>Fields marked with an asterisk are required.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Close</span>
        </div>
    </div>
</div>

<div id="error_invalid_email" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problem found before sending the message</h2>
        <p>The address <span id="addr_email"></span> does not match with the syntax of a mail address.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Close</span>
        </div>
    </div>
</div>

<div id="error_invalid_captcha" class="window_modal" style="display:none;">
    <div class="info_modal">
        <h2>Problem found before sending the message</h2>
        <p>The captcha code does not match the picture.</p>
        <div class="buttons">
            <span class="blue_button ok" onclick="cairn.close_modal()">Close</span>
        </div>
    </div>
</div>


<!-- EOF MODALS -->


<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">Contact</h1>

        <h2>Help using our website</h2>
        <p>
            Do you need helping navigating Cairn International Edition? You may find an answer <a class="inline-link" href="./help.php" class="acceder">in our FAQ</a>
        </p>

        <?php if(isset($authInfos['I']['BRANDING']) && strpos($authInfos['I']['BRANDING'],'Contact')){?>
        <h2>For any further help, please inquire in your institution</h2>
        <?php
            echo $authInfos['I']['BRANDING'];
        } ?>

        <h2>Contact Cairn by email</h2>
        <p>If you need to contact Cairn, please use the form below. This will ensure that your message is routed to the appropriate people and can be addressed as quickly as possible.</p>

        <style>
            #form_contact .articleBody > div + div { margin-top : 1.5em;}
            #form_contact label { display : block;}
        </style>

        <form action="javascript:ajax.checkContactForm();" method="post" id="form_contact">
            <div class="articleBody">
                <div>
                    <div class="blue_milk w45">
                        <label class="prenom" for="prenom">First name <span class="red">*</span></label>
                        <input id="prenom" class="prenom" type="text" required="required">
                    </div>
                    <div class="blue_milk right w45">
                        <label class="prenom" for="nom">Last name <span class="red">*</span></label>
                        <input id="nom" class="prenom" type="text" required="required">
                    </div>
                </div>
                <div class="blue_milk w97">
                    <label for="email">Email address <span class="red">*</span></label>
                    <input id="email" class="prenom" type="text" required="required">
                </div>
                <div class="blue_milk w97">
                    <label for="service">Which department would you like to contact?</label>
                    <select class="email" id="service" required="required">
                        <option value="">Choose…</option>
                        <option value="<?php echo $serviceClients; ?>">Customer service (ex: status of your order)</option>
                        <option value="<?php echo $supportTechnique; ?>">Technical support (ex: help using our website)</option>
                        <option value="<?php echo $administrateur; ?>">Web administrator (ex: content questions)</option>
                        <option value="<?php echo $serviceCommercial; ?>">Licences department (options for institutional subscriptions)</option>
                        <option value="<?php echo $serviceAdministratif; ?>">Administrative service (ex: accounts and billing)</option>
                    </select>
                </div>
                <div class="blue_milk w97">
                    <label for="message">Your message <span class="red">*</span></label>
                    <textarea class="custom_textarea_bm" rows=20 class="email" id="message" required="required"></textarea>
                </div>
                <div class="blue_milk w97">
                    <label for="copie">I would like to receive a copy of this email.</label>
                    <input type="checkbox" id="copie" style="display:inline;" />
                </div>
                <div style="overflow:hidden;">
                    <div class="blue_milk w45 left">
                        <label for="formug">Please type the code below<span class="red">*</span></label>
                        <input type="text" size="5" maxlength="7" class="inputform" id="formug" required="required">

                    </div>
                    <div class="blue_milk w45 right">
                        <input id="captchaCode" type="hidden" value="<?php echo $code; ?>">
                        <img style="text-align:center;" src="./public_libs/captcha/captcha_images.php?width=120&height=40&code=<?php echo $code?>">
                    </div>
                </div>
            </div>
            <input class="button right" type="submit" value="Send">
        </form>
        <br />
        <h2>If you need to contact Cairn:</h2>
        <p>
            <strong><em>Cairn in Belgium</em></strong>
        </p>
        <p>
            58/60, rue des Champs,<br/>
            B-4020 LIEGE - BELGIUM<br/>
            Phone: (+32) 43 403 838. Fax: (+32) 43 445 224.
        </p>
        <p>
            <strong><em>Cairn in France</em></strong>
        </p>
        <p>
            26, Rue Édouard-Lockroy,<br/>
            75011 PARIS – FRANCE<br/>
            Phone: (+33) 155 288 300. Fax: (+33) 155 283 533.
        </p>
    </div>
</div>
