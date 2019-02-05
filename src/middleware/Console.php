<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.6
 */

namespace pukoframework\middleware;

use pukoframework\peh\ThrowConsole;

/**
 * Class Console
 * @package pukoframework\middleware
 */
class Console extends Controller
{

    public function __construct()
    {
        $exception_handler = new ThrowConsole('Console Error');
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
     * @return mixed
     */
    public function AfterInitialize()
    {
        return array();
    }
}