<!--
Ce template sert à l'affichage d'une liste à 2 colonnes.
Il s'attend à recevoir:
    - $arrayForList = l'array qui contient les données, comprenant le champ TYPEPUB
    - $arrayFieldsToDisplay = un array qui contient les champs à afficher. Par défaut, seul le titre et l'image s'affichent
-->
<?php
    // Init Values
    $i = 0;
    $listeArticle = [];
    $imgToggler   = "";
?>
<?php if (isset($arrayForList)) { ?>
    <?php
    foreach ($arrayForList as $row) {
        if ($row['REVUE_TYPEPUB'] == '1') {
            if($currentPage == 'numero'){
                $url = 'revue-' . $row['REVUE_URL_REWRITING'] . '-' . $row["NUMERO_ANNEE"] . '-' . $row["NUMERO_NUMERO"];
                $urlRev = 'revue-' . $row['REVUE_URL_REWRITING'];
                $titre = $row['NUMERO_TITRE'];
                $soustitre = $row['NUMERO_SOUS_TITRE'];
                $article_format = "Format électronique<br />(HTML et PDF)";
            }else{
                if(trim($row["ARTICLE_PAGE_DEBUT"]) != '')
                    $url = 'revue-' . $row['REVUE_URL_REWRITING'] . '-' . $row["NUMERO_ANNEE"] . '-' . $row["NUMERO_NUMERO"] . '-page-' . $row["ARTICLE_PAGE_DEBUT"];
                else
                    $url = 'article.php?ID_ARTICLE='.(isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:$row['ID_ARTICLE']);
                $urlRev = 'revue-' . $row['REVUE_URL_REWRITING'];
                $titre = $row['ARTICLE_TITRE'];
                $soustitre = $row['ARTICLE_SOUSTITRE'];
                $article_format = "Format électronique<br />(HTML et PDF)";
            }
        } else if ($row['REVUE_TYPEPUB'] == '3' || $row['REVUE_TYPEPUB'] == '6') {
            if ($currentPage == 'contrib') {
                if(trim($row["ARTICLE_PAGE_DEBUT"]) != '')
                    $url = $row['NUMERO_URL_REWRITING'] . "--" . $row["NUMERO_ISBN"] . "-page-" . $row["ARTICLE_PAGE_DEBUT"];
                else
                    $url = 'article.php?ID_ARTICLE='.(isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:$row['ID_ARTICLE']);
                $urlRev = $row['NUMERO_URL_REWRITING'] . "--" . $row["NUMERO_ISBN"];
                $titre = $row['ARTICLE_TITRE'];
                $soustitre = $row['ARTICLE_SOUSTITRE'];
                $article_format = "Format électronique<br />(HTML et PDF)";
            } else {
                $url = $row['NUMERO_URL_REWRITING'] . "--" . $row["NUMERO_ISBN"];
                $urlRev = $row['NUMERO_URL_REWRITING'] . "--" . $row["NUMERO_ISBN"];
                $titre = $row['NUMERO_TITRE'];
                $soustitre = $row['NUMERO_SOUS_TITRE'];
                $article_format = "Format électronique<br />(HTML et PDF)";
            }
        } else if ($row['REVUE_TYPEPUB'] == '2') {
            // Article de magazine
            if($row["ARTICLE_ID_ARTICLE"]) {
                if(trim($row["ARTICLE_PAGE_DEBUT"]) != '')
                    $url = 'magazine-' . $row['REVUE_URL_REWRITING'] . '-' . $row["NUMERO_ANNEE"] . '-' . $row["NUMERO_NUMERO"] . '-page-' . $row["ARTICLE_PAGE_DEBUT"];
                else
                    $url = 'article.php?ID_ARTICLE='.(isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:$row['ID_ARTICLE']);
                $urlRev = 'magazine-' . $row['REVUE_URL_REWRITING'];
                $titre = $row['ARTICLE_TITRE'];
                $soustitre = $row['ARTICLE_SOUSTITRE'];
                $article_format = "Format électronique<br />(HTML)";
            }
            // Magazine
            else {
                $url = 'magazine-' . $row['REVUE_URL_REWRITING'] . '-' . $row["NUMERO_ANNEE"] . '-' . $row["NUMERO_NUMERO"];
                $urlRev = 'magazine-' . $row['REVUE_URL_REWRITING'];
                $titre = $row['NUMERO_TITRE'];
                $soustitre = $row['NUMERO_SOUS_TITRE'];
                $article_format = "Format électronique<br />(HTML)";
            }
        }
        ?>
        <div <?= in_array("ID", $arrayFieldsToDisplay)?('id="'.$row['NUMERO_ID_NUMPUBLIE'].'-'.(isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:'').'"'):'' ?> class="greybox_hover article">
            <?php
                // Init
                $imgLinkStyle   = "";

                // Ajout d'un paramètre permettant de n'afficher qu'une seule image identique
                if((isset($groupByImage)) && ($groupByImage == 1)) {
                    // Définition de l'image
                    $imgThumb       = "/".$vign_path."/".$row['REVUE_ID_REVUE']."/".$row['NUMERO_ID_NUMPUBLIE']."_L61.jpg";

                    // On n'affiche pas deux fois la même image (nouveau style)
                    if($imgThumb == $imgToggler) {
                        // Définition du style visibility (permet de conserver les espaces, etc.);
                        $imgLinkStyle = "visibility: hidden;";
                    }
                    // Redéfinition de la valeur
                    else {
                        $imgToggler = $imgThumb;
                    }
                }
            ?>
            <a style="<?php echo $imgLinkStyle; ?>" href="./<?= $url.(strpos($url,'.php')===false?'.htm':'') ?>">
                <img src="/<?= $vign_path ?>/<?= $row['REVUE_ID_REVUE'] ?>/<?= $row['NUMERO_ID_NUMPUBLIE'] ?>_L61.jpg" alt="couverture de [NUMERO_TITRE_ABREGE]" class="small_cover">
            </a>
            <div class="meta">
                <div class="title"><span class="bullet">
                        <a  href="./<?= $url.(strpos($url,'.php')===false?'.htm':'') ?>"><b><?= $titre ?><span class="subtitle"><?= $soustitre ?></span></b></a>
                    </span></div>

                <?php if (in_array("BIBLIO_AUTEURS", $arrayFieldsToDisplay)) {
                    if($row['BIBLIO_AUTEURS'] != ''){
                        $theAuthors = explode(',',$row['BIBLIO_AUTEURS']);
                        $str = "";
                        foreach ($theAuthors as $theAuthor){
                            $theauthorParam = explode(':', $theAuthor);
                            $theAutheurPrenom = $theauthorParam[0];
                            $theAutheurNom = $theauthorParam[1];
                            $theAutheurId = $theauthorParam[2];
                            $str .= ($str != '' ?', ':'');
                            $str .= '<span class="author"><a class="yellow" href="publications-de-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'.$theAutheurPrenom.' '.$theAutheurNom.'</a></span>';
                        }
                        echo '<div class="authors">'.$str.'</div>';
                    }
                }?>

                <?php if (in_array("AUTEURS_CONTRIBUTEURS", $arrayFieldsToDisplay)) {
                    if($row['AUTEURS'] != ''){
                        $theAuthors = explode(',',$row['AUTEURS']);
                        $countAuthors = count($theAuthors);
                        $str = "";
                        $i = 1;
                        foreach ($theAuthors as $theAuthor){
                            $theauthorParam = explode(':', $theAuthor);
                            $theAutheurPrenom = $theauthorParam[0];
                            $theAutheurNom = $theauthorParam[1];
                            $theAutheurId = $theauthorParam[2];
                            if($i == $countAuthors && $countAuthors > 1) {$str .= " <span style=\"font-weight: normal;color: #1f1f1f;\">et</span> ";}  // On ajoute "et" juste avant le dernier auteur...
                            else {$str .= ($str != '' ?', ':'');}                           // ...sinon simplement une virgule
                            $str .= '<span class="author"><a class="yellow" href="publications-de-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'.$theAutheurPrenom.' '.$theAutheurNom.'</a></span>';
                            $i++;
                        }
                        echo '<div class="authors"><span style="font-weight: normal;color: #1f1f1f;">Avec</span> '.$str.'</div>';
                    }
                }?>

                <?php if (in_array("COLL_TITLE", $arrayFieldsToDisplay)) { ?>
                    <div class="revue_title">Coll. <span class="title_little_blue"><a class="title_little_blue" href="./collection.php?ID_REVUE=<?= $row["REVUE_ID_REVUE"] ?>"><?= $row['REVUE_TITRE'] ?></a></span>
                        <b>(<?= $row['EDITEUR_NOM_EDITEUR'] ?>, <?= $row['NUMERO_ANNEE'] ?>)</b>
                        <?php if (in_array("FORMAT", $arrayFieldsToDisplay)) { ?><div class="format-revue"><?= $article_format ?></div><?php } ?>
                    </div>
                <?php } else if (in_array("NUMERO_TITLE", $arrayFieldsToDisplay)) { ?>
                    <div class="revue_title">Dans <span class="title_little_blue"><a class="title_little_blue" href="./<?= $urlRev ?>.htm"><?= $row['NUMERO_TITRE'] ?></a></span>
                        <b>(<?= $row['EDITEUR_NOM_EDITEUR'] ?>, <?= $row['NUMERO_ANNEE'] ?>)</b>
                        <?php if (in_array("FORMAT", $arrayFieldsToDisplay)) { ?><div class="format-revue"><?= $article_format ?></div><?php } ?>
                    </div>
                <?php } else if (in_array("REVUE_TITLE", $arrayFieldsToDisplay)) { ?>
                    <div class="revue_title">Dans <span class="title_little_blue"><a class="title_little_blue" href="./<?= $urlRev ?>.htm"><?= $row['REVUE_TITRE'] ?></a></span>
                        <?= $row['NUMERO_ANNEE'] ?>/<?= $row['NUMERO_NUMERO'] ?> (<?= $row['NUMERO_VOLUME'] ?>)
                        <?php if (in_array("FORMAT", $arrayFieldsToDisplay)) { ?><div class="format-revue"><?= $article_format ?></div><?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="revue_title">
                        <?php if (in_array("FORMAT", $arrayFieldsToDisplay)) { ?><div class="format-revue"><?= $article_format ?></div><?php } ?>
                    </div>
                <?php } ?>

                <?php if (in_array("PRIX", $arrayFieldsToDisplay)) {?>
                <div class="prix">
                    <strong><span id="price-<?= $row['NUMERO_ID_NUMPUBLIE'].'-'.(isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:'')?>"><?= number_format($row['ARTICLE_PRIX'], 2, ",", "") ?></span> €</strong>
                </div>
                <?php } ?>
                <?php if (in_array("STATE_OUV", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <a class="button" href="<?= $url.(strpos($url,'.php')===false?'.htm':'') ?>">Présentation/Sommaire</a>

                        <?php
                            // Version EN des contributions de l'auteur
                            // Page auteurs
                            if($_GET["controleur"] == "ListeDetail" && $_GET["TYPE"] == "auteur") {
                                // Ouvrages
                                // L'ID de l'ouvrage est présent dans le tableau, on affiche un lien directement vers la version de cairn-int
                                if(array_key_exists($row['NUMERO_ID_NUMPUBLIE'], $auteur["LISTE_OUVRAGES"])) {

                                    // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                                    $array             = $auteur["LISTE_OUVRAGES"][$row['NUMERO_ID_NUMPUBLIE']][0];
                                    $annee_numero_int  = $array["ANNEE"];
                                    $nro_numero_int    = $array["NUMERO"];
                                    $url_numero_int    = $array["URL_REWRITING_EN"];

                                    echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/journal-".$url_numero_int."-".$annee_numero_int."-".$nro_numero_int.".htm\">English</a>";
                                }
                            }
                        ?>

                        <?php if(in_array("REMOVE_BIBLIO", $arrayFieldsToDisplay)){
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($row['REVUE_ID_REVUE'],$row['NUMERO_ID_NUMPUBLIE'], (isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:null),null,'remove');
                        } ?>
                    </div>
                <?php } ?>
                <?php if (in_array("STATE", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <?php foreach ($row["LISTE_CONFIG_ARTICLE"] as $listeConfigArt) { ?>
                            <?php if(isset($listeConfigArt['BEFORE'])) {echo "<span style=\"margin-right: 5px;\">".$listeConfigArt['BEFORE']."</span>" ;}?>
                            <a
                                class="<?= (!isset($listeConfigArt['CLASS'])?'button':$listeConfigArt['CLASS'])?>"
                                href="<?= $listeConfigArt["HREF"] ?>"
                                <?php if (strpos($listeConfigArt['HREF'], 'load_pdf') !== false || (strpos($listeConfigArt['HREF'], 'revues.org') !== false)  || (strpos($listeConfigArt['HREF'], 'mon_panier') !== false))  : ?>
                                    <?php
                                        if (strpos($listeConfigArt['HREF'], 'load_pdf') !== false) {
                                            echo 'data-webtrends="goToPdfArticle"';
                                        } elseif (strpos($listeConfigArt['HREF'], 'revues.org') !== false) {
                                            echo 'data-webtrends="goToRevues.org"';
                                        }  elseif (strpos($listeConfigArt['HREF'], 'mon_panier') !== false) {
                                            echo 'data-webtrends="goToMonPanier" ';
                                            echo 'data-prix_article="' . number_format($row['ARTICLE_PRIX'], 2, ',', '') . '" ';
                                        }
                                    ?>
                                    data-id_article="<?= $row['ARTICLE_ID_ARTICLE'] ?>"
                                    data-titre=<?=
                                        Service::get('ParseDatas')->cleanAttributeString($row['ARTICLE_TITRE'])
                                    ?>
                                    data-authors=<?=
                                        Service::get('ParseDatas')->cleanAttributeString(
                                            Service::get('ParseDatas')->stringifyRawAuthors(
                                                $row['BIBLIO_AUTEURS'],
                                                0,
                                                ';',
                                                null,
                                                null,
                                                true,
                                                ',',
                                                ':'
                                            )
                                        )
                                    ?>
                                <?php endif; ?>
                                <?php if(isset($listeConfigArt['TARGET'])) {echo "target=\"".$listeConfigArt['TARGET']."\"";}?>
                            ><?= $listeConfigArt["LIB"] ?></a>
                            <?php
                        }
                        ?>

                        <?php
                            // Version EN des contributions de l'auteur
                            // Page auteurs
                            if($_GET["controleur"] == "ListeDetail" && $_GET["TYPE"] == "auteur") {
                                // Articles & Contributions
                                // L'ID de l'article est présent dans le tableau, on affiche un lien directement vers la version de cairn-int
                                if(array_key_exists($row['ARTICLE_ID_ARTICLE'], $auteur["LISTE_ARTICLES"])) {

                                    // Récupération des données (il s'agit d'un tableau réalisé via FETCH_GROUP)
                                    $array              = $auteur["LISTE_ARTICLES"][$row['ARTICLE_ID_ARTICLE']][0];
                                    $id_article_int     = $array["ID_ARTICLE"];
                                    $url_article_int    = $array["URL_REWRITING_EN"];

                                    echo "<a class=\"button\" href=\"".Service::get('ParseDatas')->getCrossDomainUrl()."/abstract-".$id_article_int."--".$url_article_int.".htm\">English</a>";
                                }
                            }
                        ?>

                        <?php if(in_array("REMOVE_BIBLIO", $arrayFieldsToDisplay)){
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($row['REVUE_ID_REVUE'],$row['NUMERO_ID_NUMPUBLIE'], (isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:null),null,'remove');
                        } else {
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($row['REVUE_ID_REVUE'],$row['NUMERO_ID_NUMPUBLIE'], (isset($row['ARTICLE_ID_ARTICLE'])?$row['ARTICLE_ID_ARTICLE']:null),$authInfos);
                        } ?>
                    </div>
                <?php } ?>
                <?php if (in_array("REMOVE_BASKET", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <a
                            id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                            href="javascript:void(0);"
                            class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                            onclick="ajax.removeFromBasket('ART','<?= $row['NUMERO_ID_NUMPUBLIE']?>','<?= $row['ARTICLE_ID_ARTICLE']?>')"
                            data-webtrends="removeFromCart"
                            data-id_article="<?= $row['ARTICLE_ID_ARTICLE']?>"
                            data-prix_article="<?= $row['ARTICLE_PRIX'] ?>"
                            data-titre=<?= Service::get('ParseDatas')->cleanAttributeString($row['ARTICLE_TITRE'])?>
                            data-authors=<?=
                                    Service::get('ParseDatas')->cleanAttributeString(
                                        Service::get('ParseDatas')->stringifyRawAuthors(
                                            $row['BIBLIO_AUTEURS'],
                                            0,
                                            ';',
                                            null,
                                            null,
                                            true,
                                            ',',
                                            ':'
                                        )
                                    )
                            ?>
                        >
                        <label><span>Supprimer du panier</span></label>
                        </a>
                    </div>
                <?php } ?>
                <?php if (in_array("REMOVE_BASKET_INST", $arrayFieldsToDisplay)) { ?>
                    <div class="state">
                        <a
                            id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                            href="javascript:void(0);"
                            class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                            onclick="ajax.removeFromBasketInst('ART','<?= $row['NUMERO_ID_NUMPUBLIE']?>','<?= $row['ARTICLE_ID_ARTICLE']?>')"
                            data-webtrends="removeFromCart"
                            data-id_article="<?= $row['ARTICLE_ID_ARTICLE']?>"
                            data-prix_article="<?= $row['ARTICLE_PRIX'] ?>"
                            data-titre=<?= Service::get('ParseDatas')->cleanAttributeString($row['ARTICLE_TITRE'])?>
                            data-authors=<?=
                                    Service::get('ParseDatas')->cleanAttributeString(
                                        Service::get('ParseDatas')->stringifyRawAuthors(
                                            $row['BIBLIO_AUTEURS'],
                                            0,
                                            ';',
                                            null,
                                            null,
                                            true,
                                            ',',
                                            ':'
                                        )
                                    )
                            ?>
                        >
                        <label><span>Supprimer du panier</span></label>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
            // Achat du numéro
            if (in_array("BUY_NUMERO_ELEC", $arrayFieldsToDisplay)) {

                // Définition des clés
                $currentKey = $i;
                $nextKey    = $currentKey+1;
                $id_revue   = $row["REVUE_ID_REVUE"];
                $id_numero  = $row["NUMERO_ID_NUMPUBLIE"];
                $id_article = $row["ARTICLE_ID_ARTICLE"];

                // Ajout de l'article
                $listeArticle[] = $id_article;

                // Afficher l'option uniquement si le prochain article fait partie d'un autre numéro ou si nous sommes à la fin du tableau
                if( ($arrayForList[$currentKey]["REVUE_ID_REVUE"] !== $arrayForList[$nextKey]["REVUE_ID_REVUE"]) )  {

                    // Le numéro est disponible à l'achat
                    if( ($AbsoluteRevue[$id_revue]["ACHAT_NUMERO_ELEC"] == 1) && (!in_array($id_revue, $arrayAllRevuesInCart)) ) {
                        echo "<div id=\"buy-numero-$id_numero\" class=\"panier-tips\">
                                <span class=\"panier-tips-body\">
                                    <span class=\"title block\">Le saviez-vous ?</span>
                                    <span class=\"paragraph block\">
                                        Pour <b>".$AbsoluteRevue[$id_revue]["NUMERO_PRIX_ELEC"]." €</b> il vous est possible d'acheter le numéro complet de cette revue.<br />
                                        <span style=\"font-style: normal;\"><a class=\"link-underline\" href=\"javascript:void(0);\" onclick=\"ajax.addNumeroFromBasket('$id_numero', '".implode(",", $listeArticle)."');\">Acheter plutôt le numéro</a> | <a class=\"link-underline\" href=\"javascript:void(0);\" onclick=\"$('#buy-numero-$id_numero').hide('slow');\">Non merci</a></span>
                                    </span>
                                </span>
                            </div>";
                    }

                    // On vide le tableau de toute façon
                    $listeArticle = [];
                }

                // Incr.
                $i++;
            }
        ?>
    <?php
    }
    ?>
    <?php
}
?>
