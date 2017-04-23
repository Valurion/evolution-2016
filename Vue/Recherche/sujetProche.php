<?php
$ParseDatas = Service::get('ParseDatas');
$this->titre = 'Sur un sujet proche';

$typePub = $typePubCurrent == 1?'revue':($typePubCurrent == 2?'magazine':($typePubCurrent==3?'ouvrage':'encyclopedie'));

// On prépare les libellés, urls, ... etc
$typeRev_suffixe = "";
$typeNum_suffixe = "";
$revue_url = "";
$numero_url = "";
if ($typePub == "revue" || $typePub == "magazine") {
    $article_libelle = "article";
    $article_det = "cet";
    $revue_url = $typePub . '-' . $currentNumero["REVUE_URL_REWRITING"];
    $numero_url = $typePub . '-' . $currentNumero["REVUE_URL_REWRITING"] . '-' . $currentNumero["NUMERO_ANNEE"] . '-' . $currentNumero["NUMERO_NUMERO"];
    $article_url = $numero_url."-page-".$currentArticle["ARTICLE_PAGE_DEBUT"];
} else {
    $article_libelle = "chapitre";
    $article_det = "ce";
    if ($typePub == "encyclopédie") {
        $typeRev_suffixe = " de poche";
    }
    $revue_url = $currentNumero["NUMERO_URL_REWRITING"] . '--' . $currentNumero["NUMERO_ISBN"];
    $numero_url = $currentNumero["NUMERO_URL_REWRITING"] . '--' . $currentNumero["NUMERO_ISBN"];
    $article_url = $numero_url."-page-".$currentArticle["ARTICLE_PAGE_DEBUT"];

    if ($numero["NUMERO_TYPE_NUMPUBLIE"] == 1) {
        $typeNum_suffixe = " collectif";
    }
}
$typePub_url = null;
switch ($typePub) {
    case 'revue':
        $typePub_url = 'Accueil_Revues.php';
        break;
    case 'encyclopédie':
        $typePub_url = 'que-sais-je-et-reperes.php';
        break;
    default:
        $typePub_url = $typePub.'s.php';
        break;
}

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

<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./<?php echo $typePub_url ?>"><?php echo ucfirst($typePub); ?>s<?php echo ($typeRev_suffixe != '' ? $typeRev_suffixe : ''); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./<?php echo $revue_url; ?>.htm"><?php echo ucfirst($typePub=="encyclopédie"?"ouvrage":$typePub); ?><?php echo ($typeNum_suffixe != '' ? $typeNum_suffixe : ''); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <?php if ($typePub == "revue" || $typePub == "magazine") { ?>
        <a class="inactive" href="./<?php echo $numero_url; ?>.htm<?php echo $url_token; ?>">Num&#233;ro</a>
        <span class="icon-breadcrump-arrow icon"></span>
    <?php } ?>
    <a class="inactive" href="<?= $article_url;?>.htm"><?php echo ucfirst($article_libelle); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Sur un sujet proche</a>
</div>


