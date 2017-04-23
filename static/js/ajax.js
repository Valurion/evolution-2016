/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ajax = {}

ajax.call = function(url, params, callback, callback_params) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var response = xmlhttp.responseText;
            if (response.indexOf("error_div_modal") >= 0) {
                document.getElementById('error_div').innerHTML = response;
                cairn.show_modal("#error_div_modal");
            } else {
                if(ajax[callback])
                ajax[callback](callback_params, response);
            }
        }
    }
    xmlhttp.open("POST", url, true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send(params);
}

ajax.callback_default = function(bloc, response){
    document.getElementById(bloc).innerHTML = response;
}

ajax.login = function() {
    var email = document.getElementById('email_input').value;
    var password = document.getElementById('password_input').value;
    var remember = document.getElementById('remember').checked;

    document.getElementById('password_input').value = '';
    cairn.close_login_modal();

    this.call('./index.php?controleur=User&action=login', 'EMAIL=' + email + '&PSW=' + encodeURIComponent(password) + '&remember=' + encodeURIComponent(remember), 'callback_loginBeforeCors');
    /*this.call('./index.php?controleur=User&action=login', 'EMAIL=' + email + '&PSW=' + encodeURIComponent(password), 'callback_loginlogout');
    if(document.getElementById('corsURL') && document.getElementById('corsURL').value != ''){
        this.createCORSRequest('http://' + document.getElementById('corsURL').value + '/index.php?controleur=User&action=login', 'EMAIL=' + email + '&PSW=' + encodeURIComponent(password));
    }*/
}

ajax.createCORSRequest = function(url,params) {
  var xhr = new XMLHttpRequest();
  xhr.withCredentials = true;
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4){
        ajax.callback_loginlogout();
    }
  }

  if ("withCredentials" in xhr) {
    // Check if the XMLHttpRequest object has a "withCredentials" property.
    // "withCredentials" only exists on XMLHTTPRequest2 objects.
    xhr.open("POST", url, true);
  } else if (typeof XDomainRequest != "undefined") {
    // Otherwise, check if XDomainRequest.
    // XDomainRequest only exists in IE, and is IE's way of making CORS requests.
    xhr = new XDomainRequest();
    xhr.open("POST", url);
  } else {
    // Otherwise, CORS is not supported by the browser.
    xhr = null;
    console.log('CORS not supported...');
  }
  if(xhr){
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.send(params);
  }
  return xhr;
}


ajax.logout = function() {
    this.call('./index.php?controleur=User&action=logout', null, 'callback_loginlogout');
}

ajax.callback_loginBeforeCors = function() {
    if(document.getElementById('corsURL') && document.getElementById('corsURL').value != ''){
        var value = $.cookie("cairn_token");
        var email = document.getElementById('email_input').value;
        this.createCORSRequest('https://' + document.getElementById('corsURL').value + '/index.php?controleur=User&action=loginCors', 'email='+email+'&token=' + encodeURIComponent(value));
    }else{
        ajax.callback_loginlogout();
    }
}

ajax.callback_loginlogout = function() {
    //On recharge la page pour permettre aux liens sécurisés de se mettre à jour
    setTimeout("window.location.reload()",0500);

}

ajax.connexion = function() {
    var email = document.getElementById('email_connexion').value;
    var password = document.getElementById('password_connexion').value;

    this.call('./index.php?controleur=User&action=connexion', 'email_connexion=' + email + '&password_connexion=' + encodeURIComponent(password), 'callback_connexion_refresh');
    if(document.getElementById('corsURL') && document.getElementById('corsURL').value != ''){
        this.createCORSRequest('http://' + document.getElementById('corsURL').value + '/index.php?controleur=User&action=connexion', 'email_connexion=' + email + '&password_connexion=' + encodeURIComponent(password));
    }
}
ajax.callback_connexion = function(){
    //TODO : gérer le contexte du panier
    setTimeout("ajax.goToIndex()",0500);
}

ajax.callback_connexion_refresh = function() {
    window.location.reload();
}

ajax.goToIndex = function(){
    window.location.href = 'index.php';
}

ajax.CairnIntInfoVisitor = function() {
    this.call('./index.php?controleur=User&action=cairnIntInfoVisitorCookie');
    document.getElementById('cairn-int-info-visitor').style.display='none';
}

ajax.CreditArticleAlert = function() {
    var todo  = "";
    var status = document.getElementById('CreditArticleAlert').checked;
    if(status === true) {todo = "create";} else {todo = "tempo";}
    this.call('./index.php?controleur=User&action=CreditArticleAlertCookie&todo='+todo);
}

