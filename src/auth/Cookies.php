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
 * Class Cookies
 * @package pukoframework\auth
 *
 */
class Cookies
{
    private $method;
    private $key;
    private $identifier;
    private $authentication;

    private static $cookies;
    public static $cookiesObject;

    private function __construct(Auth $authentication)
    {
        if (is_object(self::$cookiesObject)) {
            return;
        }

        $secure = Config::Data('encryption');

        $this->key = $secure['key'];
        $this->method = $secure['method'];
        $this->identifier = $secure['identifier'];

        self::$cookies = $secure['cookies'];

        $this->authentication = $authentication;
    }

    public static function Get(Auth $authentication)
    {
        if (is_object(self::$cookiesObject)) {
            return self::$cookiesObject;
        }
        return self::$cookiesObject = new Cookies($authentication);
    }

    public static function GenerateSecureToken()
    {
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(32));
        } else {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
        }
        setcookie('token', $token, time() + Auth::EXPIRED_1_DAY, '/');
        $_COOKIE['token'] = $token;
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

    public function Put($key, $val, $expired = Auth::EXPIRED_1_HOUR)
    {
        if ($expired !== null) {
            $expired = (time() + $expired);
        }
        setcookie($key, $this->Encrypt($val), $expired, "/");
        $_COOKIE[$key] = $this->Encrypt($val);
    }

    public function GetCookies($val)
    {
        if (!isset($_COOKIE[$val])) {
            return false;
        }
        return $this->Decrypt($_COOKIE[$val]);
    }

    public static function Remove($key)
    {
        setcookie($key, '', (time() - Auth::EXPIRED_1_MONTH), '/');
        $_COOKIE[$key] = '';
    }

    public static function Is()
    {
        $secure = Config::Data('encryption');
        if (isset($_COOKIE[$secure['cookies']])) {
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

        $string = $_COOKIE['x_' . $secure['cookies']];

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

    public static function Clear()
    {
        setcookie(self::$cookies, '', (time() -  Auth::EXPIRED_1_MONTH), '/');
        $_COOKIE[self::$cookies] = null;
        setcookie('x_' . self::$cookies, '', (time() - Auth::EXPIRED_1_MONTH), '/');
        $_COOKIE['x_' . self::$cookies] = null;
    }

    #region authentication
    public function Login($username, $password, $expired = Auth::EXPIRED_1_HOUR)
    {
        if ($expired !== null) {
            $expired = (time() + $expired);
        }
        $secure = $this->authentication->Login($username, $password);
        if ($secure == false || $secure == null) {
            return false;
        }
        $secure = $this->Encrypt($secure);
        setcookie(self::$cookies, $secure, $expired, "/");
        $_COOKIE[self::$cookies] = $secure;
        return true;
    }

    public function SetPermission($data = array(), $expired = Auth::EXPIRED_1_HOUR)
    {
        if ($expired !== null) {
            $expired = (time() + $expired);
        }
        $permission = $this->Encrypt(json_encode($data));
        setcookie('x_' . self::$cookies, $permission, $expired, "/");
        $_COOKIE['x_' . self::$cookies] = $permission;
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
        if (!isset($_COOKIE[self::$cookies])) {
            return false;
        }
        return $this->authentication->GetLoginData($this->Decrypt($_COOKIE[self::$cookies]));
    }
    #end region authentication
}