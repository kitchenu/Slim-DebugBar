<?php

namespace Kitchenu\Debugbar\Middleware;

use Exception;
use Kitchenu\Debugbar\SlimDebugBar;

class Debugbar
{
    /**
     * The SlimDebugBar instance
     *
     * @var SlimDebugBar
     */
    protected $debugbar;

    /**
     * The error handler
     *
     * @var callable
     */
    protected $errorHandler;

    /**
     * Create a new middleware instance.
     *
     * @param SlimDebugBar $debugbar
     * @param callable $errorHandler
     */
    public function __construct(SlimDebugBar $debugbar, callable $errorHandler)
    {
        $this->debugbar = $debugbar;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Debugbar middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  callable $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        try {
            $response = $next($request, $response); 
        } catch (Exception $e) {
            $this->debugbar->addException($e);
            // Handle the given exception
            $response = call_user_func($this->errorHandler, $request, $response, $e);
        }

        // Modify the response to add the Debugbar
        $this->debugbar->modifyResponse($response);

        return $response;
    }
}