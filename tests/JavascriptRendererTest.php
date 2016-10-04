<?php

namespace Kitchenu\Debugbar\Tests;

use Kitchenu\Debugbar\JavascriptRenderer;

class JavascriptRendererTest extends SlimDebugBarTestCase
{
    /**
     * @var JavascriptRenderer
     */
    protected $renderer;

    public function setUp()
    {
        parent::setUp();
        $this->renderer = new JavascriptRenderer($this->debugbar);
    }

    public function testRenderHeadSlim()
    {
        $this->app->get('css_test', function() {})->setName('debugbar-assets-css');
        $this->app->get('js_test', function() {})->setName('debugbar-assets-js');
        $html = $this->renderer->renderHeadSlim($this->app->getContainer()->router);
 
        $this->assertContains('<link rel="stylesheet" type="text/css" href="css_test', $html);
        $this->assertContains('<script type="text/javascript" src="js_test', $html);
        $this->assertContains('<script type="text/javascript" src="js_test', $html);
    }

    public function testDumpAssetsToString()
    {
        $string = $this->renderer->dumpAssetsToString('css');
        $this->assertContains('@font-face', $string);
    }
}
