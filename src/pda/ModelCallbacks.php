<?php

namespace pukoframework\pda;

interface ModelCallbacks
{
    public function SetQuery($customQuery);

    public function SetTable($tableName);
}