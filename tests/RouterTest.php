<?php

namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\auth\Auth;
use pukoframework\auth\Session;
use pukoframework\Request;
use pukoframework\Response;

class RouterTest extends PHPUnit_Framework_TestCase implements Auth
{

    public function setUp()
    {
        $_COOKIE['token'] = 'pukoframework';
        $_COOKIE['x_default'] = 'pukoframework';
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

    public function testAuth()
    {
        define('ROOT', __DIR__);

        $this->assertFalse(Session::IsSession());
        $this->assertFalse(Request::IsPost());
        $this->assertFalse(Session::IsHasPermission('ADMIN'));
        //$this->assertTrue(Session::Get($this)->Logout());
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

    public function Login($username, $password)
    {
        // TODO: Implement Login() method.
    }

    public function Logout()
    {
        // TODO: Implement Logout() method.
    }

    public function GetLoginData($id)
    {
        // TODO: Implement GetLoginData() method.
    }
}