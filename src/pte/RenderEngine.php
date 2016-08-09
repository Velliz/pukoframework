<?php
namespace pukoframework\pte {

    use pukoframework\Lifecycle;

    class RenderEngine
    {

        protected $ARRAYS = 0;
        protected $STRINGS = 1;
        protected $BOOLEANS = 2;
        protected $NULLS = 4;
        protected $NUMERIC = 5;
        protected $UNDEFINED = 6;

        var $htmlMaster;

        public function PDCParser($phpDocs, &$arrData)
        {
            preg_match_all("(#[ a-zA-Z0-9-:./]+)", $phpDocs, $result, PREG_PATTERN_ORDER);
            if (sizeof($result[0]) > 0) {
                foreach ($result[0] as $key => $value) {
                    $preg = explode(" ", $value);
                    $pteFn = str_replace("#", "", $preg[0]);
                    $pteKeyword = $preg[1];
                    $pteValue = '';
                    foreach ($preg as $k => $v) {
                        switch ($k) {
                            case 0:
                                break;
                            case 1:
                                break;
                            default:
                                if ($key != sizeof($preg) - 1) $pteValue .= $v . " ";
                                else $pteValue .= $v;
                                break;
                        }
                    }
                    $command = $this->$pteFn($pteKeyword, $pteValue);
                    if (is_array($arrData) && is_array($command)) {
                        foreach ($command as $k => $v) {
                            $arrData[$k] = $v;
                        }
                    }
                }
            }
        }

        public function Value($key, $val)
        {
            return array($key => $val);
        }

        public function PTEParser($filePath, $arrayData)
        {
            if (!file_exists($filePath)) throw new \Exception("html template file not found.");
            if (!file_get_contents($filePath)) throw new \Exception("html template file is not readable.");
            $filePath = file_get_contents($filePath);
            $this->htmlMaster = str_replace('{CONTENT}', $filePath, $this->htmlMaster);
            $this->htmlMaster = str_replace('{URL}', ROOT, $this->htmlMaster);
            if (sizeof($arrayData) <= 0) return $this->htmlMaster;
            foreach ($arrayData as $key => $value) {
                $tagReplace = '{!' . $key . '}';
                $openTag = '<!--{!' . $key . '}-->';
                $closeTag = '<!--{/' . $key . '}-->';
                switch ($this->GetVarType($value)) {
                    case $this->NUMERIC:
                        $this->htmlMaster = str_replace($tagReplace, $value, $this->htmlMaster);
                        break;
                    case $this->STRINGS:
                        $this->htmlMaster = str_replace($tagReplace, $value, $this->htmlMaster);
                        break;
                    case $this->ARRAYS:
                        $dynamicTags = null;
                        $ember = $this->GetStringBetween($this->htmlMaster, $openTag, $closeTag);
                        foreach ($value as $key2 => $value2) {
                            $parsed = $this->GetStringBetween($this->htmlMaster, $openTag, $closeTag);
                            foreach ($value2 as $key3 => $value3) {
                                $parsed = str_replace('{!' . $key3 . '}', $value3, $parsed);
                            }
                            $dynamicTags .= $parsed;
                        }
                        $this->htmlMaster = str_replace($ember, $dynamicTags, $this->htmlMaster);
                        break;
                    case $this->BOOLEANS:
                        $stanza = $this->BlockedConditions($this->htmlMaster, $key);
                        if (is_null($stanza)) {
                            if ($value != true) {
                                $parsed = $this->GetStringBetween($this->htmlMaster, $openTag, $closeTag);
                                $this->htmlMaster = str_replace($parsed, '', $this->htmlMaster);
                            }
                        } else if ($value == true) $this->htmlMaster = str_replace($stanza, '', $this->htmlMaster);
                        break;
                    case $this->NULLS:
                        break;
                    case $this->UNDEFINED:
                        break;
                    default:
                        break;
                }
            }
            return preg_replace('(<!--(.|\s)*?-->)', '', $this->htmlMaster);
        }

        public function PTEMaster($filePath)
        {
            $this->htmlMaster = file_get_contents($filePath);
        }

        public function PTEJson($arrayData)
        {
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            header("Author: Puko framework 1.0");
            header("Content-Type: application/json");
            $data = array(
                'time' => microtime(true) - Lifecycle::$start,
                'status' => 'success'
            );
            $data['data'] = $arrayData;
            return $data;
        }

        public function GetVarType($var)
        {
            if (is_array($var)) return $this->ARRAYS;
            if (is_null($var)) return $this->NULLS;
            if (is_string($var)) return $this->STRINGS;
            if (is_bool($var)) return $this->BOOLEANS;
            if (is_numeric($var)) return $this->NUMERIC;
            else return $this->UNDEFINED;
        }

        public function GetStringBetween($string, $start, $end)
        {
            $string = " " . $string;
            $ini = strpos($string, $start);
            if ($ini == 0) return "";
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            return substr($string, $ini, $len);
        }

        public function BlockedConditions($stanza, $key)
        {
            return $this->GetStringBetween($stanza, '<!--{!!' . $key . '}-->', '<!--{/' . $key . '}-->');
        }
    }

    class Service
    {

    }
    class View
    {

    }
}

