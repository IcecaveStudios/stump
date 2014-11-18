<?php
namespace Icecave\Stump;

use PHPUnit_Framework_TestCase;
use Phake;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

class SubLoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->targetLogger = Phake::mock('Psr\Log\LoggerInterface');

        $this->logger = new SubLogger(
            'sub-logger-name',
            $this->targetLogger
        );
    }

    public function testName()
    {
        $this->assertSame(
            'sub-logger-name',
            $this->logger->name()
        );
    }

    public function testLogger()
    {
        $this->assertSame(
            $this->targetLogger,
            $this->logger->logger()
        );
    }

    public function testLog()
    {
        $context = ['foo' => 'bar'];

        $this->logger->log(LogLevel::ERROR, 'The message!', $context);

        Phake::verify($this->targetLogger)->log(
            LogLevel::ERROR,
            'sub-logger-name: The message!',
            $context
        );
    }
}
