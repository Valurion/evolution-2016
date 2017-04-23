<?php
$this->titre = $currentArticle["ARTICLE_TITRE"];

    require_once('Vue/Pages/Blocs/headerArticle.php');

    $this->javascripts[] = '<script type="text/javascript" src="./static/js/article.js"></script>';

// On prépare les libellés, urls, ... etc
$numero_url = $typePub . '-' . $revue["REVUE_URL_REWRITING"] . '-' . $revue["NUMERO_ANNEE"] . '-' . $revue["NUMERO_NUMERO"];

?>
<body id="focus" class="article-focus">
    <header>
        <div style="margin-bottom:0.8em;" id="logo">
            <a href="./"><img title="logo" alt="logo" src="./static/images/logo-cairn-int.png"> </a>
        </div> <!--end #logo-->

        <h3>You are reading</h3>
        <h1><?php echo $currentArticle["ARTICLE_TITRE"]; ?></h1>
        <h4><?php echo $currentArticle["ARTICLE_SOUSTITRE"]; ?></h4>

        <div class="in-out-focus">
            <p class="in-focus"><img alt="icon zen" src="./static/images/icon-usermenu-tools/zen_hover.png">in Zen mode</p>
            <p class="exit-focus"><a href="./<?php echo $numero_url; ?>-page-<?php echo $currentArticle["ARTICLE_PAGE_DEBUT"]; ?>.htm">Exit Zen mode</a></p>
        </div>
    </header>
    <div class="numero meta">
        <div class="authors">
            <?php
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
                    ?></i><a href="./publications-of-<?= $theAutheurNom.'-'.$theAutheurPrenom.'--'.$theAutheurId.'.htm' ?>"><?php echo $theAutheurPrenom . ' ' . $theAutheurNom; ?></a>
<?php } ?>
        </div>

        <div class="others">
            <div class="yellow-annot">Published in</div>
            <h1 class="title_big_blue revue_title"><a href="<?= in_array($typePub, ['revue', 'magazine']) ? './journal.php?ID_REVUE='.$revue['REVUE_ID_REVUE'] : './issue.php?ID_NUMPUBLIE='.$numero['NUMERO_ID_NUMPUBLIE'] ?>"><?= $revue["REVUE_TITRE"] ?></a></h1>
            <h3 class="text_medium reference">
<?php echo $revue["NUMERO_ANNEE"] . ($revue["NUMERO_NUMERO"] != "" ? ("/" . $revue["NUMERO_NUMERO"]) : "") . " " . ($revue["NUMERO_VOLUME"] != '' ? '(' . $revue["NUMERO_VOLUME"] . ')' : ''); ?>
            </h3>
        </div>
        <div class="publisher">
            <div class="yellow-annot">Publisher</div>
            <a href="./publisher.php?ID_EDITEUR=<?= $revue['REVUE_ID_EDITEUR'] ?>"><b><?php echo $revue["EDITEUR_NOM_EDITEUR"]; ?></b></a>
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
<?php echo $htmlDatas["CONTENUS"]; ?>
        <span data-version="13032014" id="transform_data"></span>
    </div>
    <!-- FIN DU TEXTE -->



    <?php
        /* Ce qui suite ne concerne que les articles de revues affiliés au CNRS */
        if ($numero['NUMERO_TYPE_NUMPUBLIE'] === '5'):
    ?>
        <section class="thanks" style="text-align: center;">
            <p class="txtcenter mt2"><i>The English version of this issue is published thanks to the support of the CNRS</i></p>
        </section>
    <?php endif; ?>

    <div style="text-align : center;" class="in-out-focus">
        <p class="in-focus"><img alt="icon zen" src="./static/images/icon-usermenu-tools/zen_hover.png">in Zen mode</p>
        <p class="exit-focus"><a href="./<?php echo $numero_url; ?>-page-<?php echo $currentArticle["ARTICLE_PAGE_DEBUT"]; ?>.htm">Exit Zen mode</a></p>
    </div>

    <a id="jump-top" href="#top" style="display: none;"><img alt="back to top" src="./static/images/jump-top.png"></a>
    <a href="./article.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" class="bookmark">Back<br />to the website</a>
</body>
