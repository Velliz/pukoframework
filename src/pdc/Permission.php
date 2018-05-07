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

use pte\CustomRender;
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
class Permission implements Pdc, CustomRender
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

    var $dataKey;
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

        $val = explode('@', $value);
        $this->dataKey = $val[0];
        $this->permission = explode('.', $val[1]);
    }

    /**
     * @param Response &$response
     * @return mixed
     * @throws \pte\exception\PteException
     */
    public function SetStrategy(Response &$response)
    {
        //#Permission \pukoframework\auth\Bearer@\plugins\auth\UserBearer permissions@USER.ADMIN
        $this->AuthClass = $this->provider::Get($this->classes::Instance())->GetLoginData();
        foreach ($this->permission as $val) {
            if (!in_array($val, $this->AuthClass[$this->dataKey])) {
                $this->PermissionDenied($response);
            }
        }

        header('Cache-Control: must-revalidate');
        header('Cache-Control: no-cache');
        return true;
    }

    /**
     * @param Response $response
     * @throws \pte\exception\PteException
     */
    private function PermissionDenied(Response &$response)
    {
        $render = new Pte(false);
        if ($response->useMasterLayout) {
            $render->SetMaster($response->htmlMaster);
        }

        $data = array(
            'status' => 'error',
            'exception' => array(
                'Message' => 'Permission Required'
            )
        );

        http_response_code(404);
        header('Cache-Control: must-revalidate');
        header('Cache-Control: no-cache');

        $render->SetValue($data);
        if ($response->useHtmlLayout) {
            $render->SetHtml(sprintf('%s/assets/system/permission.html', ROOT));
            echo $render->Output($this, Pte::VIEW_HTML);
        } else {
            echo $render->Output($this, Pte::VIEW_JSON);
        }
        exit();
    }

    /**
     * @param $fnName
     * @param $paramArray
     */
    public function RegisterFunction($fnName, $paramArray)
    {
        // TODO: Implement RegisterFunction() method.
    }

    /**
     * @return string
     */
    public function Parse()
    {
        return '';
    }
}