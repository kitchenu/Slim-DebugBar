<?php

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Tokyo');

$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
$autoloader->addPsr4('Kitchenu\Debugbar\Tests\\', __DIR__);