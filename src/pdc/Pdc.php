<?php

namespace pukoframework\pdc;

use Exception;

interface Pdc
{

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value = null);

    /**
     * @return mixed
     * @throws Exception
     */
    public function SetStrategy();

}