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
        $expected  = 'MESSAGE: ' . $this->exception->getMessage() . PHP_EOL;
        $expected .= 'CAUSE: ' . $this->exception->getMessage() . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;
        $expected .= 'TYPE: ' . get_class($this->exception) . PHP_EOL;
        $expected .= 'MESSAGE: ' . $this->exception->getMessage() . PHP_EOL;
        $expected .= 'FILE: ' . $this->exception->getFile() . PHP_EOL;
        $expected .= 'LINE: ' . $this->exception->getLine() . PHP_EOL;
        $expected .= 'CODE: ' . $this->exception->getCode() . PHP_EOL;
        $expected .= 'TRACE:' . PHP_EOL;
        $expected .= $this->exception->getTraceAsString();

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
        $expected  = 'MESSAGE: ' . $this->testException->getMessage() . PHP_EOL;
        $expected .= 'CAUSE: ' . $this->exception->getMessage() . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;
        $expected .= 'TYPE: ' . get_class($this->testException) . PHP_EOL;
        $expected .= 'MESSAGE: ' . $this->testException->getMessage() . PHP_EOL;
        $expected .= 'FILE: ' . $this->testException->getFile() . PHP_EOL;
        $expected .= 'LINE: ' . $this->testException->getLine() . PHP_EOL;
        $expected .= 'CODE: ' . $this->testException->getCode() . PHP_EOL;
        $expected .= 'TRACE:' . PHP_EOL;
        $expected .= $this->testException->getTraceAsString() . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;
        $expected .= 'TYPE: ' . get_class($this->exception) . PHP_EOL;
        $expected .= 'MESSAGE: ' . $this->exception->getMessage() . PHP_EOL;
        $expected .= 'FILE: ' . $this->exception->getFile() . PHP_EOL;
        $expected .= 'LINE: ' . $this->exception->getLine() . PHP_EOL;
        $expected .= 'CODE: ' . $this->exception->getCode() . PHP_EOL;
        $expected .= 'TRACE:' . PHP_EOL;
        $expected .= $this->exception->getTraceAsString();

        $result = $this
            ->exceptionRenderer
            ->render($this->testException);

        $this->assertSame(
            $expected,
            $result
        );
    }
}
