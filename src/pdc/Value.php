<?php

namespace pukoframework\pdc;

class Value implements Pdc
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
        return array($this->key => $this->value);
    }
}