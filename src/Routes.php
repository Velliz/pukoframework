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
     * store notFound routing rules
     */
    var $notFound;

    /**
     * @var array
     * store maintenance routing rules
     */
    var $maintenance;

    /**
     * @var array
     * store routes file path
     */
    var $routesFile;

    /**
     * @var string
     * default controller name if not specified
     */
    public $controllerName;

    /**
     * @var string
     * default function name if not specified
     */
    public $fnName;

    /**
     * @var array
     * store all variable in REST Style URL schema
     */
    var $variable = [];

    /**
     * @param $requestUrl
     * @param $requestAccept
     * @throws Exception
     */
    public function Translate($requestUrl, $requestAccept)
    {
        $this->RouteSet(Config::Data('routes'), $requestUrl, $requestAccept);
    }

    /**
     * @param $source
     * @param $requestUrl
     * @param $requestAccept
     * @return bool
     */
    private function RouteSet($source, $requestUrl, $requestAccept)
    {
        $this->router = $source['router'];
        $this->error = $source['error'];
        $this->notFound = $source['not_found'];
        $this->maintenance = $source['maintenance'];

        if (Framework::$factory->getEnvironment() === 'MAINTENANCE') {
            $this->Mapping($this->maintenance, []);
            return true;
        }

        $temp = explode('?', $requestUrl);
        $requestUrl = explode('/', $temp[0]);

        foreach ($this->router as $key => $val) {
            $url = explode('/', $key);
            if (count($url) === count($requestUrl)) {
                $match = $parameter = array();
                foreach ($url as $pointer => $segment) {
                    if ($segment === '{?}') {
                        array_push($parameter, $requestUrl[$pointer]);
                        $segment = $requestUrl[$pointer];
                    }
                    if (strcmp($segment, $requestUrl[$pointer]) == 0) {
                        array_push($match, true);
                    } else {
                        array_push($match, false);
                    }
                }
                if (!in_array(false, $match)) {
                    if (!in_array($requestAccept, $val['accept'])) {
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
        $this->Mapping($this->notFound, array());
        return false;
    }

    /**
     * @param array $dataSpecs
     * @param array $parameter
     */
    private function Mapping($dataSpecs = array(), $parameter = array())
    {
        $this->controllerName = $dataSpecs['controller'];
        $this->fnName = $dataSpecs['function'];
        $this->variable = $parameter;
    }

}