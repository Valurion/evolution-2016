<div class="window_modal" id="choix_langues_modal" style="display: none;">
    <div class="info_modal"><a class="close_modal" href="javascript:void(0);" onclick="cairn.close_modal();"></a>

        <div class="thumbnail">
            <figure><img src="/<?= $vign_path ?>/<?= $currentArticle['REVUE_ID_REVUE'] ?>/<?= $currentArticle['NUMERO_ID_NUMPUBLIE'] ?>_L204.jpg" class="big_coverbis" alt="couverture de <?= $currentArticle['ARTICLE_ID_NUMPUBLIE'] ?>"></figure>
            <div>
                <h1><?php echo $currentArticle["REVUE_TITRE"]; ?></h1>
                <p><?php echo $revue["NUMERO_ANNEE"]; ?>/<?php echo $revue["NUMERO_NUMERO"]; ?> <b>(<?php echo $revue["NUMERO_VOLUME"]; ?>)</b></p>
            </div>
        </div>

        <div class="meta">
            <!-- Vers la version FR -->
            <div class="meta-lang meta-fr">
                <span class="note">Cet article est disponible en français sur cairn.info</span>
                <a href="article.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" class="btn" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>><span class="icon icon-round-arrow-right"></span> Version Française</a>
                <h2><a href="article.php?ID_ARTICLE=<?= $currentArticle['ARTICLE_ID_ARTICLE'] ?>" class="" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>><?php echo $currentArticle["ARTICLE_TITRE"]; ?></a></h2>                
                <p class="auteurs">
                    <?php
                        // Init
                        $liste_auteurs      = "";
                        $attribut_auteurs   = "";

                        // Liste des auteurs
                        $i = 0;
                        foreach (explode(',', $currentArticle['ARTICLE_AUTEUR']) as $index => $auteur) {
                            // Explosion des auteurs
                            if($i < 3) {
                                // Découpage de la chaine (Nom/Prénom/ID/Attribut)
                                $auteur = explode(':', $auteur); 

                                // Gestion de l'attribut
                                $attr = "";
                                if($attribut_auteurs != $auteur[3]) {
                                    $attribut_auteurs = $attr = $auteur[3];
                                }

                                // Rendu
                                $liste_auteurs .= "<span>".$attr." ".$auteur[0]." ".$auteur[1]."</span>, ";
                            }
                            $i++;
                        }
                        // Nettoyage
                        $liste_auteurs = rtrim($liste_auteurs, ", ");
                        if($i >= 3) {
                            $liste_auteurs .= "<i>et al.</i>";
                        }
                        // Rendu
                        echo $liste_auteurs;
                    ?>
                </p>
            </div>

            <!-- Vers la version EN -->
            <div class="meta-lang meta-en">
                <span class="note">This article is also available in English on Cairn International Edition</span>
                <a href="http://<?= Configuration::get('crossDomainUrl', 'www.cairn.info') ?>/article.php?ID_ARTICLE=<?= $numero['META_ARTICLE_INT']['ID_ARTICLE'] ?>" class="btn" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>><span class="icon icon-round-arrow-right"></span> English Version</a>
                <h2><a href="http://<?= Configuration::get('crossDomainUrl', 'www.cairn.info') ?>/article.php?ID_ARTICLE=<?= $numero['META_ARTICLE_INT']['ID_ARTICLE'] ?>" class="" <?php if (Configuration::get('allow_backoffice', false)): ?>rel="noreferrer"<?php endif; ?>><?php echo $numero['META_ARTICLE_INT']['TITRE'] ?></a></h2>                
                <p class="auteurs">
                    <?php
                        // Init
                        $liste_auteurs      = "";
                        $attribut_auteurs   = "";

                        // Liste des auteurs
                        $i = 0;
                        foreach (explode(',', $numero['META_ARTICLE_INT']['AUTEUR']) as $index => $auteur) {
                            // Explosion des auteurs
                            if($i < 3) {
                                // Découpage de la chaine (Nom/Prénom/ID/Attribut)
                                $auteur = explode(':', $auteur); 

                                // Gestion de l'attribut
                                $attr = "";
                                if($attribut_auteurs != $auteur[3]) {
                                    $attribut_auteurs = $attr = $auteur[3];
                                }

                                // Rendu
                                $liste_auteurs .= "<span>".$attr." ".$auteur[0]." ".$auteur[1]."</span>, ";
                            }
                            $i++;
                        }
                        // Nettoyage
                        $liste_auteurs = rtrim($liste_auteurs, ", ");
                        if($i >= 3) {
                            $liste_auteurs .= "<i>et al.</i>";
                        }
                        // Rendu
                        echo $liste_auteurs;
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php 
    // Déclencheur
    $this->javascripts[] = "cairn.show_modal('#choix_langues_modal');";
?>

