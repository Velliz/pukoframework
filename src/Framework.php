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

use pte\Pte;
use pukoframework\auth\Cookies;
use pukoframework\auth\Session;
use pukoframework\pdc\DocsEngine;
use pukoframework\middleware\Service;
use pukoframework\middleware\View;
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
     * @var Pte
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
    private $class_pdc;

    private $fn_return = array();

    /**
     * @var View|Service
     */
    private $object = null;

    /**
     * Framework constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        session_start();
        if (PHP_VERSION_ID < 506000) {
            include 'Compatibility.php';
        }

        $this->request = new Request();
        $this->response = new Response();

        $this->docs_engine = new DocsEngine();
        $this->docs_engine->SetResponseObjects($this->response);

        $token = Request::Cookies('token', null);
        if ($token === null) {
            $token = Cookies::GenerateSecureToken();
        }
        $tokenSession = Request::Session('token', null);
        if ($tokenSession === null) {
            $token = Session::GenerateSecureToken($token);
        }

        $this->fn_return['token'] = $token;
    }

    /**
     * @param string $AppDir
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \pte\exception\PteException
     */
    public function Start($AppDir = '')
    {
        $controller = $AppDir . '\\controller\\' . $this->request->controller_name;

        $this->object = new $controller();
        $this->pdc = new ReflectionClass($this->object);

        $this->class_pdc = $this->pdc->getDocComment();
        $this->docs_engine->PDCParser($this->class_pdc, $this->fn_return);

        if (is_array($this->fn_return && is_array($this->docs_engine->GetReturns()))) {
            $this->fn_return = array_merge($this->fn_return, $this->docs_engine->GetReturns());
        }

        $setup = $this->object->BeforeInitialize();
        if (is_array($setup)) {
            $this->fn_return = array_merge($this->fn_return, $setup);
        }

        if (method_exists($this->object, $this->request->fn_name)) {
            $this->fn_pdc = $this->pdc->getMethod($this->request->fn_name)->getDocComment();
            $this->docs_engine->PDCParser($this->fn_pdc, $this->fn_return);
            if (is_callable(array($this->object, $this->request->fn_name))) {
                if (empty($this->request->variable)) {
                    $this->fn_return = array_merge(
                        $this->fn_return,
                        (array)call_user_func(array(
                            $this->object,
                            $this->request->fn_name
                        ))
                    );
                } else {
                    $this->fn_return = array_merge(
                        $this->fn_return,
                        (array)call_user_func_array(array(
                            $this->object,
                            $this->request->fn_name
                        ), $this->request->variable
                        ));
                }
            } else {
                $error = sprintf(
                    'Puko Fatal Error (FW001) Function %s must set public.',
                    $this->request->fn_name
                );
                die($error);
            }
        } else {
            $error = sprintf(
                'Puko Fatal Error (FW002) Function %s not found in class: %s',
                $this->request->fn_name,
                $this->request->controller_name
            );
            die($error);
        }

        $setup = $this->object->AfterInitialize();
        if (is_array($setup)) {
            $this->fn_return = array_merge($this->fn_return, $setup);
        }

        $view = new ReflectionClass(View::class);
        $service = new ReflectionClass(Service::class);

        $this->render = new Pte(
            $this->response->useCacheLayout,
            $this->response->useMasterLayout,
            $this->response->useHtmlLayout
        );
        $this->render->SetValue($this->fn_return);

        $output = null;

        if ($this->pdc->isSubclassOf($view)) {
            if ($this->response->useMasterLayout) {
                $this->render->SetMaster($this->response->htmlMaster);
            }
            if ($this->response->useHtmlLayout) {
                $htmlPath = sprintf(
                    '%s/%s/%s.html',
                    $this->request->lang,
                    $this->request->controller_name,
                    $this->request->fn_name
                );
                $this->render->SetHtml(sprintf('%s/assets/html/%s', ROOT, $htmlPath));
            }
            $output = $this->render->Output($this->object, Pte::VIEW_HTML);
        }
        if ($this->pdc->isSubclassOf($service)) {
            $output = $this->render->Output($this->object, Pte::VIEW_JSON);
        }

        echo $output;
    }

}
