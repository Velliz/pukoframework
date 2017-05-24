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

use Exception;
use pukoframework\Response;

/**
 * Class Master
 * @package pukoframework\pdc
 */
class Master extends Response implements Pdc
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
     * @throws Exception
     */
    public function SetStrategy()
    {
        if (file_exists(ROOT . '/assets/master/' . $this->value)) {
            $this->htmlMaster = file_get_contents(ROOT . '/assets/master/' . $this->value);
            return true;
        } else {
            return false;
        }
    }
}