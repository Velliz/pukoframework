<?php
namespace pukoframework;

use pukoframework\pte\RenderEngine;

class Framework extends Lifecycle
{

    private $request;
    private $response;
    private $route;
    private $render;
    private $funcReturn;

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
        $controller = strtolower($controller);
        if(!isset($this->request->constant)) $object = new $controller();
        else $object = new $controller($this->request->constant);
        $pdc = new \ReflectionClass($object);
        $classpdc = $pdc->getDocComment();
        if (method_exists($object, $this->request->fnName)) {
            $fnpdc = $pdc->getMethod($this->request->fnName)->getDocComment();
            if (is_callable(array($object, $this->request->fnName))) {
                if (empty($this->request->variable)) $this->funcReturn = call_user_func(array($object, $this->request->fnName));
                else $this->funcReturn = call_user_func_array(array($object, $this->request->fnName), $this->request->variable);
            } else throw new \Exception("Function must set Public.");
        } else throw new \Exception("Function not found.");
        $view = new \ReflectionClass(pte\View::class);
        $service = new \ReflectionClass(pte\Service::class);
        if ($pdc->isSubclassOf($view)) {
            $this->render->PDCParser($classpdc, $this->funcReturn);
            $this->render->PDCParser($fnpdc, $this->funcReturn);
            $this->render->PTEMaster(ROOT . "/assets/html/" . $this->request->className . "/master.html");
            $template = $this->render->PTEParser(
                ROOT . "/assets/html/" . $this->request->className . "/" . $this->request->fnName . ".html",
                $this->funcReturn
            );
            echo $template;
        }
        if ($pdc->isSubclassOf($service)) {}
    }
}