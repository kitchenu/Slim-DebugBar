<?php

namespace Kitchenu\Debugbar\Tests;

use Exception;
use Kitchenu\Debugbar\Middleware\Debugbar;

class MiddlewareTest extends SlimDebugBarTestCase
{
    /**
     * @var \Slim\Container
     */
    protected $container;

    public function setUp() {
        parent::setUp();

        $this->container = $this->app->getContainer();
    }

    public function testDebugbarWithDefaultErrorHandler()
    {
        $this->invokeDebugbarMiddleware();
    }

    public function testDebugbarWithCustomErrorHandler()
    {
        $this->container['errorHandler'] = function () {
            return function ($request, $response, $e) {
                return $response;
            };
        };

        $this->invokeDebugbarMiddleware();
    }

    public function invokeDebugbarMiddleware()
    {
        $debugbar = new Debugbar(
            $this->container->debugbar,
            $this->container->errorHandler
        );

        try {
            $debugbar(
                $this->container->request,
                $this->container->response,
                function () {
                    throw new Exception('test');
                }
            );
        } catch (Exception $e) {
            $collector = $this->debugbar->getCollector('exceptions');
            $exception = $collector->getExceptions()[0];

            $this->assertEquals($exception->getMessage(), 'test');
        }
    }
}