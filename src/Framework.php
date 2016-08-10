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
        if(sizeof($this->route) == 0) return;
        foreach ($this->route as $key => $value) {
            if (strpos($request->requestUrl, $key) !== false) {
                $value = str_replace($key, $value, $request->requestUrl);
                $_GET['request'] = $value;
                break;
            }
        }
        $this->request = new Request();
    }


    public function Response(Response $response)
    {
        @set_exception_handler(array($response, 'ExceptionHandler'));
    }

    public function RouteMapping($mapping = array())
    {
        $this->route = $mapping;
    }

    public function Start()
    {
        $this->Request($this->request);
        $this->Response($this->response);
        $controller = '\\controller\\' . $this->request->className;
        $controller = strtolower($controller);
        if (!isset($this->request->constant)) $object = new $controller();
        else $object = new $controller($this->request->constant);
        $pdc = new \ReflectionClass($object);
        $classpdc = $pdc->getDocComment();
        try {
            if (method_exists($object, $this->request->fnName)) {
                $fnpdc = $pdc->getMethod($this->request->fnName)->getDocComment();
                if (is_callable(array($object, $this->request->fnName))) {
                    if (empty($this->request->variable)) $this->funcReturn = call_user_func(array($object, $this->request->fnName));
                    else $this->funcReturn = call_user_func_array(array($object, $this->request->fnName), $this->request->variable);
                } else throw new \Exception("Function must set Public.");
            } else throw new \Exception("Function not found.");
            $this->funcReturn['token'] = (isset($_COOKIE['token'])) ? $_COOKIE['token'] : null;
        } catch (\Exception $error) {
            $this->funcReturn['PukoException'] = $this->response->ExceptionHandler($error);
        } finally {
            $view = new \ReflectionClass(pte\View::class);
            $service = new \ReflectionClass(pte\Service::class);
            if ($pdc->isSubclassOf($view)) {
                $this->render->PDCParser($classpdc, $this->funcReturn);
                $this->render->PDCParser($fnpdc, $this->funcReturn);
                $this->render->PTEMaster(ROOT . "/assets/html/" . $this->request->lang . "/" . $this->request->className . "/master.html");
                $template = $this->render->PTEParser(
                    ROOT . "/assets/html/" . $this->request->lang . "/" . $this->request->className . "/" . $this->request->fnName . ".html",
                    $this->funcReturn
                );
                echo $template;
            }
            if ($pdc->isSubclassOf($service)) {
                echo json_encode($this->render->PTEJson($this->funcReturn));
            }
        }
    }
}