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

use DateTime;
use Exception;
use pukoframework\config\Config;

/**
 * Class Session
 * @package pukoframework\auth
 */
class Session
{
    private $method;
    private $key;
    private $identifier;
    private $authentication;
    private $session;
    private $expired;
    private $expiredText;
    private $errorText;

    public static $sessionObject;

    /**
     * Session constructor.
     * @param Auth $authentication
     * @throws Exception
     */
    private function __construct(Auth $authentication)
    {
        if (is_object(self::$sessionObject)) {
            return;
        }
        $secure = Config::Data('encryption');
        $this->key = $secure['key'];
        $this->method = $secure['method'];
        $this->identifier = $secure['identifier'];
        $this->session = $secure['session'];
        $this->expiredText = $secure['expiredText'];
        $this->expired = isset($secure['expired']) ? $secure['expired'] : 30;
        $this->errorText = $secure['errorText'];

        $this->authentication = $authentication;
    }

    /**
     * @param Auth $authentication
     * @return Session
     * @throws Exception
     */
    public static function Get(Auth $authentication)
    {
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

    /**
     * @return bool
     * @throws Exception
     */
    public static function Is()
    {
        $secure = Config::Data('encryption');
        if (isset($_SESSION[$secure['session']])) {
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function Clear()
    {
        $secure = Config::Data('encryption');
        $_SESSION[$secure['session']] = null;
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
            return false;
        }
        if ($loginObject->secure === null) {
            return false;
        }

        $date = new DateTime();
        $data = array(
            'secure' => $loginObject->secure,
            'permission' => $loginObject->permission,
            'generated' => $date->format('Y-m-d H:i:s'),
            'expired' => $date->modify("+{$this->expired} minutes")->format('Y-m-d H:i:s')
        );

        $secure = $this->Encrypt(json_encode($data));
        $_SESSION[$this->session] = $secure;
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
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
        if (!isset($_SESSION[$this->session])) {
            throw new Exception($this->errorText);
        }

        $data = json_decode($this->Decrypt($_SESSION[$this->session]), true);

        if ($data['expired'] !== '') {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $data['expired']);
            if ($date < new DateTime()) {
                throw new Exception($this->expiredText);
            }
        }

        return $this->authentication->GetLoginData($data['secure'], $data['permission']);
    }
    #end region authentication
}