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

use Exception;
use pukoframework\config\Config;

/**
 * Class Routes
 * @package pukoframework
 */
class Routes
{

    /**
     * @var array
     * store router routing rules
     */
    var $router;

    /**
     * @var array
     * store error routing rules
     */
    var $error;

    /**
     * @var array
     * store not_found routing rules
     */
    var $not_found;

    /**
     * @var array
     * store routes file path
     */
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

    /**
     * @param $request_url
     * @param $request_accept
     * @throws Exception
     */
    public function Translate($request_url, $request_accept)
    {
        $this->RouteSet(Config::Data('routes'), $request_url, $request_accept);
    }

    /**
     * @param $source
     * @param $request_url
     * @param $request_accept
     * @return bool
     */
    private function RouteSet($source, $request_url, $request_accept)
    {
        $this->router = $source['router'];
        $this->error = $source['error'];
        $this->not_found = $source['not_found'];

        $temp = explode('?', $request_url);
        $request_url = explode('/', $temp[0]);

        foreach ($this->router as $key => $val) {
            $url = explode('/', $key);
            if (count($url) === count($request_url)) {
                $match = $parameter = array();
                foreach ($url as $pointer => $segment) {
                    if ($segment === '{?}') {
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
                    if (!in_array($request_accept, $val['accept'])) {
                        //not accept http request codes
                        $this->Mapping($this->error, $parameter);
                        return false;
                    } else {
                        //matched
                        $this->Mapping($val, $parameter);
                        return true;
                    }
                    break;
                }
            }
        }
        $this->Mapping($this->not_found, array());
        return false;
    }

    /**
     * @param array $data_specs
     * @param array $parameter
     */
    private function Mapping($data_specs = array(), $parameter = array())
    {
        $this->controller_name = $data_specs['controller'];
        $this->fn_name = $data_specs['function'];
        $this->variable = $parameter;
    }
}