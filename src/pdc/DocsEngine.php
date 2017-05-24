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

use Error;
use Exception;
use pukoframework\Response;

/**
 * Class DocsEngine
 * @package pukoframework\pdc
 */
class DocsEngine
{

    var $target_class;

    /**
     * @var Value
     */
    var $clause;

    /**
     * @var Pdc|Response
     */
    var $command_objects;

    var $command;

    var $return_command;

    var $value = '';

    /**
     * @param $raw_docs
     * @param $return_data
     * returned from controller
     *
     * @throws Exception
     */
    public function PDCParser($raw_docs, &$return_data)
    {
        preg_match_all('(#[ a-zA-Z0-9-:./_]+)', $raw_docs, $result, PREG_PATTERN_ORDER);
        if (count($result[0]) > 0) {
            foreach ($result[0] as $key => $value) {

                $preg = explode(' ', $value);

                $this->clause = str_replace('#', '', $preg[0]);
                $this->command = $preg[1];

                $this->value = '';

                foreach ($preg as $k => $v) {
                    switch ($k) {
                        case 0:
                            break;
                        case 1:
                            break;
                        default:
                            if ($k !== sizeof($preg) - 1) {
                                $this->value .= $v . ' ';
                            } else {
                                $this->value .= $v;
                            }
                            break;
                    }
                }
                try {
                    $class = '\\pukoframework\\pdc\\' . $this->clause;
                    $this->command_objects = new $class();
                    $this->command_objects->SetCommand($this->clause, $this->command, $this->value);
                    $this->return_command = $this->command_objects->SetStrategy();
                } catch (Error $error) {
                    $false = "Puko Error (PTE001) PTE Command <b>#%s %s %s</b> unregistered.";
                    $false = sprintf($false, $this->clause, $this->command, $this->value);
                    throw new Exception($false);
                }

                if (is_array($return_data) && is_array($this->return_command)) {
                    foreach ($this->return_command as $k => $v) {
                        $return_data[$k] = $v;
                    }
                }
            }
        }
    }

    /**
     * @return Pdc|Response
     */
    public function GetObjects()
    {
        return $this->command_objects;
    }

    public function GetReturns()
    {
        return $this->return_command;
    }

}