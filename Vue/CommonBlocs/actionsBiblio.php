<?php
function checkBiblio($idRevue,$idNumPublie,$idArticle,$authInfos,$action = null){
    $status = 0; // 0 = pas dedans; 1 = dedans
    if($idArticle == null){
        if(isset($authInfos['U'])
                && isset($authInfos['U']['HISTO_JSON']->biblio)
                && in_array($idNumPublie,$authInfos['U']['HISTO_JSON']->biblio)){
            $status = 1;
        }else if(isset($authInfos['G'])
                && isset($authInfos['G']['HISTO_JSON']->biblio)
                && in_array($idNumPublie,$authInfos['G']['HISTO_JSON']->biblio)){
            $status = 1;
        }
    }else{
        if(isset($authInfos['U'])
                && isset($authInfos['U']['HISTO_JSON']->biblio)
                && in_array($idArticle,$authInfos['U']['HISTO_JSON']->biblio)){
            $status = 1;
        }else if(isset($authInfos['G'])
                && isset($authInfos['G']['HISTO_JSON']->biblio)
                && in_array($idArticle,$authInfos['G']['HISTO_JSON']->biblio)){
            $status = 1;
        }
    }

    // Si l'utilisateur n'est pas connecté, la fonction d'ajout à la bibliothèque est inaccessible (#69425)
    // Le blocage s'effectue ici plutôt que dans la fonction ajax pour eviter de devoir gérer le toggle, etc.
    // Valeurs par défaut
    $btn_link_to_add = "biblio.php?id_numpublie=$idNumPublie&id_article=$idArticle";
    $btn_link_to_rem = "biblio.php";
    // Utilisateur connecté
    if(isset($authInfos['U'])) {
        $btn_link_to_add = "javascript:ajax.addToBiblio('".$idNumPublie."','".$idArticle."')";
        $btn_link_to_rem = "javascript:ajax.removeFromBiblio('".$idNumPublie."','".$idArticle."')";
    }  

    if($action == 'usermenu'){
        if($status == 0){?>
            <li id="addToBiblio<?= $idNumPublie ?>-<?= $idArticle ?>">
                <a
                    class="icon icon-usermenu-tools icon-usermenu-tools-bigger-char"
                    href="<?php echo $btn_link_to_add; ?>"
                    data-webtrends="addToBiblio"
                    data-id_article="<?= $idArticle ?>"
                >
                <label><span>Ajouter à ma bibliographie</span></label>
                </a>
            </li>
            <li id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>" style="display:none;">
                <a
                    class="icon icon-usermenu-tools icon-usermenu-tools-lower-chr"
                    href="<?php echo $btn_link_to_rem; ?>"
                >
                <label><span>Supprimer de ma bibliographie</span></label>
                </a>
            </li>
        <?php }else{ ?>
            <li id="addToBiblio<?= $idNumPublie ?>-<?= $idArticle ?>" style="display:none;">
                <a
                    class="icon icon-usermenu-tools icon-usermenu-tools-bigger-char"
                    href="<?php echo $btn_link_to_add; ?>"
                    data-webtrends="addToBiblio"
                    data-id_article="<?= $idArticle ?>"
                >
                <label><span>Ajouter à ma bibliographie</span></label>
                </a>
            </li>
            <li id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>">
                <a
                    class="icon icon-usermenu-tools icon-usermenu-tools-lower-chr"
                    href="<?php echo $btn_link_to_rem; ?>"
                >
                <label><span>Supprimer de ma bibliographie</span></label>
                </a>
            </li>
        <?php }
    }else if($action == 'remove'){?>
        <span class="AJBIB">
            <a
                id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                href="javascript:void(0);"
                class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                data-webtrends="removeFromBiblio"
                data-id_article="<?= $idArticle ?>"
                onclick="ajax.removeFromBiblioPage('<?= $idNumPublie ?>','<?= $idArticle ?>')"
            >
            <label><span>Supprimer de ma bibliographie</span></label>
            </a>
        </span>
    <?php }else{
        // L'article n'a pas été ajouté
        if($status == 0){ ?>
            <!-- Bouton d'ajout à la bibliographie -->
            <a
                id="addToBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                href="<?php echo $btn_link_to_add; ?>"
                class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-bigger-char"
                data-webtrends="addToBiblio"
                data-id_article="<?= $idArticle ?>"
            >
            <label><span>Ajouter à ma bibliographie</span></label>
            </a>

            <!-- Bouton de suppression de la bibliographie -->
            <a
                id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                href="<?php echo $btn_link_to_rem; ?>"
                class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                style="display:none;"
                data-webtrends="removeFromBiblio"
                data-id_article="<?= $idArticle ?>"
            >
            <label><span>Supprimer de ma bibliographie</span></label>
            </a>
        <?php }else{ ?>
            <!-- Bouton d'ajout à la bibliographie -->
            <a
                id="addToBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                href="<?php echo $btn_link_to_add; ?>"
                class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-bigger-char"
                style="display:none;"
                data-webtrends="addToBiblio"
                data-id_article="<?= $idArticle ?>"
            >
            <label><span>Ajouter à ma bibliographie</span></label>
            </a>

            <!-- Bouton de suppression de la bibliographie -->
            <a
                id="removeFromBiblio<?= $idNumPublie ?>-<?= $idArticle ?>"
                href="<?php echo $btn_link_to_rem; ?>"
                class="icon icon-usermenu-tools-tiny icon-usermenu-tools-tiny-lower-char"
                data-webtrends="removeFromBiblio"
                data-id_article="<?= $idArticle ?>"
            >
            <label><span>Supprimer de ma bibliographie</span></label>
            </a>



        <?php }
    }
}
?>
