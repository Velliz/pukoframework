<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.7
 */

namespace pukoframework\middleware;

use pukoframework\peh\ThrowConsole;
use Ratchet\MessageComponentInterface;

/**
 * Class Sockets
 * @package pukoframework\middleware
 */
abstract class Sockets extends Controller implements MessageComponentInterface
{

    var $SOCKET_PORT = 8090;

    /**
     * Console constructor.
     */
    public function __construct()
    {
        $exception_handler = new ThrowConsole('Sockets Error');
        $exception_handler->setLogger($this);

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
