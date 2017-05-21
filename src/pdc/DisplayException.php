<?php

namespace pukoframework\pdc;

class DisplayException implements Pdc
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
            $this->displayException = true;
        } elseif ($this->value === 'false') {
            $this->displayException = false;
        }
    }
}