            <?php if(isset($authInfos["U"])) { ?>
                <!-- Mon cairn-info -->
                <div class="container-mon-cairn container-fluid">
                    <div class="container">
                        <div class="row clearfix">

                            <!-- Mes recherches -->
                            <div id="moncairn-mes-recherches" class="moncairn-container">
                                <div class="moncairn-title">Mes recherches récentes</div>
                                <div class="moncairn-content">
                                    <ul>
                                        <?php
                                        $recherches = array();
                                        if(isset($authInfos['U']) && isset($authInfos['U']['HISTO_JSON']->recherches)){
                                            $recherches = $authInfos['U']['HISTO_JSON']->recherches;
                                        }else if(isset($authInfos['G']) && isset($authInfos['G']['HISTO_JSON']->recherches)){
                                            $recherches = $authInfos['G']['HISTO_JSON']->recherches;
                                        }
                                        for($ind = 0 ; $ind < count($recherches) && $ind < 10 ; $ind++){
                                            echo '<li>- <a href="resultats_recherche.php?searchTerm='.urlencode($recherches[$ind][0]).'">'.htmlentities($recherches[$ind][0]).'</a></li>';
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <div class="moncairn-footer"><a href="mes_recherches.php">Toutes mes recherches</a></div>
                            </div>

                            <!-- Mes consultations -->
                            <div id="moncairn-mes-consultations" class="moncairn-container">
                                <div class="moncairn-title">Mes consultations récentes</div>
                                <div class="moncairn-content">
                                    <?php
                                        $articles = array();
                                        if(isset($authInfos['U']) && isset($authInfos['U']['HISTO_JSON_ARTICLES'])){
                                            $articles = $authInfos['U']['HISTO_JSON_ARTICLES'];
                                        }else if(isset($authInfos['G']) && isset($authInfos['G']['HISTO_JSON_ARTICLES'])){
                                            $articles = $authInfos['G']['HISTO_JSON_ARTICLES'];
                                        }
                                        for($ind = 0 ; $ind < count($articles) && $ind < 10 ; $ind++){
                                            echo '<li>- <a href="article.php?ID_ARTICLE='.$articles[$ind][0].'">'.$articles[$ind][1].'</a></li>';
                                        }
                                    ?>
                                </div>
                                <div class="moncairn-footer"><a href="mon_historique.php">Toutes mes consultations</a></div>
                            </div>

                            <!-- Mes outils -->
                            <div id="moncairn-mes-outils" class="moncairn-container">
                                <div class="moncairn-title">Mes outils</div>
                                <div class="moncairn-content">
                                    <ul>
                                        <li><a href="./biblio.php">Ma&nbsp;bibliographie</a></li>
                                        <li><a href="./mon_panier.php">Mon panier</a></li>
                                        <li><a href="./mes_achats.php">Mes achats</a></li>
                                        <?php if(isset($authInfos['U']) && isset($authInfos['U']['CREDIT_ARTICLE_SOLDE'])){ ?>
                                        <li><a href="./mon_credit.php">Mon crédit <span class="solde">(<?php echo number_format($authInfos['U']['CREDIT_ARTICLE_SOLDE'], 2, ",", ""); ?>€)</span></a></li>
                                        <?php } ?>
                                        <li><a href="./mes_alertes.php">Mes alertes</a></li>
                                        <li><a href="./mon_compte.php">Mon compte</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End - Mon cairn-info -->
            <?php } ?>