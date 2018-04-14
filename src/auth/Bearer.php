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

use pukoframework\config\Config;

/**
 * Class Bearer
 * @package pukoframework\auth
 *
 */
class Bearer
{
    private $method;
    private $key;
    private $identifier;
    private $authentication;

    private static $bearer;
    public static $bearerObject;

    private function __construct(Auth $authentication)
    {
        if (is_object(self::$bearerObject)) {
            return;
        }

        $secure = Config::Data('encryption');

        $this->key = $secure['key'];
        $this->method = $secure['method'];
        $this->identifier = $secure['identifier'];

        self::$bearer = $secure['session'];

        $this->authentication = $authentication;
    }

    public static function Get(Auth $authentication)
    {
        if (is_object(self::$bearerObject)) {
            return self::$bearerObject;
        }
        return self::$bearerObject = new Bearer($authentication);
    }

    public static function GenerateSecureToken()
    {
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(32));
        } else {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
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

    public function PutSession($key, $val)
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

    public static function RemoveSession($key)
    {
        $_SESSION[$key] = '';
    }

    public static function IsSession()
    {
        $secure = Config::Data('encryption');
        if (isset($_SESSION[$secure['session']])) {
            return true;
        }
        return false;
    }

    public static function IsHasPermission($code)
    {
        $secure = Config::Data('encryption');
        $key = $secure['key'];
        $method = $secure['method'];
        $identifier = $secure['identifier'];

        $string = $_SESSION['x_' . $secure['session']];

        $key = hash('sha256', $key);
        $iv = substr(hash('sha256', $identifier), 0, 16);
        $permission_array = json_decode(openssl_decrypt(base64_decode($string), $method, $key, 0, $iv), true);

        if (count($permission_array) === 0) {
            return false;
        }

        if (!array_diff($permission_array, explode(' ', $code))) {
            return true;
        }
        return false;
    }

    public static function ClearSession()
    {
        $_SESSION[self::$bearer] = null;
        $_SESSION['x_' . self::$bearer] = null;
        session_destroy();
    }

    #region authentication
    public function Login($username, $password)
    {
        $secure = $this->authentication->Login($username, $password);
        if ($secure == false || $secure == null) {
            return false;
        }
        $secure = $this->Encrypt($secure);
        $_SESSION[self::$bearer] = $secure;
        return true;
    }

    public function SetPermission($data = array())
    {
        $permission = $this->Encrypt(json_encode($data));
        $_SESSION['x_' . self::$bearer] = $permission;
        return true;
    }

    public function Logout()
    {
        $secure = $this->authentication->Logout();
        $this->ClearSession();
        if ($secure == false || $secure == null) {
            return false;
        }
        return true;
    }

    public function GetLoginData()
    {
        if (!isset($_SESSION[self::$bearer])) {
            return false;
        }
        return $this->authentication->GetLoginData($this->Decrypt($_SESSION[self::$bearer]));
    }
    #end region authentication
}