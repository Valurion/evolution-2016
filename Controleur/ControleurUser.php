<?php

/**
 * CONTROLER - Control the navigation for consultation pages
 * @author ©Pythagoria - www.pythagoria.com
 * @author benjamin
 */
require_once 'Framework/Controleur.php';

require_once 'Modele/ContentCom.php';
require_once 'Modele/ContentComMongo.php';
require_once 'Modele/ManagerComMongo.php';
require_once 'Modele/ManagerCom.php';
require_once 'Modele/Content.php';
require_once 'Modele/DefaultFile.php';
require_once 'Modele/Manager.php';
require_once 'Modele/ManagerAbo.php';
require_once 'Modele/RedisClient.php';
require_once 'Modele/ContentEvidensse.php';
require_once 'Modele/Filter.php';

class ControleurUser extends Controleur {

    //S'occupe des select sur la base commerciale
    private $contentCom;
    private $contentComMongo;
    //S'occupe des insert/update sur la base commerciale
    private $managerCom;
    private $managerComMongo;
    //S'occupe des select sur la base pub
    private $content;
    //S'occupe des insert/update sur la base pub
    private $manager;
    //S'occupe des insert/update sur la base abonnes
    private $managerAbo;
    //S'occupe des select sur la base evidensse
    private $contentEvidensse;

    private $file;

    private $redis = null;
    private $filter = null;

    public function __construct() {
        $this->content = new Content();
        $this->manager = new Manager();
        $this->contentCom = new ContentCom('dsn_com');
        $this->contentComMongo = new ContentComMongo(Configuration::get('dsn_com_mongo'));
        $this->managerCom = new ManagerCom('dsn_com');
        $this->managerComMongo = new ManagerComMongo(Configuration::get('dsn_com_mongo'));
        $this->managerAbo = new ManagerAbo('dsn_abo');
        $this->contentEvidensse = new contentEvidensse('dsn_evidensse');
        $this->file = new DefaultFile();
        $this->redis = new RedisClient(Configuration::get('redis_db_user'));
        $this->filter = new Filter();
    }

    public function index() {
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-mcpt', $this->authInfos);
        // = page mon compte
        if (!isset($this->authInfos["U"])) {
            $disciplines = $this->content->getDisciplines(null, 1);
            //Si on n'est pas connecté particulier, on renvoie sur la page "creer_compte"
            $this->genererVue(array('disciplines'=>$disciplines), 'creerCompte.php', null, $headers);
            return;
        }

        if ($this->requete->existeParametre("nom")) {
            $nom = $this->requete->getParametre("nom");
            $prenom = $this->requete->getParametre("prenom");
            $activity = $this->requete->getParametre("activity");
            $pos_disc = $this->requete->getParametre("pos_disc");
            $pays = $this->requete->getParametre("pays");
            $checkpartenaires = 'off';
            if ($this->requete->existeParametre("checkpartenaires")) {
                $checkpartenaires = $this->requete->getParametre("checkpartenaires");
            }
            $showall = 0;
            if ($this->requete->existeParametre("checkshowall")) {
                $showall = 1;
            }
            $userOk = $this->managerCom->updateAccount(array($nom, $prenom, $pos_disc, $activity, $pays, $showall, $this->authInfos["U"]["ID_USER"]));
            if ($userOk) {
                $alertP = $this->content->getAlertes($this->authInfos["U"]["EMAIL"], 'P');
                switch ($checkpartenaires) {
                    case 'on' :
                        if (empty($alertP))
                            $alertOk = $this->manager->insertAlertePartenaire($this->authInfos["U"]["ID_USER"]);
                        break;
                    case 'off' :
                        if (!empty($alertP))
                            $alertOk = $this->manager->removeAlertePartenaire($this->authInfos["U"]["ID_USER"]);
                        break;
                }
                //TODO : que faire avec le codepromo ???
                //TODO : gérer un retour d'erreur/d'exception des managers
                //On ne touche pas au cookie / login, mais on met à jour $authInfos["U"] pour répercuter de suite une modif de nom/prenom
                $authInfos["U"]["NOM"] = $nom;
                $authInfos["U"]["PRENOM"] = $prenom;

                // Modification Webtrends
                $headers[3]["attributes"][1]["value"] = "mon compte";
                $headers[4]["attributes"][1]["value"] = "mon compte (modification réussie)";

                //Puis on affiche le message de confirmation
                $this->genererVue(null, 'confirmationUpdate.php', null, $headers);
            }else {
                //$this->genererVue(null, 'erreurInconnue.php', 'gabaritAjax.php');
            }
        } else {
            //appel initial
            // Récupération des données (PAYS & DISCIPLINES)
            // Récupération des pays et repositionnement
            $listePays = $this->content->getListePays();
            array_unshift($listePays, "------------");                               // Ajout du séparateur
            array_unshift($listePays, "Canada");                                     // Ajout du pays CA
            array_unshift($listePays, "Suisse");                                     // Ajout du pays CH
            array_unshift($listePays, "France");                                     // Ajout du pays FR
            array_unshift($listePays, "Belgique");                                   // Ajout du pays BE
            array_unshift($listePays, "-nc-");                                       // Ajout du pays NC

            $disciplines = $this->content->getDisciplines(null, 1);
            $userActivities = $this->content->getProfessionList();

            $alerte = $this->content->getAlertes($this->authInfos["U"]["ID_USER"], 'P');
            $this->genererVue(array('disciplines' => $disciplines, 'listePays' => $listePays, 'userActivities' => $userActivities, 'alerte' => $alerte), 'monCompte.php', null, $headers);
        }
    }

    // Enregistre un cookie pour les utilisateurs non-francophone
    public function cairnIntInfoVisitorCookie() {
        // Le service AUTHENTIFICATION est exécuté partout.
        $this->authInfos = Service::get('Authentification')->UserCainIntVuCookie();
    }

    // Enregistre un cookie pour les utilisateurs non-francophone
    public function CreditArticleAlertCookie() {
        // Le service AUTHENTIFICATION est exécuté partout.
        $todo = $this->requete->getParametre("todo");
        $this->authInfos = Service::get('Authentification')->UserCreditArticleAlertCookie($todo);
    }

    public function connexion() {
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*', $this->authInfos);
        if ($this->requete->existeParametre("email_connexion")) {
            $email = $this->requete->getParametre("email_connexion");
            $password = $this->requete->getParametre("password_connexion");

            if(trim($password) == ''){
                $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');

            }else {

                $ret = $this->checkLogin($email, $password);
                switch ($ret) {
                    case 1:
                        //pas de retour, redirection côté ajax
                        $this->genererVue(null,'', 'gabaritAjax.php');
                        break;
                    case -1:
                        $this->genererVue(array('error_num' => 1), 'bademail.php', 'gabaritAjax.php');
                        break;
                    case -2:
                        $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');
                        break;
                    case -3:
                        $this->genererVue(array('error_num' => 3), 'bademail.php', 'gabaritAjax.php');
                        break;
                }
            }
        } else {
            if(($this->requete->existeParametre("connectFrom")) || ($this->requete->existeParametre("from"))) {
                // Récupération de la valeur de redirection
                if($this->requete->existeParametre("connectFrom")) {$connectFrom = $this->requete->getParametre("connectFrom");}
                if($this->requete->existeParametre("from")) {$connectFrom = $this->requete->getParametre("from");}                
                // Récupération des valeurs complémentaires (option)
                if($this->requete->existeParametre("email")) { $email = $this->requete->getParametre("email"); } else {$email = "";}
                // Paramétrage de la vue
                $this->genererVue(array('restricted' => 'oui', 'connectFrom'=>$connectFrom, 'email' => $email), 'connect.php', null, $headers);
            }else{
                $this->genererVue(null, 'connexion.php', null, $headers);
            }
        }
    }

    public function connectBefore(){
        $connectFrom = $this->requete->getParametre("connectFrom");
        $fromString = "";
        if($this->requete->existeParametre("fromString")){
            $fromString = $this->requete->getParametre("fromString");
        }
        $this->genererVue(array('connectFrom'=>$connectFrom, 'fromString'=>$fromString, 'email' => $this->requete->existeParametre('USER')?$this->requete->getParametre('USER'):''), 'connexion.php');
    }

    public function login() {
        $email = $this->requete->getParametre("EMAIL");
        $password = $this->requete->getParametre("PSW");

        if(trim($password) == ''){
            $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');
        }else {

            $ret = $this->checkLogin($email, $password);
            switch ($ret) {
                case 1:
                    $this->genererVue(null, 'logas.php', 'gabaritAjax.php');
                    $this->updateSuggestionsOuvragePourInstitution();
                    break;
                case -1:
                    $this->genererVue(array('error_num' => 1), 'bademail.php', 'gabaritAjax.php');
                    break;
                case -2:
                    $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');
                    break;
                case -3:
                    $this->genererVue(array('error_num' => 3), 'bademail.php', 'gabaritAjax.php');
                    break;
            }
        }
    }

    private function checkLogin($email, $password) {
        $user = $this->contentCom->validateLogin($email, $password);
        if ($user !== FALSE) {
            $userId = $user['ID_USER'];
            $type = $user['TYPE'];
            if($type == 'U'){

                $session = $this->contentComMongo->validateSession($email, $this->authInfos["G"]["TOKEN"],
                        $type=='U'?Configuration::get('userInactivityDuration'):Configuration::get('userInstInactivityDuration'),
                        $type=='U'?Configuration::get('userInactivityUnit'):Configuration::get('userInstInactivityUnit'));

                if($session !== FALSE){
                    //On éjecte l'autre
                    $this->managerComMongo->closeOtherUserLog($userId,$this->authInfos["G"]["TOKEN"]);
                }
            }
            $this->doLogin($userId, $type);

            return 1;
        } else {
            $user = $this->contentCom->validateEmail($email);
            if ($user === FALSE) {
                return -1;
            } else {
                return -2;
            }
        }
    }

    private function doLogin($userId, $type) {
        if (Service::get('Authentification')->getToken() != null) {
            $token = Service::get('Authentification')->updateToken($userId, $type);
        } else {
            $token = Service::get('Authentification')->createToken($userId);
        }
        $this->authInfos = Service::get('Authentification')->readToken($this->requete,0);

        if(isset($this->authInfos['I']) && $this->authInfos['I']['ID_USER'] == $userId){
            setcookie('cairn_token', $token, strtotime('+'.Configuration::get('userInstSessionDuration').' '.strtolower(Configuration::get('userInstSessionUnit')).'s'));

            //$this->managerCom->insertUserLog(Service::get('Authentification')->getUserLogId('userILog'),$userId,
            /*$this->managerComMongo->insertUserLog($this->authInfos["G"]['TOKEN'],null,
                    Configuration::get('userInstSessionDuration'),
                    Configuration::get('userInstSessionUnit'));*/
            $this->managerComMongo->insertInstLog($this->authInfos["G"]['TOKEN'],
                    Configuration::get('userSessionDuration'),
                    Configuration::get('userSessionUnit'),
                    (isset($this->authInfos["G"]["SESSION_INST_FIRST"])&& $this->authInfos["G"]["SESSION_INST_FIRST"] != '')?0:1);
            $this->managerComMongo->incrementInstitutionCounter($this->authInfos["G"]['TOKEN'], $userId);
        }else{
            //Gestion du "remember"
            $duration = Configuration::get('userSessionDuration');
            $unit = Configuration::get('userSessionUnit');
            $touchInterval = Configuration::get('userInactivityDuration')." ".Configuration::get('userInactivityUnit');
            if($this->requete->existeParametre('remember') && $this->requete->getParametre('remember') == "true"){
                $duration = Configuration::get('userSessionRememberDuration');
                $unit = Configuration::get('userSessionRememberUnit');
                $touchInterval = Configuration::get('userRememberInactivityDuration')." ".Configuration::get('userRememberInactivityUnit');
            }

            setcookie('cairn_token', $token, strtotime('+'.$duration.' '.strtolower($unit).'s'));
            //$this->managerCom->insertUserLog(Service::get('Authentification')->getUserLogId('userULog'),$userId);
            $this->managerComMongo->insertUserLog($this->authInfos["G"]['TOKEN'],$userId,
                    $duration,
                    $unit,
                    (isset($this->authInfos["G"]["SESSION_USER_FIRST"])&& $this->authInfos["G"]["SESSION_USER_FIRST"] != '')?0:1,
                    $touchInterval);
        }
    }

    public function loginCors() {
        $email = $this->requete->getParametre("email");
        $token = $this->requete->getParametre("token");
        $status = 0;

        $localAuthInfos = Service::get('Authentification')
                ->setToken($token)
                ->readToken($this->requete);

        if(isset($localAuthInfos["U"]) && $localAuthInfos["U"]["ID_USER"] == $email){
            setcookie('cairn_token', $token, strtotime('+'.Configuration::get('userSessionDuration').' '.strtolower(Configuration::get('userSessionUnit')).'s'));
            $status=1;
        }else if(isset($localAuthInfos["I"]) && $localAuthInfos["I"]["ID_USER"] == $email){
            setcookie('cairn_token', $token, strtotime('+'.Configuration::get('userInstSessionDuration').' '.strtolower(Configuration::get('userInstSessionUnit')).'s'));
            $status=1;
        }
        $this->genererVue(array("status" => $status), null, 'gabaritAjax.php');
    }

    public function loginCorsShib() {
        $token = $this->requete->getParametre("token");
        $status = 0;
        //On vérifie et on décrypte le token
        $localAuthInfos = Service::get('Authentification')
                ->setToken($token)
                ->readToken($this->requete);

        //En mode Shibboleth, on doit au minimum avoir une connexion institution
        if(isset($localAuthInfos["I"])){
            setcookie('cairn_token', $token, strtotime('+'.Configuration::get('userInstSessionDuration').' '.strtolower(Configuration::get('userInstSessionUnit')).'s'));
            $status=1;
        }
        $this->genererVue(array("status" => $status), 'loginCors.php', 'gabaritAjax.php');
    }

    public function logout() {
        $ret = $this->doLogout();
        if ($ret == 1) {
            $this->genererVue(null, 'login.php', 'gabaritAjax.php');
        } else if ($ret == 2) {
            $this->genererVue(null, 'logas.php', 'gabaritAjax.php');
        }
    }

