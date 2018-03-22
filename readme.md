## Slim Debugbar

[![Build Status](https://travis-ci.org/kitchenu/Slim-DebugBar.svg?branch=master)](https://travis-ci.org/kitchenu/Slim-DebugBar)
[![Packagist License](https://poser.pugx.org/kitchenu/Slim-DebugBar/license.png)](https://packagist.org/packages/kitchenu/slim-debugbar)

This is a package to integrate [PHP Debug Bar](http://phpdebugbar.com/) with Slim 3.

## Installation

Require this package with composer:

```
composer require kitchenu/slim-debugbar
```

Register a Provider

```
$app = new Slim\App();

$provider = new Kitchenu\Debugbar\ServiceProvider();
$provider->register($app);
```

## Bridge collectors
```
$container = $app->getContainer();

$container['pdo'] = function () {
    return new PDO('sqlite::memory:');
};

$collector = new DebugBar\DataCollector\PDO\PDOCollector($container->pdo);
$container->debugbar->addCollector($collector);
```

Read [the documentation](http://phpdebugbar.com/docs/bridge-collectors.html#bridge-collectors) for more bridge collectors