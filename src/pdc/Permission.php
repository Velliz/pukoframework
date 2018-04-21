<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2018, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.3
 */

namespace pukoframework\pdc;

use pte\Pte;
use pukoframework\auth\Bearer;
use pukoframework\auth\Cookies;
use pukoframework\auth\PukoAuth;
use pukoframework\auth\Session;
use pukoframework\Response;

/**
 * Class Permission
 * @package pukoframework\pdc
 */
class Permission implements Pdc
{

    var $key;

    /**
     * @var Session|Cookies|Bearer
     */
    var $provider;

    /**
     * @var \pukoframework\auth\Auth
     */
    var $classes;
    var $permission;

    /**
     * @var PukoAuth
     */
    var $AuthClass;

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value)
    {
        $this->key = $clause;
        $com = explode('@', $command);
        $this->provider = $com[0];
        $this->classes = $com[1];
        $this->permission = explode(',', $value);
    }

    /**
     * @param Response &$response
     * @return mixed
     */
    public function SetStrategy(Response &$response)
    {
        //#Permission Bearer@UserAuth SADMIN,USER,TEST
        $this->AuthClass = $this->provider::Get($this->classes::Instance())->GetLoginData();
        if (!$this->AuthClass instanceof PukoAuth) {
            $this->PermissionDenied($response);
        }
        foreach ($this->permission as $val) {
            if (!in_array($val, $this->AuthClass->permission)) {
                $this->PermissionDenied($response);
            }
        }

        header('Cache-Control: must-revalidate');
        header('Cache-Control: no-cache');
        return true;
    }

    private function PermissionDenied(Response &$response)
    {
        $render = new Pte(false);
        if ($response->useMasterLayout) {
            $render->SetMaster($response->htmlMaster);
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

}