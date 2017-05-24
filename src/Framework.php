<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 0.9.0
 */

namespace pukoframework;

use Exception;
use pukoframework\auth\Session;
use pukoframework\pdc\DocsEngine;
use pukoframework\peh\ValueException;
use pukoframework\pte\RenderEngine;
use pukoframework\pte\Service;
use pukoframework\pte\View;
use ReflectionClass;

/**
 * Class Framework
 * @package pukoframework
 */
class Framework
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var RenderEngine
     */
    private $render;

    /**
     * @var DocsEngine
     */
    private $docs_engine;

    /**
     * @var \ReflectionClass
     */
    private $pdc;

    private $fn_pdc;

    private $fn_return = array();

    private $class_pdc;

    /**
     * @var View|Service
     */
    private $object = null;

    public function Start($app_location = null)
    {

        if (PHP_VERSION_ID < 506000) {
            include "Compability.php";
        }

        $token = Request::Cookies('token', null);
        if ($token === null) {
            Session::GenerateSecureToken();
        }

        $this->request = new Request();
        $this->response = new Response();

        $this->docs_engine = new DocsEngine();

        $this->docs_engine->SetResponseObjects($this->response);

        if ($app_location != null) {
            $controller = $app_location . "\\controller\\" . $this->request->controller_name;
        } else {
            $controller = "\\controller\\" . $this->request->controller_name;
        }

        $this->object = new $controller();
        $this->pdc = new ReflectionClass($this->object);

        $this->class_pdc = $this->pdc->getDocComment();
        $this->docs_engine->PDCParser($this->class_pdc, $this->fn_pdc);

        $this->fn_pdc = $this->class_pdc;

        try {
            $this->fn_return['Exception'] = true;
            if (method_exists($this->object, $this->request->fn_name)) {
                $this->fn_pdc = $this->pdc->getMethod($this->request->fn_name)->getDocComment();
                $this->docs_engine->PDCParser($this->fn_pdc, $this->fn_return);
                if (is_callable(array($this->object, $this->request->fn_name))) {
                    if (empty($this->request->variable)) {
                        $this->fn_return = array_merge($this->fn_return, (array)call_user_func(array($this->object, $this->request->fn_name)));
                    } else {
                        $this->fn_return = array_merge($this->fn_return, (array)call_user_func_array(array($this->object, $this->request->fn_name), $this->request->variable));
                    }
                } else {
                    die('Puko Error (FW001) Function ' . $this->request->fn_name . " must set 'public'.");
                }
            } else {
                die("Puko Error (FW002) Function '" . $this->request->fn_name . "' not found in class: " . $this->request->controller_name);
            }
        } catch (ValueException $ve) {
            $this->fn_return = array_merge($this->fn_return, $ve->getValidations());
        }

        $setup = $this->object->OnInitialize();

        if (is_array($setup)) {
            $this->fn_return = array_merge($this->fn_return, $this->object->OnInitialize());
        }

        $this->fn_return['token'] = $_COOKIE['token'];

        $this->render = new RenderEngine($this->docs_engine->GetResponseObjects());

        if (is_array($this->fn_return && is_array($this->docs_engine->GetReturns()))) {
            $this->fn_return = array_merge($this->fn_return, $this->docs_engine->GetReturns());
        }

        echo $this->Render();
    }

    private function Render()
    {

        $html = ROOT . '/assets/html/';
        $render = '';

        $view = new ReflectionClass(View::class);
        $service = new ReflectionClass(Service::class);

        try {
            if ($this->pdc->isSubclassOf($view)) {

                $cn = str_replace('\\', '/', $this->request->controller_name);

                $this->render->PTEMaster($html . $this->request->lang . '/' . $cn . '/master.html');
                $render = $this->render->PTEParser($html . $this->request->lang . '/' . $cn . '/' . $this->request->fn_name . '.html', $this->fn_return);

            }
            if ($this->pdc->isSubclassOf($service)) {
                $render = json_encode($this->render->PTEJson($this->fn_return));
            }
        } catch (Exception $error) {
            die('Puko Error (FW003) PTE failed to parse the template. You have error in returned data.');
        }

        return $render;

    }
}
