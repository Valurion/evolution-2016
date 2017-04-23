            <?php if(isset($authInfos["U"])) { ?>
                <!-- Mon cairn-info -->
                <div class="container-mon-cairn container-fluid">
                    <div class="container">
                        <div class="row clearfix">

                            <!-- Mes recherches -->
                            <div id="moncairn-mes-recherches" class="moncairn-container">
                                <div class="moncairn-title">Recently searched</div>
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
                                <div class="moncairn-footer"><a href="my_searches.php">My Searches</a></div>
                            </div>

                            <!-- Mes consultations -->
                            <div id="moncairn-mes-consultations" class="moncairn-container">
                                <div class="moncairn-title">Recently viewed</div>
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
                                <div class="moncairn-footer"><a href="my_history.php">My history</a></div>
                            </div>

                            <!-- Mes outils -->
                            <div id="moncairn-mes-outils" class="moncairn-container">
                                <div class="moncairn-title">My tools</div>
                                <div class="moncairn-content">
                                    <ul>
                                        <li><a href="./biblio.php">My&nbsp;selection</a></li>
                                        <li><a href="./my_cart.php">My cart</a></li>
                                        <li><a href="./my_purchases.php">My purchases</a></li>
                                        <li><a href="./my_alerts.php">My email alerts</a></li>
                                        <li><a href="./my_account.php">My account</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End - Mon cairn-info -->
            <?php } ?>