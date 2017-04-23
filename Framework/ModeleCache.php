<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Configuration.php';

abstract class ModeleCache {

    private static $redis= array();
    protected $current = 0;


    protected static function getConnection($current) {
        try{
            if (!isset(self::$redis[$current])) {
                self::$redis[$current] = new Redis();
                self::$redis[$current]->connect(Configuration::get('redis_server'), Configuration::get('redis_port'), 1);
                self::$redis[$current]->select($current);
            }
            return self::$redis[$current];
        }catch(Exception $e){
            return false;
        }
    }

    protected function selectDb($current) {
        $this->current = $current;
    }

}
