<?php
/**
 * pukoframework
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @package	puko/framework
 * @author	Didit Velliz
 * @link	https://github.com/velliz/pukoframework
 * @since	Version 0.9.3
 *
 */
namespace pukoframework\auth;

class Session
{
    private $method;
    private $key;
    private $identifier;
    private $authentication;

    public static $session;

    private function __construct(Auth $authentication)
    {
        if(is_object(self::$session)) return;
        $secure = ROOT . "/config/encryption.php";
        if(!file_exists($secure)) throw new \Exception("Authentication configuration file not found.");
        $secure = include $secure;
        $this->key = $secure['key'];
        $this->method = $secure['method'];
        $this->identifier = $secure['identifier'];
        $this->authentication = $authentication;
    }

    public static function Get(Auth $authentication)
    {
        if(is_object(self::$session)) return self::$session;
        return self::$session = new Session($authentication);
    }

    public static function GenerateSecureToken()
    {
        if (function_exists('mcrypt_create_iv')) $token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        else $token = bin2hex(openssl_random_pseudo_bytes(32));
        setcookie('token', $token, time() + (86400 * 30), '/', $_SERVER['SERVER_NAME']);
        $_COOKIE['token'] = $token;
    }

    private function Encrypt($string)
    {
        $key = hash('sha256', $this->key);
        $iv = substr(hash('sha256', $this->identifier), 0, 16);
        $output = openssl_encrypt($string, $this->method, $key, 0, $iv);
        return base64_encode($output);
    }

    private function Decrypt($string)
    {
        $key = hash('sha256', $this->key);
        $iv = substr(hash('sha256', $this->identifier), 0, 16);
        return openssl_decrypt(base64_decode($string), $this->method, $key, 0, $iv);
    }

    public function PutSession($key, $val)
    {
        setcookie($key, $this->encrypt($val), time() + (86400 * 30), "/", $_SERVER['SERVER_NAME']);
    }

    public function GetSession($val){
        if (!isset($_COOKIE[$val])) return false;
        return $this->decrypt($_COOKIE[$val]);
    }

    public static function RemoveSession($key)
    {
        setcookie($key, '', time() - (86400 * 30), '/', $_SERVER['SERVER_NAME']);
    }

    public static function IsSession()
    {
        if (isset($_COOKIE['puko'])) return true;
        return false;
    }

    public static function ClearSession()
    {
        setcookie('puko', '', time() - (86400 * 30), '/', $_SERVER['SERVER_NAME']);
        $_COOKIE['puko'] = null;
    }

    #region authentication
    public function Login($username, $password)
    {
        $secure = $this->authentication->Login($username, $password);
        if($secure == false || $secure == null) return false;
        $secure = $this->encrypt($secure);
        setcookie('puko', $secure, time() + (86400), "/", $_SERVER['SERVER_NAME']);
        $_COOKIE['puko'] = $secure;
        return true;
    }

    public function Logout()
    {
        $this->ClearSession();
        $secure = $this->authentication->Logout();
        if($secure == false || $secure == null) return false;
        return true;
    }

    public function GetLoginData()
    {
        if (!isset($_COOKIE['puko'])) return false;
        return $this->authentication->GetLoginData($this->decrypt($_COOKIE['puko']));
    }
    #end region authentication
}