<?php $this->titre = "Services aux institutions"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div class="sub-menu">
    <ul id="sub-category_tabs">
        <li><a href="./a-propos.php" class="white_button">À propos de Cairn.info</a></li>
        <li><a href="./services-aux-editeurs.php" class="white_button">Services aux éditeurs</a></li>
        <li><a href="./services-aux-institutions.php" class="black_button">Services aux institutions</a></li>
        <li><a href="./services-aux-particuliers.php" class="white_button">Services aux particuliers</a></li>
    </ul>
</div>



<div id="body-content">
    <div id="free_text">

        <h1 class="main-title">
            Services aux institutions</h1>
        <br/>



        <div class="articleBody">

            <h2>Présentation</h2>

            <p>Vis-à-vis des établissements de prêt et des institutions, notamment des établissements d’enseignement et de recherche, la commercialisation des services proposés par Cairn.info prend essentiellement la forme de licences forfaitaires – établies sur une base annuelle – donnant accès à l’un ou l’autre des bouquets de publications que nous proposons :</p>

            <ul class="links_list">
                <li>
                    <a href="./static/docs/Cairn_Revues_2016.xls">
                        <span class="icon icon-arrow-blue-right"></span>
                        Bouquets de revues (fichier Excel)
                    </a>
                </li>
                <li>
                    <a href="./static/docs/Cairn_Magazines_2016.xls">
                        <span class="icon icon-arrow-blue-right"></span>
                        Bouquets magazines (fichier Excel)
                    </a>
                </li>
                <li>
                    <a href="./static/docs/Cairn_Poches_2016.xls">
                        <span class="icon icon-arrow-blue-right"></span>
                        Bouquets d’encyclopédies de poche (fichier Excel)
                    </a>
                </li>
                <li>
                    <a href="./static/docs/Cairn_Ouvrages_2016.xls">
                        <span class="icon icon-arrow-blue-right"></span>
                        Bouquets d’ouvrages de recherche (fichier Excel)
                    </a>
                </li>
            </ul>

            <p>Dans le cadre de cette offre de bouquets, les institutions clientes, identifiées par leurs adresses IP, acquièrent un droit d’accès illimité aux publications, quel que soit, par exemple, le nombre d’utilisateurs simultanés. Leurs utilisateurs ont donc la possibilité de consulter ou d’imprimer les articles et chapitres, et éventuellement de les reproduire, pour autant évidemment que cela s’effectue dans les limites de la réglementation en vigueur, concernant le droit d’auteur notamment.</p>

            <p>A noter qu’une formule alternative peut également être proposée, notamment aux associations et centres de recherche spécialisés pour qui l’acquisition d’une licence illimitée ne paraîtrait pas adéquate : un « crédit d’achat », librement défini par l’institution intéressée et débité au fur et à mesure des consultations d’articles ou des chapitres en accès conditionnel. Cette offre, non limitée à un type de ressource ou à une thématique en particulier, donne simplement accès aux contenus que nous proposons dans la limite de la somme préchargée.</p>

            <p>Autres liens :</p>

            <ul class="links_list">
                <li>
                    <a href="http://oai.cairn.info">
                        <span class="icon icon-arrow-blue-right"></span>
                        Archive OAI
                    </a>
                </li>
                <li>
                    <a href="#" onclick="cairn.show_menu(this, '#kbart-list-licences')">
                        <span class="icon icon-arrow-blue-right"></span>
                        Fichiers KBART pour les revues
                    </a>
                    <ul style="margin-left: 4em; display: none;" id="kbart-list-licences" class="menu">
                        <li>
                            <a href="./index.php?controleur=Kbart">
                                Toutes les revues
                            </a>
                            <?php if (Configuration::get('allow_backoffice', false)): ?>
                                <a href="./index.php?controleur=Kbart&amp;format=html" target="_blank" class="bo-content">
                                    (html)
                                </a>
                            <?php endif; ?>
                        </li>
                        <?php if (Configuration::get('allow_backoffice', false)): ?>
                        <?php // Le bouquet en accès libre ne doit apparaitre que sur les serveurs de tests ?>
                        <li>
                            <a href="./index.php?controleur=Kbart&amp;free=1&amp;pay=0" class="bo-content">
                                Bouquet en accès libre
                            </a>
                            <a href="./index.php?controleur=Kbart&amp;free=1&amp;pay=0&amp;format=html" target="_blank" class="bo-content">
                                (html)
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php foreach ($licences as $licence): ?>
                            <li>
                                <a href="./index.php?controleur=Kbart&amp;free=0&amp;pay=1&amp;licence=<?= $licence['id'] ?>">
                                    Bouquet "<?= $licence['name'] ?>"
                                </a>
                                <?php if (Configuration::get('allow_backoffice', false)): ?>
                                    <a href="./index.php?controleur=Kbart&amp;free=0&amp;pay=1&amp;licence=<?= $licence['id'] ?>&amp;format=html" target="_blank" class="bo-content">
                                        (html)
                                    </a>
                                    <!-- <a href="./index.php?controleur=Kbart&amp;free=0&amp;pay=1&amp;licence=<?= $licence['id'] ?>&amp;format=csv" target="_blank" class="bo-content">
                                        (excel)
                                    </a> -->
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li>
                    <a href="#" onclick="cairn.show_menu(this, '#kbart-list-licences-ouvrages')">
                        <span class="icon icon-arrow-blue-right"></span>
                        Fichiers KBART pour les ouvrages
                    </a>
                    <ul style="margin-left: 4em; display: none;" id="kbart-list-licences-ouvrages" class="menu">
                        <li>
                            <a href="./index.php?controleur=Kbart&amp;typepub=3">
                                Bouquet "Ouvrages - G&eacute;n&eacute;ral"
                            </a>
                            <?php if (Configuration::get('allow_backoffice', false)): ?>
                                <a href="./index.php?controleur=Kbart&amp;format=html&amp;typepub=3" target="_blank" class="bo-content">
                                    (html)
                                </a>
                            <?php endif; ?>
                        </li>
                        <?php if (Configuration::get('allow_backoffice', false)): ?>
                        <?php // Le bouquet en accès libre ne doit apparaitre que sur les serveurs de tests ?>
                        <li>
                            <a href="./index.php?controleur=Kbart&amp;free=1&amp;pay=0&amp;typepub=3" class="bo-content">
                                Bouquet en accès libre
                            </a>
                            <a href="./index.php?controleur=Kbart&amp;free=1&amp;pay=0&amp;format=html&amp;typepub=3" target="_blank" class="bo-content">
                                (html)
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php foreach ($licencesOuvrages as $licence): ?>
                            <li>
                                <a href="./index.php?controleur=Kbart&amp;free=0&amp;pay=1&amp;licence=<?= $licence['id'] ?>&amp;typepub=3">
                                    Bouquet "<?= $licence['name'] ?>"
                                </a>
                                <?php if (Configuration::get('allow_backoffice', false)): ?>
                                    <a href="./index.php?controleur=Kbart&amp;free=0&amp;pay=1&amp;licence=<?= $licence['id'] ?>&amp;format=html&amp;typepub=3" target="_blank" class="bo-content">
                                        (html)
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li>
                    <a href="#" onclick="cairn.show_menu(this, '#kbart-list-licences-poches')">
                        <span class="icon icon-arrow-blue-right"></span>
                        Fichiers KBART pour les encyclopédies de poches
                    </a>
                    <ul style="margin-left: 4em; display: none;" id="kbart-list-licences-poches" class="menu">
                        <li>
                            <a href="./index.php?controleur=Kbart&amp;typepub=6">
                                Bouquet "Poches - G&eacute;n&eacute;ral"
                            </a>
                            <?php if (Configuration::get('allow_backoffice', false)): ?>
                                <a href="./index.php?controleur=Kbart&amp;format=html&amp;typepub=6" target="_blank" class="bo-content">
                                    (html)
                                </a>
                            <?php endif; ?>
                        </li>
                        <?php if (Configuration::get('allow_backoffice', false)): ?>
                        <?php // Le bouquet en accès libre ne doit apparaitre que sur les serveurs de tests ?>
                        <li>
                            <a href="./index.php?controleur=Kbart&amp;free=1&amp;pay=0&amp;typepub=6" class="bo-content">
                                Bouquet en accès libre
                            </a>
                            <a href="./index.php?controleur=Kbart&amp;free=1&amp;pay=0&amp;format=html&amp;typepub=6" target="_blank" class="bo-content">
                                (html)
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php foreach ($licencesPoches as $licence): ?>
                            <li>
                                <a href="./index.php?controleur=Kbart&amp;free=0&amp;pay=1&amp;licence=<?= $licence['id'] ?>&amp;typepub=6">
                                    Bouquet "<?= $licence['name'] ?>"
                                </a>
                                <?php if (Configuration::get('allow_backoffice', false)): ?>
                                    <a href="./index.php?controleur=Kbart&amp;free=0&amp;pay=1&amp;licence=<?= $licence['id'] ?>&amp;format=html&amp;typepub=6" target="_blank" class="bo-content">
                                        (html)
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li>
                    <a href="<?= Configuration::get('csv_info_revues', '#') ?>">
                        <span class="icon icon-arrow-blue-right"></span>
                        Informations complémentaires sur les revues (fichier CSV pour Excel)
                    </a>
                </li>
                <li>
                    <a href="<?= Configuration::get('csv_info_magazines', '#') ?>">
                        <span class="icon icon-arrow-blue-right"></span>
                        Informations complémentaires sur les magazines (fichier CSV pour Excel)
                    </a>
                </li>
                <li>
                    <a href="<?= Configuration::get('csv_info_ouvrages', '#') ?>">
                        <span class="icon icon-arrow-blue-right"></span>
                        Informations complémentaires sur les ouvrages de recherche (fichier CSV pour Excel)
                    </a>
                </li>
                <li>
                    <a href="<?= Configuration::get('csv_info_encyclopedies', '#') ?>">
                        <span class="icon icon-arrow-blue-right"></span>
                        Informations complémentaires sur les encyclopédies de poche (fichier CSV pour Excel)
                    </a>
                </li>
            </ul>
