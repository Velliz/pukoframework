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
use pukoframework\Request;

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
    private $expiredText;
    private $expired;
    private $errorText;

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
        $this->expiredText = $secure['expiredText'];
        $this->errorText = $secure['errorText'];
        $this->expired = isset($secure['expired']) ? $secure['expired'] : 30;

        $this->authentication = $authentication;
    }

    public static function Get(Auth $authentication)
    {
        if (is_object(self::$bearerObject)) {
            return self::$bearerObject;
        }
        return self::$bearerObject = new Bearer($authentication);
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

    public static function Is()
    {
        $data = Request::getBearerToken();
        if ($data === null) {
            return false;
        }

        return true;
    }

    #region authentication

    /**
     * @param $username
     * @param $password
     * @return bool|string
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
        return $secure;
    }

    public function Logout()
    {
        return true;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function GetLoginData()
    {
        $data = json_decode($this->Decrypt(Request::getBearerToken()), true);
        if ($data === null) {
            throw new Exception($this->errorText);
        }

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