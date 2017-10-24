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
    var $key;
    var $command;
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
     * @param Response $response
     * @return mixed
     */
    public function SetStrategy(Response $response)
    {
        if ($this->value === 'true') {
            switch ($this->command) {
                case 'value':
                    $response->clearValues = true;
                    break;
                case 'block':
                    $response->clearBlocks = true;
                    break;
                case 'comment':
                    $response->clearComments = true;
                    break;
            }
        } elseif ($this->value === 'false') {
            switch ($this->command) {
                case 'value':
                    $response->clearValues = false;
                    break;
                case 'block':
                    $response->clearBlocks = false;
                    break;
                case 'comment':
                    $response->clearComments = false;
                    break;
            }
        }
        return false;
    }

}