<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Framework/ModeleJsonRPC.php';

class Filter extends ModelejsonRPC {

    function __construct() {
        $this->selectCurrent('filter');
    }

    public function genFilter($request) {
        try {
            return $this->executerRequete('genFilter', $request);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function genFilterByDocID($request) {
        try {
            return $this->executerRequete('genFilterByDocID', $request);
        } catch (Exception $e) {
            return false;
        }
    }

}