ajax.modifEmail = function(){
    var email   = document.getElementById('email').value;
    var cemail  = document.getElementById('cemail').value;
    var mdp     = document.getElementById('mdp').value;

    var str = "email="+email+"&mdp="+encodeURIComponent(mdp)+"&cemail="+cemail;

    this.call('./index.php?controleur=User&action=updEmail', str, 'callback_connexion');
}
ajax.modifMdp = function(){
    var email = document.getElementById('email').value;
    var mdp = document.getElementById('mdp').value;
    var mdp2 = document.getElementById('mdp2').value;
    var mdp3 = document.getElementById('mdp3').value;

    var str = "email="+email+"&mdp="+encodeURIComponent(mdp)+"&mdp2="+encodeURIComponent(mdp2)+"&mdp3="+encodeURIComponent(mdp3);

    this.call('./index.php?controleur=User&action=updMdp', str, 'callback_connexion');
}

ajax.addToBiblio = function(idNumero,idArticle){
    var str = "todo=add&idNumPublie="+idNumero+"&idArticle="+idArticle;
    this.call('./index.php?controleur=User&action=biblioActions',str,'callback_addToBiblio', idNumero+'-'+idArticle);
}

ajax.removeFromBiblio = function(idNumero,idArticle){
    var str = "todo=remove&idNumPublie="+idNumero+"&idArticle="+idArticle;
    this.call('./index.php?controleur=User&action=biblioActions',str,'callback_removeFromBiblio', idNumero+'-'+idArticle);
}

ajax.removeFromBiblioPage = function(idNumero,idArticle){
    var str = "todo=remove&idNumPublie="+idNumero+"&idArticle="+idArticle;
    this.call('./index.php?controleur=User&action=biblioActions',str,'callback_removeFromBiblioPage', idNumero+'-'+idArticle);
}

ajax.callback_addToBiblio = function(id){
    document.getElementById('removeFromBiblio'+id).style.display = 'block';
    document.getElementById('addToBiblio'+id).style.display = 'none';
}

ajax.callback_removeFromBiblio = function(id){
    document.getElementById('addToBiblio'+id).style.display = 'block';
    document.getElementById('removeFromBiblio'+id).style.display = 'none';
}
ajax.callback_removeFromBiblioPage = function(id){
    // Suppression du block
    document.getElementById(id).style.display = 'none';

    // Gestion du block parent (Numero de Revue, Article de Revue, ...)
    // Récupération du parent
    parent      = document.getElementById(id).parentNode.id;
    parentCount = document.getElementById("nbre"+parent).value;
    parentCount--;
    // Assignation de la valeur
    document.getElementById("nbre"+parent).value = parentCount;
    // Suppression du block
    if(parentCount == 0) {document.getElementById(parent).style.display = 'none';}


    // Gestion du nombre d'élément total (si = 0, rafraichissement de la page)
    // Calcul du nombre d'article dans la bibliographie
    totalCount = document.getElementById("nbreTotalBiblio").value;
    totalCount--;
    // Assignation de la valeur
    document.getElementById("nbreTotalBiblio").value = totalCount;
    // Refresh de la page
    if(totalCount == 0) {window.location.reload();}
}

ajax.removeFromBasket = function(type,id1,id2,id3){
    var str = "todo=remove&type="+type+"&id1="+id1+"&id2="+id2+"&id3="+id3;
    var returnId = id1;
    if(id2){
        returnId +="-"+id2;
    }
    if(id3){
        returnId +="-"+id3;
    }
    this.call('./index.php?controleur=User&action=panierActions',str,'callback_removeFromBasket', returnId);
}

ajax.removeFromBasketInst = function(type,id1,id2){
    var str = "todo=remove&type="+type+"&id1="+id1+"&id2="+id2;
    var returnId = id1;
    if(id2){
        returnId +="-"+id2;
    }
    this.call('./index.php?controleur=User&action=demandesActions',str,'callback_removeFromBasket', returnId);
}

