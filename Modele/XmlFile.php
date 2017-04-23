<?php

require_once 'Framework/ModeleFichier.php';

/**
 * Description of XmlFile
 *
 * @author benjamin
 */
class XmlFile extends ModeleFichier {

    protected $content = null;
    protected $xpath = null;

    public function loadFile($file, $type) {
        $this->content = $this->executerRequete($file, $type);
        $this->xpath = new DOMXpath($this->content);
    }

    public function getByXPath($element, $attribute = null) {
        return $this->xpath->query('//' . $element . ($attribute != NULL ? '[@' . $attribute . ']' : ''));
    }

    public function getAuteur() {
        $return = $this->getByXPath('auteur');
        $arrRet = array();
        foreach ($return as $nodes) {
            $key = $nodes->getElementsByTagName('prenom')->item(0)->nodeValue . " " . $nodes->getElementsByTagName('nomfamille')->item(0)->nodeValue;
            $courriel = $nodes->getElementsByTagName('courriel')->item(0)->textContent;
            //$affiliation = $nodes->getElementsByTagName('affiliation')->item(0)->textContent;
            $affiliation = $this->content->saveXML($nodes->getElementsByTagName('affiliation')->item(0));

            $arrayRet[$key] = [
                'COURRIEL' => $courriel,
                'AFFILIATION' => $affiliation
            ];
        }
        return $arrayRet;
    }

}

?>
