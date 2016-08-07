<?php
namespace pukoframework;

class Request
{
    var $requestType;
    var $requestUrl;
    var $className;

    public function __construct()
    {
        $this->className = $_GET['req'];
    }

    public function GETRequest()
    {

    }

    public function POSTRequest()
    {

    }

    public function PUTRequest()
    {

    }

    public function DELETERequest()
    {

    }
}