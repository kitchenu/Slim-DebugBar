<?php

namespace Kitchenu\Debugbar\Tests;

use Kitchenu\Debugbar\Controllers\AssetController;
use Kitchenu\Debugbar\Controllers\OpenHandlerController;

class ControllersTest extends SlimDebugBarTestCase
{
    /**
     * @var \Interop\Container\ContainerInterface 
     */
    protected $container;

    public function setUp() {
        parent::setUp();

        $this->container = $this->app->getContainer();
    }

    public function testAssetController()
    {
        $controller = new AssetController($this->container);

        $cssResponse = $controller->css(
            $this->container->request, $this->container->response, []
        );
        
        $this->assertEquals($cssResponse->getHeaderLine('Content-type'), 'text/css');

        $jsResponse = $controller->js(
            $this->container->request, $this->container->response, []
        );
        
        $this->assertEquals($jsResponse->getHeaderLine('Content-type'), 'text/javascript');
    }

    public function testOpenHandlerController()
    {
        $controller = new OpenHandlerController($this->container);

        $response = $controller->handle(
            $this->container->request, $this->container->response, []
        );
        
        $this->assertEquals($response->getHeaderLine('Content-type'), 'application/json;charset=utf-8');
    }
}