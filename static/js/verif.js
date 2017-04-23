var redirectPost = function(redirectUrl,idUser,idAlert) {
    var form = $('<form action="' + redirectUrl + '" method="post">' +
                    '<input type="hidden" name="ID_USER" value="' + idUser + '" />' +
                '</form>');
    $('body').append(form);
    $(form).submit();
};

function VerifMailnews(formulaire)
{
    var mail = formulaire.email.value;
    if (mail == "")
    {
        cairn.show_modal('#error_modal_fillMail');
        return(false);
    }

    var badcar = '[\{-ÿ]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(mail))
    {
        cairn.show_modal('#error_modal_accentedMail');
        return(false);
    }
    var goodcar = '^(.+)[\@-\@](.+)$';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        cairn.show_modal('#error_modal_signMail');
        return(false);
    }
    var goodcar = '[\.-\.]+';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        cairn.show_modal('#error_modal_dotMail');
        return(false);
    }

    $.post("./static/includes/phplist/subscribe.php",{email:mail},function(response){
        //console.log(response);
        document.getElementById('confirm_text').style.display = 'block';
        document.getElementById('breadcrump_conf').style.display = 'block';
        document.getElementById('free_text').style.display = 'none';
        document.getElementById('breadcrump_main').style.display = 'none';
    });
    //formulaire.action = "./index.php?controleur=Accueil&action=setAlertes";
    //formulaire.submit();
    return(true);
}

function VerifMailrev(formulaire)
{
    mail = formulaire.email.value;
    valrevue = formulaire.ID_REVUE.value.toString();

    if (mail == "") {
        cairn.show_modal('#error_modal_fillMail');
        return(false);
    }

    var badcar = '[\{-ÿ]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(mail))
    {
        cairn.show_modal('#error_modal_accentedMail');
        return(false);
    }
    var goodcar = '^(.+)[\@-\@](.+)$';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        cairn.show_modal('#error_modal_signMail');
        return(false);
    }
    var goodcar = '[\.-\.]+';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        cairn.show_modal('#error_modal_dotMail');
        return(false);
    }

    if( $('input#session').val() ){

        if( !document.getElementById("" + valrevue + "") )
        {
            $.post("./index.php?controleur=Accueil&action=numPublie",{id_revue:valrevue},function(response)
            {
                $('#revues-list').prepend(
                '<div id="'+ valrevue +'" class="article greybox_hover">'
                    +'<a href="revue-'+ $(formulaire.ID_REVUE).children(":selected").attr('url') +'.htm"> <img src="http://www.cairn.info/vign_rev/'+valrevue+'/'+ response.ID_NUMPUBLIE +'_L204.jpg" alt="" class="small_cover"></a>'
                    +'<div class="meta">'
                        +'<div class="revue_title">'
                                +'<h2 class="title_little_blue numero_title">'
                                    +'<a href="revue-'+ $(formulaire.ID_REVUE).children(":selected").attr('url') +'.htm">'+ $(formulaire.ID_REVUE).children(":selected").text() +'</a>'
                                +'</h2>'
                                +'<div class="editeur">'+ response.NOM_EDITEUR +'</div>'
                        +'</div>'
                        +'<div class="state">'
                            +'<img class="right" type="image" src="http://cairn.info/img/del.png" onclick="removeAlert(' + valrevue + ');" alt="Supprimer l\'alerte sur la revue « '+ $(formulaire.ID_REVUE).children(":selected").text() +' »">'
                        +'</div>'
                    +'</div>'
                +'</div>'
                );
            },'json');

            ajax.call("./index.php?controleur=Accueil&action=setAlertes" , "ID_USER=" + mail + "&ID_ALERTE=" + valrevue + "&TYPE=R");
        }

    } else {
        //ajax.call("./index.php?controleur=Accueil&action=setAlertes" , "ID_USER=" + mail + "&ID_ALERTE=" + valrevue + "&TYPE=R");
        //window.location.href = "./revue-" + $(formulaire.ID_REVUE).children(":selected").attr('url') + ".htm";
        redirectPost("./revue-" + $(formulaire.ID_REVUE).children(":selected").attr('url') + ".htm" , mail );
    }

    return(true);
}


