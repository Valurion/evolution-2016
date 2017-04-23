<?php
/**
 * Dedicated View [Coupled with the default method of the controler]
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "Journal " . $revue['TITRE'];
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>



<?php if ($revue['STATUT'] == 0): ?>
    <div class="danger backoffice article-desactivate">
        Cette revue/collection est actuellement désactivée.<br />
        Sur http://cairn-int.info, cette revue/collection <strong>n’apparaîtra pas</strong>. Elle apparaît <strong>uniquement</strong> sur <?= Configuration::get('urlSite') ?>.
    </div>
<?php endif; ?>

<div id="breadcrump">
    <a class="inactive" href="./">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="disc-<?= $curDiscipline?>.htm"><?= $filterDiscipline?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Journal</a>
    <!-- <a href="[DISCIPLINE_HREF]">Discipline ([DISCIPLINE_DISCIPLINE])</a>
<span class="icon-breadcrump-arrow icon"></span> <a href="#">Revue</a> -->
</div>

<div id="body-content">
    <div id="page_revue">
        <?php include(__DIR__.'/Blocs/indexRevue.php'); ?>

        <hr class="grey">

        <h1 class="main_title">List of Issues (<?= count($numeros) ?>)</h1>
        <div class="list_numeros">
            <?php $prev_portail = "" ?>
            <?php
            $done = 0;
            $affilToCNRS = false;
            foreach ($numeros as $numero): $done++;
                /*
                    La condition suivante ne concerne que les revues qui sont supportés par le CNRS
                    Pour éviter de boucler deux fois, on récupère ici l'éventualité qu'un numéro soit affilié au CNRS (selon le TYPE_NUMPUBLIE).
                    Il n'y a pas, à l'heure actuelle, de possibilité pour savoir si une revue est concernée par le partenariat avec le CNRS.
                    Pour l'affichage de la mention, voir en fin de fichier

                    /!\ Le code qui suit est relativement fragile. Si on passe la liste des numéros sur deux pages (comme par exemple sur l'ancien cairn-int), ça ne marchera plus.
                    J'avoue ne pas avoir le temps de réfléchir à une solution plus pérenne pour le moment, désolé.
                */
                if (($affilToCNRS === false) && ($numero['NUMERO_TYPE_NUMPUBLIE'] === '5')) {
                    $affilToCNRS = true;
                }

                $cur_portail = $numero['PORTAIL_NOM_PORTAIL'];
                ?>
                <?php if ($cur_portail != $prev_portail) : $prev_portail = $cur_portail; ?>
                    <?php if ((($done % 2) == 0) && $done > 1) : // we need to close and reset ?>
                    </div>
                    <?php $done = 1; ?>
                <?php endif; ?>
                <h1 class="main_title" style="font: bold 20px 'Alegreya SC';"><?= $cur_portail ?></h1>
            <?php endif; ?>
            <?php if (($done % 2) == 1) : ?>
                <div class="grid-g grid-2-list">
                <?php endif; ?>
                <div class="grid-u-1-2 greybox_hover numero">

                    <a href="./<?= "journal-" . $numero['REVUE_URL_REWRITING'] . '-' . ($numero['NUMERO_ANNEE']) . '-' . ($numero['NUMERO_NUMERO']) . '.htm' ?>">
                        <img src="/<?= $vign_path ?>/<?= $numero['NUMERO_ID_REVUE'] . '/' . $numero['NUMERO_ID_NUMPUBLIE'] ?>_L62.jpg" alt="couverture de <?= $numero['NUMERO_TITRE'] ?>" class="small_cover">
                    </a>
                    <div class="meta">
                        <div class="subtitle_medium_grey numero_ref"><?= $numero['NUMERO_ANNEE'] ?>/<?= $numero['NUMERO_NUMERO'] ?>
                            <?php if ($numero['NUMERO_NUMEROA'] != ''): ?>
                                -<?= $numero['NUMERO_NUMEROA'] ?><?php endif; ?>
                            (<?= $numero['NUMERO_VOLUME'] ?>)</div>
                        <h2 class="title_little_blue numero_title">

                            <a href="./<?= "journal-" . $numero['REVUE_URL_REWRITING'] . '-' . ($numero['NUMERO_ANNEE']) . '-' . ($numero['NUMERO_NUMERO']) . '.htm' ?>"><?= $numero['NUMERO_TITRE'] ?></a>

                        </h2>
                        <?php if (Configuration::get('allow_backoffice', false)): ?>
                            <div class="bo-content"><?= $numero['NUMERO_ID_NUMPUBLIE'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (($done % 2) == 0) : ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php
        foreach($revuesPrec as $revuePrec){
            $titrePrec = $revuePrec['TITRE'];
            $libPrec = $revuePrec['LIBELLE'];
            $numerosPrec = $revuePrec['NUMEROS'];
            if($titrePrec != '' && !empty($numerosPrec)){ ?>
                <?php if($titrePrec != $revue['TITRE'] || $libPrec != ''){?>
                    </div>
                    <h2 class="main_title"><?= $libPrec!=''?$libPrec:$titrePrec?></h2>
                    <div class="list_numeros">
                <?php
                    $done = 0;
                } ?>
                    <?php $prev_portail = "" ?>
                    <?php

                    foreach ($numerosPrec as $numero): $done++;
                        $cur_portail = $numero['PORTAIL_NOM_PORTAIL'];
                        ?>
                        <?php if ($cur_portail != $prev_portail) : $prev_portail = $cur_portail; ?>
                            <?php if ((($done % 2) == 0) && $done > 1) : // we need to close and reset ?>
                            </div>
                            <?php $done = 1; ?>
                        <?php endif; ?>
                        <h1 class="main_title" style="font: bold 20px 'Alegreya SC';"><?= $cur_portail ?></h1>
                    <?php endif; ?>
                    <?php if (($done % 2) == 1) : ?>
                        <div class="grid-g grid-2-list">
                        <?php endif; ?>
                        <div class="grid-u-1-2 greybox_hover numero">

                            <?php if ($numero['NUMERO_NB_ARTICLES'] != '0'): ?>
                                <a href="./<?= "journal-" . $revuePrec['URL_REWRITING'] . '-' . ($numero['NUMERO_ANNEE']) . '-' . ($numero['NUMERO_NUMERO']) . '.htm' ?>">
                                    <img src="/<?= $vign_path ?>/<?= $numero['NUMERO_ID_REVUE'] . '/' . $numero['NUMERO_ID_NUMPUBLIE'] ?>_L62.jpg" alt="couverture de <?= $numero['NUMERO_TITRE'] ?>" class="small_cover">
                                </a>
                            <?php else : ?>
                                <span>
                                    <img src="/<?= $vign_path ?>/<?= $numero['NUMERO_ID_REVUE'] . '/' . $numero['NUMERO_ID_NUMPUBLIE'] ?>_L62.jpg" alt="couverture de <?= $numero['NUMERO_TITRE'] ?>" class="small_cover">
                                </span>
                            <?php endif; ?>
                            <div class="meta">
                                <div class="subtitle_medium_grey numero_ref">
                                    <?= $numero['NUMERO_ANNEE'] ?>/<?= $numero['NUMERO_NUMERO'] ?><!--
                                    <?php if ($numero['NUMERO_NUMEROA'] != ''): ?>
                                        -->-<?= $numero['NUMERO_NUMEROA'] ?><!--
                                    <?php endif; ?>-->
                                    (<?= $numero['NUMERO_VOLUME'] ?>)
                                </div>
                                <h2 class="title_little_blue numero_title">

                                    <?php if ($numero['NUMERO_NB_ARTICLES'] != '0'): ?>
                                        <a href="./<?= "journal-" . $revuePrec['URL_REWRITING'] . '-' . ($numero['NUMERO_ANNEE']) . '-' . ($numero['NUMERO_NUMERO']) . '.htm' ?>"><?= $numero['NUMERO_TITRE'] ?></a>
                                    <?php else: ?>
                                        <?= $numero['NUMERO_TITRE'] ?>
                                    <?php endif; ?>
                                </h2>

                                <h2 class="text_medium numero_subtitle"><?= $numero['NUMERO_SOUS_TITRE'] ?></h2>

                                <?php if ($numero['NUMERO_NB_ARTICLES'] == '0'): ?>
                                    <div class="available_soon">Prochainement disponible</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (($done % 2) == 0) : ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach;
            }
        }?>
        <?php if (($done % 2) != 0) : ?>
            </div>
        <?php endif;?>


        <?php if($mostConsultated) { ?>
        <!-- ARTICLES LES PLUS CONSULTES -->
        <div id="MostViewedArticles" class="list_articles clearfix">
            <h1 class="main-title">Most Viewed Articles</h1>

            <!-- Liste des articles -->
            <?php
                // Init
                $item = "";

                // Boucle
                foreach ($mostConsultated as $most) {
                    // Contenu
                    $itemContent = "";
                    if($most['TITRE_ARTICLE'] != "") {$itemContent .= "<div class=\"titre\"><a href=\"abstract-".$most['ID_ARTICLE']."--".$most["URL_EN"].".htm\">".$most['TITRE_ARTICLE']."</a></div>";}
                    if($most['SOUSTITRE_ARTICLE'] != "") {$itemContent .= "<div class=\"sous-titre\">".$most['SOUSTITRE_ARTICLE']."</div>";}
                    if( ($most['AUTEURS']) && (count($most['AUTEURS']) != 0) ) {
                        // Init
                        $auteurs = "";

                        // Boucle
                        foreach ($most['AUTEURS'] as $auteur) {
                            $auteurs .= $auteur["AUTEUR_PRENOM"]." ".$auteur["AUTEUR_NOM"].", ";
                        }

                        $itemContent .= "<div class=\"auteur\">".rtrim($auteurs, " , ")."</div>";
                    }

                    // Canevas
                    $item .= "<div class=\"media media-2 greybox_hover\">
                                <div class=\"media-left\">
                                    <a href=\"./resume.php?ID_ARTICLE=".$most['ID_ARTICLE']."\">
                                        <img class=\"small_cover\" src=\"/".$vign_path."/".$most["ID_REVUE"]."/".$most["ID_NUMPUBLIE"]."_L62.jpg\" alt=\"".$most["ID_NUMPUBLIE"]."\">
                                    </a>
                                </div>
                                <div class=\"media-body\">
                                    ".$itemContent."
                                </div>
                              </div>";
                }
                echo $item;
            ?>
        </div>
        <?php } ?>

</div>
</div>
</div>

<?php
    /* Ce qui suite ne concerne que les numéros de revues affiliés au CNRS */
    if ($affilToCNRS === true) {
        $this->javascripts[] = <<<'EOD'
            $(function()  {
                $('#logos_footer').addClass('logo-plural').removeClass('logo-single');;
            });
EOD;
    }
?>
