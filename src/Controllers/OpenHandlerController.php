<?php

namespace Kitchenu\Debugbar\Controllers;

use DebugBar\OpenHandler;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;

class OpenHandlerController extends Controller
{
    /**
     * Return the javascript for the Debugbar
     *
     * @param  Request $request
     * @param  Response $response
     * @param  array $args
     *
     * @return Response
     */
    public function handle(Request $request, Response $response, $args)
    {
        $openHandler = new OpenHandler($this->debugbar);

        $data = $openHandler->handle(null, false, false);

        $body = $response->getBody();
        $body->rewind();
        $body->write($data);

        // Ensure that the json encoding passed successfully
        if ($data === false) {
            throw new RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
    }
}
