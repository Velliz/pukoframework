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
use pte\exception\PteException;
use pte\Pte;
use pukoframework\config\Config;
use pukoframework\config\Factory;
use pukoframework\middleware\Console;
use pukoframework\middleware\Sockets;
use pukoframework\pdc\DocsEngine;
use pukoframework\middleware\Service;
use pukoframework\middleware\View;
use pukoframework\peh\ThrowConsole;
use pukoframework\peh\ThrowService;
use pukoframework\peh\ThrowView;
use pukoframework\plugins\LanguageBinders;
use ReflectionClass;
use ReflectionException;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

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
     * @var View|Service|Console|Sockets
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
        //temporary set a starter error handler [a json formatted error]
        $e = new ThrowService('Framework Error');
        $e->setLogger(new Service());

        set_exception_handler(array($e, 'ExceptionHandler'));
        set_error_handler(array($e, 'ErrorHandler'));

        if (!$factory instanceof Factory) {
            throw new Exception('Puko Fatal Error (CF001): Factory must set.');
        }
        self::$factory = $factory;

        $this->request = new Request($factory->getCliParam());
        $this->response = new Response();

        $this->docs_engine = new DocsEngine();
        $this->docs_engine->SetResponseObjects($this->response);

        $this->app = Config::Data('app');
    }

    /**
     * @param string $AppDir
     * @throws Exception
     * @throws ReflectionException
     * @throws PteException
     */
    public function Start($AppDir = '')
    {
        $controller = $AppDir . '\\controller\\' . $this->request->controllerName;

        $this->object = new $controller();

        $this->object->const = $this->app['const'];
        $this->object->logger = $this->app['logs'];

        $languagePath = sprintf(
            '%s/%s/%s.json',
            $this->request->lang,
            $this->request->controllerName,
            $this->request->fnName
        );
        $languagePath = str_replace('\\', '/', $languagePath);
        $languageObject = new LanguageBinders($languagePath);
        $this->object->language = $languageObject->getLanguage();

        $this->pdc = new ReflectionClass($this->object);

        $view = new ReflectionClass(View::class);
        $service = new ReflectionClass(Service::class);
        $console = new ReflectionClass(Console::class);
        $sockets = new ReflectionClass(Sockets::class);

        $this->class_pdc = $this->pdc->getDocComment();
        $this->docs_engine->PDCParser($this->class_pdc, $this->fn_return);

        if (is_array($this->fn_return && is_array($this->docs_engine->GetReturns()))) {
            $this->fn_return = array_merge($this->fn_return, $this->docs_engine->GetReturns());
        }

        $setup = $this->object->BeforeInitialize();
        if (is_array($setup)) {
            $this->fn_return = array_merge($this->fn_return, $setup);
        }

        $this->response->sourceFile = $this->request->sourceFile;

        if (method_exists($this->object, $this->request->fnName)) {
            $this->fn_pdc = $this->pdc->getMethod($this->request->fnName)->getDocComment();
            $this->docs_engine->PDCParser($this->fn_pdc, $this->fn_return);
            if (is_callable(array($this->object, $this->request->fnName))) {
                if (empty($this->request->variable)) {
                    $this->fn_return = array_merge(
                        $this->fn_return,
                        (array)call_user_func(array(
                            $this->object,
                            $this->request->fnName
                        ))
                    );
                } else {
                    $this->fn_return = array_merge(
                        $this->fn_return,
                        (array)call_user_func_array(array(
                            $this->object,
                            $this->request->fnName
                        ), $this->request->variable
                        ));
                }
            } else {
                $error = sprintf(
                    'Puko Fatal Error (FW001) Function %s must set public.',
                    $this->request->fnName
                );
                if ($this->pdc->isSubclassOf($view)) {
                    new ThrowView($error, $this->response);
                }
                if ($this->pdc->isSubclassOf($service)) {
                    new ThrowService($error);
                }
                if ($this->pdc->isSubclassOf($console)) {
                    new ThrowConsole($error);
                }
                if ($this->pdc->isSubclassOf($sockets)) {
                    new ThrowConsole($error);
                }
                throw new Exception($error);
            }
        } else {
            $error = sprintf(
                'Puko Fatal Error (FW002) Function %s not found in class: %s',
                $this->request->fnName,
                $this->request->controllerName
            );
            if ($this->pdc->isSubclassOf($view)) {
                new ThrowView($error, $this->response);
            }
            if ($this->pdc->isSubclassOf($service)) {
                new ThrowService($error);
            }
            if ($this->pdc->isSubclassOf($console)) {
                new ThrowConsole($error);
            }
            if ($this->pdc->isSubclassOf($sockets)) {
                new ThrowConsole($error);
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
                    $this->request->controllerName,
                    $this->request->fnName
                );
                $htmlPath = str_replace('\\', '/', $htmlPath);
                $this->render->SetHtml(sprintf('%s/assets/html/%s', Framework::$factory->getRoot(), $htmlPath));
            }
            $output = $this->render->Output($this->object, Pte::VIEW_HTML);
        }
        if ($this->pdc->isSubclassOf($service)) {
            $output = $this->render->Output($this->object, Pte::VIEW_JSON);
        }
        if ($this->pdc->isSubclassOf($console)) {
            exit(0);
        }
        if ($this->pdc->isSubclassOf($sockets)) {
            $server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        $this->object
                    )
                ),
                $this->object->SOCKET_PORT
            );
            $server->run();
        }

        echo $output;
    }

}
