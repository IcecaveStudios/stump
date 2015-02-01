<?php
namespace Icecave\Stump\MessageRenderer;

use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class PlainMessageRendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->renderer = new PlainMessageRenderer;
    }

    public function testRender()
    {
        $this->assertEquals(
            '<date> FOO Log message.',
            $this->renderer->render(
                LogLevel::DEBUG,
                'FOO',
                '<date>',
                'Log message.'
            )
        );
    }
}
