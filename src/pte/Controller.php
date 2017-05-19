<?php
namespace pukoframework\pte;

class Controller
{

    public function RedirectTo($url, $permanent = false)
    {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
        exit();
    }

}