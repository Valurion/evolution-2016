<?php

require_once 'Framework/ModeleCache.php';

class RedisClient extends ModeleCache {

    function __construct($db = null) {
        if($db != null){
            $this->selectDb($db);
        }else if(Configuration::get('redis_db') != null){
            $this->selectDb(Configuration::get('redis_db'));
        }
    }

    public function exists($key) {
        try {
            $conn = $this->getConnection($this->current);
            if($conn){
                return $conn->exists(json_encode($key, JSON_UNESCAPED_UNICODE));
            }else{
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function get($key) {
        try {
            //echo 'getting '.$key.'<br/>';
            return $this->getConnection($this->current)->get(json_encode($key, JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            return false;
        }
    }

    public function setex($key, $value) {
        try {
            //echo 'setting '.$key.'<br/>';
            return $this->getConnection($this->current)->setex(json_encode($key, JSON_UNESCAPED_UNICODE), Configuration::get('redis_expire'), json_encode($value, JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            return false;
        }
    }

    public function sadd($key, $values, $setTimeOut = 1) {
        try {
            $cnt = 0;
            /* foreach($values as $value){
              $cnt += $this->getConnection()->sadd(strtolower(json_encode($key,JSON_UNESCAPED_UNICODE)), $value);
              //$cnt += $this->getConnection()->lpush(strtolower(json_encode($key,JSON_UNESCAPED_UNICODE)), $value);
              } */

            /* $arrays1000 = array_chunk($values, 1000);
              $redis = $this->getConnection();
              foreach($arrays1000 as $value1000){
              array_unshift($value1000, strtolower(json_encode($key,JSON_UNESCAPED_UNICODE)));
              //var_dump($value1000);
              $cnt += call_user_func_array(array($redis, "sadd"), $values);
              } */
            if (!empty($values)) {
                array_unshift($values, json_encode($key, JSON_UNESCAPED_UNICODE));
                $redis = $this->getConnection($this->current);
                //var_dump($values);
                $cnt += call_user_func_array(array($redis, "sadd"), $values);
                //var_dump($ret);
            } else {
                if (!$this->exists($key)) {
                    $this->getConnection($this->current)->sadd(json_encode($key, JSON_UNESCAPED_UNICODE), "-");
                }
            }

            if ($setTimeOut == 1) {
                $this->getConnection($this->current)->expire(json_encode($key, JSON_UNESCAPED_UNICODE), Configuration::get('redis_expire'));
            }
            return $cnt;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function smembers($key) {
        try {
            return $this->getConnection($this->current)->smembers(json_encode($key, JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            return false;
        }
    }

    public function scard($key) {
        try {
            return $this->getConnection($this->current)->scard(json_encode($key, JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($key) {
        try {
            return $this->getConnection($this->current)->del(json_encode($key, JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            return false;
        }
    }

}
