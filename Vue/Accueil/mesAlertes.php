<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */

$idRevueSubscribe = array();

foreach ($revuesAlertes as $revue) {
    $idRevueSubscribe[] = $revue['ID_REVUE'];
}
foreach ($collectionsAlertes as $revue) {
    $idRevueSubscribe[] = $revue['ID_REVUE'];
}
?>
<?php $this->titre = "Mes alertes e-mail"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <div id="breadcrump_main">
        <a class="inactive" href="/">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
        <a href="mes_alertes.php">Mes alertes e-mail</a>
    </div>
    <div id="breadcrump_conf" style="display:none;">
        <a class="inactive" href="mes_alertes.php">Mes alertes e-mail</a>
        <span class="icon-breadcrump-arrow icon"></span>
        <a href="#">Lettre d'information</a>
    </div>
</div>


<div id="body-content">
    <div id="free_text" class="biblio">

        <input id="session" type="hidden" value="<?php if(isset($authInfos['U'])){ echo $authInfos['U']['EMAIL']; } ?>">

        <h1 class="main-title">Mes alertes e-mail</h1>

        <a href="http://aide.cairn.info/les-alertes-e-mail/" target="_blank"><span class="question-mark">
                <span class="tooltip">En savoir plus sur les alertes e-mail</span>
            </span></a>

        <br />

        <div class="articleBody">

            <form id="ajoutalertes" method="post" name="ajoutalertes" onSubmit="return(VerifMail(this))">
                <div class="wrapper">
                    <?php
                        if(isset($authInfos['U']))
                        {
                            echo '<div class="blue_milk left w45">'
                                 . '<label for="email"> Indiquez votre adresse e-mail </label>'
                                 . '<span class="flash "></span>'
                                 . '<input id="email" type="text" required="required" value="'.$authInfos['U']['EMAIL'].'" name="email">'
                                 . '</div>';
                        }
                        else
                        {
                            echo '<div class="blue_milk left w45">'
                                 . '<label for="email"> Indiquez votre adresse e-mail </label>'
                                 . '<span class="flash "></span>'
                                 . '<input id="email" type="text" required="required" value="" name="email">'
                                 . '</div>';
                        }
                    ?>
                </div>

                <br />
                <?php
                /*if(empty($authInfos['U']))
                {*/
                    echo
                    '<h2 class="section">Lettre d\'information</h2><br />
                    <div class="wrapper">
                        <input type="button" name="newL" id="newsL" class="white_button"
                        style="float: left; text-align: left;"
                        value="Je souhaite recevoir la lettre d’information et ainsi être tenu informé de toute l’actualité de Cairn.info."
                        onclick="return(VerifMailnews(this.form));">
                    </div>';
                //}
                ?>

                <h2 class="section">Revues</h2>
                <div class="specs"><br />
                    Je souhaite être averti(e) à chaque nouvelle parution des revues suivantes :
                </div>
                <br />

                <select name="ID_REVUE" id="ID_REVUE" onchange="return(VerifMailrev(this.form));">
                    <option class="ital" selected value="">Choisir la revue...</option>
                    <?php foreach ($revues as $revue): ?>
                        <?php if (in_array($revue['ID_REVUE'], $idRevueSubscribe)) { continue; } ?>
                        <option
                            url="<?php echo $revue['URL_REWRITING'] ?>"
                            value="<?php echo $revue['ID_REVUE'] ?>">
                                <?php echo $revue['TITRE'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <br />
                <br />

                <div id="revues-list" class="list_articles">
                <?php
                if (isset($authInfos['U'])) {
                    foreach ($revuesAlertes as $revueAlerte)
                    {
                        echo '
                        <div id="'.$revueAlerte['ID_ALERTE'].'" class="article greybox_hover">
                            <a href = "revue-'.$revueAlerte['URL_REWRITING'].'.htm">
                                <img src = "/'.$vign_path.'/'.$revueAlerte['ID_ALERTE'].'/'.$revueAlerte['ID_NUMPUBLIE'].'_L204.jpg" alt = "" class = "small_cover">
                            </a>
                            <div class = "meta">
                                <div class = "revue_title">
                                    <h2 class = "title_little_blue numero_title">
                                        <a href = "revue-'.$revueAlerte['URL_REWRITING'].'.htm">'.$revueAlerte['TITRE'].'</a>
                                    </h2>
                                    <div class = "editeur">'.$revueAlerte['NOM_EDITEUR'].'</div>
                                </div>
                                <div class = "state">
                                    <img style="cursor: pointer;" class = "right" type = "image" src = "http://cairn.info/img/del.png" onclick = "removeAlert('.$revueAlerte['ID_ALERTE'].');" alt = "Supprimer l\'alerte sur la revue « '.$revueAlerte['TITRE'].' »">
                                </div>
                            </div>
                        </div>';
                    }
                }
                ?>
                </div>

                <!--<h2 class="section">Collections</h2>
                <div class="specs">
                    <p class="intro ">Je souhaite être averti(e) à chaque nouvelle
                        parution d'un ouvrage dans les collections suivantes :</p>
                </div>

                <br />

                <select name="ID_COLL" id="ID_COLL"
                        onchange="return(VerifMailcoll(this.form));">
                    <option class="ital" selected value="">Choisir la collection...</option>
                    <?php foreach ($collections as $collection): ?>
                        <?php if (in_array($collection['ID_REVUE'], $idRevueSubscribe)) { continue; } ?>
                        <option
                            url="<?php echo $collection['URL_REWRITING'] ?>"
                            value="<?php echo $collection['ID_REVUE'] ?>">
                                <?php echo $collection['TITRE'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <br />
                <br />-->

                <div id="collections-list" class="list_articles">
                <?php
                if (isset($authInfos['U'])) {
                    foreach ($collectionsAlertes as $collectionAlerte) {
                        echo '
                        <div id="'.$collectionAlerte['ID_ALERTE'].'" class="article greybox_hover">
                            <a href = "collection-'.$collectionAlerte['URL_REWRITING'].'.htm">
                                <img src = "/'.$vign_path.'/'.$collectionAlerte['ID_ALERTE'].'/'.$collectionAlerte['ID_NUMPUBLIE'].'_L204.jpg" alt = "" class = "small_cover">
                            </a>
                            <div class = "meta">
                                <div class = "revue_title">
                                    <h2 class = "title_little_blue numero_title">
                                        <a href = "collection-'.$collectionAlerte['URL_REWRITING'].'.htm">'.$collectionAlerte['TITRE'].'</a>
                                    </h2>
                                    <div class = "editeur">'.$collectionAlerte['NOM_EDITEUR'].'</div>
                                </div>
                                <div class = "state">
                                    <img style="cursor: pointer;" class = "right" type = "image" src = "http://cairn.info/img/del.png" onclick = "removeAlert('.$collectionAlerte['ID_ALERTE'].');" alt = "Supprimer l\'alerte sur la revue « '.$collectionAlerte['TITRE'].' »">
                                </div>
                            </div>
                        </div>';
                    }
                }
                ?>
                </div>

            </form>

            <?php
            if(empty($authInfos['U']))
            {
                echo '<div class="articleIntro">
                        <h2>Pour gérer vos alertes e-mail, vous devez disposer d\'un compte Cairn.info.</h2>
                        <br />
                        <p>Si vous disposez d\'un compte Cairn.info, identifiez-vous&#160;: <a href="./connexion.php" class="acceder link_custom_generic">S&rsquo;identifier</a></p>
                        <p>Si vous ne disposez pas de compte Cairn.info, créez-en un&#160;: <a href="./creer_compte.php" class="acceder link_custom_generic">Créer mon compte</a></p>
                    </div>';
            }
            ?>
        </div>
    </div>

    <div id="confirm_text" class="biblio" style="display:none;">

        <input id="session" type="hidden" value="<?php if(isset($authInfos['U'])){ echo $authInfos['U']['EMAIL']; } ?>">

        <h1 class="main-title">Confirmation d'inscription</h1>

        <a href="http://aide.cairn.info/les-alertes-e-mail/" target="_blank"><span class="question-mark">
                <span class="tooltip">En savoir plus sur les alertes e-mail</span>
            </span></a>

        <br />

        <div class="specs"><br>
            Votre demande d'inscription a bien été prise en compte. Vous recevrez la prochaine lettre d'information.
        </div>
    </div>

<?php include (__DIR__ . '/mesAlertesPopups.php');?>

