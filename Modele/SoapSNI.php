<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Framework/ModeleSoap.php';

class SoapSNI extends ModeleSoap {

    public function doGetDoc($revue, $currentArticle, $authInfos) {
        $soap = $revue['SOAP'];
        $soap = substr($soap, 0, -1);
        $soap = substr($soap, 1);

        $soapInfos = explode('][', $soap);
        $login = $soapInfos[1];
        $mdp = $soapInfos[2];
        $idProd = $soapInfos[3];

        if (isset($authInfos['I']) && strpos($authInfos['I']['FACT_NOM'], '[SNI]')) {
            $sniInfos = explode('[SNI]', $authInfos['I']['FACT_NOM']);
            if ($sniInfos[0] != '') {
                $login = $sniInfos[0];
                $mdp = $sniInfos[1];
            }
        }
        $identity = $this->executerRequete('client', 'Login', array('Username' => $login, 'Password' => $mdp, 'Product_id' => $idProd));
        $soapRequestInfos = new stdClass;
        $soapRequestInfos->Name = $currentArticle["ARTICLE_ID_ARTICLE"];
        $soapRequestInfos->DocumentLanguage_id = '';
        $soapRequestInfos->Version = '';
        $soapRequestInfos->Hits = '';
        $soapRequestInfos->Relevance = '';
        $soapRequestInfos->Source = '';
        $soapRequestInfos->Date = '';
        $soapRequestInfos->Title = '';
        $soapRequestInfos->Teaser = '';
        $soapRequestInfos->Authors = '';
        $soapRequestInfos->Length = '';
        $soapRequestInfos->Program = '';
        $soapRequestInfos->WordCount = '';
        $soapRequestInfos->Status_id = '';
        $soapRequestInfos->BroadcastingTime = '';
        $soapRequestInfos->Attachments = '';
        $soapRequestInfos->DocumentUrl = '';

        $soapRequest = new stdClass;
        $soapRequest->SearchDocInfo = $soapRequestInfos;

        $soapRequestGlobal = new stdClass;
        $soapRequestGlobal->SearchDocInfoItems = $soapRequest;
        $soapResult = $this->executerRequete('clientCnt', 'GetXmlDocuments', array('Identity' => $identity->LoginResult, 'SearchDocInfos' => $soapRequestGlobal));

        $result = $soapResult->GetXmlDocumentsResult->DocumentItems->Document->Content;
        $this->executerRequete('client', 'Logout', array('Identity' => $identity->LoginResult));

        $xmlContent = simplexml_load_string($result);
        $docId = $xmlContent->xpath('//doc-id')[0];
        foreach ($docId->attributes() as $a => $b) {
            if ($a == 'id-string') {
                $publicCopy = $b;
            }
        }
        $copyright = $xmlContent->xpath('//copyrite')[0];

        include (Configuration::get('cairn_includes_path') . '/showfront.php');
        $front = ShowConvertCairn($revue["REVUE_ID_REVUE"], $revue["NUMERO_ID_NUMPUBLIE"], $currentArticle["ARTICLE_ID_ARTICLE"], $result);

        $contenus = $front[1];
        $contenus .= '<br>'
                . '<p class="copymag">' . $copyright . '</p>'
                . '<p class="copymag"><img src="img/public.jpg"> ' . $publicCopy . '</p>';

        $arrayRet = [
            "METAS" => $front[0],
            "CONTENUS" => $contenus
        ];
        return $arrayRet;
    }

}
