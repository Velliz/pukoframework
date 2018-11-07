<?php

namespace pukoframework\middleware;

use pte\CustomRender;
use pukoframework\Framework;
use pukoframework\peh\ThrowView;
use pukoframework\Response;

/**
 * Class View
 * @package pukoframework\middleware
 */
class View extends Controller implements CustomRender
{

    var $fn;
    var $param;

    var $tempJs = '';
    var $tempCss = '';

    /**
     * View constructor.
     */
    public function __construct()
    {
        $exception_handler = new ThrowView('View Error', new Response());
        $exception_handler->setLogger($this);

        set_exception_handler(array($exception_handler, 'ExceptionHandler'));
        set_error_handler(array($exception_handler, 'ErrorHandler'));
    }

    /**
     * @return array
     */
    public function BeforeInitialize()
    {
        return array();
    }

    /**
     * @return array
     */
    public function AfterInitialize()
    {
        return array();
    }


    /**
     * @param $fnName
     * @param $paramArray
     */
    public function RegisterFunction($fnName, $paramArray)
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
            return Framework::$factory->getBase() . $this->param;
        }
        if ($this->fn === 'const') {
            return $this->const[$this->param];
        }
        return '';
    }

}