<div id="body-content">
    <a name="top"></a>
    <div id="free_text" class="biblio">
        
        <?php
            // Formatage des auteurs
            $theAuthors = explode(',',$currentArticle["ARTICLE_AUTEUR"]);
            $auteurs = "";
            foreach ($theAuthors as $theAuthor){
                $theauthorParam = explode(':', $theAuthor);
                $theAutheurPrenom = $theauthorParam[0];
                $theAutheurNom = $theauthorParam[1];
                $theAutheurId = $theauthorParam[2];
                $auteurs .= ($auteurs != '' ?', ':'');
                $auteurs .= '<a class="yellow" href="publications-de-'.$theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm">'.$theAutheurPrenom.' '.$theAutheurNom.'</a>, ';
            }
        ?>
        
        <div class="media" style="display: block;/*margin: 0 auto;width: 350px;*/padding: 20px 40px;">
            <div class="media-body" style="display: block;">
                <div style="color: #323232;" class="titre"><a href="<?= $article_url;?>.htm"><?= $titre ?></a></div>
                <div style="margin-top: 0;color: #a5a524;" class="auteur"><?= rtrim($auteurs, ", "); ?></div>
                <div class="meta" style="margin-left: 0;padding-left: 0;"><?= $currentNumero["REVUE_TITRE"];?> <?= $currentNumero["NUMERO_ANNEE"];?>/<?= $currentNumero["NUMERO_NUMERO"];?><br /><?= $currentNumero["NUMERO_TITRE"];?></div>
            </div>
        </div>

        <div class="memo-numpublie">
            <h2 id="memo">Autres publications sur un sujet proche</h2>
        </div>
        <div class="list_articles">
            <?php if (sizeof($Ouvrages) > 0) : ?>

                <h2 class="section">
                    <span>Contributions d'ouvrages</span>
                </h2>

                <?php foreach ($Ouvrages as $result) : ?>
                    <?php
                    $typePubTitle = $typeDocument[$pack][$offset];
                    $typePub = $result->userFields->tp;
                    $typeNumPublie = $result->userFields->tnp;
                    $ARTICLE_ID_ARTICLE = $result->userFields->id;
                    $ARTICLE_ID_REVUE = $result->userFields->id_r;
                    $NUMERO_ID_REVUE = $ARTICLE_ID_REVUE;
                    $ARTICLE_PRIX = $result->userFields->px;

                    $ARTICLE_ID_NUMPUBLIE = $result->userFields->np;
                    $NUMERO_ID_NUMPUBLIE = $ARTICLE_ID_NUMPUBLIE;
                    $ARTICLE_HREF = "revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd . ".htm";
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
                            //$ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE";
                            $ARTICLE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
                            $NUMERO_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                            $REVUE_HREF;
                            $REVUE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                            break;
                        case "2":
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
                            break;
                    }


                    $BLOC_AUTEUR = '';
                    if (sizeof($authors) > 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> et al.";
                    } else if (sizeof($authors) == 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> et ";
                        $authors2 = explode('#', $authors[1]);
                        $BLOC_AUTEUR .= "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> ";
                    } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a>";
                    }

                    $BLOC_AUTEUR = trim($BLOC_AUTEUR);
                    ?>
                    <div class="article greybox_hover">
                        <a href="<?= $ARTICLE_HREF ?>">
                            <img src="http://<?= $vign_url ?>/<?= $vign_path ?>/<?=$ARTICLE_ID_REVUE?>/<?=$ARTICLE_ID_NUMPUBLIE?>_L61.jpg" alt="couverture" class="small_cover">
                        </a>

                        <div class="meta">
                            <div class="title">
                                <strong><a href="<?= $ARTICLE_HREF ?>"> <span
                                            class="subtitle"></span>
                                     <?= $ARTICLE_TITRE ?>
                                        <span class="subtitle"></span>
                                    </a></strong>
                            </div>
                            <br />
                            <div class="authors">
                                <?=$BLOC_AUTEUR?>
                            </div>
                            <div class="revue_title">
                                Dans <span class="title_little_blue"><a href="<?=$NUMERO_HREF?>">
                                        <?=$NUMERO_TITRE?> </a></span> <strong>(<?=$NOM_EDITEUR?>, <?=$NUMERO_ANNEE?>)

                                </strong>
                            </div>

                            <div class="state">
                            <?php if($cfgaArr[0]>0) : ?>
                            <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                                <?php if($cfgaArr[0]==1) echo "Résumé"; else if($cfgaArr[0]==2) echo "Première page"; else if($cfgaArr[0]==3) echo "Premières lignes"; ?>
                            </a>
                            <?php endif ;?>
                            <?php
                            if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1){
                                ?>
                                <?php if($cfgaArr[1]>0) :?>
                                <a href="article.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&amp;DocId=<?= $DOCID ?>" class="button">
                                    Version HTML
                                </a>
                                <?php endif ;?>

                                <?php if ($cfgaArr[2] > 0) : ?>
                                        <?php if ($isPdf) : ?>
                                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                                        <?php else: ?>
                                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                                        <?php endif; ?>
                                        Feuilleter en ligne
                                        </a>
                                <?php endif; ?>

                                <?php if($cfgaArr[4]>0) :?>
                                <a href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button" data-webtrends="goToPdfArticle" data-id_article="<?= $ARTICLE_ID_ARTICLE ?>" data-titre=<?=
                                                $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                            ?> data-authors=<?=
                                                $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )
                                                )
                                            ?>>
                                    Version PDF
                                </a>
                                <?php endif ; ?>

                                <?php if ($cfgaArr[3] > 0) : ?>
                                    <a href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button" data-webtrends="goToPdfArticle" data-id_article="<?= $ARTICLE_ID_ARTICLE ?>" data-titre=<?=
                                                $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                            ?> data-authors=<?=
                                                $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )
                                                )
                                            ?>>
                                        Version PDF
                                    </a>
                                <?php endif; ?>

                                <?php if ($cfgaArr[5] > 0) : ?>
                                    <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                                        <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                                    </a>
                                <?php
                                endif;
                            }else{
                                if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2){
                                    //WebTrends : "tracking sur les boutons d'ajout au panier"
                                    if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                        echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                                . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                                . 'data-webtrends="goToMonPanier" '
                                                . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                                . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                                . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE) . ' '
                                                . 'data-authors=' . $ParseDatas->cleanAttributeString(
                                                $ParseDatas->stringifyRawAuthors(
                                                    str_replace(
                                                        '#',
                                                        $ParseDatas::concat_name,
                                                        implode($ParseDatas::concat_authors, $authors)
                                                    ), 0, ';'
                                                )) . ' '
                                                . '>'
                                                . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']
                                                . '</a>';
                                    } else {
                                        echo '<a href="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'].'" '.(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'])?('class="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'].'"'):'button').'>'.$articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'].'</a>';
                                    }
                                }
                            }
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                            ?>
                        </div>
                        </div>
                    </div>
    <?php endforeach; ?>
            <?php endif; ?>


              <?php if (sizeof($Revues) > 0) : ?>

                <h2 class="section">
                    <span>Articles de revues</span>
                </h2>

                 <?php foreach ($Revues as $result) : ?>
                    <?php
                    //$typePubTitle = $typeDocument[$pack][$offset];
                    $typePub = $result->userFields->tp;
                    $typeNumPublie = $result->userFields->tnp;
                    $ARTICLE_ID_ARTICLE = $result->userFields->id;
                    $ARTICLE_ID_REVUE = $result->userFields->id_r;
                    $NUMERO_ID_REVUE = $ARTICLE_ID_REVUE;
                    $ARTICLE_PRIX = $result->userFields->px;

                    $ARTICLE_ID_NUMPUBLIE = $result->userFields->np;
                    $NUMERO_ID_NUMPUBLIE = $ARTICLE_ID_NUMPUBLIE;
                    $ARTICLE_HREF = "revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd . ".htm";
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
                            //$ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE";
                            $ARTICLE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
                            $NUMERO_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                            $REVUE_HREF;
                            $REVUE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                            break;
                        case "2":
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
                            break;
                    }


                    $BLOC_AUTEUR = '';
                    if (sizeof($authors) > 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> et al.";
                    } else if (sizeof($authors) == 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> et ";
                        $authors2 = explode('#', $authors[1]);
                        $BLOC_AUTEUR .= "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> ";
                    } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a>";
                    }

                    $BLOC_AUTEUR = trim($BLOC_AUTEUR);
                    ?>
                <div class="article greybox_hover">
                    <a href="<?=$ARTICLE_HREF?>">
                        <img src="http://<?= $vign_url ?>/<?= $vign_path ?>/<?=$ARTICLE_ID_REVUE?>/<?=$ARTICLE_ID_NUMPUBLIE?>_L61.jpg" alt="couverture" class="small_cover">
                    </a>

                    <div class="meta">
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
                            Dans <a href="<?=$REVUE_HREF?>"><span class="title_little_blue"><?=$REVUE_TITRE?></span> <strong><?=$NUMERO_ANNEE?>/<?=$NUMERO_NUMERO?>

                                </strong></a>
                        </div>
                         <div class="state">
                            <?php if($cfgaArr[0]>0) : ?>
                            <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                                <?php if($cfgaArr[0]==1) echo "Résumé"; else if($cfgaArr[0]==2) echo "Première page"; else if($cfgaArr[0]==3) echo "Premières lignes"; ?>
                            </a>
                            <?php endif ;?>
                            <?php
                            if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1){
                            ?>
                            <?php if($cfgaArr[1]>0) :?>
                            <a href="article.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&amp;DocId=<?= $DOCID ?>" class="button">
                                Version HTML
                            </a>
                            <?php endif ;?>

                             <?php if ($cfgaArr[2] > 0) : ?>
                                        <?php if ($isPdf) : ?>
                                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                                        <?php else: ?>
                                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                                        <?php endif; ?>
                                        Feuilleter en ligne
                                        </a>
                                <?php endif; ?>

                                <?php if($cfgaArr[4]>0) :?>
                                <a href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button" data-webtrends="goToPdfArticle" data-id_article="<?= $ARTICLE_ID_ARTICLE ?>" data-titre=<?=
                                                $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                            ?> data-authors=<?=
                                                $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )
                                                )
                                            ?>>
                                    Version PDF
                                </a>
                                <?php endif ; ?>

                                <?php if ($cfgaArr[3] > 0) : ?>
                                    <a href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button" data-webtrends="goToPdfArticle" data-id_article="<?= $ARTICLE_ID_ARTICLE ?>" data-titre=<?=
                                                $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                            ?> data-authors=<?=
                                                $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )
                                                )
                                            ?>>
                                        Version PDF
                                    </a>
                                <?php endif; ?>

                                <?php if ($cfgaArr[5] > 0) : ?>
                                    <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                                        <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                                    </a>
                                <?php
                                endif;

                            }else{
                                if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2){
                                    //WebTrends : "tracking sur les boutons d'ajout au panier"
                                    if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                        echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                                . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                                . 'data-webtrends="goToMonPanier" '
                                                . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                                . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                                . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE) . ' '
                                                . 'data-authors=' . $ParseDatas->cleanAttributeString(
                                                $ParseDatas->stringifyRawAuthors(
                                                    str_replace(
                                                        '#',
                                                        $ParseDatas::concat_name,
                                                        implode($ParseDatas::concat_authors, $authors)
                                                    ), 0, ';'
                                                )) . ' '
                                                . '>'
                                                . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']
                                                . '</a>';
                                    } else {
                                        echo '<a href="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'].'" '.(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'])?('class="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'].'"'):'button').'>'.$articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'].'</a>';
                                    }
                                }
                            }
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                            ?>

                        </div>
                    </div>

                </div>
