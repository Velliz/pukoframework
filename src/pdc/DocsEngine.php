<?php

namespace pukoframework\pdc;

use Error;
use Exception;

class DocsEngine
{

    var $target_class;

    /**
     * @var Value
     */
    var $clause;

    var $command;

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

                foreach ($preg as $k => $v) {
                    switch ($k) {
                        case 0:
                            break;
                        case 1:
                            break;
                        default:
                            if ($key !== count($preg) - 1) {
                                $this->value .= $v . ' ';
                            } else {
                                $this->value .= $v;
                            }
                            break;
                    }
                }
                try {
                    $command = new $this->clause();
                    $command->SetCommand($this->clause, $this->command, $this->value);
                    $return_command = $command->SetStrategy();
                } catch (Error $error) {
                    $false = "Puko Error (PTE001) PTE Command <b>#%s %s %s</b> unregistered.";
                    $false = sprintf($false, $this->clause, $this->command, $this->value);
                    throw new Exception($false);
                }

                if (is_array($return_data) && is_array($return_command)) {
                    foreach ($return_command as $k => $v) {
                        $return_data[$k] = $v;
                    }
                }
            }
        }
    }

}