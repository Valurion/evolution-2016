<?php
$this->titre = "Accès hors campus";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>

<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Accès hors campus</a>
</div>

<div id="body-content">
    <?php if($err == 1){?>
    <div class="red">Votre tentative de connexion hors-campus a échoué, merci de ré-essayer plus tard.</div>
    <?php }?>
    <div id="free_text">

        <h1 class="main-title">Accès hors campus, authentification</h1>
        <a target="_blank" href="http://aide.cairn.info/comment-acceder-aux-publications-diffusees-sur-cairn-a-distance/"><span class="question-mark">
                <span class="tooltip">En savoir plus sur l'accès distant</span>
            </span></a>

        <form method="get" action="javascript:ajax.loginsso()"
               name="wayf">
            <input type="hidden" id="baseUrl" value="<?= $baseUrl ?>"/>
            <input type="hidden" id="targetUrl" value="<?= $targetUrl ?>"/>
              <p>Veuillez sélectionner le pays de votre établissement d’appartenance puis le nom de votre établissement et
                cliquer sur "Valider".
                <br>Puis, si vous ne l'avez pas encore fait, indiquez vos
                identifiant et mot de passe sur le serveur d’authentification de
                votre établissement.<br>
                Vous retournerez alors sur la page d'accueil de Cairn.info où
                serez reconnu(e) comme membre de cet établissement.</p>

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
                        <label for="etabl">Etablissement</label> :
                    </td>
                    <td>
                        <select id="etablFrance" class="selectetab" name="SSOCAS" style="width:550px;">
                            <option class="ital" value="0">Sélectionnez...</option>

                            <?php foreach($ssos as $sso){
                                $urlLogin = $sso['URL_LOGIN'];
                                if(strpos($sso['ID_SSO'],'SHI_') === false && strpos($urlLogin,'service=') !== false && strpos($urlLogin,'service=') == (strlen($urlLogin)-8) ){
                                    $urlLogin .= "http://www.cairn.info/identSSO.php";
                                }
                                echo '<option value="'.$urlLogin.'">'.$sso['TITLE'].'</option>';
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
                                        <option class="ital" value="0">Sélectionnez...</option>';
                                $lastPays = $ssoInt['PAYS'];
                            }
                            $urlLogin = $ssoInt['URL_LOGIN'];
                            if(strpos($ssoInt['ID_SSO'],'SHI_') === false && strpos($urlLogin,'service=') !== false && strpos($urlLogin,'service=') == (strlen($urlLogin)-8) ){
                                $urlLogin .= "http://www.cairn.info/identSSO.php";
                            }
                            echo '<option value="'.$urlLogin.'">'.$ssoInt['TITLE'].'</option>';
                        }
                        if($lastPays != ''){
                            echo '</select>';
                        }
                        ?>
                    </td>
                    <td>
                        &nbsp;&nbsp;<input type="submit" class="button" value="Valider">
                    </td>
                </tr>
            </table>
            <p>
                <br>Si votre établissement ne figure pas dans la liste
                ci-dessus, veuillez vous adresser au responsable de votre service
                commun de documentation.
            </p>
        </form>
    </div>
</div>



<?php
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
