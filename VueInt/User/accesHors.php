<?php
$this->titre = "Off-Campus Access";
include (__DIR__ . '/../CommonBlocs/tabs.php');

$this->javascripts[] = <<<'EOD'
    function switchList(){
        var paysSelect = document.getElementById('pays').value;
        var lists = document.getElementsByTagName('select');
        for(var i = 0 ; i < lists.length ; i++){
            var list = lists[i];
            if(list.id != 'pays'){
                if(list.id == 'etabl'+paysSelect){
                    list.style.display='block';
                }else{
                    list.style.display='none';
                }
            }
        }
    }
EOD;
?>

<div id="breadcrump">
    <a class="inactive" href="./">Home</a> <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Off-Campus Access</a>
</div>

<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">Off-Campus Access, Authentication</h1>
        <!--a target="_blank" href="http://aide.cairn.info/comment-acceder-aux-publications-diffusees-sur-cairn-a-distance/"><span class="question-mark">
                <span class="tooltip">En savoir plus sur l'accès distant</span>
            </span></a-->

        <form method="get" action="javascript:ajax.loginsso()"
               name="wayf">
            <input type="hidden" id="baseUrl" value="<?= $baseUrl ?>"/>
            <input type="hidden" id="targetUrl" value="<?= $targetUrl ?>"/>
              <p>Please select your country and the institution with which you are affiliated and click "Validate".
                <br>Next, if you haven’t already done so, enter your login I.D. and password on your institution’s authentication server.<br>
                You will then be returned to the Cairn International home page where you will be recognized as a member of this institution.</p>

            <table border="0" style="border:none; text-align:left;">
                <tr>
                    <td width="150px;">
                        <label for="pays">Pays</label> :
                    </td>
                    <td>
                        <select id="pays" class="selectetab" name="pays" style="width:550px;" onchange="switchList()">
                            <!--option class="ital" value="0">Sélectionnez...</option-->
                            <option class="ital" value="France">France</option>

                            <?php foreach($ssosPays as $ssoPays){
                               echo '<option value="'.$ssoPays['PAYS'].'">'.$ssoPays['PAYS'].'</option>';
                            }?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="etabl">Institution</label> :
                    </td>
                    <td>
                        <select id="etablFrance" class="selectetab" name="SSOCAS" style="width:550px;">
                            <option class="ital" value="0">Please select...</option>

                            <?php foreach($ssos as $sso){
                                echo '<option value="'.$sso['URL_LOGIN'].'">'.$sso['TITLE'].'</option>';
                            }?>
                        </select>

                        <!-- Boucler sur les établissments des autres pays-->
                        <?php
                        $lastPays = "";
                        foreach ($ssosInt as $ssoInt){
                            if($lastPays != $ssoInt['PAYS']){
                                if($lastPays != ''){
                                    echo '</select>';
                                }
                                echo '<select id="etabl'.$ssoInt['PAYS'].'" class="selectetab" name="SSOCAS" style="width:550px; display:none;">
                                        <option class="ital" value="0">Please select...</option>';
                                $lastPays = $ssoInt['PAYS'];
                            }
                            echo '<option value="'.$ssoInt['URL_LOGIN'].'">'.$ssoInt['TITLE'].'</option>';
                        }
                        if($lastPays != ''){
                            echo '</select>';
                        }
                        ?>
                    </td>
                    <td>
                        &nbsp;&nbsp;<input type="submit" class="button" value="Validate">
                    </td>
                </tr>
            </table>
            <p>
                <br>If your institution is not listed among the options above, please contact your librarian.
            </p>
        </form>
    </div>
</div>


