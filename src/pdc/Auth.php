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
use pukoframework\auth\Cookies;
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
        if ($response->useMasterLayout) {
            $render->SetMaster($response->htmlMaster);
        }

        if ($this->permission === 'true') {
            $hasPermission = false;
            if ($this->switch === 'cookies') {
                $hasPermission = Cookies::Is();
            }
            if ($this->switch === 'session') {
                $hasPermission = Session::Is();
            }
            if (!$hasPermission) {
                $data = array(
                    'exception' => 'Authentication Required'
                );
                $render->SetValue($data);
                if ($response->useHtmlLayout) {
                    $render->SetHtml(sprintf('%s/assets/system/auth.html', ROOT));
                    echo $render->Output($this, Pte::VIEW_HTML);
                } else {
                    echo $render->Output($this, Pte::VIEW_JSON);
                }
                exit();
            }
            if ($this->permission === '+') {
                return true;
            }

            $isHasPermission = false;
            if ($this->switch === 'cookies') {
                $isHasPermission = Cookies::IsHasPermission($this->permission);
            }
            if ($this->switch === 'session') {
                $isHasPermission = Session::IsHasPermission($this->permission);
            }
            if ($isHasPermission) {
                return true;
            }
            $data = array(
                'exception' => 'Permission Required'
            );
            $render->SetValue($data);
            if ($response->useHtmlLayout) {
                $render->SetHtml(sprintf('%s/assets/system/permission.html', ROOT));
                echo $render->Output($this, Pte::VIEW_HTML);
            } else {
                echo $render->Output($this, Pte::VIEW_JSON);
            }
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