<?php

namespace Kitchenu\Debugbar\Middleware;

use Kitchenu\Debugbar\SlimDebugBar;
use Slim\Interfaces\RouterInterface;

class Debugbar
{
    /**
     * The DebugBar instance
     *
     * @var SlimDebugbar
     */
    protected $debugbar;

    /**
     * The Router instance
     *
     * @var Router
     */
    protected $router;

    /**
     * Create a new middleware instance.
     *
     * @param  SlimDebugBar $debugbar
     * @param  RouterInterface $router
     */
    public function __construct(SlimDebugBar $debugbar, RouterInterface $router)
    {
        $this->debugbar = $debugbar;
        $this->router = $router;
    }

    /**
     * Debugbar middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $response = $next($request, $response);

        // Modify the response to add the Debugbar
        $this->debugbar->modifyResponse($response, $this->router);

        return $response;
    }
}
