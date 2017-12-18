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

use pte\CustomRender;
use pte\Pte;
use pukoframework\auth\Session;
use pukoframework\Response;

/**
 * Class Auth
 * @package pukoframework\pdc
 */
class Auth implements Pdc, CustomRender
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
        $render = new Pte(false);

        if ($this->switch === 'true') {
            if (!Session::IsSession()) {
                $response->useMasterLayout = false;

                $render->SetHtml(ROOT . '/assets/system/auth.html');
                $render->SetValue(array(
                    'exception' => 'Authentication Required'
                ));
                $render->Output($this);
                die();
            }
            if ($this->permission === '+') {
                return true;
            }
            if (Session::IsHasPermission($this->permission)) {
                return true;
            }

            $response->useMasterLayout = false;

            $render->SetHtml(ROOT . '/assets/system/permission.html');
            $render->SetValue(array(
                'exception' => 'Permission Required'
            ));
            echo $render->Output($this);
            exit();
        }
        return true;
    }

    /**
     * @param $fnName
     * @param $paramArray
     */
    public function RegisterFunction($fnName, $paramArray)
    {
        // TODO: Implement Register() method.
    }

    /**
     * @return string
     */
    public function Parse()
    {
        return null;
    }
}