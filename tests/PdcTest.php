<?php

namespace tests;

use PHPUnit_Framework_TestCase;

class PdcTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        define('ROOT', __DIR__);
        define('BASE_URL', $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . '/floors/');

        $_COOKIE['token'] = 'pukoframework';
    }

    public function testAuth()
    {

    }

}