    public function doLogout() {
        //On ferme la connexion dans la DB
        //$this->managerComMongo->closeUserLog(Service::get('Authentification')->getUserLogId('userULog'));
        $this->managerComMongo->closeUserLog($this->authInfos["G"]["TOKEN"]);
        //On supprime le token, ou sa partie U
        $token = Service::get('Authentification')->dropToken();
        //On met à jour les infos utilisées par l'appli
        $this->authInfos = Service::get('Authentification')->readToken($this->requete);
        //On met à jour et/ou on supprime le cookie

        if ($token == null) {
            setcookie('cairn_token', $token, time() - 3600, '/', '', 0);
            unset($_COOKIE["cairn_token"]);
            return 1;
        } else {
            //Si il reste un cookie après un logout, c'est forcément pour une INST
            setcookie('cairn_token', $token, strtotime('+'.Configuration::get('userInstSessionDuration').' '.strtolower(Configuration::get('userInstSessionUnit')).'s'));
            return 2;
        }
    }

    public function creerCompte() {
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*', $this->authInfos);

        // Modification du header
        if($this->requete->existeParametre('from')){
            $headers[4]["attributes"][1]["value"] = "créer un compte (via ".$this->requete->getParametre('from').")";
        }
        //accès initial
        // Récupération des données (PAYS & DISCIPLINES)
        // Récupération des pays et repositionnement
        $listePays = $this->content->getListePays();
        array_unshift($listePays, "------------");                               // Ajout du séparateur
        array_unshift($listePays, "Canada");                                     // Ajout du pays CA
        array_unshift($listePays, "Suisse");                                     // Ajout du pays CH
        array_unshift($listePays, "France");                                     // Ajout du pays FR
        array_unshift($listePays, "Belgique");                                   // Ajout du pays BE

        // On récupère les disciplines principales, et on les indexe par l'identifiant de la discipline
        $disciplines = array();
        foreach ($this->content->getDisciplines(null, 1) as $disc) {
            $disciplines[$disc['POS_DISC']] = $disc;
        }
        $userActivities = $this->content->getProfessionList();

        // Récupération des revues
        $revues = $this->content->getRevuesByType(1);

        if (!$this->requete->existeParametre("email")) {
            // Récupération de l'e-mail pré-encodée (depuis les alertes e-mail, par exemple)
            if ($this->requete->existeParametre("user")) {$email = $this->requete->getParametre('user');} else {$email = "";}
            //var_dump($this->requete->getParametre('from', ''));
            // Formulaire pour la création du compte
            $this->genererVue([
                'disciplines' => $disciplines,
                'listePays' => $listePays,
                'from' => $this->requete->getParametre('from', ''),
                'email' => $email,
                'userActivities' => $userActivities,
            ], 'creerCompte.php', null, $headers);
            return;
        }

        //soumission de la demande de création
        $email = $this->requete->getParametre("email");
        $cemail = $this->requete->getParametre("cemail");
        $mdp = $this->requete->getParametre("mdp");
        $cmdp = $this->requete->getParametre("cmdp");
        $nom = $this->requete->getParametre("nom");
        $prenom = $this->requete->getParametre("prenom");
        $pays = $this->requete->getParametre("pays");
        $activity = $this->requete->getParametre("activity", null);
        $pos_disc = $this->requete->getParametre("pos_disc", null);
        $accept_partenaires = $this->requete->getParametre("accept_partenaires", "off") === "on";

        $errorNum = null;
        $errorTpl = null;
        if ($mdp != $cmdp) {
            // Vérification du mot de passe
            $errorNum = 2;
            $errorTpl = Configuration::get('dirVue').'/User/badpwd.php';
        } elseif (strlen($mdp) < 6 || strlen($mdp) > 20 || !preg_match("#[a-zA-Z0-9]+#", $mdp)) {
            // Validité du mot de passe
            $errorNum = 3;
            $errorTpl = Configuration::get('dirVue').'/User/badpwd.php';
        } elseif ($this->contentCom->validateEmail($email)) {
            // On vérifie que le compte n'existe pas encore
            $errorNum = 2;
            $errorTpl = Configuration::get('dirVue').'/User/bademail.php';
        } elseif ($email != $cemail) {
            // On vérifie que l'email est le même que celui qui doit être recopié par l'utilisateur
            $errorNum = 4;
            $errorTpl = Configuration::get('dirVue').'/User/bademail.php';
        }

        // La vue générée en cas d'erreur de l'utilisateur
        if ($errorNum !== null) {
            $this->genererVue([
                'disciplines' => $disciplines,
                'posDisc' => $pos_disc,
                'listePays' => $listePays,
                'pays' => $pays,
                'error_tpl' => $errorTpl,
                'error_num' => $errorNum,
                'email' => $email,
                'cemail' => $cemail,
                'nom' => $nom,
                'prenom' => $prenom,
                'userActivities' => $userActivities,
                'posProf' => $activity,
            ],'creerCompte.php', null, $headers);
            return;
        }

        //Cryptage du mot de passe
        $mdp = Service::get("CairnToken")->cryptMp($mdp);

        //On crée le compte et on renvoie vers l'écran de confirmation
        $userOk = $this->managerCom->createAccount(array($email, $nom, $prenom, $email, $activity, $mdp, 'U', $pos_disc, $pays));
        if (!$userOk) return; // ???? Pas de vues ????

        $this->managerAbo->createAbonne($email,$nom.' '.$prenom,$mdp);
        if ($accept_partenaires) {
            $alertOk = $this->manager->insertAlertePartenaire($email);
        }
        //TODO : que faire avec le codepromo ???
        //TODO : gérer un retour d'erreur/d'exception des managers
        //Puis on le logue...
        $this->doLogin($email,'U');

        //Si c'est une création de compte dans le cadre d'un panier ou d'une demande, on doit faire suivre l'info du Guest -> User
        $from = "";
        if($this->requete->existeParametre('from')){
            $from = $this->requete->getParametre('from');
            switch ($from) {
                case 'demandeBiblio':
                    Service::get('CairnHisto')->copieFromGuestToUser('panierInst',$this->authInfos, 1);
                    break;
                case 'panierAchat':
                    Service::get('CairnHisto')->copieFromGuestToUser('panier',$this->authInfos, 1);
                    break;
            }
        }

        // Définition de la valeur a envoyé par e-mail
        if(Configuration::get('mode') == 'normal') {$mailDisField = "DISCIPLINE";$mailPosField = "PROFESSION";}
        if(Configuration::get('mode') == 'cairninter') {$mailDisField = "DISCIPLINE_EN";$mailPosField = "PROFESSION_EN";}

        //On envoie l'e-mail
        Service::get('Mailer')->sendMailFromTpl(
            '/'.Configuration::get('dirVue').'/User/Mails/mailConfirmation.xml',
            [
                'to' => $email,
                'EMAIL' => $email,
                'MDP' => $mdp,
                'NOM' => $nom,
                'PRENOM' => $prenom,
                'ACTIVITY' => isset($pos_prof) ? $userActivities[$pos_prof][$mailPosField] : 'non renseignée',
                'PAYS' => $pays,
                'DISCIPLINE' => isset($pos_disc) ? $disciplines[$pos_disc][$mailDisField]: 'non renseignée',
                'IS_SUBSCRIBE_TO_NEWSLETTERS' => $accept_partenaires ? 'oui' : 'non',
            ]);

        //Tag dans le headers, pour le tag : Wt.cg_s.
                    $headers[3] = Array('tagname' => meta,'attributes' =>
            Array(0 => Array('name' => 'name', 'value' => 'WT.cg_s'),
            1 => Array('name' => 'content', 'value' => 'confirmation de création de compte')));

        //Puis on affiche le message de confirmation
        $this->genererVue(['from' => $this->requete->getParametre('from', ''), 'revues' => $revues], 'confirmation.php', null, $headers);
    }


    public function supprimerCompte() {
        // Vérification de la connexion en cours
        if ($this->authInfos["U"]) {

            // Définition de l'ID
            $newIdUser = "DEL-".time();
            // Récupération des données de l'utilisateur
            $idUser = $this->authInfos["U"]["ID_USER"];

            // Suppression des données
            $delete = $this->managerCom->deleteAccount($idUser, $newIdUser);

            // Déconnexion de l'utilisateur
            $logout = $this->doLogout();

            echo 1;
        }
        else {
            echo 0;
        }
    }

    public function updEmail() {
        if ($this->requete->existeParametre("email") && $this->requete->existeParametre("cemail")) {
            //$oldemail = $this->requete->getParametre("email");
            $oldemail   = $this->authInfos["U"]["EMAIL"];
            $oldmdp     = $this->requete->getParametre("mdp");
            $email      = $this->requete->getParametre("email");
            $cemail     = $this->requete->getParametre("cemail");
            // on conserve le mdp
            $mdp        = $oldmdp;

            $errorNum = null;
            $errorTpl = null;

            // Vérification du Login et du Mdp
            if ($this->contentCom->validateLogin($oldemail, $oldmdp) === FALSE) {
                // Utilisateur inconnu
                if ($this->contentCom->validateEmail($oldemail) === FALSE) {
                    $errorNum = 1;
                    $errorTpl = "bademail.php";
                }
                // Mot de passe incorrect
                else {
                    $errorNum = 1;
                    $errorTpl = "badpwd.php";
                }
            }
            // Vérification de l'existance d'un compte avec la nouvelle adresse e-mail ou E-mail inchangé
            else if(($this->contentCom->validateEmail($email) === TRUE) || ($oldemail == $email)) {
                $errorNum = 2;
                $errorTpl = "bademail.php";
            }
            // La confirmation de l'e-mail est incorrect
            else if($email != $cemail) {
                $errorNum = 4;
                $errorTpl = "bademail.php";
            }


            // La vue générée en cas d'erreur de l'utilisateur
            if ($errorNum !== null) {
                $this->genererVue(array('error_num' => $errorNum), $errorTpl, 'gabaritAjax.php');
                return;
            }
            // Tout est OK
            else {
                //On déloggue et on relogue l'utilisateur
                $this->doLogout();
                //On adapte les dépendances (vu que l'email est l'identifiant...)
                $this->manager->updateUserAlertes($oldemail, $email);
                //A valider : $this->manager->updateUserEditeur($oldemail,$email);
                $this->managerCom->updateUserAchats($oldemail, $email);
                $this->managerCom->updateUserAchatsAbo($oldemail, $email);
                $this->managerCom->updateUserCreditArticle($oldemail, $email);
                $this->managerComMongo->updateUserLogs($oldemail, $email);
                //Puis on fait la modif
                $this->managerCom->updateUserEmail($oldemail, $email, $mdp);
                $this->managerAbo->updateAbonne($oldemail,$email,$mdp);
                //On reloggue
                $this->doLogin($email,'U');

                //Enfin, on envoie le mail de confirmation
                //Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailConfirmationUpdate.xml', array('to' => $email));

                $disciplines = $this->content->getDisciplines(null, 1);
                $alerte = $this->content->getAlertes($this->authInfos["U"]["ID_USER"], 'P');
                $this->genererVue(array('disciplines' => $disciplines, 'alerte' => $alerte), 'monCompte.php', 'gabaritAjax.php');
            }
        }
        else {
            //accès initial
            $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-mail-mdp', $this->authInfos);
            $this->genererVue(null, 'updateEmail.php', null, $headers);
        }
    }

    public function updMdp() {
        if ($this->requete->existeParametre("email") && $this->requete->existeParametre("mdp")) {
            $oldemail = $this->requete->getParametre("email");
            $oldmdp = $this->requete->getParametre("mdp");
            $mdp = $this->requete->getParametre("mdp2");
            $cmdp = $this->requete->getParametre("mdp3");
            // on conserve l'e-mail
            $email = $oldemail;
            //On valide le couple oldemail/oldmdp
            if ($this->contentCom->validateLogin($oldemail, $oldmdp) === FALSE) {
                if ($this->contentCom->validateEmail($oldemail) === FALSE) {
                    $this->genererVue(array('error_num' => 1), 'bademail.php', 'gabaritAjax.php');
                } else {
                    $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');
                }
            } else if ($mdp != $cmdp) { //On vérifie les nouveaux mdp
                $this->genererVue(array('error_num' => 2), 'badpwd.php', 'gabaritAjax.php');
            } else if (strlen($mdp) < 6 || strlen($mdp) > 20 || !preg_match("#[a-zA-Z0-9]+#", $mdp)) {
                $this->genererVue(array('error_num' => 3), 'badpwd.php', 'gabaritAjax.php');
            } else{
                //On sauve la modif de pwd
                $this->managerCom->updateUserPassword($mdp,$email);
                $this->managerAbo->updateAbonnePassword($mdp,$email);

                $disciplines = $this->content->getDisciplines(null, 1);
                $alerte = $this->content->getAlertes($this->authInfos["U"]["ID_USER"], 'P');
                $this->genererVue(array('disciplines' => $disciplines, 'alerte' => $alerte), 'monCompte.php', 'gabaritAjax.php');
            }
        } else {
            //accès initial
            $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-mail-mdp', $this->authInfos);
            $this->genererVue(null, 'updateMdp.php', null, $headers);
        }
    }

    public function credit($gabarit = 'gabarit.php') {
        $headers = Service::get('Webtrends')->webtrendsHeaders('credit', $this->authInfos);
        if (isset($this->authInfos["U"])) {
            $credit = array();
            $creditDispo = $this->contentCom->getCreditDispo($this->authInfos['U']['ID_USER']);
            if($creditDispo){
                $expire = $creditDispo['EXPIRATION_CREDIT'];
                $solde = $creditDispo['SOLDE'];

                $credit = [
                    "expire" => date_format(new DateTime($expire), 'd-m-Y'),
                    "solde" => $solde
                ];
            }

            $this->genererVue(array('credit' => $credit), 'creditArticle.php', $gabarit, $headers);
        } else {
            $this->genererVue(null, 'creditArticleGuest.php', null, $headers);
        }
    }

