<?php

require_once __DIR__ . '/PHPMailer/PHPMailerAutoload.php';

/**
 * Description of Mailer
 *
 * @author benjamin
 */
class Mailer {

    private $host = 'localhost';
    private $port = 25;

    function __construct() {
        $this->host = Configuration::get("mailer_host");
        $this->port = Configuration::get("mailer_port");
    }

    public function sendMailFromTpl($tpl, $arrayParams, $arrayAttachs = array()) {
        if (file_exists(__DIR__ . "/.." . $tpl)) {
            $mail_contents = simplexml_load_file(__DIR__ . "/.." . $tpl);
            $params = array();
            foreach ($arrayParams as $key => $value) {
                $params[$key] = $value;
            }
            foreach ($mail_contents->children() as $childName => $childValue) {
                //On boucle sur l'arrayParams pour les éventuels remplacements
                foreach ($arrayParams as $key => $value) {
                    $childValue = str_replace('[' . strtoupper($key) . ']', $value, $childValue);
                }
                $params[$childName] = $childValue;
            }

            $this->sendMail($params, $arrayAttachs);
        }
    }

    public function sendMailFromParams($subject, $body, $to, $from, $fromName, $arrayAttachs=null){
        $arrayAttachs = $arrayAttachs === null ? array() : $arrayAttachs;
        $this->sendMail([
            'subject' => $subject,
            'body'  => $body,
            'to'    => $to,
            'from'  => $from,
            'fromName' => $fromName
        ], $arrayAttachs);
    }

    private function sendMail($params, $arrayAttachs) {
        $mail = new PHPMailer;

        $mail->Host = $this->host;
        $mail->Port = $this->port;

        if (Configuration::get('fromAddress') != null && (!isset($params['IS_USED_FROM']))) {
            $mail->From = Configuration::get('fromAddress');
        } else {
            $mail->From = $params["from"];
        }
        if (isset($params["fromName"])) {
            $mail->FromName = $params["fromName"];
        }

        // Dans le code fourni, il n'était pas possible d'ajouter le nom des destinataires.
        // Pour conserver une forme plus ou moins normée, on commence par écrire le nom de la personne
        // puis l'email entre chevrons.
        // Si les chevrons ne sont pas fournis, les adresses seront considérés comme des emails (comme le code d'origine)
        $adresses = explode(',', $params['to']);
        foreach ($adresses as $adresse) {
            // Les templates mails sont des xmls. Pour conserver les chevrons, ils faut les échapper
            $adresse = html_entity_decode($adresse);
            $email = null;
            $name = null;
            if (preg_match('/^([^<]+)<([^>]+)>\s*$/', $adresse, $args)) {
                $name = $args[1];
                $email = $args[2];
            } else {
                $email = $adresse;
            }
            $mail->addAddress($email, $name);
        }

        if (isset($params["toCc"])) {
            foreach($params['toCc'] as $cc){
                $mail->addCC($cc);
            }
        }
        if (isset($params["toBcc"])) {
            foreach($params['toBcc'] as $bcc){
                $mail->addBCC($bcc);
            }
        }

        if (isset($params["isHtml"])) {
            $mail->isHTML($params["isHtml"]);
        }
        $mail->CharSet = 'utf-8';
        $mail->Subject = $params["subject"];
        $mail->Body = $params["body"];
        if (isset($params["altBody"])) {
            $mail->AltBody = implode(",", $params["altBody"]);
        }

        foreach ($arrayAttachs as $key => $value) {
            $mail->addStringAttachment($value, $key);
        }

        if (!$mail->send()) {
            //TODO : faire remonter l'erreur...
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            //echo 'Message has been sent';
        }
    }

}
