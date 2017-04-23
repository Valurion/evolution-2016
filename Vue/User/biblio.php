<?php
$this->titre = "Ma bibliographie";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="body-content">
    <div class="biblio" id="free_text">
        <br>
        <div class="list_articles">
            <h1 class="main-title">Ma bibliographie</h1>

            <p class="text-center">
                <a href="#" onclick="cairn.show_modal('#modal_mail')" class="bold link-underline">Envoyer par e-mail</a>
            </p>

            <div class="center link_font">
                <div style="font-family: 'Alegreya';font-weight: normal;font-size: 17px;">Vous pouvez également exporter toutes les citations dans votre outil de gestion de bibliographie en utilisant les liens ci­-dessous :</div>
                <?php if($biblioList != "") {?>
                    <a style="margin-left: 5px;margin-right: 5px;"  href="http://www.refworks.com/express/ExpressImport.asp?vendor=Cairn&amp;filter=Refworks%20Tagged%20Format&amp;encoding=65001&amp;url=<?= Configuration::get('refworks') ?>?ID_ARTICLE=<?= $biblioList ?>" onclick="" class="link-underline">RefWorks</a>
                    <span>|</span>
                    <a style="margin-left: 5px;"  href="<?= Configuration::get('zotero') ?>?ID_ARTICLE=<?= $biblioList ?>" onclick="" class="link-underline">Zotero (.ris)</a>
                    <span>|</span>
                    <a style="margin-left: 5px;margin-right: 5px;"  href="<?= Configuration::get('endnote') ?>?ID_ARTICLE=<?= $biblioList ?>" onclick="" class="link-underline">EndNote (.enw)</a>

                <?php } else { ?>
                    <p class="alert alert-warning"><b>Votre bibliographie est vide.</b><br /> <a href="http://aide.cairn.info/comment-creer-une-bibliographie/">Comment créer une bibliographie ?</a></p>
                <?php } ?>

                <!-- Calcul du nombre total d'élement dans la bibliographie -->
                <form>
                    <input type="hidden" id="nbreTotalBiblio" name="nbreTotalBiblio" value="<?php echo count($numOuv) + count($artOuv) + count($numRev) + count($artRev) + count($numMag) + count($artMag) ; ?>" />
                    <input type="hidden" id="nbreNumOuv" name="nbreNumOuv" value="<?php echo count($numOuv); ?>" />
                    <input type="hidden" id="nbreArtOuv" name="nbreArtOuv" value="<?php echo count($artOuv); ?>" />
                    <input type="hidden" id="nbreNumRev" name="nbreNumRev" value="<?php echo count($numRev); ?>" />
                    <input type="hidden" id="nbreArtRev" name="nbreArtRev" value="<?php echo count($artRev); ?>" />
                    <input type="hidden" id="nbreNumMag" name="nbreNumMag" value="<?php echo count($numMag); ?>" />
                    <input type="hidden" id="nbreArtMag" name="nbreArtMag" value="<?php echo count($artMag); ?>" />
                </form>
            </div>

            <?php if (!empty($numRev)) { ?>
                <div id="NumRev" class="mt2">
                    <h2 class="section"><span>Numéros de revues</span></h2>
                    <?php
                        $arrayForList = $numRev;
                        $currentPage = 'numero';
                        $arrayFieldsToDisplay = array('ID', 'REVUE_TITLE', 'BIBLIO_AUTEURS', 'STATE_OUV', 'REMOVE_BIBLIO');
                        include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                    ?>
                </div>
            <?php } ?>

            <?php if (!empty($artRev)) { ?>
                <div id="ArtRev" class="mt2">
                    <h2 class="section"><span>Articles de revues</span></h2>
                    <?php
                        $arrayForList = $artRev;
                        $currentPage = 'contrib';
                        $arrayFieldsToDisplay = array('ID', 'REVUE_TITLE', 'BIBLIO_AUTEURS', 'STATE', 'REMOVE_BIBLIO');
                        include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                    ?>
                </div>
            <?php } ?>

            <?php if (!empty($numOuv)) { ?>
                <div id="NumOuv" class="mt2">
                    <h2 class="section"><span>Ouvrages</span></h2>
                    <?php
                        $currentPage = 'numero';
                        $arrayForList = $numOuv;
                        $arrayFieldsToDisplay = array('ID', 'COLL_TITLE', 'BIBLIO_AUTEURS', 'STATE_OUV', 'REMOVE_BIBLIO');
                        include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                    ?>
                </div>
            <?php } ?>

            <?php if (!empty($artOuv)) { ?>
                <div id="ArtOuv" class="mt2">
                    <h2 class="section"><span>Contributions d’ouvrages</span></h2>
                    <?php
                        $arrayForList = $artOuv;
                        $currentPage = 'contrib';
                        $arrayFieldsToDisplay = array('ID', 'NUMERO_TITLE', 'BIBLIO_AUTEURS', 'STATE', 'REMOVE_BIBLIO');
                        include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                    ?>
                </div>
            <?php } ?>

            <?php if (!empty($numMag)) { ?>
                <div id="ArtMag" class="mt2">
                    <h2 class="section"><span>Numéros de magazine</span></h2>
                    <?php
                        $arrayForList = $numMag;
                        $currentPage = 'numero';
                        $arrayFieldsToDisplay = array('ID', 'REVUE_TITLE', 'BIBLIO_AUTEURS', 'STATE_OUV', 'REMOVE_BIBLIO');
                        include (__DIR__ . '/../CommonBlocs/liste_1col.php');
                    ?>
                </div>
            <?php } ?>

            <?php if (!empty($artMag)) { ?>
                <div id="NumOuv" class="mt2">
                    <h2 class="section"><span>Articles de magazines</span></h2>
                    <?php
                        $arrayForList = $artMag;
                        $currentPage = 'contrib';
                        $arrayFieldsToDisplay = array('ID', 'REVUE_TITLE', 'BIBLIO_AUTEURS', 'STATE', 'REMOVE_BIBLIO');
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
        <h2>Envoyer par email</h2>
        <p>
            Votre bibliographie sera envoyée par email, accompagnée si vous le désirez d’un commentaire de votre choix.
            Les coordonnées que vous indiquez dans cette page ne sont pas conservées et sont à usage unique.
        </p>

        <form method="post" action="javascript:ajax.sendBiblioMail()" name="inscription" id="inscription">
            <input id="site" type="hidden" value="cairn" name="site">
            <input id="biblioList" type="hidden" value="<?= $biblioList ?>" name="biblio">

            <!-- Alert - Erreur -->
            <div id="formSendBiblioMailError1" style="display: none;margin-bottom: 1em;text-align: center;color: red;font-weight: bold;">Les champs marqués d'une étoile * sont obligatoires</div>
            <div id="formSendBiblioMailError2" style="display: none;margin-bottom: 1em;text-align: center;color: red;font-weight: bold;">Vous n'avez choisi aucun destinataire</div>

            <!-- Coordonnées de l'utilisateur -->
            <div class="wrapper">
                <div class="">
                    <label><input type="checkbox" id="sendToUser" name="sendToUser" value="1" checked="checked" onchange="$('.toggleUser').toggle();"> Envoyer à mon adresse email</label>
                </div>
                <div class="toggleUser blue_milk left w45">
                    <label for="nomUser">Prénom et Nom<span class="red">*</span></label>
                    <div>
                        <span><?php echo $authInfos["U"]["PRENOM"]." ".$authInfos["U"]["NOM"]; ?></span>
                        <small class="right"><a class="link-underline" href="mon_compte.php">Changer</a></small>
                        <input type="hidden" id="nomUser" name="nomUser" value="<?php echo $authInfos["U"]["PRENOM"]." ".$authInfos["U"]["NOM"]; ?>">
                    </div>
                </div>
                <div class="toggleUser blue_milk right w45">
                    <label class="emailUser" for="prenom">Adresse e-mail <span class="red">*</span></label>
                    <div>
                        <span><?php echo $authInfos["U"]["EMAIL"]; ?></span>
                        <small class="right"><a class="link-underline" href="modification-adresse-email.php">Changer</a></small>
                        <input type="hidden" id="emailUser" name="emailUser" value="<?php echo $authInfos["U"]["EMAIL"]; ?>">
                    </div>
                </div>
            </div>

            <!-- Coordonnées du destinataire -->
            <div class="wrapper mt1">
                <div class="">
                    <label><input type="checkbox" id="sendToDestination" name="sendToDestination" value="1" onchange="$('.toggleDestination').toggle();"> et/ou Envoyer à un autre destinataire</label>
                </div>
                <div class="toggleDestination blue_milk left w45" style="display: none;">
                    <label for="nomDestination"> Nom du destinataire <span class="red">*</span></label>
                    <input type="text" id="nomDestination" name="nomDestination" class="textInput">
                </div>

                <div class="toggleDestination blue_milk right w45" style="display: none;">
                    <label for="emailDestination"> Adresse e-mail du destinataire <span class="red">*</span></label>
                    <input type="email" id="emailDestination" name="emailDestination" class="textInput">
                </div>
            </div>

            <!-- Commentaire -->
            <div class="wrapper mt1">
                <div class="blue_milk" style="width: 97%;">
                    <label for="commentaire">Ajouter le commentaire suivant :</label>
                    <textarea id="commentaire" name="commentaire" cols="40" rows="5" class="textInput custom_textarea_bm"></textarea>
                </div>
            </div>

            <div class="wrapper">
                <div style="overflow: hidden;" class="mt1">
                    <input style="color: #FFF;" type="submit" value="Envoyer" class="button blue_button right">
                </div>
            </div>
        </form>
    </div>
</div>
