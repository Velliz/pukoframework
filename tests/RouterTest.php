<?php
namespace tests;

use PHPUnit_Framework_TestCase;
use pukoframework\Request;

class RouterTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
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

        $this->assertEquals($req->className, 'ticket');
        $this->assertEquals($req->fnName, 'exchange');
        $this->assertEquals($req->variable, array('1'));

        $_GET['request'] = 'ticket/12/exchange/3/23/4';
        $req = new Request();

        $this->assertEquals($req->className, 'ticket');
        $this->assertEquals($req->fnName, 'exchange');
        $this->assertEquals($req->constant, '12');
        $this->assertEquals($req->variable, array('3', '23', '4'));

    }

    public function testLeave()
    {
        $this->assertEquals($this->isLeaveAllowed(), false);
        $this->assertEquals($this->newClosure(), 1);

    }

    function isLeaveAllowed($selectedDate = '2017-01-10', $limit = 7)
    {
        $nowDateObject = new \DateTime();
        $selectedDateObject = \DateTime::createFromFormat('Y-m-d', $selectedDate);
        $limitDateObject = $nowDateObject->modify("+$limit days");

        if ($selectedDateObject < $limitDateObject) return false;
        else return true;
    }

    function newClosure()
    {
        $deleteDirectory = 1;
        $app = function($path) use (&$deleteDirectory) {
            return $path;
        };

        $data = $app(true);
        return $data;
    }

}