ajax.callback_removeFromBasket = function(id){
    console.log("ID: "+id);
    // Suppression du block
    document.getElementById(id).style.display = 'none';

    // Gestion du block parent (Numero de Revue, Article de Revue, ...)
    // Récupération du parent
    parent      = document.getElementById(id).parentNode.id;
    parentCount = document.getElementById("nbre"+parent).value;
    parentCount--;
    // Assignation de la valeur
    document.getElementById("nbre"+parent).value = parentCount;
    // Suppression du block
    if(parentCount == 0) {document.getElementById(parent).style.display = 'none';}

    // Gestion du nombre d'élément total (si = 0, rafraichissement de la page)
    // Calcul du nombre d'article dans la bibliographie
    totalCount = document.getElementById("nbreTotalPanier").value;
    totalCount--;
    // Assignation de la valeur
    document.getElementById("nbreTotalPanier").value = totalCount;

    // Refresh de la page
    if(totalCount == 0) {window.location.reload();}


    // Gestion des tips (achat des numéros)
    var tips_ids = id.split("-");   // ex.: POUR_229-POUR_229_0007  => <div id="POUR_229-POUR_229_0007" class="greybox_hover article">
    var tips_id  = tips_ids[0];
    var tips_n   = $("div[id|='"+tips_id+"']:visible").length;  // Combien de bloc comprenant l'ID existe et sont visible
    if(tips_n == 0) {
        $('buy-numero-'+tips_id).hide();
    }

    // Recalcul du prix
    if(document.getElementById('totalPrice')){
        // Récupération des données et transformation en nombre
        var removePrice = parseFloat(document.getElementById('price-'+id).innerHTML.replace(',', '.'));
        var totalPrice  = parseFloat(document.getElementById('totalPrice').innerHTML.replace(',', '.'));
        console.log(removePrice, totalPrice);
        // Calcul et formatage
        totalPrice -= removePrice;
        totalPrice = totalPrice.toFixed(2).replace(".", ",");
        // Assign
        document.getElementById('totalPrice').innerHTML = totalPrice;
    }
    // Refresh de la page
    if(totalPrice == 0) {window.location.href = 'mon_panier.php';}
}

ajax.addNumeroFromBasket = function(idNumPublie, array) {
    // Init
    var type = "ART";

    // Récupération des articles à supprimer au profis du numéro
    var articles = array.split(',');
    for(i = 0; i < articles.length; i++) {
        // Suppression de l'élément du panier
        var id_article  = articles[i];
        var str         = "todo=remove&type="+type+"&id1="+idNumPublie+"&id2="+id_article+"&id3=";
        this.call('./index.php?controleur=User&action=panierActions',str);
    }

    // Suppression terminée, on ajoute le numéro au panier
    setTimeout(function() { window.location.href = 'mon_panier.php?VERSION=ELEC&ID_NUMPUBLIE='+idNumPublie; }, 500);
}

ajax.alertPopup = function()
{   
    var idRevue         = document.getElementById('email').attributes.revue.value;
    var idUser          = document.getElementById('email').value;
    //var connectFrom     = document.getElementById('alertConnectFrom').value;
    var connectFrom     = "alerte";
    //$.post("./index.php?controleur=Accueil&action=setAlertes",{ID_USER:idUser, ID_ALERTE:idRevue,TYPE:"R"});
    // Gestion des callback
    $.post("./index.php?controleur=Accueil&action=setAlertes",{ID_USER:idUser, ID_ALERTE:idRevue,TYPE:"R"})
        .done(function( data ) {
            // Valeurs du callback (0 = Erreur, 1 = Enregistré, 2 = Déjà enregistré, 'notconnected' = Utilisateur non-connecté)
            var data          = JSON.parse(data);       // Transformation du string en tableau JSON
            var callbackValue = data["has-subscribe"];

            // Préparation de la modal
            $('#div_modal_alert .msg').hide(); // On cache tous les messages pour ne sélectionner que celui nécessaire

            // Erreur (paramètre(s) manquant(s))
            if(callbackValue == 0) { cairn.show_modal('#div_modal_alert'); $('#div_modal_alert .msg-invalide').show(); }
            // Enregistrement effectué
            else if(callbackValue == 1) { cairn.show_modal('#div_modal_alert'); $('#div_modal_alert .msg-valide').show(); }
            // Alerte déjà enregistrée
            else if(callbackValue == 2) { cairn.show_modal('#div_modal_alert'); $('#div_modal_alert .msg-already').show(); }
            // Utilisateur non-connecté
            else { window.location.href = 'connexion.php?from='+connectFrom+'&email='+idUser; }        
        }
    );
}

ajax.connexionAlerte = function(){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    this.call('./index.php?controleur=User&action=login', 'EMAIL=' + email + '&PSW=' + encodeURIComponent(pwd), 'callback_loginAlerte');
}
ajax.callback_loginAlerte = function() {
    //On recharge la page précédente
    setTimeout("window.history.back()",0500);
}

