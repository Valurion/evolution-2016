<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "Mes recherches"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
    <a href="mes_recherches.php">Mes recherches</a>
</div>

<div id="body-content">
    <div id="free_text">

        <h1 class="main-title">Mes recherches</h1>

        <div class="articleIntro">
            <p>Vos 100 dernières recherches effectuées sur Cairn.info.</p>
        </div>

        <div class="articleBody">
            <ul class="links_list">
                <?php
                    if(isset($authInfos['U']))
                    {
                        foreach($authInfos['U']['HISTO_JSON']->recherches as $userTopic)
                        {
                            echo '<li>
                                    <a href="resultats_recherche.php?searchTerm='.urlencode($userTopic[0]).'">
                                        <span class="icon icon-arrow-blue-right"></span><span class="title_little_blue">'.htmlentities($userTopic[0]).'</span>
                                    </a>('.$userTopic[1].')
                                </li>';
                        }
                    }
                    elseif(isset($authInfos['G']) && isset($authInfos['G']['HISTO_JSON']->recherches))
                    {
                        foreach($authInfos['G']['HISTO_JSON']->recherches as $userTopic)
                        {
                            echo '<li>
                                    <a href="resultats_recherche.php?searchTerm='.urlencode($userTopic[0]).'">
                                        <span class="icon icon-arrow-blue-right"></span><span class="title_little_blue">'.htmlentities($userTopic[0]).'</span>
                                    </a>('.$userTopic[1].')
                                </li>';
                        }
                    }
                ?>

            </ul>
            <?php if(!isset($authInfos['U'])) { ?>
                <p>
                    Pour retrouver cet historique lors de vos prochaines visites sur Cairn.info, <a href="creer_compte.php">inscrivez-vous</a> ou <a href="connexion.php">connectez-vous</a>.
                </p>
            <?php } ?>
        </div>
    </div>
</div>
