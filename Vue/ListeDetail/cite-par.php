<?php
$this->titre = 'Publications citant ' . $article["ARTICLE_ID_ARTICLE"];

$typePub = ($revue['TYPEPUB'] == 1 ? 'revue' : 'ouvrage'); //Pas de tab sélectionné...
include (__DIR__ . '/../CommonBlocs/tabs.php');

$typeNum_suffixe = "";
if($typePub == 'revue'){
    $urlOnglet = "./";
    $urlRevue = "./revue-".$revue["URL_REWRITING"];
    $urlNumero = "revue-".$revue["URL_REWRITING"]."-".$numero["NUMERO_ANNEE"]."-".$numero["NUMERO_NUMERO"];
    if(empty($article)){
        $urlArticle = "";
    }else{
        $urlArticle = "revue-".$revue["URL_REWRITING"]."-".$numero["NUMERO_ANNEE"]."-".$numero["NUMERO_NUMERO"]."-page-".$article["ARTICLE_PAGE_DEBUT"];
    }
}else{
    $urlOnglet = "./ouvrages.php";
    $urlRevue = $numero["NUMERO_URL_REWRITING"]."--".$numero["NUMERO_ISBN"];
    if(empty($article)){
        $urlArticle = "";
    }else{
        $urlArticle = $numero["NUMERO_URL_REWRITING"]."--".$numero["NUMERO_ISBN"]."-page-".$article["ARTICLE_PAGE_DEBUT"];
    }

    if ($numero["NUMERO_TYPE_NUMPUBLIE"] == 1) {
        $typeNum_suffixe = " collectif";
    }
}
?>
<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="<?=$urlOnglet?>"><?= ucfirst($typePub)?>s</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="./<?php echo $urlRevue; ?>.htm"><?php echo ucfirst($typePub=="encyclopédie"?"ouvrage":$typePub); ?><?php echo ($typeNum_suffixe != '' ? $typeNum_suffixe : ''); ?></a>
    <span class="icon-breadcrump-arrow icon"></span>
    <?php if ($typePub == "revue" || $typePub == "magazine") { ?>
        <a class="inactive" href="./<?php echo $urlNumero; ?>.htm">Num&#233;ro</a>
        <span class="icon-breadcrump-arrow icon"></span>
    <?php } ?>
    <?php if(!empty($article)){?>
    <a class="inactive" href="<?= $urlArticle?>.htm">Article</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <?php } ?>
    <a href="#">Cité&nbsp;par...</a>
