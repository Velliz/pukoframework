<?php
namespace pukoframework;

class Framework extends Lifecycle
{

    private $request;
    private $response;
    private $route;

    public function OnInitialize()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function Request(Request $request)
    {

    }


    public function Response(Response $response)
    {

    }

    public function RouteMapping($mapping = array())
    {
        $this->route = $mapping;
    }

    public function Start()
    {
        $controller = new $this->request->className();

    }
}