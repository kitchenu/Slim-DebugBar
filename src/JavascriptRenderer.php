<?php

namespace Kitchenu\Debugbar;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer as BaseJavascriptRenderer;
use Slim\Interfaces\RouterInterface;

/**
 * {@inheritdoc}
 */
class JavascriptRenderer extends BaseJavascriptRenderer
{
    /**
     * {@inheritdoc}
     */
    public function __construct(DebugBar $debugBar, $baseUrl = null, $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);

        $this->cssVendors['fontawesome'] = __DIR__ . '/Resources/vendor/font-awesome/style.css';
    }

    /**
     * Renders the html to include needed assets
     *
     * Only useful if Assetic is not used
     *
     * @param  RouterInterface Description
     *
     * @return string
     */
    public function renderHeadSlim(RouterInterface $router)
    {
        $jsModified = $this->getModifiedTime('js');
        $cssModified = $this->getModifiedTime('css');

        $html = '';
        $html .= sprintf(
            '<link rel="stylesheet" type="text/css" href="%s?%s">' . "\n",
            $router->pathFor('debugbar-assets-css'),
            $cssModified
        );
        $html .= sprintf(
            '<script type="text/javascript" src="%s?%s"></script>' . "\n",
            $router->pathFor('debugbar-assets-js'),
            $jsModified
        );

        if ($this->isJqueryNoConflictEnabled()) {
            $html .= '<script type="text/javascript">jQuery.noConflict(true);</script>' . "\n";
        }

        return $html;
    }

    /**
     * Get the last modified time of any assets.
     *
     * @param string $type 'js' or 'css'
     *
     * @return int
     */
    protected function getModifiedTime($type)
    {
        $files = $this->getAssets($type);

        $latest = 0;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latest) {
                $latest = $mtime;
            }
        }
        return $latest;
    }

    /**
     * Return assets as a string
     *
     * @param string $type 'js' or 'css'
     *
     * @return string
     */
    public function dumpAssetsToString($type)
    {
        $files = $this->getAssets($type);

        $content = '';
        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }

}
