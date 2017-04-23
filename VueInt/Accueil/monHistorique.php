<?php
/**
 *
 *  
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "My history"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
        <a class="inactive" href="/">Home</a> <span class="icon-breadcrump-arrow icon"></span>
        <a href="my_history.php">My history</a>
</div>

<div id="body-content" class="biblio">
    <div class="list_articles">
        <h1 class="main-title">My history</h1>
        
        <br />

        <div class="articleIntro">
            <p>List of the last 20 publications consulted on Cairn International.</p>
        </div>
        <div class="articleBody">         
            <?php
            foreach($historiqueList as $histoArt){
                $art = $historiqueDetail[$histoArt];
                $arrayForList = array($art);
                $currentPage = 'contrib';
                switch($art['REVUE_TYPEPUB']){
                    case '1':
                        $arrayFieldsToDisplay = array('ID','NUMERO_TITLE', 'BIBLIO_AUTEURS','STATE_INTER');
                        break;
                    case '2':
                        $arrayFieldsToDisplay = array('ID','REVUE_TITLE', 'BIBLIO_AUTEURS','STATE_INTER');
                        break;
                    default:
                        $arrayFieldsToDisplay = array('ID','NUMERO_TITLE', 'BIBLIO_AUTEURS','STATE_INTER');
                        break;
                }
                include (__DIR__ . '/../CommonBlocs/liste_1col.php');
            }            
            ?>
                        
            <br /> 
            
            <?php if(empty($authInfos['U'])){
                echo 'To find this history during future visits Cairn.info, 
                     <a class="link_custom_generic" onclick="cairn.show_modal(\'#modal_login\')" href="#">Login</a>';
            }?>

            <div id="modal_login" class="window_modal" style="display: none;">
                <div class="basic_modal">
                    <h1 class="main-title">Connection</h1>
                    <br />
                    <p class="lightboxAlerte">This password is invalid</p>
                    <form id="lightboxAlerteForm" action="#" method="post">
                        <input type="hidden" value="CONNECT" name="OPERATE" />

                        <div class="blue_milk left w45">
                            <label for="email"> 
                                Your email address 
                                <span>*</span>
                            </label> 
                            <input id="LOG" class="prenom" type="text" value="" name="LOG">
                        </div>
                        <div class="blue_milk right w45">
                            <label for="mdp"> 
                                Your password 
                                <span>*</span>
                            </label> 
                            <input id="PWD" class="prenom" type="password" value="" name="PWD">
                        </div>                        
                        <br /><br /><br />                       
                        <input type="submit" class="inputButton" value="Envoyer" />                        
                        <a href="./password_forgotten.php" title="Mot de passe oublié"
                           class="link_custom">Password forgotten</a> <a
                           href="./create_account.php" title="Créer un compte"
                           class="link_custom">Signup</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>