<?php

namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\pte\RenderEngine;
use pukoframework\Request;

class RequestTest extends PHPUnit_Framework_TestCase
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
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['request'] = 'puko/tests/1';
        $_GET['lang'] = 'id';
        $req = new Request();

        $this->assertEquals('puko', $req->className);
        $this->assertEquals('tests', $req->fnName);
        $this->assertEquals('id', $req->lang);
    }

    public function testPost()
    {
        $user = Request::Post('user', 0);
        $this->assertEquals(0, $user);

        $_POST['framework'] = 'puko';
        $fw = Request::Post('framework', 0);
        $this->assertEquals('puko', $fw);
    }

    public function testGet()
    {
        $_GET['user'] = 'puko';
        $user = Request::Get('user', 0);
        $this->assertEquals('puko', $user);

        $fw = Request::Get('framework', 0);
        $this->assertEquals(0, $fw);
    }

    public function testIsPost()
    {
        $isPost = Request::IsPost();
        $this->assertFalse($isPost);
    }

    public function testOutputBuffers()
    {
        Request::OutputBufferStart();
        $thumb = imagecreatetruecolor(300, 300);
        imagejpeg($thumb);
        $save = Request::OutputBufferFinish();
        imagedestroy($thumb);
        $this->assertNotNull($save);
    }

}
