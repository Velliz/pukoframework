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

    public static function GenerateSecureToken()
    {
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(32));
        } else {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
        }
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

    public function PutBearer($key, $val)
    {
        $_bearer[$key] = $this->Encrypt($val);
        $authorization = "Authorization: Bearer 080042cad6356ad5dc0a720c18b53b8e53d4c274";
    }

    public function GetBearer($val)
    {
        if (!isset($_bearer[$val])) {
            return false;
        }
        return $this->Decrypt($_bearer[$val]);
    }

    public static function RemoveBearer($key)
    {

    }

    public static function Is()
    {
        $secure = Config::Data('encryption');
        $bearer = Request::Header($secure['bearer'], null);
        if (isset($bearer)) {
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

        $string = 'x_' . $secure['bearer'];

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

    public static function ClearBearer()
    {

    }

    #region authentication
    public function Login($username, $password)
    {
        $secure = $this->authentication->Login($username, $password);
        if ($secure == false || $secure == null) {
            return false;
        }
        $secure = $this->Encrypt($secure);
        return $secure;
    }

    public function SetPermission($data = array())
    {
        $permission = $this->Encrypt(json_encode($data));
        return $permission;
    }

    public function Logout()
    {
        $secure = $this->authentication->Logout();
        $this->Clearbearer();
        if ($secure == false || $secure == null) {
            return false;
        }
        return true;
    }

    public function GetLoginData()
    {
        if (!isset($_bearer[self::$bearer])) {
            return false;
        }
        return $this->authentication->GetLoginData($this->Decrypt($_bearer[self::$bearer]));
    }
    #end region authentication

    /**
     * Get hearder Authorization
     * */
    function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * get access token from header
     * */
    function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}