ajax.sendBiblioMail = function()
{
    /*names = document.getElementById('userNames').value;
    biblioList = document.getElementById('biblioList').value;
    mail = document.getElementById('emailUser').value;
    commentaire = document.getElementById('commentaire').value;*/

    // Récupération des valeurs
    var error               = 0; // 0 = Aucune erreur, 1 = Nom ou E-mail manquante, 2 = Aucun destinataire
    var biblioList          = document.getElementById('biblioList').value;
    var site                = document.getElementById('site').value; // cairn.info ou cairn-int.info ?
    // L'utilisateur
    var sendToUser          = document.getElementById('sendToUser').checked;        // true or false
    var nomUser             = document.getElementById('nomUser').value;
    var emailUser           = document.getElementById('emailUser').value;
    // Le destinataire
    var sendToDestination   = document.getElementById('sendToDestination').checked; // true or false
    var nomDestination      = document.getElementById('nomDestination').value;
    var emailDestination    = document.getElementById('emailDestination').value;
    // Le commentaire
    var commentaire         = document.getElementById('commentaire').value;

    // Validation du formulaire
    if((sendToUser === true) && (nomUser == "" || emailUser == "")) {error = 1;}
    if((sendToDestination === true) && (nomDestination == "" || emailDestination == "")) {error = 1;}
    if((sendToUser === false) && (sendToDestination === false)) {error = 2;}

    // Reset des erreurs
    document.getElementById('formSendBiblioMailError1').style.display='none';
    document.getElementById('formSendBiblioMailError2').style.display='none';

    // Définition du suffixe
    var suffixe = "";
    if(site == "cairn-int") {suffixe = "-int";}

    // Exécution
    // Erreur 1
    if(error == 1) {
        document.getElementById('formSendBiblioMailError1').style.display='block';
    }
    // Erreur 2
    else if(error == 2) {
        document.getElementById('formSendBiblioMailError2').style.display='block';
    }
    // Validation
    else {
        // Envoi à l'utilisateur
        if(sendToUser === true) {
            $.get("./Tools/mail-biblio/index"+suffixe+".php", {from_nom:nomUser, from_email:emailUser, from_commentaire:commentaire, to_nom:nomUser, to_email:emailUser, to_user_exist:'1', bibliographie:biblioList});
        }
        // Envoi à destination
        if(sendToDestination === true) {
            // Est-ce que cet utilisateur existe ?
            $.post("./index.php?controleur=User&action=isUserExist",{USERMAIL:emailDestination}, function(response) {
                $.get("./Tools/mail-biblio/index"+suffixe+".php", {from_nom:nomUser, from_email:emailUser, from_commentaire:commentaire, to_nom:nomDestination, to_email:emailDestination, to_user_exist:response, bibliographie:biblioList});
            });
        }
        cairn.close_modal('#modal_mail');
    }
}

ajax.sendPasswordMail = function()
{
    var email = document.getElementById('email').value;
    $.post("./index.php?controleur=User&action=sendPasswordMail",{USERMAIL:email},function(response){
        if(response != 1)
        {

            cairn.show_modal('#modal_mail_success_oblie');
            document.getElementById('email_ok').innerHTML = document.getElementById('email').value;
        }
        else
        {
            cairn.show_modal('#modal_mail_error_oblie');
            document.getElementById('email_ko').innerHTML = document.getElementById('email').value;
        }
    });
}

ajax.saveNewPassword = function(form)
{
    var userMail = document.getElementById('email').value;
    var newPassword = document.getElementById('newPwd').value;
    var confirmNewPassword = document.getElementById('confirmPwd').value;
    var token = getParameterByName('id');

    if(newPassword != confirmNewPassword)
    {
        cairn.show_modal('#modal_pwd_error');
    }
    else
    {
        $.post("./index.php?controleur=User&action=saveNewPassword",{USERMAIL:userMail , PWD:newPassword , TOKEN:token},function(response){
            console.log(response);
            if(response == '0')
            {
                //cairn.show_modal('#modal_pwd_success');
                ajax.goToIndex();
            }
            else
            {
                cairn.show_modal('#modal_token_error');
            }
        });
    }
}

ajax.deleteUserModal = function(type) {

    // Confirmation de la suppression du compte
    if(type == "confirm") {
        // Affichage de l'action en cours
        document.getElementById("delete_user_message").style.display='none';
        document.getElementById("delete_user_in_progress").style.display='block';

        // Suppression du compte
        $.post("./index.php?controleur=User&action=supprimerCompte", "",function(response) {
            console.log(response);
            if(response == '1') {
                // Affichage de l'action en cours
                document.getElementById("delete_user_in_progress").style.display='none';
                document.getElementById("delete_user_valide").style.display='block';
                ajax.goToIndex();
            }
            else {
                // Affichage de l'action en cours
                document.getElementById("delete_user_in_progress").style.display='none';
                document.getElementById("delete_user_error").style.display='block';
                ajax.goToIndex();
            }
        });
    }
    // Affichage du modal
    else {
        cairn.show_modal('#modal_confirm_delete_user');
    }
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}


