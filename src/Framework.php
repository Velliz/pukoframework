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
        $controller = '\\controller\\'.$this->request->className;
        $object = new $controller(); //todo: var $constant into constructor
        if (method_exists($object, $this->request->fnName)) {
            if (is_callable(array($object, $this->request->fnName))) {
                if (empty($this->request->variable)) call_user_func(array($object, $this->request->fnName));
                else call_user_func_array(array($object, $this->request->fnName), $this->request->variable);
            } else throw new \Exception("Function must set Public.");
        } else throw new \Exception("Function not found.");
    }
}