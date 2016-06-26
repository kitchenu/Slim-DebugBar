<?php

namespace Kitchenu\Debugbar\Controllers;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AssetController
{
    /**
     * The DebugBar instance
     *
     * @var \Kitchenu\Debugbar\SlimDebugBar
     */
    protected $debugbar;

    /**
     * @param  ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->debugbar = $ci->get('debugbar');
    }

    /**
     * Return the javascript for the Debugbar
     *
     * @param  RequestInterface $request
     * @param  ResponseInterface $response
     * @param  array $args
     * @return ResponseInterface
     */
    public function js(RequestInterface $request, ResponseInterface $response, $args)
    {
        $renderer = $this->debugbar->getJavascriptRenderer();

        $body = $response->getBody();
        $body->rewind();
        $body->write($renderer->dumpAssetsToString('js'));

        return $response->withHeader('Content-type', 'text/javascript');
    }

    /**
     * Return the stylesheets for the Debugbar
     * 
     * @param  RequestInterface $request
     * @param  ResponseInterface $response
     * @param  array $args
     * @return ResponseInterface
     */
    public function css(RequestInterface $request, ResponseInterface $response, $args)
    {
        $renderer = $this->debugbar->getJavascriptRenderer();

        $body = $response->getBody();
        $body->rewind();
        $body->write($renderer->dumpAssetsToString('css'));

        return $response->withHeader('Content-type', 'text/css');
    }
}
