<?php

namespace Kitchenu\Debugbar\Middleware;

use Kitchenu\Debugbar\SlimDebugBar;

class Debugbar
{
    /**
     * The DebugBar instance
     *
     * @var SlimDebugbar
     */
    protected $debugbar;

    /**
     * Create a new middleware instance.
     *
     * @param  SlimDebugBar $debugbar
     */
    public function __construct(SlimDebugBar $debugbar)
    {
        $this->debugbar = $debugbar;
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
        $this->debugbar->modifyResponse($response);

        return $response;
    }
}
