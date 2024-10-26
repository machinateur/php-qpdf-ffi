<?php

require __DIR__ . '/bootstrap_preload.php';

// This is intentional, as `bin/phpunit` defines this file as autoload
return require \dirname(__DIR__) . '/vendor/autoload.php';
