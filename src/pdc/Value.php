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

use pukoframework\Response;

/**
 * Class Value
 * @package pukoframework\pdc
 */
class Value implements Pdc
{

    var $key;

    var $value;

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value = null)
    {
        $this->key = $command;
        $this->value = $value;
    }

    /**
     * @param Response $response
     * @return mixed
     */
    public function SetStrategy(Response $response)
    {
        return array($this->key => $this->value);
    }
}