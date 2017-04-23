<?php

/**
 * 
 * Crypte / décrypte un utlisateur Cairn.
 * On peut envisager si besoin de placer dans le token des informations plus "élaborées" que l'id, nom et prénom
 * 
 * @todo : prévoir un retour d'erreur lisible plutôt que seulement false
 */
class CairnToken {

    const PRIVATE_KEY = 'Pte chaîne connue de nous slt';

    //const EXPIRES = 480; //8h de validité par défaut

    /**
     * crypte l'array passé en paramètre, avec une expiration, spécifiée ou par défaut
     */
    public static function encode($toEncode /* , $expires = self::EXPIRES */) {
        $add = Generic_Tokenizer::pbkdf2($toEncode, self::PRIVATE_KEY, 1000, Generic_Tokenizer::IV_LENGTH);
        return md5($toEncode . $add);
        //$toEncode['validUntil'] = time() + $expires;
        //return Generic_Tokenizer::tokenize($toEncode, self::PRIVATE_KEY, true);
    }

    public static function compare($token, $toCompare) {
        $add = Generic_Tokenizer::pbkdf2($toCompare, self::PRIVATE_KEY, 1000, Generic_Tokenizer::IV_LENGTH);
        if (md5($toCompare . $add) == $token) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function crypt($toEncode, $expires = null) {
        if($expires !== null){
            $toEncode['validUntil'] = time() + $expires;
        }
        return Generic_Tokenizer::tokenize($toEncode, self::PRIVATE_KEY, true);
    }

    public static function decrypt($token) {
        $decrypted = Generic_Tokenizer::detokenize($token, self::PRIVATE_KEY, true);
        if (isset($decrypted['validUntil']) && (time() > (int) $decrypted['validUntil'])) {
            return false;
        }
        if (isset($decrypted['validUntil'])){
            unset($decrypted['validUntil']);
        }
        return $decrypted;
    }
    
    public static function cryptMp($toEncode /* , $expires = self::EXPIRES */) {
        return Generic_Tokenizer::cryptMp($toEncode, self::PRIVATE_KEY, true);
    }

    public static function compareMp($token, $toCompare) {
        $add = Generic_Tokenizer::cryptMp($toCompare, self::PRIVATE_KEY, true);
        if ($add == $token) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public static function decryptMp($token /* , $expires = self::EXPIRES */) {
        return Generic_Tokenizer::decryptMp($token, self::PRIVATE_KEY, true);
    }

}

/**
 * Classe standard...
 */
class Generic_Tokenizer {

    const ALGORITHM = MCRYPT_RIJNDAEL_256;
    const MODE = 'ctr';
    const IV_LENGTH = 32;
    const IV = '01234567890123456789012345678901';

    public static function tokenize($toEncrypt, $privateKey, $base64 = false) {

        if (false === function_exists('mcrypt_module_open')) {
            throw new Exception('MCrypt missing');
        }

        if (false === ($td = mcrypt_module_open(self::ALGORITHM, '', self::MODE, ''))) {
            return false;
        }

        $toEncrypt = serialize($toEncrypt);
        $iv = mcrypt_create_iv(self::IV_LENGTH, MCRYPT_RAND);

        if (0 !== mcrypt_generic_init($td, $privateKey, $iv)) {
            return false;
        }

        $toEncrypt = $iv . mcrypt_generic($td, $toEncrypt);
        $mac = self::pbkdf2($toEncrypt, $privateKey, 1000, self::IV_LENGTH);
        $toEncrypt .= $mac;

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        if ($base64) {
            $toEncrypt = base64_encode($toEncrypt);
        }

        return $toEncrypt;
    }

    public static function detokenize($token, $privateKey, $base64 = false) {
        if (false !== $base64) {
            $token = base64_decode($token);
        }

        if (false === function_exists('mcrypt_module_open')) {
            throw new Exception('MCrypt missing');
        }

        if (false === ($td = mcrypt_module_open(self::ALGORITHM, '', self::MODE, ''))) {
            return false;
        }

        $iv = substr($token, 0, self::IV_LENGTH);
        $macOffset = strlen($token) - self::IV_LENGTH;
        $macExtract = substr($token, $macOffset);
        $token = substr($token, self::IV_LENGTH, strlen($token) - 64);
        $mac = self::pbkdf2($iv . $token, $privateKey, 1000, self::IV_LENGTH);

        if ($macExtract !== $mac) {
            return false;
        }

        if (0 !== mcrypt_generic_init($td, $privateKey, $iv)) {
            return false;
        }

        $token = mdecrypt_generic($td, $token);
        $token = unserialize($token);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $token;
    }

    public static function pbkdf2($secret, $salt, $count, $keyLength, $algo = 'sha256') {
        $hashLength = strlen(hash($algo, null, true));
        $keyBlocks = ceil($keyLength / $hashLength);
        $derivedKey = '';

        for ($block = 1; $block <= $keyBlocks; ++$block) {
            $iterateBlock = $b = hash_hmac($algo, $salt . pack('N', $block), $secret, true);
            for ($i = 1; $i < $count; ++$i) {
                $iterateBlock ^= ($b = hash_hmac($algo, $b, $secret, true));
            }
            $derivedKey .= $iterateBlock;
        }

        return substr($derivedKey, 0, $keyLength);
    }
    
    public static function cryptMp($toEncrypt, $privateKey, $base64 = false) {

        if (false === function_exists('mcrypt_module_open')) {
            throw new Exception('MCrypt missing');
        }

        if (false === ($td = mcrypt_module_open(self::ALGORITHM, '', self::MODE, ''))) {
            return false;
        }
        
        if (0 !== mcrypt_generic_init($td, $privateKey, self::IV)) {
            return false;
        }

        $toEncrypt = mcrypt_generic($td, $toEncrypt);  
       
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        if ($base64) {
            $toEncrypt = base64_encode($toEncrypt);
        }

        return $toEncrypt;
    }
    
    public static function decryptMp($token, $privateKey, $base64 = false) {
        if (false !== $base64) {
            $token = base64_decode($token);
        }

        if (false === function_exists('mcrypt_module_open')) {
            throw new Exception('MCrypt missing');
        }

        if (false === ($td = mcrypt_module_open(self::ALGORITHM, '', self::MODE, ''))) {
            return false;
        }

        if (0 !== mcrypt_generic_init($td, $privateKey, self::IV)) {
            return false;
        }

        $token = mdecrypt_generic($td, $token);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $token;
    }

}

?>
