<?php
/*
 * Quelques petits calculs...
 */

//Libellé "Pages x - y" en gérant le fait qu'il n'y aie qu'une page voire pas du tout
$libPage = ($currentArticle["ARTICLE_PAGE_DEBUT"] != '' ? "Page" . ($currentArticle["ARTICLE_PAGE_FIN"] != '' ? 's ' : ' ') . $currentArticle["ARTICLE_PAGE_DEBUT"] : '')
        . ($currentArticle["ARTICLE_PAGE_FIN"] != '' ? (' - ' . $currentArticle["ARTICLE_PAGE_FIN"]) : '');

//Correctif de Dimitry Berté (Cairn) : le 17/12/2015.
$tabArticleTemp = array();
foreach ($articles as $article) {
    if ($article['ARTICLE_STATUT'] == '1') {
        $tabArticleTemp[] = $article;
    }
}

//Article précédent et suivant
for ($ind = 0; $ind < count($tabArticleTemp) && $tabArticleTemp[$ind]['ARTICLE_ID_ARTICLE'] != $currentArticle['ARTICLE_ID_ARTICLE']; $ind++) {

}
$previousArticle = ($ind != 0) ? $tabArticleTemp[$ind - 1] : FALSE;
$nextArticle = ($ind != count($tabArticleTemp) - 1) ? $tabArticleTemp[$ind + 1] : FALSE;


// Définition des liens des articles suivants et précédent
// Précédent
$article_precedent = $previousArticle["ARTICLE_PAGE_DEBUT"];
$link_article_precedent = "$numero_url-page-$article_precedent.htm";

// Suivant
$article_suivant = $nextArticle["ARTICLE_PAGE_DEBUT"];
$link_article_suivant = "$numero_url-page-$article_suivant.htm";

// Définition de l'URL sur PREPROD / BON A TIRER
// Concervation du TOKEN dans l'URL
if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
{
    $link_article_precedent .= "?token=" . $_GET['token'];
    $link_article_suivant 	.= "?token=" . $_GET['token'];
}

// Ajout d'une ancre
//$link_article_precedent .= "#page_header";
//$link_article_suivant   .= "#page_header";

?>

<div class="article_navpages">
    <?php if ($previousArticle !== FALSE) { ?>
        <a class="left blue_button" href="./<?php echo $link_article_precedent; ?>">
            <span class="icon-arrow-white-left icon"></span>
            <?php echo ucfirst($article_libelle); ?> précédent
        </a>
    <?php } ?>
    <span class="current_page">
        <?php if($article["ARTICLE_MODEPAGE"] == 1) {echo $libPage;} ?>
    </span>
    <?php if ($nextArticle !== FALSE) { ?>
        <a class="right blue_button" href="./<?php echo $link_article_suivant; ?>">
            <?php echo ucfirst($article_libelle); ?> suivant
            <span class="icon-arrow-white-right icon"></span>
        </a>
    <?php } ?>
</div>
