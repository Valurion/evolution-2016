<script>
    // C'est du copié-collé depuis static/js/cairn_webtrends.js
    // C'est crade, mais j'en ai vraiment plein le dos des templates qui changent sans arrêt de manière de fonctionner avec des kms de lignes
    $(function() {
        "use strict";

        if (!window.hasOwnProperty('wtCairn')) return;
        $('.pertinent_articles [data-webtrends="goToPdfArticle"]').mouseup(function() {
            var $this = $(this);
            if ($this.data('hasAlreadySendEvent')) return;
            $this.data('hasAlreadySendEvent', true);

            //Récupération des informations manquantes sur l'article.
            var webTrend = JSON.parse($.ajax({ type: "POST",
                        url: "index.php?controleur=Revues&action=getInfosAboutArticleForWebTrends",
                        data: {id_article: $this.data('id_article')},
                        async: false
                      }).responseText);

            //Pour les consultations à prendre en compte.
            var w_art_p2 = 'non';
            if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
                if (webTrend.type_publication == '1') {
                    if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                } else if (webTrend.type_publication == '3') {
                    if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                } else if (webTrend.type_publication == '6') {
                    if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                } else if (webTrend.type_publication == '2') {
                    if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                }
            }

            window.wtCairn.sendEvent({
                'content-uid': $this.data('id_article'),
                'article-titre': $this.data('titre'),
                'numero-auteurs': $this.data('authors'),
                'action': 'pdfDownload',
                'revue-id_revue': webTrend.pn_grid,
                'revue-titre': webTrend.pn_gr,
                'art_p1': webTrend.art_p1,
                'numero-id_numpublie': webTrend.pn_nid,
                'numero-titre': webTrend.pn_ntit,
                'article-nb_pages': webTrend.doc_nb_pages,
                'id_editeur': webTrend.id_editeur,
                'annee_tomaison': webTrend.annee_tomaison,
                'annee_mise_en_ligne': webTrend.annee_mise_en_ligne,
                'editeur': webTrend.editeur,
                'comm_rev' : webTrend.comm_rev,
                'doc_temps_de_lecture' : webTrend.doc_temps_de_lecture,
                'doc_pdf_dispo' : webTrend.doc_pdf_dispo,
                'revue-discipline' : webTrend.discipline_principale,
                'discipline' : webTrend.discipline,
                'sub-discipline' : webTrend.sub_discipline,
                'content-type' : webTrend.pn_type,
                'cleo' : webTrend.cleo,
                'art_p2' : w_art_p2,
                'article-type_commercialisation' : webTrend.comm_art,
                'format': 'PDF',
                'category' : 'texte intégral PDF'
            });
        });
    });
</script>

<?php

