<?php

namespace App\Traits;

trait GeneratesCodes {

    /**
     * @param string $modifier
     * to modify output with the given parameter
     * @return string
     */
    public function generateRandomUniqueId($modifier='') {
        return md5($modifier.microtime());
    }

    static function slug($z, $replaceChar){
        $z = strtolower($z);
        $z = preg_replace('/[^a-z0-9 -]+/', '', $z);
        $z = str_replace(' ', $replaceChar, $z);
        return trim($z, $replaceChar);
    }

}