<?php
/**
 *
 *  
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "My Searches"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
    <a href="my_searches.php">My Searches</a>
</div>

<div id="body-content">
    <div id="free_text">

        <h1 class="main-title">My Searches</h1>

        <div class="articleIntro">
            <p>Your last 20 searches performed on Cairn International.</p>
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
                                        <span class="icon icon-arrow-blue-right"></span><span class="title_little_blue">'.$userTopic[0].' ('.$userTopic[1].')</span>
                                    </a>
                                </li>';
                        }
                    }
                    elseif(isset($authInfos['G']))
                    {
                        foreach($authInfos['G']['HISTO_JSON']->recherches as $userTopic)
                        {
                            echo '<li>
                                    <a href="resultats_recherche.php?searchTerm='.urlencode($userTopic[0]).'">
                                        <span class="icon icon-arrow-blue-right"></span><span class="title_little_blue">'.$userTopic[0].' ('.$userTopic[1].')</span>
                                    </a>
                                </li>';
                        }
                    }
                ?>
                
            </ul>
            <?php
                
            if(!isset($authInfos['U'])){?>
                <p>
                    To find this history during your next visits Cairn International, create a <a href="creer_compte.php">new account</a> or <a href="connexion.php">log in</a>.
                </p>
            <?php } ?> 
        </div>
    </div>
</div>