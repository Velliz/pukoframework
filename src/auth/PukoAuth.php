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

class PukoAuth
{
    /**
     * @var array|null
     */
    var $secure;

    /**
     * @var array|null
     */
    var $permission;

    /**
     * PukoAuth constructor.
     * @param $secure
     * @param $permission
     */
    public function __construct($secure, $permission)
    {
        $this->secure = $secure;
        $this->permission = $permission;
    }


}