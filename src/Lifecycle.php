<?php
namespace pukoframework;

abstract class Lifecycle
{

    public static $start;

    public function __construct()
    {
        self::$start = microtime(true);
        $this->OnInitialize();
    }

    public abstract function OnInitialize();

    public abstract function Request(Request $request);

    public abstract function Response(Response $response);

}