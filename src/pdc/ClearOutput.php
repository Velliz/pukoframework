<?php

namespace pukoframework\pdc;

class ClearOutput implements Pdc
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