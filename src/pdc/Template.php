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
 * Class Template
 * @package pukoframework\pdc
 */
class Template extends Response implements Pdc
{
    var $key;
    var $value;
    var $switch;

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value = null)
    {
        $this->key = $clause;
        $this->value = $command;
        $this->switch = $value;
    }

    /**
     * @return mixed
     */
    public function SetStrategy()
    {
        switch ($this->value) {
            case 'master':
                if (strcasecmp(str_replace(' ', '', $this->switch), 'false') === 0) {
                    $this->useMasterLayout = false;
                }
                break;
            case 'html':
                if (strcasecmp(str_replace(' ', '', $this->switch), 'false') === 0) {
                    $this->useHtmlLayout = false;
                }
                break;
        }

        return true;
    }
}