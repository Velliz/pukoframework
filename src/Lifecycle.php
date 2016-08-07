<?php
namespace pukoframework;

abstract class Lifecycle
{

    var $start;

    public function __construct()
    {
        $this->start = microtime(true);
        $this->OnInitialize();
    }

    public abstract function OnInitialize();

    public abstract function Request(Request $request);

    public abstract function Response(Response $response);

}