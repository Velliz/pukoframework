<?php

namespace pukoframework\middleware;

use pte\CustomRender;
use pukoframework\peh\ThrowService;

/**
 * Class Service
 * @package pukoframework\middleware
 */
abstract class Service extends Controller
{

    public function __construct()
    {
        $exception_handler = new ThrowService('Service Error');
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

}