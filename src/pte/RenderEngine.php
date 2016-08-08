<?php
namespace pukoframework\pte;

class RenderEngine
{
    public function PTEParser($phpDocs)
    {
        preg_match_all("(#[ a-zA-Z0-9-:./]+)", $phpDocs, $result, PREG_PATTERN_ORDER);
        if (sizeof($result) > 0) {
            foreach ($result as $key => $value) {
                $preg = explode(" ", $value);
            }
        }
        return $result;
    }
}