ajax.checkContactForm = function()
{
    var prenom = document.getElementById('prenom').value;
    var nom = document.getElementById('nom').value;
    var email = document.getElementById('email').value;
    var institution = document.getElementById('institution').value;
    var service = document.getElementById('service').value;
    var message = document.getElementById('message').value;
    var telephone = document.getElementById('telephone').value;
    var captcha = document.getElementById('formug').value;
    var code = document.getElementById('captchaCode').value;
    //var copie = document.getElementById('copie').value;
    var copie = "on";

    var emailSintax = /(([a-zA-Z0-9\-?\.?]+)@(([a-zA-Z0-9\-_]+\.)+)([a-z]{2,}))+$/;

    var captchaEncoded = '';
    jQuery.ajaxSetup({async:false});
    $.post('./public_libs/captcha/shared.php',{code:captcha,async:false},function(response){
        captchaEncoded = response;
    });
    jQuery.ajaxSetup({async:true});
    //if anyone is empty..
    /*if( (prenom == "") || (nom == "") || (email == "") || (service == "") ||(message == "") || (captcha == "") )
    {
        cairn.show_modal('#error_empty_field');
    }*/
    if( (!emailSintax.test(email)) && (email != '') )
    {
        document.getElementById('addr_email').innerHTML = "'" + email + "'";
        cairn.show_modal('#error_invalid_email');
    }
    else if(captchaEncoded != code)
    {
        cairn.show_modal('#error_invalid_captcha');
    }
    else if(service == "") {
        cairn.show_modal('#error_empty_field');
    }
    else
    {
        $.post("./index.php?controleur=Outils&action=sendContactMail",{ prenom:prenom , nom:nom , email:email , institution:institution, service:service , message:message , telephone:telephone, copie:copie },function(response){
            console.log(response);
            if(response == '1')
            {
                cairn.show_modal('#error_envoye');
            }
            else
            {
                //cairn.show_modal('#success_envoye');
                document.location.href="contact-confirm.php";
            }
        });
    }
}

ajax.demandeBiblio = function(){
    var str = "todo=demandeBiblio";
    this.call('./index.php?controleur=User&action=demandesActions',str,'callback_default', 'body-content');
}

ajax.envoiDemandeBiblio = function(){
    if( document.getElementById('MOTIVATION').value == "")
    {
        document.getElementById('MOTIVATION').value = "-";
    }

    var str = "todo=envoiDemandeBiblio&prenom="+document.getElementById('PRENOM').value
                +"&nom="+document.getElementById('NOM').value
                +"&fonction="+document.getElementById('FONCTION').value
                +"&motivation="+document.getElementById('MOTIVATION').value
    this.call('./index.php?controleur=User&action=demandesActions',str,'callback_default', 'body-content');
}

ajax.connexionDemandeBiblio = function(){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    var str = "todo=connect&email_connexion="+email+"&password_connexion="+encodeURIComponent(pwd);
    this.call('./index.php?controleur=User&action=demandesActions',str,'callback_default', 'body-content');

}

ajax.connexionBiblio = function(){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    this.call('./index.php?controleur=User&action=login', 'EMAIL=' + email + '&PSW=' + encodeURIComponent(pwd), 'callback_loginBiblio');
}
ajax.callback_loginBiblio = function() {
    // Récupération des valeurs
    var id_numpublie    = document.getElementById('id_numpublie').value;
    var id_article      = document.getElementById('id_article').value;

    // Exécution
    ajax.addToBiblio(id_numpublie, id_article);

    //On recharge la page
    setTimeout("window.location.reload()",0500);
}

ajax.panierAchat = function(){
    if(document.getElementById('totalPrice')){
        var str = "todo=achat&totalPrice="+document.getElementById('totalPrice').innerHTML;
        if(document.getElementById('tmpCmdIdFrom')){
            str += "&tmpCmdIdFrom="+document.getElementById('tmpCmdIdFrom').value;
        }

        this.call('./index.php?controleur=User&action=panierActions',str,'callback_default', 'body-content');
    }else{
        var str = "todo=achat&tmpCmdId="+document.getElementById('tmpCmdId').value;
        this.call('./index.php?controleur=User&action=panierActions',str,'callback_default', 'body-content');
    }
}

ajax.connexionPanierAchat = function(){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    var totalPrice = document.getElementById('totalPrice').value;
//    var str = "todo=connect&email_connexion="+email+"&password_connexion="+encodeURIComponent(pwd)+"&totalPrice="+totalPrice;
//    this.call('./index.php?controleur=User&action=panierActions',str,'callback_default', 'body-content');
    this.call('./index.php?controleur=User&action=login', 'EMAIL=' + email + '&PSW=' + encodeURIComponent(pwd), 'callback_loginlogout');
}

