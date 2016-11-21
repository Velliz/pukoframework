<?php
/**
 * pukoframework
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @package	puko/framework
 * @author	Didit Velliz
 * @link	https://github.com/velliz/pukoframework
 * @since	Version 0.9.2
 *
 */
namespace pukoframework;

use Error;
use Exception;

class Response
{
    var $responseType;

    /**
     * @param Exception $error
     * @return mixed
     */
    public function ExceptionHandler($error)
    {
        $emg['Exception'] = false;
        $emg['token'] = $_COOKIE['token'];
        $emg['ExceptionMessage'] = $error->getMessage();
        return $emg;
    }

    /**
     * @param Error $error
     * @return mixed
     */
    public function ErrorHandler($error)
    {
        $emg['Exception'] = false;
        $emg['token'] = $_COOKIE['token'];
        $emg['ExceptionMessage'] = $error->getMessage();
        return $emg;
    }
}