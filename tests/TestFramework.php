<?php

namespace tests;

use phpunit\framework\TestCase;
use pukoframework\Request;

class TestFramework extends TestCase
{

    public function testRequest()
    {
        include '../vendor/autoload.php';
        $framework = new Request();
        $this->assertEquals("main", "main");
        $this->assertEquals("main", "main");
    }

}
