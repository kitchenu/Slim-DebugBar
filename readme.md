## Slim Debugbar

This is a package to integrate [PHP Debug Bar](http://phpdebugbar.com/) with Slim 3.

## Installation

Require this package with composer:

```
composer require kitchenu/slim-debugbar
```

Register a Provider

```
$app = new Slim\App($config);

$provider = new Kitchenu\Debugbar\ServiceProvider();
$provider->register($app);
```