$ParseDatas = Service::get('ParseDatas');

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
<svg height="14" width="60" style="position: absolute; top: -14px; left: 275px;" class="pertinent_arrow"><polygon points="0,15 30,0 60,15" style="fill:rgb(244, 244, 244); stroke:rgb(204, 204, 204); stroke-width:1"></polygon></svg>
<div class="results_list list_articles">
    <?php foreach ($results as $result) : ?>
        <?php

        // On réduit le contexte des résultats à N mots, quoi qu'il arrive.
        // Le moteur de recherche de pythagoria renvoie N mots APRÈS la dernière occurence (ce qui est une autre logique)
        // Voir #69476
        $result->item->Synopsis = preg_split('/\s+(?!<BR>|&nbsp;)/i', $result->item->Synopsis);
        $result->item->Synopsis = array_slice($result->item->Synopsis, 0, $searchT['amountOfContext']);
        $result->item->Synopsis = implode(' ', $result->item->Synopsis);
        // En gros, j'essaye de rajouter 3 points à la fin d'un contexte.
        // Mais la regexp est un peu naze, ça provoquera certainement une demande dans le futur.
        if (!preg_match('/(...\s*<BR\s*\/?>\s*)$/i', $result->item->Synopsis)) {
            $result->item->Synopsis .= ' ...';
        }
        //recup variables
        if ((int) $result->item->packed == '1') {
            $pack = 1;
        } else {
            $pack = 0;
        }
        if ((int) $result->userFields->tp == 3) {
            $offset = (int) $result->userFields->tp + 2 * (int) $result->userFields->tnp;
        } else {
            $offset = (int) $result->userFields->tp;
        }
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
        $ARTICLE_PAGEFIN = $result->userFields->pgf;

        $PORTAIL = $result->userFields->idp;

        //echo $result->item->hits;
        $arrHits = explode(' ',$result->item->hits);
        $arrHits = array_slice($arrHits,(count($arrHits)-250));
        $hitsStr = implode(' ',$arrHits);
        //echo '<br/>'.$hitsStr;
        $getDocUrlParameters = '&DocId=' . $result->item->docId . '&hits=' . urlencode($hitsStr);
        //$getDocUrlParameters = '&DocId=' . $result->item->docId . '&hits=' . urlencode($result->item->hits);
        $isPdf = (stripos($result->item->Filename, '.pdf') > 0);

        $NOM_EDITEUR = $metaNumero[$NUMERO_ID_NUMPUBLIE]['NOM_EDITEUR'];
        $REVUE_TITRE = $result->userFields->rev0;
        $cfgaArr = explode(',', $result->userFields->cfg0);
        $NUMERO_MEMO = substr($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'], 0, strpos($metaNumero[$NUMERO_ID_NUMPUBLIE]['MEMO'], " ", 200));
        $CONTEXTE = strip_tags($result->item->Synopsis, '<b>');

        $ARTICLE_HREF = '';
        $NUMERO_HREF = '';
        $REVUE_HREF = "";
        switch ($typePub) {
            case "1":
                $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
                $NUMERO_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                $REVUE_HREF;
                $REVUE_HREF = "./revue-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                break;
            case "2":
                $ARTICLE_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-" . $NUMERO_ANNEE . "-" . $NUMERO_NUMERO . "-page-" . $ARTICLE_PAGE . ".htm";
                $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
                $NUMERO_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . "-$NUMERO_ANNEE-$NUMERO_NUMERO.htm";
                $REVUE_HREF;
                $REVUE_HREF = "./magazine-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                break;
            case "3":

                $ARTICLE_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
                $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
                $NUMERO_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . ".htm";
                $REVUE_HREF = "./collection-" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['REVUE_URL_REWRITING'] . ".htm";
                break;

            case "6":

                $ARTICLE_HREF = "./" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] . "--" . $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] . '-page-' . $ARTICLE_PAGE . ".htm";
                $ARTICLE_HREF = "./article.php?ID_ARTICLE=$ARTICLE_ID_ARTICLE" . $getDocUrlParameters;
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
        //if($BLOC_AUTEUR == '- ')
        //  $BLOC_AUTEUR='';
        ?>

        <?php if ($typePub == 6) : ?>
            <?php if (!$pack) : ?>
                <!-- RECHERCHE D'ENCYCLOPÉDIE DE POCHE -->

                <div class="result article encyclopedie" id="<?= $ARTICLE_ID_ARTICLE ?>">
                    <div class="wrapper_meta">
                        <div class="meta">
                            <div>
                               <img src="img/pert_<?php $score=(int)($result->item->scorePercent/10); if($score==0){$score=1;}echo $score; ?>.png" alt="niveau de pertinence évalué à <?=$score?>" class="pertinence">
                               <span class="pages">page <?= $ARTICLE_PAGE . ' à ' . $ARTICLE_PAGEFIN ?></span>
                               <a href="<?= $ARTICLE_HREF ?>">
                                 <div class="title"><strong><?= $ARTICLE_TITRE ?></strong></div>
                                </a>
                            </div>
                            <div class="authors">
                                <span class="author">
                                    <?= $BLOC_AUTEUR ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="contexte"><?= $CONTEXTE ?></div>
                     <div class="state">

                    <?php if ($cfgaArr[0] > 0) : ?>
                        <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                            <?php if ($cfgaArr[0] == 1) echo "Résumé"; else if ($cfgaArr[0] == 2) echo "Première page"; else if ($cfgaArr[0] == 3) echo "Premières lignes"; ?>
                        </a>
                    <?php endif; ?>
                    <?php
                    if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1){
                    ?>
                    <?php if ($cfgaArr[1] > 0) : ?>
                        <a href="<?= $ARTICLE_HREF ?>" class="button">
                            Version HTML
                        </a>
                    <?php endif; ?>
                    <?php if ($cfgaArr[2] > 0) : ?>
                        <?php if ($isPdf) : ?>
                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                            <?php else: ?>
                                <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                                <?php endif; ?>
                                Feuilleter en ligne
                            </a>

                        <?php endif; ?>

                        <?php if ($cfgaArr[3] > 0) : ?>
                            <a
                                href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                class="button"
                                data-webtrends="goToPdfArticle"
                                data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                data-titre=<?=
                                    $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                ?>
                                data-authors=<?=
                                    $ParseDatas->cleanAttributeString(
                                        $ParseDatas->stringifyRawAuthors(
                                            str_replace(
                                                '#',
                                                $ParseDatas::concat_name,
                                                implode($ParseDatas::concat_authors, $authors)
                                            ), 0, ';'
                                        )
                                    )
                                ?>
                                data-comm_rev="<?= ($metaNumero[$NUMERO_ID_NUMPUBLIE]['MOVINGWALL'] == 0) ? 'gratuite' : 'payante' ?>"
                            >
                                Version PDF
                            </a>
                        <?php endif; ?>

                        <?php if ($cfgaArr[5] > 0) : ?>
                            <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                               <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                            </a>
                        <?php endif;
                    }else{
                        if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2){
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
                                    echo '<a href="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'].'" '.(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'])?('class="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'].'"'):'class="button"').'>'.$articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'].'</a>';
                                }
                        }
                    }
                    require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                    ?>

                </div>
                </div>

            <?php else: ?>

                <div class="result article encyclopedie" id="<?= $ARTICLE_ID_ARTICLE ?>">
                    <h2><?= $typePubTitle ?></h2>
                    <div class="wrapper_meta">
                        <a href="<?= $NUMERO_HREF ?>">
                            <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                        </a>
                        <div class="meta">
                            <div class="revue_title">
                                <a href="<?= $NUMERO_HREF ?>" class="title_little_blue">
                                    <span class="title_little_blue"><?= $NUMERO_TITRE ?></span>
                                </a>
                                <strong>(<?= $NOM_EDITEUR ?>, <?= $NUMERO_ANNEE ?>)</strong>
                            </div>
                            <!-- <a href="resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=[NUMERO_HREF]&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]"> -->
                            <div class="title">dans <a href="./que-sais-je-et-reperes.php?ID_REVUE=<?= $REVUE_ID ?>"><strong><?= $REVUE_TITRE ?></strong></a></div>
                            <!-- </a> -->
                            <div class="authors">
                                <?= $BLOC_AUTEUR ?>
                            </div>
                        </div>
                    </div>
                    <div class="contexte"><?= $NUMERO_MEMO ?></div>
                    <div class="state">

                        <a href="<?= $NUMERO_HREF ?>"  class="button">Présentation/Sommaire</a>
                        <?php
                        require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                        checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                        ?>
                        <a href="#" class="button" onclick="cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                    </div>

                    <div class="pertinent_articles" id="__pertinent_[NUMERO_ID_NUMPUBLIE]">

                        <div class="meta">
                            <div>
                                <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                                <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>

                                <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                            </div>
                            <div class="contexte">[CONTEXTE]</div>
                            <div class="state">
                                [LISTE_CONFIG_ARTICLE]
                                <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                                   [ARTICLE_LIBELLE_LIBELLE]
                            </a>
                            [/LISTE_CONFIG_ARTICLE]

                            [BLOC_CREDIT_INST]
                            [BLOC_ARTICLE_ACHAT]
                            <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                            [/BLOC_ARTICLE_ACHAT]
                            [/BLOC_CREDIT_INST]

                            [BLOC_CAIRN_INST_ACHAT]
                            [BLOC_CREDIT_INST_OFF]
                            [BLOC_ARTICLE_ACHAT]
                            <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                                <span class="button first">Consulter</span>
                                <span class="icon icon-add-to-cart"></span>
                                <span class="button last">[ARTICLE_PRIX] €</span>
                            </a>
                            [/BLOC_ARTICLE_ACHAT]
                            [/BLOC_CREDIT_INST_OFF]
                            [/BLOC_CAIRN_INST_ACHAT]
                        </div>
                    </div>
                    <hr class="grey" />
                </div>
            </div>

            <!-- FIN DE RECHERCHE D'ENCYCLOPÉDIE DE POCHE -->

        <?php endif; ?>
    <?php endif; ?>







    <?php if ($typePub == 3) : ?>
        <?php if (!$pack) : ?>
            <!-- RECHERCHE D'OUVRAGE -->

            <div class="result article ouvrage" id="<?= $ARTICLE_ID_ARTICLE ?>">
                <div class="wrapper_meta">
                    <div class="meta">
                        <div>
                           <img src="img/pert_<?php $score=(int)($result->item->scorePercent/10); if($score==0){$score=1;}echo $score; ?>.png" alt="niveau de pertinence évalué à <?=$score?>" class="pertinence">
                            <span class="pages">page <?= $ARTICLE_PAGE . ' à ' . $ARTICLE_PAGEFIN ?></span>
                            <a href="<?= $ARTICLE_HREF ?>">
                                <div class="title"><strong><?= $ARTICLE_TITRE ?></strong></div>
                            </a>
                        </div>
                        <div class="authors">

                            <?= $BLOC_AUTEUR ?>

                        </div>
                    </div>
                </div>
                <div class="contexte"><?= $CONTEXTE ?></div>
                 <div class="state">

                    <?php if ($cfgaArr[0] > 0) : ?>
                        <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                            <?php if ($cfgaArr[0] == 1) echo "Résumé"; else if ($cfgaArr[0] == 2) echo "Première page"; else if ($cfgaArr[0] == 3) echo "Premières lignes"; ?>
                        </a>
                    <?php endif; ?>
                    <?php
                    if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1){
                    ?>
                    <?php if ($cfgaArr[1] > 0) : ?>
                        <a href="<?= $ARTICLE_HREF ?>" class="button">
                            Version HTML
                        </a>
                    <?php endif; ?>
                    <?php if ($cfgaArr[2] > 0) : ?>
                        <?php if ($isPdf) : ?>
                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                            <?php else: ?>
                                <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                                <?php endif; ?>
                                Feuilleter en ligne
                            </a>

                        <?php endif; ?>

                        <?php if ($cfgaArr[3] > 0) : ?>
                            <a
                                href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                class="button"
                                data-webtrends="goToPdfArticle"
                                data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                data-titre=<?=
                                    $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                ?>
                                data-authors=<?=
                                    $ParseDatas->cleanAttributeString(
                                        $ParseDatas->stringifyRawAuthors(
                                            str_replace(
                                                '#',
                                                $ParseDatas::concat_name,
                                                implode($ParseDatas::concat_authors, $authors)
                                            ), 0, ';'
                                        )
                                    )
                                ?>
                                data-comm_rev="<?= ($metaNumero[$NUMERO_ID_NUMPUBLIE]['MOVINGWALL'] == 0) ? 'gratuite' : 'payante' ?>"
                            >
                                Version PDF
                            </a>
                        <?php endif; ?>

                    <?php if ($cfgaArr[5] > 0) : ?>
                            <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                               <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                            </a>
                        <?php endif;
                    }else{
                        if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2){
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
                                    echo '<a href="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'].'" '.(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'])?('class="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'].'"'):'class="button"').'>'.$articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'].'</a>';
                                }
                        }
                    }
                    require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                    ?>

                </div>

            </div>

        <?php else: ?>

            <div class="result numero ouvrage" id="<?= $NUMERO_ID_NUMPUBLIE ?>">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_meta">
                    <div class="meta">
                        <div class="revue_title"><a href="<?= $metaNumero[$NUMERO_ID_NUMPUBLIE]['URL_REWRITING'] ?>--<?= $metaNumero[$NUMERO_ID_NUMPUBLIE]['ISBN'] ?>.htm" class="title_little_blue"><span class="title_little_blue"><?= $NUMERO_TITRE . '. ' . $NUMERO_SOUS_TITRE ?> </span></a> <strong>(<?= $NOM_EDITEUR ?>, <?= $NUMERO_ANNEE ?>)</strong></div>
                        <div class="title">dans
                            <a href="<?= $REVUE_HREF ?>"><strong><?= $REVUE_TITRE ?></strong></a>
                        </div>

                        <div class="authors">
                            <?php if ($typeNumPublie == 1 && trim($BLOC_AUTEUR) <> '') : ?>
                                Sous la direction de
                            <?php endif; ?>
                            <?= $BLOC_AUTEUR ?>
                        </div>
                    </div>
                </div>

                <div class="contexte"><?= $NUMERO_MEMO ?> ...</div>
                <div class="state">
                    <a href="<?= $NUMERO_HREF ?>"  class="button">Présentation/Sommaire</a>
                    <?php
                    require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                    ?>
                    <a href="#" class="button" onclick="cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                </div>
                <div class="pertinent_articles" id="__pertinent_[NUMERO_ID_NUMPUBLIE]">
                    [LISTE_RESULTAT_ARTICLES_CONTRIB_OUVRAGE]
                    <div class="meta">
                        <div>
                            <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                            <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                            <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                        </div>
                        [BLOC_NUMERO_TYPE_NUMPUBLIE_1]
                        <div class="authors">
                            [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                            [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                        </div>
                        [/BLOC_NUMERO_TYPE_NUMPUBLIE_1]
                        <div class="contexte">[CONTEXTE]</div>
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]
                    </div>
                </div>
                <hr class="grey" />
                [/LISTE_RESULTAT_ARTICLES_CONTRIB_OUVRAGE]
            </div>
            <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=[ARTICLE_TITRE]&amp;rft.title=[REVUE_TITRE]&amp;rft.issn=[REVUE_ISSN]&amp;rft.date=[NUMERO_ANNEE]&amp;rft.volume=[NUMERO_VOLUME]&amp;rft.issue=[NUMERO_NUMERO]&amp;rft.spage=[ARTICLE_PAGE_DEBUT]&amp;rft.epage=[ARTICLE_PAGE_FIN]&amp;rft.au=[BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_AUTEURS]&amp;rft_id=info:doi/[ARTICLE_DOI]&amp;rft_id=[ARTICLE_HREF]"></span>-->
            </div>

            <!-- FIN DE RECHERCHE D'OUVRAGE -->

        <?php endif; ?>
    <?php endif; ?>



    <?php if ($typePub == 1) : ?>
        <?php if (!$pack) : ?>
            <!-- RECHERCHE DE REVUE -->

            <div class="result article revue" id="<?= $ARTICLE_ID_ARTICLE ?>">
                <div class="wrapper_meta">
                    <div class="meta">
                        <div>
                            <img src="img/pert_<?php $score=(int)($result->item->scorePercent/10); if($score==0){$score=1;}echo $score; ?>.png" alt="niveau de pertinence évalué à <?=$score?>" class="pertinence">
                            <span class="pages">page <?= $ARTICLE_PAGE . ' à ' . $ARTICLE_PAGEFIN ?></span>
                            <div class="title"><a href="<?= $ARTICLE_HREF ?>"><strong><?= $ARTICLE_TITRE ?></strong></a></div>
                        </div>
                        <div class="authors">
                            <?= $BLOC_AUTEUR ?>
                        </div>
                    </div>
                </div>
                <div class="contexte"><?= $CONTEXTE ?></div>
                <div class="state">

                </div>
                 <div class="state">

                    <?php if ($cfgaArr[0] > 0) : ?>
                        <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                            <?php if ($cfgaArr[0] == 1) echo "Résumé"; else if ($cfgaArr[0] == 2) echo "Première page"; else if ($cfgaArr[0] == 3) echo "Premières lignes"; ?>
                        </a>
                    <?php endif; ?>
                    <?php
                    if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1){
                    ?>
                    <?php if ($cfgaArr[1] > 0) : ?>
                        <a href="<?= $ARTICLE_HREF ?>" class="button">
                            Version HTML
                        </a>
                    <?php endif; ?>
                    <?php if ($cfgaArr[2] > 0) : ?>
                        <?php if ($isPdf) : ?>
                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                            <?php else: ?>
                                <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                                <?php endif; ?>
                                Feuilleter en ligne
                            </a>

                        <?php endif; ?>

                        <?php if ($cfgaArr[3] > 0) : ?>
                            <a
                                href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                class="button"
                                data-webtrends="goToPdfArticle"
                                data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                data-titre=<?=
                                    $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                ?>
                                data-authors=<?=
                                    $ParseDatas->cleanAttributeString(
                                        $ParseDatas->stringifyRawAuthors(
                                            str_replace(
                                                '#',
                                                $ParseDatas::concat_name,
                                                implode($ParseDatas::concat_authors, $authors)
                                            ), 0, ';'
                                        )
                                    )
                                ?>
                                data-comm_rev="<?= ($metaNumero[$NUMERO_ID_NUMPUBLIE]['MOVINGWALL'] == 0) ? 'gratuite' : 'payante' ?>"
                            >
                                Version PDF
                            </a>
                        <?php endif; ?>

                       <?php if ($cfgaArr[5] > 0) : ?>
                            <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                               <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                            </a>
                        <?php endif;
                    }else{
                        if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2){
                            if (strpos($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'], 'wrapper_buttons_add-to-cart') !== false) {
                                    echo '<a href="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'] . '" '
                                            . (isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS']) ? ('class="' . $articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'] . '"') : '')
                                            . 'data-webtrends="goToMonPanier" '
                                            . 'data-prix_article="' . number_format($ARTICLE_PRIX, 2, '.', '') . '" '
                                            . 'data-id_article="' . $ARTICLE_ID_ARTICLE . '" '
                                            . 'data-titre=' . $ParseDatas->cleanAttributeString($ARTICLE_TITRE)  . ' '
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
                                    echo '<a href="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'].'" '.(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'])?('class="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'].'"'):'class="button"').'>'.$articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'].'</a>***';
                                }
                        }
                    }
                    require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                    ?>
                </div>
            </div>

        <?php else: ?>

            <div class="result numero revue" id="<?= $NUMERO_ID_NUMPUBLIE ?>">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_contexte wrapper_meta">
                    <a href="<?= $NUMERO_HREF ?>">
                        <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <div class="revue_title">
                            <a href="<?= $REVUE_HREF ?>" class="title_little_blue"><span class="title_little_blue"><?= $REVUE_TITRE ?></span></a>
                            <strong><?= $NUMERO_ANNEE ?>/<?= $NUMERO_NUMERO ?> (<?= $NUMERO_VOLUME ?>)</strong>
                        </div>
                        <div class="numero_title">
                            <a href="<?= $NUMERO_HREF ?>">
                                <strong><?= $NUMERO_TITRE ?><?php if (trim($NUMERO_SOUS_TITRE) != '') echo ". $NUMERO_SOUS_TITRE"; ?></strong>
                            </a>
                        </div>
                        <div class="authors">

                        </div>
                    </div>
                    <div class="contexte"><?= $NUMERO_MEMO ?></div>
                </div>
                <div class="state">
                    <a href="<?= $NUMERO_HREF ?>" class="button">Présentation/Sommaire</a>
                    <?php
                    require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                    ?>
                    <a href="#" class="button" onclick="cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                </div>
                <div class="pertinent_articles" id="__pertinent_<?= $NUMERO_ID_NUMPUBLIE ?>">

                    <div class="meta">
                        <div>
                            <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                            <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                            <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                            [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                        </div>
                        <div class="contexte">[CONTEXTE]</div>
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]
                    </div>
                </div>
                <hr class="grey" />

            </div>
            <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=[ARTICLE_TITRE]&amp;rft.title=[REVUE_TITRE]&amp;rft.issn=[REVUE_ISSN]&amp;rft.date=[NUMERO_ANNEE]&amp;rft.volume=[NUMERO_VOLUME]&amp;rft.issue=[NUMERO_NUMERO]&amp;rft.spage=[ARTICLE_PAGE_DEBUT]&amp;rft.epage=[ARTICLE_PAGE_FIN]&amp;rft.au=[BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_AUTEURS]&amp;rft_id=info:doi/[ARTICLE_DOI]&amp;rft_id=[ARTICLE_HREF]"></span>-->
            </div>

            <!-- FIN DE RECHERCHE DE REVUE -->

        <?php endif; ?>
    <?php endif; ?>

    <?php if ($typePub == 2) : ?>
        <?php if (!$pack) : ?>
            <!-- RECHERCHE DE MAGAZINE -->

            <div class="result article magazine" id="<?= $ARTICLE_ID_ARTICLE ?>">

                <div class="wrapper_meta">
                    <div class="meta">
                        <div>
                           <img src="img/pert_<?php $score=(int)($result->item->scorePercent/10); if($score==0){$score=1;}echo $score; ?>.png" alt="niveau de pertinence évalué à <?=$score?>" class="pertinence">
                            <span class="pages">page <?= $ARTICLE_PAGE . ' à ' . $ARTICLE_PAGEFIN ?></span>
                            <a href="<?= $ARTICLE_HREF ?>">
                                <div class="title"><strong><?= $ARTICLE_TITRE ?></strong></div>
                            </a>
                        </div>
                        <div class="authors">
                            <?= $BLOC_AUTEUR ?>
                        </div>

                    </div>
                </div>
                <div class="contexte"><?= $CONTEXTE ?></div>
                         <div class="state">

                    <?php if ($cfgaArr[0] > 0) : ?>
                        <a href="resume.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>" class="button">
                            <?php if ($cfgaArr[0] == 1) echo "Résumé"; else if ($cfgaArr[0] == 2) echo "Première page"; else if ($cfgaArr[0] == 3) echo "Premières lignes"; ?>
                        </a>
                    <?php endif; ?>
                    <?php
                    if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 1){
                    ?>
                    <?php if ($cfgaArr[1] > 0) : ?>
                        <a href="<?= $ARTICLE_HREF ?>" class="button">
                            Version HTML
                        </a>
                    <?php endif; ?>
                    <?php if ($cfgaArr[2] > 0) : ?>
                        <?php if ($isPdf) : ?>
                            <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE . $getDocUrlParameters ?>&ispdf=<?= $isPdf ?>" class="button">
                            <?php else: ?>
                                <a href="feuilleter.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>&searchTerm=<?= urlencode($searchTerm) ?>" class="button">
                                <?php endif; ?>
                                Feuilleter en ligne
                            </a>

                        <?php endif; ?>

                        <?php if ($cfgaArr[3] > 0) : ?>
                            <a
                                href="load_pdf.php?ID_ARTICLE=<?= $ARTICLE_ID_ARTICLE ?>"
                                class="button"
                                data-webtrends="goToPdfArticle"
                                data-id_article="<?= $ARTICLE_ID_ARTICLE ?>"
                                data-titre=<?=
                                    $ParseDatas->cleanAttributeString($ARTICLE_TITRE)
                                ?>
                                data-authors=<?=
                                    $ParseDatas->cleanAttributeString(
                                        $ParseDatas->stringifyRawAuthors(
                                            str_replace(
                                                '#',
                                                $ParseDatas::concat_name,
                                                implode($ParseDatas::concat_authors, $authors)
                                            ), 0, ';'
                                        )
                                    )
                                ?>
                                data-comm_rev="<?= ($metaNumero[$NUMERO_ID_NUMPUBLIE]['MOVINGWALL'] == 0) ? 'gratuite' : 'payante' ?>"
                            >
                                Version PDF
                            </a>
                        <?php endif; ?>

                       <?php if ($cfgaArr[5] > 0) : ?>
                            <a href="<?= $portalInfo[$ARTICLE_ID_ARTICLE]["URL_PORTAIL"] ?>" class="button">
                               <?= $portalInfo[$ARTICLE_ID_ARTICLE]["NOM_PORTAIL"] ?>
                            </a>
                        <?php endif;
                    }else{
                        if(isset($articlesButtons[$ARTICLE_ID_ARTICLE]) && $articlesButtons[$ARTICLE_ID_ARTICLE]['STATUT'] == 2){
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
                                    echo '<a href="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['HREF'].'" '.(isset($articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'])?('class="'.$articlesButtons[$ARTICLE_ID_ARTICLE]['CLASS'].'"'):'class="button"').'>'.$articlesButtons[$ARTICLE_ID_ARTICLE]['LIB'].'</a>';
                                }
                        }
                    }
                    require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, $ARTICLE_ID_ARTICLE, $authInfos);
                    ?>
                </div>
            <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=[ARTICLE_TITRE]&amp;rft.title=[REVUE_TITRE]&amp;rft.issn=[REVUE_ISSN]&amp;rft.date=[NUMERO_ANNEE]&amp;rft.volume=[NUMERO_VOLUME]&amp;rft.issue=[NUMERO_NUMERO]&amp;rft.spage=[ARTICLE_PAGE_DEBUT]&amp;rft.epage=[ARTICLE_PAGE_FIN]&amp;rft.au=[BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_AUTEURS]&amp;rft_id=info:doi/[ARTICLE_DOI]&amp;rft_id=[ARTICLE_HREF]"></span>-->
            </div>

        <?php else: ?>

            <div class="result numero magazine" id="<?= $NUMERO_ID_NUMPUBLIE ?>">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_contexte wrapper_meta">
                    <a href="<?= $NUMERO_HREF ?>">
                        <img src="/<?= $vign_path ?>/<?= $ARTICLE_ID_REVUE ?>/<?= $ARTICLE_ID_NUMPUBLIE ?>_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <div class="revue_title">
                            <a href="<?= $REVUE_HREF ?>" class="title_little_blue">
                                <span class="title_little_blue"><?= $REVUE_TITRE ?></span>
                            </a>
                            <strong><?= $NUMERO_ANNEE ?>/<?= $NUMERO_NUMERO ?> (<?= $NUMERO_VOLUME ?>)</strong>
                        </div>
                        <div class="numero_title">
                            <a href="<?= $NUMERO_HREF ?>">
                                <strong><?= $NUMERO_TITRE ?><?php if (trim($NUMERO_SOUS_TITRE) != '') echo ". $NUMERO_SOUS_TITRE"; ?></strong>
                            </a>
                        </div>
                        <div class="authors">

                        </div>
                    </div>
                    <div class="contexte"><?= $NUMERO_MEMO ?></div>
                </div>
                <div class="state">
                    <a href="<?= $NUMERO_HREF ?>"  class="button">Présentation/Sommaire</a>
                    <?php
                    require_once(__DIR__.'/../CommonBlocs/actionsBiblio.php');
                    checkBiblio($ARTICLE_ID_REVUE, $ARTICLE_ID_NUMPUBLIE, null, $authInfos);
                    ?>
                    <a href="#" class="button" onclick="cairn_search_deploy_pertinent_articles('<?= $NUMERO_ID_NUMPUBLIE ?>', this);">Articles les plus pertinents</a>
                </div>
                <div class="pertinent_articles" id="__pertinent_[NUMERO_ID_NUMPUBLIE]">
                    [LISTE_RESULTAT_ARTICLES_MAGAZINE]
                    <div class="meta">
                        <div>
                            <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                            <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                            <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                            [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                        </div>
                        <div class="contexte">[CONTEXTE]</div>
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]
                    </div>
                </div>
                <hr class="grey" />
                [/LISTE_RESULTAT_ARTICLES_MAGAZINE]
            </div>
            <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=[ARTICLE_TITRE]&amp;rft.title=[REVUE_TITRE]&amp;rft.issn=[REVUE_ISSN]&amp;rft.date=[NUMERO_ANNEE]&amp;rft.volume=[NUMERO_VOLUME]&amp;rft.issue=[NUMERO_NUMERO]&amp;rft.spage=[ARTICLE_PAGE_DEBUT]&amp;rft.epage=[ARTICLE_PAGE_FIN]&amp;rft.au=[BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_AUTEURS]&amp;rft_id=info:doi/[ARTICLE_DOI]&amp;rft_id=[ARTICLE_HREF]"></span>-->
            </div>

            <!-- FIN DE RECHERCHE DE MAGAZINE -->

        <?php endif; ?>
    <?php endif; ?>

    <?php if ($typePub == 4) : ?>
        <?php if (!$pack) : ?>
            <!-- RECHERCHE DE ETAT DU MONDE -->
            [BLOC_TYPEPUB_EDM]
            <div class="result article magazine" id="[ARTICLE_ID_ARTICLE]">
                <h2><?= $typePubTitle ?></h2>
                <div class="wrapper_meta">
                    <a href="[ARTICLE_HREF]">
                        <img src="./vign_rev/[ARTICLE_ID_REVUE]/[ARTICLE_ID_NUMPUBLIE]_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <a href="[ARTICLE_HREF]">
                            <div class="title"><strong>[ARTICLE_TITRE]</strong></div>
                        </a>
                        <div class="authors">
                            [BLOC_AUTEURS]
                            <span class="author">
                                [AUTEUR_PRENOM] [AUTEUR_NOM]
                                [BLOC_PLUSDEDEUX] <em>et al.</em> [/BLOC_PLUSDEDEUX]
                                [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX]
                            </span>
                            [/BLOC_AUTEURS]
                        </div>
                        <div class="revue_title">Dans
                            <a href="[REVUE_HREF]" class="title_little_blue"><span class="title_little_blue">[REVUE_TITRE]</span></a>
                            <strong>([EDITEUR_NOM_EDITEUR], [BLOC_NUMERO_VOLUME][NUMERO_VOLUME] [/BLOC_NUMERO_VOLUME][NUMERO_ANNEE])</strong>
                        </div>
                    </div>
                </div>
                <div class="contexte">[CONTEXTE]</div>
                <div class="state">
                    [LISTE_CONFIG_ARTICLE]
                    <a href="[ARTICLE_LIBELLE_HREF]" class="button">
                        [ARTICLE_LIBELLE_LIBELLE]
                    </a>
                    [/LISTE_CONFIG_ARTICLE]

                    [BLOC_CREDIT_INST]
                    [BLOC_ARTICLE_ACHAT]
                    <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                    [/BLOC_ARTICLE_ACHAT]
                    [/BLOC_CREDIT_INST]

                    [BLOC_CAIRN_INST_ACHAT]
                    [BLOC_CREDIT_INST_OFF]
                    [BLOC_ARTICLE_ACHAT]
                    <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                        <span class="button first">Consulter</span>
                        <span class="icon icon-add-to-cart"></span>
                        <span class="button last">[ARTICLE_PRIX] €</span>
                    </a>
                    [/BLOC_ARTICLE_ACHAT]
                    [/BLOC_CREDIT_INST_OFF]
                    [/BLOC_CAIRN_INST_ACHAT]

                    [BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                    <a href="[URL]&amp;AJOUTBIBLIO=[ARTICLE_ID_ARTICLE]#[ARTICLE_ID_ARTICLE]" class="icon icon-add-biblio">&#160;</a>
                    [/BLOC_ARTICLE_BIBLIO_AJOUT_ON]
                    [BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                    <span class="infoajout">Ajout&eacute; &agrave; <a href="./biblio.php" class="yellow"><strong>ma bibliographie</strong></a> <span class="icon icon-remove-biblio"></span></span>
                    [/BLOC_ARTICLE_BIBLIO_AJOUT_OFF]
                </div>
                <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=[ARTICLE_TITRE]&amp;rft.title=[REVUE_TITRE]&amp;rft.issn=[REVUE_ISSN]&amp;rft.date=[NUMERO_ANNEE]&amp;rft.volume=[NUMERO_VOLUME]&amp;rft.issue=[NUMERO_NUMERO]&amp;rft.spage=[ARTICLE_PAGE_DEBUT]&amp;rft.epage=[ARTICLE_PAGE_FIN]&amp;rft.au=[BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_AUTEURS]&amp;rft_id=info:doi/[ARTICLE_DOI]&amp;rft_id=[ARTICLE_HREF]"></span>-->
            </div>
            [/BLOC_TYPEPUB_EDM]
        <?php else: ?>
            [BLOC_TYPEPUB_EDM_NUM]
            <div class="result numero magazine" id="[NUMERO_ID_NUMPUBLIE]">
                <h2>Dossier de l'État du monde</h2>
                <div class="wrapper_meta">
                    <a href="resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=[NUMERO_HREF]&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]">
                        <img src="./vign_rev/[NUMERO_ID_REVUE]/[NUMERO_ID_NUMPUBLIE]_L61.jpg" alt="" class="small_cover"/>
                    </a>
                    <div class="meta">
                        <div class="numero_title">
                            <a href="[NUMERO_HREF]">
                                <strong>[NUMERO_TITRE][BLOC_NUMERO_SOUS_TITRE]. [NUMERO_SOUS_TITRE][/BLOC_NUMERO_SOUS_TITRE]</strong>
                            </a>
                        </div>
                        <div class="revue_title">dans
                            <a href="[REVUE_HREF]" class="title_little_blue">
                                <span class="title_little_blue">[REVUE_TITRE]</span>
                            </a>
                            <strong>([EDITEUR_NOM_EDITEUR], [BLOC_NUMERO_VOLUME][NUMERO_VOLUME] [/BLOC_NUMERO_VOLUME][NUMERO_ANNEE])</strong>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS]
                            <span class="author">
                                [AUTEUR_PRENOM] [AUTEUR_NOM]
                                [BLOC_PLUSDEDEUX] <em>et al.</em> [/BLOC_PLUSDEDEUX]
                                [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX]
                            </span>
                            [/BLOC_AUTEURS]
                        </div>
                    </div>
                </div>
                <div class="wrapper_contexte">
                    <div class="contexte">[NUMERO_MEMO]</div>
                </div>
                <div class="state">
                    <a href="resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=[NUMERO_HREF]&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]"  class="button">Présentation/Sommaire</a>
                    [BLOC_NUMERO_BIBLIO_AJOUT_ON]
                    <a href="[URL]&amp;AJOUTBIBLIO=[NUMERO_ID_NUMPUBLIE]" class="icon icon-add-biblio">&#160;</a>
                    [/BLOC_NUMERO_BIBLIO_AJOUT_ON]
                    <a href="#" class="button" onclick="cairn_search.deploy_pertinent_articles('resultats_recherche.php?MOV=0&amp;SESS=[NUM_SESSION]&amp;BLOC=LISTE_RESULTAT_ARTICLES_EDM&amp;ID_REVUE=&amp;ID_NUMPUBLIE=[NUMERO_ID_NUMPUBLIE]', '#__pertinent_[NUMERO_ID_NUMPUBLIE]', this);">Chapitres les plus pertinents</a>
                </div>
                <div class="pertinent_articles" id="__pertinent_[NUMERO_ID_NUMPUBLIE]">
                    [LISTE_RESULTAT_ARTICLES_EDM]
                    <div class="meta">
                        <div>
                            <img src="img/pert_[ARTICLE_PERTI].png" alt="niveau de pertinence évalué à [ARTICLE_PERTI]" class="pertinence"/>
                            <span class="pages">page [ARTICLE_PAGE_DEBUT] &agrave; [ARTICLE_PAGE_FIN]</span>
                            <div class="title">[ARTICLE_TITRE][BLOC_ARTICLE_SOUSTITRE]. [ARTICLE_SOUSTITRE][/BLOC_ARTICLE_SOUSTITRE]</div>
                        </div>
                        <div class="authors">
                            [BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][BLOC_PLUSDEDEUX] <em>et al.</em>[/BLOC_PLUSDEDEUX]
                            [BLOC_DEUX] et [AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_DEUX][/BLOC_AUTEURS]
                        </div>
                        <div class="contexte">[CONTEXTE]</div>
                        <div class="state">
                            [LISTE_CONFIG_ARTICLE]
                            <a href="[ARTICLE_LIBELLE_HREF]" class="button" [BLOC_CONFIG_ARTICLE_PDF] onclick="javascript:trackPDFViewer('[ARTICLE_ID_ARTICLE]', '[ARTICLE_TITRE_JVSCT]', '[BLOC_AUTEURS][AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][BLOC_DEUX];[AUTEUR_PRENOM_JVSCT] [AUTEUR_NOM_JVSCT][/BLOC_DEUX][/BLOC_AUTEURS]');" [/BLOC_CONFIG_ARTICLE_PDF]>
                               [ARTICLE_LIBELLE_LIBELLE]
                        </a>
                        [/LISTE_CONFIG_ARTICLE]

                        [BLOC_CREDIT_INST]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mes_demandes.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="button">Demander cet article</a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST]

                        [BLOC_CAIRN_INST_ACHAT]
                        [BLOC_CREDIT_INST_OFF]
                        [BLOC_ARTICLE_ACHAT]
                        <a href="mon_panier.php?ID_ARTICLE=[ARTICLE_ID_ARTICLE]" class="wrapper_buttons_add-to-cart">
                            <span class="button first">Consulter</span>
                            <span class="icon icon-add-to-cart"></span>
                            <span class="button last">[ARTICLE_PRIX] €</span>
                        </a>
                        [/BLOC_ARTICLE_ACHAT]
                        [/BLOC_CREDIT_INST_OFF]
                        [/BLOC_CAIRN_INST_ACHAT]
                    </div>
                </div>
                <hr class="grey" />
                [/LISTE_RESULTAT_ARTICLES_EDM]
            </div>
            <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=[ARTICLE_TITRE]&amp;rft.title=[REVUE_TITRE]&amp;rft.issn=[REVUE_ISSN]&amp;rft.date=[NUMERO_ANNEE]&amp;rft.volume=[NUMERO_VOLUME]&amp;rft.issue=[NUMERO_NUMERO]&amp;rft.spage=[ARTICLE_PAGE_DEBUT]&amp;rft.epage=[ARTICLE_PAGE_FIN]&amp;rft.au=[BLOC_AUTEURS][AUTEUR_PRENOM] [AUTEUR_NOM][/BLOC_AUTEURS]&amp;rft_id=info:doi/[ARTICLE_DOI]&amp;rft_id=[ARTICLE_HREF]"></span>-->
            </div>
            [/BLOC_TYPEPUB_EDM_NUM]
            <!-- FIN DE RECHERCHE DE ETAT DU MONDE -->
        <?php endif; ?>
    <?php endif; ?>

