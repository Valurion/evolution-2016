<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Framework/ModeleJsonRPC.php';

class Search extends ModelejsonRPC {

    public function doSearch($request) {
        return $this->executerRequete('myCpl', $request);
    }

    public function doGetDoc($request) {
        $result = $this->executerRequete('getDoc', $request);
        //$result = html_entity_decode($result);

        $metas = substr($result, strpos($result, '<div id="from_xml_top'));
        $metas = substr($metas, 0, strpos($metas, '<div id="from_xml_bottom'));
        $contenus = substr($result, strpos($result, '<div id="from_xml_bottom'));
        $arrayRet = [
            "METAS" => $metas,
            "CONTENUS" => $contenus
        ];
        return $arrayRet;
    }

    public function doGetHilightPdf($request) {
        $datas = $this->executerRequete('getHilightPdf', $request);

        $datas = str_replace(Chr(13), '', $datas);
        $datas = str_replace(Chr(10), '', $datas);
        $datas = str_replace('<', '&lt;', $datas);
        $datas = str_replace('>', '&gt;', $datas);

        $datas = preg_replace('/units=([a-z0-9]*)\scolor=([#a-f0-9]*)\smode=([a-z]*)\sversion=([0-9]*)\&gt;/', 'units="$1" color="$2" mode="$3" version="$4"&gt;', $datas);
        $datas = preg_replace('/pg=([0-9]*)\spos=([0-9]*)\slen=([0-9]*)\&gt;/', 'pg="$1" pos="$2" len="$3"&gt;&lt;/loc&gt;', $datas);
        //var_dump($datas);
        return $datas;
    }

}
