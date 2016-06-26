<?php

namespace Kitchenu\Debugbar;

use Kitchenu\Debugbar\Middleware\Debugbar;
use Slim\App;

class ServiceProvider
{
    /**
     * The App instance.
     *
     * @var App
     */
    protected $app;

    /**
     * @param  App
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Register DebugBar service.
     * 
     * @return void
     */
    public function register()
    {
        $container = $this->app->getContainer();

        $container['debugbar'] = function ($container) {
            return new SlimDebugBar($container->get('router'), $container->get('request'));
        };

        $this->app->group('/_debugbar', function() {
            $this->get('/assets/stylesheets', 'Kitchenu\Debugbar\Controllers\AssetController:css')
                ->setName('debugbar-assets-css');

            $this->get('/assets/javascript', 'Kitchenu\Debugbar\Controllers\AssetController:js')
                ->setName('debugbar-assets-js');
        });

        $enabled = $container->get('settings')['displayErrorDetails'];

        if (!$enabled) {
            return;
        }

        $this->app->add(new Debugbar($container['debugbar'], $container['router']));
    }
}
