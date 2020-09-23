<?php
namespace App\Library;

use Phalcon\Crypt;

class Cryptography extends Crypt {

	function encryptBase64URL($text) {        
       
	  	return $this->encryptBase64($text, $this->getKey(), true);
    }

    function decryptBase64URL($text) {       
       
  		return $this->decryptBase64($text, $this->getKey(), true);
    }
}