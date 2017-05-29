<?php
namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\Request;
use pukoframework\Response;

class RouterTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        define('ROOT', __DIR__);
        define('BASE_URL', $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . '/pukoframework/runner');

        $_COOKIE['token'] = 'pukoframework';
    }

    public function tearDown()
    {
    }

    public function testRoute()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET['request'] = 'ticket/exchange/1';
        $req = new Request();

        $this->assertEquals($req->controller_name, 'ticket');
        $this->assertEquals($req->fn_name, 'exchange');
        $this->assertEquals($req->variable, array('1'));

        $_GET['request'] = 'ticket/12/exchange/3/23/4';
        $req = new Request();

        $this->assertEquals($req->controller_name, 'ticket');
        $this->assertEquals($req->fn_name, 'exchange');
        $this->assertEquals($req->variable, array('3', '23', '4'));

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

    public function testRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['request'] = 'puko/tests/1';
        $_GET['lang'] = 'id';
        $req = new Request();

        $this->assertEquals('puko', $req->controller_name);
        $this->assertEquals('tests', $req->fn_name);
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
        $save = Request::OutputBufferFlush();
        imagedestroy($thumb);
        $this->assertNotNull($save);
    }
}