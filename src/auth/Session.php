<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 0.9.3
 */

namespace pukoframework\auth;

use Exception;
use pukoframework\config\Config;

/**
 * Class Session
 * @package pukoframework\auth
 *
 */
class Session
{
    private $method;
    private $key;
    private $identifier;
    private $authentication;

    private static $session;
    public static $sessionObject;

    private function __construct(Auth $authentication)
    {
        if (is_object(self::$sessionObject)) {
            return;
        }

        $secure = Config::Data('encryption');

        $this->key = $secure['key'];
        $this->method = $secure['method'];
        $this->identifier = $secure['identifier'];

        $this->authentication = $authentication;
    }

    public static function Get(Auth $authentication)
    {
        $secure = Config::Data('encryption');
        session_name($secure['session']);
        session_start();

        if (is_object(self::$sessionObject)) {
            return self::$sessionObject;
        }
        return self::$sessionObject = new Session($authentication);
    }

    /**
     * @param null $token
     * @return null|string
     * @throws \Exception
     */
    public static function GenerateSecureToken($token = null)
    {
        if ($token === null) {
            if (function_exists('random_bytes')) {
                $token = bin2hex(random_bytes(32));
            } else {
                $token = bin2hex(openssl_random_pseudo_bytes(32));
            }
        }
        $_SESSION['token'] = $token;
        return $token;
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

    public function Put($key, $val)
    {
        $_SESSION[$key] = $this->Encrypt($val);
    }

    public function GetSession($val)
    {
        if (!isset($_SESSION[$val])) {
            return false;
        }
        return $this->Decrypt($_SESSION[$val]);
    }

    public static function Remove($key)
    {
        $_SESSION[$key] = '';
    }

    public static function Is()
    {
        $secure = Config::Data('encryption');
        if (isset($_SESSION[$secure['session']])) {
            return true;
        }
        return false;
    }

    public static function Clear()
    {
        $_SESSION[self::$session] = null;
        session_destroy();
    }

    #region authentication

    /**
     * @param $username
     * @param $password
     * @return bool
     * @throws Exception
     */
    public function Login($username, $password)
    {
        $loginObject = $this->authentication->Login($username, $password);
        if (!$loginObject instanceof PukoAuth) {
            throw new Exception('Auth must be object of PukoAuth instance');
        }
        if ($loginObject->secure === null) {
            return false;
        }

        $data = array(
            'secure' => $loginObject->secure,
            'permission' => $loginObject->permission,
        );

        $secure = $this->Encrypt(json_encode($data));
        $_SESSION[self::$session] = $secure;
        return true;
    }

    public function Logout()
    {
        $secure = $this->authentication->Logout();
        $this->Clear();
        if ($secure == false || $secure == null) {
            return false;
        }
        return true;
    }

    public function GetLoginData()
    {
        if (!isset($_SESSION[self::$session])) {
            return false;
        }

        $data = json_decode($this->Decrypt($_SESSION[self::$session]));
        return $this->authentication->GetLoginData($data['secure'], $data['permission']);
    }
    #end region authentication
}