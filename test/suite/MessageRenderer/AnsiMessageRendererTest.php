<?php

namespace Icecave\Stump\MessageRenderer;

use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class AnsiMessageRendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->renderer = new AnsiMessageRenderer();
    }

    /**
     * @dataProvider renderTestVectors
     */
    public function testRender($level, $levelStyle, $messageStyle)
    {
        $expected = '<ESC>[2;37m<date><ESC>[39;49;22m '
                  . $levelStyle
                  . 'FOO<ESC>[39;49;22m '
                  . $messageStyle
                  . 'Log message.<ESC>[39;49;22m';

        $result = $this->renderer->render(
            $level,
            'FOO',
            '<date>',
            'Log message.'
        );

        $this->assertEquals(
            $expected,
            str_replace("\033", '<ESC>', $result)
        );
    }

    public function renderTestVectors()
    {
        return [
            [LogLevel::EMERGENCY, '<ESC>[1;37;41m', '<ESC>[0;31m'],
            [LogLevel::ALERT,     '<ESC>[1;37;41m', '<ESC>[0;31m'],
            [LogLevel::CRITICAL,  '<ESC>[1;37;41m', '<ESC>[0;31m'],
            [LogLevel::ERROR,     '<ESC>[0;31m',    '<ESC>[0;31m'],
            [LogLevel::WARNING,   '<ESC>[0;33m',    '<ESC>[0;33m'],
            [LogLevel::NOTICE,    '<ESC>[0;34m',    '<ESC>[0;34m'],
            [LogLevel::INFO,      '<ESC>[1;37m',    '<ESC>[39;49;22m'],
            [LogLevel::DEBUG,     '<ESC>[0m',       '<ESC>[2;37m'],
        ];
    }
}
