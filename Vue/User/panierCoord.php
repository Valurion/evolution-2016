<?php
$infos = $authInfos['U'];
if(isset($commandeTmp)){
    $infos = $commandeTmp;
}
?>
<!-- VUE AJAX dépendant de #BODY-CONTENT
<div class="biblio mon-panier" id="body-content">-->
    <div id="breadcrump">
        <a class="inactive" href="/">Accueil</a>
        <span class="icon-breadcrump-arrow icon"></span>
        <a class="inactive" href="javascript:ajax.panierStart()">Mon Panier</a>
        <span class="icon-breadcrump-arrow icon"></span>
        <a href="#">Mes coordonnées</a>
    </div>

    <div id="">

        <h1 class="main-title">Mes coordonnées</h1>

        <div id="wrapper_breadcrumb_cart">
            <ol id="breadcrumb_cart">
                <li><span>1</span> Panier</li>
                <li class="black_button"><span>2</span> Coordonnées</li>
                <li><span>3</span> Méthode de paiement</li>
                <li><span>4</span> Paiement</li>
                <li><span>5</span> Accès</li>
            </ol>
        </div>


        <form id="coordo" action="javascript:goToMethodePaiement()">
            <input type="hidden" id="tmpCmdId" value="<?= $tmpCmdId ?>"/>

            <?php if ($livraison == 1): ?>
                <!-- Adresse de livraison -->
                <h2 class="subTitle">Votre adresse de livraison</h2>
                <div class="mb1 overflow-auto">
                    <div class="blue_milk left w45 grid-u-2">
                        <label for="prenom">
                            Prénom
                            <span class="red">*</span>
                        </label>
                        <input required="required" type="text" value="<?= $infos['PRENOM'] ?>" id="prenom" name="prenom" size="20">
                    </div>

                    <div class="blue_milk right w45 grid-u-2">
                        <label for="nom">
                            Nom
                            <span class="red">*</span>
                        </label>
                        <input required="required" type="text" value="<?= $infos['NOM'] ?>" id="nom" name="nom" size="20">
                    </div>
                </div>
                <div class="mb1 overflow-auto">
                    <div class="blue_milk left w45">
                        <label for="adr">
                            Adresse postale
                            <span class="red">*</span>
                        </label>
                        <input required="required" type="text" value="<?= $infos['ADRESSE'] ?>" id="adr" name="adr" size="30">
                    </div>
                </div>
                <div class="mb1">
                    <div class="blue_milk left w30" style="width: 28%;">
                        <label for="cp">
                            Code postal
                            <span class="red">*</span>
                        </label>
                        <input required="required" type="text" value="<?= $infos['CP'] ?>" id="cp" name="cp" size="10">
                    </div>
                    <div class="blue_milk w30" style="margin: 0 34px;width: 28%;">
                        <label for="ville">
                            Ville
                            <span class="red">*</span>
                        </label>
                        <input required="required" type="text" value="<?= $infos['VILLE'] ?>" id="ville" name="ville" size="15">
                    </div>
                    <div class="blue_milk right w30" style="width: 28%;">
                        <label for="pays">
                            Pays
                            <span class="red">*</span>
                        </label>
                        <select class="w100" id="pays" name="pays">
                            <?php if (isset($infos['PAYS']) && $infos['PAYS'] != ''): ?>
                                <option selected=""><?= $infos['PAYS'] ?></option>
                            <?php endif; ?>
                            <option>France</option>
                            <option>Belgique</option>
                            <?php foreach($listePays as $pays): ?>
                                <option><?= $pays ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb3 overflow-auto text-center">
                    <input type="checkbox" id="checksvgadr" checked="" name="checksvgadr">
                    <label for="checksvgadr">Retenir cette adresse pour mes prochaines commandes</label>
                </div>
            <?php endif; ?>

            <h2 class="subTitle">Votre adresse de facturation</h2>

            <!-- Adresse de facturation -->
            <?php
                if ($livraison == 1) {
                    $formFacturation = "display: none";
                    $formToggler     = 1;
                }
                else {
                    $formFacturation = "display: block";
                    $formToggler     = 0;
                }
            ?>

            <?php if($formToggler == 1): ?>
                <div class="mb1">
                    <input type="checkbox" id="checkidemadresse" name="checkidemadresse" checked="" onclick="cairn.affichefact();">
                    <label for="checkidemadresse">Identique à l'adresse de livraison.</label>
                </div>
            <?php endif; ?>

            <div style="<?php echo $formFacturation; ?>" id="adressefact">
                <div class="mb1 overflow-auto">
                    <div class="blue_milk left w45">
                        <label for="fact_nom">
                            Prénom et nom, société ou institution
                            <span class="red">*</span>
                        </label>
                        <input type="text" value="<?= $infos['FACT_NOM'] ?>" id="fact_nom" name="fact_nom" size="40">
                    </div>
                    <div class="blue_milk right w45">
                        <label for="fact_adr">
                            Adresse
                            <span class="red">*</span>
                        </label>
                        <input type="text" value="<?= $infos['FACT_ADR'] ?>" id="fact_adr" name="fact_adr" size="50">
                    </div>
                </div>
                <div class="mb1">
                    <div class="blue_milk w30" style="width: 28%;">
                        <label for="fact_cp">
                            Code postal
                            <span class="red">*</span>
                        </label>
                        <input type="text" value="<?= $infos['FACT_CP'] ?>" id="fact_cp" name="fact_cp">
                    </div>

                    <div class="blue_milk w30" style="margin: 0 34px;width: 28%;">
                        <label for="fact_ville">
                            Ville
                            <span class="red">*</span>
                        </label>
                        <input type="text" value="<?= $infos['FACT_VILLE'] ?>" id="fact_ville" name="fact_ville">
                    </div>
                    <div class="blue_milk w30" style="width: 28%;">
                        <label for="fact_pays">
                            Pays
                            <span class="red">*</span>
                        </label>
                        <select class="w100" id="fact_pays" name="fact_pays">
                            <?php if(isset($infos['FACT_PAYS']) && $infos['FACT_PAYS'] != ''): ?>
                                <option selected=""><?= $infos['FACT_PAYS'] ?></option>
                            <?php endif; ?>
                            <option>France</option>
                            <option>Belgique</option>
                            <?php foreach($listePays as $pays): ?>
                                <option><?= $pays ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb1 overflow-auto text-center">
                    <input type="checkbox" id="checksvgfactadr" checked="" name="checksvgfactadr">&nbsp;
                    <label for="checksvgfactadr">Retenir cette adresse de facturation pour mes prochaines commandes</label>
                </div>
            </div>

            <?php if (isset($okEditeur)): ?>
                <div class="mb1 overflow-auto">
                    <input type="checkbox" id="ok-editeur" name="ok-editeur"<?= $okEditeur === true ? 'checked' : '' ?>>
                    <label for="ok-editeur">J’autorise Cairn à communiquer mes coordonnées à ses éditeurs-partenaires</label>
                </div>
            <?php endif; ?>

            <div class="checkout-bottom-section">
                <button id="panierCoordButton" class="payer checkout-button">Suivant</button>
                <a class="payer checkin-button" href="javascript:ajax.panierStart()">Précédent</a>
            </div>
            <br>

        </form>
    </div>

