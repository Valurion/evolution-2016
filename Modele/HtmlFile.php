<?php

/**
 * Description of HtmlFile
 *
 * @author benjamin
 */
require_once 'Framework/ModeleFichier.php';

class HtmlFile extends ModeleFichier {

    protected $content = null;

    public function loadFile($file, $type) {
        return $this->content = $this->executerRequete($file, $type);
    }

    public function getContent() {
        //On coupe le fichier HTML pour extraire les parties métadonnées et texte
        /* @todo: inspecter un cache pour voir si on a quelque chose dedans */
        $metas = substr($this->content, strpos($this->content, '<div id="from_xml_top'));
        $metas = substr($metas, 0, strpos($metas, '<div id="from_xml_bottom'));
        $contenus = substr($this->content, strpos($this->content, '<div id="from_xml_bottom'));
        $arrayRet = [
            "METAS" => $metas,
            "CONTENUS" => $contenus
        ];
        return $arrayRet;
    }

}
