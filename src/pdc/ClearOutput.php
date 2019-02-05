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
class ClearOutput implements Pdc
{

    /**
     * @var string
     */
    var $key;

    /**
     * @var string
     */
    var $command;

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
        $this->command = $command;
        $this->value = $value;
    }

    /**
     * @param Response &$response
     * @return mixed
     */
    public function SetStrategy(Response &$response)
    {
        if ($this->value === 'true') {
            switch ($this->command) {
                case 'binary':
                    $response->disableOutput = true;
                    break;
            }
        } elseif ($this->value === 'false') {
            switch ($this->command) {
                case 'binary':
                    $response->disableOutput = false;
                    break;
            }
        }

        return true;
    }

}