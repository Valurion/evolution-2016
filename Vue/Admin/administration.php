<?php
header('Content-Type: text/html; charset=utf-8');
$this->titre = "Administration";

if(empty($authInfos['U']['ADMIN'])) {
    header('Location: ./');
}
?>

<div id="header">
    <ul id="nav1">        
        <li>
            <a href="./">Retour au site www2.semantic.lu</a>
        </li>
    </ul>
    
    <style>
        img#middleLogo
        {
            display:block;
            margin:0 auto;
        }
    </style>
    <img id="middleLogo" src="./static/images/logo-cairn.png" alt="CAIRN.INFO : Chercher, repérer, avancer.">
    
</div>

<div id="contenu">
    <div id="wrapper_category_tabs">
        <div class="mainTabs" id="main_tabs">
            <ul id="category_tabs">
                <!--li>
                    <a href="./statistiques_consultation.php" class="blue_button">Statistiques de consultation du site</a>
                </li-->
                <li>
                    <a href="./gestion_utilisateurs.php" class="blue_button">Gestion des utilisateurs</a>
                </li>
                <li>
                    <a target="_blank" href="./evidensseConfigurator/" class="blue_button">Configuration du moteur de recherche</a>
                </li>
            </ul>
        </div>
    </div>
</div>