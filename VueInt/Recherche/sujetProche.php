<?php
$this->titre = 'You may be interested in';
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function formatrewriting($chaine) {
    //les accents
    //$chaine=  strtolower(trim($chaine));

    $chaine = remove_accents($chaine);
    $chaine = strtolower(trim($chaine));
    //les caracètres spéciaux (aures que lettres et chiffres en fait)
    $chaine = preg_replace('/([^.a-z0-9]+)/i', '-', $chaine);
    if (substr($chaine, 0, 6) == 'revue-')
        $chaine = substr($chaine, 6);
    return $chaine;
}
?>

<div id="body-content">
    <a name="top"></a>
    <div id="free_text" class="biblio">
        <h1 class="main_title"><?= $titre ?></h1>
        <span class="soustitre"></span>
        <div class="memo-numpublie">
            <h2 id="memo">You may be interested in</h2>
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

        <div class="CB"></div>
    </div>
</div>