    public function creditDetail() {
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*', $this->authInfos);
        if (isset($this->authInfos["U"])) {
            $credit = array();
            $sumCreditAchat = $this->contentCom->getSumCreditAchat($this->authInfos['U']['ID_USER']);
            if ($sumCreditAchat > 0) {
                $sumAchatCredit = $this->contentCom->getSumAchatCredit($this->authInfos['U']['ID_USER']);
                $sumAboCredit = $this->contentCom->getSumAbosCredit($this->authInfos['U']['ID_USER']);
                $achatsCredit = $this->contentCom->getAchatsCredit($this->authInfos['U']['ID_USER']);
                $abosCredit = $this->contentCom->getAbosCredit($this->authInfos['U']['ID_USER']);
                $creditDispo = $this->contentCom->getCreditDispo($this->authInfos['U']['ID_USER']);

                $credit = [
                    "solde" => $creditDispo['SOLDE'],
                    "prix" => $creditDispo['PRIX'],
                    "sumCredit" => $sumCreditAchat,
                    "sumAchat" => ($sumAchatCredit+$sumAboCredit),
                    "lastAchat" => $creditDispo['DATE_CREDIT'],
                    "expire" => date_format(new DateTime($creditDispo['EXPIRATION_CREDIT']), 'd-m-Y')
                ];
                $arrayNumRev = array();
                $arrayNumOuv = array();
                $arrayNumMag = array();
                $arrayArtRev = array();
                $arrayArtOuv = array();
                $arrayArtMag = array();
                foreach ($achatsCredit as $achat) {
                    if ($achat['ID_ARTICLE'] != '') {
                        $details = $this->content->getAchatDetailForArticle($achat['ID_ARTICLE']);
                        $achat['details'] = $details;
                        $tmpAchat = array(&$achat['details']);  //La fonction suivante n'accepte qu'une liste par référence
                        Service::get('ContentArticle')->readContentArticles($tmpAchat, 'revue', $this->authInfos);
                        $this->checkType('A', $details['REVUE_TYPEPUB'], $achat, $arrayNumRev, $arrayNumOuv, $arrayNumMag, $arrayArtRev, $arrayArtOuv, $arrayArtMag);
                    } else {
                        $details = $this->content->getAchatDetailForNumero($achat['ID_NUMPUBLIE']);
                        $achat['details'] = $details;
                        $this->checkType('N', $details['REVUE_TYPEPUB'], $achat, $arrayNumRev, $arrayNumOuv, $arrayNumMag, $arrayArtRev, $arrayArtOuv, $arrayArtMag);
                    }
                }
                foreach($abosCredit as &$abo){
                    $details = $this->content->getAboDetails($abo['ID_ABON'], $abo['ID_REVUE']);
                    $abo['details'] = $details;
                }
            }
            $this->genererVue(array('credit' => $credit,
                'abos' => $abosCredit,
                'numRev' => $arrayNumRev,
                'artOuv' => $arrayArtOuv,
                'artRev' => $arrayArtRev,
                'artMag' => $arrayArtMag), 'creditArticleDetail.php', null, $headers);
        } else {
            $this->genererVue(null, 'creditArticleGuest.php', null, $headers);
        }
    }

    public function achats($gabarit = 'gabarit.php') {
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-achat', $this->authInfos);
        if (isset($this->authInfos['U'])) {
            $abos = $this->contentCom->getAchatsAbonnements($this->authInfos['U']['ID_USER']);
            foreach ($abos as &$abo) {
                $details = $this->content->getAboDetails($abo['ID_ABON'], $abo['ID_REVUE']);
                $abo['details'] = $details;
            }
            $achats = $this->contentCom->getAchats($this->authInfos['U']['ID_USER']);

            $arrayNumRev = array();
            $arrayNumOuv = array();
            $arrayNumMag = array();
            $arrayArtRev = array();
            $arrayArtOuv = array();
            $arrayArtMag = array();
            foreach ($achats as $achat) {
                if ($achat['ID_ARTICLE'] != '') {
                    $details = $this->content->getAchatDetailForArticle($achat['ID_ARTICLE']);
                    $achat['details'] = $details;
                    $tmpAchat = array(&$achat['details']);  //La fonction suivante n'accepte qu'une liste par référence
                    Service::get('ContentArticle')->readContentArticles($tmpAchat, 'revue', $this->authInfos);
                    $this->checkType('A', $details['REVUE_TYPEPUB'], $achat, $arrayNumRev, $arrayNumOuv, $arrayNumMag, $arrayArtRev, $arrayArtOuv, $arrayArtMag);
                } else {
                    $details = $this->content->getAchatDetailForNumero($achat['ID_NUMPUBLIE']);
                    $achat['details'] = $details;
                    $this->checkType('N', $details['REVUE_TYPEPUB'], $achat, $arrayNumRev, $arrayNumOuv, $arrayNumMag, $arrayArtRev, $arrayArtOuv, $arrayArtMag);
                }
            }

            $this->genererVue(array(
                'abos' => $abos,
                'numRev' => $arrayNumRev,
                'numOuv' => $arrayNumOuv,
                'artOuv' => $arrayArtOuv,
                'artRev' => $arrayArtRev,
                'artMag' => $arrayArtMag), 'achats.php', $gabarit, $headers);
        } else {
            $this->genererVue(array('restricted' => 'oui', 'connectFrom' => 'achats'), 'connect.php', null, $headers);
        }
    }

    public function subConnect(){
        if ($this->requete->existeParametre("email_connexion")) {
            $email = $this->requete->getParametre("email_connexion");
            $password = $this->requete->getParametre("password_connexion");

            $ret = $this->checkLogin($email, $password);

            switch ($ret) {
                case 1:
                    $action = $this->requete->getParametre("todo");
                    $this->$action('gabaritAjax.php');
                    break;
                case -1:
                    $this->genererVue(array('error_num' => 1), 'bademail.php', 'gabaritAjax.php');
                    break;
                case -2:
                    $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');
                    break;
                case -3:
                    $this->genererVue(array('error_num' => 3), 'bademail.php', 'gabaritAjax.php');
                    break;
            }
        }
    }

    public function biblio() {
        // Les utilisateurs connectés ne peuvent plus se rendre sur la page bibliographie (#69425)
        // Utilisateur connecté :
        if(isset($this->authInfos['U'])){
            $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-biblio', $this->authInfos);

            if (isset($this->authInfos['U'])) {
                $biblio = $this->authInfos['U']['HISTO_JSON']->biblio;
            } else {
                $biblio = $this->authInfos['G']['HISTO_JSON']->biblio;
            }
            if ($biblio != null) {
                $typesAchat = array("MODE" => Service::get('ControleAchat')->getModeAchat($this->authInfos));
                $articles = $this->content->getBiblioArticles($biblio);
                $modeBoutons = Configuration::get('modeBoutons');
                if($modeBoutons == 'cairninter'){
                    Service::get('ContentArticle')
                                ->setTypesAchat($typesAchat)
                                ->readButtonsForInter($articles, $this->authInfos);
                }else{
                    Service::get('ContentArticle')
                            ->setTypesAchat($typesAchat)
                            ->readContentArticles($articles, '', $this->authInfos);
                }
                $numeros = $this->content->getBiblioNumeros($biblio);

                $arrayNumRev = array();
                $arrayNumOuv = array();
                $arrayNumMag = array();
                $arrayArtRev = array();
                $arrayArtOuv = array();
                $arrayArtMag = array();
                foreach ($numeros as $numero) {
                    $this->checkType('N', $numero['REVUE_TYPEPUB'], $numero, $arrayNumRev, $arrayNumOuv, $arrayNumMag, $arrayArtRev, $arrayArtOuv, $arrayArtMag);
                }
                foreach ($articles as $article) {
                    $this->checkType('A', $article['REVUE_TYPEPUB'], $article, $arrayNumRev, $arrayNumOuv, $arrayNumMag, $arrayArtRev, $arrayArtOuv, $arrayArtMag);
                }

                $biblioList = implode('/', $biblio);
                $this->genererVue(array('biblioList' => $biblioList,
                    'numOuv' => $arrayNumOuv,
                    'numRev' => $arrayNumRev,
                    'numMag' => $arrayNumMag,
                    'artOuv' => $arrayArtOuv,
                    'artRev' => $arrayArtRev,
                    'artMag' => $arrayArtMag), 'biblio.php', null, $headers);
            } else {
                $this->genererVue(null, 'biblio.php', null, $headers);
            }
        }
        // Utilisateur non-connecté
        else {
            // Initialisation des valeurs supplémentaires
            $params = null;
            if($this->requete->existeParametre('id_numpublie')) {$params["id_numpublie"] = $this->requete->getParametre('id_numpublie');} else {$params["id_numpublie"] = "";}
            if($this->requete->existeParametre('id_article')) {$params["id_article"] = $this->requete->getParametre('id_article');} else {$params["id_article"] = "";}

            // Génération de la vue
            $this->genererVue(array('restricted' => 'oui', 'connectFrom'=>'biblio.php', 'params' => $params), 'connect.php', null, $headers);
        }
    }

    public function biblioActions() {
        // Récupération des valeurs
        $idForAction = "";
        $type        = "";
        $todo        = $this->requete->getParametre('todo');

        // Récupération des valeurs et définition du type
        if ($this->requete->existeParametre('idArticle')) {
            $idForAction = $this->requete->getParametre('idArticle');
            $type = "article";
        } else {
            $idForAction = $this->requete->getParametre('idNumPublie');
            $type = "numero";
        }

        // Lors de l'ajout, on vérifie si il s'agit d'un article ou d'un numéro
        if($todo == "add") {
            // Si il s'agit d'un numéro, on vérifie si il s'agit d'une revue (id_numpublie et typepub = 1) ou d'un ouvrage
            if($type == "numero") {
                // Récupération du type
                $typepub = $this->content->getRevueTypepubFromNumero($idForAction);
                $typepub = $typepub["TYPEPUB"];

                // Si il s'agit d'une revue, on ajoute uniquement les articles de la revue
                if($typepub == 1) {
                    $articles = $this->content->getArticlesIdFromNumero("'".$idForAction."'", 1);
                    foreach ($articles as $article) {
                        Service::get('CairnHisto')->addToHisto('biblio', $article, $this->authInfos);
                    }
                }
                // Ajout d'un ouvrage
                else {
                    Service::get('CairnHisto')->addToHisto('biblio', $idForAction, $this->authInfos);
                }
            }
            // Ajout d'un article/chapitre/...
            else {
                Service::get('CairnHisto')->addToHisto('biblio', $idForAction, $this->authInfos);
            }
        }
        // Suppression
        else {
            Service::get('CairnHisto')->removeFromHisto('biblio', $idForAction, $this->authInfos);
        }
    }

    public function biblioPrint() {
        if (isset($this->authInfos['U'])) {
            $biblio = $this->authInfos['U']['HISTO_JSON']->biblio;
        } else {
            $biblio = $this->authInfos['G']['HISTO_JSON']->biblio;
        }
        if (!empty($biblio)) {
            $articles = $this->content->getBiblioArticles($biblio);
            $numeros = $this->content->getBiblioNumeros($biblio);

            $biblioList = implode('/', $biblio);
            $this->genererVue(array('articles' => $articles, 'numeros' => $numeros), 'biblio_p.php', 'gabaritAjax.php');
        } else {
            $this->genererVue(null, 'biblio_p.php', 'gabaritAjax.php');
        }
    }

    private function checkType($typeAchat, $typepub, $achat, &$arrayNumRev, &$arrayNumOuv, &$arrayNumMag, &$arrayArtRev, &$arrayArtOuv, &$arrayArtMag) {
        switch ($typepub) {
            case '1':
                if ($typeAchat == 'A') {
                    $arrayArtRev[] = $achat;
                } else {
                    $arrayNumRev[] = $achat;
                }
                break;
            case '2':
                if ($typeAchat == 'A') {
                    $arrayArtMag[] = $achat;
                } else {
                    $arrayNumMag[] = $achat;
                }
                break;
            default:
                if ($typeAchat == 'A') {
                    $arrayArtOuv[] = $achat;
                } else {
                    $arrayNumOuv[] = $achat;
                }
        }
    }

    public function sendBiblioMail() {
        $biblioList = $this->requete->getParametre('biblio');
        $userMail = $this->requete->getParametre('userMail');
        $userNames = $this->requete->getParametre('userNames');
        $text = $this->requete->getParametre('text');

        Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailTemplateBiblio.xml', array('to' => 'traitement2015@cairn.info', 'exp' => $userNames, 'com' => $text, 'bib' => $biblioList, 'MAIL' => $userMail));
    }

    public function isUserExist() {
        $email = $this->requete->getParametre('USERMAIL');
        if($this->contentCom->validateEmail($email) === TRUE) {
            $userExist = 1;
        }
        else {
            $userExist = 0;
        }
        echo $userExist;
    }

    public function mdpOublie() {
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-mdp', $this->authInfos);
        $this->genererVue(null, 'mdpOublie.php', null, $headers);
    }

    public function mdpRecover() {
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*', $this->authInfos);

        if ($this->requete->existeParametre('id')) {
            $tokenCrypt = $this->requete->getParametre("id");
            $tokenDecrypt = Service::get('CairnToken')->decrypt($tokenCrypt);
            $this->genererVue(array('token' => $tokenDecrypt), 'mdpNouveau.php', null, $headers);
        } else {
            $this->genererVue(null, 'mdpNouveau.php', null, $headers);
        }
    }

