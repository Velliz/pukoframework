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

/**
 * Interface Auth
 * @package pukoframework\auth
 */
interface Auth
{

    /**
     * @return mixed
     */
    public static function Instance();

    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    public function Login($username, $password);

    /**
     * @return mixed
     */
    public function Logout();

    /**
     * @param $data
     * @param $permission
     * @return mixed
     */
    public function GetLoginData($data, $permission);

}