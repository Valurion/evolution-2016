<?php 

switch($vueMode){
    case 0:
        header("Location: ./Accueil_Revues.php?shib=1");
        break;
    default:
        
        $this->titre = "Off-Campus Access";
        include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
        <div id="breadcrump">
            <a class="inactive" href="/">Home</a>
            <span class="icon-breadcrump-arrow icon"></span>
            <a href="#">Confirmation</a>
        </div>
        <div id="body-content">
            <div id="free_text">
                <h1 class="main-title">Off-Campus Access</h1>
                <br/>
                <?php if($vueMode == -1){
                    echo '<p>There are too many users already connected with this account.
                            <br>Please try later.</p>';
                }else if($vueMode == -2){
                    echo '<p>There are too many users already connected with this account.
                            <br>Please try later.</p>';
                }?>                
            </div>
        </div> 
<?php
}
?>
