<?php $this->titre = "Activation accès abonnés particuliers"; ?>

<?php include (__DIR__ . '/../CommonBlocs/tabs.php'); ?>

<div id="breadcrump">
    <div id="breadcrump_main">
        <a class="inactive" href="/">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
        <a href="code-abonnement-papier.php">Activation d'accès</a>
    </div>
</div>

<div id="body-content">
    <div id="free_text" class="biblio">

        <h1 class="main-title">Activation d'accès pour abonnés particuliers</h1>

        <p>Cette fonction est réservée aux abonnés à titre individuel à la revue, disposant d'un code d'abonné que l'éditeur leur a transmis.</p>

        <div class="articleBody mt1">
            <h2 class="section">Précisez la revue à laquelle vous êtes abonné(e)</h2>
            <form id="ajoutalertes" method="post" name="ajoutalertes" action='code-abonnement-papier.php'>                
                <div class="wrapper">
                    <div class="blue_milk left w45">
                        <label for="ID_REVUE">Revue</label>
                        <select name="ID_REVUE" id="ID_REVUE" style="width: 100%;">
                            <option class="ital" selected value="">Choisir la revue...</option>
                            <?php foreach ($revues as $revue): ?>
                                <?php if($revue["ACCES_EN_LIGNE"] == 1) { ?>
                                    <option value="<?php echo $revue['ID_REVUE'] ?>"><?php echo $revue['TITRE'] ?></option>
                                <?php } ?>
                            <?php endforeach; ?>
                        </select>
                    </div>                    
                    <div class="blue_milk right w45">
                        <label for="code_abonne">Code d'abonné</label>
                        <input type="text" name="code_abonne" id="code_abonne" value="">
                    </div>
                </div>
                <div class="wrapper mt1">
                    <input type='submit' class="button right" value='Activer mon accès'>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
    if(isset($_GET["ID_REVUE"])) {
        echo "<script type=\"text/javascript\">document.getElementById('ID_REVUE').value='".$_GET["ID_REVUE"]."';</script>";
    }
?>
