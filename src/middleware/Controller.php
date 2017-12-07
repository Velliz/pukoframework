<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.0.3
 */

namespace pukoframework\middleware;

/**
 * Class Controller
 * @package pukoframework\pte
 */
class Controller
{

    /**
     * @param $url
     * @param bool $permanent
     */
    public function RedirectTo($url, $permanent = false)
    {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
        exit;
    }

    /**
     * @param int $length
     * @return string
     */
    public function GetRandomToken($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @return string
     * get system date information
     */
    public function GetServerDateTime()
    {
        return date('c');
    }

}