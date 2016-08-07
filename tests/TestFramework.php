<?php

class TestFramework extends \PHPUnit_Framework_TestCase
{

    public function TestRequest()
    {
        $framework = new \pukoframework\Framework();
        assertInstanceOf(get_class($framework), $framework);
    }

}
