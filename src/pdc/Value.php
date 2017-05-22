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
        $this->key = $command;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function SetStrategy()
    {
        return array($this->key => $this->value);
    }
}