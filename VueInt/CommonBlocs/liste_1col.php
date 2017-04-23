<!--
Ce template sert à l'affichage d'une liste à 2 colonnes.
Il s'attend à recevoir:
    - $arrayForList = l'array qui contient les données, comprenant le champ TYPEPUB
    - $arrayFieldsToDisplay = un array qui contient les champs à afficher. Par défaut, seul le titre et l'image s'affichent
-->

<?php if (isset($arrayForList)) { ?>
    <?php
    foreach ($arrayForList as $row) {
        if ($row['REVUE_TYPEPUB'] == '1') {
            if($currentPage == 'numero'){
                $url = 'journal-' . $row['REVUE_URL_REWRITING_EN'] . '-' . $row["NUMERO_ANNEE"] . '-' . $row["NUMERO_NUMERO"];
                $urlRev = 'journal-' . $row['REVUE_URL_REWRITING_EN'];
                $titre = $row['NUMERO_TITRE'];
                $soustitre = $row['NUMERO_SOUSTITRE'];
            }else{
                $url = 'article-' . $row['ARTICLE_ID_ARTICLE'] . '--' . $row["ARTICLE_URL_REWRITING_EN"];
                $urlRev = 'journal-' . $row['REVUE_URL_REWRITING_EN'];
                $titre = $row['ARTICLE_TITRE'];
                $soustitre = $row['ARTICLE_SOUSTITRE'];
            }
        }

        ?>
        <div <?= in_array("ID", $arrayFieldsToDisplay)?('id="'.$row['NUMERO_ID_NUMPUBLIE'].'-'.$row['ARTICLE_ID_ARTICLE'].'"'):'' ?> class="greybox_hover article">

            <div class="pages_article vign_small">
                <a  href="./<?= $url ?>.htm">
                <img src="/<?= $vign_path ?>/<?= $row['REVUE_ID_REVUE'] ?>/<?= $row['NUMERO_ID_NUMPUBLIE'] ?>_L62.jpg" alt="couverture de [NUMERO_TITRE_ABREGE]" class="small_cover">
                </a>
            </div>
            <div class="metadata_article">
                <div class="title"><span class="bullet">
                        <a  href="./<?= $url ?>.htm"><b><?= $titre ?><span class="subtitle"><?= $soustitre ?></span></b></a>
                    </span></div>

                <?php if (in_array("BIBLIO_AUTEURS", $arrayFieldsToDisplay)) {
                    $theAuthors = explode(',',$row['BIBLIO_AUTEURS']);
                    $str = "";
                    foreach ($theAuthors as $theAuthor){
                        $theauthorParam = explode(':', $theAuthor);
                        $theAutheurPrenom = $theauthorParam[0];
                        $theAutheurNom = $theauthorParam[1];
                        $theAutheurId = $theauthorParam[2];
                        $str .= ($str != '' ?', ':'');
                        $str .= '<span class="author"><a class="yellow" href="./publications-of-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'.$theAutheurPrenom.' '.$theAutheurNom.'</a></span>';
                    }
                    echo '<div class="authors">'.$str.'</div>';
                }?>
                <?php if (in_array("COLL_TITLE", $arrayFieldsToDisplay)) { ?>
                    <div class="revue_title">Coll. <span class="title_little_blue"><a class="title_little_blue" href="./collection.php?ID_REVUE=<?= $row["REVUE_ID_REVUE"] ?>"><?= $row['REVUE_TITRE'] ?></a></span>
                        <b>(<?= $row['EDITEUR_NOM_EDITEUR'] ?>, <?= $row['NUMERO_ANNEE'] ?>)</b>
                    </div>
                <?php } ?>
                <?php if (in_array("NUMERO_TITLE", $arrayFieldsToDisplay)) { ?>
                    <div class="revue_title">in <span class="title_little_blue"><a class="title_little_blue" href="./<?= $urlRev ?>.htm"><?= $row['NUMERO_TITRE'] ?></a></span>
                        <b>(<?= $row['EDITEUR_NOM_EDITEUR'] ?>, <?= $row['NUMERO_ANNEE'] ?>)</b>
                    </div>
                <?php } ?>
                <?php if (in_array("REVUE_TITLE", $arrayFieldsToDisplay)) { ?>
                    <div class="revue_title">in <span class="title_little_blue"><a class="title_little_blue" href="./<?= $urlRev ?>.htm"><?= $row['REVUE_TITRE'] ?></a></span>
                        <?= $row['NUMERO_ANNEE'] ?>/<?= $row['NUMERO_NUMERO'] ?> (<?= $row['NUMERO_VOLUME'] ?>)
                    </div>
                <?php } ?>

            </div>
            <div class="state_article">
                <?php if (in_array("PRIX", $arrayFieldsToDisplay)) {?>
                <div class="prix_inter">
                    <strong><span id="price-<?= $row['NUMERO_ID_NUMPUBLIE'].'-'.$row['ARTICLE_ID_ARTICLE']?>"><?= $row['ARTICLE_PRIX'] ?></span> €</strong>
                </div>
                <?php } ?>
                <?php if (in_array("STATE_OUV", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <a class="button" href="./<?= $url ?>.htm">Présentation/Sommaire</a>
                        <?php if(in_array("REMOVE_BIBLIO", $arrayFieldsToDisplay)){
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($row['REVUE_ID_REVUE'],$row['NUMERO_ID_NUMPUBLIE'], $row['ARTICLE_ID_ARTICLE'],null,'remove');
                        } ?>
                    </div>
                <?php } ?>
                <?php if (in_array("STATE", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <?php foreach ($row["LISTE_CONFIG_ARTICLE"] as $listeConfigArt) { ?>
                            <a
                                class="<?= (!isset($listeConfigArt['CLASS'])?'button':$listeConfigArt['CLASS'])?>"
                                href="<?= $listeConfigArt["HREF"] ?>"
                                <?php if (strpos($listeConfigArt['HREF'], 'load.pdf') !== -1): ?>
                                    data-webtrends="goToPdfArticle"
                                    data-id_article="<?= $row['ARTICLE_ID_ARTICLE'] ?>"
                                    data-titre=<?=
                                        Service::get('ParseDatas')->cleanAttributeString($row['ARTICLE_TITRE'])
                                    ?>
                                <?php endif; ?>
                            ><?= $listeConfigArt["LIB"] ?></a>
                            <?php
                        }
                        ?>
                        <?php if(in_array("REMOVE_BIBLIO", $arrayFieldsToDisplay)){
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($row['REVUE_ID_REVUE'],$row['NUMERO_ID_NUMPUBLIE'], $row['ARTICLE_ID_ARTICLE'],null,'remove');
                        } else {
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($row['REVUE_ID_REVUE'],$row['NUMERO_ID_NUMPUBLIE'], $row['ARTICLE_ID_ARTICLE'],$authInfos);
                        } ?>
                    </div>
                <?php } ?>
                <?php if (in_array("STATE_INTER", $arrayFieldsToDisplay)) { ?>
                        <?php
                        $abstract = $row['LISTE_CONFIG_ARTICLE'][0];
                        if($abstract == ''){
                            echo '<span class="button-grey2 w49 left">Abstract</span>';
                        }else{
                            echo '<a href="./abstract-'.$row['ARTICLE_ID_ARTICLE'].'--'.$row['ARTICLE_URL_REWRITING_EN'].'.htm" class="button-blue2 w49 left">Abstract</a>';
                        }
                        $french = $row['LISTE_CONFIG_ARTICLE'][1];
                        if($french == ''){
                            echo '<span class="button-grey2 w49 right">French</span>';
                        }else{
                            echo '<a href="'.Service::get('ParseDatas')->getCrossDomainUrl().'/article.php?ID_ARTICLE='.$row['ARTICLE_ID_ARTICLE_S'].'" class="button-blue2 w49 right">French</a>';
                        }
                        echo '<br>';
                        $english = $row['LISTE_CONFIG_ARTICLE'][2];
                        if($english == ''){
                            echo '<span class="button-grey2 w100">English
                                        <span data-article-title="'.$row['ARTICLE_TITRE'].'" data-suscribe-on-translation="'.$row['ARTICLE_ID_ARTICLE'].'" class="question-mark">
                                            <span class="tooltip">Why is this article not available in English?</span>
                                        </span>
                                    </span>';
                        }else{
                            echo '<a href="./'.$english.'" class="button-blue2 w100">';

                            if(strpos($english,'my_cart.php') !== FALSE){
                                echo 'English <span class="cart-icon">'.$row['ARTICLE_PRIX'].' € </span>';
                            }else{
                                if($row['ARTICLE_PRIX'] == 0
                                    || ($row['NUMERO_MOVINGWALL'] != '0000-00-00'
                                            && $row['NUMERO_MOVINGWALL'] <= date('Ymd')
                                            && $row['ARTICLE_TOUJOURS_PAYANT'] == 0)){
                                    echo 'English : Free';
                                }else{
                                    echo 'English';
                                }
                            }
                            echo '</a>';
                        }?>

                <?php } ?>
                <?php if(in_array("REMOVE_BIBLIO", $arrayFieldsToDisplay)){ ?>
                    <a
                        class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                        style=""
                        href="javascript:void(0);"
                        data-webtrends="addToBiblio"
                        data-id_article="<?= $idArticle ?>"
                        onclick="ajax.removeFromBiblioPage('<?= $row['NUMERO_ID_NUMPUBLIE']; ?>','<?= $row['ARTICLE_ID_ARTICLE']; ?>')"
                    >
                    <label><span>Delete from my selection</span></label>
                    </a>
                <?php } ?>
                <?php if (in_array("REMOVE_BASKET", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <input type="image" class="icon del-panier" src="static/images/del.png" alt="Remove from cart" onclick="ajax.removeFromBasket('ART','<?= $row['NUMERO_ID_NUMPUBLIE']?>','<?= $row['ARTICLE_ID_ARTICLE']?>')">
                    </div>
                <?php } ?>
                <?php if (in_array("REMOVE_BASKET_INST", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <input type="image" class="icon del-panier" src="static/images/del.png" alt="Remove from cart" onclick="ajax.removeFromBasketInst('ART','<?= $row['NUMERO_ID_NUMPUBLIE']?>','<?= $row['ARTICLE_ID_ARTICLE']?>')">
                    </div>
                <?php } ?>
            </div>

        </div>
        <?php
    }
    ?>
    <?php
}
?>
