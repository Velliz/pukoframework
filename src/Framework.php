<?php
/**
 * pukoframework
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @package    puko/framework
 * @author    Didit Velliz
 * @link    https://github.com/velliz/pukoframework
 * @since    Version 0.9.0
 *
 */
namespace pukoframework;

use pukoframework\auth\Session;
use pukoframework\pte\RenderEngine;

class Framework extends Lifecycle
{

    /**
     * @var Request
     */
    private $request;
    private $route;

    /**
     * @var RenderEngine
     */
    private $render;
    private $funcReturn;

    /**
     * @var \ReflectionClass
     */
    private $pdc;
    private $fnPdc;
    private $classPdc;

    public function OnInitialize()
    {
        $this->request = new Request();
        $this->render = new RenderEngine();
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

    public function Response()
    {
        @set_exception_handler(array($this, 'ExceptionHandler'));
        @set_error_handler(array($this, 'ErrorHandler'));
    }

    public function RouteMapping($mapping = array())
    {
        $this->route = $mapping;
    }
    
    public function Start()
    {
        $this->Request($this->request);
        $controller = '\\controller\\' . $this->request->className;
        $controller = strtolower($controller);
        if (!class_exists($controller)) $this->Render('404');
        try {
            if (!isset($this->request->constant)) $object = new $controller();
            else $object = new $controller($this->request->constant);
            $this->pdc = new \ReflectionClass($object);
            $this->classPdc = $this->pdc->getDocComment();
            $this->fnPdc = $this->classPdc;
            
            if (method_exists($object, $this->request->fnName)) {
                $this->fnPdc = $this->pdc->getMethod($this->request->fnName)->getDocComment();
                if (is_callable(array($object, $this->request->fnName))) {
                    if (empty($this->request->variable)) $this->funcReturn = call_user_func(array($object, $this->request->fnName));
                    else $this->funcReturn = call_user_func_array(array($object, $this->request->fnName), $this->request->variable);
                } else die("Puko Error (FW001) Function " . $this->request->fnName . " must set 'public'.");
            } else die("Puko Error (FW002) Function '" . $this->request->fnName . "' not found in class: " . $this->request->className);
            if (!isset($_COOKIE['token'])) Session::GenerateSecureToken();
            $this->funcReturn['token'] = $_COOKIE['token'];
            $this->funcReturn['ExceptionMessage'] = "";
            $this->funcReturn['Exception'] = true;
        } catch (\Exception $error) {
            $this->funcReturn = $this->ExceptionHandler($error);
        } finally {
            $this->Render();
        }
    }

    private function Render($renderCode = '200')
    {
        $html = ROOT . "/assets/html/";
        $sys_html = ROOT . "/assets/system/";
        if ($renderCode == '404') {
            $this->render->PTEMaster($sys_html . $this->request->lang . "/master.html");
            $template = $this->render->PTEParser($sys_html . $this->request->lang . "/404.html", $this->funcReturn);
            echo $template;
            die();
        }
        $view = new \ReflectionClass(pte\View::class);
        $service = new \ReflectionClass(pte\Service::class);
        try {
            if ($this->pdc->isSubclassOf($view)) {
                $this->render->PDCParser($this->classPdc, $this->funcReturn);
                $this->render->PDCParser($this->fnPdc, $this->funcReturn);
                $this->render->PTEMaster($html . $this->request->lang . "/" . $this->request->className . "/master.html");
                $template = $this->render->PTEParser($html . $this->request->lang . "/" . $this->request->className . "/" . $this->request->fnName . ".html", $this->funcReturn);
                echo $template;
                die();
            }
            if ($this->pdc->isSubclassOf($service)) {
                echo json_encode($this->render->PTEJson($this->funcReturn));
                die();
            }
        } catch (\Exception $error) {
            die('Puko Error (FW003) PTE failed to parse the template. You have error in returned data.');
        }
    }

    /**
     * @param \Exception $error
     * @return mixed
     */
    public function ExceptionHandler($error)
    {
        $emg['Message'] = $error->getMessage();
        $emg['File'] = $error->getFile();
        $emg['LineNumber'] = $error->getLine();

        $sys_html = ROOT . "/assets/system/";
        $render = new RenderEngine();
        $render->useMasterLayout = false;
        $template = $render->PTEParser($sys_html . "/exception.html", $emg);

        if ($this->render->displayException) echo $template;
        die();
    }

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     *
     * @return mixed
     */
    public function ErrorHandler($error, $message, $file, $line)
    {
        $emg['Error'] = $error;
        $emg['Message'] = $message;
        $emg['File'] = $file;
        $emg['LineNumber'] = $line;

        $sys_html = ROOT . "/assets/system/";
        $render = new RenderEngine();
        $render->useMasterLayout = false;
        $template = $render->PTEParser($sys_html . "/error.html", $emg);

        if ($this->render->displayException) echo $template;
    }
}