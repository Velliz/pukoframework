<?php
namespace pukoframework;

class Framework extends Lifecycle
{

    private $request;
    private $response;
    private $route;
    private $render;
    private $funcReturn;

    private $pdc;
    private $fnPdc;
    private $classPdc;

    public function OnInitialize()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->render = new \pukoframework\pte\RenderEngine();
    }

    public function Request(Request $request)
    {
        if (!isset($request->requestUrl)) return;
        if (sizeof($this->route) == 0) return;
        if (!isset($_GET['request'])) return;
        foreach ($this->route as $key => $value) {
            if (strpos($request->requestUrl, $key) !== false) {
                $value = str_replace($key, $value, $request->requestUrl);
                $_GET['request'] = $value;
                $this->request = new Request();
                break;
            }
        }
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
        if (!class_exists($controller)) die("404 not found.");
        if (!isset($this->request->constant)) $object = new $controller();
        else $object = new $controller($this->request->constant);
        $this->pdc = new \ReflectionClass($object);
        $this->classPdc = $this->pdc->getDocComment();
        $this->fnPdc = $this->classPdc;
        try {
            if (method_exists($object, $this->request->fnName)) {
                $this->fnPdc = $this->pdc->getMethod($this->request->fnName)->getDocComment();
                if (is_callable(array($object, $this->request->fnName))) {
                    if (empty($this->request->variable)) $this->funcReturn = call_user_func(array($object, $this->request->fnName));
                    else $this->funcReturn = call_user_func_array(array($object, $this->request->fnName), $this->request->variable);
                } else die("Function " . $this->request->fnName . " must set 'public'.");
            } else die("Function '" . $this->request->fnName . "'' not found in class: " . $this->request->className);
            if (!isset($_COOKIE['token'])) \pukoframework\auth\Session::GenerateSecureToken();
            $this->funcReturn['token'] = $_COOKIE['token'];
            $this->funcReturn['ExceptionMessage'] = "";
            $this->funcReturn['Exception'] = true;
        } catch (\Exception $error) {
            $this->funcReturn = $this->response->ExceptionHandler($error);
        } finally {
            $this->Render();
        }
    }

    private function Render()
    {
        $view = new \ReflectionClass(pte\View::class);
        $service = new \ReflectionClass(pte\Service::class);
        try {
            if ($this->pdc->isSubclassOf($view)) {
                $this->render->PDCParser($this->classPdc, $this->funcReturn);
                $this->render->PDCParser($this->fnPdc, $this->funcReturn);
                $this->render->PTEMaster(ROOT . "/assets/html/" . $this->request->lang . "/" . $this->request->className . "/master.html");
                $template = $this->render->PTEParser(
                    ROOT . "/assets/html/" . $this->request->lang . "/" . $this->request->className . "/" . $this->request->fnName . ".html",
                    $this->funcReturn
                );
                if ($template != null) echo $template;
                return;
            }
            if ($this->pdc->isSubclassOf($service)) {
                echo json_encode($this->render->PTEJson($this->funcReturn));
                return;
            }
        } catch (\Exception $error) {
            echo $this->response->ExceptionHandler($error);
        }
    }

}