<?php endforeach; ?>
</div>
<script>
    $(function() {
        "use strict";

        //WebTrends : "tracking sur les boutons d'ajout au panier"
        $('a[data-webtrends="goToMonPanier"]').mouseup(function() {
            var $this = $(this);
            if ($this.data('hasAlreadySendEvent')) return;
            $this.data('hasAlreadySendEvent', true);

            //Récupération des informations manquantes sur l'article.
            var webTrend = JSON.parse($.ajax({ type: "POST",
                            url: "index.php?controleur=Revues&action=getInfosAboutArticleForWebTrends",
                            data: {id_article: $this.data('id_article')},
                            async: false
                          }).responseText);

            //Pour les consultations à prendre en compte.
            var w_art_p2 = 'non';
            if ($("meta[name='DCSext.inst_ID']").attr('content') != '') {
                if (webTrend.type_publication == '1') {
                    if ($("meta[name='DCSext.inst_p14']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                } else if (webTrend.type_publication == '3') {
                    if ($("meta[name='DCSext.inst_p16']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                } else if (webTrend.type_publication == '6') {
                    if ($("meta[name='DCSext.inst_p17']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                } else if (webTrend.type_publication == '2') {
                    if ($("meta[name='DCSext.inst_p15']").attr('content') == 'Oui' && webTrend.consultation == 'oui') {
                        w_art_p2 = 'oui';
                    }
                }
            }

            window.wtCairn.sendEvent({
                'content-uid': $this.data('id_article'),
                'article-titre': $this.data('titre'),
                'numero-auteurs': $this.data('authors'),
                'action': 'addToCart',
                'revue-id_revue': webTrend.pn_grid,
                'revue-titre': webTrend.pn_gr,
                'art_p1': webTrend.art_p1,
                'numero-id_numpublie': webTrend.pn_nid,
                'numero-titre': webTrend.pn_ntit,
                'article-nb_pages': webTrend.doc_nb_pages,
                'id_editeur': webTrend.id_editeur,
                'annee_tomaison': webTrend.annee_tomaison,
                'annee_mise_en_ligne': webTrend.annee_mise_en_ligne,
                'editeur': webTrend.editeur,
                'comm_rev' : webTrend.comm_rev,
                'doc_temps_de_lecture' : webTrend.doc_temps_de_lecture,
                'doc_pdf_dispo' : webTrend.doc_pdf_dispo,
                'revue-discipline' : webTrend.discipline_principale,
                'discipline' : webTrend.discipline,
                'sub-discipline' : webTrend.sub_discipline,
                'content-type' : webTrend.pn_type,
                'cleo' : webTrend.cleo,
                'art_p2' : w_art_p2,
                'article-type_commercialisation' : webTrend.comm_art,
                'tx_e' : 'a',
                'tx_u' : '1',
                'tx_s' : getPriceWebTrends($this.data('prix_article'))
            });
        });
        //

        /*
        Cette fonction permet d'avoir le prix formaté pour webTrends.
        */
       function getPriceWebTrends(price) {

           price = price.toString();

           price = price.replace(' €', '');

           if (price.indexOf('.') === -1 && price !== '') {
               price += '.00';
           }

           return price;
       }

    });
</script>
