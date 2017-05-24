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

use DateTime;
use Exception;

/**
 * Class Date
 * @package pukoframework\pdc
 */
class Date implements Pdc
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
        $now = date('d-m-Y H:i:s');
        $target = (new DateTime($this->value))->format('d-m-Y H:i:s');
        if (strcasecmp($this->key, 'before') === 0) {
            if ($now > $target) {
                throw new Exception('URL available before ' . $this->value);
            }
        }
        if (strcasecmp($this->key, 'after') === 0) {
            if ($now < $target) {
                throw new Exception('URL available after ' . $this->value);
            }
        }
        return true;
    }
}