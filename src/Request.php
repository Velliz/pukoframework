<?php
namespace pukoframework;

class Request
{
    var $requestType;
    var $requestUrl;
    var $className = "main";
    var $fnName = "main";
    var $variable = array();
    var $constant;
    var $lang = "id";

    public function __construct()
    {
        $this->requestType = $_SERVER['REQUEST_METHOD'];
        if (isset($_GET['lang']) && $_GET['lang'] != "") $this->lang = $_GET['lang'];
        if (isset($_GET['request'])) $this->requestUrl = $_GET['request'];
        $tail = substr($this->requestUrl, -1);
        if($tail != "/") $this->requestUrl .= "/";
        $this->requestUrl = explode("/", $this->requestUrl);
        foreach ($this->requestUrl as $point => $value) {
            if ($value == "") break;
            switch ($point) {
                case 0:
                    $this->className = $value;
                    break;
                case 1:
                    if (intval($value)) $this->constant = $value;
                    else $this->fnName = $value;
                    break;
                case 2:
                    if (isset($this->constant) || is_int($this->constant)) $this->fnName = $value;
                    else array_push($this->variable, $value);
                    break;
                default:
                    array_push($this->variable, $value);
                    break;
            }
        }
        if (isset($_GET['request'])) $this->requestUrl = $_GET['request'];
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