<?php
namespace Icecave\Stump;

use Phake;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PrefixLoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->targetLogger = Phake::mock(LoggerInterface::class);

        $this->logger = new PrefixLogger(
            'The Prefix: ',
            $this->targetLogger
        );
    }

    public function testPrefix()
    {
        $this->assertSame(
            'The Prefix: ',
            $this->logger->prefix()
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
            'The Prefix: The message!',
            $context
        );
    }
}
