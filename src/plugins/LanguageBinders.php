<?php

namespace pukoframework\plugins;

use pukoframework\Framework;
use pukoframework\Request;

/**
 * Class LanguageBinders
 * @package pukoframework\plugins
 *
 * json
 * validation = Error, %s already registered!
 *
 * Example: LanguageBinders::say('validation', ['framework']);
 */
class LanguageBinders
{

    /**
     * @var LanguageBinders
     */
    private static $lang_obj;

    /**
     * LanguageBinders constructor.
     * @param string $keyword
     * @param array $variables
     */
    protected function __construct($keyword = '', $variables = [])
    {
        if (is_object(self::$lang_obj)) {
            return;
        }



    }

    /**
     * @param string $keyword
     * @param array $variables
     * @return LanguageBinders
     */
    public static function say($keyword = '', $variables = [])
    {
        return new LanguageBinders($keyword, $variables);
    }

    public function Parse($data = null, $template = '')
    {
        if ($data === null) {
            return '';
        }

        //get from master
        $lang = Request::Cookies('lang', 'id');
        $master = Framework::$factory->getRoot() . '/assets/master/' . $lang . '.master.json';
        $masterData = null;
        if (file_exists($master)) {
            $masterData = json_decode(file_get_contents($master), true);
        }

        //get from layout
        $resource = str_replace('.html', '.json', $template);
        $resourceData = null;
        if (file_exists($resource)) {
            $resourceData = json_decode(file_get_contents($resource), true);
        }

        //combine them
        $language = [];
        if (is_array($masterData)) {
            $language = array_merge($language, $masterData);
        }
        if (is_array($resourceData)) {
            $language = array_merge($language, $resourceData);
        }

        if (!$data) {
            return isset($language) ? json_encode($language) : '';
        }
        return isset($language[$data]) ? $language[$data] : $data;

    }
}