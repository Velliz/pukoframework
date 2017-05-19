<?php
/**
 * pukoframework.
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @author    Didit Velliz
 *
 * @link    https://github.com/velliz/pukoframework
 * @since    Version 0.9.0
 */

namespace pukoframework;

use Exception;
use pukoframework\peh\ValueException;
use pukoframework\pte\RenderEngine;
use pukoframework\pte\Service;
use pukoframework\pte\View;
use ReflectionClass;

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
    private $funcReturn = array();

    /**
     * @var \ReflectionClass
     */
    private $pdc;
    private $fnPdc;
    private $classPdc;

    /**
     * @var View|Service
     */
    private $object = null;

    public function OnInitialize()
    {
        $this->request = new Request();
        $this->render = new RenderEngine();
    }

    public function Request(Request $request)
    {
        if (!isset($request->requestUrl)) {
            return;
        }
        if (count($this->route) === 0) {
            return;
        }
        if (!isset($_GET['request'])) {
            return;
        }
        foreach ($this->route as $key => $value) {
            if (strpos($request->requestUrl, $key) !== false) {
                if (substr_count($value, '/') > 1) {
                    $segment = explode('/', $value);
                    $classSegment = '';
                    foreach ($segment as $pos => $rows) {
                        array_push($this->request->variable, $rows);
                        if ($pos === 0) {
                            $classSegment .= $rows;
                        }
                        if (($pos > 0) && ($pos + 1) < count($segment)) {
                            $classSegment .= '\\' . $rows;
                        } else {
                            $this->request->fnName = $rows;
                        }
                    }
                    $this->request->className = $classSegment;

                    return;
                } else {
                    $value = str_replace($key, $value, $request->requestUrl);
                    $_GET['request'] = $value;
                    $this->request = new Request();

                    return;
                }
            }
        }
    }

    public function RouteMapping($mapping = array())
    {
        $this->route = $mapping;
    }

    public function Start($controllerPath = null)
    {
        $this->Request($this->request);
        if ($controllerPath != null) {
            $controller = $controllerPath . '\\controller\\' . $this->request->className;
        } else {
            $controller = '\\controller\\' . $this->request->className;
        }
        $controller = strtolower($controller);
        if (!class_exists($controller)) {
            $this->Render('404');
        }

        if (!isset($this->request->constant)) {
            $this->object = new $controller();
        } else {
            $this->object = new $controller($this->request->constant);
        }
        $this->pdc = new ReflectionClass($this->object);
        $this->classPdc = $this->pdc->getDocComment();
        $this->render->PDCParser($this->classPdc, $this->funcReturn);
        $this->fnPdc = $this->classPdc;

        try {
            $this->funcReturn['Exception'] = true;
            if (method_exists($this->object, $this->request->fnName)) {
                $this->fnPdc = $this->pdc->getMethod($this->request->fnName)->getDocComment();
                $this->render->PDCParser($this->fnPdc, $this->funcReturn);
                if (is_callable(array($this->object, $this->request->fnName))) {
                    if (empty($this->request->variable)) {
                        $this->funcReturn = array_merge($this->funcReturn, (array)call_user_func(array($this->object, $this->request->fnName)));
                    } else {
                        $this->funcReturn = array_merge($this->funcReturn, (array)call_user_func_array(array($this->object, $this->request->fnName), $this->request->variable));
                    }
                } else {
                    die('Puko Error (FW001) Function ' . $this->request->fnName . " must set 'public'.");
                }
            } else {
                die("Puko Error (FW002) Function '" . $this->request->fnName . "' not found in class: " . $this->request->className);
            }
        } catch (ValueException $ve) {
            $this->funcReturn = array_merge($this->funcReturn, $ve->getValidations());
        }

        $setup = $this->object->OnInitialize();
        if (is_array($setup)) {
            $this->funcReturn = array_merge($this->funcReturn, $this->object->OnInitialize());
        }
        $this->funcReturn['token'] = $_COOKIE['token'];

        echo $this->Render();
    }

    private function Render($renderCode = '200')
    {
        $html = ROOT . '/assets/html/';
        $sys_html = ROOT . '/assets/system/';
        if ($renderCode === '404') {
            $this->render->PTEMaster($sys_html . $this->request->lang . '/master.html');
            $template = $this->render->PTEParser($sys_html . $this->request->lang . '/404.html', $this->funcReturn);
            return $template;
        }
        $view = new ReflectionClass(pte\View::class);
        $service = new ReflectionClass(pte\Service::class);
        try {
            if ($this->pdc->isSubclassOf($view)) {
                $cn = str_replace('\\', '/', $this->request->className);
                $this->render->PTEMaster($html . $this->request->lang . '/' . $cn . '/master.html');
                return $this->render->PTEParser($html . $this->request->lang . '/' . $cn . '/' . $this->request->fnName . '.html', $this->funcReturn);
            }
            if ($this->pdc->isSubclassOf($service)) {
                return json_encode($this->render->PTEJson($this->funcReturn));
            }
        } catch (Exception $error) {
            die('Puko Error (FW003) PTE failed to parse the template. You have error in returned data.');
        }

        return '';
    }
}
