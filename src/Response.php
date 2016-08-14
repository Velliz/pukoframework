<?php
namespace pukoframework;

class Response
{
    var $responseType;

    public function ExceptionHandler($error)
    {
        $emg['Exception'] = false;
        $emg['token'] = $_COOKIE['token'];
        $emg['ExceptionMessage'] = $error->getMessage();
        return $emg;
    }
}