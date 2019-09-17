<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.5
 */
namespace pukoframework\log;

use pukoframework\config\Config;
use pukoframework\Framework;
use pukoframework\plugins\CurlRequest;

/**
 * Trait LogTransforms
 * @package pukoframework\log
 */
trait LogTransforms
{

    private $err_const = ['file', 'line', 'function', 'class', 'type', 'args'];

    /**
     * @param $message
     * error message string
     *
     * @param $desc
     * @param array $context
     * stacktree of exception
     *
     */
    function notify($message, $desc, array $context = [])
    {
        foreach (Config::Data('app')['logs'] as $name => $configuration) {
            if ($configuration['active']) {
                $messages = array(
                    'attachments' => [
                        [
                            'author_name' => $desc,
                            'title' => $configuration['username'],
                            'title_link' => Framework::$factory->getRoot(),
                            'text' => $message,
                            'fields' => $this->TranslateArray($context),
                            'color' => '#0067AC',
                        ]
                    ]
                );
                CurlRequest::To($configuration['url'])->Method('POST')
                    ->Receive($messages, CurlRequest::JSON);
            }
        }
    }

    /**
     * @param $arr
     * @param array $fields
     * @param $index
     * @return array
     */
    function TranslateArray($arr, &$fields = [], &$index = 0)
    {
        foreach ($arr as $key => $val) {
            if (is_array($val) || $index < 3) {
                $index++;
                $this->TranslateArray($val, $fields);
            }
            if (strlen($key) > 2) {
                $fields[] = array(
                    'value' => "{$key}: {$val}",
                    'short' => false
                );
            }
        }
        return $fields;
    }
}