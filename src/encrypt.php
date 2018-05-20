<?php

class Crypt {

    private $secretkey = '4d3e81da-79d4-4572-8779-86cd7e75f5e7';

    //Encrypts a string
    public function encrypt($text) {
        $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->secretkey, $text, MCRYPT_MODE_ECB, 'keee');
        return base64_encode($data);
    }
    
    public function encryptWithKey($text, $key){
        $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB, 'keee');
        return base64_encode($data);
    }

    //Decrypts a string
    public function decrypt($text) {
        $text = base64_decode($text);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->secretkey, $text, MCRYPT_MODE_ECB, 'keee');
    }
    
    public function decryptWithKey($text, $key) {
        $text = base64_decode($text);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB, 'keee');
    }

}
