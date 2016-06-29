<?php

namespace Kitchenu\Debugbar;

use Closure;
use DebugBar\DebugBar;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouterInterface;

class SlimDebugBar extends DebugBar
{
    /**
     * @parama  array $setting
     */
    public function __construct($setting)
    {
        $collectorsSetting = $setting['collectors'];

        if ($collectorsSetting['phpinfo']) {
            $this->addCollector(new PhpInfoCollector());
        }

        if ($collectorsSetting['messages']) {
            $this->addCollector(new MessagesCollector());
        }

        if ($collectorsSetting['time']) {
            $this->addCollector(new TimeDataCollector());
            $this->startMeasure('app', 'App');
        }

        if ($collectorsSetting['memory']) {
            $this->addCollector(new MemoryCollector());
        }

        if ($collectorsSetting['exceptions']) {
            $this->addCollector(new ExceptionsCollector());
        }

        if ($collectorsSetting['request']) {
            $this->addCollector(new RequestDataCollector());
        }
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     *
     * @return void
     */
    public function startMeasure($name, $label = null)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Stops a measure
     *
     * @param  string $name
     *
     * @return void
     */
    public function stopMeasure($name)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->stopMeasure($name);
        }
    }

    /**
     * Adds an exception to be profiled in the debug bar
     *
     * @param Exception $e
     *
     * @return void
     */
    public function addException(Exception $e)
    {
        if ($this->hasCollector('exceptions')) {
            /** @var \DebugBar\DataCollector\ExceptionsCollector $collector */
            $collector = $this->getCollector('exceptions');
            $collector->addException($e);
        }
    }

    /**
     * Returns a JavascriptRenderer for this instance
     *
     * @param string $baseUrl
     * @param string $basePath
     *
     * @return JavascriptRenderer
     */
    public function getJavascriptRenderer($baseUrl = null, $basePath = null)
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavascriptRenderer($this, $baseUrl, $basePath);
        }
        return $this->jsRenderer;
    }

    /**
     * Modify the response and inject the debugbar
     *
     * @param  ResponseInterface $response
     * @param  RouterInterface $router
     *
     * @return ResponseInterface
     */
    public function modifyResponse(ResponseInterface $response, RouterInterface $router)
    {
        if (
            $response->hasHeader('Content-Type') &&
            strpos($response->getHeaderLine('Content-Type'), 'html'))
        {
            $this->injectDebugbar($response, $router);
        }

        return $response;
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param  ResponseInterface $response
     * @param  RouterInterface $router
     *
     * @return void
     */
    public function injectDebugbar(ResponseInterface $response, RouterInterface $router)
    {
        $body = $response->getBody();

        $renderer = $this->getJavascriptRenderer();

        $renderedContent = $renderer->renderHeadSlim($router) . $renderer->render();

        $pos = strripos($body, '</body>');
        if ($pos !== false) {
            $content = substr($body, 0, $pos) . $renderedContent . substr($body, $pos);
        } else {
            $content = $body . $renderedContent;
        }

        $body->rewind();
        $body->write($content);
    }

    /**
     * Adds a measure
     *
     * @param  string $label
     * @param  float $start
     * @param  float $end
     *
     * @return void
     */
    public function addMeasure($label, $start, $end)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param  string $label
     * @param  Closure $closure
     *
     * @return void
     */
    public function measure($label, Closure $closure)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->measure($label, $closure);
        } else {
            $closure();
        }
    }

    /**
     * Adds a message to the MessagesCollector
     *
     * A message can be anything from an object to a string
     *
     * @param mixed $message
     * @param string $label
     *
     * @return void
     */
    public function addMessage($message, $label = 'info')
    {
        if ($this->hasCollector('messages')) {
            /** @var \DebugBar\DataCollector\MessagesCollector $collector */
            $collector = $this->getCollector('messages');
            $collector->addMessage($message, $label);
        }
    }

    /**
     * Magic calls for adding messages
     *
     * @param  string $method
     * @param  array $args
     *
     * @return mixed|void
     */
    public function __call($method, $args)
    {
        $messageLevels = array('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log');
        if (in_array($method, $messageLevels)) {
            foreach($args as $arg) {
                $this->addMessage($arg, $method);
            }
        }
    }
}
