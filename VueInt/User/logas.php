<?php
    /* Cette vue a trois formes différentes:
     *  - connecté particulier
     *  - connecté institution
     *  - connecté institution ET particulier
     */
    
if(isset($authInfos["U"]) && !isset($authInfos["I"])){
?>
    <a id="user_islogin" onclick="ajax.logout()" href="javascript:void(0)"><?= $authInfos["U"]["PRENOM"] . " " . $authInfos["U"]["NOM"] ?><span class="icon icon-logout"></span></a>
<?php
}else if(!isset($authInfos["U"]) && isset($authInfos["I"])){
?>
    <a id="user_islogout" onclick="cairn.show_menu(this, '#menu_login');" href="javascript:void(0)">
        <span class="icon icon-login"></span>Login
    </a>
    <span id="inst_islogin"><?= $authInfos["I"]["NOM"] ?></span>
<?php
}else if(isset($authInfos["U"]) && isset($authInfos["I"])){
?>
    <a id="user_islogin" onclick="ajax.logout()" href="javascript:void(0)"><?= $authInfos["U"]["PRENOM"] . " " . $authInfos["U"]["NOM"] ?><span class="icon icon-logout"></span></a>
    <span id="inst_islogin"><?= $authInfos["I"]["NOM"] ?></span>
<?php
}
?>
