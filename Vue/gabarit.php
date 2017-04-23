<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <title><?= htmlspecialchars(strip_tags($titre)) ?> | <?= Configuration::get('siteName', 'Cairn.info') ?></title>
        <meta name="viewport" content="width=1024">
        <link rel="icon" type="image/png" href="favicon.ico" />
        <!-- cairn-build :: [test] -->

        <?php
            // Inclusion des feuilles de style css
            require_once('CommonBlocs/headerCss.php');
        ?>

        <?= $this->getHeaders('html', "\n        ") ?>

    </head>
    <body>
        <?php if (Configuration::get('alert_display', false) === "1"): ?>
            <div class="alert-<?= Configuration::get('alert_level', 'info') ?>">
                <?= Configuration::get('alert_message', '') ?>
            </div>
        <?php endif; ?>
        <div id="header">

            <?php if((!isset($_COOKIE["callout-cairnint-vu"])) && (isset($authInfos['I'])) && ($authInfos['I']["PARAM_INST_WEBTRENDS"]["LANGUE"] != 'FR'))  { ?>
                <!-- Mise en évidence de Cairn-int pour les non-francophones -->
                <div class="header-wrapper">
                    <div id="cairn-int-info-visitor">
                        <div class="wrapper">
                            <span class="title">It looks like you’re not in France...</span>
                            <span class="msg">
                                We noticed you might be connecting from a non-French speaking institution.
                                You might also want to visit <a class="link-underline" href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>">www.cairn-int.info</a>, our international edition filled with translated abstracts and articles
                                from key French-language journals.
                            </span>
                            <a class="btn-cairn-int-view" href="javascript:void(0);" onclick="ajax.CairnIntInfoVisitor();"><img src="./static/images/icon-close-white.png" alt="[X]" /></a>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- Access-Hors & Top Links -->
            <div class="container-top-links container-fluid">
                <div class="container">
                    <div class="row clearfix">
                        <div class="left access-hors-id">
                            <?php
                                // Connexion Institution non-établie
                                if(!isset($authInfos["I"])) {
                                    echo "<a class=\"ah-not-connected\" href=\"acces_hors.php\">Accès hors campus</a>";
                                }
                                // Affichage de l'institution
                                else {
                                    echo "<a class=\"ah-connected\" href=\"javascript:void(0);\">Accès <i>via</i> ".$authInfos["I"]["NOM"]."</a>";
                                }
                            ?>
                        </div>
                        <div class="right">
                            <div class="nav">
                                <ul>
                                    <li><a href="./a-propos.php">À propos</a></li>
                                    <li><a href="http://aide.cairn.info">Aide</a></li>
                                    <li><a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>">English version</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End - Access-Hors & Top Links -->

            <!-- User - Identification -->
            <div class="container-user container">
                <div class="row clearfix">
                    <div class="nav">
                        <ul>
                            <?php if(!isset($authInfos["U"])) { ?>
                                <li><a class="user-not-connected" href="javascript:void(0);" onclick="cairn.show_login_modal();">Me connecter</a></li>
                                <li><a href="creer_compte.php">Créer un compte</a></li>
                            <?php } else { ?>
                                <li><a class="user-connected" href="javascript:void(0);" onclick="ajax.logout();"><span id="user-name"><?php echo $authInfos["U"]["PRENOM"]." ".$authInfos["U"]["NOM"]; ?></span><span class="label-logout">Me déconnecter</span></a></li>
                                <li><a class="user-mon-cairn-info toggle-mon-cairn" href="javascript:void(0);" onclick="$('.container-mon-cairn').slideToggle();$(this).toggleClass('active');">Mon cairn.info</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <input type="hidden" id="corsURL" value="<?= $corsURL ?>"/>
            <!-- End - Access-Hors & Top Links -->

            <!-- My Cairn (online) -->
            <?php include(__DIR__ . '/User/mycairn.php'); ?>
            <!-- End - My Cairn (online) -->

            <!-- Cairn.info : Logo & Formulaire -->
            <div class="container-cairn container">
                <div class="row clearfix">
                    <!-- Logo Cairn-info -->
                    <div class="logo">
                        <h1><a href="./" class="logo"><img src="./static/images/logo-cairn.png" srcset="./static/images/logo-cairn@2x.png 2x, ./static/images/logo-cairn@3x.png 3x, ./static/images/logo-cairn@4x.png 4x" alt="CAIRN.INFO : Chercher, repérer, avancer."></a></h1>
                    </div>

                    <!-- Formulaire -->
                    <?php
                        // Récupération des derniers termes de recherche
                        $recherches = array();
                        // Définition du terme de recherche
                        // Recherche par mot clé (simple)
                        if($_REQUEST["searchTerm"]) {
                            $recherchesTerms = $_REQUEST["searchTerm"];
                        }
                        // Recherche par moteur avancé (on récupère la dernière recherche)
                        // /!\ dans certain cas, une des valeurs peut disparaitre du champ de recherche au changement de page ou changement de filre,
                        // car elle est reprise directement dans les facettes (ex.: 2000-2017 ET Agone => Agone)
                        if($_REQUEST["submitAdvForm"]) {
                            if(isset($authInfos['U']) && isset($authInfos['U']['HISTO_JSON']->recherches)){
                                $recherches = $authInfos['U']['HISTO_JSON']->recherches;
                                $recherchesTerms = $recherches[0][0];
                            }
                            if(isset($authInfos['G']) && isset($authInfos['G']['HISTO_JSON']->recherches)){
                                $recherches = $authInfos['G']['HISTO_JSON']->recherches;
                                $recherchesTerms = $recherches[0][0];
                            }
                        }
                    ?>
                    <div class="search-form">
                        <form action="./resultats_recherche.php" method="GET">
                            <div class="border_grey w100" id="main_search_form">
                                <input type="submit" class="right black_button" id="send_search_field" name="send_search_field" value="Chercher" />
                                <div id="wrapper_search_input">
                                    <input autocomplete="off" id="compute_search_field" placeholder="Vos mots clés" class="w98 no_border ui-autocomplete-input" name="searchTerm" type="text" title="rechercher sur Cairn.info" value="<?php echo $recherchesTerms; ?>"><span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
                                    <?php $this->javascripts []= "$(function() {cairn.autocomplete('#compute_search_field', {redirectOnClick: true})})"; ?>
                                </div>
                            </div>
                            <?php
                                // Les filtres sont limités pour les connexions institutions ET si le nombre de licence de l'institution est limité
                                if(isset($authInfos['I'])) {
                                    if((isset($authInfos['U']) && isset($authInfos['U']['HISTO_JSON']->searchModeInfo) && $authInfos['U']['HISTO_JSON']->searchModeInfo[0] == 'access') || (!isset($authInfos['U']) && isset($authInfos['G']) && isset($authInfos['G']['HISTO_JSON']->searchModeInfo) && $authInfos['G']['HISTO_JSON']->searchModeInfo[0] == 'access') || (isset($_GET["searchIn"]) && $_GET["searchIn"] == "access") || (isset($_COOKIE["UsersearchIn"]) && ($_COOKIE["UsersearchIn"] == "access"))) {
                                        $searchInAll = "";
                                        $searchInAccess = "checked";
                                    }
                                    else {
                                        $searchInAll = "checked";
                                        $searchInAccess = "";
                                    } ?>
                                    <div class="search_form_advanced">
                                        <span><label><input type="radio" id="searchInAll" name="searchIn" value="all" <?php echo $searchInAll; ?> onChange="ajax.updateMode('all')" /> Tout </label></span>
                                        <span><label><input type="radio" id="searchInAll" name="searchIn" value="access" <?php echo $searchInAccess; ?> onChange="ajax.updateMode('access')" /> Texte intégral accessible <i>via</i> votre institution</label></span>
                                    </div>
                            <?php } else { ?>
                                <input type="hidden" name="searchIn" value="all"/>
                            <?php } ?>

                            <?php
                                // Dans le cas d'une institution, on sélectionne le bouton radio précédemment sélectionné
                                if(isset($authInfos['I'])) {
                                    $this->javascripts[] = "$('input[name=searchIn][value=\'".$searchTermAccess."\']').attr('checked', 'checked');";
                                }
                            ?>
                        </form>
                    </div>

                    <!-- Options -->
                    <div class="search_option">
                        <div class="so-label">
                            <a id="search-option-button" href="javascript:void(0);"></a>
                            <div class="so-panel">
                                <a href="recherche_avancee.php">Recherche avancée</a>
                                <a href="http://aide.cairn.info/tag/recherche/">Aide à la recherche</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End - Access-Hors & Top Links -->


        </div>

        <div id="contenu">
            <?= $contenu ?>
        </div> <!-- #contenu -->
        <!-- #global -->

        <div id="wrapper_footer">
            <div id="footer">

                <div id="footer_shortcuts">
                    <a id="logo_cairn_footer" href="./">
                        <img src="./static/images/logo-cairn-footer.png" srcset="./static/images/logo-cairn-footer@2x.png 2x, ./static/images/logo-cairn-footer@3x.png 3x, ./static/images/logo-cairn-footer@4x.png 4x" alt="CAIRN.INFO : Chercher, repérer, avancer.">
                    </a>

                    <div id="logos_footer" class="logo-single">
                        <label>
                            <span class="lbl-single">Avec le soutien du</span>
                            <span class="lbl-plural">Avec leur soutien</span>
                        </label>

                        <a id="logo_cnl_footer" href="http://www.centrenationaldulivre.fr"><img src="./static/images/logo-cnl-footer.png" alt="logo CNL"></a>

                        <?php /*
                        N'est affiché que sur les pages concernant des revues du CNRS.
                        Voir l'entrée `revuesCNRS` dans le fichier de configuration
                        */?>
                        <a id="logo_cnrs_footer" href="http://www.cnrs.fr"><img src="./static/images/logo-cnrs-footer.png" alt="logo CNRS"></a>
                    </div>



                </div>

                <div id="footer_cairn">
                    <h1>Cairn.info</h1>
                    <ul>
                        <li><a href="./a-propos.php">À propos de Cairn.info</a></li>
                        <li><a href="./aide-institutions-clientes.htm">Institutions clientes</a></li>
                        <li><a href="./services-aux-editeurs.php">Services aux éditeurs</a></li>
                        <li><a href="./services-aux-institutions.php">Services aux institutions</a></li>
                        <li><a href="./services-aux-particuliers.php">Services aux particuliers</a></li>
                        <li><a href="./conditions.php">Conditions d’utilisation</a></li>
                        <li><a href="./conditions-generales-de-vente.php">Conditions de vente</a></li>
                        <li><a href="./conditions-generales-de-vente.php#retractation">Droit de rétractation</a></li>
                        <li><a href="./vie-privee.php">Vie privée</a></li>
                    </ul>
                    <ul class="mt1">
                        <a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>">English version</a>
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
                        <li><a href="https://twitter.com/cairninfo">Twitter</a></li>
                        <li><a href="https://www.facebook.com/cairninfo">Facebook</a></li>
                    </ul>
                </div>

                <div id="footer_menu_user">
                    <h1>Mon Cairn.info</h1>
                    <ul>
                        <?php if(isset($authInfos['U'])) { ?>
                            <!-- Utilisateur Connecté -->
                            <li><a href="./biblio.php" accesskey="3">Ma bibliographie</a></li>
                            <li><a href="./mon_panier.php" accesskey="4">Mon panier</a></li>
                            <li><a href="./mes_achats.php">Mes achats</a></li>
                            <li><a href="./mes_alertes.php">Mes alertes</a></li>
                            <li><a href="./mon_compte.php">Mon compte</a></li>
                            <li><a href="javascript:void(0);" onclick="ajax.logout();">Déconnexion</a></li>
                        <?php } else { ?>
                            <!-- Utilisateur non-connecté -->
                            <li><a href="./mon_compte.php">Créer un compte</a></li>
                            <li><a href="./connexion.php">Me connecter</a></li>
                            <li><a href="./mon_panier.php" accesskey="4">Mon panier</a></li>
                        <?php } ?>
                    </ul>
                </div>

            </div>
        </div>
        <!-- Avertissement de l'utilisation des cookies -->
        <div id="cookie-alert">
            <div class="wrapper">
                <p>
                    Cairn.info utilise des cookies à des fins de statistiques. Ces données anonymes nous permettent ainsi de vous offrir une expérience de navigation optimale. En continuant votre visite
                    vous acceptez de recevoir ces cookies. Vous pouvez toutefois les désactiver dans les paramètres de votre navigateur web. <a class="link-underline" href="vie-privee.php#cookies">En savoir plus</a>
                </p>
            </div>
        </div>
        <div id="post_footer">
            <span>&copy; 2010-<?php echo date("Y", time()); ?> Cairn.info</span>
        </div>

        <!-- Login -->
        <?php if(!isset($authInfos["U"])) { ?>
        <div onclick="cairn.close_login_modal()" id="whiteground"></div>
        <div id="modal-login" class="">
            <div id="modal-login-container" class="container" onclick="javascript:void(0);">

                <form id="login_form" name="login_form" action="javascript:ajax.login()" method="GET">
                    <div class="wrapper">
                        <input class="field" id="email_input" name="LOG" required="required" type="text" placeholder="E-mail">
                        <input class="field" id="password_input" name="PWD" required="required" type="password" placeholder="Mot de passe">
                    </div>
                    <div class="wrapper text-right" style="margin-top: 5px;margin-bottom: -5px;">
                        <a class="link-underline" href="./mdp_oublie.php">Mot de passe oublié ?</a>
                    </div>
                    <div class="wrapper buttons clearfix">
                        <label><input type="checkbox" id="remember" name="remember" value="1" checked="checked" /> Rester connecté</label>
                        <input id="login_button" type="submit" value="Me connecter">
                    </div>
                </form>
                <div class="post-form">
                    Pas encore enregistré ? <a href="./creer_compte.php">Créer un compte</a>
                </div>

            </div>
        </div>
        <?php } ?>

        <?php
            // Utilisateur connecté
            if(isset($authInfos['U']) && isset($authInfos['U']['CREDIT_ARTICLE_SOLDE'])){

            // Crédit d'article arrive à expiration
            $limitCredit            = 30;
            $aujourdhui             = date("Y-m-d", time());
            $creditExpirationDate   = $authInfos['U']['CREDIT_ARTICLE_EXPIRATION'];

            // Calcul de la différence de jours
            $datetime1              = date_create($aujourdhui);
            $datetime2              = date_create($creditExpirationDate);
            $interval               = date_diff($datetime1, $datetime2);
            $nbreJoursInterval      = $interval->format('%R%a'); // nbre de jours

            // Vérification du panier
            $hasPanier              = count($authInfos['U']['HISTO_JSON']->panier);

            // Affichage de la modal, uniquement si entre 1 et la limite ET si le cookie CreditArticleAlert n'existe pas
            if(($nbreJoursInterval >= 1) && ($nbreJoursInterval <= $limitCredit) && (!isset($_COOKIE["CreditArticleAlert"]))) {
            ?>
                <!-- CREDIT_ARTICLE_EXPIRATION, CREDIT_ARTICLE_SOLDE -->
                <div style="display: none;" class="window_modal" id="modal_credit_article">
                    <div class="info_modal"><a class="close_modal" href="javascript:void(0);" onclick="cairn.close_modal();"></a>
                        <h2>Votre crédit d’achat arrive à expiration dans <?= substr($nbreJoursInterval, -1); ?> jour(s)</h2>
                        <p>
                            Pour rappel, les crédits d'achat sont valides jusqu'à la fin de l'année suivant leur commande, pour l’ensemble des publications
                            disponibles sur notre site. Vous avez ainsi jusqu’au <b><?= date_format(new DateTime($authInfos['U']['CREDIT_ARTICLE_EXPIRATION']), 'd/m/Y'); ?></b> pour l’utiliser.
                            <a class="link-underline" href="mon_credit.php">En savoir plus</a>
                        </p>
                        <p class="clearfix mt2">
                            <span class="left" style="margin-top: 5px;margin-left: 20px;font-size: 15px;"><label><input type="checkbox" id="CreditArticleAlert" name="CreditArticleAlert" onchange="ajax.CreditArticleAlert();" /> Ne plus afficher ce message</label></span>

                            <?php if($hasPanier != 0) {?>
                                <span class="right"><a class="blue_button" href="mon_panier.php">Continuer vers votre panier</a></span>
                            <?php } ?>
                        </p>
                    </div>
                </div>
                <?php $this->javascripts[] = '<script type="text/javascript">cairn.show_modal(\'#modal_credit_article\');</script>'; ?>
                <?php
                    // Création d'un cookie (temporaire) lors de l'affichage de l'alerte (uniquement si le cookie n'existe pas)
                    if(!isset($_COOKIE["CreditArticleAlert"])) {
                        $this->javascripts[] = '<script type="text/javascript">ajax.CreditArticleAlert();</script>';
                    }
                ?>
            <?php } ?>
        <?php } ?>


        <div onclick="cairn.close_modal();" id="blackground"></div>
        <div id="error_div">
            <?php
                if (isset($contenu_erreur)) {
                    echo $contenu_erreur;
                    $this->javascripts[] = '<script type="text/javascript">cairn.show_modal(\'#error_div_modal\');</script>';
                }
            ?>
        </div>
        <a id="jump-top" href="#top" style="display: none;"><img alt="back to top" src="./static/images/jump-top.png"></a>

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

    <?php
    if($authInfos["G"]["INIT"] == "1"){
       echo '<img src="http://'.$corsURL.'/about.php?cairn_guest='.$authInfos["G"]["TOKEN"].'" style=display:none;';
    }?>

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

    <?php if (Configuration::get('datadome_js_key', false)): ?>
        <script>
            !function(a,b,c,d,e){a.ddjskey=e;var f=b.createElement(c),g=b.getElementsByTagName(c)[0];
            f.async=1,f.src=d,g.parentNode.insertBefore(f,g)}
            (window,document,"script","https://js.datadome.co/tags.js","<?= Configuration::get('datadome_js_key') ?>");
        </script>
    <?php endif ;?>

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
