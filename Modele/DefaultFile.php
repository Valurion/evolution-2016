<?php

require_once 'Framework/ModeleFichier.php';

class DefaultFile extends ModeleFichier {

    protected $content = null;

    public function loadFile($file, $type) {
        return $this->content = $this->executerRequete($file, $type);
    }

    public function getContent() {
        return $this->content;
    }

}
