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
 * Class ClearOutput
 * @package pukoframework\pdc
 */
class ClearOutput extends Response implements Pdc
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
        $this->key = $clause;
        $this->value = $command;
    }

    /**
     * @return mixed
     */
    public function SetStrategy()
    {
        if ($this->value === 'true') {
            $this->clearOutput = true;
        } elseif ($this->value === 'false') {
            $this->clearOutput = false;
        }
    }

}