<?php endforeach; ?>
<?php endif; ?>
             <br />

           <?php if (sizeof($Magazines) > 0) : ?>

            <h2 class="section">
                <span>Articles de magazines</span>
            </h2>

              <?php foreach ($Magazines as $result) : ?>
                    <?php
                    //$typePubTitle = $typeDocument[$pack][$offset];
                    $typePub = $result->userFields->tp;
                    $typeNumPublie = $result->userFields->tnp;
                    $ARTICLE_ID_ARTICLE = $result->userFields->id;
                    $ARTICLE_ID_REVUE = $result->userFields->id_r;
                    $NUMERO_ID_REVUE = $ARTICLE_ID_REVUE;
                    $ARTICLE_PRIX = $result->userFields->px;

                    $ARTICLE_ID_NUMPUBLIE = $result->userFields->np;
                    $NUMERO_ID_NUMPUBLIE = $ARTICLE_ID_NUMPUBLIE;
                    $ARTICLE_HREF = "revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd . ".htm";
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
                            //$ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE";
                            $ARTICLE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
                            $NUMERO_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                            $REVUE_HREF;
                            $REVUE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                            break;
                        case "2":
                            $ARTICLE_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING']. "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
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
                            break;
                    }


                    $BLOC_AUTEUR = '';
                    if (sizeof($authors) > 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[1] . ' ' . $authors2[2] . "</a> et al.";
                    } else if (sizeof($authors) == 2) {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> et ";
                        $authors2 = explode('#', $authors[1]);
                        $BLOC_AUTEUR .= "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a> ";
                    } else if (sizeof($authors) == 1 && $authors[0] != ' - ') {
                        $authors2 = explode('#', $authors[0]);
                        $BLOC_AUTEUR = "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[0] . ' ' . $authors2[2] . ' ' . $authors2[1] . "</a>";
                    }

                    $BLOC_AUTEUR = trim($BLOC_AUTEUR);
                    ?>
            <div class="article greybox_hover">
                <a href="<?=$ARTICLE_HREF?>">
                    <img src="http://<?= $vign_url ?>/<?= $vign_path ?>/<?=$ARTICLE_ID_REVUE?>/<?=$ARTICLE_ID_NUMPUBLIE?>_L61.jpg" alt="couverture" class="small_cover">
                </a>

                <div class="meta">
                    <div class="title">
                        <a href="<?=$ARTICLE_HREF?>"><strong> <span class="subtitle"></span>
                                 <?= $ARTICLE_TITRE?>
                                 <span class="subtitle"></span>

                            </strong></a>
                    </div>
                    <div class="authors">
                        <?=$BLOC_AUTEUR?>
                    </div>
                    <div class="revue_title">
                        Dans <a href="<?=$REVUE_HREF?>"><span class="title_little_blue"><?=$REVUE_TITRE?></span> <strong><?=$NUMERO_ANNEE?>/<?=$NUMERO_NUMERO?>

                            </strong></a>
                    </div>
                     <div class="state">
                            <?php if($cfgaArr[0]>0) : ?>
                            <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                                <?php if($cfgaArr[0]==1) echo "Résumé"; else if($cfgaArr[0]==2) echo "Première page"; else if($cfgaArr[0]==3) echo "Premières lignes"; ?>
                            </a>
                            <?php endif ;?>
                            <?php
                            if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1){
                            ?>
                            <?php if($cfgaArr[1]>0) :?>
                            <a href="article.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&amp;DocId=<?= $DOCID ?>" class="button">
                                Version HTML
                            </a>
                            <?php endif ;?>
                            <?php if ($cfgaArr[2] > 0) : ?>
                                        <?php if ($isPdf) : ?>
                                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                                        <?php else: ?>
                                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                                        <?php endif; ?>
                                        Feuilleter en ligne
                                        </a>
                                <?php endif; ?>

                                <?php if($cfgaArr[4]>0) :?>
                                <a href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button" data-webtrends="goToPdfArticle" data-id_article="<?= $ARTICLE_ID_ARTICLE ?>" data-titre=<?=
                                                $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                            ?> data-authors=<?=
                                                $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )
                                                )
                                            ?>>
                                    Version PDF
                                </a>
                                <?php endif ; ?>

                                <?php if ($cfgaArr[3] > 0) : ?>
                                    <a href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button" data-webtrends="goToPdfArticle" data-id_article="<?= $ARTICLE_ID_ARTICLE ?>" data-titre=<?=
                                                $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                            ?> data-authors=<?=
                                                $ParseDatas->cleanAttributeString(
                                                    $ParseDatas->stringifyRawAuthors(
                                                        str_replace(
                                                            '#',
                                                            $ParseDatas::concat_name,
                                                            implode($ParseDatas::concat_authors, $authors)
                                                        ), 0, ';'
                                                    )
                                                )
                                            ?>>
                                        Version PDF
                                    </a>
                                <?php endif; ?>

                                <?php if ($cfgaArr[5] > 0) : ?>
                                    <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button" data-webtrends="goToPdfArticle">
                                        <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                                    </a>
                                <?php
                                endif;
                            }else{
                                if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2){
                                    //WebTrends : "tracking sur les boutons d'ajout au panier"
                                    if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                        echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                                . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                                . 'data-webtrends="goToMonPanier" '
                                                . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                                . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                                . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE) . ' '
                                                . 'data-authors=' . $ParseDatas->cleanAttributeString(
                                                $ParseDatas->stringifyRawAuthors(
                                                    str_replace(
                                                        '#',
                                                        $ParseDatas::concat_name,
                                                        implode($ParseDatas::concat_authors, $authors)
                                                    ), 0, ';'
                                                )) . ' '
                                                . '>'
                                                . $articlesButtons[$ARTICLE_ID_ARTICLE]['LIB']
                                                . '</a>';
                                    } else {
                                        echo '<a href="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'].'" '.(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'])?('class="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'].'"'):'button').'>'.$articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'].'</a>';
                                    }
                                }
                            }
                            require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                            checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                            ?>


                        </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>

        </div>

        <div class="CB"></div>
    </div>
</div>


