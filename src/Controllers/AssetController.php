<?php

namespace Kitchenu\Debugbar\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AssetController extends Controller
{
    /**
     * Return the javascript for the Debugbar
     *
     * @param  Request $request
     * @param  Response $response
     * @param  array $args
     * @return ResponseInterface
     */
    public function js(Request $request, Response $response, $args)
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
     * @param  Request $request
     * @param  Response $response
     * @param  array $args
     * @return ResponseInterface
     */
    public function css(Request $request, Response $response, $args)
    {
        $renderer = $this->debugbar->getJavascriptRenderer();

        $body = $response->getBody();
        $body->rewind();
        $body->write($renderer->dumpAssetsToString('css'));

        return $response->withHeader('Content-type', 'text/css');
    }
}
