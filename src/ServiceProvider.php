<?php

namespace Kitchenu\Debugbar;

use Kitchenu\Debugbar\Middleware\Debugbar;
use Slim\App;

class ServiceProvider
{
    /**
     * Default settings
     *
     * @var array
     */
    protected $setting = [
        'enabled'    => true,
        'collectors' => [
            'phpinfo'    => true,  // Php version
            'messages'   => true,  // Messages
            'time'       => true,  // Time Datalogger
            'memory'     => true,  // Memory usage
            'exceptions' => true,  // Exception displayer
            'request'    => true,  // Request logger
        ]
    ];

    /**
     * @param  array $setting
     */
    public function __construct($setting = [])
    {
        $this->setting = array_merge($this->setting, $setting);
    }

    /**
     * Register DebugBar service.
     *
     * @param  App $app
     *
     * @return void
     */
    public function register(App $app)
    {
        $container = $app->getContainer();

        $container['debugbar'] = function ($container) {
            return new SlimDebugBar($this->setting);
        };

        $app->group('/_debugbar', function() {
            $this->get('/assets/stylesheets', 'Kitchenu\Debugbar\Controllers\AssetController:css')
                ->setName('debugbar-assets-css');

            $this->get('/assets/javascript', 'Kitchenu\Debugbar\Controllers\AssetController:js')
                ->setName('debugbar-assets-js');
        });

        if (!$this->setting['enabled']) {
            return;
        }

        $app->add(new Debugbar($container['debugbar'], $container['router']));
    }
}
