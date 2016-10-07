<?php

namespace Kitchenu\Debugbar\Middleware;

use Exception;
use Kitchenu\Debugbar\SlimDebugBar;
use Slim\Handlers\Error;

class Debugbar
{
    /**
     * The SlimDebugBar instance
     *
     * @var SlimDebugBar
     */
    protected $debugbar;

    /**
     * The Error instance
     *
     * @var Error
     */
    protected $error;

    /**
     * Create a new middleware instance.
     *
     * @param SlimDebugBar
     * @param Error
     */
    public function __construct(SlimDebugBar $debugbar, Error $error)
    {
        $this->debugbar = $debugbar;
        $this->error = $error;
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
        try {
            $response = $next($request, $response); 
        } catch (Exception $e) {
            $this->debugbar->addException($e);
            // Handle the given exception
            $response = $this->error->__invoke($request, $response, $e);
        }

        // Modify the response to add the Debugbar
        $this->debugbar->modifyResponse($response);

        return $response;
    }
}