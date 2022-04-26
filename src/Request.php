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

use Exception;
use pukoframework\plugins\Files;

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
     * @param null $cli_param
     * @throws Exception
     */
    public function __construct($cli_param = null)
    {
        $this->request_accept = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $this->client = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';

        $this->request_url = Request::Get('request', $cli_param === null ? '' : $cli_param);
        $lang = '';
        if (isset($_SERVER['HTTP_X_LANG'])) {
            $lang = $_SERVER['HTTP_X_LANG'];
        }
        if (strlen($lang) === 0) {
            $lang = Request::Cookies('lang', 'id');
        }
        $this->lang = $lang;

        $this->Translate($this->request_url, $this->request_accept);
    }

    /**
     * @param $key
     * @param $default
     * @param bool $filter
     * @return mixed
     */
    public static function Get($key, $default, $filter = true)
    {
        if (!isset($_GET[$key])) {
            return $default;
        }

        return ($filter) ? filter_var($_GET[$key], FILTER_UNSAFE_RAW) : $_GET[$key];
    }

    /**
     * @param $key
     * @param $default
     * @param bool $filter
     * @return mixed
     */
    public static function Post($key, $default, $filter = true)
    {
        if (!isset($_POST[$key])) {
            return $default;
        }
        if (is_array($_POST[$key])) {
            return $_POST[$key];
        } else {
            return ($filter) ? filter_var($_POST[$key], FILTER_UNSAFE_RAW) : $_POST[$key];
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
     * @param bool $filter
     * @return mixed
     */
    public static function Vars($key, $default, $filter = true)
    {
        if (!isset($key)) {
            return $default;
        }

        return ($filter) ? filter_var($key, FILTER_UNSAFE_RAW) : $key;
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
     * @param bool $asObject
     * @return mixed
     */
    public static function Files($key, $default, $asObject = false)
    {
        if (!isset($_FILES[$key])) {
            return $default;
        }

        if ($asObject) {
            return new Files($_FILES[$key]);
        }
        return $_FILES[$key];
    }

    public static function JsonBody($default = array())
    {
        if (sizeof($default) > 0) {
            return $default;
        }
        $inputJSON = file_get_contents('php://input');
        return json_decode($inputJSON, true);
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
     * Get hearder Authorization
     */
    public static function getAuthorizationHeader()
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
    public static function getBearerToken()
    {
        $headers = Request::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

}
