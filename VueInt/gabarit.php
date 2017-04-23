<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <title><?= htmlspecialchars(strip_tags($titre)) ?> | <?= Configuration::get('siteName', 'Cairn International') ?></title>
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

            <!-- Access-Hors & Top Links -->
            <div class="container-top-links container-fluid">
                <div class="container">
                    <div class="row clearfix">
                        <div class="left access-hors-id">
                            <?php
                                // Connexion Institution non-établie
                                if(isset($authInfos["I"])) {
                                    echo "<a class=\"ah-connected\" href=\"javascript:void(0);\">Access through ".$authInfos["I"]["NOM"]."</a>";
                                }
                            ?>
                        </div>
                        <div class="right">
                            <div class="nav">
                                <ul>
                                    <li><a href="about.php">About</a></li>
                                    <li><a href="help.php">Help</a></li>
                                    <li><a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>">French Edition</a></li>
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
                                <li><a class="user-not-connected" href="javascript:void(0);" onclick="cairn.show_login_modal();">Log in</a></li>
                                <li><a href="create_account.php">Create an account</a></li>
                            <?php } else { ?>
                                <li><a class="user-connected" href="javascript:void(0);" onclick="ajax.logout();"><span id="user-name"><?php echo $authInfos["U"]["PRENOM"]." ".$authInfos["U"]["NOM"]; ?></span><span class="label-logout">Sign out</span></a></li>
                                <li><a class="user-mon-cairn-info toggle-mon-cairn" href="javascript:void(0);" onclick="$('.container-mon-cairn').slideToggle();$(this).toggleClass('active');">My cairn.info</a></li>
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
                        <h1><a href="./" class="logo"><img src="./static/images/logo-cairn-int.png" srcset="./static/images/logo-cairn-int@2x.png 2x, ./static/images/logo-cairn-int@3x.png 3x, ./static/images/logo-cairn-int@4x.png 4x" alt="CAIRN.INFO : International Edition"></a></h1>
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
                        // ** Pas d'application sur cairn-int **
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
                    <div class="search-form" style="margin-right: 5px;">
                        <form class="border_grey w100" id="main_search_form" action="./resultats_recherche.php" method="GET">
                            <input type="submit" class="right black_button" id="send_search_field" name="send_search_field" value="Search" />
                            <div id="wrapper_search_input">
                                <input autocomplete="off" id="compute_search_field" placeholder="Your keywords" class="w98 no_border ui-autocomplete-input" name="searchTerm" type="text" title="rechercher sur Cairn.info" value="<?php echo $recherchesTerms; ?>"><span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
                                <?php $this->javascripts []= "$(function() {cairn.autocomplete('#compute_search_field', {redirectOnClick: true})})"; ?>
                            </div>
                        </form>
                        <div class="search_form_advanced">
                            <?php if((isset($authInfos['U']) && isset($authInfos['U']['HISTO_JSON']->searchMode) && $authInfos['U']['HISTO_JSON']->searchMode[0] == 'english')
                                || (!isset($authInfos['U']) && isset($authInfos['G']) && isset($authInfos['G']['HISTO_JSON']->searchMode) && $authInfos['G']['HISTO_JSON']->searchMode[0] == 'english')){ ?>
                                <span><label><input form="main_search_form" type="radio" id="type_search" name="type_search" value="all"> All articles </label></span>
                                <span><label><input form="main_search_form" type="radio" id="type_search" name="type_search" value="english" checked="1"> English full-text articles </label></span>
                            <?php }else{ ?>
                                <span><label><input form="main_search_form" type="radio" id="type_search" name="type_search" value="all" checked="1"> All articles </label></span>
                                <span><label><input form="main_search_form" type="radio" id="type_search" name="type_search" value="english"> English full-text articles </label></span>
                            <?php } ?>
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
                        <img src="./static/images/logo-cairn-int-footer.png" srcset="./static/images/logo-cairn-int-footer@2x.png 2x, ./static/images/logo-cairn-int-footer@3x.png 3x, ./static/images/logo-cairn-int-footer@4x.png 4x" alt="CAIRN.INFO : International Edition">
                    </a>

                    <div id="logos_footer" class="logo-single">
                        <label>
                            <span class="lbl-single"><small>With the support of</small></span>
                            <span class="lbl-plural">With their support</span>
                        </label>

                        <a id="logo_cnl_footer" href="http://www.cfcopies.com"><img src="./static/images/logo-cfc-footer.png" alt="logo CFC"></a>

                        <?php /*
                        N'est affiché que sur les pages concernant des revues du CNRS.
                        Voir l'entrée `revuesCNRS` dans le fichier de configuration
                        */?>
                        <a id="logo_cnrs_footer" href="http://www.cnrs.fr"><img src="./static/images/logo-cnrs-footer.png" alt="logo CNRS"></a>
                    </div>
                </div>

                <div id="footer_cairn"><br/>
                    <h1>Cairn International</h1>
                    <ul>
                        <li><a href="./about.php">About</a></li>
                        <li><a href="./conditions.php">Terms of use</a></li>
                        <li><a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>">French edition</a></li>
                    </ul>
                </div>

                <div id="footer_tools"><br/>
                    <h1>Tools</h1>
                    <ul>
                        <li><a href="./help.php">Help</a></li>
                        <li><a href="./rss_feeds.php">RSS feeds</a></li>
                        <li><a href="./contact.php">Contact</a></li>
                        <li><a href="https://twitter.com/cairnint">Twitter</a></li>
                        <li><a href="https://www.facebook.com/cairninfo">Facebook</a></li>
                    </ul>
                </div>

                <div id="footer_menu_user"><br/>
                    <h1>My Cairn.info</h1>
                    <ul>
                        <?php if(isset($authInfos['U'])) { ?>
                            <!-- User Connected -->
                            <li><a href="./biblio.php">My selection</a></li>
                            <li><a href="./my_cart.php">My cart</a></li>
                            <li><a href="./my_purchases.php">My purchases</a></li>
                            <li><a href="./my_alerts.php">My email alerts</a></li>
                            <li><a href="./my_account.php">My account</a></li>
                            <li><a href="javascript:void(0);" onclick="ajax.logout();">Sign out</a></li>
                        <?php } else { ?>
                            <!-- User Not Connected -->
                            <li><a href="./my_account.php">Create an account</a></li>
                            <li><a href="./connexion.php">Sign In</a></li>
                            <li><a href="./my_cart.php">My cart</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Acceptation des cookies -->
        <div id="cookie-alert">
            <div class="wrapper">
                <p>
                    Cairn-int.info uses cookies for statistical analysis. These anonymous data allow us to improve your online experience. If you continue browsing our web site
                    you accept to receive cookies from us. You can, however, turn them off in your browser settings.
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
                        <input class="field" id="email_input" name="LOG" required="required" type="text" placeholder="Email address">
                        <input class="field" id="password_input" name="PWD" required="required" type="password" placeholder="Password">
                    </div>
                    <div class="wrapper text-right" style="margin-top: 5px;margin-bottom: -5px;/*Conflit de style avec .wrapper de cairnint.css*/text-align: right;">
                        <a class="link-underline" href="./password_forgotten.php">Password forgotten?</a>
                    </div>
                    <div class="wrapper buttons clearfix">
                        <label><input type="checkbox" id="remember" name="remember" value="1" checked="checked" /> Remember me</label>
                        <input id="login_button" type="submit" value="Log in">
                    </div>
                </form>
                <div class="post-form">
                    Not registered yet? <a href="./create_account.php">Sign Up!</a>
                </div>

            </div>
        </div>
        <?php } ?>

        <div onclick="cairn.close_modal()" id="blackground"></div>
        <div id="error_div">
            <?php
                if(isset($contenu_erreur)){
                    echo $contenu_erreur;
                    $this->javascripts[] = 'cairn.show_modal(\'#error_div_modal\');';
                }
            ?>
        </div>
        <a id="jump-top" href="#top" style="display: none;"><img alt="back to top" src="./static/images/jump-top.png"></a>

        <div id="modal_why-not-article" class="window_modal" style="display: none;">
        <div class="basic_modal">
            <span onclick="cairn.close_modal();" class="close_modal"></span>
            <h1>Why is this article not available in English?</h1>
            <!--h2 id="article-title_translation">Asia-Pacific: China’s Foreign Policy Priority</h2-->
            <div class="w100">
                <p class="w45 inbl mr3">
                    Cairn International Edition is a service dedicated to helping a <span style="white-space:nowrap;">non&ndash;French&ndash;speaking</span> readership to browse, read, and discover work published in French journals. You will find English <span style="white-space:nowrap;">full&ndash;text</span> translations, in addition to French version already available on Cairn regular edition. Full text translations only exist for a selection of articles.
                </p>
                <div class="w45 inbl">
                    <p>
                        If you are interested in having this article translated into English, please enter your email address and you will receive an email alert when this article has been translated.
                    </p>
                    <form id="alert_on_translation" method="POST" action="./static/includes/feedback/alert_translation_form.php">
                        <input type="hidden" id="id_article_translation" name="id_article_translation" value="E_PE_143_0011">
                        <div class="case_blue-milk w100 inbl mt3">
                            <label for="email_translation">Your email address</label>
                            <input type="email" required="" value="" name="email_translation" id="email_translation">
                        </div>
                        <input type="submit" style="margin-top:0.5em;" class="button-blue right" value="Send">
                    </form>
                </div>
            </div>
        </div>
        </div>

        <script type="text/javascript">
        function showEjectModal(){
            cairn.show_modal('#modal_logouteject');
        }
        </script>
        <div id="modal_logouteject" class="window_modal" style="display:none;">
            <div class="info_modal">
                <h2>Connection closed</h2>
                <p>Your account is in use from another device. Your connection has been closed</p>
                <div class="buttons">
                    <span class="blue_button ok" onclick="cairn.close_modal()">Close</span>
                </div>
            </div>
        </div>

        <div id="modal_empty_input" class="window_modal" style="display:none;">
            <div class="info_modal">
                <h2>Alert</h2>
                <p>Some required fields are empty.</p>
                <div class="buttons">
                    <span class="blue_button ok" onclick="cairn.close_modal()">Close</span>
                </div>
            </div>
        </div>
        <div id="modal_confirm-why-not-article" class="window_modal">
            <div class="basic_modal">
                <h1>Message sent</h1>
                <p>
                    Your email address has been saved.<br>
                    We will notify you when this article becomes available in English.
                </p>
                <br>
                <br>
                <button onclick="cairn.close_modal();" class="button-blue">Close</button>
            </div>
        </div>

    <!-- JS starts here -->
    <?php
        // Inclusion des scripts js
        require_once('CommonBlocs/footerJavascript.php');
    ?>

    <?php
        if (Configuration::get('webtrends_datasource', null)) {
            include(__DIR__ . '/../Vue/CommonBlocs/webtrends.php');
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