ajax.connexionAchats = function(){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    var str = "email_connexion="+email+"&password_connexion="+encodeURIComponent(pwd);
    this.call('./index.php?controleur=User&action=subConnect&todo=achats',str,'redirectAchats', 'body-content');
}

ajax.redirectAchats = function(){
	document.location.href="mes_achats.php"
}

ajax.connexionCreditArticle = function(){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    var str = "email_connexion="+email+"&password_connexion="+encodeURIComponent(pwd);
    this.call('./index.php?controleur=User&action=subConnect&todo=credit',str,'callback_default', 'body-content');
}

ajax.connexionCodeAboPapier = function(){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    var str = "email_connexion="+email+"&password_connexion="+encodeURIComponent(pwd);
    this.call('./index.php?controleur=User&action=subConnect&todo=codeAboPapier',str,'callback_loginlogout', 'body-content');
}

ajax.panierStart = function(){
    var livrStr = '';
    if(document.getElementById('checksvgadr')){
        var prenom = document.getElementById('prenom').value;
        var nom = document.getElementById('nom').value;
        var adr = document.getElementById('adr').value;
        var cp = document.getElementById('cp').value;
        var ville = document.getElementById('ville').value;
        var pays = document.getElementById('pays').value;
        var checksvgadr = document.getElementById('checksvgadr').checked;

        if(prenom == '' || nom == '' || adr == ''
                || cp == '' || ville == '' || pays == ''){
            document.getElementById('panierCoordButton').click();
            return;
        }

        livrStr = '&prenom='+prenom+'&nom='+nom+"&adr="+adr+"&cp="+cp
              +"&ville="+ville+"&pays="+pays+"&checksvgadr="+checksvgadr;

    }
    var fact_nom = fact_adr = fact_cp = fact_ville = fact_pays = checksvgfactadr = "";
    if(document.getElementById('checkidemadresse') && document.getElementById('checkidemadresse').checked){
        fact_nom = prenom + " " + nom;
        fact_adr = adr;
        fact_cp = cp;
        fact_ville = ville;
        fact_pays = pays;
        checksvgfactadr = checksvgadr;
    }else{
        fact_nom = document.getElementById('fact_nom').value;
        fact_adr = document.getElementById('fact_adr').value;
        fact_cp = document.getElementById('fact_cp').value;
        fact_ville = document.getElementById('fact_ville').value;
        fact_pays = document.getElementById('fact_pays').value;
        checksvgfactadr = document.getElementById('checksvgfactadr').checked;

        if(fact_adr == '' || fact_cp == '' || fact_ville == '' || fact_pays == ''){
            document.getElementById('panierCoordButton').click();
            return;
        }
    }
    var tmpCmdId = document.getElementById('tmpCmdId').value;

    this.call('./index.php?controleur=User&action=panierActions',
            'todo=start&fact_nom='+fact_nom+"&fact_adr="+fact_adr+"&fact_cp="+fact_cp
              +"&fact_ville="+fact_ville+"&fact_pays="+fact_pays+"&checksvgfactadr="+checksvgfactadr
              +"&tmpCmdId="+tmpCmdId+livrStr
            ,'callback_default', 'body-content');
}

ajax.panierCoord = function(){
    var livrStr = '';
    if(document.getElementById('checksvgadr')){
        var prenom = document.getElementById('prenom').value;
        var nom = document.getElementById('nom').value;
        var adr = document.getElementById('adr').value;
        var cp = document.getElementById('cp').value;
        var ville = document.getElementById('ville').value;
        var pays = document.getElementById('pays').value;
        var checksvgadr = document.getElementById('checksvgadr').checked;

        livrStr = '&prenom='+prenom+'&nom='+nom+"&adr="+adr+"&cp="+cp
              +"&ville="+ville+"&pays="+pays+"&checksvgadr="+checksvgadr;

        if (inputOkEditeur = document.getElementById('ok-editeur')) {
            livrStr += '&ok-editeur=' + document.getElementById('ok-editeur').checked;
        }
    }
    var fact_nom = fact_adr = fact_cp = fact_ville = fact_pays = checksvgfactadr = "";
    if(document.getElementById('checkidemadresse') && document.getElementById('checkidemadresse').checked){
        fact_nom = prenom + " " + nom;
        fact_adr = adr;
        fact_cp = cp;
        fact_ville = ville;
        fact_pays = pays;
        checksvgfactadr = checksvgadr;
    }else{
        fact_nom = document.getElementById('fact_nom').value;
        fact_adr = document.getElementById('fact_adr').value;
        fact_cp = document.getElementById('fact_cp').value;
        fact_ville = document.getElementById('fact_ville').value;
        fact_pays = document.getElementById('fact_pays').value;
        checksvgfactadr = document.getElementById('checksvgfactadr').checked;

        if(fact_adr == '' || fact_cp == '' || fact_ville == '' || fact_pays == ''){
            //document.getElementById('panierCoordButton').click();
            return;
        }
    }
    var tmpCmdId = document.getElementById('tmpCmdId').value;

    this.call('./index.php?controleur=User&action=panierActions',
            'todo=coord&fact_nom='+fact_nom+"&fact_adr="+fact_adr+"&fact_cp="+fact_cp
              +"&fact_ville="+fact_ville+"&fact_pays="+fact_pays+"&checksvgfactadr="+checksvgfactadr
              +"&tmpCmdId="+tmpCmdId+livrStr
            ,'callback_default', 'body-content');
}

