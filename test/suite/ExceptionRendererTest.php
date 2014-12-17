<?php
namespace Icecave\Stump;

use Exception;
use PHPUnit_Framework_TestCase;

class ExceptionRendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->exception = new Exception(
            'A basic exception.',
            10
        );

        $this->testException = new TestException(
            'A custom exception.',
            20,
            $this->exception
        );

        $this->exceptionRenderer = new ExceptionRenderer();
    }

    public function testRenderSingleException()
    {
        $expected  = 'Message: ' . $this->exception->getMessage() . PHP_EOL;
        $expected .= 'Code:    ' . $this->exception->getCode() . PHP_EOL;
        $expected .= 'Type:    ' . get_class($this->exception) . PHP_EOL;
        $expected .= sprintf(
            'Source:  %s:%d' . PHP_EOL,
            $this->exception->getFile(),
            $this->exception->getLine()
        );
        $trace = explode(PHP_EOL, $this->exception->getTraceAsString());
        foreach ($trace as $line) {
            $expected .= '    ' . $line . PHP_EOL;
        }
        $expected = trim($expected);

        $result = $this
            ->exceptionRenderer
            ->render($this->exception);

        $this->assertSame(
            $expected,
            $result
        );
    }

    public function testRenderNestedException()
    {
        $expected  = 'Message: ' . $this->testException->getMessage() . PHP_EOL;
        $expected .= 'Code:    ' . $this->testException->getCode() . PHP_EOL;
        $expected .= 'Type:    ' . get_class($this->testException) . PHP_EOL;
        $expected .= sprintf(
            'Source:  %s:%d' . PHP_EOL,
            $this->testException->getFile(),
            $this->testException->getLine()
        );
        $trace = explode(PHP_EOL, $this->testException->getTraceAsString());
        foreach ($trace as $line) {
            $expected .= '    ' . $line . PHP_EOL;
        }

        $expected .= PHP_EOL . PHP_EOL;

        $expected .= 'Message: ' . $this->exception->getMessage() . PHP_EOL;
        $expected .= 'Code:    ' . $this->exception->getCode() . PHP_EOL;
        $expected .= 'Type:    ' . get_class($this->exception) . PHP_EOL;
        $expected .= sprintf(
            'Source:  %s:%d' . PHP_EOL,
            $this->exception->getFile(),
            $this->exception->getLine()
        );
        $trace = explode(PHP_EOL, $this->exception->getTraceAsString());
        foreach ($trace as $line) {
            $expected .= '    ' . $line . PHP_EOL;
        }
        $expected = trim($expected);

        $result = $this
            ->exceptionRenderer
            ->render($this->testException);

        $this->assertSame(
            $expected,
            $result
        );
    }
}
