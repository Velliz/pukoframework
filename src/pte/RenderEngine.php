<?php
namespace pukoframework\pte;

class RenderEngine
{
    public function PTEParser(&$phpDocs = array())
    {
        $result = array();
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
                if(is_array($phpDocs) && is_array($command)) {
                    foreach ($result as $k => $v) {
                        $phpDocs[$k] = $v;
                    }
                }
            }
        }
        return $result;
    }

    public function Value($key, $val)
    {
        return array($key => $val);
    }
}