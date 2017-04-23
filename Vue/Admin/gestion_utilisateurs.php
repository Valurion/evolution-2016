<?php
header('Content-Type: text/html; charset=utf-8');
$this->titre = "Gestion des utilisateurs";

if(empty($authInfos['U']['ADMIN'])) {
    header('Location: ./');
}
?>

<style>
    #gestion_container
    {
        display:block;
        margin:0 auto;
        width:960px;
        height:auto;
    }

    #gestion_container h1
    {
        display:block;
        padding: 20px 0;
        text-align: center;
    }

    img#middleLogo
    {
        display:block;
        margin:0 auto;
    }

    b.blue
    {
        color:#50A6C2;
    }

    p
    {
        margin:0;
        padding:0;
    }

    table tr td
    {
        padding:10px 0;
        vertical-align: top;
    }

    table tr td a.icons
    {
        display:block;
        width:20px;
        height:20px;
        float:right;
        margin:0 5px;
    }

    table tr td a.icons img
    {
        width:100%;
        height:100%;
    }
</style>


<div style="display: none;" class="window_modal" id="add_address_modal">
    <div class="basic_modal">
        <h1>Ajouter nouveau address</h1>
        <br>
        <input id="new_user_selected" type="hidden" value=<?php echo $_GET['id_user']; ?>>
        <input id="new_id_selected" type="hidden" value=<?php echo $idAbonnes['ID']; ?>>
        <table>
            <tr>
                <td>Network address</td>
                <td>Subnet Mask</td>
            </tr>
            <tr>
                <td><input id="new-addess" type="text"></td>
                <td><input id="new-mask" type="text"></td>
            </tr>
        </table>
        <button onclick="javascript:ajax.addNetworkAddress()" class="button-blue">Ok</button>
        <button onclick="javascript:cairn.close_modal();" class="button-blue">Fermer</button>
    </div>
</div>



<div style="display: none;" class="window_modal" id="edit_address_modal">
    <div class="basic_modal">
        <h1>Editer les address</h1>
        <br>
        <input id="user_selected" type="hidden">
        <input id="ip_selected" type="hidden">
        <table>
            <tr>
                <td>Network address</td>
                <td>Subnet Mask</td>
            </tr>
            <tr>
                <td><input id="edit-addess" type="text"></td>
                <td><input id="edit-mask" type="text"></td>
            </tr>
        </table>

        <button onclick="javascript:ajax.editNetworkAddress()" class="button-blue ok">Ok</button>
        <button onclick="javascript:cairn.close_modal();" class="button-blue ok">Fermer</button>
    </div>
</div>


<div style="display: none;" class="window_modal" id="add_params_modal">
    <div class="basic_modal">
        <h2>Nouveau paramètre institution</h2>
        <br>
        <p>Paramètre :
            <select id="params_select">
                <option value="">Veuillez sélectionner un paramètre</option>
                <option value="A">[A] Achat inactif pour l'institution</option>
                <option value="D">[D] Disciplines désactivées</option>
                <option value="H">[H] Crédit d'achat institution</option>
                <option value="O">[O] Ordre des onglets</option>
                <option value="P">[P] Onglets désactivés</option>
                <option value="S">[S] Disciplines désactivées pour la recherche</option>
                <option value="U">[U] ?</option>
                <option value="Y">[Y] Types de publication désactivés pour la recherche</option>
            </select>

            <br/><br/>
            <!-- A remplacer par une select box avec les disciplines si on est en mode D ou S
                                                avec les typepub si on est en mode Y
            -->
            Valeur :
            <input class="choice_value" id="insert_value_param" type="text" name="input" id="input" size="40" style="display:none;"/>

            <select class="choice_value" id="select_disciplines" style="display:none;">
                <option value="">Veuillez sélectionner une discipline</option>
                <?php
                    foreach ($disciplines as $discipline)
                    {
                        echo '<option value="'.$discipline['POS_DISC'].'">'.$discipline['DISCIPLINE'].'</option>';
                    }
                ?>
            </select>

            <select class="choice_value" id="select_typepub" style="display:none;">
                <option value="">Veuillez sélectionner une typepub</option>
                <?php
                    foreach ($typePubs as $typePub)
                    {
                        echo '<option value="'.$typePub['TYPEPUB'].'">'.$typePub['NOM_TYPEPUB'].'</option>';
                    }
                ?>
            </select>

            <input id="user_params" type="hidden" value="<?php echo $_GET['id_user']; ?>">
        </p>
        <br>
        <div class="buttons">
            <a href="javascript:ajax.addCairnParams();" class="blue_button ok">Ok</a>
            <a href="javascript:cairn.close_modal();" class="blue_button ok">Fermer</a>
        </div>
    </div>
