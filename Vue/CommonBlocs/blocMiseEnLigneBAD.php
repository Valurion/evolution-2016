<div class="bad" >
<?php 
$dateTransfertFormated = new dateTime($oneTokenBAD['DATE_TRANSFERT']);
$dateTransfertFormated = $dateTransfertFormated->format('d-m-Y');
setlocale(LC_TIME, "fr_FR.utf8");
if( (!isset($oneTokenBAD['DATE_TRANSFERT'])) || ($oneTokenBAD['DATE_TRANSFERT'] == "0000-00-00") )
{
	echo "Mise en ligne en attente d'autorisation";
}
else 
{
	echo "Mise en ligne prévue le <br><br><span class='badDate'>" . $dateTransfertFormated ."</span>";
}

$messageBad = "La mise en ligne de ";
if($numero["REVUE_TYPEPUB"] == "3" || $numero["REVUE_TYPEPUB"] == "6") { $messageBad .= "de cet ouvrage "; }
else { $messageBad .= "de ce numéro "; }

if( (!isset($oneTokenBAD['DATE_TRANSFERT'])) || ($oneTokenBAD['DATE_TRANSFERT'] == "0000-00-00") ) {
	$messageBad .= "est en attente de votre accord.";
}
else {
    $messageBad .= "est prévue le ". $dateTransfertFormated ." au matin";
}


?>

    <br>
    <br>
    <div class="btnBAD">
        <a href="#" onclick="cairn.show_modal('#div_modal_alert');">Modifier la date/Signaler des erreurs</a>
    </div>

</div>
<div id="div_modal_alert" class="window_modal" style='font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;'>
    <div class="info_modal" style="border: none;box-shadow: 0 5px 15px rgba(0,0,0,.5);border-radius: 6px;">
        <?php 
        if( (!isset($oneTokenBAD['DATE_TRANSFERT'])) || ($oneTokenBAD['DATE_TRANSFERT'] == "0000-00-00") )
        {
            echo "<h1 style='font-size: 1.5em;'>Mise en ligne en attente d'autorisation</h1><br>";
        }
        else 
        {
            echo "<h1 style='font-size: 1.5em;'>Mise en ligne prévue le ". $dateTransfertFormated ."</h1><br>";
        }
        ?>

        <div  style="text-align:left;color: #4bb2ac;">
        <span style ="color:black;" ><?php echo $messageBad ;?></span>
        <br>
        <br>
        <form action="" method="post">
        <span class="close_modal" onclick="cairn.close_modal();"></span>
        <input id="CAE_checkbox" type="checkbox" name="correctionAEffectuer" value="CAE" onchange="showComment();"><span>Signaler des corrections à effectuer</span><br>
            
            <div id="areaComment">
            <div style="margin-bottom: 10px;margin-top: 10px;border: 1px solid #ccc;border-radius: 3px;padding: 5px;text-align: left;">
                <label for="f_message" style="text-align: left;cursor: pointer;"><strong>Commentaires à l’attention des convertisseurs</strong></label>
                <textarea id="f_message" name="commentaires" style ="
                    background-color: #fff;
                    border: 0 none;
                    display: block;
                    font-family: 'Alegreya',serif;
                    font-size: 17px;
                    margin: 5px;
                    resize: none;
                    width: 98%;"></textarea>
            </div>
            </div>
        
            <input id="MEL_checkbox" type="checkbox" name="miseenligneAutorisation" value="MEL" checked onchange="showMEL()">Modifier la date de mise en ligne<br>
            <div id="badFormRadioLvl2" style="padding-left: 25px;">
                <?php if($numero["REVUE_TYPEPUB"] == "3") {  ?>
                <?php //if (true) { ?>
                au début du mois de
                <select name="miseenlignemois">
                    <?php for($iBAD=1 ; $iBAD<=6 ; $iBAD++) { 
                          $dateTmpBad = new DateTime((date("Y-m")."-1"));
                          $dateTmpBad->add(new DateInterval('P'.$iBAD.'M'));
                        ?>
                    <option value="<?php echo $iBAD ;?>" ><?php echo(strftime("%B %Y",$dateTmpBad->getTimestamp())); ?></option>
                    <?php } ?>
                </select>
                <?php }else { ?>
                    <input type="radio" name="miseenligne" value="NOW" checked>Demain matin<br>
                    
                     
                    <?php if( (!isset($oneTokenBAD['DATE_TRANSFERT'])) || ($oneTokenBAD['DATE_TRANSFERT'] == "0000-00-00") ) {?>
                    <input type="radio" name="miseenligne" value="date">Le
                    <input type="text" name ="date" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}" placeholder="jj-mm-aaaa" style="width: 120px;"><br>
                    <?php } else {?>
                    <input type="radio" name="miseenligne" value="date" checked>Le
                    <input type="text" name ="date" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}" placeholder="jj-mm-aaaa" value="<?php echo $dateTransfertFormated ; ?>"  style="width: 120px;"><br>
                    <?php } ?>
                <?php } ?>
            </div>
            <br>
            <div class="inbl">
                <input class="button" type="submit" id="submitMiseEnLigneBADN" name="submitMiseEnLigneBADN" value="Valider">
            </div>
        </form>
        </div>
    </div>
</div>
<script type="text/javascript">
function showComment() {
	
	var etat = document.getElementById('CAE_checkbox').checked;

	if(etat)
	{
		document.getElementById('areaComment').style.display = "";
	}
	else
	{
		document.getElementById('areaComment').style.display = "none";
	}
}

function showMEL() {
	
	var etat = document.getElementById('MEL_checkbox').checked;

	if(etat)
	{
		document.getElementById('badFormRadioLvl2').style.display = "";
	}
	else
	{
		document.getElementById('badFormRadioLvl2').style.display = "none";
	}
}

showComment();

</script>
