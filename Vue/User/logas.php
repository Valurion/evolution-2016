<?php
    /* Cette vue a trois formes différentes:
     *  - connecté particulier
     *  - connecté institution
     *  - connecté institution ET particulier
     */

if(isset($authInfos["U"]) && !isset($authInfos["I"])){
?>
    <a id="user_islogin" onclick="ajax.logout()" href="javascript:void(0)"><?= htmlentities($authInfos["U"]["PRENOM"]) . " " . htmlentities($authInfos["U"]["NOM"]) ?><span class="icon icon-logout"></span></a>
<?php
}else if(!isset($authInfos["U"]) && isset($authInfos["I"])){
?>
    <a id="user_islogout" onclick="cairn.show_menu(this, '#menu_login');" href="javascript:void(0)">
        <span class="icon icon-login"></span>Connexion
    </a>
    <span id="inst_islogin"><?= htmlentities($authInfos["I"]["NOM"]) ?></span>
<?php
}else if(isset($authInfos["U"]) && isset($authInfos["I"])){
?>
    <a id="user_islogin" onclick="ajax.logout()" href="javascript:void(0)"><?= htmlentities($authInfos["U"]["PRENOM"]) . " " . htmlentities($authInfos["U"]["NOM"]) ?><span class="icon icon-logout"></span></a>
    <span id="inst_islogin"><?= htmlentities($authInfos["I"]["NOM"]) ?></span>    
<?php
}


//On regarde si il est nécessaire de générer le cache des articles accessibles aux utilisateurs connectés
if(isset($authInfos['I']) && $authInfos['I']['CACHE'] == 0){
    $this->javascripts[] = "ajax.loadAccessIntoCache('".$authInfos['I']['ID_USER']."');";
}
?>

