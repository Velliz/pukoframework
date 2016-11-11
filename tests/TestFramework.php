<?php

namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\pte\RenderEngine;

class TestFramework extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testRender()
    {
        $pte = new RenderEngine();
        $this->assertTrue($pte->clearOutput);
    }

}
