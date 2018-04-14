<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 0.9.2
 */

namespace pukoframework;

use pukoframework\auth\Cookies;

/**
 * Class Request
 * @package pukoframework
 */
class Request extends Routes
{
    /**
     * @var string
     * [GET, POST, PUT, UPDATE, PATCH, DELETE]
     */
    var $request_accept;

    /**
     * @var string
     * client USER_AGENT
     */
    var $client;

    /**
     * @var string
     * full URL string
     */
    public $request_url;

    /**
     * @var string
     * application language code [id, en]
     */
    public $lang;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->request_accept = $_SERVER['REQUEST_METHOD'];
        $this->client = $_SERVER['HTTP_USER_AGENT'];

        $this->request_url = Request::Get('request', '');
        $this->lang = Request::Cookies('lang', 'id');

        $this->Translate($this->request_url, $this->request_accept);
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function Get($key, $default)
    {
        if (!isset($_GET[$key])) {
            return filter_var($default, FILTER_SANITIZE_STRING);
        }

        return filter_var($_GET[$key], FILTER_SANITIZE_STRING);
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function Post($key, $default)
    {
        if (!isset($_POST[$key])) {
            if (!is_array($_POST[$key])) {
                return filter_var($default, FILTER_SANITIZE_STRING);
            } else {
                return $default;
            }
        }
        if (!is_array($_POST[$key])) {
            return filter_var($_POST[$key], FILTER_SANITIZE_STRING);
        } else {
            return $_POST[$key];
        }
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function Session($key, $default)
    {
        if (!isset($_SESSION[$key])) {
            return $default;
        }
        return $_SESSION[$key];
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function Cookies($key, $default)
    {
        if (!isset($_COOKIE[$key])) {
            return $default;
        }
        return $_COOKIE[$key];
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function Vars($key, $default)
    {
        if (!isset($key)) {
            return filter_var($default, FILTER_SANITIZE_STRING);
        }

        return filter_var($key, FILTER_SANITIZE_STRING);
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function Header($key, $default)
    {
        return isset(getallheaders()[$key]) ? getallheaders()[$key] : $default;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function Files($key, $default)
    {
        if (!isset($_FILES[$key])) {
            return $default;
        }

        return $_FILES[$key];
    }

    /**
     * @return bool
     * start the php output buffer
     */
    public static function OutputBufferStart()
    {
        return ob_start();
    }

    /**
     * @return string
     * flush the php output buffer
     */
    public static function OutputBufferFlush()
    {
        $data = ob_get_contents();
        ob_end_flush();

        return $data;
    }

    /**
     * @return string
     * clean the php output buffer
     */
    public static function OutputBufferClean()
    {
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    /**
     * @return bool
     *
     * Request::IsPost()
     * validating post input and provide guards from CSRF attacks
     */
    public static function IsPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $submit = Request::Post('_submit', null);
        if ($submit === null) {
            return false;
        }

        $session_token = Request::Post('token', null);
        $cookies_token = Request::Cookies('token', null);
        if (!hash_equals($session_token, $cookies_token)) {
            return false;
        }

        //re-create secure token
        Cookies::GenerateSecureToken();
        return true;
    }


}
