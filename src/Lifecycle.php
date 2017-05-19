<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 0.9.2
 */

namespace pukoframework;

abstract class Lifecycle
{
    /**
     * @var double
     * frameowrk time start
     * for performance log
     */
    var $start;

    public function __construct()
    {
        $this->start = microtime(true);
    }

    abstract public function Request(Request $request);

    abstract public function Response(Response $response);
}
