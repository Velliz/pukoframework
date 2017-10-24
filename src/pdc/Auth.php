<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.0
 */

namespace pukoframework\pdc;

use pukoframework\auth\Session;
use pukoframework\pte\RenderEngine;
use pukoframework\Response;

/**
 * Class Auth
 * @package pukoframework\pdc
 */
class Auth implements Pdc
{

    var $key;
    var $switch;
    var $permission;

    /**
     * DocsAuth constructor.
     */
    public function __construct()
    {
        header('Expires: Mon, 1 Jul 1998 01:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate('D, j M Y H:i:s') . ' GMT');
    }


    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value)
    {
        $this->key = $clause;
        $this->switch = $command;
        $this->permission = $value;
    }

    /**
     * @param Response &$response
     * @return mixed
     */
    public function SetStrategy(Response &$response)
    {
        if ($this->switch === 'true') {
            if (!Session::IsSession()) {
                $response->useMasterLayout = false;
                $render = new RenderEngine($response);
                echo $render->PTEParser(ROOT . '/assets/system/auth.html', array(
                    'exception' => 'Authentication Required'
                ));
                exit;
            }
            if ($this->permission === '+') {
                return true;
            }
            if (Session::IsHasPermission($this->permission)) {
                return true;
            }

            $response->useMasterLayout = false;
            $render = new RenderEngine($response);
            echo $render->PTEParser(ROOT . '/assets/system/permission.html', array(
                'exception' => 'Permission Required'
            ));
            exit;
        }
        return true;
    }

}