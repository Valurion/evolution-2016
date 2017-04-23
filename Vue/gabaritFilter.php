<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <!--base href="<?= $racineWeb . '/cairn' ?>" -->

        <meta name="WT.cg_n" content="Navigation">
        <meta name="WT.cg_s" content="Navigation Accueil">
        <meta name="DCSext.pg_type" content="Catalogue">
        <meta name="DCSext.pn_type" content="Revues">

        <?php
            // Inclusion des feuilles de style css
            require_once('CommonBlocs/headerCss.php');
        ?>

        <?php foreach ($head as $tag): ?>
            <?= $tag ?>
        <?php endforeach; ?>

        <link rel="icon" type="image/png" href="favicon.ico" />
        <!-- JQUERY and JQUERY UI -->

<style>
    .ui-autocomplete.ui-menu.ui-widget.ui-widget-content.ui-corner-all {
        font-size:11px;
    }
    .ui-autocomplete-category {
        background-color:#4BB2AC;
        font-weight:bold;
        color:white;

font: bold 20px/100% "Alegreya SC";

        padding:3px 0 3px 2px ;
    }
    .ui-menu-item a {
        padding: 0px;
        min-height: 0;
        font-weight: normal;
	font: 16px "Alegreya";
       line-height: 1;


    }
</style>
<!-- <script src="./js/jquery-ui.js" charset="utf-8"></script> -->
<script src="./static/js/jquery-ui-1.9.2.custom.min.js" charset="utf-8"></script>
 <script src="./static/js/autocomplete_research.js"></script>


    <title><?= strip_tags($titre) ?> - Cairn.info</title>
    </head>
    <body>
        <div id="header">

            <ul id="nav1">
                <li>
                    <?php
                    if (isset($authInfos['U']) || isset ($authInfos['I'])) {
                        include(__DIR__ . '/User/logas.php');
                    } else {
                        include(__DIR__ . '/User/login.php');
                    }
                    ?>
                </li>
                <li><a href="#" onclick="cairn.show_menu(this, '#menu_mycairn');" id="user_mycairn_menu">Mon Cairn.info</a></li>
                <li><a href="./acces_hors.php"><span class="icon-remote-access icon" id="icon_remote-acess"></span>Hors campus</a></li>

                <li><a href="./a-propos.php" class="grey">À propos</a></li>
                <!-- <li><a href="./contact.php" class="grey">Contacts</a></li> -->
                <li><a href="http://aide.cairn.info" class="grey" target="blank" id="aide_h">Aide</a></li>
                <li><a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>" class="grey" id="header_link_cairnint">English</a></li>
                <?php
                if(!empty($authInfos['U']['ADMIN']))
                {
                    echo '<li><a id="isAdminLink" href="./administration.php"><span id="isAdmin">Administration</span></a></li>';
                }
                ?>
            </ul>
            <input type="hidden" id="corsURL" value="<?= $corsURL ?>"/>
            <div id="menu_login" class="menu menu_login" style="display: none; height: 34px; margin-bottom: 0px;">
                <div id="login_links" class="inbl right">
                    <b>Pas encore enregistré ?</b>
                    <a href="./creer_compte.php">Inscrivez-vous !</a><br>
                    <a href="./mdp_oublie.php">Mot de passe oublié ?</a>
                </div>
                <div id="login_inputs" class="inbl right">
                    <form id="login_form" name="login_form" action="javascript:ajax.login()" method="GET">
                    <label for="email_input">E-mail</label>
                    <input id="email_input" name="LOG" required="required" type="text">
                    <label for="password_input">Mot de passe</label>
                    <input id="password_input" name="PWD" required="required" type="password">
                    <input id="login_button" value="" class="inbl icon-submit-arrow" type="submit">
                    </form>
                </div>
            </div>
            <!--div id="menu_login_dev" class="menu menu_login" style="display:none;">
                <input value="CONNECT" name="OPERATE" type="hidden">
                <div id="login_inputs" class="inbl right">
                    <label for="email_input">Veuillez choisir un compte de test:</label>
                    <input value="Anonyme" class="inbl" type="button" onclick="ajax.logintest('1');">
                    <input value="Paris VII (vue grisée)" class="inbl" type="button" onclick="ajax.logintest('2');">
                    <input value="Paris VII (vue filtrée)" class="inbl" type="button" onclick="ajax.logintest('3');">
                </div>
            </div-->
            <style>
                #wrapper_menu_mycairn { position : relative; }
                #wrapper_menu_mycairn .close_menu {
                    height : 26px;
                    width : 26px;
                    position :absolute;
                    right : -52px;
                    cursor : pointer;
                }
            </style>

            <?php include(__DIR__ . '/User/mycairn.php'); ?>

            <div id="wrapper_search">
                <a href="./" class="inbl" style="margin-right:100px;">
                    <img src="./static/images/logo-cairn.png" alt="CAIRN.INFO : Chercher, repérer, avancer.">
                </a>
                <div class="inbl" style="width:430px;">
                    <form class="border_grey w100" id="main_search_form" action="./resultats_recherche.php" method="GET">
                        <button type="submit" class="right black_button" id="send_search_field" style="padding-bottom:0.1em;">
                            <span class="icon-magnifing-glass left mr6"></span>Chercher
                        </button>
                        <div id="wrapper_search_input">
                            <input autocomplete="off" id="compute_search_field" placeholder="Vos mots clés" class="w98 no_border ui-autocomplete-input" name="searchTerm" style="width:325px" type="text"><span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
                        </div>
                    </form>
                    <span id="link_search_advanced" style="font-size:12px;float:left;">
                        <?php if((isset($authInfos['U']) && isset($authInfos['U']['HISTO_JSON']->searchModeInfo) && $authInfos['U']['HISTO_JSON']->searchModeInfo[0] == 'access')
                            || (!isset($authInfos['U']) && isset($authInfos['G']) && isset($authInfos['G']['HISTO_JSON']->searchModeInfo) && $authInfos['G']['HISTO_JSON']->searchModeInfo[0] == 'access')){ ?>
                        Chercher dans: <input type="radio" name="searchIn" id="searchInAll" onChange="ajax.updateMode('all')" value="all"/>Tous&nbsp;&nbsp;<input checked type="radio" name="searchIn" id="searchInAccess" onChange="ajax.generateAccessCache()" value="access"/>Texte intégral accessible
                        <?php } else { ?>
                        Chercher dans: <input type="radio" name="searchIn" id="searchInAll" onChange="ajax.updateMode('all')" checked value="all"/>Tous&nbsp;&nbsp;<input type="radio" name="searchIn" id="searchInAccess" onChange="ajax.generateAccessCache()" value="access"/>Texte intégral accessible
                        <?php } ?>
                    </span>
                </div>
                <div class="inbl" style="width:180px;">
                    <a href="./recherche_avancee.php"><button id="send_search_field" class="right grey_button" style="top:5px;position:relative;padding-left:2px; height:45px; color:black; font-weight:normal;cursor:pointer;">Recherche avancée</button></a>
                </div>
            </div>

        </div>

        <div id="contenu">
            <?= $contenu ?>
        </div> <!-- #contenu -->
        <!-- #global -->

        <div id="wrapper_footer">
            <div id="footer">
                <div id="footer_shortcuts">
                    <a href="./" id="logo_cairn_footer">
                        <img src="./static/images/logo-cairn-footer.png" alt="CAIRN.INFO : Chercher, repérer, avancer.">
                    </a>
                    <ul>
                        <li><a href="./a-propos.php">À propos de Cairn.info</a></li>
                        <li><a href="./services-aux-editeurs.php">Services aux éditeurs</a></li>
                        <li><a href="./services-aux-institutions.php">Services aux institutions</a></li>
                        <li><a href="./services-aux-particuliers.php">Services aux particuliers</a></li>
                        <li><a href="./conditions.php">Conditions d’utilisation</a></li>
                        <li><a href="./conditions-generales-de-vente.php">Conditions de vente</a></li>
                        <li><a href="./conditions-generales-de-vente.php#retractation">Droit de rétractation</a></li>
                        <li><a href="./vie-privee.php">Vie privée</a></li>
                    </ul>
                </div>
                <div id="footer_disciplines">
                    <h1>Disciplines</h1>
                    <ul>
                        <li><a href="./disc-droit.htm">Droit</a></li>
                        <li><a href="./disc-economie-gestion.htm">Économie, gestion</a></li>
                        <li><a href="./disc-geographie.htm">Géographie</a></li>
                        <li><a href="./disc-histoire.htm">Histoire</a></li>
                        <li><a href="./disc-lettres-linguistique.htm">Lettres et linguistique</a></li>
                        <li><a href="./disc-philosophie.htm">Philosophie</a></li>
                        <li><a href="./disc-psychologie.htm">Psychologie</a></li>
                        <li><a href="./disc-sciences-de-l-education.htm">Sciences de l'éducation</a></li>
                        <li><a href="./disc-sciences-de-l-information.htm">Sciences de l'information</a></li>
                        <li><a href="./disc-sciences-politiques.htm">Sciences politiques</a></li>
                        <li><a href="./disc-sociologie-et-societe.htm">Sociologie et société</a></li>
                        <li><a href="./disc-sport-et-societe.htm">Sport et société</a></li>
                        <li><a href="./disc-interet-general.htm">Revues d'intérêt général</a></li>
                        <li><a href="./listerev.php" accesskey="2">Toutes les revues</a></li>
                    </ul>
                </div>
                <div id="footer_tools">
                    <h1>Outils</h1>
                    <ul>
                        <li><a href="http://aide.cairn.info" accesskey="6" id="aide_f">Aide</a></li>
                        <li><a href="./aide-plan-du-site.htm" accesskey="7">Plan du site</a></li>
                        <li><a href="./abonnement_flux.php">Flux RSS</a></li>
                        <li><a href="./acces_hors.php">Accès hors campus</a></li>
                        <li><a href="./contact.php" accesskey="5">Contacts</a></li>
                    </ul>
                </div>
                <div id="footer_menu_user">
                    <h1>Mon Cairn.info</h1>
                    <ul>
                        <li>

                            <a href="./mon_compte.php">Créer un compte</a>
                        </li>
                        <li><a href="./mon_panier.php" accesskey="4">Mon panier</a></li>
                        <li>Mes achats</li>
                        <li><a href="./biblio.php" accesskey="3">Ma bibliographie</a></li>
                        <li><a href="./mes_alertes.php">Mes alertes e-mail</a></li>
                        <li><a href="./credit.php">Mon crédit d'articles</a></li>
                    </ul>
                    <a href="http://www.centrenationaldulivre.fr/" id="logo_cnl">
                        <img src="./static/images/logo-cnl.png" alt="logo CNL" id="footer_logo_cnl">
                    </a>
                </div>
                <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>" id="footer_link_cairnint" id="english_f">English</a>
            </div>
        </div>
        <div id="post_footer">
            <span>&copy; 2010-2014 Cairn.info</span>
            <!--    <a href="#">Privacy Policy</a>
            <a href="#">Terms of use</a> -->
        </div>
        <div onclick="cairn.close_modal()" id="blackground"></div>
        <div id="error_div">
            <?php
                if(isset($contenu_erreur)){
                    echo $contenu_erreur;
                    echo '<script type="text/javascript">cairn.show_modal(\'#error_div_modal\');</script>';
                }
            ?>
        </div>
        <a id="jump-top" href="#top" style="display: block;"><img alt="back to top" src="./static/images/jump-top.png"></a>
        <div id="feedback">
            <a href="#" onclick="cairn.show_modal('#modal_feedback');" class="feedback-trigger">Feedback</a>
        </div>
        <div style="display: none;" class="window_modal" id="modal_feedback">
            <form method="POST" action="./static/includes/feedback/feedback_form.php" id="send_feedback">
                <h1>Votre Feedback à propos de Cairn.info</h1>
                <span onclick="cairn.close_modal();" class="close_modal"></span>
                <div id="feedback_radios">
                    <input type="radio" name="f_category" value="1" id="radio_idea">
                    <label for="radio_idea" class="active">Suggestion</label>
                    <input type="radio" name="f_category" value="2" checked="" id="radio_question">
                    <label for="radio_question">Question</label>
                    <input type="radio" name="f_category" value="3" id="radio_bug">
                    <label for="radio_bug">Anomalie</label>
                    <input type="radio" name="f_category" value="4" id="radio_praise">
                    <label style="display: none;" for="radio_praise">Encouragement</label>
                </div>
                <div id="feedback_fields">
                    <div class="feedback_field">
                        <label for="f_email">
                            <strong>Adresse e-mail</strong>
                        </label>
                        <input type="email" name="f_email" value="" required="" id="f_email">
                    </div>
                    <div class="feedback_field">
                        <label for="f_message">
                            <strong>Message</strong>
                        </label>
                        <textarea name="f_message" required="" id="f_message"></textarea>
                    </div>
                    <button type="submit">Envoyer</button>
                </div>
            </form>
        </div>
        <script type="text/javascript">
        function showEjectModal(){
            cairn.show_modal('#modal_logouteject');
        }
        </script>
        <div id="modal_logouteject" class="window_modal" style="display:none;">
            <div class="info_modal">
                <h2>Connexion fermée</h2>
                <p>Vous avez été déconnecté car votre compte est utilisé à partir d'un autre appareil.</p>
                <div class="buttons">
                    <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
                </div>
            </div>
        </div>
        <div id="modal_empty_input" class="window_modal" style="display:none;">
            <div class="info_modal">
                <h2>Alert</h2>
                <p>Il faut remplir les champs obligatoire.</p>
                <div class="buttons">
                    <span class="blue_button ok" onclick="cairn.close_modal()">Fermer</span>
                </div>
            </div>
        </div>



    <!-- JS starts here -->
    <?php
        // Inclusion des scripts js
        require_once('CommonBlocs/footerJavascript.php');
    ?>

    <?php
        if (Configuration::get('webtrends_datasource', null)) {
            include(__DIR__ . '/CommonBlocs/webtrends.php');
        }
    ?>

    <?php if (Configuration::get('allow_backoffice', false)): ?>
        <script>
            <?php if (isset($_SERVER['REDIRECT_QUERY_STRING'])): ?>
                // Uniquement à destination des développeurs, pour faciliter la recherche de controlleur et actions
                console.debug("URL-DEBUG :: <?= $_SERVER['REDIRECT_QUERY_STRING'] ?>");
            <?php endif; ?>
            window.DEBUG = true;
        </script>
    <?php endif; ?>

    <?= $this->getJavascripts(); ?>
    <!-- JS ends here -->

    </body>
</html>
