<!--
    Il faut fournir à cette vue:
        - $typePub = libellé de l'onglet courant, en minuscule et au singulier
-->
<?php
if ($tabsMode == 'typepub') {

    if (!isset($typePub))
        $typePub = null;

    $arrayTabs = [
        'tab_1' => '<li><a href="./Accueil_Revues.php" class="' . ($typePub == "revue" ? "black_button" : "blue_button") . '" >Revues</a></li>',
        'tab_1_disabled' => '<li class="desactivate"><span class="grey_button">Revues</span></li>',
        'tab_3' => '<li><a href="./ouvrages.php" class="' . ($typePub == "ouvrage" ? "black_button" : "blue_button") . '">Ouvrages</a></li>',
        'tab_3_disabled' => '<li class="desactivate"><span class="grey_button">Ouvrages</span></li>',
        'tab_6' => '<li><a href="./que-sais-je-et-reperes.php" class="' . ($typePub == "encyclopedie" ? "black_button" : "blue_button") . '" >Encyclopédies de poche</a></li>',
        'tab_6_disabled' => '<li class="desactivate"><span class="grey_button">Encyclopédies de poche</span></li>',
        'tab_2' => '<li><a href="./magazines.php" class="' . ($typePub == "magazine" ? "black_button" : "blue_button") . '" >Magazines</a></li>',
        'tab_2_disabled' => '<li class="desactivate"><span class="grey_button">Magazines</span></li>'
    ];
    $order = '1362';
    $disabled = array();

    if (isset($authInfos['I']) && $authInfos['I']['PARAM_INST'] !== false) {
        if (isset($authInfos['I']['PARAM_INST']['O'])) {
            $order = $authInfos['I']['PARAM_INST']['O'];
        }
        if (isset($authInfos['I']['PARAM_INST']['P'])) {
            $disabled = explode(',', $authInfos['I']['PARAM_INST']['P']);
        }
    }
    ?>

    <div id="wrapper_category_tabs">
        <div class="mainTabs" id="main_tabs">
            <ul id="category_tabs">
                <?php
                $arrOrder = str_split($order);
                foreach ($arrOrder as $tabNum) {
                    $suffix = '';
                    if (in_array($tabNum, $disabled)) {
                        $suffix = '_disabled';
                    }
                    if (isset($arrayTabs['tab_' . $tabNum . $suffix]))
                        echo $arrayTabs['tab_' . $tabNum . $suffix];
                }
                ?>
                <!--li>
                    <a href="./Accueil_Revues.php" class='<?php echo $typePub == "revue" ? "black_button" : "blue_button"; ?>' >Revues</a>
                </li>
                <li>
                    <a href="./ouvrages.php" class='<?php echo $typePub == "ouvrage" ? "black_button" : "blue_button"; ?>'>Ouvrages</a>
                </li>
                <li>
                    <a href="./que-sais-je-et-reperes.php" class='<?php echo $typePub == "encyclopedie" ? "black_button" : "blue_button"; ?>' >Encyclopédies de poche</a>
                </li>
                <li>
                    <a href="./magazines.php" class='<?php echo $typePub == "magazine" ? "black_button" : "blue_button"; ?>' >Magazines</a>
                </li-->
            </ul>
        </div>
    </div>

    <?php
}else if ($tabsMode == 'discpos') {

    if (!isset($curDiscipline))
        $curDiscipline = null;
    ?>
    <div id="wrapper_category_tabs" class="wrapper">
    <section class="research Clearfix">
        <nav>
            <ul>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='communication-and-information'?' dark':''?>"
                       href="./disc-communication-and-information.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Communication & Information</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='economics'?' dark':''?>"
                       href="./disc-economics.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Economics &amp; Management</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='education'?' dark':''?>"
                       href="./disc-education.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Education</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='general-interest'?' dark':''?>"
                       href="./disc-general-interest.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">General Interest</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='geography'?' dark':''?>"
                       href="./disc-geography.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Geography</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='history'?' dark':''?>"
                       href="./disc-history.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">History</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='literature'?' dark':''?>"
                       href="./disc-literature.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Literature &amp; Linguistics</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='philosophy'?' dark':''?>"
                       href="./disc-philosophy.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Philosophy</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='political-science-and-law'?' dark':''?>"
                       href="./disc-political-science-and-law.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Political Science &amp; Law</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='psychology'?' dark':''?>"
                       href="./disc-psychology.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Psychology</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='sociology'?' dark':''?>"
                       href="./disc-sociology.htm"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">Sociology &nbsp;&amp;&nbsp; Culture&nbsp;</span></a>
                </li>
                <li>
                    <a style="line-height: 2.5em" class="btn<?=$curDiscipline=='ALL'?' dark':''?>"
                       href="./listrev.php"><span style="display:inline-block;vertical-align:middle;line-height: 1.2em;">All</span></a>
                </li>
            </ul>
        </nav>
    </section>
    </div>
    <?php
}
?>