function VerifMailcoll(formulaire)
{
    mail = formulaire.email.value;
    valrevue = formulaire.ID_COLL.value;

    if (mail == "") {
        cairn.show_modal('#error_modal_fillMail');
        return(false);
    }

    var badcar = '[\{-ÿ]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(mail))
    {
        cairn.show_modal('#error_modal_accentedMail');
        return(false);
    }
    var goodcar = '^(.+)[\@-\@](.+)$';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        cairn.show_modal('#error_modal_signMail');
        return(false);
    }
    var goodcar = '[\.-\.]+';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        cairn.show_modal('#error_modal_dotMail');
        return(false);
    }

    if( $('input#session').val() ){

        if( !document.getElementById(""+valrevue+"") )
        {
            $.post("./index.php?controleur=Accueil&action=numPublie",{id_revue:valrevue},function(response)
            {
                $('#collections-list').append(
                '<div id="'+valrevue+'" class="article greybox_hover">'
                    +'<a href="revue-'+ $(formulaire.ID_COLL).children(":selected").attr('url') +'.htm"> <img src="http://www.cairn.info/vign_rev/'+valrevue+'/'+ response.ID_NUMPUBLIE +'_L204.jpg" alt="" class="small_cover"></a>'
                    +'<div class="meta">'
                        +'<div class="revue_title">'
                                +'<h2 class="title_little_blue numero_title">'
                                    +'<a href="revue-'+ $(formulaire.ID_COLL).children(":selected").attr('url') +'.htm">'+ $(formulaire.ID_COLL).children(":selected").text() +'</a>'
                                +'</h2>'
                                +'<div class="editeur">'+ response.NOM_EDITEUR +'</div>'
                        +'</div>'
                        +'<div class="state">'
                            +'<img class="right" type="image" src="http://cairn.info/img/del.png" onclick="removeAlert('+valrevue+')" alt="Supprimer l\'alerte sur la revue « '+ $(formulaire.ID_REVUE).children(":selected").text() +' »">'
                        +'</div>'
                    +'</div>'
                +'</div>'
                );
            },'json');

            ajax.call("./index.php?controleur=Accueil&action=setAlertes" , "ID_USER=" + mail + "&ID_ALERTE=" + valrevue + "&TYPE=R");
        }

    } else {
        //ajax.call("./index.php?controleur=Accueil&action=setAlertes" , "ID_USER=" + mail + "&ID_ALERTE=" + valrevue + "&TYPE=R");
        //window.location.href = "./collection-" + $(formulaire.ID_COLL).children(":selected").attr('url') + ".htm";
        redirectPost("./collection-" + $(formulaire.ID_COLL).children(":selected").attr('url') + ".htm" , mail );
    }
    return(true);
}

function removeAlert(revue)
{
    $.post("./index.php?controleur=Accueil&action=deleteAlert" , {id_user: $('input#session').val() , id_alerte:revue.id},function(){
        revue.outerHTML = "";
        delete revue;
    });
    return true;
}


