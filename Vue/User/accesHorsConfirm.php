<?php 

switch($vueMode){
    case 0:
        header("Location: /Accueil_Revues.php?shib=1");
        break;
    default:
        
        $this->titre = "Connexion Hors Campus";
        include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
        <div id="breadcrump">
            <a class="inactive" href="/">Accueil</a>
            <span class="icon-breadcrump-arrow icon"></span>
            <a href="#">Confirmation</a>
        </div>
        <div id="body-content">
            <div id="free_text">
                <h1 class="main-title">Connexion Hors Campus</h1>
                <br/>
                <?php if($vueMode == -1){
                    echo '<p>Le nombre maximum d\'utilisateurs actuellement connectés avec cette adresse email est atteint.
                            <br>Veuillez réessayer un peu plus tard.</p>';
                }else if($vueMode == -2){
                    echo '<p>Le nombre maximum d\'utilisateurs actuellement connectés à cette institution est atteint.
                        <br>Veuillez réessayer un peu plus tard.</p>';
                }?>                
            </div>
        </div>
<?php
}
?>
