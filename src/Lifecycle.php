<?php
/**
 * pukoframework.
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @author	Didit Velliz
 *
 * @link	https://github.com/velliz/pukoframework
 * @since	Version 0.9.2
 */

namespace pukoframework;

abstract class Lifecycle
{
    public static $start;

    public function __construct()
    {
        self::$start = microtime(true);
        $this->OnInitialize();
    }

    abstract public function OnInitialize();

    abstract public function Request(Request $request);
}
