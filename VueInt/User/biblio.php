<?php
$this->titre = "My selection";
?>
<div id="body-content">
    <div class="biblio" id="free_text">
        <br>
        <div class="list_articles">
            <h1 class="main-title">My selection</h1>

            <p class="text-center">
                <a href="#" onclick="cairn.show_modal('#modal_mail')" class="bold link-underline">Send by email</a>                
            </p> 

            <div class="center link_font">
                <div style="font-family: 'Alegreya';font-weight: normal;font-size: 17px;">You may also export this reference in the citation tool of your choice by using the following links:</div>
                <?php if($biblioList != "") {?>
                    <a style="margin-left: 5px;margin-right: 5px;"  href="<?= Configuration::get('refworks') ?>?ID_ARTICLE=<?= $biblioList ?>" onclick="" class="link-underline">Refworks</a>
                    <span>|</span>
                    <a style="margin-left: 5px;"  href="<?= Configuration::get('zotero') ?>?cairnint=1&ID_ARTICLE=<?= $biblioList ?>" onclick="" class="link-underline">Zotero (.ris)</a>
                    <span>|</span>
                    <a style="margin-left: 5px;margin-right: 5px;"  href="<?= Configuration::get('endnote') ?>?ID_ARTICLE=<?= $biblioList ?>" onclick="" class="link-underline">EndNote (.enw)</a>
                <?php } else { ?>
                    <p class="alert alert-warning"><b>Your list is empty.</b><br /> <!--<a href="http://aide.cairn.info/comment-creer-une-bibliographie/">How to create a list of articles ?</a>--></p>
                <?php } ?>

                <!-- Calcul du nombre total d'élement dans la bibliographie -->
                <form>
                    <input type="hidden" id="nbreTotalBiblio" name="nbreTotalBiblio" value="<?php echo count($artRev) ; ?>" />
                    <input type="hidden" id="nbreArtRev" name="nbreArtRev" value="<?php echo count($artRev); ?>" />
                </form>
            </div>

            <?php if (!empty($artRev)) { ?>
                <div id="ArtRev" class="mt2">
                    <h2 class="section"><span>Journal articles</span></h2>
                    <?php
                        $arrayForList = $artRev;
                        $currentPage = 'contrib';
                        $arrayFieldsToDisplay = array('ID', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'REMOVE_BIBLIO');
                        include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../CommonBlocs/invisible.php'; ?>


<? /* Fenêtre modale pour l'envoi de bibliographie par mail */ ?>
<div style="display: none;" class="window_modal" id="modal_mail">
    <div class="info_modal"><a class="close_modal" href="javascript:void(0);" onclick="cairn.close_modal();"></a>
        <h2>Send by mail</h2>
        <p>
            Your selection will be sent by email, including optional comments to the recipient. 
            The contact details you enter on this page are not saved and are for single use only.
        </p>

        <form method="post" action="javascript:ajax.sendBiblioMail()" name="inscription" id="inscription">
            <input id="site" type="hidden" value="cairn-int" name="site">
            <input id="biblioList" type="hidden" value="<?= $biblioList ?>" name="biblio">

            <!-- Alert - Erreur -->
            <div id="formSendBiblioMailError1" style="display: none;margin-bottom: 1em;text-align: center;color: red;font-weight: bold;">All fields with * are required</div>
            <div id="formSendBiblioMailError2" style="display: none;margin-bottom: 1em;text-align: center;color: red;font-weight: bold;">No recipient defined</div>

            <!-- Coordonnées de l'utilisateur -->
            <div class="wrapper-int">
                <div class="">
                    <label><input type="checkbox" id="sendToUser" name="sendToUser" value="1" checked="checked" onchange="$('.toggleUser').toggle();"> Send to my email address</label>
                </div>
                <div class="toggleUser blue_milk left w45">
                    <label for="nomUser">First name and Last name <span class="red">*</span></label>
                    <div>
                        <span><?php echo $authInfos["U"]["PRENOM"]." ".$authInfos["U"]["NOM"]; ?></span>
                        <input type="hidden" id="nomUser" name="nomUser" value="<?php echo $authInfos["U"]["PRENOM"]." ".$authInfos["U"]["NOM"]; ?>">
                        <small class="right"><a class="link-underline" href="my_account.php">Change</a></small>
                    </div>
                </div>
                <div class="toggleUser blue_milk right w45">
                    <label class="emailUser" for="prenom">Email address <span class="red">*</span></label>
                    <div>
                        <span><?php echo $authInfos["U"]["EMAIL"]; ?></span>
                        <small class="right"><a class="link-underline" href="modification-adresse-email.php">Change</a></small>
                        <input type="hidden" id="emailUser" name="emailUser" value="<?php echo $authInfos["U"]["EMAIL"]; ?>">
                    </div>
                </div>
            </div>

            <!-- Coordonnées du destinataire -->
            <div class="wrapper-int mt1">
                <div class="">
                    <label><input type="checkbox" id="sendToDestination" name="sendToDestination" value="1" onchange="$('.toggleDestination').toggle();"> and/or send to another recipient</label>
                </div>
                <div class="toggleDestination blue_milk left w45" style="display: none;">
                    <label for="nomDestination">Recipient's name <span class="red">*</span></label> 
                    <input type="text" id="nomDestination" name="nomDestination" class="textInput">
                </div>

                <div class="toggleDestination blue_milk right w45" style="display: none;">
                    <label for="emailDestination">Recipient's email address<span class="red">*</span></label> 
                    <input type="email" id="emailDestination" name="emailDestination" class="textInput">
                </div>
            </div>

            <!-- Commentaire -->
            <div class="wrapper-int mt1">
                <div class="blue_milk" style="width: 97%;">
                    <label for="commentaire">Add the following comment:</label>
                    <textarea id="commentaire" name="commentaire" cols="40" rows="5" class="textInput custom_textarea_bm"></textarea>
                </div>
            </div>

            <div class="wrapper-int">
                <div style="overflow: hidden;" class="mt1">
                    <input style="color: #FFF;" type="submit" value="Send" class="button blue_button right">
                </div>
            </div>
        </form>
    </div>
</div>
