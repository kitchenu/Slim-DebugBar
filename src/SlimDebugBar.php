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
use DebugBar\Storage\FileStorage;
use DebugBar\Storage\MemcachedStorage;
use DebugBar\Storage\PdoStorage;
use DebugBar\Storage\RedisStorage;
use Exception;
use Interop\Container\ContainerInterface as Container;
use Kitchenu\Debugbar\DataCollector\SlimRouteCollector;
use Psr\Http\Message\ResponseInterface as Response;

class SlimDebugBar extends DebugBar
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $settings = [
        'storage' => [
            'enabled' => true,
            'driver'  => 'file',  // file, pdo, redis
            'path'    => '',
            'connection' => null
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

    /**
     * @param Container $container
     * @param array $settings
     */
    public function __construct(Container $container, $settings)
    {
        $this->container = $container;
        $this->settings = array_replace_recursive($this->settings, $settings);

        $storageSettings = $settings['storage'];

        if ($storageSettings['enabled']) {
            switch ($storageSettings['driver']) {
                case 'pdo':
                    $storage = new PdoStorage($storageSettings['connection']);
                    break;
                case 'redis':
                    $storage = new RedisStorage($storageSettings['connection']);
                    break;
                case 'memcached':
                    $storage = new MemcachedStorage($storageSettings['connection']);
                    break;
                case 'file':
                default:
                    $path = $storageSettings['path'];
                    if(!is_dir($path)){
                        mkdir($path);
                    }
                    $storage = new FileStorage($path);
                    break;
            }

            $this->setStorage($storage);
        }

        $collectorsSettings = $settings['collectors'];

        if ($collectorsSettings['phpinfo']) {
            $this->addCollector(new PhpInfoCollector());
        }

        if ($collectorsSettings['messages']) {
            $this->addCollector(new MessagesCollector());
        }

        if ($collectorsSettings['time']) {
            $this->addCollector(new TimeDataCollector());
            $this->startMeasure('app', 'App');
        }

        if ($collectorsSettings['memory']) {
            $this->addCollector(new MemoryCollector());
        }

        if ($collectorsSettings['exceptions']) {
            $exceptionCollector = new ExceptionsCollector();
            $exceptionCollector->setChainExceptions(true);
            $this->addCollector($exceptionCollector);
        }

        if ($collectorsSettings['route']) {
            $this->addCollector(
                new SlimRouteCollector($container->router, $container->request)
            );
        }

        if ($collectorsSettings['request']) {
            $this->addCollector(new RequestDataCollector());
        }

        $renderer = $this->getJavascriptRenderer();
        $renderer->setBindAjaxHandlerToXHR($this->settings['capture_ajax']);
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
     * @param  Response $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function modifyResponse(Response $response)
    {
        if ($this->isDebugbarRequest()) {
            return $response;
        }

        if ($this->isRedirection($response) && session_status() == PHP_SESSION_ACTIVE) {
            $this->stackData();
        } elseif ($this->isJsonRequest() &&  $this->settings['capture_ajax']) {
            $this->sendDataInHeaders(true);
        } elseif (
            $response->hasHeader('Content-Type') &&
            strpos($response->getHeaderLine('Content-Type'), 'html'))
        {
            $this->injectDebugbar($response);
        } else {
            $this->collect();
        }

        return $response;
    }

    /**
     * Check if this is a request to the Debugbar OpenHandler
     *
     * @return bool
     */
    protected function isDebugbarRequest()
    {
        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = $this->container->get('request');

        return preg_match('#^(\/|)_debugbar\/#', $request->getUri()->getPath());
    }

    /**
     * Is this response a redirection?
     *
     * @param  Response $response
     *
     * @return bool
     */
    protected function isRedirection(Response $response)
    {
        return $response->getStatusCode() >= 300 && $response->getStatusCode() < 400;
    }

    /**
     * Is this json request?
     *
     * @return bool
     */
    protected function isJsonRequest()
    {
        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = $this->container->get('request');

        // If XmlHttpRequest, return true
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return true;
        }

        // Check if the request wants Json
        return (
            $request->hasHeader('Content-Type') &&
            $request->getHeader('Content-Type') == 'application/json'
        );
    }

    /**
     * Collects the data from the collectors
     *
     * @return array
     */
    public function collect()
    {
        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = $this->container->get('request');

        $this->data = [
            '__meta' => [
                'id' => $this->getCurrentRequestId(),
                'datetime' => date('Y-m-d H:i:s'),
                'utime' => microtime(true),
                'method' => $request->getMethod(),
                'uri' => $request->getUri()->getPath(),
                'ip' => $request->getUri()->getHost(),
            ]
        ];

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        // Remove all invalid (non UTF-8) characters
        array_walk_recursive(
            $this->data,
            function (&$item) {
                if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
            }
        );

        if ($this->storage !== null) {
            $this->storage->save($this->getCurrentRequestId(), $this->data);
        }

        return $this->data;
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param  Response $response
     *
     * @return void
     */
    public function injectDebugbar(Response $response)
    {
        /** @var Slim\Router $router */
        $router = $this->container->get('router');

        $body = $response->getBody();

        $renderer = $this->getJavascriptRenderer();
        if ($this->getStorage()) {
            $renderer->setOpenHandlerUrl($router->pathFor('debugbar-openhandler'));
        }

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
