<?php $this->titre = "Flux RSS"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<?php define("URL_RSS", "http://v3.cairn.info/RSS/flux/") ?>

<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
    <a href="">Flux RSS</a>
</div>

<div id="body-content">
    <div id="free_text abo-flux">
        <h1 class="main-title">S'abonner aux flux RSS</h1>

        <a href="http://aide.cairn.info/les-flux-rss/" target="_blank">
            <span class="question-mark">
                <span class="tooltip">En savoir plus sur les flux RSS</span>
            </span>
        </a>

        <div class="boxHome abo_flux">
            <h2 class="section">
                <span>Revues</span>
            </h2>
            <div class="specs">
                <div class="titlepanier">
                    <select name='idrevue_flux' id='idrevue_flux' onChange="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/' + this.value, 'WT.ti', this.value, 'WT.rss_ev', 's', 'WT.rss_f', this.value);changeselect();">
                        <option class="ital" selected value="">Choisir la revue...</option>
                        <?php foreach ($revues as $revue): ?>
                        <option value="<?php echo $rss_path .'/rss_revue-'.$revue['ID_REVUE'].'.xml'; ?>"><?php echo $revue['TITRE'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <br />
                <br />
            </div>

            <h2 class="w50 left">
                <span>Revues par disciplines</span>
            </h2>

            <h2>
                <span>Ouvrages de recherche</span>
            </h2>

            <div class="overflow">
                <ul class="links_list w50 left">
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_revues.xml', 'WT.ti', 'Revues toutes disciplines', 'WT.rss_ev', 's', 'WT.rss_f', 'Revues toutes disciplines');" href="<?= $rss_path ?>/rss_revues.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Revues toutes disciplines
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_arts.xml','WT.ti','Arts','WT.rss_ev','s','WT.rss_f', 'Arts');" href="<?= $rss_path ?>/rss_arts.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Arts
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_droit.xml','WT.ti','Droit','WT.rss_ev','s','WT.rss_f', 'Droit');" href="<?= $rss_path ?>/rss_droit.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Droit
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_economie-gestion.xml','WT.ti','Economie, Gestion','WT.rss_ev','s','WT.rss_f', 'Economie, Gestion');" href="<?= $rss_path ?>/rss_economie-gestion.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Economie, Gestion
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_geographie.xml','WT.ti','Géographie','WT.rss_ev','s','WT.rss_f', 'Géographie');" href="<?= $rss_path ?>/rss_geographie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Géographie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_histoire.xml','WT.ti','Histoire','WT.rss_ev','s','WT.rss_f', 'Histoire');" href="<?= $rss_path ?>/rss_histoire.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Histoire
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_sciences-de-l-information.xml','WT.ti','Info. - Com.','WT.rss_ev','s','WT.rss_f', 'Info. - Com.');" href="<?= $rss_path ?>/rss_sciences-de-l-information.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Info. - Com.
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_interet-general.xml','WT.ti','Intérêt général','WT.rss_ev','s','WT.rss_f', 'Intérêt général');" href="<?= $rss_path ?>/rss_interet-general.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Intérêt général
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_lettres-linguistique.xml','WT.ti','Lettres et linguistique','WT.rss_ev','s','WT.rss_f', 'Lettres et linguistique');" href="<?= $rss_path ?>/rss_lettres-linguistique.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Lettres et linguistique
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_philosophie.xml','WT.ti','Philosophie','WT.rss_ev','s','WT.rss_f', 'Philosophie');" href="<?= $rss_path ?>/rss_philosophie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Philosophie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_psychologie.xml', 'WT.ti', 'Psychologie', 'WT.rss_ev', 's', 'WT.rss_f', 'Psychologie');" href="<?= $rss_path ?>/rss_psychologie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Psychologie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_sciences-de-l-education.xml', 'WT.ti', 'Sciences&nbsp;de&nbsp;l’éducation', 'WT.rss_ev', 's', 'WT.rss_f', 'Sciences&nbsp;de&nbsp;l’éducation');" href="<?= $rss_path ?>/rss_sciences-de-l-education.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sciences&nbsp;de&nbsp;l’éducation
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_sciences-politiques.xml', 'WT.ti', 'Sciences&nbsp;politiques', 'WT.rss_ev', 's', 'WT.rss_f', 'Sciences&nbsp;politiques');" href="<?= $rss_path ?>/rss_sciences-politiques.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sciences&nbsp;politiques
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_sociologie-et-societe.xml', 'WT.ti', 'Sociologie et société', 'WT.rss_ev', 's', 'WT.rss_f', 'Sociologie et société');" href="<?= $rss_path ?>/rss_sociologie-et-societe.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sociologie et société
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_sport-et-societe.xml', 'WT.ti', 'Sport&nbsp;et&nbsp;société', 'WT.rss_ev', 's', 'WT.rss_f', 'Sport&nbsp;et&nbsp;société');" href="<?= $rss_path ?>/rss_sport-et-societe.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sport&nbsp;et&nbsp;société
                        </a>
                    </li>
                </ul>

                <ul class="links_list w50 right">
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages.xml', 'WT.ti', 'Ouvrages de recherche toutes disciplines', 'WT.rss_ev', 's', 'WT.rss_f', 'Ouvrages de recherche toutes disciplines');" href="<?= $rss_path ?>/rss_ouvrages.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Ouvrages de recherche toutes disciplines
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-droit.xml', 'WT.ti', 'Droit', 'WT.rss_ev', 's', 'WT.rss_f', 'Droit');" href="<?= $rss_path ?>/rss_ouvrages-droit.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Droit
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-economie-gestion.xml', 'WT.ti', 'Economie, gestion', 'WT.rss_ev', 's', 'WT.rss_f', 'Economie, gestion');" href="<?= $rss_path ?>/rss_ouvrages-economie-gestion.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Economie, gestion
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-geographie.xml', 'WT.ti', 'Géographie', 'WT.rss_ev', 's', 'WT.rss_f', 'Géographie');" href="<?= $rss_path ?>/rss_ouvrages-geographie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Géographie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-histoire.xml', 'WT.ti', 'Histoire', 'WT.rss_ev', 's', 'WT.rss_f', 'Histoire');" href="<?= $rss_path ?>/rss_ouvrages-histoire.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Histoire
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-lettres-linguistique.xml', 'WT.ti', 'Lettres, linguistique', 'WT.rss_ev', 's', 'WT.rss_f', 'Lettres, linguistique');" href="<?= $rss_path ?>/rss_ouvrages-lettres-linguistique.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Lettres, linguistique
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-philosophie.xml', 'WT.ti', 'Philosophie', 'WT.rss_ev', 's', 'WT.rss_f', 'Philosophie');" href="<?= $rss_path ?>/rss_ouvrages-philosophie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Philosophie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-psychologie.xml', 'WT.ti', 'Psychologie', 'WT.rss_ev', 's', 'WT.rss_f', 'Psychologie');" href="<?= $rss_path ?>/rss_ouvrages-psychologie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Psychologie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-sciences-de-l-information.xml', 'WT.ti', 'Sc. de l\'information', 'WT.rss_ev', 's', 'WT.rss_f', 'Sc. de l\'information');" href="<?= $rss_path ?>/rss_ouvrages-sciences-de-l-information.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sc. de l'information
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-sciences-de-l-education.xml', 'WT.ti', 'Sciences de l\'éducation', 'WT.rss_ev', 's', 'WT.rss_f', 'Sciences de l\'éducation');" href="<?= $rss_path ?>/rss_ouvrages-sciences-de-l-education.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sciences de l'éducation
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-sciences-politiques.xml', 'WT.ti', 'Sciences politiques', 'WT.rss_ev', 's', 'WT.rss_f', 'Sciences politiques');" href="<?= $rss_path ?>/rss_ouvrages-sciences-politiques.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sciences politiques
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-sociologie-et-societe.xml', 'WT.ti', 'Sociologie et société', 'WT.rss_ev', 's', 'WT.rss_f', 'Sociologie et société');" href="<?= $rss_path ?>/rss_ouvrages-sociologie-et-societe.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sociologie et société
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri', 'http://www.cairn.info/<?= $rss_path ?>/rss_ouvrages-sport-et-societe.xml', 'WT.ti', 'Sport et société', 'WT.rss_ev', 's', 'WT.rss_f', 'Sport et société');" href="<?= $rss_path ?>/rss_ouvrages-sport-et-societe.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sport et société
                        </a>
                    </li>
                </ul>
            </div> <!-- fin overflow -->

            <br />

            <h2 class="w50 left">
                <span>Que sais-je ? / Repères</span>
            </h2>

            <h2>
                <span>Magazines</span>
            </h2>
            
            <div class="overflow">
                <ul class="links_list w50 right">
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_magazines.xml','WT.ti','Tous les magazines','WT.rss_ev','s','WT.rss_f', 'Tous les magazines');" href="<?= $rss_path ?>/rss_magazines.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Tous les magazines
                        </a>
                    </li>
                    
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-alternatives-economiques.xml','WT.ti','Alternatives économiques','WT.rss_ev','s','WT.rss_f', 'Alternatives économiques');" href="<?= $rss_path ?>/rss_revue-alternatives-economiques.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Alternatives économiques
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-alternatives-internationales.xml','WT.ti','Alternatives Internationales','WT.rss_ev','s','WT.rss_f', 'Alternatives Internationales');" href="<?= $rss_path ?>/rss_revue-alternatives-internationales.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Alternatives Internationales
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-sciences-humaines.xml','WT.ti','Sciences humaines','WT.rss_ev','s','WT.rss_f', 'Sciences humaines');" href="<?= $rss_path ?>/rss_revue-sciences-humaines.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sciences humaines
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-les-grands-dossiers-des-sciences-humaines.xml','WT.ti','Les Grands Dossiers des Sciences Humaines','WT.rss_ev','s','WT.rss_f', 'Les Grands Dossiers des Sciences Humaines');" href="<?= $rss_path ?>/rss_revue-les-grands-dossiers-des-sciences-humaines.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Les Grands Dossiers des Sciences Humaines
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-le-monde-diplomatique.xml','WT.ti','Le Monde diplomatique','WT.rss_ev','s','WT.rss_f', 'Le Monde diplomatique');" href="<?= $rss_path ?>/rss_revue-le-monde-diplomatique.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Le Monde diplomatique
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-maniere-de-voir.xml','WT.ti','Manière de voir','WT.rss_ev','s','WT.rss_f', 'Manière de voir');" href="<?= $rss_path ?>/rss_revue-maniere-de-voir.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Manière de voir
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-le-magazine-litteraire.xml','WT.ti','Le Magazine Littéraire','WT.rss_ev','s','WT.rss_f', 'Le Magazine Littéraire');" href="<?= $rss_path ?>/rss_revue-le-magazine-litteraire.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Le Magazine Littéraire
                        </a>
                    </li>
                    <li><a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_revue-l-histoire.xml','WT.ti','L'Histoire','WT.rss_ev','s','WT.rss_f', 'L'Histoire');" href="<?= $rss_path ?>/rss_revue-l-histoire.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            L'Histoire
                        </a>
                    </li>
                </ul>

                <ul class="links_list w50 left">
                    <li>
                        <a href="<?= $rss_path ?>/rss_qsj.xml" onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_qsj.xml','WT.ti','Collection Que sais-je ? toutes disciplines','WT.rss_ev','s','WT.rss_f', 'Collection Que sais-je ? toutes disciplines');">
                            <span class="icon icon-arrow-blue-right"></span>
                            Collection "Que sais-je ?" toutes disciplines
                        </a>
                    </li>
                    <li>
                        <a href="<?= $rss_path ?>/rss_rep.xml" onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_rep.xml','WT.ti','Collection Repères toutes disciplines','WT.rss_ev','s','WT.rss_f', 'Collection Repères toutes disciplines');">
                            <span class="icon icon-arrow-blue-right"></span>
                            Collection "Repères" toutes disciplines
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_arts.xml','WT.ti','Arts','WT.rss_ev','s','WT.rss_f', 'Arts');" href="<?= $rss_path ?>/rss_encyclo-arts.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Arts
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_droit.xml','WT.ti','Droit','WT.rss_ev','s','WT.rss_f', 'Droit');" href="<?= $rss_path ?>/rss_encyclo-droit.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Droit
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_economie-gestion.xml','WT.ti','Economie, Gestion','WT.rss_ev','s','WT.rss_f', 'Economie, Gestion');" href="<?= $rss_path ?>/rss_encyclo-economie-gestion.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Economie, Gestion
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_geographie.xml','WT.ti','Géographie','WT.rss_ev','s','WT.rss_f', 'Géographie');" href="<?= $rss_path ?>/rss_encyclo-geographie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Géographie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_histoire.xml','WT.ti','Histoire','WT.rss_ev','s','WT.rss_f', 'Histoire');" href="<?= $rss_path ?>/rss_encyclo-histoire.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Histoire
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_sciences-de-l-information.xml','WT.ti','Info. - Com.','WT.rss_ev','s','WT.rss_f', 'Info. - Com.');" href="<?= $rss_path ?>/rss_encyclo-sciences-de-l-information.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Info. - Com.
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_interet-general.xml','WT.ti','Intérêt général','WT.rss_ev','s','WT.rss_f', 'Intérêt général');" href="<?= $rss_path ?>/rss_encyclo-interet-general.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Intérêt général
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_lettres-linguistique.xml','WT.ti','Lettres et linguistique','WT.rss_ev','s','WT.rss_f', 'Lettres et linguistique');" href="<?= $rss_path ?>/rss_encyclo-lettres-linguistique.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Lettres et linguistique
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_philosophie.xml','WT.ti','Philosophie','WT.rss_ev','s','WT.rss_f', 'Philosophie');" href="<?= $rss_path ?>/rss_encyclo-philosophie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Philosophie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_psychologie.xml','WT.ti','Psychologie','WT.rss_ev','s','WT.rss_f', 'Psychologie');" href="<?= $rss_path ?>/rss_encyclo-psychologie.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Psychologie
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_sciences-de-l-education.xml','WT.ti','Sciences&nbsp;de&nbsp;l’éducation','WT.rss_ev','s','WT.rss_f', 'Sciences&nbsp;de&nbsp;l’éducation');" href="<?= $rss_path ?>/rss_encyclo-sciences-de-l-education.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sciences&nbsp;de&nbsp;l’éducation
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_sciences-politiques.xml','WT.ti','Sciences&nbsp;politiques','WT.rss_ev','s','WT.rss_f', 'Sciences&nbsp;politiques');" href="<?= $rss_path ?>/rss_encyclo-sciences-politiques.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sciences&nbsp;politiques
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_sociologie-et-societe.xml','WT.ti','Sociologie et société','WT.rss_ev','s','WT.rss_f', 'Sociologie et société');" href="<?= $rss_path ?>/rss_encyclo-sociologie-et-societe.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sociologie et société
                        </a>
                    </li>
                    <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_sport-et-societe.xml','WT.ti','Sport&nbsp;et&nbsp;société','WT.rss_ev','s','WT.rss_f', 'Sport&nbsp;et&nbsp;société');" href="<?= $rss_path ?>/rss_encyclo-sport-et-societe.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Sport&nbsp;et&nbsp;société
                        </a>
                    </li>
                </ul>
            </div> <!-- fin overflow -->
            
            <br>
            
            <h2 class="w50 left">
                <span>Toutes les ressources</span>
            </h2>
            <h2>&#8239;
            </h2>
            
            <div class="overflow">
            
                <ul class="links_list w50 left">
                <li>
                        <a onclick="dcsMultiTrack('DCS.dcsuri','http://www.cairn.info/<?= $rss_path ?>/rss_all.xml','WT.ti','Dernières mises en ligne sur Cairn.info','WT.rss_ev','s','WT.rss_f', 'Dernières mises en ligne sur Cairn.info');" href="<?= $rss_path ?>/rss_all.xml">
                            <span class="icon icon-arrow-blue-right"></span>
                            Dernières mises en ligne sur Cairn.info
                        </a>
                    </li>
                </ul>
            </div><!-- fin overflow -->
        </div> <!-- fin boxhome -->
    </div>
</div>



<?php

$this->javascripts[] = <<<'EOD'
    function changeselect() {
        document.location.href = document.getElementById('idrevue_flux').value;
    }
EOD;

?>
