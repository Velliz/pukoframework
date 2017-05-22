<?php
/**
 * pukoframework.
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @author    Didit Velliz
 *
 * @link    https://github.com/velliz/pukoframework
 * @since    Version 0.9.2
 */

namespace pukoframework\pte;

use pukoframework\Response;

class RenderEngine
{
    protected $ARRAYS = 0;
    protected $STRINGS = 1;
    protected $BOOLEANS = 2;
    protected $NULLS = 4;
    protected $NUMERIC = 5;
    protected $UNDEFINED = 6;

    /**
     * @var Response
     */
    var $response;

    /**
     * RenderEngine constructor.
     *
     * @param Response $response
     * @param string $sourceFile
     */
    public function __construct(Response $response, $sourceFile = 'file')
    {
        $this->sourceFile = $sourceFile;
        $this->response = $response;
    }

    public function PTEParser($filePath, $arrayData, $source = 'file')
    {

        header('Author: Puko Framework');
        header('Content-Type: text/html');

        if (!$this->response->useHtmlLayout) {
            return "";
        }
        if ($this->sourceFile === $source) {
            $filePath = file_get_contents($filePath);
            $filePath = (!$filePath) ? '' : $filePath;
        }
        if ($this->response->useMasterLayout) {
            $this->response->htmlMaster = str_replace('{CONTENT}', $filePath, $this->response->htmlMaster);
        }
        if (!$this->response->useMasterLayout) {
            $this->response->htmlMaster = $filePath;
        }
        $this->AssetsParser('CSS');
        $this->AssetsParser('JS');
        $this->response->htmlMaster = str_replace('{URL}', BASE_URL, $this->response->htmlMaster);
        if (count($arrayData) <= 0) {
            return $this->response->htmlMaster;
        }
        foreach ($arrayData as $key => $value) {
            $this->TemplateParser($key, $value);
        }

        if ($this->response->clearOutput) {
            preg_match_all('(<!--{![\s\S]*?}-->)', $this->response->htmlMaster, $result);
            foreach ($result[0] as $key => $value) {
                if (strpos($value, '<!--{!!') !== false) {
                    $parsed = $this->GetStringBetween($this->response->htmlMaster, $value, str_replace('<!--{!!', '<!--{/', $value));
                    $this->response->htmlMaster = str_replace($parsed, '', $this->response->htmlMaster);
                } else {
                    $parsed = $this->GetStringBetween($this->response->htmlMaster, $value, str_replace('<!--{!', '<!--{/', $value));
                    $this->response->htmlMaster = str_replace($parsed, '', $this->response->htmlMaster);
                }
            }
            $this->response->htmlMaster = preg_replace('(<!--(.|\s)*?-->)', '', $this->response->htmlMaster);
        }
        $this->response->htmlMaster = preg_replace('({!(.|\s)*?})', '', $this->response->htmlMaster);
        return $this->response->htmlMaster;
    }

    public function TemplateParser($key, $value)
    {
        $tagReplace = '{!' . $key . '}';
        $openTag = '<!--{!' . $key . '}-->';
        $closeTag = '<!--{/' . $key . '}-->';
        switch ($this->GetVarType($value)) {
            case $this->ARRAYS:
                foreach ($value as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        if (is_array($value3) || is_bool($value3)) {
                            $this->TemplateParser($key3, $value3);
                        }
                    }
                }
                $dynamicTags = '';
                $ember = $this->GetStringBetween($this->response->htmlMaster, $openTag, $closeTag);
                foreach ($value as $key2 => $value2) {
                    $parsed = $this->GetStringBetween($this->response->htmlMaster, $openTag, $closeTag);
                    foreach ($value2 as $key3 => $value3) {
                        if (!is_array($value3) && !is_bool($value3)) {
                            $parsed = str_replace('{!' . $key3 . '}', $value3, $parsed);
                        }
                    }
                    $dynamicTags .= $parsed;
                }
                $this->response->htmlMaster = str_replace($ember, $dynamicTags, $this->response->htmlMaster);
                break;
            case $this->NUMERIC:
                //todo: prevent also replacing tag in loop
                $this->response->htmlMaster = str_replace($tagReplace, $value, $this->response->htmlMaster);
                break;
            case $this->STRINGS:
                //todo: prevent also replacing tag in loop
                $this->response->htmlMaster = str_replace($tagReplace, $value, $this->response->htmlMaster);
                break;
            case $this->BOOLEANS:
                $stanza = $this->BlockedConditions($this->response->htmlMaster, $key);
                if ($stanza != null && $stanza != "") {
                    if (!$value) {
                        $parsed = $this->GetStringBetween($this->response->htmlMaster, $openTag, $closeTag);
                        $this->response->htmlMaster = str_replace($parsed, '', $this->response->htmlMaster);
                    } elseif ($value) {
                        $this->response->htmlMaster = str_replace($stanza, '', $this->response->htmlMaster);
                    }
                }
                break;
            case $this->NULLS:
                $this->response->htmlMaster = preg_replace('({!(' . $key . ')*?})', '', $this->response->htmlMaster);
                break;
            case $this->UNDEFINED:
                $this->response->htmlMaster = preg_replace('(<!--(' . $key . ')*?-->)', '', $this->response->htmlMaster);
                break;
            default:
                break;
        }
        $this->response->htmlMaster = str_replace($openTag, '', $this->response->htmlMaster);
        $this->response->htmlMaster = str_replace($closeTag, '', $this->response->htmlMaster);
    }

    public function AssetsParser($key)
    {
        $openTag = '{!' . $key . '}';
        $closeTag = '{/' . $key . '}';
        $ember = $this->GetStringBetween($this->response->htmlMaster, $openTag, $closeTag);
        $this->response->htmlMaster = str_replace($openTag, '', $this->response->htmlMaster);
        $this->response->htmlMaster = str_replace($closeTag, '', $this->response->htmlMaster);
        $this->response->htmlMaster = str_replace($ember, '', $this->response->htmlMaster);
        $this->response->htmlMaster = str_replace('{' . $key . '}', $ember, $this->response->htmlMaster);
    }

    public function PTEMaster($filePath)
    {
        if (!$this->response->htmlMaster) {
            $this->response->htmlMaster = file_get_contents($filePath);
        }
    }

    public function PTEJson($arrayData, $start)
    {

        header('Author: Puko Framework');
        header('Content-Type: application/json');

        if ($arrayData['Exception']) {
            $success = 'success';
            unset($arrayData['Exception']);
            unset($arrayData['ExceptionMessage']);
        } else {
            $success = 'failed';
            $arrayData['Exception'] = true;
        }
        $data = array(
            'time' => microtime(true) - $start,
            'status' => $success,
        );
        $data['data'] = $arrayData;

        return $data;
    }

    public function GetVarType($var)
    {
        if (is_array($var)) {
            return $this->ARRAYS;
        }
        if (is_null($var)) {
            return $this->NULLS;
        }
        if (is_string($var)) {
            return $this->STRINGS;
        }
        if (is_bool($var)) {
            return $this->BOOLEANS;
        }
        if (is_numeric($var)) {
            return $this->NUMERIC;
        } else {
            return $this->UNDEFINED;
        }
    }

    public function GetStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini === false) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    public function BlockedConditions($stanza, $key)
    {
        return $this->GetStringBetween($stanza, '<!--{!!' . $key . '}-->', '<!--{/' . $key . '}-->');
    }
}