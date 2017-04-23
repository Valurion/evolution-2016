<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php $this->titre = "Mes factures"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <a class="inactive" href="/">Accueil</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a class="inactive" href="mes_achats.php">Mes achats</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <a href="mes_factures.php">Mes factures</a>
</div>



<div id="body-content" class="biblio">
    <div class="list_articles">
        <div class="wrapper mt1 mb2" style="text-align: center; position: relative;">
            <a href="mes_achats.php" class="search_button" style="position: absolute; left:0;">
                <span class="unicon unicon-round-arrow-black-left left mr6" style="transform: rotate(0.5turn);">➜</span>
                Mes achats
            </a>
            <h1 class="main-title" style="margin: 0; padding: 0; display: inline-block;">Mes Factures</h1>
        </div>

        <div class="articleBody">
            <ul class="list-bills">
                <?php foreach ($commandes as $commande): ?>
                    <?php
                        // Si la facture a une date d'envoi correcte
                        $isDateSending = (trim($commande['DATE'])) || (strpos($commande['DATE'], '0000-00-00') !== false);
                        $hasBillHtml = isset($commande['factures']['htm']);
                        $hasBillPdf = isset($commande['factures']['pdf']);
                    ?>
                    <li>
                        <div class="wrapper grid-g">
                            <div class="grid-offset-1-8 grid-u-1-3">
                                Commande
                                <i class="yellow ml3 bold">n°</i>
                                <strong><?= $commande['NO_COMMANDE'] ?></strong>
                            </div>

                            <div class="grid-u-1-5">
                                <?php if ($isDateSending && ($hasBillHtml || $hasBillPdf)): ?>
                                    <i class="yellow ml3 bold">du</i>
                                    <strong class="ml3"><?= $commande['DATE'] ?></strong>
                                <?php elseif (!$isDateSending && ($hasBillHtml || $hasBillPdf)): ?>
                                    <!-- Aucune date de facturation disponible -->
                                <?php else: ?>
                                    en cours
                                <?php endif; ?>
                            </div>

                            <!-- Facture au format html -->
                            <?php if ($hasBillHtml): ?>
                                <a
                                    class="blue_button grid-u-1-12"
                                    href="load_facture.php?file=<?= $commande['factures']['htm'] ?>"
                                    >HTML
                                </a>
                            <?php else: ?>
                                <span class="grey_button grid-u-1-12">HTML</span>
                            <?php endif; ?>
                            <!-- Facture au format pdf -->
                            <?php if ($hasBillPdf): ?>
                                <a
                                    class="blue_button grid-u-1-12 ml1e"
                                    href="load_facture.php?file=<?= $commande['factures']['pdf'] ?>"
                                    >PDF
                                </a>
                            <?php else: ?>
                                <span class="grey_button grid-u-1-12 ml1e">PDF</span>
                            <?php endif; ?>

                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!-- <hr />
            <ul class="links_list">
            <?php
                // foreach($commandes as $commande){
                //     echo '<li>Facture de la commande '.$commande['NO_COMMANDE'].' du '.$commande['DATE'].' : ';
                //     if($commande['DATE_SENDFACT'] == '' || strpos($commande['DATE_SENDFACT'],'0000-00-00')!== false){
                //         echo "La facture n'a pas encore été générée";
                //     }else{
                //         if(!isset($commande['factures']['htm']) && !isset($commande['factures']['htm'])){
                //             echo 'Non disponible';
                //         }else{
                //             if(isset($commande['factures']['htm'])){
                //                 echo '[<a href="load_facture.php?file='.$commande['factures']['htm'].'">Version HTML</a>]';
                //             }
                //             if(isset($commande['factures']['pdf'])){
                //                 echo '[<a href="load_facture.php?file='.$commande['factures']['pdf'].'">Version PDF</a>]';
                //             }
                //         }
                //     }
                //     echo '</li>';
                // }
            ?>
            </ul> -->
        </div>
    </div>
</div>
</div>