function VerifForm(formulaire)
{
    prenom = formulaire.prenom.value;
    nom = formulaire.nom.value;
    mail = formulaire.mail.value;
    identi = formulaire.identi.value;
    mdp = formulaire.mdp.value;
    cmdp = formulaire.cmdp.value;

    if ((prenom == "") || (nom == "") || (nom == "") || (mail == "") || (identi == "") || (mdp == "") || (cmdp == ""))
    {
        document.getElementById('txterr').innerHTML = 'Certains champs obligatoires ne sont pas renseignés.<br />Merci de compléter.';
        if (prenom == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('prenom1').style.color = '#990000';
        } else {
            document.getElementById('prenom1').style.color = '#5E6A59';
        }

        if (nom == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('nom1').style.color = '#990000';
        } else {
            document.getElementById('nom1').style.color = '#5E6A59';
        }

        if (mail == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('mail1').style.color = '#990000';
        } else {
            document.getElementById('mail1').style.color = '#5E6A59';
        }

        if (identi == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('identi1').style.color = '#990000';
        } else {
            document.getElementById('identi1').style.color = '#5E6A59';
        }

        if (mdp == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('mdp1').style.color = '#990000';
        } else {
            document.getElementById('mdp1').style.color = '#5E6A59';
        }

        if (cmdp == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('cmdp1').style.color = '#990000';
        } else {
            document.getElementById('cmdp1').style.color = '#5E6A59';
        }
        return(false);
    }
// VERIF IDENT
    var badcar = '[\{-ÿ]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(identi))
    {
        document.getElementById('txterr').innerHTML = 'Votre identifiant contient des caractères accentués.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }
    var badcar = '[\:-\@]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(identi))
    {
        document.getElementById('txterr').innerHTML = 'Votre identifiant contient des caractères de ponctuation.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }
    var badcar = '([\b-\/]|[\;-\?])+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(identi))
    {
        document.getElementById('txterr').innerHTML = 'Votre identifiant contient des espaces ou des caractères de ponctuation.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }
// EMAIL
    /*var badcar = '[\b-\b]+';
     var bad=new RegExp(badcar,"gi");
     if (bad.test(mail))
     {
     document.getElementById('txterr').innerHTML = 'Votre email contient un ou plusieurs espaces.<br />Merci de modifier.'
     document.getElementById('error').style.display = 'block';
     document.getElementById('mail1').style.color = '#990000';
     return(false);
     }*/

    var badcar = '[\{-ÿ]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(mail))
    {
        document.getElementById('txterr').innerHTML = 'Votre email contient des caractères accentués.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mail1').style.color = '#990000';
        return(false);
    }
    var goodcar = '^(.+)[\@-\@](.+)$';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        document.getElementById('txterr').innerHTML = 'Votre email semble incorrect. Problème d&rsquo;arobase (@).<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mail1').style.color = '#990000';
        return(false);
    }
    var goodcar = '[\.-\.]+';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        document.getElementById('txterr').innerHTML = 'Votre email contient ne contient pas de point (.).<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mail1').style.color = '#990000';
        return(false);
    }


// VERIF NOMBRE CARACTERE IDENT
    var length = identi.length;
    if (length < 4) {
        document.getElementById('txterr').innerHTML = 'Votre identifiant doit comporter au moins 4 caractères.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }
// VERIF NOMBRE CARACTERE MDP
    var length = mdp.length;
    if (length < 4) {
        document.getElementById('txterr').innerHTML = 'Votre mot de passe doit comporter au moins 4 caractères.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mdp').style.color = '#990000';
        return(false);
    }

    if (mdp !== cmdp) {
        document.getElementById('txterr').innerHTML = 'Votre mot de passe est différent de votre confirmation.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mdp').style.color = '#990000';
        return(false);
    }

    formulaire.submit();
    document.getElementById('error').style.display = 'none';
    return(true);

}