</div>



<div id="header">
    <ul id="nav1">
        <li>
            <a href="./">Retour au site www2.semantic.lu</a>
        </li>
    </ul>

    <img id="middleLogo" src="./static/images/logo-cairn.png" alt="CAIRN.INFO : Chercher, repérer, avancer.">
</div>

<div id="contenu">
    <div id="wrapper_category_tabs">
        <div class="mainTabs" id="main_tabs">
            <ul id="category_tabs">
                <li>
                    <a href="./statistiques_consultation.php" class="blue_button">Statistiques de consultation du site</a>
                </li>
                <li>
                    <a href="./gestion_utilisateurs.php" class="blue_button">Gestion des utilisateurs</a>
                </li>
            </ul>
        </div>
    </div>

    <div id="gestion_container">
        <h1>Gestion des utilisateurs</h1>
        <hr>

        <table id="gestionTableHeader">
            <tr>
                <td>
                    <form method="GET" enctype="text/plain">
                        <div class="time_container">
                            <h5>User name</h5>
                            <input id="id_user" type="text" name="id_user" value="<?php if(isset($_GET['id_user'])){echo $_GET['id_user'];}?>">
                            <input id="submit_dates" type="submit" value="Search">
                        </div>
                    </form>
                </td>
                <td>
                    <h5>N. Sessions</h5>
                    <p id="n_sessions"><?php echo $userSessionCounter['COUNT(*)']; ?></p>
                </td>
                <td>
                    <h5>Max. Sessions</h5>
                    <p id="max_sessions"><?php echo $userMaxSessionCounter['MAX_AB']; ?></p>
                </td>
                <td>
                    <h5>Change Max. Sessions</h5>
                    <input style="width:20px" type="text" id="changeMaxSessions">
                    <input type="button" onclick="javascript:ajax.changeMaxSessions()" value="ok">
                </td>

                <td>
                    <h5>Max. Sessions IP</h5>
                    <p id="max_ip_sessions"><?php echo $instMaxSessionCounter['MAX_IP']; ?></p>
                </td>
                <td>
                    <h5>Change Max. Sessions IP</h5>
                    <input style="width:20px" type="text" id="changeMaxSessionsIP">
                    <input type="button" onclick="javascript:ajax.changeMaxSessionsIP()" value="ok">
                </td>
            </tr>
        </table>

        <br>

        <table id="gestionTableContent" cellspacing="0" cellpadding="0">
            <tr>
                <td width="40%"><b>Network address</b></td>
                <td width="40%"><b>Subnet Mask</b></td>
                <?php
                    if(isset($_GET['id_user']))
                    {
                        echo '
                            <td width="20%">
                                <a class="icons" href="javascript:cairn.show_modal(\'#add_address_modal\');">
                                    <img src="./static/images/add-icon.png" alt="Add" title="Add">
                                </a>
                            </td>
                            ';
                    }
                ?>

            </tr>

            <?php
            foreach($networkAddresses as $networkAddress) {
                echo '
                    <tr>
                        <td style="border-top:1px solid #999;">'.$networkAddress['NETWORK_ADDRESS'].'</td>
                        <td style="border-top:1px solid #999;">'.$networkAddress['SUBNET_MASK'].'</td>
                        <td style="border-top:1px solid #999;">
                            <a class="icons" href="javascript:ajax.removeNetworkAddress(\''.urlencode($_GET['id_user']).'\','.$networkAddress['ID_IP'].');">
                                <img src="./static/images/remove-icon.png" alt="Remove" title="Remove">
                            </a>
                            <a class="icons" href="javascript:ajax.editModalNetworkAddress(\''.urlencode($_GET['id_user']).'\',\''.$networkAddress['ID_IP'].'\',\''.$networkAddress['NETWORK_ADDRESS'].'\',\''.$networkAddress['SUBNET_MASK'].'\');">
                                <img src="./static/images/edit-icon.png" alt="Edit" title="Edit">
                            </a>

                        </td>
                    </tr>
                ';
            }
            ?>
        </table>

        <br>

        <?php if($getTypeUser['TYPE'] == 'I'){ ?>
        <table id="gestionParamsContent" cellspacing="0" cellpadding="0">
            <tr>
                <td width="40%">
                    <h5>Filtre Institution</h5>
                </td>
                <td width="40%">
                    <h5>Valeur</h5>
                </td>
                <td width="20%">
                    <a class="icons" href="javascript:cairn.show_modal('#add_params_modal');">
                        <img src="./static/images/add-icon.png" alt="Add" title="Add">
                    </a>
                </td>
            </tr>

            <?php foreach ($cairnParams as $cairnParam){ ?>
            <tr>
                <td style="border-top:1px solid #999;">
                <?php
                switch ($cairnParam['TYPE']) {
                    case 'A':
                        echo "A (Achat inactif pour l'institution)";
                    break;
                    case 'D':
                        echo "D (Disciplines désactivées)";
                    break;
                    case 'H':
                        echo "H (Crédit d'achat institution)";
                    break;
                    case 'O':
                        echo "O (Ordre des onglets)";
                    break;
                    case 'P':
                        echo "P (Onglets désactivés)";
                    break;
                    case 'S':
                        echo "S (Disciplines désactivées pour la recherche)";
                    break;
                    case 'U':
                        echo "U (?)";
                    break;
                    case 'Y':
                        echo "Y (Types de publication désactivés pour la recherche)";
                    break;
                }
                ?>
                </td>
                <td style="border-top:1px solid #999;"><?php echo $cairnParam['VALEUR']; ?></td>
                <td style="border-top:1px solid #999;">
                    <a class="icons" href="javascript:ajax.removeCairnParam('<?php echo $cairnParam['TYPE']; ?>','<?php echo $cairnParam['VALEUR']; ?>','<?php echo urlencode($_GET['id_user']); ?>')">
                        <img src="./static/images/remove-icon.png" alt="Remove" title="Remove">
                    </a>
                </td>
            </tr>
            <?php } ?>

        </table>
        <?php } ?>
        <br>
        <br>
    </div>

</div>


<?php
$this->javascripts[] = <<<'EOD'
    $(document).ready(function(){
        $('select#params_select').on('change',function(){

            //reset visibilaty..
            $('select#select_disciplines').css('display','none');
            $('select#select_typepub').css('display','none');
            $('input#insert_value_param').css('display','none');
            //reset values..
            $('select#select_disciplines').val('');
            $('select#select_typepub').val('');
            $('input#insert_value_param').val('');


            option = $(this).val();

            if( (option == 'D') || (option == 'S') )
            {
                $('select#select_disciplines').css('display','block');
            }
            else if( (option == 'P') || (option == 'Y') )
            {
                $('select#select_typepub').css('display','block');
            }
            else
            {
                $('input#insert_value_param').css('display','block');
            }
        });
    });
EOD;
?>
