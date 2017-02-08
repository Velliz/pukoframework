<?php
/**
 * pukoframework.
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @author	Didit Velliz
 *
 * @link	https://github.com/velliz/pukoframework
 * @since	Version 0.9.2
 */

namespace pukoframework;

use pukoframework\auth\Session;

class Request
{
    public $requestType;
    public $requestUrl;
    public $className = 'main';
    public $fnName = 'main';
    public $variable = array();
    public $constant;
    public $lang = 'id';

    public function __construct()
    {
        if (!isset($_COOKIE['token'])) {
            Session::GenerateSecureToken();
        }
        $this->requestType = $_SERVER['REQUEST_METHOD'];
        if (isset($_GET['lang']) && $_GET['lang'] !== '') {
            $this->lang = $_GET['lang'];
        }
        if (isset($_GET['request'])) {
            $this->requestUrl = $_GET['request'];
        }
        $tail = substr($this->requestUrl, -1);
        if ($tail !== '/') {
            $this->requestUrl .= '/';
        }
        $this->requestUrl = explode('/', $this->requestUrl);
        foreach ($this->requestUrl as $point => $value) {
            if ($value === '') {
                break;
            }
            switch ($point) {
                case 0:
                    $this->className = $value;
                    break;
                case 1:
                    if (intval($value)) {
                        $this->constant = $value;
                    } else {
                        $this->fnName = $value;
                    }
                    break;
                case 2:
                    if (isset($this->constant) || is_int($this->constant)) {
                        $this->fnName = $value;
                    } else {
                        array_push($this->variable, $value);
                    }
                    break;
                default:
                    array_push($this->variable, $value);
                    break;
            }
        }
        if (isset($_GET['request'])) {
            $this->requestUrl = $_GET['request'];
        }
    }

    public static function Get($key, $default)
    {
        if (!isset($_GET[$key])) {
            return $default;
        }

        return $_GET[$key];
    }

    public static function Post($key, $default)
    {
        if (!isset($_POST[$key])) {
            return $default;
        }

        return $_POST[$key];
    }

    public static function OutputBufferStart()
    {
        return ob_start();
    }

    /**
     * @return string
     * @deprecated
     */
    public static function OutputBufferFinish()
    {
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    public static function OutputBufferFlush()
    {
        $data = ob_get_contents();
        ob_end_flush();

        return $data;
    }

    public static function OutputBufferClean()
    {
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    public static function IsPost()
    {
        if (!isset($_POST['_submit'])) {
            return false;
        }
        if (!isset($_POST['token'])) {
            return false;
        }
        if (!isset($_COOKIE['token'])) {
            return false;
        }
        if (!hash_equals($_POST['token'], $_COOKIE['token'])) {
            return false;
        }

        Session::GenerateSecureToken();

        return true;
    }
}
