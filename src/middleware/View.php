<?php

namespace pukoframework\middleware;

use pte\CustomRender;
use pukoframework\peh\ThrowView;
use pukoframework\Response;

abstract class View extends Controller implements CustomRender
{

    var $fn;
    var $param;

    var $tempJs = '';
    var $tempCss = '';

    public function __construct()
    {
        $exception_handler = new ThrowView('View Error', new Response());
        set_exception_handler(array($exception_handler, 'ExceptionHandler'));
        set_error_handler(array($exception_handler, 'ErrorHandler'));
    }

    public function BeforeInitialize()
    {
        return array();
    }

    public function AfterInitialize()
    {
        return array();
    }


    /**
     * @param $fnName
     * @param $paramArray
     */
    public function Register($fnName, $paramArray)
    {
        $this->fn = $fnName;
        $this->param = $paramArray;
    }

    /**
     * @return string
     */
    public function Parse()
    {
        if ($this->fn === 'url') {
            return BASE_URL . $this->param;
        }
        return '';
    }
}