<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.0
 */

namespace pukoframework\pdc;

use pukoframework\Framework;
use pukoframework\Response;

/**
 * Class Master
 * @package pukoframework\pdc
 */
class Master implements Pdc
{

    /**
     * @var string
     */
    var $key;

    /**
     * @var string
     */
    var $value;

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value = null)
    {
        $this->key = $clause;
        $this->value = $command;
    }

    /**
     * @param Response &$response
     * @return mixed
     */
    public function SetStrategy(Response &$response)
    {
        if (file_exists(Framework::$factory->getRoot() . '/assets/master/' . $this->value)) {
            $response->htmlMaster = Framework::$factory->getRoot() . '/assets/master/' . $this->value;
            return true;
        } else {
            return false;
        }
    }
}