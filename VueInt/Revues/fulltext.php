<?php $this->titre = "Journal " . $revue['TITRE'];
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="breadcrump">
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="disc-<?= $curDiscipline?>.htm"><?= $filterDiscipline?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./journal-<?php echo $revue["URL_REWRITING_EN"] ?>.htm">Journal</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Articles with English Full Text</a>
</div>

<div id="body-content">
    <div id="page_numero">
        <?php
        $modeIndex = 'fulltext';
        include(__DIR__.'/Blocs/indexRevue.php'); ?>

        <hr class="grey"/>
        <h1 class="main-title" id="summary">
            Articles with English Full Text (<?= count($articles)?>)
        </h1>

        <div class="list_articles articles_fulltext">

            <?php
            $done = -1;
            foreach ($articles as $article): $done++;
                ?>
                <?php if ($article['NUMERO_ID_NUMPUBLIE'] != '' && $article['NUMERO_ID_NUMPUBLIE'] != $articles[$done - 1]['NUMERO_ID_NUMPUBLIE']) {
                    ?>
                <div class="numero">
                    <h2><?= $article['NUMERO_ANNEE'] ?>/<?= $article['NUMERO_NUMERO'] ?><?= ($article['NUMERO_VOLUME']!=''?(' ('.$article['NUMERO_VOLUME'].')'):'')?>
                        <span class="blue petitecap"><?= $article['NUMERO_TITRE'] ?></span>
                    </h2>
                </div>
                <?php } ?>

                <hr class="separator_article">
                <div class="article"  style="padding:0">
                    <div class="pages_article">
                        <p style="position:relative;top:24px;" class="btn gray">
                            <b>Page <?= $article['ARTICLE_PAGE_DEBUT'] ?> to <?= $article['ARTICLE_PAGE_FIN'] ?></b>
                        </p>
                    </div>

                    <div class="metadata_article">
                        <p class="yellow-author2">
                            <?php
                            if($article['ARTICLE_AUTEUR'] != ''){
                                $theAuthors = explode(",", $article['ARTICLE_AUTEUR']);
                                $numItems = count($theAuthors);
                                $i = 0;
                                foreach ($theAuthors as $theAuthor):
                                    $theauthorParam = explode(':', $theAuthor);
                                    $theAutheurPrenom = $theauthorParam[0];
                                    $theAutheurNom = $theauthorParam[1];
                                    $theAutheurId = $theauthorParam[2];
                                    $theAutheurAttribut = $theauthorParam[3];
                                    if (++$i != 1) echo'<span>, </span>';
                                    if ($theAutheurAttribut != '') echo '<i class="yellow">'.$theAutheurAttribut.'</i>';
                                    echo '<a class="yellow" href="./publications-de-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'. $theAutheurPrenom . ' ' . $theAutheurNom .'</a></span>';
                                endforeach;
                            }else{
                                echo '&nbsp;';
                            }
                            ?>
                        </p>
                        <p class="mt1">
                            <?= $article['ARTICLE_TITRE']?><br>
                            <span class="subtitle"><?= $article['ARTICLE_SOUSTITRE'] ?></span>
                        </p>
                    </div>

                    <div class="state_article">
                        <?php
                        $abstract = $article['LISTE_CONFIG_ARTICLE'][0];
                        if($abstract == ''){
                            echo '<span class="button-grey2 w49 left">Abstract</span>';
                        }else{
                            echo '<a href="./abstract-'.$article['ARTICLE_ID_ARTICLE'].'--'.$article['ARTICLE_URL_REWRITING_EN'].'.htm" class="button-blue2 w49 left">Abstract</a>';
                        }
                        $french = $article['LISTE_CONFIG_ARTICLE'][1];
                        if($french == ''){
                            echo '<span class="button-grey2 w49 right">French</span>';
                        }else{
                            echo '<a href="'.Service::get('ParseDatas')->getCrossDomainUrl().'/article.php?ID_ARTICLE='.$article['ARTICLE_ID_ARTICLE_S'].'" class="button-blue2 w49 right">French</a>';
                        }
                        echo '<br>';
                        $english = trim($article['LISTE_CONFIG_ARTICLE'][2], '/');
                        if($english == ''){
                            echo '<span class="button-grey2 w100">English
                                        <span data-article-title="'.$article['ARTICLE_TITRE'].'" data-suscribe-on-translation="'.$article['ARTICLE_ID_ARTICLE'].'" class="question-mark">
                                            <span class="tooltip">Why is this article not available in English?</span>
                                        </span>
                                    </span>';
                        }else{
                            echo '<a href="./'.$english.'" class="button-blue2 w100">';

                            if(strpos($english,'my_cart.php') !== FALSE){
                                echo 'English <span class="cart-icon">'.$article['ARTICLE_PRIX'].' € </span>';
                            }else{
                                if($article['ARTICLE_PRIX'] == 0
                                    || ($article['NUMERO_MOVINGWALL'] != '0000-00-00'
                                            && $article['NUMERO_MOVINGWALL'] <= date('Ymd')
                                            && $article['ARTICLE_TOUJOURS_PAYANT'] == 0)){
                                    echo 'English : Free';
                                }else{
                                    echo 'English';
                                }
                            }
                            echo '</a>';
                        }?>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>

        <!--div class="about-numpublie">
            <h2 id="about">Fiche technique</h2>
            <p>
                <?php
                echo '<em>' . $revue["REVUE_TITRE"] . '</em> ' . $numero['NUMERO_ANNEE'] . '/' . $numero['NUMERO_NUMERO'];
                echo $numero['NUMERO_NUMEROA'] != '' ? ('-' . $numero['NUMERO_NUMEROA']) : '';
                echo ' (' . $numero['NUMERO_VOLUME'] . '). ' . $numero['NUMERO_NB_PAGE'] . '&#160;pages.
                    '.($revue['ISSN'] == ''?'':('<br />ISSN : ' . $revue['ISSN'] . '.')).'
                    '.($revue["REVUE_ISSN_NUM"]==''&& $revue["NUMERO_EAN"]==''?'':'<br />').($revue["REVUE_ISSN_NUM"] == ''?'':('ISSN en ligne : ' . $revue['REVUE_ISSN_NUM'].'. ')) . ($revue["NUMERO_EAN"] == ''?'':('ISBN&#160;: ' . $numero['NUMERO_EAN'].'.')).'
                    '.($numero['NUMERO_DOI']!=null?'<br/>DOI : '.$numero['NUMERO_DOI']:'').'
                    <br />Lien : &lt;<u>'.Service::get('ParseDatas')->getCrossDomainUrl().'/revue-' . $revue["REVUE_URL_REWRITING"] . '-' . $numero["NUMERO_ANNEE"] . '-' . $numero["NUMERO_NUMERO"] . '.htm</u>&gt;.';
                ?>
            </p>
        </div-->

    </div>

</div>
<?php include (__DIR__ . "/../CommonBlocs/invisible.php"); ?>