    public function sendPasswordMail() {
        $userMail = $this->requete->getParametre('USERMAIL');

        //check if mail exist..
        if ($user = $this->contentCom->getUserInfos($userMail)) {
            $token = Service::get('CairnToken')->crypt(array($userMail), 3600 * 4);
            $this->managerCom->addUserTmpPassword($token, $userMail);
            Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPassRecover.xml', [
                    'isHtml' => 0,
                    'email' => $user['EMAIL'],
                    'nom' => $user['NOM'],
                    'prenom' => $user['PRENOM'],
                    'link' => urlencode($token),
                    'urlSite' => Configuration::get('urlSite'),
            ]);
            echo urlencode($token);
        } else {
            echo 1;
        }
    }

    public function saveNewPassword() {
        $userMail = $this->requete->getParametre('USERMAIL');
        $userPwd = $this->requete->getParametre('PWD');
        $token = $this->requete->getParametre('TOKEN');

        if ($this->contentCom->getTokenByUser($userMail)[0] == $token) {
            //record in database..
            $this->managerCom->updateUserPassword($userPwd, $userMail);
            echo '0';
        } else {
            echo '1';
        }
    }

    public function demandes($gabarit = null){
        if(($checkAdd = $this->checkAdd()) != null){
            Service::get('CairnHisto')->addToHisto('panierInst',$checkAdd['addToBasket'],$this->authInfos);
        }

        //On lit le contenu de l'historique panier
        if (isset($this->authInfos['U'])) {
            $panier = $this->authInfos['U']['HISTO_JSON']->panierInst;
        } else {
            $panier = $this->authInfos['G']['HISTO_JSON']->panierInst;
        }
        $arrayArts = array();
        foreach($panier as $item){
            $prefix = substr($item,0,1);
            switch($prefix){
                case 'A':
                    $arrayArts[] = substr($item,1);
                    break;
            }
        }
        $arrayArtRev = array();
        $arrayArtOuv = array();
        $arrayArtMag = array();
        $emptyArray = array();
        if(!empty($arrayArts)){
            $articles = $this->content->getBiblioArticles($arrayArts);
            foreach ($articles as $article) {
                $this->checkType('A', $article['REVUE_TYPEPUB'], $article, $emptyArray, $emptyArray, $emptyArray, $arrayArtRev, $arrayArtOuv, $arrayArtMag);
            }
        }

        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*', $this->authInfos);
        $this->genererVue(array(
                'artOuv' => $arrayArtOuv,
                'artRev' => $arrayArtRev,
                'artMag' => $arrayArtMag,
                'gabarit' => $gabarit==null?'gabarit.php':$gabarit,
                'returnLink' => $checkAdd['returnLink'])
               ,'demandes.php',$gabarit==null?'gabarit.php':$gabarit, $headers);
    }

    public function demandesActions(){
        $todo = $this->requete->getParametre('todo');
        switch($todo){
            case 'remove':
                $this->removeDemande();
                break;
            case 'demandeBiblio':
                $this->demandeBiblio();
                break;
            case 'connect':
                $this->connectBiblio();
                break;
            case 'envoiDemandeBiblio':
                $this->envoiDemandeBiblio();
                break;
            case 'merge':
                $this->demandeMerge();
                break;
            case 'erase':
                $this->demandeErase();
                break;
        }
    }

    private function removeDemande(){
        $type = $this->requete->getParametre('type');
        $removeFromBasket = "";
        switch($type){
            case 'ART':
                $removeFromBasket = 'A'.$this->requete->getParametre('id2');
        }

        Service::get('CairnHisto')->removeFromHisto('panierInst',$removeFromBasket,$this->authInfos);
    }

    private function demandeBiblio(){
        if(isset($this->authInfos['U'])){
            //Envoi directement sur la page de demande
            $this->genererVue(null, 'demandeBiblio.php','gabaritAjax.php');
        }else{
            //Passe par la connexion
            $this->genererVue(array('restricted' => 'oui', 'connectFrom' => 'demandeBiblio'), 'connect.php','gabaritAjax.php');
        }
    }

    private function connectBiblio(){
        if ($this->requete->existeParametre("email_connexion")) {
            $email = $this->requete->getParametre("email_connexion");
            $password = $this->requete->getParametre("password_connexion");

            $ret = $this->checkLogin($email, $password);

            //On écrase la liste de demandes Guest par la liste de demandes User
            $alert = Service::get('CairnHisto')->copieFromGuestToUser('panierInst',$this->authInfos);
            if($alert == 'alert'){
                $this->genererVue(array('from' => 'demande'), 'fusionListe.php', 'gabaritAjax.php');
            }else{
                switch ($ret) {
                    case 1:
                        $this->genererVue(null, 'demandeBiblio.php', 'gabaritAjax.php');
                        break;
                    case -1:
                        $this->genererVue(array('error_num' => 1), 'bademail.php', 'gabaritAjax.php');
                        break;
                    case -2:
                        $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');
                        break;
                    case -3:
                        $this->genererVue(array('error_num' => 3), 'bademail.php', 'gabaritAjax.php');
                        break;
                }
            }
        }
    }

    private function demandeMerge(){
        Service::get('CairnHisto')->mergeGuestAndUser('panierInst',$this->authInfos);
        $this->demandes('gabaritAjax.php');
    }

    private function demandeErase(){
        Service::get('CairnHisto')->copieFromGuestToUser('panierInst',$this->authInfos,1);
        $this->genererVue(null, 'demandeBiblio.php', 'gabaritAjax.php');
    }

    private function envoiDemandeBiblio(){
        $prenom = $this->requete->getParametre("prenom");
        $nom = $this->requete->getParametre("nom");
        $fonction = $this->requete->getParametre("fonction");
        $motivation = $this->requete->getParametre("motivation");

        $arrSels = $this->authInfos['U']['HISTO_JSON']->panierInst;
        $str = "";
        foreach($arrSels as $arrSel){
            $str .= ($str!=''?'/':'').substr($arrSel,1);
        }

        Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailDemandeBiblio.xml', array('to' => Configuration::get('contact_credit'),
            'NOM' => $nom, 'PRENOM' => $prenom, 'FONCTION' => $fonction, 'MOTIVATION' => $motivation,
            'SELECTIONS' => $str,
            'ID_INST' => $this->authInfos['I']['ID_USER'], 'ID_USER' => $this->authInfos['U']['ID_USER']));

        //On vide le panierInst
        Service::get('CairnHisto')->clearHistorique(isset($this->authInfos['U'])?'U':'G', 'panierInst', $this->authInfos);

        $this->genererVue(null, 'demandeBiblioConfirm.php','gabaritAjax.php');
    }

    public function panier(){
        if(($checkAdd = $this->checkAdd()) != null){
            Service::get('CairnHisto')->addToHisto('panier',$checkAdd['addToBasket'],$this->authInfos);
        }
        $this->panierStart('gabarit.php',isset($checkAdd)?$checkAdd['returnLink']:null);
    }

    private function panierStart($gabarit, $returnLink= null){
        //On lit le contenu de l'historique panier
        if (isset($this->authInfos['U'])) {
            $panier = $this->authInfos['U']['HISTO_JSON']->panier;
        } else {
            $panier = $this->authInfos['G']['HISTO_JSON']->panier;
        }

        // Réception des données absolues de la revue (pour chaque article, on récupère les données de la REVUE à laquelle il se rapporte)
        $arrayAbsoluteRevue = array();

        // Tableau des éléments a afficher
        $arrayAbos = array();
        $arrayNumRev = array();
        $arrayNumRevElec = array();
        $arrayArts = array();
        $arrayCredits = array();
        $addCartError = null;

        // Parcours des éléments du panier
        foreach($panier as $item){
            $prefix = substr($item,0,1);
            switch($prefix){
                case 'B':
                    $itemElems = explode('°',substr($item,1));
                    $arrayAbo = [
                      "ABO" => $this->content->getAboDetails($itemElems[0], $itemElems[1]),
                      "REVUE" => $this->content->getRevuesById($itemElems[1])[0]
                    ];
                    if(strlen($itemElems[2]) == 4 && $itemElems[2] > 2010 && $itemElems[2] < 2050){
                        $arrayAbo['ANNEE'] = $itemElems[2];
                    }else{
                        $arrayAbo['FIRSTNUM'] = $this->content->getNumpublieById($itemElems[2])[0];
                    }
                    $arrayAbos[] = $arrayAbo;
                    break;
                case 'N':
                    $numeroPaper = $this->content->getNumpublieById(substr($item,1))[0];
                    // Oui, c'est du dirty-fix
                    if ($numeroPaper['NUMERO_PRIX'] == 0 || $numeroPaper['NUMERO_EPUISE'] == 1) {
                        break;
                    }
                    $arrayNumRev[] = $numeroPaper;
                    break;
                case 'E':
                    $arrayNumRevElec[] = $this->content->getNumpublieById(substr($item,1))[0];
                    break;
                case 'A':
                    $arrayArts[] = substr($item,1);
                    break;
                case 'D':
                    //Bundle...
                    break;
                case 'C':
                    $arrayCredits[] = array("PRIX" => substr($item,1), "EXPIRE" => (intval(date('Y'))+1));
                    break;
            }
        }

        $arrayArtRev = array();
        $arrayArtOuv = array();
        $arrayArtMag = array();
        $emptyArray = array();
        if(!empty($arrayArts)){
            $articles = $this->content->getBiblioArticles($arrayArts);
            foreach ($articles as $article) {

                // On enregistre les données absolues de la revue si elle n'existe pas encore
                if(!in_array($article["REVUE_ID_REVUE"], $arrayAbsoluteRevue)) {
                    // Récupération des données de la revue et du numéro
                    $revue_article  = $this->content->getRevuesById($article["REVUE_ID_REVUE"]);
                    $numero_article = $this->content->getNumpublieById($article["NUMERO_ID_NUMPUBLIE"]);

                    // Enregistrement des données nécessaires
                    $arrayAbsoluteRevue[$article["REVUE_ID_REVUE"]] = array("ACHAT_NUMERO_ELEC" => $revue_article[0]["ACHAT_NUMERO_ELEC"], "NUMERO_PRIX_ELEC" => $numero_article[0]["NUMERO_PRIX_ELEC"]);
                }

                // Retour
                $this->checkType('A', $article['REVUE_TYPEPUB'], $article, $emptyArray, $emptyArray, $emptyArray, $arrayArtRev, $arrayArtOuv, $arrayArtMag);

                // Idéalement ce contrôle aurait dû être fait AVANT mais la méthode d'ajout au panier ne le permet pas, donc
                // comme on doit parcourir tout les articles, on en profite à ce moment-ci :
                if( ($this->requete->existeParametre('ID_ARTICLE')) && ($this->requete->getParametre('ID_ARTICLE') == $article["ARTICLE_ID_ARTICLE"]) ) {
                    // Récupération des valeurs
                    $idNewArticle       = $this->requete->getParametre('ID_ARTICLE'); // ID de l'article
                    $idNewArticleNumero = $article["NUMERO_ID_NUMPUBLIE"];            // ID du numéro parent de l'article (ex.: POUR_228);

                    // Parcours des tableaux des revues
                    $arrayControl = array_merge($arrayNumRev, $arrayNumRevElec);
                    foreach($arrayControl as $numeroControl) {
                        // Le numéro contenant l'article ajouté est déjà présent dans le panier d'achat
                        if($numeroControl["NUMERO_ID_NUMPUBLIE"] == $idNewArticleNumero) {
                            // On supprime l'article du panier
                            $removeFromBasket = 'A'.$idNewArticle;
                            Service::get('CairnHisto')->removeFromHisto('panier',$removeFromBasket,$this->authInfos);

                            // On affiche un message d'alerte
                            $addCartError = "ARTICLE_IN_NUMERO";
                        }
                    }
                }
            }
        }
        $tmpCmdId = '';
        if($this->requete->existeParametre('tmpCmdId')){
            $tmpCmdId = $this->requete->getParametre('tmpCmdId');
        }

        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-panier', $this->authInfos);
        $this->genererVue(array(
                'credits' => $arrayCredits,
                'abos' => $arrayAbos,
                'numRev' => $arrayNumRev,
                'numRevElec' => $arrayNumRevElec,
                'artOuv' => $arrayArtOuv,
                'artRev' => $arrayArtRev,
                'artMag' => $arrayArtMag,
                'AbsoluteRevue' => $arrayAbsoluteRevue,
                'tmpCmdIdFrom' => $tmpCmdId,
                'gabarit' => $gabarit,
                'addCartError' => $addCartError,
                'returnLink' => $returnLink), 'panier.php', $gabarit, $headers
                );
    }

    private function checkAdd(){
        $addToBasket = "";
        $returnLink = "";
        if($this->requete->existeParametre('ID_ABON')){
            $addToBasket = 'B'.$this->requete->getParametre('ID_ABON')
                           .'°'.$this->requete->getParametre('ID_REVUE');
            if($this->requete->existeParametre('ID_NUMPUBLIE')){
                $addToBasket .= '°' .$this->requete->getParametre('ID_NUMPUBLIE');
            }else{
                $addToBasket .= '°' .$this->requete->getParametre('ANNEE');
            }
            $revue = $this->content->getRevuesById($this->requete->getParametre('ID_REVUE'))[0];
            if($revue['TYPEPUB'] == 1){
                $returnLink = 'revue-'.$revue['URL_REWRITING'].".htm";
            }else if($revue['TYPEPUB'] == 2){
                $returnLink = 'magazine-'.$revue['URL_REWRITING'].".htm";
            }
        } else if($this->requete->existeParametre('ID_NUMPUBLIE')){
            //Vérif épuisé ?
            if($this->requete->existeParametre('VERSION') && $this->requete->getParametre('VERSION')=='ELEC'){
                $addToBasket = 'E' . $this->requete->getParametre('ID_NUMPUBLIE');
            }else{
                $addToBasket = 'N' . $this->requete->getParametre('ID_NUMPUBLIE');
            }
            $returnLink = 'numero.php?ID_NUMPUBLIE='.$this->requete->getParametre('ID_NUMPUBLIE');
        } else if($this->requete->existeParametre('ID_ARTICLE')){
            $addToBasket = 'A' . $this->requete->getParametre('ID_ARTICLE');
            $returnLink = 'article.php?ID_ARTICLE='.$this->requete->getParametre('ID_ARTICLE');
        } else if($this->requete->existeParametre('ID_BUNDLE')) {
            $addToBasket = 'D' . $this->requete->getParametre('ID_BUNDLE');
        } else if($this->requete->existeParametre('ID_CREDIT')) {
            $addToBasket = 'C' . $this->requete->getParametre('ID_CREDIT');
            $returnLink = 'credit.php';
        }
        if($addToBasket != ''){
            return array('addToBasket' => $addToBasket, 'returnLink' => $returnLink);
        }else{
            return null;
        }
    }

    public function panierActions(){
        $todo = $this->requete->getParametre('todo');
        switch($todo){
            case 'start':
                $this->updateTmp();
                $this->panierStart('gabaritAjax.php');
                break;
            case 'remove':
                $this->panierRemove();
                break;
            case 'achat':
                $this->panierAchat();
                break;
            case 'connect':
                $this->panierConnect();
                break;
            case 'coord':
                $this->panierCoord();
                break;
            case 'cheque':
                $this->panierCheque();
                break;
            case 'credit':
                $this->panierCredit();
                break;
            case 'merge':
                $this->panierMerge();
                break;
            case 'erase':
                $this->panierErase();
                break;
        }
    }

    private function updateTmp(){
        $factNom = $this->requete->getParametre('fact_nom');
        $factAdr = $this->requete->getParametre('fact_adr');
        $factCp = $this->requete->getParametre('fact_cp');
        $factVille = $this->requete->getParametre('fact_ville');
        $factPays = $this->requete->getParametre('fact_pays');
        if($this->requete->existeParametre('checksvgadr')){
            $prenom = $this->requete->getParametre('prenom');
            $nom = $this->requete->getParametre('nom');
            $adr = $this->requete->getParametre('adr');
            $cp = $this->requete->getParametre('cp');
            $ville = $this->requete->getParametre('ville');
            $pays = $this->requete->getParametre('pays');
        }

        $tmpCmdId = $this->requete->getParametre('tmpCmdId');

        $this->managerCom->updateCommandeTmp($tmpCmdId, $factNom, $factAdr,
                                                    $factCp, $factVille, $factPays,
                                                    $prenom, $nom, $adr, $cp,
                                                    $ville, $pays, 0);
    }

    private function panierRemove(){
        $type = $this->requete->getParametre('type');
        $id1 = $this->requete->getParametre('id1');
        $removeFromBasket = "";
        switch($type){
            case 'CREDIT':
                $removeFromBasket = 'C'.$id1;
                break;
            case 'ABO':
                $id2 = $this->requete->getParametre('id2');
                $id3 = $this->requete->getParametre('id3');
                $removeFromBasket = 'B'.$id1.'°'.$id2.'°'.$id3;
                break;
            case 'NUM':
                if($id1 == 'ELEC'){
                    $removeFromBasket = 'E'.$this->requete->getParametre('id2');
                }else{
                    $removeFromBasket = 'N'.$id1;
                }
                break;
            case 'ART':
                $removeFromBasket = 'A'.$this->requete->getParametre('id2');
        }

        Service::get('CairnHisto')->removeFromHisto('panier',$removeFromBasket,$this->authInfos);
    }

    private function panierAchat(){
        $okEditeur = null;
        if($this->requete->existeParametre('tmpCmdId')){
            //Retour de l'écran suivant...
            $tmpCmdId = $this->requete->getParametre('tmpCmdId');
            $commandeTmp = $this->contentCom->getCommandeTmp($tmpCmdId);

            $livraison = 0;
            if(isset($commandeTmp['ADRESSE']) && $commandeTmp['ADRESSE'] != ''){
                $livraison = 1;
            }

            $listePays = $this->content->getListePays();

            //Envoi directement sur la page des coordonnées
            $this->genererVue(array(
                'livraison' => $livraison,
                'listePays' => $listePays,
                'tmpCmdId'=>$tmpCmdId,
                "commandeTmp" => $commandeTmp,
                "okEditeur" => $commandeTmp['OK_EDITEUR'] === '1',
            ), 'panierCoord.php','gabaritAjax.php');
        }else if($this->requete->existeParametre('totalPrice')){
            if(isset($this->authInfos['U'])){
                if(isset($this->authInfos['U']['HISTO_JSON']->panier)
                        && $this->authInfos['U']['HISTO_JSON']->panier != null
                        && $this->authInfos['U']['HISTO_JSON']->panier != 'null'){
                    //On recalcule le prix
                    $myTotalPrice = 0;
                    if (isset($this->authInfos['U'])) {
                        $panier = $this->authInfos['U']['HISTO_JSON']->panier;
                    } else {
                        $panier = $this->authInfos['G']['HISTO_JSON']->panier;
                    }
                    $arrayArts = array();
                    foreach($panier as $item){
                        $prefix = substr($item,0,1);
                        switch($prefix){
                            case 'B':
                                $itemElems = explode('°',substr($item,1));
                                $arrayAbo = [
                                  "ABO" => $this->content->getAboDetails($itemElems[0], $itemElems[1])
                                ];
                                $myTotalPrice += $arrayAbo['ABO']['PRIX'];
                                break;
                            case 'N':
                                $numeroPaper = $this->content->getNumpublieById(substr($item,1))[0];
                                // Oui, c'est du dirty-fix
                                if ($numeroPaper['NUMERO_PRIX'] == 0 || $numeroPaper['NUMERO_EPUISE'] == 1) {
                                    break;
                                }
                                // La validation de cette condition permet d'afficher l'accord de diffusion de métadonnées
                                // aux éditeurs partenaires
                                if (($numeroPaper['REVUE_ID_EDITEUR'] === 'PUF') && ($numeroPaper['REVUE_TYPEPUB'] == 1)) {
                                    $okEditeur = false;
                                }
                                $myTotalPrice += $numeroPaper['NUMERO_PRIX'];
                                break;
                            case 'E':
                                $arrayNumRevElec = $this->content->getNumpublieById(substr($item,1))[0];
                                $myTotalPrice += $arrayNumRevElec['NUMERO_PRIX_ELEC'];
                                break;
                            case 'A':
                                $arrayArts[] = substr($item,1);
                                break;
                            case 'D':
                                //Bundle...
                                break;
                            case 'C':
                                $myTotalPrice += substr($item,1);
                                break;
                        }
                    }
                    if(!empty($arrayArts)){
                        $articles = $this->content->getBiblioArticles($arrayArts);
                        foreach ($articles as $article) {
                            $myTotalPrice += $article['ARTICLE_PRIX'];
                        }
                    }
                   //echo $myTotalPrice .'><'.$this->requete->getParametre('totalPrice');
                    //On initie la commande temporaire
                    if($this->requete->existeParametre('tmpCmdIdFrom')){
                        $tmpCmdId = $this->requete->getParametre('tmpCmdIdFrom');
                        $commandeTmp = $this->contentCom->getCommandeTmp($tmpCmdId);
                        $this->managerCom->updateCommandeTmpAchats($tmpCmdId,
                            json_encode($this->authInfos['U']['HISTO_JSON']->panier),
                            $myTotalPrice
                            );
                    }else{
                        $tmpCmdId = date('Ymdhi').'-'.rand(1000000, 10000000);
                        $this->managerCom->createCommandeTmp($tmpCmdId,
                            $this->authInfos['U']['ID_USER'],
                            json_encode($this->authInfos['U']['HISTO_JSON']->panier),
                            $myTotalPrice,
                            $this->authInfos['G']['TOKEN']
                            );
                    }

                    $arrayAbos = array();
                    $arrayNums = array();
                    $arrayNumsElec = array();
                    $arrayArts = array();
                    $arrayCredits = array();
                    $this->lecturePanier($this->authInfos['U']['HISTO_JSON']->panier,
                            $arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits);
                    $livraison = 0;
                    if(!empty($arrayAbos) || !empty($arrayNums)){
                        $livraison = 1;
                    }

                    $listePays = $this->content->getListePays();

                    //Envoi directement sur la page des coordonnées
                    $this->genererVue(array(
                        'livraison' => $livraison,
                        'listePays' => $listePays,
                        'tmpCmdId' => $tmpCmdId,
                        "commandeTmp" => (isset($commandeTmp)?$commandeTmp:null),
                        'okEditeur' => $okEditeur,
                    ), 'panierCoord.php','gabaritAjax.php');
                } else {
                   $this->panierStart('gabaritAjax.php');
                }
            }else{
                //Passe par la connexion
                $this->genererVue(array('restricted' => 'oui', 'connectFrom' => 'panierAchat',
                        'totalPrice' => $this->requete->getParametre('totalPrice')),
                        'connect.php','gabaritAjax.php');
            }
        }
    }

    private function panierConnect(){
        if ($this->requete->existeParametre("email_connexion")) {
            $email = $this->requete->getParametre("email_connexion");
            $password = $this->requete->getParametre("password_connexion");

            $ret = $this->checkLogin($email, $password);

            //On écrase la liste du panier User par la liste du panier Guest
            $alert = Service::get('CairnHisto')->copieFromGuestToUser('panier',$this->authInfos);

            if($alert == 'alert'){
                $this->genererVue(array('from' => 'panier'), 'fusionListe.php', 'gabaritAjax.php');
            }else{
                switch ($ret) {
                    case 1:
                        //$this->genererVue(null, 'panierCoord.php', 'gabaritAjax.php');
                        $this->panierAchat();
                        break;
                    case -1:
                        $this->genererVue(array('error_num' => 1), 'bademail.php', 'gabaritAjax.php');
                        break;
                    case -2:
                        $this->genererVue(array('error_num' => 1), 'badpwd.php', 'gabaritAjax.php');
                        break;
                    case -3:
                        $this->genererVue(array('error_num' => 3), 'bademail.php', 'gabaritAjax.php');
                        break;
                }
            }
        }
    }

    private function panierMerge(){
        Service::get('CairnHisto')->mergeGuestAndUser('panier',$this->authInfos);
        $this->panierStart('gabaritAjax.php');
    }

    private function panierErase(){
        Service::get('CairnHisto')->copieFromGuestToUser('panier',$this->authInfos,1);
        $this->panierAchat();
    }

    /**
     * Cette fonction va permettre
     * de créer des commandes en statut 5,
     * dans la partie BO, mais uniquement pour les paiements Ogone.
     */
    public function commandeToBO() {
        $tmpCmdId = $this->requete->getParametre('tmpCmdId');

        //Calcul des frais de port
        $panier = $this->authInfos['U']['HISTO_JSON']->panier;

        $arrayAbos = array();
        $arrayNums = array();
        $arrayNumsElec = array();
        $arrayArts = array();
        $arrayCredits = array();
        $this->lecturePanier($panier, $arrayAbos, $arrayNums, $arrayNumsElec, $arrayArts, $arrayCredits);

        //Pour la partie achat des abonnements.
        if (count($arrayAbos) > 0) {
            $arr = array();
            foreach ($arrayAbos as $abo) {
                $arr[] = $abo['ID_REVUE'];
                $idZone = $this->content->getFraisZone($abo['ID_REVUE'], $this->authInfos['U']['PAYS']);
                $this->managerCom->insertAchatAbo($tmpCmdId, $abo, $idZone, $this->authInfos['U']['ID_USER'], 5);
            }
        }

        //Pour la partie achat des numéros.
        if (count($arrayNums) > 0) {
            $arr = array();
            foreach ($arrayNums as $num) {
                $arr[] = $num['NUMERO_ID_REVUE'];
                $this->managerCom->insertAchatNum($tmpCmdId, $num, floatval($this->content->getFraisPort('PORT_NUM', array($num['NUMERO_ID_REVUE']), $this->authInfos['U']['PAYS'])), $this->authInfos['U']['ID_USER'], 0, 5);
            }
        }

        //Partie : Achat des articles.
        if(count($arrayArts) > 0) {
            $arr = array();
            foreach($arrayArts as $art) {
               $this->managerCom->insertAchatArt($tmpCmdId, $art, 0, $this->authInfos['U']['ID_USER'], 5);
            }
        }

        //Partie : Achat des numéros électroniques.
        if(count($arrayNumsElec) > 0) {
            foreach($arrayNumsElec as $num) {
                $this->managerCom->insertAchatNum($tmpCmdId, $num, 0, $this->authInfos['U']['ID_USER'], 1, 5);
            }
        }

    }

    /**
     * Cette méthode va permettre la gestion
     * du panier.
     */
    private function panierCoord() {
        if ($this->requete->existeParametre('checksvgfactadr')) {
            $saveFact = $this->requete->getParametre('checksvgfactadr') == 'true' ? 1 : 0;
            $factNom = $this->requete->getParametre('fact_nom');
            $factAdr = $this->requete->getParametre('fact_adr');
            $factCp = $this->requete->getParametre('fact_cp');
            $factVille = $this->requete->getParametre('fact_ville');
            $factPays = $this->requete->getParametre('fact_pays');
            $saveLivr = 0;
            if ($this->requete->existeParametre('checksvgadr')) {
                $saveLivr = $this->requete->getParametre('checksvgadr') == 'true' ? 1 : 0;
                $prenom = $this->requete->getParametre('prenom');
                $nom = $this->requete->getParametre('nom');
                $adr = $this->requete->getParametre('adr');
                $cp = $this->requete->getParametre('cp');
                $ville = $this->requete->getParametre('ville');
                $pays = $this->requete->getParametre('pays');
            }
            // On enregistre, si besoin, l'accord de l'utilisateur pour la diffusion de ses données chez les partenaires
            $okEditeur = null;
            if ($this->requete->existeParametre('ok-editeur')) {
                $okEditeur = $this->requete->getParametre('ok-editeur') === 'true';
            }

            $tmpCmdId = $this->requete->getParametre('tmpCmdId');

            //Calcul des frais de port
            $panier = $this->authInfos['U']['HISTO_JSON']->panier;

            $arrayAbos = array();
            $arrayNums = array();
            $arrayNumsElec = array();
            $arrayArts = array();
            $arrayCredits = array();
            $this->lecturePanier($panier, $arrayAbos, $arrayNums, $arrayNumsElec, $arrayArts, $arrayCredits);

            $fp = 0;
            if (count($arrayAbos) > 0) {
                $arr = array();
                foreach ($arrayAbos as $abo) {
                    $arr[] = $abo['ID_REVUE'];
                }
                $fp += floatval($this->content->getFraisPort('PORT_ABO', $arr, $pays));
            }
            if (count($arrayNums) > 0) {
                $arr = array();
                foreach ($arrayNums as $num) {
                    $arr[] = $num['NUMERO_ID_REVUE'];
                }
                $fp += floatval($this->content->getFraisPort('PORT_NUM', $arr, $pays));
            }

            // On sauve les infos de facturation et les frais de port de la commande
            $this->managerCom->updateCommandeTmp($tmpCmdId, $factNom, $factAdr, $factCp, $factVille, $factPays, $prenom, $nom, $adr, $cp, $ville, $pays, $fp, $okEditeur);
            //Si demandé, on sauve les informations de facturation de l'utilisateur
            if ($saveFact == 1) {
                $this->managerCom->updateUserFactInfos($this->authInfos['U']['ID_USER'], $factNom, $factAdr, $factCp, $factVille, $factPays);
            }
            if ($saveLivr == 1) {
                $this->managerCom->updateUserLivrInfos($this->authInfos['U']['ID_USER'], $prenom, $nom, $adr, $cp, $ville, $pays);
            }
        } else {
            //On est en mode "retour" donc on a déjà tout qui est stocké.
            $tmpCmdId = $this->requete->getParametre('tmpCmdId');
        }

        $commandeTmp = $this->contentCom->getCommandeTmp($tmpCmdId);

        if ($commandeTmp['ACHATS'] != null && $commandeTmp['ACHATS'] != 'null') {
            $prixTotal = (floatval($commandeTmp['PRIX']) + floatval($commandeTmp['FRAIS_PORT']));

            if (empty($arrayCredits)) {
                //Vérification de la présence d'un crédit d'achat, SAUF si le panier contient un crédit d'achat...
                //Attention, à partir du 04/11/2014, il n'y a plus qu'un crédit en cours.
                //Toute autre situation résulte d'une non-correction de la base de données par l'équipe Cairn
                $creditDispo = $this->contentCom->getCreditDispo($this->authInfos['U']['ID_USER']);
                $soldeDispo = $creditDispo['SOLDE'];
            } else {
                $soldeDispo = 'N/A';
            }

            $ogoneOptions = Service::get("Ogone")->getOgoneInputs($commandeTmp);

            $this->genererVue(array('tmpCmdId' => $tmpCmdId,
                'creditDispo' => $soldeDispo,
                'commandeTmp' => $commandeTmp,
                'prixTotal' => $prixTotal,
                'ogoneOptions' => $ogoneOptions,
                'ogone_url' => Configuration::get('ogone_url')
                    ), 'panierPaiement.php', 'gabaritAjax.php');
        } else {
            $this->panierStart('gabaritAjax.php');
        }
    }

    private function lecturePanier($panier,&$arrayAbos,&$arrayNums,&$arrayNumsElec,&$arrayArts,&$arrayCredits){
        foreach($panier as $item){
            $prefix = substr($item,0,1);
            switch($prefix){
                case 'B':
                    $itemElems = explode('°',substr($item,1));
                    $abo = $this->content->getAboDetails($itemElems[0], $itemElems[1]);
                    $abo['INFOSUP'] = $itemElems[2];
                    $arrayAbos[] = $abo;
                    break;
                case 'N':
                    $numeroPaper = $this->content->getNumpublieById(substr($item,1))[0];
                    // Oui, c'est du dirty-fix
                    if ($numeroPaper['NUMERO_PRIX'] == 0 || $numeroPaper['NUMERO_EPUISE'] == 1) {
                        break;
                    }
                    $arrayNums[] = $numeroPaper;
                    break;
                case 'E':
                    $arrayNumsElec[] = $this->content->getNumpublieById(substr($item,1))[0];
                    break;
                case 'A':
                    $arrayArts[] = $this->content->getArticleFromId(substr($item,1));
                    break;
                case 'C':
                    $arrayCredits[] = substr($item,1);
            }
        }
    }

    public function panierOgoneErreur($type = '') {

        //Ajout des tags webTrends, pour l'erreur de paiement avec Ogone.
        $header = Service::get('Webtrends')->webtrendsHeaders('erreur-*-achat-ogone', $this->authInfos);
        //

        $this->genererVue(null, 'panierOgoneErreur.php', null, $header);
    }

    public function panierOgone(){
        //Vérifications + mail + activation
        //file_put_contents('./postsale.log',print_r($this->requete,true));

        if(Service::get('Ogone')->checkPostSaleIP($this->requete)
                && Service::get('Ogone')->checkShaOut($this->requete)){

            $commandeTmp = $this->contentCom->getCommandeTmp($this->requete->getParametre('orderID'));
            if($commandeTmp === false){
                //Ce n'est pas une commande connue dans le cairn info => on regarde dans l'inter
                $this->content = new Content('dsn_inter');
                $this->contentCom = new ContentCom('dsn_com_inter');
                $this->managerCom = new ManagerCom('dsn_com_inter');

                $commandeTmp = $this->contentCom->getCommandeTmp($this->requete->getParametre('orderID'));
            }else if($commandeTmp['SITE'] == 1) {
                //C'est une commande CairnInt (mode après fusion des bases COM), on va dans chercher les infos dans  cairnint_pub
                $this->content = new Content('dsn_inter');
            }
            $this->authInfos['U']['ID_USER'] = $commandeTmp['ID_USER'];
            $this->authInfos['U']['EMAIL'] = $commandeTmp['ID_USER'];
            $this->authInfos['U']['PAYS'] = $commandeTmp['FACT_PAYS'];
            $this->authInfos['G']['TOKEN'] = $commandeTmp['SESSION'];

            $this->finaliseCommande($this->requete->getParametre('orderID'),
                    $commandeTmp,
                    'ogone','gabarit.php');
        }else{
            $this->panierOgoneErreur('postsale');
        }
    }

    public function panierOgoneDisplay(){
        $cmd = $this->contentCom->getCommandeTmp($this->requete->getParametre('NO_COMMANDE'));
        $achats = json_decode($cmd['ACHATS']);
        $arrayAbos = array();
        $arrayNums = array();
        $arrayNumsElec = array();
        $arrayArts = array();
        $arrayCredits = array();
        $this->lecturePanier($achats,$arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits);
        $typesPanier = $this->getTypePanier($arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits);
        $typePanier = $typesPanier['typePanier'];
        $linkPanier = $typesPanier['linkPanier'];

        Service::get('CairnHisto')->clearHistorique(isset($this->authInfos['U'])?'U':'G', 'panier', $this->authInfos);

        //Ajout des tags webTrends, pour la confirmation d'achat avec Ogone.
        $header = Service::get('Webtrends')->webtrendsHeaders('confirm-*-achat-ogone', $this->authInfos);
        //

        $gabarit = "gabarit.php";
        $this->genererVue(array('typePanier'=>$typePanier,'linkPanier'=>$linkPanier, 'gabarit'=>$gabarit), 'panierFinalise.php','gabarit.php', $header);
    }

    public function panierOgone2(){
        //Vérifications + mail + activation
        if(Service::get('Ogone')->checkPostSaleIP($this->requete)
                && Service::get('Ogone')->checkShaOut($this->requete)){

            $commandeTmp = $this->contentCom->getCommandeTmp($this->requete->getParametre('NO_COMMANDE'));
            $this->authInfos['U']['ID_USER'] = $commandeTmp['ID_USER'];
            $this->authInfos['U']['EMAIL'] = $commandeTmp['ID_USER'];
            $this->authInfos['U']['PAYS'] = $commandeTmp['FACT_PAYS'];
            $this->authInfos['G']['TOKEN'] = $commandeTmp['SESSION'];

            $this->finaliseCommande($this->requete->getParametre('NO_COMMANDE'),
                    $this->contentCom->getCommandeTmp($this->requete->getParametre('NO_COMMANDE')),
                    'ogone','gabarit.php');
        }else{
            $this->panierOgoneErreur('postsale');
        }
    }

    public function panierCheque(){
        $tmpCmdId = $this->requete->getParametre('tmpCmdId');
        $idUser = $this->authInfos['U']['ID_USER'];
        $emailUser = $this->authInfos['U']['EMAIL'];
        $commandeTmp = $this->contentCom->getCommandeTmp($tmpCmdId);

        //Finalisation de la commande TMP et insertion dans COMMANDE_LOG
        //Création des achats (EN STATUT 5 !)
        //Envoi du bon de commande par email.
        //Génération de la vue
        $this->finaliseCommande($tmpCmdId, $commandeTmp,'cheque_vir','gabaritAjax.php');
    }

    public function panierCredit(){
        $tmpCmdId = $this->requete->getParametre('tmpCmdId');
        $idUser = $this->authInfos['U']['ID_USER'];
        $emailUser = $this->authInfos['U']['EMAIL'];

        $commandeTmp = $this->contentCom->getCommandeTmp($tmpCmdId);
        $firstCredit = $this->contentCom->getCreditDispo($idUser);
        if($firstCredit === FALSE){
            //Bug temporaire de chevauchement des crédits d'articles...
            echo 'Une erreur est survenue, veuillez contacter le service clientèle';
            return;
        }
        $this->finaliseCommande($firstCredit['NO_COMMANDE'],$commandeTmp,'credit','gabaritAjax.php');
    }

    private function finaliseCommande($idCommande, $commandeTmp,$typePaiement,$gabarit){
        $check = $this->contentCom->getCommande($idCommande);
        $ogone = false;
        if($check === false || $typePaiement == 'credit') {
            if($typePaiement != 'credit'){
                $this->managerCom->validateCommandeTmp($idCommande,null,$typePaiement,$commandeTmp['ID_COMMANDE']);
                if($typePaiement == 'ogone'){
                    $this->managerCom->insertCommandeLog($idCommande,
                            $typePaiement=='ogone'?1:($typePaiement=='credit'?6:2),
                            $commandeTmp['ID_COMMANDE'],
                            $this->requete->existeParametre('PAYID')?$this->requete->getParametre('PAYID'):'',
                            $this->requete->existeParametre('STATUS')?$this->requete->getParametre('STATUS'):''
                        );
                    $ogone = true;
                }else{
                    $this->managerCom->insertCommandeLog($idCommande,$typePaiement=='ogone'?1:($typePaiement=='credit'?6:2),$commandeTmp['ID_COMMANDE']);
                }
            }else{
                $this->managerCom->validateCommandeTmp(null,$idCommande,$typePaiement,$commandeTmp['ID_COMMANDE']);
            }
            //Création de ACHAT et/ou ACHAT_ABONNEMENT
            $achats = json_decode($commandeTmp['ACHATS']);
            $arrayAbos = array();
            $arrayNums = array();
            $arrayNumsElec = array();
            $arrayArts = array();
            $arrayCredits = array();
            $this->lecturePanier($achats,$arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits);
            $this->createAchats($idCommande,$arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits,($typePaiement=='cheque_vir'?5:1), $ogone,$commandeTmp["SITE"]);

            if($typePaiement == 'cheque_vir'){
                if(count($arrayCredits) == 1 && empty($arrayAbos) && empty($arrayNums) && empty($arrayNumsElec) && empty($arrayArts)){
                    $typePanier = 'bdc_credit';
                }else{
                    $typePanier = 'bdc';
                }
            }else{
                //Recherche des infos pour déterminer quel mail envoyer et l'affichage précis de l'écran de confirmation
                $typesPanier = $this->getTypePanier($arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits);
                $typePanier = $typesPanier['typePanier'];
                $linkPanier = $typesPanier['linkPanier'];
            }

            $emailUser = $this->authInfos['U']['EMAIL'];
            $emailsCC = $this->content->getUsersCairn();
            $toBcc = array();
            foreach ($emailsCC as $emailCC){
               $toBcc[] = $emailCC['ID_USER'];
            }
            //Envoi du mail de confirmation
            switch($typePanier){
                case 'bdc' :
                case 'bdc_credit' :
                    //Construction du bon de commande à placer en attachment
                    foreach($arrayAbos as &$abo){
                        $infoSup = $abo['INFOSUP'];
                        if(strlen($infoSup) == 4 && $infoSup > 2010 && $infoSup < 2050){
                            $abo['INFOSUP'] = 'Pour l\'année '. $infoSup;
                        }else{
                            $numFrom = $this->content->getNumpublieById($infoSup)[0];
                            $abo['INFOSUP'] = 'À partir du n°'.$numFrom['NUMERO_ANNEE'].'/'.$numFrom['NUMERO_NUMERO'].($numFrom['NUMERO_VOLUME']==''?'':(' ('.$numFrom['NUMERO_VOLUME'].')'));
                        }
                    }
                    include (__DIR__."/../".Configuration::get('dirVue')."/User/bdc.php");
                    $bdcContent = getBdc($idCommande,$commandeTmp,$this->authInfos, $arrayAbos, $arrayNums, $arrayNumsElec, $arrayArts, $arrayCredits);
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirmChequeVir'.($typePanier=='bdc'?'':'Credit').'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande,
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'toBcc' => $toBcc,
                                'URLSITE' => Configuration::get('urlSite')),
                            array("Bon-de-commande.html" => $bdcContent)
                            );
                    break;
                case 'credit':
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirm'.ucfirst($typePanier).'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande,
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'toBcc' => $toBcc,
                                'CREDIT_PRIX' => $arrayCredits[0], 'CREDIT_EXPIRATION_CREDIT' => '31-12-'.(intval(date('Y'))+1),
                                'URLSITE' => Configuration::get('urlSite')));
                    break;
                case 'numero':
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirm'.ucfirst($typePanier).'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande,
                                'NUMERO_ANNEE' => $arrayNums[0]['NUMERO_ANNEE'], 'NUMERO_NUMERO' => $arrayNums[0]['NUMERO_NUMERO'],
                                'REVUE_TITRE' => $arrayNums[0]['REVUE_TITRE'], 'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'ID_REVUE' => $arrayNums[0]['NUMERO_ID_REVUE'],
                                'NUMERO_ID_NUMPUBLIE' => $arrayNums[0]['NUMERO_ID_NUMPUBLIE'], 'toBcc' => $toBcc,
                                'URLSITE' => Configuration::get('urlSite')));
                    break;
                case 'numeroElec':
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirm'.ucfirst($typePanier).'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande,
                                'NUMERO_ANNEE' => $arrayNumsElec[0]['NUMERO_ANNEE'], 'NUMERO_NUMERO' => $arrayNumsElec[0]['NUMERO_NUMERO'],
                                'REVUE_TITRE' => $arrayNumsElec[0]['REVUE_TITRE'], 'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'ID_REVUE' => $arrayNumsElec[0]['NUMERO_ID_REVUE'],
                                'NUMERO_ID_NUMPUBLIE' => $arrayNumsElec[0]['NUMERO_ID_NUMPUBLIE'], 'toBcc' => $toBcc,
                                'URLSITE' => Configuration::get('urlSite')));
                    break;
                case 'article':
                case 'articleMag':
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirm'.ucfirst($typePanier).'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande,
                                'REVUE_TITRE' => $arrayArts[0]['REVUE_TITRE'], 'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'toBcc' => $toBcc,
                                'ID_ARTICLE' => $arrayArts[0]['ARTICLE_ID_ARTICLE'],
                                'URLSITE' => Configuration::get('urlSite')));
                    break;
                case 'chapitre':
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirm'.ucfirst($typePanier).'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande,
                                'NUMERO_TITRE' => $arrayArts[0]['NUMERO_TITRE'], 'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'toBcc' => $toBcc,
                                'ID_ARTICLE' => $arrayArts[0]['ARTICLE_ID_ARTICLE'],
                                'URLSITE' => Configuration::get('urlSite')));
                    break;
                case 'abo':
                    $infoSup = $arrayAbos[0]['INFOSUP'];
                    if(strlen($infoSup) == 4 && $infoSup > 2010 && $infoSup < 2050){
                        $abonnementInfoSup = $infoSup;
                    }else{
                        $numFrom = $this->content->getNumpublieById($infoSup)[0];
                        $abonnementInfoSup = 'à partir du n°'.$numFrom['NUMERO_ANNEE'].'/'.$numFrom['NUMERO_NUMERO'].($numFrom['NUMERO_VOLUME']==''?'':(' ('.$numFrom['NUMERO_VOLUME'].')'));
                    }
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirm'.ucfirst($typePanier).'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande, 'toBcc' => $toBcc,
                                'REVUE_TITRE' => $arrayAbos[0]['TITRE'], 'TYPE_PAIEMENT' => Configuration::get('typePaiement'.ucfirst($typePaiement)),
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'ID_REVUE' => $arrayAbos[0]['ID_REVUE'],
                                'ABONNEMENT_LIBELLE' => $arrayAbos[0]['LIBELLE'], 'ABONNEMENT_INFOSUP' => $abonnementInfoSup,
                                'URLSITE' => Configuration::get('urlSite')));
                    break;
                case 'achats':
                    Service::get('Mailer')->sendMailFromTpl('/'.Configuration::get('dirVue').'/User/Mails/mailPanierConfirm'.ucfirst($typePanier).'.xml',
                            array('to' => $emailUser, 'NO_COMMANDE' => $idCommande,
                                'ID_USER' => $this->authInfos['U']['ID_USER'], 'toBcc' => $toBcc,
                                'URLSITE' => Configuration::get('urlSite')));
                    break;
            }

            //On vide le panier...
            Service::get('CairnHisto')->clearHistorique(isset($this->authInfos['U'])?'U':'G', 'panier', $this->authInfos);

            if($typePaiement == 'cheque_vir'){
                $this->genererVue(null, 'panierCheque.php','gabaritAjax.php');
            }else if($typePaiement != 'ogone'){
                $this->genererVue(array('typePanier'=>$typePanier,'linkPanier'=>$linkPanier, 'gabarit'=>$gabarit), 'panierFinalise.php',$gabarit);
            }
        }
    }

    private function getTypePanier($arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits){
        $poidPanier = 0;
        $typePanier = '';
        $linkPanier = '';
        if(!empty($arrayArts)){
            $chap = 0;
            $art = 0;
            $artMag = 0;
            foreach ($arrayArts as $article){
                if($article['REVUE_TYPEPUB'] == 1){
                    $art++;
                }else if($article['REVUE_TYPEPUB'] == 2){
                    $artMag++;
                }else{
                    $chap++;
                }
            }
            if($art > 0){
                $poidPanier += $art;
                $typePanier = 'article';
                $linkPanier = './article.php?ID_ARTICLE='.$arrayArts[0]['ARTICLE_ID_ARTICLE'];
            }
            if($artMag > 0){
                $poidPanier += $artMag;
                $typePanier = 'articleMag';
                $linkPanier = './article.php?ID_ARTICLE='.$arrayArts[0]['ARTICLE_ID_ARTICLE'];
            }
            if($chap > 0){
                $poidPanier += $chap;
                $typePanier = 'chapitre';
                $linkPanier = './article.php?ID_ARTICLE='.$arrayArts[0]['ARTICLE_ID_ARTICLE'];
            }
        }
        if(!empty($arrayNums)){
            $poidPanier += count($arrayNums);
            $typePanier = 'numero';
            $linkPanier = './revue-'.$arrayNums[0]['REVUE_URL_REWRITING']
                        .'-'.$arrayNums[0]['NUMERO_ANNEE']
                        .'-'.$arrayNums[0]['NUMERO_NUMERO'].'.htm';
        }
        if(!empty($arrayNumsElec)){
            $poidPanier += count($arrayNumsElec);
            $typePanier = 'numeroElec';
            $linkPanier = './revue-'.$arrayNumsElec[0]['REVUE_URL_REWRITING']
                        .'-'.$arrayNumsElec[0]['NUMERO_ANNEE']
                        .'-'.$arrayNumsElec[0]['NUMERO_NUMERO'].'.htm';
        }
        if(!empty($arrayAbos)){
            $poidPanier += count($arrayAbos);
            $typePanier = 'abo';
            $linkPanier = './revue-'.$arrayAbos[0]['URL_REWRITING'].'.htm';
        }
        if(!empty($arrayCredits)){
            $poidPanier += count($arrayCredits);
            $typePanier = 'credit';
            $linkPanier = './mon_credit.php';
        }
        if($poidPanier > 1){
            $typePanier = 'achats';
            $linkPanier = './mes_achats.php';
        }
        return array("typePanier"=>$typePanier, 'linkPanier'=>$linkPanier);
    }

    private function createAchats($cmdId,$arrayAbos,$arrayNums,$arrayNumsElec,$arrayArts,$arrayCredits,$statut = 1, $ogone = false, $site = 0){
        if(!empty($arrayCredits)){
            $mnt = 0;
            foreach($arrayCredits as $credit){
                $mnt+=$credit;
            }
            $mntCmd = $mnt;
            //On va regarder ce qu'il reste sur les crédits précédent non expirés pour l'ajouter au montant
            //Si on est en paiement par cheque/virement, cela se fera lors de l'activation du crédit d'article...
            if($statut == 1){
                $soldeDispo = $this->contentCom->getSoldeCreditDispo($this->authInfos['U']['ID_USER']);
                if($soldeDispo > 0){
                    $mnt += $soldeDispo;
                }
                $this->managerCom->termineCreditEnCours($this->authInfos['U']['ID_USER']);
            }
            //On crée un nouveau crédit avec le montant total
            //On préfixe le numéro de commande avec un C,
            //afin d'éviter que les autres achats dans le même panier ne soient liés à ce crédit par leur n° de commande
            $this->managerCom->insertAchatCredit('C'.$cmdId,$mnt,$mntCmd,$this->authInfos['U']['ID_USER'],$statut);
        }
        $abosCache = array();
        foreach($arrayAbos as $abo){
            $idZone = $this->content->getFraisZone($abo['ID_REVUE'],$this->authInfos['U']['PAYS']);
            $this->managerCom->insertAchatAbo($cmdId,$abo,$idZone,$this->authInfos['U']['ID_USER'],$statut);
            $abosCache[] = $abo['ID_REVUE'];
        }

        $numsCache = array();
        foreach($arrayNums as $num){
            $fp = $this->content->getFraisPort('PORT_NUM', array($num['NUMERO_ID_REVUE']), $this->authInfos['U']['PAYS']);
            $this->managerCom->insertAchatNum($cmdId,$num,($fp==null?0:$fp),$this->authInfos['U']['ID_USER'],0,$statut, $site);
            $numsCache[] = $num['NUMERO_ID_NUMPUBLIE'];
        }
        foreach($arrayNumsElec as $num){
            $this->managerCom->insertAchatNum($cmdId,$num,0,$this->authInfos['U']['ID_USER'],1,$statut, $site);
            $this->managerComMongo->incrementAchatsCounter($this->authInfos['G']['TOKEN'],1,true);
            $numsCache[] = $num['NUMERO_ID_NUMPUBLIE'];
        }

        $artsCache = array();
        foreach($arrayArts as $art){
            $this->managerCom->insertAchatArt($cmdId,$art,0,$this->authInfos['U']['ID_USER'],$statut, $site);
            $this->managerComMongo->incrementAchatsCounter($this->authInfos['G']['TOKEN'],1,true);
            $artsCache[] = $art['ARTICLE_ID_ARTICLE'];
        }

        $this->updateAccessCache('revue', $abosCache);
        $this->updateAccessCache('numero', $numsCache);
        $this->updateAccessCache('article', $artsCache);
    }



    public function administration(){
        $this->genererVue(null, 'administration.php','gabaritAdmin.php');
    }

    public function statistiquesConsultation(){
        $this->genererVue(null, 'statistiques_consultation.php','gabaritAdmin.php');
    }

    public function factures(){
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*', $this->authInfos);
        //1 On récupère les commandes
        if(isset($this->authInfos['U'])){
            $commandes = $this->contentCom->getCommandesByUser($this->authInfos['U']['ID_USER']);
            foreach($commandes as &$commande){
                $factures = array();
                if(file_exists(Configuration::get('facturePath').'htm/F'.$commande['NO_COMMANDE'].'.htm')){
                    $factures['htm'] = 'htm/F'.$commande['NO_COMMANDE'].'.htm';
                }
                if(file_exists(Configuration::get('facturePath').'pdf/F'.$commande['NO_COMMANDE'].'.pdf')){
                    $factures['pdf'] = 'pdf/F'.$commande['NO_COMMANDE'].'.pdf';
                }
                $commande['factures'] = $factures;
            }
            $this->genererVue(array('commandes' => $commandes), null, null, $headers);
        }else{
            $this->genererVue(array('restricted' => 'oui', 'connectFrom' => 'factures'), 'connect.php','gabarit.php', $headers);
        }
    }

    public function loadFacture(){
        if(isset($this->authInfos['U'])){
            $file = $this->requete->getParametre('file');

            //On vérifie que la facture se rapporte bien à l'utilisateur connecté...
            $commande = substr($file,5,strpos($file,'.')-5); //on enlève le htm/F ou le pdf/F
            $commandeTmp = $this->contentCom->getCommandeTmp($commande);
            if($commandeTmp['ID_USER'] != $this->authInfos['U']['ID_USER']){
                $this->genererVue(array('restricted' => 'oui', 'connectFrom' => 'factures'), 'connect.php','gabarit.php');
            }

            if ($this->file->loadFile(Configuration::get('facturePath').$file, 'HTM')) {
                $datas = $this->file->getContent();
            }
            $this->genererVue(array('datas' => $datas, 'fileName' => $commande = substr($file,5)), 'file.php', 'none');
        }else{
            $this->genererVue(array('restricted' => 'oui', 'connectFrom' => 'factures'), 'connect.php','gabarit.php');
        }
    }

    public function accesHors(){
        $headers = Service::get('Webtrends')->webtrendsHeaders('compte-*-acces-hors', $this->authInfos);
        if($this->requete->existeParametre('REDIRECT_Shib-Session-ID')){
            if($this->requete->existeParametre('REDIRECT_Shib-Identity-Provider')){
                $idP = $this->requete->getParametre('REDIRECT_Shib-Identity-Provider');

                if($this->requete->existeParametre('REDIRECT_persistent-id')){

                    $persistentId = $this->requete->getParametre('REDIRECT_persistent-id');

                    $shibUser = $this->contentCom->existShibUser($persistentId);
                    if($shibUser === FALSE || $shibUser['ID_SSO'] == ''){
                        $ssocas = $this->content->getSsoByUrl($idP);
                        if(!$ssocas){
                            $HTTP_REFERER = $this->requete->getParametre('HTTP_REFERER');
                            $HTTP_REFERER = explode(";",$HTTP_REFERER );
                            $HTTP_REFERER = $HTTP_REFERER[0];
                            $ssocas = $this->content->getSsoByUrl($HTTP_REFERER);
                        }
                        $instId = $ssocas['INST'];
                        $userId = $this->contentCom->getNextShibUser();
                        $userId = 'SHI_0'.substr(('00'.$userId),strlen('00'.$userId)-6);

                        $this->managerCom->createUserShib($persistentId,$ssocas['ID_SSO'],$userId);
                        $this->managerCom->createAccount(array($userId, '', '', $userId, 0, '', 'U', 0, ''));
                        $this->managerAbo->createAbonne($userId,'','');
                    }else{
                        $shibIdUser = $shibUser['ID_USER'];
                        $ssocas = $this->content->getSsoById($shibUser['ID_SSO']);
                        $instId = $ssocas['INST'];
                        $userId = $shibIdUser;
                    }
                }else{
                    //var_dump($this->requete);
                    $ssocas = $this->content->getSsoByUrl($idP);
                    if(!$ssocas){
                        $HTTP_REFERER = $this->requete->getParametre('HTTP_REFERER');
                        $HTTP_REFERER = explode(";",$HTTP_REFERER );
                        $HTTP_REFERER = $HTTP_REFERER[0];
                        $ssocas = $this->content->getSsoByUrl($HTTP_REFERER);
                    }
                    $instId = $ssocas['INST'];
                    $userId = "";
                }
                $vueMode = 0;

                if($userId != ""){
                    $sessionUser = $this->contentComMongo->validateSession($userId,$this->authInfos["G"]["TOKEN"],
                        Configuration::get('userInactivityDuration'),
                        Configuration::get('userInactivityUnit'));
                    if($sessionUser === FALSE){
                        //On éjecte...
                        $this->managerComMongo->closeOtherUserLog($userId,$this->authInfos["G"]["TOKEN"]);
                    }
                    if (Service::get('Authentification')->getToken() != null) {
                        //Si il y avait déjà des connexions existantes, on les ferme proprement
                        Service::get('Authentification')->removeAllTokenParts();
                    }

                    $token = Service::get('Authentification')->createToken($userId.'£'.$instId);

                    $this->authInfos = Service::get('Authentification')->readToken($this->requete,0);

                    setcookie('cairn_token', $token, strtotime('+'.Configuration::get('userInstSessionDuration').' '.strtolower(Configuration::get('userInstSessionUnit')).'s'),'/');

                    if(Service::get('Authentification')->getUserLogId('userILog') != null){
                        //$this->managerCom->insertUserLog(Service::get('Authentification')->getUserLogId('userILog'),$instId,
                        $this->managerComMongo->insertInstLog($this->authInfos["G"]['TOKEN'],
                            Configuration::get('userInstSessionDuration'),
                            Configuration::get('userInstSessionUnit'),
                            (isset($this->authInfos["G"]["SESSION_INST_FIRST"])&& $this->authInfos["G"]["SESSION_INST_FIRST"] != '')?0:1);
                        $this->managerComMongo->incrementInstitutionCounter($this->authInfos["G"]['TOKEN'], $instId);
                    }
                    if(Service::get('Authentification')->getUserLogId('userULog') != null){
                        //$this->managerCom->insertUserLog(Service::get('Authentification')->getUserLogId('userULog'),$userId,
                        $this->managerComMongo->insertUserLog($this->authInfos["G"]['TOKEN'],$userId,
                            Configuration::get('userSessionDuration'),
                            Configuration::get('userSessionUnit'),
                            (isset($this->authInfos["G"]["SESSION_USER_FIRST"])&& $this->authInfos["G"]["SESSION_USER_FIRST"] != '')?0:1,
                            Configuration::get('userInactivityDuration')." ".Configuration::get('userInactivityUnit'));
                    }
                }else{
                    if (Service::get('Authentification')->getToken() != null) {
                        //Si il y avait déjà des connexions existantes, on les ferme proprement
                        Service::get('Authentification')->removeAllTokenParts();
                    }

                    $token = Service::get('Authentification')->createToken($instId);

                    $this->authInfos = Service::get('Authentification')->readToken($this->requete,0);

                    setcookie('cairn_token', $token, strtotime('+'.Configuration::get('userInstSessionDuration').' '.strtolower(Configuration::get('userInstSessionUnit')).'s'),'/');

                    if(Service::get('Authentification')->getUserLogId('userILog') != null){
                        //$this->managerCom->insertUserLog(Service::get('Authentification')->getUserLogId('userILog'),$instId,
                        $this->managerComMongo->insertInstLog($this->authInfos["G"]['TOKEN'],
                            Configuration::get('userInstSessionDuration'),
                            Configuration::get('userInstSessionUnit'));
                        $this->managerComMongo->incrementInstitutionCounter($this->authInfos["G"]['TOKEN'], $instId);
                    }
                }

                $this->genererVue(array('vueMode'=>$vueMode),'accesHorsConfirm.php', null, $headers);
            }else{
                //On est en retour d'une identification shibboleth manquée (pas de matching)
                $mt = microtime();
                file_put_contents("/data/www/sites/tmp/shibboleth-noIdp-".$mt.".log",print_r($this->requete,true));
                Service::get('Mailer')->sendMailFromParams("Erreur Shibboleth (no Idp)", "shibboleth-noIdp-".$mt, "benjamin.hennon@pythagoria.com", "admin@cairn.info", "Admin Cairn Info");
                header("Location: http://".Configuration::get('urlSite')."/acces_hors.php?errShib=1");
            }
        }else{
            if($this->requete->getParametre("REQUEST_URI") == '/shibboleth/ident_sso2.php'){
                //On est en retour d'une identification shibboleth manquée (pas de session)
                //$mt = microtime();
                //file_put_contents("/data/www/sites/tmp/shibboleth-noSession-".$mt.".log",print_r($this->requete,true));
                //Service::get('Mailer')->sendMailFromParams("Erreur Shibboleth (no Session)", "shibboleth-noSession-".$mt, "benjamin.hennon@pythagoria.com", "admin@cairn.info", "Admin Cairn Info");
                header("Location: http://".Configuration::get('urlSite')."/acces_hors.php?errShib=1");
            }

            $ssosPays = $this->content->getSsosPays();
            $ssos = $this->content->getSsos();
            $ssosInt = $this->content->getSSosInt();
            $baseUrl = Configuration::get('baseShibUrl');
            $targetUrl = Configuration::get('targetShibUrl');

            $err = $this->requete->getParametre("errShib",0);

            //Modification du 16/12/2015. Dimitry Berté.
            if ($this->requete->existeParametre('SSOCAS')) {
                $idEtablissement = $this->requete->getParametre('SSOCAS');
                $url = $this->getUrlEtablissement($idEtablissement);
                if (!empty($url)) {

                    if (preg_match("/service=$/i", $url)) { //Modification du 11/01/2016. Dimitry (Cairn)
                        $url .= 'http://www.cairn.info/identSSO.php';
                    }

                    $this->setSSOInternal($url);
                    header("Location: " . $url);
                }
            }

            $this->genererVue(array('ssos' => $ssos, 'ssosInt' => $ssosInt, 'ssosPays' => $ssosPays,
                'baseUrl' => $baseUrl, 'targetUrl' => $targetUrl, 'err' => $err), null, null, $headers);
        }
    }

    public function generateAccessCacheService(){
        $this->updateSearchMode('access');
        if(Configuration::get('filterEnabled') != 1 ){
            return;
        }
        $idUser = '';
        if(isset($this->authInfos['I'])){
            $idUser = $this->authInfos['I']['ID_USER'];
            Service::get('Authentification')->genFilter($idUser);
        }
        /*if(isset($this->authInfos['U'])){
            if($idUser != ''){
                $firstFilter = $idUser;
                $idUser = $this->authInfos['U']['ID_USER'];
                Service::get('Authentification')->genFilter($idUser,Configuration::get('filterPath').'/'.$firstFilter.'.flt',1);
            }else{
                $idUser = $this->authInfos['U']['ID_USER'];
                Service::get('Authentification')->genFilter($idUser);
            }

        }*/

    }

    public function updateSearchMode($mode = null){
        if($mode == null){
            $mode = $this->requete->getParametre('mode','all');
        }
        Service::get('CairnHisto')->addToHisto('searchModeInfo', $mode, $this->authInfos);
    }

    public function generateAccessCache(){
        return;
        if(Configuration::get('filterEnabled') != 1 ){
            return;
        }
        $idUser = $this->requete->getParametre('idUser');
        $user = $this->contentCom->getUserInfos($idUser);
        if($user['TYPE'] == 'I'){

            if(!$this->redis->exists($idUser."AccessFilter")){
                $time_start = microtime(true);

                //Dans la liste des bouquets
                $licencesArticles = $this->content->getLicencesArticlesDocId($idUser,$time_start);
                $time_inter = microtime(true);
                echo "getLicencesArticlesDocId:".($time_inter - $time_start);


                //Dans la liste des achats par numéros
                $numerosAchetes = $this->contentCom->getAchatsNumeros($idUser);
                $numPublies="'".implode("','",$numerosAchetes)."'";
                $articlesFromNumeros = $this->content->getArticlesDocIdFromNumero($numPublies);
                $time_inter2 = microtime(true);
                echo "getArticlesDocIdFromNumero:".($time_inter2 - $time_inter);
                /*echo 'Par licences:<br/>';
                var_dump($licencesArticles);
                echo '<br/>######################<br/>Par acaht de numéros:<br/>';
                var_dump($articlesFromNumeros);*/
             //   $cnt = 0;
             //   $cnt += $this->redis->sadd($idUser,$licencesArticles);
                /*$time_inter3 = microtime(true);
                echo "sadd licencesarticles:".($time_inter3 - $time_inter2);*/
             //   $cnt += $this->redis->sadd($idUser,$articlesFromNumeros,0);
                /*$time_inter4 = microtime(true);
                echo "sadd articlesFromNumero:".($time_inter4 - $time_inter3);*/

                $fullList = array_merge($licencesArticles,$articlesFromNumeros);
                $cnt = count($fullList);
                $time_inter3 = microtime(true);
                echo "merge:".($time_inter3 - $time_inter2);
                $applyFilter = Configuration::get('filterPath')."/".$idUser.'.flt';
                $request = array(
                    "index" => Configuration::get('indexPath'),
                    "filterPath" => $applyFilter,
                    "docsId" => array_map('intval',$fullList),
                    "firstFilterPath" => Configuration::get('filterPath').'/cairnFreeArticles.flt',
                    "filterOperator" => "orWith"
                );
                //var_dump($request);
                $ok = $this->filter->genFilter($request);
                //var_dump($ok);
                $time_inter4 = microtime(true);
                echo "filter ok:".($time_inter4 - $time_inter3);
                $this->redis->setex($idUser."AccessFilter",$applyFilter);
                $time_inter5 = microtime(true);
                echo "redis ok:".($time_inter5 - $time_inter4);
            }
           echo $cnt;
        }else{
           //Dans la liste des achats
           /*$numerosAchetes = $this->contentCom->getAchatsNumeros($idUser);
           $numPublies="'".implode("','",$numerosAchetes)."'";
           $articlesFromNumeros = $this->content->getArticlesDocIdFromNumero($numPublies);
           //echo 'NUMPUBLIES : ';
           //var_dump($numPublies);

           $articlesAchetes = $this->contentCom->getAchatsArticles($idUser);

           //Dans la liste des abonnements
           $abos = $this->contentCom->getAchatsAbonnements($idUser);
           $revuesAbos = "";
           foreach($abos as $abo){
               $revue = $this->content->getRevuesById($abo['ID_REVUE'])[0];
               $access = Service::get('ControleAchat')->verifyAbo($abo,$revue);
               if($access && strpos($revuesAbos,("'".$abo['ID_REVUE']."'"))===FALSE){
                   $revuesAbos .= ($revuesAbos!=''?',':'')."'".$abo['ID_REVUE']."'";
               }
           }
           //echo 'REVUES ABOS : ';
           //var_dump($revuesAbos);


           $cnt = 0;
           $cnt += $this->redis->sadd($idUser,$articlesFromNumeros);
           $cnt += $this->redis->sadd($idUser,$this->contentEvidensse->getDocsId($articlesAchetes),0);
           if($revuesAbos != ''){
                $articlesAbos = $this->content->getArticlesDocIdFromRevues($revuesAbos);
                $cnt += $this->redis->sadd($idUser,$articlesAbos,0);
           }
           echo $cnt;*/
        }
    }

    public function updateAccessCache($type,$ids){
        switch($type){
            case 'revue':
                $revuesAbos="'".implode("','",$ids)."'";
                $articles = $this->content->getArticlesDocIdFromRevues($revuesAbos);
                break;
            case 'numero':
                $numPublies="'".implode("','",$ids)."'";
                $articles = $this->content->getArticlesDocIdFromNumero($numPublies);
                break;
            case 'article':
                $articles = $ids;
                break;
        }
        if(!empty($articles)){
            $cnt = $this->redis->sadd($this->authInfos['U']['ID_USER'],$articles,1);
        }
        return $cnt;
    }

    public function setSSO(){
        $idp = $this->requete->getParametre('idp');
        $sso = $this->content->getSsoByUrl($idp);
        if(!$sso){
            $idp = substr($idp,0,strpos($idp,'service=')+8);
            $sso = $this->content->getSsoByUrl($idp);
            if(!$sso){
                return;
            }else{
                setcookie('cairn_sso', $sso['ID_SSO'], time() + 900);
            }
        }
    }

    /**
     * Permet de préparer la connexion au
     * site de cairn.
     * Dimitry (Cairn), le 01/02/2016.
     */
    public function setSSOInternal($idp = null) {
        $sso = $this->content->getSsoByUrl($idp);
        if(!$sso){
            $idp = substr($idp,0,strpos($idp,'service=')+8);
            $sso = $this->content->getSsoByUrl($idp);
            if(!$sso){
                return;
            }else{
                setcookie('cairn_sso', $sso['ID_SSO'], time() + 900);
            }
        }
    }

    public function identSSO(){
        $ticket = $this->requete->getParametre('ticket');
        $ssoCasId = $this->requete->getParametre('cairn_sso');
        $sso = $this->content->getSsoById($ssoCasId);
        $url = $sso['URL_VALID'];

        //$url = "https://9990075c-cas.esidoc.fr/cas/serviceValidate?service=";
        if($ssoCasId == 'ctheque'){
            $url .= urlencode("http://".Configuration::get('urlSite')."/index.php?casid=".$ssoCasId)."&ticket=".$ticket;
        }else{
            $url .="http://".Configuration::get('urlSite')."/identSSO.php&ticket=".$ticket;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_USERPWD, "Cairn15:Pythagoria!");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        $response = (curl_exec($ch));
        //$info=curl_getinfo($ch);
        curl_close($ch);

        //print_r($info);
        if($response == '' || strpos($response, 'authenticationFailure')!== FALSE || strpos($response,'no|') !== FALSE){
            echo 'No access';
            return;
        }else{
            $this->doLogin($sso['INST'], 'I');
            //echo 'Log ok';
            $this->genererVue(array('vueMode'=>0),'accesHorsConfirm.php', null, null);
        }
    }

    public function errOgone(){
        //Méthode pour éviter que le retour d'annulation configuré dans le compte ogone ne tombe en 404
        return true;
    }

    public function codeAboPapier(){
        if(! isset($this->authInfos['U'])){
            $this->genererVue(array('restricted' => 'oui', 'modal_activate_acces' => 'oui', 'connectFrom'=>'codeAboPapier'), 'connect.php', null, null);
        }else if($this->requete->existeParametre('code_abonne')){
            $codeAbo = $this->requete->getParametre('code_abonne');
            $idRevue = $this->requete->getParametre('ID_REVUE');

            $revue = $this->content->getRevuesById($idRevue);

            $userEditeur = $this->content->getUserEditeur($revue[0]['ID_EDITEUR'],$codeAbo);
            if($userEditeur === FALSE){
                $this->genererVue(null,'codeAboPapierErreur.php', null, null);
            }else{
                $this->manager->updateUserEditeur($revue[0]['ID_EDITEUR'], $codeAbo, $this->authInfos['U']['ID_USER']);
                $this->managerCom->updateUserAbo($revue[0]['ID_EDITEUR'], $codeAbo, $this->authInfos['U']['ID_USER']);

                $this->genererVue(array("revue" => $revue[0]),'codeAboPapierConfirm.php', null, null);
            }

        }else{
            $revues = $this->content->getRevuesByType(1);
            $this->genererVue(array('revues' => $revues),'codeAboPapier.php', null, null);
        }
    }

    /**
     * Cette url va permettre
     * de récupérer l'url de l'établissement.
     * Modification du 18/12/2015, par Dimitry Berté.
     */
    private function getUrlEtablissement($idEtablissement) {
        $url = '';

        $etablissement = $this->content->getSsoById($idEtablissement);

        if (isset($etablissement['URL_LOGIN']) && !empty($etablissement['URL_LOGIN'])) {
            $url = $etablissement['URL_LOGIN'];
        }

        return $url;

    }

    /**
    * Cette méthode va permettre l'affichage
    * de la page de désinscription d'alerte,
    * avec les tags WebTrends.
    */
    public function desinscriptionAlerte() {

        //Por l'ID_NUMPUBLIE
        $getIdNumpublie = explode('WT.mc_id=', $_SERVER['QUERY_STRING']);

        $idNumPublie = $getIdNumpublie[1];

        //Récupération de l'information du numéro transmit.
        $numero = $this->content->getRevueNumeroFromId($idNumPublie);

        if (isset($numero) && !empty($numero)) {

            $revues = $this->content->getRevuesByUrl(null, $idNumPublie, $numero['REVUE_TYPEPUB']);

            $webtrendsService = Service::get('Webtrends');
            //Recherche des disciplines.
            if (in_array($numero['REVUE_TYPEPUB'], array(3, 6))) {//Pour le cas d'un ouvrage ou d'une encyclopédie de poche.
                $dataDiscipline = $this->content->getDisciplinesOfRevue($numero['NUMERO_ID_NUMPUBLIE']);
            } elseif ($numero['REVUE_TYPEPUB'] == 1) {//Pour les revues.
                $dataDiscipline = $this->content->getDisciplinesOfRevue($numero['NUMERO_ID_REVUE']);
            }

            $webtrendsTags = array_merge($webtrendsService->getTagsForNumeroPage($numero, $revues[0], $dataDiscipline),
            $webtrendsService->getTagsForAllPages('compte-*-desinscription-newsletter', $this->authInfos));

            $headers = $webtrendsService->webtrendsTagsToHeadersTags($webtrendsTags);
        }

        //Affichage de la page.
        $this->genererVue(array('revue' => $revues[0]), 'desinscriptionAlerte.php', null, $headers);
    }


    // Un utilisateur en institution souhaite suggérer l'achat d'un ouvrage à son bibliothécaire
    // TODO: Aucune information sur le succès ou non de la requête n'est envoyée au client.
    public function suggererOuvragePourInstitution() {
        if ($this->requete->getParametre('REQUEST_METHOD') !== 'POST') return;
        // On vérifie si il s'agit bien d'un utilisateur en institution
        if (!isset($this->authInfos['I'])) return;
        // On récupère les paramètres d'enregistrement
        $idNumpublie = $this->requete->getParametre('ID_NUMPUBLIE');
        $idInstitution = $this->authInfos['I']['ID_USER'];
        $idSession = $this->authInfos['G']['TOKEN'];
        $idUser = isset($this->authInfos['U']['ID_USER']) ? $this->authInfos['U']['ID_USER'] : null;
        // On s'assure que la suggestion n'a pas déjà été faite dans le passé
        $hasAlreadySubmitSuggestionForInstitution = $this->contentCom->hasAlreadySubmitSuggestionForInstitution(
            $idInstitution,
            $idSession,
            $idUser,
            $idNumpublie
        );
        if ($hasAlreadySubmitSuggestionForInstitution) return;
        // On enregistre la suggestion
        $this->managerCom->insertSuggestionOuvragePourInstitution(
            $idNumpublie,
            $idInstitution,
            $idSession,
            $idUser
        );
    }


    // On met à jour les suggestions qui ont étés faites AVANT la connexion
    private function updateSuggestionsOuvragePourInstitution() {
        if (!isset($this->authInfos['G']['TOKEN'])) return;
        if (!isset($this->authInfos['U']['ID_USER'])) return;
        $this->managerCom->updateSuggestionOuvragePourInstitutionApresLogin(
            $this->authInfos['G']['TOKEN'],
            $this->authInfos['U']['ID_USER']
        );
    }
}