</div>
<div class="page-cite-par" id="body-content">
    <div id="page_numero">
        <h1 class="main-title"><span class="subinfos">Articles ou publications citant</span></h1>
        <?php if(!empty($article)){?>
            <h1 class="main_title"><a href="revue-<?= $revue["URL_REWRITING"] ?>-<?= $numero["NUMERO_ANNEE"] ?>-<?= $numero["NUMERO_NUMERO"] ?>-page-<?= $article["ARTICLE_PAGE_DEBUT"] ?>.htm"><?php echo $article["ARTICLE_TITRE"]; ?></a></h1>
        <?php } else { ?>
            <h1 class="main_title"><a href="revue-<?= $revue["URL_REWRITING"] ?>-<?= $numero["NUMERO_ANNEE"] ?>-<?= $numero["NUMERO_NUMERO"] ?>.htm"><?php echo $numero["NUMERO_TITRE"]; ?></a></h1>
        <?php } ?>
        <h1 class="main-title">
            <span class="subinfos">
                <h3 class="text_medium title"><a class="title_little_blue" href="<?= $typePub=="revue"?"revue":"collection" ?>-<?= $revue["URL_REWRITING"] ?>.htm"><?php echo $revue["TITRE"]; ?></a><?php echo ($numero["NUMERO_VOLUME"]==''?'':(' '.$numero["NUMERO_VOLUME"])); ?>, <?php echo $numero["NUMERO_ANNEE"]; ?><?php echo ($numero["NUMERO_NUMERO"]==''?'':('/'.$numero["NUMERO_NUMERO"])); ?></h3>
            </span>
        </h1>

        &nbsp;<br>
        <?php if(count($referencedByR) > 0) { ?>
        <h1 class="main-title" id="cite-par-revue">
            <span>Articles de revues</span>
        </h1>
        <?php } ?>

        <?php foreach ($referencedByR as $refBy) {
            
            // Affichage de la source uniquement si le titre est défini
            if($refBy["TITRE_ARTSOURCE"] != ''){

                // Définition du/des auteur/s
                $refAuteur = "";

                // Pas d'auteur renseigné, on le récupère depuis le tableau des auteurs
                if($refBy["AUTEUR_SOURCE"] == '') {
                    foreach($auteurs[$refBy["ID_ARTICLE"]] as $auteur) {$refAuteur .= $auteur.", ";}
                    $refAuteur = rtrim($refAuteur, ", ");
                }
                // Récupération standard
                else {
                    $refAuteur = $refBy["AUTEUR_SOURCE"];
                }
                
                ?>
                <?php if($refBy["TYPEF"]=='E'){?>
                    <a href="<?= $refBy["DOI_SOURCE"] ?>">
                <?php }else{?>
                    <a href="article.php?ID_ARTICLE=<?= $refBy["ID_ARTICLE"] ?>">
                <?php }?>
                <div class="free LHeight" style="clear:both; min-height:60px;">
                    <div class="reference"><img alt="" src="./img/<?= $refBy["TYPEF"]=='E'?'enligne.png':'enligneCairn.png'?>"></div>
                    <div class="meta-cite"><strong><?= $refAuteur ?></strong>, <span class="Trebuchet">«&nbsp;<?= $refBy["TITRE_ARTSOURCE"] ?>&nbsp;», <em><?= $refBy["TITRE_OUVSOURCE"] ?></em> <?= $refBy["ANNEE_SOURCE"] ?><?= ($refBy["NUMERO_SOURCE"]==''?'':'/'.$refBy["NUMERO_SOURCE"]) ?><?= ($refBy["VOLUME_SOURCE"]==''?'':' ('.$refBy["VOLUME_SOURCE"].')') ?>.</span></div>
                </div></a>
            <?php } 
            } ?>

        &nbsp;<br>
        <?php if(count($referencedByM) > 0) { ?>
        <h1 class="main-title" id="cite-par-ouvrage">
            <span>Articles de magazines</span>
        </h1>
        <?php } ?>

        <?php foreach ($referencedByM as $refBy) {
            
            // Affichage de la source uniquement si le titre est défini
            if($refBy["TITRE_ARTSOURCE"] != ''){

                // Définition du/des auteur/s
                $refAuteur = "";

                // Pas d'auteur renseigné, on le récupère depuis le tableau des auteurs
                if($refBy["AUTEUR_SOURCE"] == '') {
                    foreach($auteurs[$refBy["ID_ARTICLE"]] as $auteur) {$refAuteur .= $auteur.", ";}
                    $refAuteur = rtrim($refAuteur, ", ");
                }
                // Récupération standard
                else {
                    $refAuteur = $refBy["AUTEUR_SOURCE"];
                }
                
                ?>
                <?php if($refBy["TYPEF"]=='E'){?>
                    <a href="<?= $refBy["DOI_SOURCE"] ?>">
                <?php }else{?>
                    <a href="article.php?ID_ARTICLE=<?= $refBy["ID_ARTICLE"] ?>">
                <?php }?>
                <div class="free LHeight" style="clear:both; min-height:60px;">
                    <div class="reference"><img alt="" src="./img/<?= $refBy["TYPEF"]=='E'?'enligne.png':'enligneCairn.png'?>"></div>
                    <div class="meta-cite"><strong><?= $refAuteur ?></strong>, <span class="Trebuchet">«&nbsp;<?= $refBy["TITRE_ARTSOURCE"] ?>&nbsp;», <em><?= $refBy["TITRE_OUVSOURCE"] ?></em> <?= $refBy["ANNEE_SOURCE"] ?><?= ($refBy["NUMERO_SOURCE"]==''?'':'/'.$refBy["NUMERO_SOURCE"]) ?><?= ($refBy["VOLUME_SOURCE"]==''?'':' ('.$refBy["VOLUME_SOURCE"].')') ?>.</span></div>
                </div></a>
            <?php } 
            } ?>

        &nbsp;<br>
        <?php if(count($referencedByO) > 0) { ?>
        <h1 class="main-title" id="cite-par-ouvrage">
            <span>Ouvrages</span>
        </h1>
        <?php } ?>

        <?php foreach ($referencedByO as $refBy) {
            if($refBy["AUTEUR_SOURCE"] != '' && $refBy["TITRE_ARTSOURCE"] != ''){
            ?>
            <?php if($refBy["TYPEF"]=='E'){?>
                <a href="<?= $refBy["DOI_SOURCE"] ?>">
            <?php }else{?>
                <a href="article.php?ID_ARTICLE=<?= $refBy["ID_ARTICLE"] ?>">
            <?php }?>
            <div class="free LHeight" style="clear:both; min-height:60px;">
                <div class="reference"><img alt="" src="./img/<?= $refBy["TYPEF"]=='E'?'enligne.png':'enligneCairn.png'?>"></div>
                <div class="meta-cite"><strong><?= $refBy["AUTEUR_SOURCE"] ?></strong>, <span class="Trebuchet">«&nbsp;<?= $refBy["TITRE_ARTSOURCE"] ?>&nbsp;», <em><?= $refBy["TITRE_OUVSOURCE"] ?></em> <?= $refBy["ANNEE_SOURCE"] ?><?= ($refBy["NUMERO_SOURCE"]==''?'':'/'.$refBy["NUMERO_SOURCE"]) ?><?= ($refBy["VOLUME_SOURCE"]==''?'':' ('.$refBy["VOLUME_SOURCE"].')') ?>.</span></div>
            </div></a>
            <?php }
            } ?>
    </div>
</div>

