<?php
/**
 * pukoframework.
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @author    Didit Velliz
 *
 * @link    https://github.com/velliz/pukoframework
 * @since    Version 0.9.2
 */

namespace pukoframework\pte {

    use pukoframework\auth\Session;
    use pukoframework\Lifecycle;
    use pukoframework\peh\ThrowService;
    use pukoframework\peh\ThrowView;

    class RenderEngine
    {
        protected $ARRAYS = 0;
        protected $STRINGS = 1;
        protected $BOOLEANS = 2;
        protected $NULLS = 4;
        protected $NUMERIC = 5;
        protected $UNDEFINED = 6;

        public $sourceFile;

        public $htmlMaster;
        public $useMasterLayout = true;
        public $useHtmlLayout = true;
        public $clearOutput = true;
        public $displayException = true;

        /**
         * RenderEngine constructor.
         *
         * @param string $sourceFile
         */
        public function __construct($sourceFile = 'file')
        {
            $this->sourceFile = $sourceFile;
        }

        /**
         * @param $phpDocs
         * @param $arrData
         */
        public function PDCParser($phpDocs, &$arrData)
        {
            preg_match_all('(#[ a-zA-Z0-9-:./]+)', $phpDocs, $result, PREG_PATTERN_ORDER);
            if (count($result[0]) > 0) {
                foreach ($result[0] as $key => $value) {
                    $preg = explode(' ', $value);
                    $pteFn = str_replace('#', '', $preg[0]);
                    $pteKeyword = $preg[1];
                    $pteValue = '';
                    foreach ($preg as $k => $v) {
                        switch ($k) {
                            case 0:
                                break;
                            case 1:
                                break;
                            default:
                                if ($key !== count($preg) - 1) {
                                    $pteValue .= $v . ' ';
                                } else {
                                    $pteValue .= $v;
                                }
                                break;
                        }
                    }
                    try {
                        $command = $this->$pteFn($pteKeyword, $pteValue);
                    } catch (\Error $error) {
                        die("Puko Error (PTE001) PTE Command <b>#$pteFn $pteKeyword $pteValue</b> unregistered.");
                    }
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

        public function Date($key, $val)
        {
            $now = date('d-m-Y H:i:s');
            $target = (new \DateTime($val))->format('d-m-Y H:i:s');
            if (strcasecmp($key, 'before') === 0) {
                if ($now > $target) {
                    throw new \Exception('URL available before ' . $val);
                }
            }
            if (strcasecmp($key, 'after') === 0) {
                if ($now < $target) {
                    throw new \Exception('URL available after ' . $val);
                }
            }
        }

        /**
         * @param $val
         * @throws \Exception
         */
        public function Auth($val)
        {
            if ($val === 'true') {

                header('Expires: Mon, 1 Jul 1998 01:00:00 GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                header('Last-Modified: ' . gmdate('D, j M Y H:i:s') . ' GMT');

                if (!Session::IsSession()) {
                    throw new \Exception('Authentication Required');
                }
            }
        }

        public function DisplayException($val)
        {
            if ($val === 'true') {
                $this->displayException = true;
            } elseif ($val === 'false') {
                $this->displayException = false;
            }
        }

        public function ClearOutput($val)
        {
            if ($val === 'true') {
                $this->clearOutput = true;
            } elseif ($val === 'false') {
                $this->clearOutput = false;
            }
        }

        public function Template($key, $val)
        {
            switch ($key) {
                case 'master':
                    if (strcasecmp(str_replace(' ', '', $val), 'false') === 0) {
                        $this->useMasterLayout = false;
                    }
                    break;
                case 'html':
                    if (strcasecmp(str_replace(' ', '', $val), 'false') === 0) {
                        $this->useHtmlLayout = false;
                    }
                    break;
            }
        }

        public function PTEParser($filePath, $arrayData, $source = 'file')
        {

            header('Author: Puko Framework');
            header('Content-Type: text/html');

            if (!$this->useHtmlLayout) {
                return;
            }
            if ($this->sourceFile === $source) {
                if (!file_exists($filePath)) {
                    throw new \Exception('html template file not found.');
                }
                if (!file_get_contents($filePath)) {
                    throw new \Exception('html template file is not readable.');
                }
                $filePath = file_get_contents($filePath);
            }
            if ($this->useMasterLayout) {
                $this->htmlMaster = str_replace('{CONTENT}', $filePath, $this->htmlMaster);
            }
            if (!$this->useMasterLayout) {
                $this->htmlMaster = $filePath;
            }
            $this->AssetsParser('CSS');
            $this->AssetsParser('JS');
            $this->htmlMaster = str_replace('{URL}', BASE_URL, $this->htmlMaster);
            if (count($arrayData) <= 0) {
                return $this->htmlMaster;
            }
            foreach ($arrayData as $key => $value) {
                $this->TemplateParser($key, $value);
            }

            if ($this->clearOutput) {
                preg_match_all('(<!--{![\s\S]*?}-->)', $this->htmlMaster, $result);
                foreach ($result[0] as $key => $value) {
                    if (strpos($value, '<!--{!!') !== false) {
                        $parsed = $this->GetStringBetween($this->htmlMaster, $value, str_replace('<!--{!!', '<!--{/', $value));
                        $this->htmlMaster = str_replace($parsed, '', $this->htmlMaster);
                    } else {
                        $parsed = $this->GetStringBetween($this->htmlMaster, $value, str_replace('<!--{!', '<!--{/', $value));
                        $this->htmlMaster = str_replace($parsed, '', $this->htmlMaster);
                    }
                }
                $this->htmlMaster = preg_replace('(<!--(.|\s)*?-->)', '', $this->htmlMaster);
                $this->htmlMaster = preg_replace('({!(.|\s)*?})', '', $this->htmlMaster);
            }

            return $this->htmlMaster;
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
                    $ember = $this->GetStringBetween($this->htmlMaster, $openTag, $closeTag);
                    foreach ($value as $key2 => $value2) {
                        $parsed = $this->GetStringBetween($this->htmlMaster, $openTag, $closeTag);
                        foreach ($value2 as $key3 => $value3) {
                            if (!is_array($value3) && !is_bool($value3)) {
                                $parsed = str_replace('{!' . $key3 . '}', $value3, $parsed);
                            }
                        }
                        $dynamicTags .= $parsed;
                    }
                    $this->htmlMaster = str_replace($ember, $dynamicTags, $this->htmlMaster);
                    break;
                case $this->NUMERIC:
                    //todo: prevent also replacing tag in loop
                    $this->htmlMaster = str_replace($tagReplace, $value, $this->htmlMaster);
                    break;
                case $this->STRINGS:
                    //todo: prevent also replacing tag in loop
                    $this->htmlMaster = str_replace($tagReplace, $value, $this->htmlMaster);
                    break;
                case $this->BOOLEANS:
                    $stanza = $this->BlockedConditions($this->htmlMaster, $key);
                    if ($stanza != null && $stanza != "") {
                        if (!$value) {
                            $parsed = $this->GetStringBetween($this->htmlMaster, $openTag, $closeTag);
                            $this->htmlMaster = str_replace($parsed, '', $this->htmlMaster);
                        } elseif ($value) {
                            $this->htmlMaster = str_replace($stanza, '', $this->htmlMaster);
                        }
                    }
                    break;
                case $this->NULLS:
                    $this->htmlMaster = preg_replace('({!(' . $key . ')*?})', '', $this->htmlMaster);
                    break;
                case $this->UNDEFINED:
                    $this->htmlMaster = preg_replace('(<!--(' . $key . ')*?-->)', '', $this->htmlMaster);
                    break;
                default:
                    break;
            }
            $this->htmlMaster = str_replace($openTag, '', $this->htmlMaster);
            $this->htmlMaster = str_replace($closeTag, '', $this->htmlMaster);
        }

        public function AssetsParser($key)
        {
            $openTag = '{!' . $key . '}';
            $closeTag = '{/' . $key . '}';
            $ember = $this->GetStringBetween($this->htmlMaster, $openTag, $closeTag);
            $this->htmlMaster = str_replace($openTag, '', $this->htmlMaster);
            $this->htmlMaster = str_replace($closeTag, '', $this->htmlMaster);
            $this->htmlMaster = str_replace($ember, '', $this->htmlMaster);
            $this->htmlMaster = str_replace('{' . $key . '}', $ember, $this->htmlMaster);
        }

        public function PTEMaster($filePath)
        {
            $this->htmlMaster = file_get_contents($filePath);
        }

        public function PTEJson($arrayData)
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
                'time' => microtime(true) - Lifecycle::$start,
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

    class Service
    {

        public function __construct()
        {
            $exceptionHandler = new ThrowService();

            set_exception_handler(array($exceptionHandler, 'ExceptionHandler'));
            set_error_handler(array($exceptionHandler, 'ErrorHandler'));
        }

        public function RedirectTo($url, $permanent = false)
        {
            header('Location: ' . $url, true, $permanent ? 301 : 302);
            exit();
        }

        public function OnInitialize(){}
    }

    class View
    {
        public function __construct()
        {
            $exceptionHandler = new ThrowView();

            set_exception_handler(array($exceptionHandler, 'ExceptionHandler'));
            set_error_handler(array($exceptionHandler, 'ErrorHandler'));
        }

        public function RedirectTo($url, $permanent = false)
        {
            header('Location: ' . $url, true, $permanent ? 301 : 302);
            exit();
        }

        public function OnInitialize(){}
    }
}
