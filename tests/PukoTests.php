<?php

namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\pte\RenderEngine;
use pukoframework\Request;

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
        $pte = new RenderEngine('string');
        $this->assertTrue($pte->clearOutput);
    }
    
    public function testRequest()
    {
        $_GET['user'] = 'puko';

        $user = Request::Post('user', 0);
        $this->assertEquals(0, $user);

        $user = Request::Get('user', 0);
        $this->assertEquals('puko', $user);

        $user = Request::IsPost();
        $this->assertFalse($user);
    }

}
