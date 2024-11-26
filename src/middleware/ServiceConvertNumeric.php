<?php

namespace pukoframework\middleware;

use pukoframework\peh\ThrowService;

/**
 * Class ServiceConvertNumeric
 * @package pukoframework\middleware
 */
class ServiceConvertNumeric extends Controller
{

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $exception_handler = new ThrowService('ServiceConvertNumeric Error');
        $exception_handler->setLogger($this);

        set_exception_handler(array($exception_handler, 'ExceptionHandler'));
        set_error_handler(array($exception_handler, 'ErrorHandler'));
    }

    /**
     * @return array
     */
    public function BeforeInitialize()
    {
        return [];
    }

    /**
     * @return array
     */
    public function AfterInitialize()
    {
        return [];
    }

}
