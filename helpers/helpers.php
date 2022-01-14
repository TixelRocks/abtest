<?php

use Tixel\AbTest\AbTest;

if (! function_exists('abTest')) {
    function abTest($text = null)
    {
        $instance = app(AbTest::class);

        if ($text) {
            $instance->setId($text);
        }

        return $instance;
    }
}
