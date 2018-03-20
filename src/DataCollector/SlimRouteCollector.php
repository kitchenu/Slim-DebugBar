<?php

namespace Kitchenu\Debugbar\DataCollector;

use Closure;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Interop\Container\ContainerInterface as Container;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Slim\DeferredCallable;
use Slim\Http\Request;
use Slim\Route;
use Slim\Router;

class SlimRouteCollector extends DataCollector implements Renderable
{
    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * The router instance.
     *
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @param Container $container
     */
    public function __construct(Router $router, Request $request)
    {
        $this->router = $router;
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $routeInfo = $this->router->dispatch($this->request);
        $route = (isset($routeInfo[1]) && is_string($routeInfo[1])) ? $this->router->lookupRoute($routeInfo[1]) : null;

        return $route ? $this->getRouteInformation($route) : null;
    }

    /**
     * Get the route information for a given route.
     *
     * @param  Route $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        $result = [];

        $result['uri'] = $route->getMethods()[0] . ' ' . $route->getPattern();

        $result['name'] = $route->getName() ?: '';

        $result['group'] = '';
        foreach ($route->getGroups() as $group) {
            $result['group'] .= $group->getPattern();
        }

        $callable = $route->getCallable();

        $result['middleware'] = [];
        foreach ($route->getMiddleware() as $middleware) {
            $result['middleware'][] = Closure::bind(function () {
                return get_class($this->callable);
            }, $middleware, DeferredCallable::class)->__invoke();
        }

        if(is_array($callable)) {
            $result['callable'] = get_class($callable[0]) . ':' . $callable[1];
            $reflector = new ReflectionMethod($callable[0], $callable[1]);
        } elseif ($callable instanceof Closure) {
            $result['callable'] = $this->formatVar($callable);
            $reflector = new ReflectionFunction($callable);
        } elseif (is_object($callable)) {
            $result['callable'] = get_class($callable);
            $reflector = new ReflectionMethod($callable, '__invoke');
        } else {
            $result['callable'] = $callable;
            $callable = explode(':', $callable);
            if (isset($callable[1])) {
                $reflector = new ReflectionMethod($callable[0], $callable[1]);
            } else {
                $reflector = new ReflectionMethod($callable, '__invoke');
            }
        }

        $result['file'] = $reflector->getFileName() . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'route';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            'route' => [
                'icon'    => 'share',
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget',
                'map'     => 'route',
                'default' => '{}',
            ],
            'currentroute' => [
                'icon'    => 'share',
                'tooltip' => 'Route',
                'map'     => 'route.uri',
                'default' => '',
            ],
        ];
    }
}
