<?php

namespace Kitchenu\Debugbar\Tests;

use Slim\App;
use Kitchenu\Debugbar\SlimDebugBar;
use PHPUnit_Framework_TestCase;

abstract class SlimDebugBarTestCase  extends PHPUnit_Framework_TestCase
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var SlimDebugBar
     */
    protected $debugbar;

    public function setUp()
    {
        $this->app = new App([
            'settings' => [
                'displayErrorDetails' => true,
            ]
        ]);
        $container = $this->app->getContainer();
        
        $settings = [
            'storage' => [
                'enabled' => true,
                'path'    => __DIR__ . '/../debugbar',
            ],
            'capture_ajax' => true,
            'collectors' => [
                'phpinfo'    => true,  // Php version
                'messages'   => true,  // Messages
                'time'       => true,  // Time Datalogger
                'memory'     => true,  // Memory usage
                'exceptions' => true,  // Exception displayer
                'route'      => true,
                'request'    => true,  // Request logger
            ]
        ];
        $this->debugbar = $container['debugbar'] = new SlimDebugBar($this->app->getContainer(), $settings);
    }
}