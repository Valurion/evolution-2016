<?php
/**
 * Dedicated View [Coupled with the default method of the controler]
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "Your gateway to the francophone social sciences and humanities";
include (__DIR__ . '/../CommonBlocs/tabs.php');

$arrayExcludeDisc = array();
if(isset($authInfos['I']) && $authInfos['I']['PARAM_INST'] !== false && isset($authInfos['I']['PARAM_INST']['D'])){
   $arrayExcludeDisc = explode(',', $authInfos['I']['PARAM_INST']['D']);
}

/*
 * Modification septembre 2016 : la page d'accueil peut être appelée avec un paramètre shib=1
 * Cela permet de déclencher une authentification sur la CorsUrl au retour d'un login Shibboleth...
 */

if(isset($corsURL) && isset($token) && $token != null){
    echo '<img style="display:none;" src="http://'.$corsURL.'/index.php?controleur=User&action=loginCorsShib&token='.urlencode($token).'"/>';
}
?>

<div id="body-content">

    <?php if (isset($revues)): ?>
        <div id="list_revue_suscriber">
            <br/><br/>
            <?php $x = 1; ?>
            <?php foreach ($revues as $revue): ?>
                <?php $x++; ?>
                <?php if (($x % 2) == 0): ?>

                    <div class="grid-g grid-2-list">
                    <?php endif; ?>
                    <div class="grid-u-1-2 greybox_hover revue">
                        <a  href="./journal-<?= $revue['URL_REWRITING'] ?>.htm">
                            <img src="/<?= $vign_path ?>/<?= $revue['ID_REVUE'] ?>/<?= $revue['ID_NUMPUBLIE'] ?>_L61.jpg" alt="couverture de [NUMERO_TITRE_ABREGE]" class="small_cover">
                        </a>
                        <div class="meta">
                            <h2 class="title_little_blue numero_title"><a  href="./journal-<?= $revue['URL_REWRITING'] ?>.htm"><?= $revue['TITRE'] ?></a></h2>
                            <div class="editeur">
                                <!-- Snippet pour la période de test SHS (début 01/04/2014) -->
                                <?= $revue['NOM_EDITEUR'] ?>
                            </div>
                        </div>
                    </div>
                    <?php if (($x % 2) == 1): ?>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
            <?php if (($x % 2) == 0): ?>
            </div>
        <?php endif; ?>

    </div>
<?php else: ?>
    <!-- NUMEROS RECENTS -->
    <div id="numeros-recents">
        <h1 class="main-title mt2">Recently added - Issues</h1>

        <!-- Set up your HTML -->
        <div class="owl-carousel owl-numeros-recents">
            <?php
                // Init
                $item = "";

                // Boucle
                foreach ($lastpubs as $lastpub) {
                    $item .= "<div class=\"owl-item\">
                                <a href=\"revue-".$this->nettoyer($lastpub['URL_REWRITING'])."-".$this->nettoyer($lastpub['ANNEE'])."-".$this->nettoyer($lastpub['NUMERO']).".htm\">
                                    <img src=\"/".$vign_path."/".$this->nettoyer($lastpub['ID_REVUE'])."/".$this->nettoyer($lastpub['ID_NUMPUBLIE'])."_H310.jpg\" alt=\"couverture de ".$lastpub['ID_NUMPUBLIE']."\" />
                                </a>
                                <h2>".$this->nettoyer($lastpub['TITRE_ABREGE'])."</h2>
                                <h3>".$this->nettoyer(strip_tags($lastpub['TITRE']))."</h3>
                                <p>".$this->nettoyer($lastpub['ANNEE'])."/".$this->nettoyer($lastpub['NUMERO'])." ".$this->nettoyer($lastpub['VOLUME'])."</p>
                              </div>";
               }
               echo $item;
            ?>
        </div>

        <a href="./listrev.php" class="more-journals">More Journals</a><br/>
    </div>

<?php endif; ?>
<?php if (isset($lastArts)): ?>
    <!--div class="homepage-section"-->
        <hr class="grey" style="margin-top:0px;">
        <h1 class="main-title">Recently Added &ndash; Articles</h1>
        <div class="homepage-section-content">
            <ul>
            <?php foreach ($lastArts as $lastArt): ?>
                <li style="margin-bottom: 1em; position: relative; height: 58px;" class="article_recent">
                    <a class="recent-article-title" href="article-<?= $lastArt['ARTICLE_ID_ARTICLE']?>--<?= $lastArt['ARTICLE_URL_REWRITING_EN']?>.htm"><?= $lastArt['ARTICLE_TITRE']?><br>
                        <span style="position : relative; top : 0.4em;" class="wrapper_name_revue">
                            <span class="italic">in</span>
                            <span class="home-article-category"><?= $lastArt['REVUE_TITRE']?></span>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
	<!--/div-->
        <hr style="margin-top:0px;" class="grey">
    <?php endif; ?>
    </div>
</div>
<div class="homepage-section bottom-box">
    <div class="homepage-section-content">
        <div class="welcome-box">
            <div class="welcome-box-item">
                <div class="welcome-box-text">
                    <h2 class="welcome-title">Welcome!</h2>
                    <p class="welcome-text-p">
                        Founded in 2005 by four Belgian and French academic publishers, Cairn.info offers the most comprehensive online collection of francophone publications in social sciences and humanities.
                    </p>
                </div>
            </div>
        </div>

        <div class="welcome-box">
            <div class="welcome-box-item">
                <div class="welcome-box-text">
                    <h2>What is Cairn?</h2>
                    <p>
                        In 2017, more than 450 journals and 8.000 eBooks (representing over 300.000 articles/chapters) from major French, Belgian and Swiss publishers can be accessed by students, scholars and librarians worldwide on <a class="inline-link" href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>">www.cairn.info</a>.
                    </p>
                    <!--<a class="more" href="#">More...</a>-->
                </div>
            </div>
        </div>

        <div class="welcome-box">
            <div class="welcome-box-item">
                <div class="welcome-box-text">
                    <h2>Why an international edition?</h2>
                    <p>
                        Filled with translated abstracts and articles from key French-language journals, Cairn International Edition is the perfect gateway to francophone academia for non-French speakers.
                    </p>
                    <!--<a class="more" href="#">More...</a>-->
                </div>
            </div>
        </div>

        <div class="welcome-box">
            <div class="welcome-box-item">
                <div class="welcome-box-text">
                    <h2>Get Full Access to Cairn.Info!</h2>
                    <p>Different options can be chosen by institutions to provide access to their community (i.e. teachers, researchers, students or walk-in users) to the different "Bouquets" or packages of journals in humanities and social sciences set up by Cairn.info.</p>
                    <!--<a class="more" href="#">More...</a>-->
                </div>
            </div>
        </div>
    </div>
</div>


<?php $this->javascripts[] = <<<'EOD'
    
    $('.owl-carousel').owlCarousel({
        items : 5,
        slideBy: 5,
        loop : true,
        nav : true,
        margin : 20,
        mouseDrag : false
    });
EOD;
?>
