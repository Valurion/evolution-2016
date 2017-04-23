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
<?php $this->titre = "My alerts"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>


<div id="breadcrump">
    <a class="inactive" href="/">Home</a> <span class="icon-breadcrump-arrow icon"></span>
    <a href="my_alerts.php">My alerts</a>
</div>

<div id="body-content">
    <div id="free_text" class="biblio">

        <input id="session" type="hidden" value="<?php if(isset($authInfos['U'])){ echo $authInfos['U']['EMAIL']; } ?>">

        <h1 class="main-title">My alerts</h1>

        <br />

        <div class="articleBody">

            <form id="ajoutalertes" method="post" name="ajoutalertes" onSubmit="return(VerifMail(this))">
                <div class="wrapper">
                    <?php
                        if(isset($authInfos['U']))
                        {
                            echo '<h2>Sign up for the newsletter</h2>'
                                 . '<input type="checkbox">'
                                 . '<span>I want to receive Cairn.info\'s newsletter and receive updates directly in my inbox.</span>'
                                 . '<input id="email" type="hidden" required="required" value="'. $authInfos['U']['EMAIL'] .'" name="email">';
                        }
                        else
                        {
                            echo '<div class="blue_milk left w45">'
                                 . '<label for="email">Enter your email address </label>'
                                 . '<span class="flash "></span>'
                                 . '<input id="email" type="text" required="required" value="" name="email">'
                                 . '</div>';
                        }
                    ?>
                </div>

                <br />
                <?php
                if(empty($authInfos['U']))
                {
                    echo
                    '<h2 class="section">Newsletter</h2><br />
                    <div class="wrapper">
                        <input type="button" name="newL" id="newsL" class="white_button"
                        style="float: left; text-align: left;"
                        value="I want to receive Cairn.info\'s newsletter and receive updates directly in my inbox."
                        onclick="return(VerifMailnews(this.form));">
                    </div>';
                }
                ?>

                <h2 class="section">Table of Contents Alert</h2>
                <div class="specs"><br />
                    I want to be notified for each new release of the following journals:
                </div>
                <br />

                <select name="ID_REVUE" id="ID_REVUE" onchange="return(VerifMailrev(this.form));">
                    <option class="ital" selected value="">Choose the journal ...</option>
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
                            <a href = "journal-'.$revueAlerte['URL_REWRITING'].'.htm">
                                <img src = "/'.$vign_path.'/'.$revueAlerte['ID_ALERTE'].'/'.$revueAlerte['ID_NUMPUBLIE'].'_L62.jpg" alt = "" class = "small_cover">
                            </a>
                            <div class = "meta">
                                <div class = "revue_title">
                                    <h2 class = "title_little_blue numero_title">
                                        <a href = "journal-'.$revueAlerte['URL_REWRITING'].'.htm">'.$revueAlerte['TITRE'].'</a>
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


            </form>

            <?php
            if(empty($authInfos['U']))
            {
                echo '<div class="articleIntro">
                        <h2>To manage your email alerts, you must have a Cairn.info account.</h2>
                        <br />
                        <p>If you have a Cairn.info account, please login: <a href="./connexion.php" class="acceder link_custom_generic">Login</a></p>
                        <p>If you do not have a Cairn.info account, create one: <a href="./creer_compte.php" class="acceder link_custom_generic">Sign up</a></p>
                    </div>';
            }
            ?>
        </div>
    </div>
<?php include (__DIR__ . '/mesAlertesPopups.php'); ?>
