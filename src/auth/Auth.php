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
 * @since	Version 0.9.3
 *
 */
namespace pukoframework\auth;

interface Auth
{

    const EXPIRED_ON_CLOSE = null;
    const EXPIRED_1_HOUR = 3600;
    const EXPIRED_1_DAY = 86400;
    const EXPIRED_1_WEEK = 604800;
    const EXPIRED_1_MONTH = 2592000;

    public function Login($username, $password);

    public function Logout();

    public function GetLoginData($id);

}