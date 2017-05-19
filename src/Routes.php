<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.0.3
 */

namespace pukoframework;

/**
 * Class Routes
 * @package pukoframework
 */
class Routes
{

    var $page;

    var $error;

    var $not_found;

    var $routes_file;

    /**
     * @var string
     * default controller name if not specified
     */
    public $controller_name;

    /**
     * @var string
     * default function name if not specified
     */
    public $fn_name;

    /**
     * @var array
     * store all variable in REST Style URL schema
     */
    var $variable = array();

    public function Translate($request_url, $request_type)
    {
        $this->routes_file = ROOT . '/config/routes.php';
        if (!file_exists($this->routes_file)) {
            die('Puko Fatal Error. Routes configuration file not found or ROOT is not set.');
        }
        $this->RouteSet(include $this->routes_file, $request_url, $request_type);
    }

    private function RouteSet($source, $request_url, $request_type)
    {
        $this->page = $source['page'];
        $this->error = $source['error'];
        $this->not_found = $source['not_found'];

        $request_url = explode('/', $request_url);
        $parameter = array();

        foreach ($this->page as $key => $val) {
            $url = explode('/', $key);
            if (count($url) == count($request_url)) {
                $match = array();
                foreach ($url as $pointer => $segment) {
                    if ($segment === '{!}') {
                        array_push($parameter, $request_url[$pointer]);
                        $segment = $request_url[$pointer];
                    }
                    if (strcmp($segment, $request_url[$pointer]) == 0) {
                        array_push($match, true);
                    } else {
                        array_push($match, false);
                    }
                }
                if (!in_array(false, $match)) {
                    if (!in_array($request_type, $val['type'])) {
                        $this->controller_name = $this->error['controller'];
                        $this->fn_name = $this->error['function'];
                        $this->variable = $parameter;
                    } else {
                        $this->controller_name = $val['controller'];
                        $this->fn_name = $val['function'];
                        $this->variable = $parameter;
                    }
                    break;
                } else {
                    $this->controller_name = $this->not_found['controller'];
                    $this->fn_name = $this->not_found['function'];
                    $this->variable = $parameter;
                }
            }
        }
    }
}