<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "Mon historique"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
        <a class="inactive" href="/">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
        <a href="mon_historique.php">Mon historique</a>
</div>



<div id="body-content" class="biblio">
    <div class="list_articles">
        <h1 class="main-title">Mon historique</h1>

        <br />

        <div class="articleIntro">
            <p>Liste des 100 dernières publications consultées sur Cairn.info.</p>
        </div>
        <div class="articleBody">
            <?php
            foreach($historiqueList as $histoArt){
                $art = $historiqueDetail[$histoArt];
                $arrayForList = array($art);
                $currentPage = 'contrib';
                switch($art['REVUE_TYPEPUB']){
                    case '1':
                        $arrayFieldsToDisplay = array('ID','REVUE_TITLE', 'BIBLIO_AUTEURS','STATE');
                        break;
                    case '2':
                        $arrayFieldsToDisplay = array('ID','REVUE_TITLE', 'BIBLIO_AUTEURS','STATE',);
                        break;
                    default:
                        $arrayFieldsToDisplay = array('ID','NUMERO_TITLE', 'BIBLIO_AUTEURS','STATE');
                        break;
                }
                include (__DIR__ . '/../CommonBlocs/liste_1col.php');
            }
            ?>

            <br />

            <?php if(empty($authInfos['U'])){
                echo 'Pour retrouver cet historique lors de vos prochaines visites sur Cairn.info,
                     <a class="link_custom_generic" onclick="cairn.show_modal(\'#modal_login\')" href="#">Connectez-vous</a>';
            }?>

            <div id="modal_login" class="window_modal" style="display: none;">
                <div class="basic_modal">
                    <h1 class="main-title">Connexion</h1>
                    <br />
                    <p class="lightboxAlerte">Ce mot de passe n’est pas valide</p>
                    <form id="lightboxAlerteForm" action="#" method="post">
                        <input type="hidden" value="CONNECT" name="OPERATE" />

                        <div class="blue_milk left w45">
                            <label for="email">
                                Votre adresse email
                                <span>*</span>
                            </label>
                            <input id="LOG" class="prenom" type="text" value="" name="LOG">
                        </div>
                        <div class="blue_milk right w45">
                            <label for="mdp">
                                Votre mot de passe
                                <span>*</span>
                            </label>
                            <input id="PWD" class="prenom" type="password" value="" name="PWD">
                        </div>
                        <br /><br /><br />
                        <input type="submit" class="inputButton" value="Envoyer" />
                        <a href="./mdp_oublie.php" title="Mot de passe oublié"
                           class="link_custom">Mot de passe oublié</a> <a
                           href="./creer_compte.php" title="Créer un compte"
                           class="link_custom">Créer un compte</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
