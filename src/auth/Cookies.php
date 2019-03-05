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
    private $expiredText;
    private $errorText;

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
        $this->expiredText = $secure['expiredText'];
        $this->errorText = $secure['errorText'];

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

    public static function Clear()
    {
        setcookie(self::$cookies, '', (time() - Auth::EXPIRED_1_MONTH), '/');
        $_COOKIE[self::$cookies] = null;
    }

    #region authentication

    /**
     * @param $username
     * @param $password
     * @param int $expired
     * @return bool
     * @throws Exception
     */
    public function Login($username, $password, $expired = Auth::EXPIRED_1_HOUR)
    {
        $loginObject = $this->authentication->Login($username, $password);
        if (!$loginObject instanceof PukoAuth) {
            throw new Exception('Auth must be object of PukoAuth instance');
        }
        if ($loginObject->secure === null) {
            return false;
        }

        if ($expired !== null) {
            $expired = (time() + $expired);
        }

        $data = array(
            'secure' => $loginObject->secure,
            'permission' => $loginObject->permission,
        );
        $secure = $this->Encrypt(json_encode($data));
        setcookie(self::$cookies, $secure, $expired, "/");
        $_COOKIE[self::$cookies] = $secure;
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

    /**
     * @return mixed
     * @throws Exception
     */
    public function GetLoginData()
    {
        if (!isset($_COOKIE[self::$cookies])) {
            throw new Exception($this->errorText);
        }

        $data = json_decode($this->Decrypt($_COOKIE[self::$cookies]), true);
        return $this->authentication->GetLoginData($data['secure'], $data['permission']);
    }
    #end region authentication
}