<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.1
 */

namespace pukoframework\auth;

use Couchbase\Authenticator;

class PukoAuth implements Auth
{

    /**
     * @var authenticator
     */
    static $authenticator;

    public static function Instance()
    {
        if (!self::$authenticator instanceof PukoAuth) {
            self::$authenticator = new PukoAuth();
        }
        return self::$authenticator;
    }

    public function Login($username, $password)
    {
    }

    public function Logout()
    {
    }

    public function GetLoginData($id)
    {
    }
}