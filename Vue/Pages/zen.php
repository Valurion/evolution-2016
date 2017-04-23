<?php

    $this->titre = $currentArticle["ARTICLE_TITRE"];

    require_once('Blocs/headerArticle.php');

    $this->javascripts[] = '<script type="text/javascript" src="./static/js/article.js"></script>';

// On prépare les libellés, urls, ... etc
$numero_url = "";
if ($typePub == "revue" || $typePub == "magazine") {
    $numero_url = $typePub . '-' . $revue["REVUE_URL_REWRITING"] . '-' . $revue["NUMERO_ANNEE"] . '-' . $revue["NUMERO_NUMERO"];
} else {
    $numero_url = $numero["NUMERO_URL_REWRITING"] . '--' . $numero["NUMERO_ISBN"];
}
?>
<body id="focus" class="article-focus">
    <header>
        <div style="margin-bottom:0.8em;" id="logo">
            <a href="./"><img title="logo" alt="logo" src="./static/images/logo-cairn.png"> </a>
        </div> <!--end #logo-->

        <h3>Vous lisez</h3>
        <h1><?php echo $currentArticle["ARTICLE_TITRE"]; ?></h1>
        <h4></h4>

        <div class="in-out-focus">
            <p class="in-focus"><img alt="icon zen" src="./static/images/icon-usermenu-tools/zen_hover.png">en mode Zen</p>
            <p class="exit-focus">
                <a id="top-exit-zen" href="./<?php echo $numero_url; ?>-page-<?php echo $currentArticle["ARTICLE_PAGE_DEBUT"]; ?>.htm">
                    Sortir du mode Zen
                </a>
            </p>
        </div>
    </header>
    <div class="numero meta">

        <?php if ($numero['NUMERO_STATUT'] == 0 || $revue['STATUT'] == 0): ?>
            <div class="danger backoffice article-desactivate">
                <?php if ($revue['STATUT'] == 0): ?>
                    Cette revue est actuellement désactivé.<br />
                <?php endif; ?>
                <?php if ($numero['NUMERO_STATUT'] == 0): ?>
                    Ce numéro est actuellement désactivé.<br />
                <?php endif; ?>
                <?php if ($currentArticle['ARTICLE_STATUT'] == 0): ?>
                    Cet article est actuellement désactivé.<br />
                <?php endif; ?>
                Sur http://cairn.info, ce numéro <strong>n’apparaîtra pas</strong>. Il apparaît <strong>uniquement</strong> sur <?= Configuration::get('urlSite') ?>.
            </div>
        <?php endif; ?>
        <div class="authors">
            <?php
            if($auteurs != null){
                foreach ($auteurs as $auteur) {
                    ?>
                    <i><?php
                        echo ( ++$i > 1 ? '<br/>' : '');
                        echo $auteur['AUTEUR_ATTRIBUT'];
                        ?></i><a href="./publications-de-<?= $auteur['AUTEUR_NOM'].'-'.$auteur['AUTEUR_PRENOM'].'--'.$auteur['AUTEUR_ID_AUTEUR'].'.htm' ?>"><?php echo $auteur['AUTEUR_PRENOM'] . ' ' . $auteur['AUTEUR_NOM']; ?></a>
                <?php }
            }else{
                if( $currentArticle['ARTICLE_AUTEUR'] != ''){
                    $theAuthors = explode(",", $currentArticle['ARTICLE_AUTEUR']);
                    $i = 0;
                    foreach ($theAuthors as $theAuthor) {
                        $theauthorParam = explode(':', $theAuthor);
                        $theAutheurPrenom = $theauthorParam[0];
                        $theAutheurNom = $theauthorParam[1];
                        $theAutheurId = $theauthorParam[2];
                        $theAutheurAttribut = $theauthorParam[3];
                        ?>
                        <i><?php
                            echo ( ++$i > 1 ? '<br/>' : '');
                            echo $theAutheurAttribut;
                            ?></i><a href="./publications-de-<?= $theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm' ?>"><?php echo $theAutheurPrenom . ' ' . $theAutheurNom; ?></a>
                    <?php }
                }
            }?>
        </div>
        <div class="others">
            <div class="yellow-annot">Publié dans</div>
            <h1 class="title_big_blue revue_title"><a href="<?= in_array($typePub, ['revue', 'magazine']) ? './revue.php?ID_REVUE='.$revue['REVUE_ID_REVUE'] : './numero.php?ID_NUMPUBLIE='.$numero['NUMERO_ID_NUMPUBLIE'] ?>"><?php echo (($typePub == "revue" || $typePub == "magazine") ? $revue["REVUE_TITRE"] : $numero["NUMERO_TITRE"]); ?></a></h1>
            <h3 class="text_medium reference">
<?php echo $revue["NUMERO_ANNEE"] . ($revue["NUMERO_NUMERO"] != "" ? ("/" . $revue["NUMERO_NUMERO"]) : "") . " " . ($revue["NUMERO_VOLUME"] != '' ? '(' . $revue["NUMERO_VOLUME"] . ')' : ''); ?>
            </h3>
        </div>
        <div class="publisher">
            <div class="yellow-annot">Éditeur</div>
            <a href="./editeur.php?ID_EDITEUR=<?= $revue['REVUE_ID_EDITEUR'] ?>"><b><?php echo $revue["EDITEUR_NOM_EDITEUR"]; ?></b></a>
        </div>
    </div>

    <div class="pages-numbers">
        <div class="current">
            <?php
            echo ($currentArticle["ARTICLE_PAGE_DEBUT"] > 0 ? "Page" . ($currentArticle["ARTICLE_PAGE_FIN"] > 0 ? 's ' : ' ') . $currentArticle["ARTICLE_PAGE_DEBUT"] : '')
            . ($currentArticle["ARTICLE_PAGE_FIN"] > 0 ? (' - ' . $currentArticle["ARTICLE_PAGE_FIN"]) : '');
            ?>
        </div>
    </div>

    <!-- LE TEXTE -->
    <div class="content focus" id="textehtml">
<?php echo isset($htmlDatas["CONTENUS"])?$htmlDatas["CONTENUS"]:''; ?>
        <span data-version="13032014" id="transform_data"></span>
    </div>
    <!-- FIN DU TEXTE -->

    <div style="text-align : center;" class="in-out-focus">
        <p class="in-focus"><img alt="icon zen" src="./static/images/icon-usermenu-tools/zen_hover.png">en mode Zen</p>
        <p class="exit-focus">
            <a id="bottom-exit-zen" href="./<?php echo $numero_url; ?>-page-<?php echo $currentArticle["ARTICLE_PAGE_DEBUT"]; ?>.htm">
                Sortir du mode Zen
            </a>
        </p>
    </div>

    <a id="jump-top" href="#top" style="display: none;"><img alt="back to top" src="./static/images/jump-top.png"></a>
    <a id="right-side-exit-zen" href="./article.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" class="bookmark">Retour<br />vers le site</a>
</body>
