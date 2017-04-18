<?php
namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\pda\Model;

class ModelTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $_COOKIE['token'] = 'pukoframework';
    }

    public function tearDown()
    {
    }

    public function testInsert()
    {
        $pte = new Model('puko');
        $pte->
        $this->assertTrue($pte->clearOutput);
    }
}
