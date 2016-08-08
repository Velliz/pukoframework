<?php
namespace pukoframework;

use pukoframework\pte\RenderEngine;

class Framework extends Lifecycle
{

    private $request;
    private $response;
    private $route;
    private $render;

    public function OnInitialize()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->render = new RenderEngine();
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
        if(!isset($this->request->constant)) $object = new $controller();
        else $object = new $controller($this->request->constant);
        $pdc = new \ReflectionClass($object);
        $classpdc = $pdc->getDocComment();
        $classpdc = $this->render->PTEParser($classpdc);
        var_dump($classpdc);
        if (method_exists($object, $this->request->fnName)) {
            $fnpdc = $pdc->getMethod($this->request->fnName)->getDocComment();
            if (is_callable(array($object, $this->request->fnName))) {
                if (empty($this->request->variable)) call_user_func(array($object, $this->request->fnName));
                else call_user_func_array(array($object, $this->request->fnName), $this->request->variable);
            } else throw new \Exception("Function must set Public.");
        } else throw new \Exception("Function not found.");
    }
}