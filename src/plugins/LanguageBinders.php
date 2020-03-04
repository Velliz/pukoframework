<?php

namespace pukoframework\plugins;

use pukoframework\Framework;
use pukoframework\Request;

/**
 * Class LanguageBinders
 * @package pukoframework\plugins
 */
class LanguageBinders
{

    /**
     * @var array
     */
    private $language = [];

    /**
     * LanguageBinders constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $lang = '';
        if (isset($_SERVER['HTTP_X_LANG'])) {
            $lang = $_SERVER['HTTP_X_LANG'];
        }
        if (strlen($lang) === 0) {
            $lang = Request::Cookies('lang', 'id');
        }

        //get from master
        $master = Framework::$factory->getRoot() . '/assets/master/' . $lang . '.master.json';
        $masterData = null;
        if (file_exists($master)) {
            $masterData = json_decode(file_get_contents($master), true);
        }

        //get from layout
        $resourceData = null;
        if (file_exists($filePath)) {
            $resourceData = json_decode(file_get_contents($filePath), true);
        }

        //combine them
        $language = [];
        if (is_array($masterData)) {
            $language = array_merge($language, $masterData);
        }
        if (is_array($resourceData)) {
            $language = array_merge($language, $resourceData);
        }

        $this->language = $language;
    }

    /**
     * @return array
     */
    public function getLanguage()
    {
        return $this->language;
    }

}