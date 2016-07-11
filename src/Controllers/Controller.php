<?php 

namespace Kitchenu\Debugbar\Controllers;

use Interop\Container\ContainerInterface as Container;

abstract class Controller
{
    /**
     * The DebugBar instance
     *
     * @var \Kitchenu\Debugbar\SlimDebugBar
     */
    protected $debugbar;

    /**
     * @param  Container $container
     */
    public function __construct(Container $container)
    {
        $this->debugbar = $container->get('debugbar');
    }
}