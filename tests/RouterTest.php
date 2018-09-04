<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use pukoframework\Request;

class routerTest extends TestCase
{

    public function testGet()
    {
        $_GET['user'] = 'puko';
        $user = Request::Get('user', 0);
        $this->assertEquals('puko', $user);

        $fw = Request::Get('framework', 0);
        $this->assertEquals(0, $fw);
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