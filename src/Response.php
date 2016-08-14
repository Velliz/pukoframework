<?php
namespace pukoframework;

class Response
{
    var $responseType;

    public function ExceptionHandler($error)
    {
        $emg['Exception'] = true;
        $emg['ExceptionMessage'] = $error->getMessage();
        return $emg;
    }
}