<?php

namespace Kitchenu\Debugbar\Tests;

use Kitchenu\Debugbar\JavascriptRenderer;
use Kitchenu\Debugbar\ServiceProvider;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class SlimDebugBarTest extends SlimDebugBarTestCase
{
    public function testStartStopMeasure()
    {
        $this->debugbar->startMeasure('test');
        $this->debugbar->stopMeasure('test');
        $collector = $this->debugbar->getCollector('time');
        $measure = $collector->getMeasures()[0];

        $this->assertEquals($measure['label'], 'test');
        $this->assertGreaterThan($measure['start'], $measure['end']);
    }

    public function testAddMeasure()
    {
        $this->debugbar->addMeasure('test', 10, 20);
        $collector = $this->debugbar->getCollector('time');
        $measure = $collector->getMeasures()[0];

        $this->assertEquals('test', $measure['label']);
        $this->assertGreaterThan($measure['start'], $measure['end']);
    }

    public function testMeasure()
    {
        $this->debugbar->measure('test', function () {});
        $collector = $this->debugbar->getCollector('time');
        $measure = $collector->getMeasures()[0];

        $this->assertEquals('test', $measure['label']);
    }

    public function testAddException()
    {
        $this->debugbar->addException(new \Exception('test'));
        $collector = $this->debugbar->getCollector('exceptions');
        $exception = $collector->getExceptions()[0];

        $this->assertEquals($exception->getMessage(), 'test');
    }

    public function testGetJavascriptRenderer()
    {
        $javascriptRenderer = $this->debugbar->getJavascriptRenderer();

        $this->assertInstanceOf(JavascriptRenderer::class, $javascriptRenderer);
    }

    public function testModifyResponse()
    {
        $provider = new ServiceProvider([
            'storage' => [
                'enabled' => true,
                'path'    => __DIR__ . '/../debugbar',
            ]
        ]);
        $provider->register($this->app);
        
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/test'
            ]
        );

        $request = Request::createFromEnvironment($environment);

        $response = new Response();
 
        $this->app->get('/test', function ($request, $response, $args) {
            $body = $response->getBody();
            $body->write('Test');
            return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
        });

        $response = $this->app->process($request, $response);

        $response = $this->debugbar->modifyResponse($response);
        
        $this->assertContains('var phpdebugbar = new PhpDebugBar.DebugBar();', (string) $response->getBody());
    }

    public function testCollect()
    {
        $this->assertArrayHasKey('__meta', $this->debugbar->collect());
    }

    public function testAddMessage()
    {
        $this->debugbar->addMessage('test');
        $collector = $this->debugbar->getCollector('messages');
        $message = $collector->getMessages()[0];

        $this->assertEquals($message['message'], 'test');
    }

    public function test__call()
    {
        $this->debugbar->info('test');

        $collector = $this->debugbar->getCollector('messages');
        $message = $collector->getMessages()[0];

        $this->assertEquals($message['label'], 'info');
        $this->assertEquals($message['message'], 'test');
    }
}