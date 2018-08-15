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
use pukoframework\auth\Bearer;
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
    var $auth;

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value)
    {
        $this->key = $clause;
        $this->switch = $command;
        $this->auth = $value;
    }

    /**
     * @param Response &$response
     * @return mixed
     * @throws \pte\exception\PteException
     */
    public function SetStrategy(Response &$response)
    {
        $render = new Pte(false);
        if ($response->useMasterLayout) {
            $render->SetMaster($response->htmlMaster);
        }

        $hasPermission = false;
        if ($this->switch === 'cookies') {
            $hasPermission = Cookies::Is();
        }
        if ($this->switch === 'session') {
            $hasPermission = Session::Is();
        }
        if ($this->switch === 'bearer') {
            $hasPermission = Bearer::Is();
        }
        if (!$hasPermission) {
            $data = array(
                'exception' => 'Authentication Required'
            );

            http_response_code(403);
            header('Cache-Control: must-revalidate');
            header('Cache-Control: no-cache');

            $render->SetValue($data);
            if ($response->useHtmlLayout) {
                $render->SetHtml(sprintf('%s/assets/system/auth.html', ROOT));
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