ajax.panierCheque = function(tmpCmdId){
    this.call('./index.php?controleur=User&action=panierActions',
            'todo=cheque&tmpCmdId='+tmpCmdId
            ,'callback_default', 'body-content');
}
ajax.panierCredit = function(tmpCmdId){
    this.call('./index.php?controleur=User&action=panierActions',
            'todo=credit&tmpCmdId='+tmpCmdId
            ,'callback_default', 'body-content');
}

ajax.promotion = function(str){
    this.call('./static/includes/ajax/promotion.php',str);
}

ajax.mergePanier = function(str){
    cairn.close_modal();
    this.call('./index.php?controleur=User&action=panierActions',
            'todo=merge','callback_default', 'body-content');
}
ajax.erasePanier = function(str){
    cairn.close_modal();
    var totalPrice = document.getElementById('totalPrice').value;
    this.call('./index.php?controleur=User&action=panierActions',
            'todo=erase&totalPrice='+totalPrice,'callback_default', 'body-content');
}
ajax.mergeDemande = function(str){
    cairn.close_modal();
    this.call('./index.php?controleur=User&action=demandesActions',
            'todo=merge','callback_default', 'body-content');
}
ajax.eraseDemande = function(str){
    cairn.close_modal();
    this.call('./index.php?controleur=User&action=demandesActions',
            'todo=erase','callback_default', 'body-content');
}

ajax.connexionRouteur = function(redirect){
    var email = document.getElementById('email_connexion').value;
    var pwd = document.getElementById('password_connexion').value;
    var str = "email_connexion="+email+"&password_connexion="+encodeURIComponent(pwd);
    this.call('./index.php?controleur=User&action=connexion',str,'callback_connexionRouteur', redirect);
}

ajax.callback_connexionRouteur = function(redirect){
    window.location.href = redirect;
}


ajax.removeNetworkAddress = function(user,_id){
    $.post("./index.php?controleur=Admin&action=removeNetworkAddress",{ id:_id },function(){
        window.location.href = 'gestion_utilisateurs.php?id_user=' + encodeURIComponent(user);
    });
}

ajax.editModalNetworkAddress = function(user,id,adress,mask){
    $('#user_selected').val(user.toString());
    $('#ip_selected').val(id.toString());
    $('#edit-addess').val(adress.toString());
    $('#edit-mask').val(mask.toString());

    cairn.show_modal("#edit_address_modal");
}

ajax.editNetworkAddress = function(){
    user = $('#user_selected').val();
    line = $('#ip_selected').val();
    _address = $('#edit-addess').val();
    _mask = $('#edit-mask').val();

    if(_mask.indexOf("255.") != 0){
        alert('invalid mask');
    }else{
        $.post("./index.php?controleur=Admin&action=editNetworkAddress",{ edit_id:line, address:_address, mask:_mask },function(){
            cairn.close_modal();
            window.location.href = 'gestion_utilisateurs.php?id_user=' + encodeURIComponent(user);
        });
    }
}

ajax.addNetworkAddress = function(){
    _user = $('#new_user_selected').val();
    _idAbonnes = $('#new_id_selected').val();
    _address = $('#new-addess').val();
    _mask = $('#new-mask').val();

    if(_mask.indexOf("255.") != 0){
        alert('invalid mask');
    }else{
        $.post("./index.php?controleur=Admin&action=addNetworkAddress",{ idAbonnes:_idAbonnes, address:_address, mask:_mask },function(){
            cairn.close_modal();
            window.location.href = 'gestion_utilisateurs.php?id_user=' + encodeURIComponent(_user);
        });
    }
}