<!--             <div>
                <span class="icon icon-arrow-blue-right"></span>
                Fichiers KBART pour les revues
            </div> -->
            <br>
            <p>N’hésitez pas à <a href="./contact.php">nous contacter</a> pour tout complément d’information sur ces différentes offres.</p>


            <h2>Bouquets de revues</h2>

            <p>Concernant l’offre de revues de Cairn.info, les institutions ont la possibilité d’acquérir une licence d’accès au bouquet « général » rassemblant l’ensemble des titres proposés sur notre portail, soit près de 400 revues actives, ou à l’un des bouquets thématiques décrits ci-dessous&nbsp;:</p>

            <ul class="bullets_list">
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    le bouquet « sciences économiques sociales et politiques », rassemblant les revues proposées sur Cairn.info en sciences économiques, en sciences politiques (notamment en relations internationales), liées aux problématiques du développement ainsi que les revues en sociologie et certaines revues d’histoire contemporaine&#8239;;
                </li>
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    le bouquet « humanités », rassemblant les revues proposées sur Cairn.info en histoire ancienne, lettre, linguistique et philosophie&#8239;;
                </li>
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    le bouquet « travail social » rassemblant une sélection de revues en psychologie, intervention sociale et économie de la santé proposées sur Cairn.info&#8239;;
                </li>
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    le bouquet « psychologie », rassemblant les revues dans les différents domaines de la psychologie et de la santé mentale (psychanalyse, psychologie clinique, psychiatrie) proposées sur Cairn.info&#8239;;
                </li>
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    le bouquet « économie, gestion », rassemblant les revues d’économie et de gestion proposées sur Cairn.info&#8239;;
                </li>
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    le bouquet « sciences de l’éducation », rassemblant une sélection de revues abordant la thématique de l’éducation proposées sur Cairn.info&#8239;;
                </li>
            </ul>

            <p>Les prix de ces différents bouquets tiennent compte bien évidemment du nombre de revues les composant, mais aussi d’autres facteurs liés à la situation de l’établissement utilisateur&nbsp;: type, taille et pays de l’établissement. Une réduction peut également être accordée aux établissements s’engageant à conserver, durant la durée de la licence, un certain nombre d’abonnements «&nbsp;papier » aux différentes revues faisant partie du bouquet retenu.</p>

            <h2>Autres bouquets</h2>

            <p>Cairn.info s'ouvre progressivement à d’autres publications de sciences humaines et sociales que des revues. A ce jour, trois autres types de publications sont proposées en texte intégral sur la plateforme Cairn.info:</p>

            <ul class="bullets_list">
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    des magazines de haut niveau, susceptibles d’intéresser particulièrement les étudiants en début de cursus universitaire&#8239;;
                </li>
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    des ouvrages de recherche récents issus du catalogue d’une dizaine d’éditeurs de référence pour la langue française et les sciences humaines et sociales&#8239;;
                </li>
                <li>
                    <span class="icon icon-diamond-bullet"></span>
                    des encyclopédies de poche : la collection « Que sais-je ? » des Presses universitaires de France ainsi que la collection « Repères » des Éditions La Découverte&#8239;;
                </li>
            </ul>

            <p>
                La commercialisation de ces nouveaux types de publications s’inspire très largement du modèle retenu pour les revues universitaires, les ouvrages de recherche, les magazines et les encyclopédies de poche présentés sur le portail Cairn.info étant proposés aux institutions dans le cadre de « bouquets » (bouquets thématiques ou bouquets interdisciplinaires) auxquels les institutions pourront s’abonner sous forme de licences d’accès forfaitaire (le nombre de consultations étant alors illimité).
            </p>

            <p>Les ouvrages de recherche proposés sur Cairn.info sont par ailleurs vendus &agrave; l’unité, &agrave; des conditions tarifaires exposées au sein d’une «&nbsp;Boutique&nbsp;» en ligne, accessible aux institutions clientes de Cairn après authentification &agrave; l’adresse suivante : <a href="http://laboutique.cairn.info" class="link_custom" target="blank">http://laboutique.cairn.info.</a></p>
            <p>L’ensemble des informations sur nos conditions de commercialisation sont par ailleurs disponibles auprès de nos services, n’hésitez pas &agrave; nous contacter directement &agrave; cet effet&nbsp;:</p>


            <p><a href="./contact.php" class="link_custom">Nous contacter</a></p>

        </div>

    </div>
</div>
