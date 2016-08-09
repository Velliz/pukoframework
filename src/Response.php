<?php
namespace pukoframework;

class Response
{
    var $responseType;

    public function ExceptionHandler($error)
    {
        return $error->getMessage();
    }
}