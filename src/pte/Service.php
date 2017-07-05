<?php

namespace pukoframework\pte;

use pukoframework\peh\ThrowService;

abstract class Service extends Controller
{

    public function __construct()
    {
        $exception_handler = new ThrowService('Service Error');
        set_exception_handler(array($exception_handler, 'ExceptionHandler'));
        set_error_handler(array($exception_handler, 'ErrorHandler'));
    }

    /**
     * @deprecated
     */
    public function OnInitialize()
    {
    }

    public function BeforeInitialize()
    {
    }

    public function AfterInitialize()
    {
    }
}