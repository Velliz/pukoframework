<?php

namespace pukoframework\pdc;

use Exception;
use pukoframework\Response;

class Master extends Response
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