function VerifFormAd(formulaire)
{
    prenom = formulaire.prenom.value;
    nom = formulaire.nom.value;
    mail = formulaire.mail.value;
    identi = formulaire.identi.value;
    mdp = formulaire.mdp.value;
    cmdp = formulaire.cmdp.value;
    adresse = formulaire.adresse.value;
    cp = formulaire.cp.value;
    ville = formulaire.ville.value;
    pays = formulaire.pays.value;

    if ((prenom == "") || (nom == "") || (mail == "") || (identi == "") || (mdp == "") || (cmdp == "") || (adresse == "") || (cp == "") || (ville == "") || (pays == ""))
    {
        document.getElementById('txterr').innerHTML = 'Certains champs obligatoires ne sont pas renseignés.<br />Merci de compléter.';
        if (prenom == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('prenom1').style.color = '#990000';
        } else {
            document.getElementById('prenom1').style.color = '#5E6A59';
        }

        if (nom == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('nom1').style.color = '#990000';
        } else {
            document.getElementById('nom1').style.color = '#5E6A59';
        }

        if (mail == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('mail1').style.color = '#990000';
        } else {
            document.getElementById('mail1').style.color = '#5E6A59';
        }

        if (identi == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('identi1').style.color = '#990000';
        } else {
            document.getElementById('identi1').style.color = '#5E6A59';
        }

        if (mdp == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('mdp1').style.color = '#990000';
        } else {
            document.getElementById('mdp1').style.color = '#5E6A59';
        }

        if (cmdp == "") {
            document.getElementById('error').style.display = 'block';
            document.getElementById('cmdp1').style.color = '#990000';
        } else {
            document.getElementById('cmdp1').style.color = '#5E6A59';
        }
        return(false);
    }
// VERIF IDENT
    var badcar = '[\{-ÿ]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(identi))
    {
        document.getElementById('txterr').innerHTML = 'Votre identifiant contient des caractères accentués.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }
    var badcar = '[\:-\@]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(identi))
    {
        document.getElementById('txterr').innerHTML = 'Votre identifiant contient des caractères de ponctuation.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }
    var badcar = '([\b-\/]|[\;-\?])+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(identi))
    {
        document.getElementById('txterr').innerHTML = 'Votre identifiant contient des espaces ou des caractères de ponctuation.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }

    var badcar = '[\{-ÿ]+';
    var bad = new RegExp(badcar, "gi");
    if (bad.test(mail))
    {
        document.getElementById('txterr').innerHTML = 'Votre email contient des caractères accentués.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mail1').style.color = '#990000';
        return(false);
    }
    var goodcar = '^(.+)[\@-\@](.+)$';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        document.getElementById('txterr').innerHTML = 'Votre email semble incorrect. Problème d&rsquo;arobase (@).<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mail1').style.color = '#990000';
        return(false);
    }
    var goodcar = '[\.-\.]+';
    var good = new RegExp(goodcar, "gi");
    if (!good.test(mail))
    {
        document.getElementById('txterr').innerHTML = 'Votre email contient ne contient pas de point (.).<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mail1').style.color = '#990000';
        return(false);
    }

// VERIF NOMBRE CARACTERE IDENT
    var length = identi.length;
    if (length < 4) {
        document.getElementById('txterr').innerHTML = 'Votre identifiant doit comporter au moins 4 caractères.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('identi1').style.color = '#990000';
        return(false);
    }
// VERIF NOMBRE CARACTERE MDP
    var length = mdp.length;
    if (length < 4) {
        document.getElementById('txterr').innerHTML = 'Votre mot de passe doit comporter au moins 4 caractères.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mdp').style.color = '#990000';
        return(false);
    }

    if (mdp !== cmdp) {
        document.getElementById('txterr').innerHTML = 'Votre mot de passe est différent de votre confirmation.<br />Merci de modifier.'
        document.getElementById('error').style.display = 'block';
        document.getElementById('mdp').style.color = '#990000';
        return(false);
    }

    formulaire.submit();
    document.getElementById('error_div').style.display = 'none';
    return(true);

}

// Validation du formulaire AVANT checkout (voir Vue/User/panierPaiement.php)
function formCheckoutValidation(event, lang) {

    // Traduction
    var texte = {"textfr" : "Vous devez accepter les Conditions Générales de vente.", "texten" : "You must accept the general conditions of sale."}

    // Désactivation du comportement
    event.preventDefault ? event.preventDefault() : (event.returnValue = false);

    // Vérification de la checkbox (true | false)
    var status = document.getElementById('checkout-cgv-acceptation-status').checked;
    
    // Le formulaire est valide
    if(status === true) {
        document.getElementById('ogone').submit();
    }
    // L'utilisateur n'a pas lu et accepté les CGV
    else {
        document.getElementById('checkout-cgv-acceptation-error').innerHTML = '<b>'+texte['text'+lang]+'</b>';
        document.getElementById('checkout-cgv-acceptation-error').style.display = 'block';
        document.getElementById('checkout-cgv-acceptation-error').style.color = '#990000';
        return false;
    }    
}










