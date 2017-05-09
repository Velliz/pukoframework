<?php
namespace tests;

use PHPUnit_Framework_TestCase;

class FrameworkTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        define('ROOT', __DIR__);
        define('BASE_URL', $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . '/floors/');
    }

    public function tearDown()
    {

    }

    public function testFramework()
    {

    }
}
