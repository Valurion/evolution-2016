<?php
header('Content-Type: text/html; charset=utf-8');
$this->titre = "Statistique de consultation du site";

if(empty($authInfos['U']['ADMIN'])) {
    header('Location: ./');
}
?>

<style>
    #statistic_container
    {
        display:block;
        margin:0 auto;
        width:960px;
        height:auto;
    }

    #statistic_container table
    {
        margin:10px 0;
    }

    #statistic_container table#statisticTableHeader tr td .time_container
    {
        display:inline-block;
        margin:0 auto;
        padding:0 20px;
    }

    #statistic_container h1
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
</style>

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

    <div id="statistic_container">
        <h1>Statistique de consultation du site</h1>
        <hr>
        <table id="statisticTableHeader">
            <tr>
                <td>
                    <p class="dates"><?php echo (new DateTime())->format('d/m/Y'); ?></p>
                    <p id="hours"></p>
                </td>
                <td>
                    <form action="./statistiques_consultation.php">
                        <h4>Time travel (Empty fields = Now)</h4>
                        <div class="time_container">
                            <p>Begin date (yyyy-mm-dd)</p>
                            <input id="begin_date" type="text" name="begin_date" value="<?php if(isset($_GET['begin_date'])){echo $_GET['begin_date'];}?>">
                        </div>

                        <div class="time_container">
                            <p>End date (yyyy-mm-dd)</p>
                            <input id="end_date" type="text" name="end_date" value="<?php if(isset($_GET['end_date'])){echo $_GET['end_date'];}?>">
                        </div>

                        <br clear="all">

                        <input id="submit_dates" type="submit" value="submit">
                    </form>
                </td>
                <td>
                    <h4>Max. Sessions</h4>
                    <p id="max_sessions"><?php echo $maxSessionCounter; ?></p>
                </td>
            </tr>
        </table>

        <table style="display:none">
            <tr>
                <td colspan="2">
                    <h4>Totals</h4>
                </td>
            </tr>
            <tr>
                <td width="50%">Guests</td>
                <td width="50%"><?php echo $guestSessionCounter['COUNT(*)']; ?></td>
            </tr>
            <tr>
                <td width="50%">Users</td>
                <td width="50%"><?php echo $userSessionCounter['COUNT(*)']; ?></td>
            </tr>
            <tr>
                <td width="50%">Institutions</td>
                <td width="50%"><?php echo $institutionSessionCounter['COUNT(*)']; ?></td>
            </tr>
        </table>

        <br>
        <h3 style="text-align:center;">Utilisateurs Enregistrés</h3>
        <table>
            <tr>
                <td></td>
                <td><b class="blue">User Login</b></td>
                <td><b class="blue">N. Sessions</b></td>
                <td><b class="blue">N. Max Sessions</b></td>
                <td><b class="blue">N. Sessions IP</b></td>
                <td><b class="blue">N. Max Sessions IP</b></td>
            </tr>

            <?php
                foreach ($dataBoardUser as $data)
                {
                    echo '<tr>
                            <td><img src="./static/images/bullet_list_icon.png"></td>
                            <td>
                                <a href="./gestion_utilisateurs.php?id_user='.urlencode($data['ID_USER']).'">'.$data['ID_USER'].'</a>
                            </td>
                            <td>'.$data['USER_AB'].'</td>
                            <td>'.$data['MAX_AB'].'</td>
                            <td>'.$data['USER_IP'].'</td>
                            <td>'.$data['MAX_IP'].'</td>
                        </tr>';
                }
            ?>
        </table>

        <br>
        <h3 style="text-align:center;">Utilisateurs Anonymes</h3>
        <table>
            <tr>
                <td></td>
                <td><b class="blue">User Login</b></td>
                <td><b class="blue">Date</b></td>
            </tr>
            <?php
                foreach($dataBoardGuest as $data)
                {
                    echo '<tr>
                            <td><img src="./static/images/bullet_list_icon.png"></td>
                            <td>'.$data['ID_USER'].'</td>
                            <td>'.$data['CDATE'].'</td>
                        </tr>';
                }
            ?>

        </table>
    </div>

</div>



<?php
$this->javascripts[] = <<<'EOD'
    document.body.onload = function() {
        function startTime(){
            var today = new Date();
            var h = today.getHours();
            var m = today.getMinutes();
            var s = today.getSeconds();
            m = checkTime(m);
            s = checkTime(s);
            document.getElementById('hours').innerHTML = h+":"+m+":"+s;
            var t = setTimeout(function(){startTime()},500);
        }

        function checkTime(i) {
            if (i<10) {i = "0" + i};  // add zero in front of numbers < 10
            return i;
        }

        startTime();
    }
EOD;
?>
