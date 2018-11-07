<?php

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Class FrameworkTest
 * @package tests
 */
class FrameworkTest extends TestCase
{

    public function testFramework()
    {
        $protocol = 'http';
        if (isset($_SERVER['HTTPS'])) {
            $protocol = 'https';
        } else if (isset($_SERVER['HTTP_X_SCHEME'])) {
            $protocol = strtolower($_SERVER['HTTP_X_SCHEME']);
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        } else if (isset($_SERVER['SERVER_PORT'])) {
            $serverPort = (int)$_SERVER['SERVER_PORT'];
            if ($serverPort == 80) {
                $protocol = 'http';
            } else if ($serverPort == 443) {
                $protocol = 'https';
            }
        }

        $setup = array(
            'base' => ($protocol . "://localhost/"),
            'root' => __DIR__,
            'start' => microtime(true)
        );

        $this->assertNotNull($setup);
    }

}