ajax.addCairnParams = function()
{
    _param = $('select#params_select').val();
    _value = $('.choice_value:visible').val();
    _user = $('input#user_params').val();

    $.post("./index.php?controleur=Admin&action=addCairnParams",{ param:_param, value:_value, user:_user },function(){
        cairn.close_modal();
        window.location.href = 'gestion_utilisateurs.php?id_user=' + encodeURIComponent(_user);
    });
}

ajax.removeCairnParam = function(_type,_value,_user){
    $.post("./index.php?controleur=Admin&action=removeCairnParam",{ type:_type , value:_value , user:_user },function(){
        window.location.href = 'gestion_utilisateurs.php?id_user=' + encodeURIComponent(_user);
    });
}

ajax.loginsso = function(){
    var pays = document.getElementById('pays').value;
    var idp = document.getElementById('etabl'+pays).value;
    $.post("./index.php?controleur=User&action=setSSO",{ idp:idp },function(){
        var pays = document.getElementById('pays').value;
        var idp = document.getElementById('etabl'+pays).value;

        var baseUrl = document.getElementById('baseUrl').value;
        var target = document.getElementById('targetUrl').value;

        var url = "";
        /*if(baseUrl.indexOf("check.pythagoria.com") > 0){
            url = baseUrl;
            url += "&entityID=https%3A%2F%2Ftest.federation.renater.fr%2Fidp%2Fshibboleth";
            url += "&target="+target;
        }else{*/
            url += idp;
        //}

        window.location.href = url;
    });

}

ajax.changeMaxSessions = function(){
    var newMax = document.getElementById('changeMaxSessions').value;
    var user = document.getElementById('id_user').value;
    $.post("./index.php?controleur=Admin&action=changeMaxSessions",{ newMax:newMax, user:user },function(){
        window.location.reload();
    });

}

ajax.changeMaxSessionsIP = function(){
    var newMax = document.getElementById('changeMaxSessionsIP').value;
    var user = document.getElementById('id_user').value;
    $.post("./index.php?controleur=Admin&action=changeMaxSessionsIP",{ newMax:newMax, user:user },function(){
        window.location.reload();
    });
}

ajax.clearRechConfig = function(){
    $.post("./evidensseConfigurator/ajax/saveConfig.php",null,function(){
        window.location.reload();
    });
}

ajax.loadAccessIntoCache = function(idUser){

    //ajax.call("/index.php?controleur=User&action=generateAccessCache","idUser="+idUser);
}

ajax.generateAccessCache = function(){
    ajax.call("/index.php?controleur=User&action=generateAccessCacheService");
}

ajax.updateMode = function(mode){
    ajax.call("/index.php?controleur=User&action=updateSearchMode",'mode='+mode);
}


ajax.sendFeedback = function() {
    $('#modal_feedback').hide();
    // Admirez mon super système anti-spam
    var datas = $('#send_feedback').serialize();
    datas += '&f_i-am-not-a-robot=i-am-a-castor-sauvage';
    datas += '&f_url=' + encodeURIComponent(window.location);
    $.post("./index.php?controleur=Outils&action=sendFeedbackMail", datas)
    .done(function(data) {
        cairn.show_modal("#modal-success-feedback");
    })
    .fail(function() {
        cairn.show_modal("#modal-success-feedback");
        // TODO: c'est crade, mais pas le temps de faire dans le détail. On récupère l'erreur coté serveur de toute manière, sauf si problème http (on croise les doigts...)
        console.log('Error on sending feedback', arguments);
    });
    return false;
}

ajax.alertSample = function(idNumPublie) {

    // Définition de la source de la frame
    //src = "http://dedi.cairn.info/NL/exemple_NL.php?ID_NUMPUBLIE="+idNumPublie;
    src = "Tools/alertes/?id_numpublie="+idNumPublie;
    document.getElementById('alert_iframe').src=src;

    // Ouverture de la modal
    cairn.show_modal('#modal_alerte_email_exemple');
}

ajax.paiementOgone = function(tmpCmdId) {
    //Récupération des informations manquantes sur l'article.
    $.ajax({ type: "POST",
            url: "index.php?controleur=User&action=commandeToBO",
            data: {tmpCmdId: tmpCmdId},
            async: false
            });

    $('#ogone').submit();
}

// L'utilisateur suggère un ouvrage à son bibliothécaire (seulement en institution)
ajax.postSuggestionOuvragePourInstitution = function($idNumpublie) {
    $.post('index.php?controleur=User&action=suggererOuvragePourInstitution', {ID_NUMPUBLIE: $idNumpublie})
    .done(function() {
        cairn.show_modal('#suggest-ouvrage-institution-modal');
        var currentDiv = document.getElementById('suggest-ouvrage-institution');
        currentDiv.parentNode.removeChild(currentDiv);
    });
}
