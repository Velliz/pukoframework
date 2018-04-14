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
        $data = Bearer::getBearerToken();
        if ($data === null) {
            return false;
        }

        return true;
    }

    #region authentication
    public function Login($username, $password)
    {
        $secure = $this->authentication->Login($username, $password);
        if ($secure == false || $secure == null) {
            return false;
        }
        $date = new DateTime();
        $data = array(
            'secure' => $secure,
            'generated' => $date->format('Y-m-d H:i:s'),
            'expired' => $date->modify('+7 day')->format('Y-m-d H:i:s')
        );
        $secure = $this->Encrypt(json_encode($data));
        return $secure;
    }

    public function Logout()
    {
        return true;
    }

    public function GetLoginData()
    {
        $data = json_decode($this->Decrypt($this->getBearerToken()), true);
        if ($data === null) {
            throw new Exception('token bearer miss match');
        }

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $data['expired']);
        if ($date < new DateTime()) {
            throw new Exception('token bearer expired');
        }

        return $this->authentication->GetLoginData($data['secure']);
    }
    #end region authentication

    /**
     * Get hearder Authorization
     */
    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map(
                'ucwords',
                array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * get access token from header
     */
    private function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}