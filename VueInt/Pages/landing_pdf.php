<?php
    $this->titre = "Download of PDF file";
    include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="breadcrump">
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./disc-<?= $curDiscipline ?>.htm"><?= $filterDiscipline?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./journal-<?php echo $revue["URL_REWRITING_EN"] ?>.htm">Journal</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./journal-<?php echo $revue["URL_REWRITING_EN"] ?>-<?php echo $numero["NUMERO_ANNEE"]; ?>-<?php echo $numero["NUMERO_NUMERO"]; ?>.htm">Issue</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">PDF</a>
</div>

<div id="body-content" class="">
    <h1 class="main-title">Article now downloading</h1>

    <div class="landing_pdf">
        <p class="text_medium">
            <a class="title" href="./article.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>">
                <strong><?= $currentArticle['ARTICLE_TITRE'] ?></strong>
            </a>
        </p>
        <?php //if ($currentArticle['ARTICLE_SOUSTITRE']) { ?>
            <p class="text_medium"><i><?= $currentArticle['ARTICLE_SOUSTITRE'] ?></i></p>
        <?php //} ?>
        <p class="auteurs">
            <span class="yellow bold">by </span>
            <?php
                // Formatage des auteurs
                $liste   = array();
                $auteurs = Service::get('ParseDatas')->stringifyRawAuthors($currentArticle['ARTICLE_AUTEUR'], 0, null, null, null, true, ',', ':');

                // Tableau des auteurs
                $auteurs = explode(",", $auteurs);

                // Parcours
                foreach($auteurs as $auteur) {
                    // Découpage
                    $auteur = explode(":", $auteur);

                    // Ajout au tableau
                    $liste[] = "<span class=\"auteur\">".$auteur[0]."</span>";
                }
                $auteurs = implode(",", $liste);
            ?>
            <?= $auteurs; ?>
        </p>
        <p class="text-center">
            <img class="big_coverbis" style="margin: 40px auto;" src="/<?= $vign_path ?>/<?= $currentArticle["ARTICLE_ID_REVUE"]; ?>/<?= $currentArticle["ARTICLE_ID_NUMPUBLIE"]; ?>_H310.jpg" />
        </p>

        <p class="text-center">
            Your download will start automatically.<br />
            <span class="yellow">Please <a class="link-underline yellow" href="./load_pdf_do_not_index.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE']; ?>">click here</a> if it does not start automatically.</span>
        </p>
    </div>

    <?php
        /* 
         * Cette partie du code a été récupérée depuis la vue sujetProche, et adaptée pour fonctionner ici 
         * Les valeurs nécessaires se trouvent dans une variable $sujetProche
         */

        // Récupération des variables nécessaires
        $metaNumero         = $sujetProche["metaNumero"];
        $Revues             = $sujetProche["Revues"];
        $typepub            = $sujetProche["typepub"];
        $searchTerm         = $sujetProche["searchTerm"];
        $typeDocument       = $sujetProche["typeDocument"];
        $articlesButtons    = $sujetProche["articlesButtons"];
        $portalInfo         = $sujetProche["portalInfo"];
        $typePubCurrent     = $sujetProche["typePubCurrent"];
        $ParseDatas         = Service::get('ParseDatas');
        $typePub            = $typePubCurrent == 1?'revue':($typePubCurrent == 2?'magazine':($typePubCurrent==3?'ouvrage':'encyclopedie'));
    ?>

    <?php if( (sizeof($Revues) > 0) ) { ?>
    <div id="free_text" class="biblio mt3">

        <div class="memo-numpublie">
            <h2 id="memo">You may also be interested in the following articles:</h2>
        </div>

        <div class="list_articles">
            
            <?php if (sizeof($Revues) > 0) : ?>     
             
                <h2 class="section">
                    <span>Journal articles</span>
                </h2>
             
                 <?php foreach ($Revues as $result) : ?>
                    <?php
                    //$typePubTitle = $typeDocument[$pack][$offset];
                    //$typePub = $result->userFields->tp;
                    $typePub = 1;
                    $typeNumPublie = $result->userFields->tnp;
                    $ARTICLE_ID_ARTICLE = $result->userFields->id;
                    $ARTICLE_ID_REVUE = $result->userFields->id_r;
                    $NUMERO_ID_REVUE = $ARTICLE_ID_REVUE;
                    $ARTICLE_PRIX = $result->userFields->px;

                    $ARTICLE_ID_NUMPUBLIE = $result->userFields->np;
                    $NUMERO_ID_NUMPUBLIE = $ARTICLE_ID_NUMPUBLIE;
                    $ARTICLE_HREF = "journal-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd . ".htm";
                    $ARTICLE_TITRE = $result->userFields->tr;
                    $NUMERO_TITRE = $result->userFields->titnum;
                    $NUMERO_SOUS_TITRE = $metaNumero[$NUMERO_ID_NUMPUBLIE]['SOUS_TITRE'];
                    $REVUE_ID = $result->userFields->id_r;
                    $authors = explode('|', $result->userFields->auth0);
                    $NUMERO_ANNEE = $result->userFields->an;
                    $NUMERO_NUMERO = $result->userFields->NUM0;
                    $NUMERO_VOLUME = $result->userFields->vol;
                    $ARTICLE_PAGE = $result->userFields->pgd;

                    $NOM_EDITEUR = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NOM_EDITEUR'];
                    $REVUE_TITRE = $result->userFields->rev0;
                    $cfgaArr = explode(',', $result->userFields->cfg0);


                    $NUMERO_MEMO = substr($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'], 0, strpos($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'], " ", 200));
                    $CONTEXTE = strip_tags($result->item->Synopsis, '<b>');

                    $DOCID = $result->item->docId;
                    
                    $ARTICLE_HREF = '';
                    $NUMERO_HREF = '';
                    $REVUE_HREF = "";
                    switch ($typePub) {
                        case "1":
                            $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE";
                            $NUMERO_HREF = "./journal-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                            $REVUE_HREF;
                            $REVUE_HREF = "./journal-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                            break;
                        /*case "2":
                            $ARTICLE_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
                            $NUMERO_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                            $REVUE_HREF;
                            $REVUE_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                            break;
                        case "3":

                            $ARTICLE_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
                            $NUMERO_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . ".htm";
                            $REVUE_HREF = "./collection-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                            break;

                        case "6":

                            $ARTICLE_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
                            $NUMERO_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . ".htm";
                            $REVUE_HREF = "./collection-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                            break;*/
                    }


                    $BLOC_AUTEUR = '';
                    if (sizeof($authors) > 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-of-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> et al.";
                    } else if (sizeof($authors) == 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-of-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> et ";
                        $authors2 = explode('#', $authors[1]);
                        $BLOC_AUTEUR .= "<a class=\"yellow\" href=\"publications-of-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> ";
                    } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-of-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a>";
                    }

                    $BLOC_AUTEUR = trim($BLOC_AUTEUR);
                    ?>
                <div class="article greybox_hover">
                    <div class="pages_article vign_small">
                        <a  href="./<?= $urlRev ?>.htm">
                        <img src="/<?= $vign_path ?>/<?=$ARTICLE_ID_REVUE?>/<?=$ARTICLE_ID_NUMPUBLIE?>_L62.jpg" alt="couverture de [NUMERO_TITRE_ABREGE]" class="small_cover">
                        </a>
                    </div>
                    <div class="metadata_article">
                        <div class="title">
                            <a href="<?=$ARTICLE_HREF?>"><strong> <span class="subtitle"></span>
                                    <?=$ARTICLE_TITRE?>
                                     <span class="subtitle"></span>                                    
                                </strong></a>
                        </div>
                        <div class="authors">
                            <?=$BLOC_AUTEUR?>
                        </div>
                        <div class="revue_title">
                            In <a href="<?=$REVUE_HREF?>"><span class="title_little_blue"><?=$REVUE_TITRE?></span> <strong><?=$NUMERO_ANNEE?>/<?=$NUMERO_NUMERO?>

                                </strong></a>
                        </div>
                    </div>    
                    <div class="state_article">
                        <?php 
                        $abstract = $articlesButtons[$ARTICLE_ID_ARTICLE][0];
                        if($abstract == ''){
                            echo '<span class="button-grey2 w49 left">Abstract</span>';
                        }else{
                            echo '<a href="abstract-'.$ARTICLE_ID_ARTICLE.'--'.$articlesButtons[$ARTICLE_ID_ARTICLE][4].'.htm" class="button-blue2 w49 left">Abstract</a>';
                        }
                        $french = $articlesButtons[$ARTICLE_ID_ARTICLE][1];
                        if($french == ''){
                            echo '<span class="button-grey2 w49 right">French</span>';
                        }else{
                            echo '<a href="'.Service::get('ParseDatas')->getCrossDomainUrl().'/article.php?ID_ARTICLE='.$articlesButtons[$ARTICLE_ID_ARTICLE][5].'" class="button-blue2 w49 right">French</a>';
                        }
                        echo '<br>';
                        $english = $articlesButtons[$ARTICLE_ID_ARTICLE][2];
                        if($english == ''){
                            echo '<span class="button-grey2 w100">English
                                        <span data-article-title="'.$ARTICLE_TITRE.'" data-suscribe-on-translation="'.$ARTICLE_ID_ARTICLE.'" class="question-mark">
                                            <span class="tooltip">Why is this article not available in English?</span>
                                        </span>
                                    </span>';
                        }else{
                            echo '<a href="'.$english.(strpos($english,'my_cart.php')===FALSE?('?'.$getDocUrlParameters):'').'" class="button-blue2 w100">';

                            if(strpos($english,'my_cart.php') !== FALSE){
                                echo 'English <span class="cart-icon">'.$articlesButtons[$ARTICLE_ID_ARTICLE][3].' € </span>';
                            }else{
                                if($articlesButtons[$ARTICLE_ID_ARTICLE][3] == 0){
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
            <?php endif; ?>
             <br />            
           

        </div>

        <hr class="grey" />
        <p>
            <a href="./see_also.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>">
                View all related articles <span class="icon-arrow-black-right icon"></span>
            </a>
        </p>

    </div>
    <?php } ?>
</div>

<?php
//$this->javascripts[] = 'window.location = "./load_pdf.php?download=1&ID_ARTICLE=' . $currentArticle['ARTICLE_ID_ARTICLE'] . '";';
$this->javascripts[] = 'setTimeout(function() {window.location = "./load_pdf_do_not_index.php?ID_ARTICLE='
    .$currentArticle['ARTICLE_ID_ARTICLE']
    .'"}, 1000);';
?>