<?php
namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\Request;
use pukoframework\Response;

class RouterTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $_COOKIE['token'] = 'pukoframework';
    }

    public function tearDown()
    {
    }

    public function testRender()
    {
        $response = new Response();
        $this->assertNull($response->sourceFile);
        $this->assertFalse($response->htmlMaster);
        $this->assertTrue($response->useMasterLayout);
        $this->assertTrue($response->useHtmlLayout);
        $this->assertFalse($response->clearOutput);
        $this->assertTrue($response->displayException);
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
        $save = Request::OutputBufferFlush();
        imagedestroy($thumb);
        $this->assertNotNull($save);
    }
}