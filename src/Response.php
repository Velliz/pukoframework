<?php
namespace pukoframework;

class Response
{
    var $responseType;

    public function ExceptionHandler(\Exception $error)
    {
        echo $error->getMessage();
    }
}