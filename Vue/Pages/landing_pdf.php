<?php
    $this->titre = "Téléchargement de fichier PDF";
    include (__DIR__ . '/../CommonBlocs/tabs.php');


    // On prépare les libellés, urls, ... etc
    $typeRev_suffixe = "";
    $typeNum_suffixe = "";
    $revue_url = "";
    $numero_url = "";
    if ($typePub == "revue" || $typePub == "magazine") {
        $article_libelle = "article";
        $article_det = "cet";
        $revue_url = $typePub . '-' . $revue["REVUE_URL_REWRITING"] . '.htm';
        $numero_url = $typePub . '-' . $revue["REVUE_URL_REWRITING"] . '-' . $revue["NUMERO_ANNEE"] . '-' . $revue["NUMERO_NUMERO"] . '.htm';
    } else {
        $article_libelle = "chapitre";
        $article_det = "ce";
        if ($typePub == "encyclopédie") {
            $typeRev_suffixe = " de poche";
        }
        $revue_url = $numero["NUMERO_URL_REWRITING"] . '--' . $numero["NUMERO_ISBN"];
        $numero_url = $numero["NUMERO_URL_REWRITING"] . '--' . $numero["NUMERO_ISBN"];

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
?>

<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./<?php echo $typePub_url; ?>">
        <?php echo ucfirst($typePub); ?>s<?php echo ($typeRev_suffixe != '' ? $typeRev_suffixe : ''); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./<?php echo $revue_url; ?>"><?php echo ucfirst($typePub=="encyclopédie"?"ouvrage":$typePub); ?><?php echo ($typeNum_suffixe != '' ? $typeNum_suffixe : ''); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <?php if ($typePub == "revue" || $typePub == "magazine") { ?>
        <a class="inactive" href="./<?php echo $numero_url; ?>">Num&#233;ro</a>
        <span class="icon-breadcrump-arrow icon"></span>
    <?php } ?>
    <a href="#">Pdf</a>
</div>

<div id="body-content" class="">
    <h1 class="main-title">Vous téléchargez</h1>

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
            <span class="yellow bold">par </span>
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
            <img class="big_coverbis" style="margin: 40px auto;" src="/<?= $vign_path ?>/<?= $currentArticle["ARTICLE_ID_REVUE"]; ?>/<?= $currentArticle["ARTICLE_ID_NUMPUBLIE"]; ?>_L204.jpg" />
        </p>

        <p class="text-center">
            Votre téléchargement va démarrer automatiquement.<br />
            <span class="yellow">Veuillez <a class="link-underline yellow" href="./load_pdf_do_not_index.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE']; ?>">cliquer ici</a> au cas où il ne démarrerait pas.</span>
        </p>
    </div>


    <?php
        /* 
         * Cette partie du code a été récupérée depuis la vue sujetProche, et adaptée pour fonctionner ici 
         * Les valeurs nécessaires se trouvent dans une variable $sujetProche
         */

        // Récupération des variables nécessaires
        $metaNumero         = $sujetProche["metaNumero"];
        $Ouvrages           = $sujetProche["Ouvrages"];
        $Revues             = $sujetProche["Revues"];
        $Magazines          = $sujetProche["Magazines"];
        $typepub            = $sujetProche["typepub"];
        $searchTerm         = $sujetProche["searchTerm"];
        $typeDocument       = $sujetProche["typeDocument"];
        $articlesButtons    = $sujetProche["articlesButtons"];
        $portalInfo         = $sujetProche["portalInfo"];
        $typePubCurrent     = $sujetProche["typePubCurrent"];
        $ParseDatas         = Service::get('ParseDatas');
        $typePub            = $typePubCurrent == 1?'revue':($typePubCurrent == 2?'magazine':($typePubCurrent==3?'ouvrage':'encyclopedie'));
    ?>

    <?php if( (sizeof($Ouvrages) > 0) || (sizeof($Revues) > 0) || (sizeof($Magazines) > 0) ) { ?>
    <div id="free_text" class="biblio mt3">

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
                            $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE";
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
                        <img
                            src="/<?= $vign_path ?>/<?=$ARTICLE_ID_REVUE?>/<?=$ARTICLE_ID_NUMPUBLIE?>_L61.jpg"
                            alt="couverture" class="small_cover">

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
                            $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE";
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
                    <img
                        src="/<?= $vign_path ?>/<?=$ARTICLE_ID_REVUE?>/<?=$ARTICLE_ID_NUMPUBLIE?>_L61.jpg"
                        alt="couverture" class="small_cover">

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
                            $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE";
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
                <img
                    src="/<?= $vign_path ?>/<?=$ARTICLE_ID_REVUE?>/<?=$ARTICLE_ID_NUMPUBLIE?>_L61.jpg"
                    alt="couverture" class="small_cover">

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

        <hr class="grey" />
        <p>
            <a href="./sur-un-sujet-proche.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>">
                Voir toutes les  publications sur un sujet proche <span class="icon-arrow-black-right icon"></span>
            </a>
        </p>

    </div>
    <?php } ?>
</div>

<?php
$this->javascripts[] = 'setTimeout(function() {window.location = "./load_pdf_do_not_index.php?ID_ARTICLE='
    .$currentArticle['ARTICLE_ID_ARTICLE']
    .'"}, 1000);';
?>
