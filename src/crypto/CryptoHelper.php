<?php
include_once '../api-rest/src/config/EnvironmentVariables.php';

final class CryptoHelper
{
    private $php_metodo = "";
    private $php_secre_key = "";
    private $php_secre_iv = "";

    private $keey = "";
    private $iiv = "";
    private $iiv_ = "";
    private $keey_ = "";
    private $valor_ = false;
    private $openssl_raw_data = 0; //OPENSSL_RAW_DATA==1 ò 0
    private $envVariables;

    public function __construct() {

        $this->envVariables = new EnvironmentVariables();
        $this->php_metodo = $this->envVariables->getEncrMethod();
        $this->php_secre_key = $this->envVariables->getEncrSecretKey();
        $this->php_secre_iv = $this->envVariables->getEncrSecretIv();
        

        $this->keey = hash('sha256', $this->php_secre_key, $this->valor_);
        $this->iiv = substr(hash('sha256', $this->php_secre_iv, $this->valor_), 0, 16);

        $this->keey_ = hash('sha256', $this->php_secre_key, true); // ✅ 32 bytes
        $this->iiv_ = substr(hash('sha256', $this->php_secre_iv, true), 0, 16); // ✅ 16 bytes
    }

    // Función para desemcriptar
    public function desencriptar($string)
    {
        $output = openssl_decrypt(base64_decode($string), $this->php_metodo, $this->keey_, true, $this->iiv_);
        return $output;
    }    

    public function encriptar($string) {        
        $output = openssl_encrypt($string, $this->php_metodo, $this->keey_, true, $this->iiv_);
        $output = base64_encode($output);

        return $output;
    }
}
