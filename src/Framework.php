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
use pte\Pte;
use pukoframework\config\Config;
use pukoframework\config\Factory;
use pukoframework\pdc\DocsEngine;
use pukoframework\middleware\Service;
use pukoframework\middleware\View;
use pukoframework\peh\ThrowService;
use pukoframework\peh\ThrowView;
use ReflectionClass;

/**
 * Class Framework
 * @package pukoframework
 */
class Framework
{

    /**
     * @var array
     */
    private $app = array();

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
     * @var ReflectionClass
     */
    private $pdc;

    /**
     * @var array
     */
    private $fn_pdc;

    /**
     * @var array
     */
    private $class_pdc;

    /**
     * @var array
     */
    private $fn_return = array();

    /**
     * @var View|Service
     */
    private $object = null;

    /**
     * @var Factory
     */
    public static $factory;

    /**
     * @param Factory $factory
     * @throws Exception Framework constructor.
     * The construct function called for init Request and Response objects.
     * Token also generated when don't exists before.
     */
    public function __construct(Factory $factory)
    {
        if (!$factory instanceof Factory) {
            throw new Exception('Puko Fatal Error (CF001) Faqctory must set.');
        }
        self::$factory = $factory;

        $e = new ThrowService('Framework Error');
        $e->setLogger(new Service());

        set_exception_handler(array($e, 'ExceptionHandler'));
        set_error_handler(array($e, 'ErrorHandler'));

        $this->request = new Request();
        $this->response = new Response();

        $this->docs_engine = new DocsEngine();
        $this->docs_engine->SetResponseObjects($this->response);

        $this->app = Config::Data('app');
    }

    /**
     * @param string $AppDir
     * @throws Exception
     * @throws \ReflectionException
     * @throws \pte\exception\PteException
     */
    public function Start($AppDir = '')
    {
        $controller = $AppDir . '\\controller\\' . $this->request->controller_name;

        $this->object = new $controller();

        $this->object->const = $this->app['const'];
        $this->object->logger = $this->app['logs'];
        $this->pdc = new ReflectionClass($this->object);

        $view = new ReflectionClass(View::class);
        $service = new ReflectionClass(Service::class);

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
                if ($this->pdc->isSubclassOf($view)) {
                    new ThrowView($error, $this->response);
                }
                if ($this->pdc->isSubclassOf($service)) {
                    new ThrowService($error);
                }
                throw new Exception($error);
            }
        } else {
            $error = sprintf(
                'Puko Fatal Error (FW002) Function %s not found in class: %s',
                $this->request->fn_name,
                $this->request->controller_name
            );
            if ($this->pdc->isSubclassOf($view)) {
                new ThrowView($error, $this->response);
            }
            if ($this->pdc->isSubclassOf($service)) {
                new ThrowService($error);
            }
            throw new Exception($error);
        }

        $setup = $this->object->AfterInitialize();
        if (is_array($setup)) {
            $this->fn_return = array_merge($this->fn_return, $setup);
        }

        if ($this->response->disableOutput) {
            exit;
        }

        $this->render = new Pte(
            $this->response->cacheDriver,
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
                $htmlPath = str_replace('\\', '/', $htmlPath);
                $this->render->SetHtml(sprintf('%s/assets/html/%s', Framework::$factory->getRoot(), $htmlPath));
            }
            $output = $this->render->Output($this->object, Pte::VIEW_HTML);
        }
        if ($this->pdc->isSubclassOf($service)) {
            $output = $this->render->Output($this->object, Pte::VIEW_JSON);
        }

        echo $output;
    }

}
